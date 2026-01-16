<?php
require_once '../config/db.php';
require_once '../includes/functions.php';
require_once '../includes/notification_helpers.php';

$title = "Notifications";

if (!isLoggedIn()) {
    redirect("/hrms/auth/login.php");
}

$user_id = $_SESSION['user_id'];
$role_id = $_SESSION['role_id'];
$company_id = $_SESSION['company_id'];

// Get employee ID
$emp_result = query($mysqli, "SELECT id, department_id FROM employees WHERE user_id = ?", [$user_id]);
$employee_id = $emp_result['success'] && !empty($emp_result['data']) ? $emp_result['data'][0]['id'] : 0;
$department_id = $emp_result['success'] && !empty($emp_result['data']) ? $emp_result['data'][0]['department_id'] : 0;

// Get all notifications for the user (limit 50)
$notifications = getNotifications($mysqli, $user_id, 50, false);

// Get pending leave requests (for approvers: Manager, HR, Owner)
$pending_leaves = [];
if (in_array($role_id, [2, 3, 6])) {
    if ($role_id == 6) {
        // Manager: Get leaves from their team/department
        $pending_leaves = query($mysqli, "
            SELECT l.*, e.first_name, e.last_name 
            FROM leaves l
            JOIN employees e ON l.employee_id = e.id
            JOIN users u ON e.user_id = u.id
            WHERE l.status = 'pending' 
            AND u.company_id = ?
            AND (e.department_id = ? OR e.id IN (
                SELECT tm.employee_id FROM team_members tm WHERE tm.assigned_by = ?
            ))
            ORDER BY l.applied_at DESC
            LIMIT 10
        ", [$company_id, $department_id, $user_id])['data'] ?? [];
    } elseif ($role_id == 3) {
        // HR: Get all pending leaves from employees and managers
        $pending_leaves = query($mysqli, "
            SELECT l.*, e.first_name, e.last_name 
            FROM leaves l
            JOIN employees e ON l.employee_id = e.id
            JOIN users u ON e.user_id = u.id
            WHERE l.status = 'pending' AND u.company_id = ? AND u.role_id IN (4, 6)
            ORDER BY l.applied_at DESC
            LIMIT 10
        ", [$company_id])['data'] ?? [];
    } elseif ($role_id == 2) {
        // Owner: Get all pending leaves
        $pending_leaves = query($mysqli, "
            SELECT l.*, e.first_name, e.last_name 
            FROM leaves l
            JOIN employees e ON l.employee_id = e.id
            JOIN users u ON e.user_id = u.id
            WHERE l.status = 'pending' AND u.company_id = ?
            ORDER BY l.applied_at DESC
            LIMIT 10
        ", [$company_id])['data'] ?? [];
    }
}

// Get my pending tasks
$pending_tasks = [];
if ($employee_id) {
    $pending_tasks = query($mysqli, "
        SELECT t.*, CONCAT(e.first_name, ' ', e.last_name) as assigned_by_name
        FROM tasks t
        LEFT JOIN users u ON t.assigned_by = u.id
        LEFT JOIN employees e ON u.id = e.user_id
        WHERE t.employee_id = ? AND t.status IN ('pending', 'in_progress')
        ORDER BY t.due_date ASC
        LIMIT 10
    ", [$employee_id])['data'] ?? [];
}

// Get my pending leave requests
$my_pending_leaves = [];
if ($employee_id) {
    $my_pending_leaves = query($mysqli, "
        SELECT * FROM leaves 
        WHERE employee_id = ? AND status = 'pending'
        ORDER BY applied_at DESC
        LIMIT 5
    ", [$employee_id])['data'] ?? [];
}

// Get recent feedback for manager
$recent_feedback = [];
if (in_array($role_id, [2, 3, 6])) {
    $recent_feedback = query($mysqli, "
        SELECT f.*, e.first_name, e.last_name
        FROM feedback f
        JOIN employees e ON f.employee_id = e.id
        WHERE f.status = 'pending' AND e.department_id = ?
        ORDER BY f.created_at DESC
        LIMIT 5
    ", [$department_id])['data'] ?? [];
}

require_once '../components/layout/header.php';
?>

<div class="d-flex">
    <?php require_once '../components/layout/sidebar.php'; ?>
    <div class="p-3 p-md-4" style="flex: 1;">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4">
            <h2 class="h3 mb-0"><i class="ti ti-bell me-2"></i>Notifications & Requests</h2>
            <button class="btn btn-outline-primary btn-sm" id="markAllReadBtn">
                <i class="ti ti-checks me-1"></i>Mark all as read
            </button>
        </div>

        <div class="row">
            <!-- Main Notifications Column -->
            <div class="col-lg-8 mb-4">
                <!-- Notifications Card -->
                <div class="card shadow-sm mb-4">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h6 class="m-0"><i class="ti ti-bell me-2"></i>Recent Notifications</h6>
                        <span class="badge bg-primary"><?= count($notifications) ?></span>
                    </div>
                    <div class="card-body p-0">
                        <?php if (empty($notifications)): ?>
                            <div class="text-center py-4">
                                <i class="ti ti-bell-off fs-1 text-muted"></i>
                                <p class="text-muted mb-0 mt-2">No notifications yet</p>
                            </div>
                        <?php else: ?>
                            <div class="list-group list-group-flush" style="max-height: 400px; overflow-y: auto;">
                                <?php foreach ($notifications as $notification): ?>
                                    <?php
                                    $link = getNotificationLink($notification['type'], $notification['related_type'], $notification['related_id'], $role_id);
                                    $iconClass = match($notification['type']) {
                                        'leave' => 'ti-calendar-event text-primary',
                                        'payroll' => 'ti-receipt text-success',
                                        'attendance' => 'ti-clock text-info',
                                        'performance' => 'ti-chart-bar text-warning',
                                        'task' => 'ti-list-check text-secondary',
                                        default => 'ti-bell text-primary'
                                    };
                                    $time = strtotime($notification['created_at']);
                                    $diff = time() - $time;
                                    $timeAgo = $diff < 60 ? 'Just now' : ($diff < 3600 ? floor($diff/60).'m' : ($diff < 86400 ? floor($diff/3600).'h' : date('M j', $time)));
                                    ?>
                                    <a href="<?= htmlspecialchars($link) ?>" 
                                       class="list-group-item list-group-item-action notification-item <?= $notification['is_read'] ? '' : 'bg-light' ?>"
                                       data-id="<?= (int)$notification['id'] ?>">
                                        <div class="d-flex align-items-center">
                                            <i class="ti <?= $iconClass ?> fs-4 me-3"></i>
                                            <div class="flex-grow-1">
                                                <div class="d-flex justify-content-between">
                                                    <strong class="<?= $notification['is_read'] ? '' : 'text-dark' ?>"><?= htmlspecialchars($notification['title']) ?></strong>
                                                    <small class="text-muted"><?= $timeAgo ?></small>
                                                </div>
                                                <small class="text-muted"><?= htmlspecialchars($notification['message']) ?></small>
                                            </div>
                                            <?php if (!$notification['is_read']): ?>
                                                <span class="badge bg-primary ms-2">New</span>
                                            <?php endif; ?>
                                        </div>
                                    </a>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Pending Leave Requests (for approvers) -->
                <?php if (!empty($pending_leaves)): ?>
                <div class="card shadow-sm mb-4">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h6 class="m-0"><i class="ti ti-calendar-event me-2"></i>Pending Leave Requests</h6>
                        <span class="badge bg-warning text-dark"><?= count($pending_leaves) ?> pending</span>
                    </div>
                    <div class="card-body p-0">
                        <div class="list-group list-group-flush">
                            <?php foreach ($pending_leaves as $leave): ?>
                                <div class="list-group-item d-flex justify-content-between align-items-center">
                                    <div>
                                        <strong><?= htmlspecialchars($leave['first_name'] . ' ' . $leave['last_name']) ?></strong>
                                        <br>
                                        <small class="text-muted">
                                            <?= htmlspecialchars($leave['leave_type']) ?> • 
                                            <?= date('M j', strtotime($leave['start_date'])) ?> - <?= date('M j', strtotime($leave['end_date'])) ?>
                                        </small>
                                    </div>
                                    <a href="/hrms/company/leave_management.php" class="btn btn-sm btn-outline-primary">
                                        Review
                                    </a>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
            </div>

            <!-- Sidebar Column -->
            <div class="col-lg-4">
                <!-- My Pending Tasks -->
                <?php if (!empty($pending_tasks)): ?>
                <div class="card shadow-sm mb-4">
                    <div class="card-header">
                        <h6 class="m-0"><i class="ti ti-list-check me-2"></i>My Pending Tasks</h6>
                    </div>
                    <div class="list-group list-group-flush">
                        <?php foreach ($pending_tasks as $task): ?>
                            <div class="list-group-item">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div>
                                        <strong><?= htmlspecialchars($task['title']) ?></strong>
                                        <?php if ($task['due_date']): ?>
                                            <br><small class="text-muted">Due: <?= date('M j, Y', strtotime($task['due_date'])) ?></small>
                                        <?php endif; ?>
                                    </div>
                                    <span class="badge bg-<?= $task['status'] == 'in_progress' ? 'info' : 'warning' ?>">
                                        <?= ucfirst(str_replace('_', ' ', $task['status'])) ?>
                                    </span>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>

                <!-- My Pending Leave Requests -->
                <?php if (!empty($my_pending_leaves)): ?>
                <div class="card shadow-sm mb-4">
                    <div class="card-header">
                        <h6 class="m-0"><i class="ti ti-calendar me-2"></i>My Leave Requests</h6>
                    </div>
                    <div class="list-group list-group-flush">
                        <?php foreach ($my_pending_leaves as $leave): ?>
                            <div class="list-group-item">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <strong><?= htmlspecialchars($leave['leave_type']) ?></strong>
                                        <br><small class="text-muted"><?= date('M j', strtotime($leave['start_date'])) ?> - <?= date('M j', strtotime($leave['end_date'])) ?></small>
                                    </div>
                                    <span class="badge bg-warning text-dark">Pending</span>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Recent Feedback (for managers) -->
                <?php if (!empty($recent_feedback)): ?>
                <div class="card shadow-sm mb-4">
                    <div class="card-header">
                        <h6 class="m-0"><i class="ti ti-message-circle me-2"></i>Pending Feedback</h6>
                    </div>
                    <div class="list-group list-group-flush">
                        <?php foreach ($recent_feedback as $fb): ?>
                            <div class="list-group-item">
                                <strong><?= htmlspecialchars($fb['first_name'] . ' ' . $fb['last_name']) ?></strong>
                                <br><small class="text-muted"><?= htmlspecialchars(substr($fb['message'], 0, 50)) ?>...</small>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Quick Stats -->
                <div class="card shadow-sm">
                    <div class="card-header">
                        <h6 class="m-0"><i class="ti ti-chart-dots me-2"></i>Quick Stats</h6>
                    </div>
                    <div class="card-body">
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">Unread Notifications</span>
                            <strong><?= getUnreadNotificationCount($mysqli, $user_id) ?></strong>
                        </div>
                        <?php if (!empty($pending_tasks)): ?>
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">Pending Tasks</span>
                            <strong><?= count($pending_tasks) ?></strong>
                        </div>
                        <?php endif; ?>
                        <?php if (!empty($pending_leaves)): ?>
                        <div class="d-flex justify-content-between">
                            <span class="text-muted">Leave Requests to Review</span>
                            <strong><?= count($pending_leaves) ?></strong>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once '../components/layout/footer.php'; ?>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Mark as read on click
    document.querySelectorAll('.notification-item').forEach(item => {
        item.addEventListener('click', function() {
            const notifId = this.dataset.id;
            fetch('/hrms/api/api_notifications.php?action=mark_read', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: 'notification_id=' + notifId
            });
        });
    });

    // Mark all as read
    document.getElementById('markAllReadBtn')?.addEventListener('click', function() {
        fetch('/hrms/api/api_notifications.php?action=mark_all_read', { method: 'POST' })
        .then(() => {
            document.querySelectorAll('.notification-item.bg-light').forEach(i => i.classList.remove('bg-light'));
            document.querySelectorAll('.notification-item .badge.bg-primary').forEach(b => b.remove());
            const hb = document.getElementById('notificationBadge');
            if (hb) hb.classList.add('d-none');
        });
    });
});
</script>
