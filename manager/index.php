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
$team_members_result = query($mysqli, "
    SELECT e.*, u.email, d.name as department_name, des.name as designation_name
    FROM employees e
    JOIN users u ON e.user_id = u.id
    LEFT JOIN departments d ON e.department_id = d.id
    LEFT JOIN designations des ON e.designation_id = des.id
    WHERE e.department_id = ? AND e.id != ? AND e.status = 'active'
    ORDER BY e.first_name ASC
", [$manager_department_id, $manager_id]);

$team_members = $team_members_result['success'] ? $team_members_result['data'] : [];

// Get pending leave requests from team members
$pending_leaves_result = query($mysqli, "
    SELECT l.*, e.first_name, e.last_name, e.employee_code
    FROM leaves l
    JOIN employees e ON l.employee_id = e.id
    WHERE e.department_id = ? AND l.status = 'pending'
    ORDER BY l.applied_at DESC
    LIMIT 10
", [$manager_department_id]);

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
    WHERE e.department_id = ? AND a.date = ? AND a.status = 'leave'
", [$manager_department_id, $today]);

$team_stats['on_leave_today'] = $on_leave_today_result['success'] ? $on_leave_today_result['data'][0]['count'] : 0;

// Get task statistics
$task_stats_result = query($mysqli, "
    SELECT 
        COUNT(CASE WHEN t.status = 'completed' THEN 1 END) as completed,
        COUNT(CASE WHEN t.status IN ('pending', 'in_progress') THEN 1 END) as pending
    FROM tasks t
    JOIN employees e ON t.employee_id = e.id
    WHERE e.department_id = ?
", [$manager_department_id]);

if ($task_stats_result['success']) {
    $task_stats = $task_stats_result['data'][0];
    $team_stats['completed_tasks'] = $task_stats['completed'];
    $team_stats['pending_tasks'] = $task_stats['pending'];
}

// Get recent team activities (last 7 days)
$recent_activities_result = query($mysqli, "
    SELECT 
        'leave' as type,
        CONCAT(e.first_name, ' ', e.last_name) as employee_name,
        CONCAT('Applied for ', l.leave_type, ' leave') as activity,
        l.applied_at as created_at
    FROM leaves l
    JOIN employees e ON l.employee_id = e.id
    WHERE e.department_id = ? AND l.applied_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
    
    UNION ALL
    
    SELECT 
        'task' as type,
        CONCAT(e.first_name, ' ', e.last_name) as employee_name,
        CONCAT('Completed task: ', t.title) as activity,
        t.created_at
    FROM tasks t
    JOIN employees e ON t.employee_id = e.id
    WHERE e.department_id = ? AND t.status = 'completed' AND t.created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
    
    ORDER BY created_at DESC
    LIMIT 10
", [$manager_department_id, $manager_department_id]);

$recent_activities = $recent_activities_result['success'] ? $recent_activities_result['data'] : [];

require_once '../components/layout/header.php';
?>

<div class="d-flex">
    <?php require_once '../components/layout/sidebar.php'; ?>
    <div class="p-3 p-md-4" style="flex: 1;">
        <h2 class="h3 mb-4 text-gray-800">
            <i class="fas fa-tachometer-alt me-2"></i>Manager Dashboard
            <small class="text-muted">- <?= htmlspecialchars($manager['first_name'] . ' ' . $manager['last_name']) ?></small>
        </h2>

        <!-- Stat Cards Row -->
        <div class="row">
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card stat-card shadow-sm">
                    <div class="card-body">
                        <div class="icon-circle bg-primary"><i class="fas fa-users"></i></div>
                        <div>
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Team Members</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?= $team_stats['total_team_members'] ?></div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card stat-card shadow-sm">
                    <div class="card-body">
                        <div class="icon-circle bg-warning"><i class="fas fa-clock"></i></div>
                        <div>
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Pending Leaves</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?= $team_stats['pending_leaves'] ?></div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card stat-card shadow-sm">
                    <div class="card-body">
                        <div class="icon-circle bg-info"><i class="fas fa-tasks"></i></div>
                        <div>
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Pending Tasks</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?= $team_stats['pending_tasks'] ?></div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card stat-card shadow-sm">
                    <div class="card-body">
                        <div class="icon-circle bg-success"><i class="fas fa-check-circle"></i></div>
                        <div>
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Completed Tasks</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?= $team_stats['completed_tasks'] ?></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Content Row -->
        <div class="row">
            <!-- Pending Leave Requests -->
            <div class="col-lg-6 mb-4">
                <div class="card shadow-sm h-100">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h6 class="m-0 font-weight-bold">Pending Leave Requests</h6>
                        <a href="leave_approval.php" class="btn btn-sm btn-primary">View All</a>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($pending_leaves)): ?>
                            <div class="list-group list-group-flush">
                                <?php foreach ($pending_leaves as $leave): ?>
                                    <div class="list-group-item d-flex justify-content-between align-items-center px-0">
                                        <div>
                                            <div class="fw-bold"><?= htmlspecialchars($leave['first_name'] . ' ' . $leave['last_name']) ?></div>
                                            <small class="text-muted">
                                                <?= htmlspecialchars($leave['leave_type']) ?> - 
                                                <?= date('M j, Y', strtotime($leave['start_date'])) ?> to 
                                                <?= date('M j, Y', strtotime($leave['end_date'])) ?>
                                            </small>
                                        </div>
                                        <div class="text-end">
                                            <small class="text-muted d-block"><?= date('M j', strtotime($leave['applied_at'])) ?></small>
                                            <div class="btn-group btn-group-sm mt-1">
                                                <button class="btn btn-success btn-sm" onclick="approveLeave(<?= $leave['id'] ?>)">
                                                    <i class="fas fa-check"></i>
                                                </button>
                                                <button class="btn btn-danger btn-sm" onclick="rejectLeave(<?= $leave['id'] ?>)">
                                                    <i class="fas fa-times"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <div class="text-center text-muted p-4">
                                <i class="fas fa-check-circle fa-3x mb-3"></i>
                                <p>No pending leave requests</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Team Members -->
            <div class="col-lg-6 mb-4">
                <div class="card shadow-sm h-100">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h6 class="m-0 font-weight-bold">Team Members</h6>
                        <a href="team_management.php" class="btn btn-sm btn-primary">Manage Team</a>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($team_members)): ?>
                            <div class="list-group list-group-flush">
                                <?php foreach (array_slice($team_members, 0, 5) as $member): ?>
                                    <div class="list-group-item d-flex justify-content-between align-items-center px-0">
                                        <div>
                                            <div class="fw-bold"><?= htmlspecialchars($member['first_name'] . ' ' . $member['last_name']) ?></div>
                                            <small class="text-muted"><?= htmlspecialchars($member['designation_name'] ?? 'N/A') ?></small>
                                        </div>
                                        <div class="text-end">
                                            <span class="badge bg-success">Active</span>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                                <?php if (count($team_members) > 5): ?>
                                    <div class="text-center mt-2">
                                        <small class="text-muted">And <?= count($team_members) - 5 ?> more...</small>
                                    </div>
                                <?php endif; ?>
                            </div>
                        <?php else: ?>
                            <div class="text-center text-muted p-4">
                                <i class="fas fa-users fa-3x mb-3"></i>
                                <p>No team members found</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Activities -->
        <div class="row">
            <div class="col-12">
                <div class="card shadow-sm">
                    <div class="card-header">
                        <h6 class="m-0 font-weight-bold">Recent Team Activities</h6>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($recent_activities)): ?>
                            <div class="timeline">
                                <?php foreach ($recent_activities as $activity): ?>
                                    <div class="timeline-item">
                                        <div class="timeline-marker bg-<?= $activity['type'] === 'leave' ? 'warning' : 'success' ?>"></div>
                                        <div class="timeline-content">
                                            <div class="fw-bold"><?= htmlspecialchars($activity['employee_name']) ?></div>
                                            <div class="text-muted"><?= htmlspecialchars($activity['activity']) ?></div>
                                            <small class="text-muted"><?= date('M j, Y g:i A', strtotime($activity['created_at'])) ?></small>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <div class="text-center text-muted p-4">
                                <i class="fas fa-history fa-3x mb-3"></i>
                                <p>No recent activities</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once '../components/layout/footer.php'; ?>

<style>
.timeline {
    position: relative;
    padding-left: 30px;
}

.timeline-item {
    position: relative;
    margin-bottom: 20px;
}

.timeline-marker {
    position: absolute;
    left: -35px;
    top: 5px;
    width: 12px;
    height: 12px;
    border-radius: 50%;
    border: 2px solid #fff;
    box-shadow: 0 0 0 2px #e3e6f0;
}

.timeline-content {
    background: #f8f9fc;
    padding: 15px;
    border-radius: 8px;
    border-left: 3px solid #4e73df;
}
</style>

<script>
function approveLeave(leaveId) {
    if (confirm('Are you sure you want to approve this leave request?')) {
        fetch('/hrms/api/api_manager.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
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
    }
}

function rejectLeave(leaveId) {
    if (confirm('Are you sure you want to reject this leave request?')) {
        fetch('/hrms/api/api_manager.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
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
    }
}
</script>
