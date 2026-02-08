<?php
require_once '../config/db.php';
require_once '../includes/functions.php';
$title = "Manager Dashboard";

if (!isLoggedIn() || $_SESSION['role_id'] !== 6) {
    redirect("/hrms/auth/login.php");
}

$user_id = $_SESSION['user_id'];
$company_id = $_SESSION['company_id'];

// Get manager's employee record
$manager_result = query($mysqli, "SELECT * FROM employees WHERE user_id = ?", [$user_id]);
$manager = $manager_result['success'] ? $manager_result['data'][0] : null;

if (!$manager) {
    redirect("/hrms/pages/unauthorized.php");
}

$manager_id = $manager['id'];
$manager_department_id = $manager['department_id'];

// Get team members (employees in the same department)
// Get team members (employees in the same department OR in teams managed by this user)
$team_members_result = query($mysqli, "
    SELECT DISTINCT e.*, u.email, d.name as department_name, des.name as designation_name
    FROM employees e
    JOIN users u ON e.user_id = u.id
    LEFT JOIN departments d ON e.department_id = d.id
    LEFT JOIN designations des ON e.designation_id = des.id
    LEFT JOIN team_members tm ON e.id = tm.employee_id
    LEFT JOIN teams t ON tm.team_id = t.id
    WHERE (e.department_id = ? OR t.created_by = ?) 
    AND e.id != ? 
    AND e.status = 'active'
    ORDER BY e.first_name ASC
", [$manager_department_id, $user_id, $manager_id]);

$team_members = $team_members_result['success'] ? $team_members_result['data'] : [];

// Get team member IDs for filtering
$team_member_ids = array_column($team_members, 'id');
$ids_placeholder = !empty($team_member_ids) ? implode(',', array_map('intval', $team_member_ids)) : '0';

// Get pending leave requests from team members
$pending_leaves_result = query($mysqli, "
    SELECT l.*, e.first_name, e.last_name, e.employee_code
    FROM leaves l
    JOIN employees e ON l.employee_id = e.id
    WHERE e.id IN ($ids_placeholder) AND l.status = 'pending'
    ORDER BY l.applied_at DESC
    LIMIT 10
", []);

$pending_leaves = $pending_leaves_result['success'] ? $pending_leaves_result['data'] : [];

// Get team statistics
$team_stats = [
    'total_team_members' => count($team_members),
    'pending_leaves' => count($pending_leaves),
    'on_leave_today' => 0,
    'completed_tasks' => 0,
    'pending_tasks' => 0
];

// Get employees on leave today
$today = date('Y-m-d');
$on_leave_today_result = query($mysqli, "
    SELECT COUNT(a.id) as count
    FROM attendance a
    JOIN employees e ON a.employee_id = e.id
    WHERE e.id IN ($ids_placeholder) AND a.date = ? AND a.status = 'leave'
", [$today]);

$team_stats['on_leave_today'] = $on_leave_today_result['success'] ? $on_leave_today_result['data'][0]['count'] : 0;

// Get task statistics
$task_stats_result = query($mysqli, "
    SELECT 
        COUNT(CASE WHEN t.status = 'completed' THEN 1 END) as completed,
        COUNT(CASE WHEN t.status IN ('pending', 'in_progress') THEN 1 END) as pending
    FROM tasks t
    JOIN employees e ON t.employee_id = e.id
    WHERE (e.id IN ($ids_placeholder) OR t.assigned_by = ?)
", [$user_id]);

if ($task_stats_result['success']) {
    $task_stats = $task_stats_result['data'][0];
    $team_stats['completed_tasks'] = $task_stats['completed'];
    $team_stats['pending_tasks'] = $task_stats['pending'];
}
$recent_activities_result = query($mysqli, "
    SELECT 
        l.id as id,
        'leave' as type,
        CONCAT(e.first_name, ' ', e.last_name) as employee_name,
        CONCAT('Applied for ', l.leave_type, ' leave') as activity,
        l.applied_at as created_at
    FROM leaves l
    JOIN employees e ON l.employee_id = e.id
    WHERE e.id IN ($ids_placeholder) AND l.applied_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
    
    UNION ALL
    
    SELECT 
        t.id as id,
        'task' as type,
        CONCAT(e.first_name, ' ', e.last_name) as employee_name,
        CONCAT('Completed task: ', t.title) as activity,
        t.created_at
    FROM tasks t
    JOIN employees e ON t.employee_id = e.id
    WHERE (e.id IN ($ids_placeholder) OR t.assigned_by = ?) 
    AND t.status = 'completed' 
    AND t.created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
    
    ORDER BY created_at DESC
    LIMIT 10
", [$user_id]);

$recent_activities = $recent_activities_result['success'] ? $recent_activities_result['data'] : [];

require_once '../components/layout/header.php';
$additionalScripts = ['attendance-calendar.js', 'attendance-checkin.js'];
?>

<div class="d-flex">
    <?php require_once '../components/layout/sidebar.php'; ?>
    <div class="p-3 p-md-4" style="flex: 1;">
        <div id="dashboardStats" class="row"></div>

        <!-- Row 1: Check-in and Pending Leaves -->
        <div class="row">
            <div class="col-md-4 mb-4">
                <div class="card shadow-sm h-100">
                    <div class="card-header">
                        <h6 class="m-0 font-weight-bold">Today's Work Hours</h6>
                    </div>
                    <div class="card-body d-flex flex-column justify-content-center">
                        <div id="attendanceCheckInContainer"></div>
                    </div>
                </div>
            </div>
            <div class="col-md-8 mb-4">
                <div class="card shadow-sm h-100">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h6 class="m-0 font-weight-bold">Pending Leave Requests</h6>
                        <a href="leave_approval.php" class="btn btn-sm btn-outline-primary">View All</a>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($pending_leaves)): ?>
                            <div class="list-group list-group-flush">
                                <?php foreach ($pending_leaves as $leave): ?>
                                    <div class="list-group-item d-flex justify-content-between align-items-center px-0">
                                        <div>
                                            <strong><?= htmlspecialchars($leave['first_name'] . ' ' . $leave['last_name']) ?></strong>
                                            <small class="d-block text-muted">
                                                <?= htmlspecialchars($leave['leave_type']) ?> -
                                                <?= date('M j', strtotime($leave['start_date'])) ?> to
                                                <?= date('M j', strtotime($leave['end_date'])) ?>
                                            </small>
                                        </div>
                                        <div class="text-end">
                                            <div class="btn-group btn-group-sm">
                                                <button class="btn btn-success btn-sm"
                                                    onclick="approveLeave(<?= $leave['id'] ?>)">
                                                    <i class="ti ti-check"></i>
                                                </button>
                                                <button class="btn btn-danger btn-sm"
                                                    onclick="rejectLeave(<?= $leave['id'] ?>)">
                                                    <i class="ti ti-x"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <div class="text-center text-muted p-4">
                                <i class="ti ti-circle-check" style="font-size: 2rem;"></i>
                                <p class="mb-0 mt-2">No pending leave requests</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-8 order-md-1 order-2">
                <div class="row">
                    <div class="col-12 mb-4">
                        <div class="card shadow-sm">
                            <div class="card-header">
                                <h6 class="m-0 font-weight-bold">Recent Team Activities</h6>
                            </div>
                            <div class="card-body">
                                <?php if (!empty($recent_activities)): ?>
                                    <div class="timeline">
                                        <?php foreach ($recent_activities as $activity): ?>
                                            <?php
                                            $link = '#';
                                            if ($activity['type'] === 'leave') {
                                                $link = 'leave_approval.php?id=' . $activity['id'];
                                            } elseif ($activity['type'] === 'task') {
                                                $link = 'task_management.php?id=' . $activity['id'];
                                            }
                                            ?>
                                            <div class="timeline-item">
                                                <div
                                                    class="timeline-marker bg-<?= $activity['type'] === 'leave' ? 'warning' : 'success' ?>">
                                                </div>
                                                <div class="timeline-content bg-body">
                                                    <a href="<?= $link ?>" class="text-decoration-none text-reset d-block">
                                                        <div class="fw-bold"><?= htmlspecialchars($activity['employee_name']) ?>
                                                        </div>
                                                        <div class="text-muted"><?= htmlspecialchars($activity['activity']) ?>
                                                        </div>
                                                        <small
                                                            class="text-muted"><?= date('M j, Y g:i A', strtotime($activity['created_at'])) ?></small>
                                                    </a>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                <?php else: ?>
                                    <div class="text-center text-muted p-4">
                                        <i class="ti ti-history" style="font-size: 2rem;"></i>
                                        <p class="mb-0 mt-2">No recent activities</p>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-4">
                        <div class="card shadow-sm h-100">
                            <div class="card-header">
                                <h6 class="m-0 font-weight-bold">My Personal To-Do List</h6>
                            </div>
                            <div class="card-body">
                                <form id="todoForm" class="d-flex mb-3">
                                    <input type="text" name="task" class="form-control me-2"
                                        placeholder="Add a new task..." required>
                                    <button type="submit" class="btn btn-primary">Add</button>
                                </form>
                                <ul class="list-group list-group-flush" id="todoList"></ul>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 mb-4">
                        <div class="card shadow-sm h-100">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h6 class="m-0 font-weight-bold">Team Members</h6>
                                <a href="team_management.php" class="btn btn-sm btn-outline-primary">Manage</a>
                            </div>
                            <div class="card-body">
                                <?php if (!empty($team_members)): ?>
                                    <div class="list-group list-group-flush">
                                        <?php foreach (array_slice($team_members, 0, 5) as $member): ?>
                                            <div class="list-group-item d-flex justify-content-between align-items-center px-0">
                                                <div>
                                                    <div class="fw-bold">
                                                        <?= htmlspecialchars($member['first_name'] . ' ' . $member['last_name']) ?>
                                                    </div>
                                                    <small
                                                        class="text-muted"><?= htmlspecialchars($member['designation_name'] ?? 'N/A') ?></small>
                                                </div>
                                                <div class="text-end">
                                                    <span class="badge bg-success-subtle text-success-emphasis">Active</span>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                        <?php if (count($team_members) > 5): ?>
                                            <div class="text-center mt-2">
                                                <small class="text-muted">And <?= count($team_members) - 5 ?>
                                                    more...</small>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                <?php else: ?>
                                    <div class="text-center text-muted p-4">
                                        <i class="ti ti-users" style="font-size: 2rem;"></i>
                                        <p class="mb-0 mt-2">No team members found</p>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Column (Calendar) -->
            <div class="col-md-4 mb-4 order-md-2 order-1">
                <div class="card shadow-sm">
                    <div class="card-header">
                        <h6 class="m-0 font-weight-bold">My Attendance (Current Month)</h6>
                    </div>
                    <div class="card-body">
                        <div id="attendanceCalendarContainer"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once '../components/layout/footer.php'; ?>
<script>
    $(function () {
        // Initialize To-Do List
        initializeTodoList('#todoForm', '#todoList');

        // Initialize attendance check-in component
        new AttendanceCheckIn({
            containerId: 'attendanceCheckInContainer',
            allowCheckIn: true,
            allowCheckOut: true,
            showDetailedTime: true
        });

        // Initialize attendance calendar
        new AttendanceCalendar({
            containerId: 'attendanceCalendarContainer',
            showMonthNavigation: true,
            onlyCurrentEmployee: true,
            containerClass: 'row row-cols-1 row-cols-md-2 row-cols-xl-4 g-4' // Adjust grid if supported or needed
        });
    });

    // Render Stats using global function
    const dashboardStats = [
        { label: 'Team Members', value: '<?= $team_stats['total_team_members'] ?>', color: 'primary', icon: 'users' },
        { label: 'Pending Leaves', value: '<?= $team_stats['pending_leaves'] ?>', color: 'warning', icon: 'clock' },
        { label: 'Pending Tasks', value: '<?= $team_stats['pending_tasks'] ?>', color: 'info', icon: 'checklist' },
        { label: 'Completed Tasks', value: '<?= $team_stats['completed_tasks'] ?>', color: 'success', icon: 'circle-check' }
    ];
    renderStatCards('dashboardStats', dashboardStats);

    function approveLeave(leaveId) {
        showConfirmationModal('Are you sure you want to approve this leave request?', () => {
            fetch('/hrms/api/api_manager.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `action=approve_leave&leave_id=${leaveId}`
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showToast(data.message, 'success');
                        location.reload();
                    } else {
                        showToast(data.message, 'error');
                    }
                })
                .catch(error => {
                    showToast('An error occurred. Please try again.', 'error');
                });
        }, 'Approve Leave', 'Approve', 'btn-success');
    }

    function rejectLeave(leaveId) {
        showConfirmationModal('Are you sure you want to reject this leave request?', () => {
            fetch('/hrms/api/api_manager.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `action=reject_leave&leave_id=${leaveId}`
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showToast(data.message, 'success');
                        location.reload();
                    } else {
                        showToast(data.message, 'error');
                    }
                })
                .catch(error => {
                    showToast('An error occurred. Please try again.', 'error');
                });
        }, 'Reject Leave', 'Reject', 'btn-danger');
    }
</script>