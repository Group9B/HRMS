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

// Initial data for permissions/context only
$employee_info = query($mysqli, "SELECT id, department_id FROM employees WHERE user_id = ?", [$user_id]);
$employee_id = $employee_info['success'] && !empty($employee_info['data']) ? $employee_info['data'][0]['id'] : 0;
$department_id = $employee_info['success'] && !empty($employee_info['data']) ? $employee_info['data'][0]['department_id'] : 0;

// Roles allowed to see pending leaves/feedback
$is_approver = in_array($role_id, [2, 3, 6]);

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
                        <span class="badge bg-primary" id="notifCount">0</span>
                    </div>
                    <div class="card-body p-0">
                        <div id="notificationsContainer" style="max-height: 400px; overflow-y: auto;">
                            <!-- Skeletons will be injected here -->
                        </div>
                    </div>
                </div>

                <!-- Pending Leave Requests (for approvers) -->
                <?php if ($is_approver): ?>
                    <div class="card shadow-sm mb-4">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h6 class="m-0"><i class="ti ti-calendar-event me-2"></i>Pending Leave Requests</h6>
                            <span class="badge bg-warning text-dark" id="pendingLeavesCount">0 pending</span>
                        </div>
                        <div class="card-body p-0">
                            <div id="pendingLeavesContainer">
                                <!-- Skeletons -->
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Sidebar Column -->
            <div class="col-lg-4">
                <!-- My Pending Tasks -->
                <?php if ($employee_id): ?>
                    <div class="card shadow-sm mb-4">
                        <div class="card-header">
                            <h6 class="m-0"><i class="ti ti-list-check me-2"></i>My Pending Tasks</h6>
                        </div>
                        <div class="list-group list-group-flush" id="myTasksContainer">
                            <!-- Skeletons -->
                        </div>
                    </div>
                <?php endif; ?>

                <!-- My Pending Leave Requests -->
                <?php if ($employee_id): ?>
                    <div class="card shadow-sm mb-4">
                        <div class="card-header">
                            <h6 class="m-0"><i class="ti ti-calendar me-2"></i>My Leave Requests</h6>
                        </div>
                        <div class="list-group list-group-flush" id="myLeavesContainer">
                            <!-- Skeletons -->
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Recent Feedback (for managers) -->
                <?php if ($is_approver): ?>
                    <div class="card shadow-sm mb-4">
                        <div class="card-header">
                            <h6 class="m-0"><i class="ti ti-message-circle me-2"></i>Pending Feedback</h6>
                        </div>
                        <div class="list-group list-group-flush" id="feedbackContainer">
                            <!-- Skeletons -->
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Quick Stats -->
                <div class="card shadow-sm">
                    <div class="card-header">
                        <h6 class="m-0"><i class="ti ti-chart-dots me-2"></i>Quick Stats</h6>
                    </div>
                    <div class="card-body" id="quickStatsContainer">
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">Unread Notifications</span>
                            <strong id="statUnread">0</strong>
                        </div>
                        <?php if ($employee_id): ?>
                            <div class="d-flex justify-content-between mb-2">
                                <span class="text-muted">Pending Tasks</span>
                                <strong id="statTasks">0</strong>
                            </div>
                        <?php endif; ?>
                        <?php if ($is_approver): ?>
                            <div class="d-flex justify-content-between">
                                <span class="text-muted">Leave Requests to Review</span>
                                <strong id="statPendingLeaves">0</strong>
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
    document.addEventListener('DOMContentLoaded', function () {
        const roleId = <?= $role_id ?>;
        const employeeId = <?= $employee_id ?: 0 ?>;
        const isApprover = <?= $is_approver ? 'true' : 'false' ?>;

        // 1. Fetch Notifications
        UIController.fetch({
            container: '#notificationsContainer',
            blueprint: 'notification',
            count: 5,
            url: '/hrms/api/api_notifications.php?action=get_notifications&limit=50',
            smoothSwap: true,
            onRender: (result, container) => {
                if (!result.success || !result.data || result.data.length === 0) {
                    container.innerHTML = `
                    <div class="text-center py-4">
                        <i class="ti ti-bell-off fs-1 text-muted"></i>
                        <p class="text-muted mb-0 mt-2">No notifications yet</p>
                    </div>`;
                    document.getElementById('notifCount').textContent = '0';
                    document.getElementById('statUnread').textContent = '0';
                    return;
                }

                document.getElementById('notifCount').textContent = result.data.length;
                document.getElementById('statUnread').textContent = result.unread_count || 0;

                let html = '<div class="list-group list-group-flush">';
                result.data.forEach(n => {
                    const iconClass = {
                        'leave': 'ti-calendar-event text-primary',
                        'payroll': 'ti-receipt text-success',
                        'attendance': 'ti-clock text-info',
                        'performance': 'ti-chart-bar text-warning',
                        'task': 'ti-list-check text-secondary'
                    }[n.type] || 'ti-bell text-primary';

                    html += `
                    <a href="${n.link || '#'}" 
                       class="list-group-item list-group-item-action notification-item ${n.is_read ? '' : 'bg-light'}"
                       data-id="${n.id}">
                        <div class="d-flex align-items-center">
                            <i class="ti ${iconClass} fs-4 me-3"></i>
                            <div class="flex-grow-1">
                                <div class="d-flex justify-content-between">
                                    <strong class="${n.is_read ? '' : 'text-dark'}">${n.title}</strong>
                                    <small class="text-muted">${n.time_ago}</small>
                                </div>
                                <small class="text-muted">${n.message}</small>
                            </div>
                            ${!n.is_read ? '<span class="badge bg-primary ms-2">New</span>' : ''}
                        </div>
                    </a>`;
                });
                html += '</div>';
                container.innerHTML = html;

                // Re-attach listeners
                attachNotificationListeners();
            }
        });

        // 2. Fetch Pending Leave Requests (Approver)
        if (isApprover) {
            UIController.fetch({
                container: '#pendingLeavesContainer',
                blueprint: 'list-item', // Using list-item as generic simpler row
                count: 3,
                url: '/hrms/api/api_leaves.php?action=get_pending_requests',
                smoothSwap: true,
                onRender: (result, container) => {
                    if (!result.success || !result.data || result.data.length === 0) {
                        // Hide card or show empty
                        document.getElementById('pendingLeavesCount').textContent = '0 pending';
                        document.getElementById('statPendingLeaves').textContent = '0';
                        container.closest('.card').style.display = 'none'; // Auto-hide if empty
                        return;
                    }

                    document.getElementById('pendingLeavesCount').textContent = `${result.data.length} pending`;
                    document.getElementById('statPendingLeaves').textContent = result.data.length;

                    let html = '<div class="list-group list-group-flush">';
                    result.data.slice(0, 10).forEach(l => {
                        html += `
                        <div class="list-group-item d-flex justify-content-between align-items-center">
                            <div>
                                <strong>${l.first_name} ${l.last_name}</strong>
                                <br>
                                <small class="text-muted">
                                    ${l.leave_type} • 
                                    ${new Date(l.start_date).toLocaleDateString()} - ${new Date(l.end_date).toLocaleDateString()}
                                </small>
                            </div>
                            <a href="/hrms/company/leave_management.php" class="btn btn-sm btn-outline-primary">
                                Review
                            </a>
                        </div>`;
                    });
                    html += '</div>';
                    container.innerHTML = html;
                }
            });

            // 5. Fetch Feedback (Approver)
            UIController.fetch({
                container: '#feedbackContainer',
                blueprint: 'list-item',
                count: 2,
                url: '/hrms/api/api_notifications.php?action=get_recent_feedback',
                smoothSwap: true,
                onRender: (result, container) => {
                    if (!result.success || !result.data || result.data.length === 0) {
                        container.closest('.card').style.display = 'none';
                        return;
                    }

                    let html = '';
                    result.data.forEach(fb => {
                        html += `
                        <div class="list-group-item">
                            <strong>${fb.first_name} ${fb.last_name}</strong>
                            <br><small class="text-muted">${fb.message.substring(0, 50)}...</small>
                        </div>`;
                    });
                    container.innerHTML = html;
                }
            });
        }

        // 3. Fetch My Pending Tasks
        if (employeeId) {
            UIController.fetch({
                container: '#myTasksContainer',
                blueprint: 'list-item',
                count: 3,
                url: '/hrms/api/api_tasks.php?action=get_assigned_tasks',
                smoothSwap: true,
                onRender: (result, container) => {
                    // Filter for pending/in_progress
                    const tasks = (result.data || []).filter(t => ['pending', 'in_progress'].includes(t.status)).slice(0, 10);

                    document.getElementById('statTasks').textContent = tasks.length;

                    if (tasks.length === 0) {
                        container.closest('.card').style.display = 'none';
                        return;
                    }

                    let html = '';
                    tasks.forEach(task => {
                        const badgeClass = task.status === 'in_progress' ? 'info' : 'warning';
                        html += `
                        <div class="list-group-item">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <strong>${task.title}</strong>
                                    ${task.due_date ? `<br><small class="text-muted">Due: ${new Date(task.due_date).toLocaleDateString()}</small>` : ''}
                                </div>
                                <span class="badge bg-${badgeClass}">
                                    ${task.status.replace('_', ' ').charAt(0).toUpperCase() + task.status.replace('_', ' ').slice(1)}
                                </span>
                            </div>
                        </div>`;
                    });
                    container.innerHTML = html;
                }
            });
        }

        // 4. Fetch My Leave Requests
        if (employeeId) {
            UIController.fetch({
                container: '#myLeavesContainer',
                blueprint: 'list-item',
                count: 2,
                url: '/hrms/api/api_leaves.php?action=get_my_leaves',
                smoothSwap: true,
                onRender: (result, container) => {
                    const leaves = (result.data || []).filter(l => l.status === 'pending').slice(0, 5);

                    if (leaves.length === 0) {
                        container.closest('.card').style.display = 'none';
                        return;
                    }

                    let html = '';
                    leaves.forEach(l => {
                        html += `
                        <div class="list-group-item">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <strong>${l.leave_type}</strong>
                                    <br><small class="text-muted">${new Date(l.start_date).toLocaleDateString('en-US', { month: 'short', day: 'numeric' })} - ${new Date(l.end_date).toLocaleDateString('en-US', { month: 'short', day: 'numeric' })}</small>
                                </div>
                                <span class="badge bg-warning text-dark">Pending</span>
                            </div>
                        </div>`;
                    });
                    container.innerHTML = html;
                }
            });
        }

        function attachNotificationListeners() {
            // Mark as read on click
            document.querySelectorAll('.notification-item').forEach(item => {
                item.addEventListener('click', function () {
                    const notifId = this.dataset.id;
                    fetch('/hrms/api/api_notifications.php?action=mark_read', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                        body: 'notification_id=' + notifId
                    });
                });
            });
        }

        // Mark all as read
        document.getElementById('markAllReadBtn')?.addEventListener('click', function () {
            fetch('/hrms/api/api_notifications.php?action=mark_all_read', { method: 'POST' })
                .then(() => {
                    document.querySelectorAll('.notification-item.bg-light').forEach(i => i.classList.remove('bg-light'));
                    document.querySelectorAll('.notification-item .badge.bg-primary').forEach(b => b.remove());
                    // Update stats
                    document.getElementById('statUnread').textContent = '0';
                    // Also update header badge if exists
                    const hb = document.getElementById('notificationBadge');
                    if (hb) hb.classList.add('d-none');
                });
        });
    });
</script>