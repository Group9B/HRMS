<?php
require_once '../config/db.php';
require_once '../includes/functions.php';
$title = "My Dashboard";

// --- SECURITY & SESSION ---
if (!isLoggedIn()) {
    redirect("/hrms/auth/login.php");
}
// This dashboard is for Employees (role_id = 4)
if ($_SESSION['role_id'] !== 4) {
    redirect("/hrms/unauthorized.php");
}
$user_id = $_SESSION['user_id'];

// Find the employee_id associated with the current user_id
$employee_profile = query($mysqli, "SELECT id, first_name FROM employees WHERE user_id = ?", [$user_id]);
if (!$employee_profile['success'] || empty($employee_profile['data'])) {
    // Optional: Redirect to profile page if employee record doesn't exist yet
    // redirect("/hrms/admin/profile.php"); 
    redirect("/hrms/unauthorized.php");
}
$employee_id = $employee_profile['data'][0]['id'];
$employee_name = $employee_profile['data'][0]['first_name'];

// --- DATA FETCHING for Employee ---
$days_present = query($mysqli, "SELECT COUNT(id) as count FROM attendance WHERE employee_id = ? AND status = 'present' AND MONTH(date) = MONTH(CURDATE()) AND YEAR(date) = YEAR(CURDATE())", [$employee_id])['data'][0]['count'] ?? 0;
$approved_leaves_this_year = query($mysqli, "SELECT COUNT(id) as count FROM leaves WHERE employee_id = ? AND status = 'approved' AND YEAR(start_date) = YEAR(CURDATE())", [$employee_id])['data'][0]['count'] ?? 0;
$annual_leave_allowance = 20;
$leaves_remaining = $annual_leave_allowance - $approved_leaves_this_year;
$pending_tasks = query($mysqli, "SELECT COUNT(id) as count FROM tasks WHERE employee_id = ? AND status = 'pending'", [$employee_id])['data'][0]['count'] ?? 0;
$open_tickets = query($mysqli, "SELECT COUNT(id) as count FROM support_tickets WHERE user_id = ? AND status = 'open'", [$user_id])['data'][0]['count'] ?? 0;

// Recent Leave Requests List
$recent_leaves_result = query($mysqli, "SELECT * FROM leaves WHERE employee_id = ? ORDER BY created_at DESC LIMIT 5", [$employee_id]);
$recent_leaves = $recent_leaves_result['success'] ? $recent_leaves_result['data'] : [];

require_once '../components/layout/header.php';
?>
<div class="d-flex">
    <?php require_once '../components/layout/sidebar.php'; ?>
    <div class="p-3 p-md-4" style="flex: 1;">
        <h2 class="h3 mb-4 text-gray-800">Welcome back, <?= htmlspecialchars($employee_name) ?>!</h2>

        <!-- Stat Cards Row -->
        <div class="row">
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card stat-card shadow-sm">
                    <div class="card-body">
                        <div class="icon-circle bg-success"><i class="fas fa-calendar-check"></i></div>
                        <div>
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Days Present (Month)
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?= $days_present ?></div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card stat-card shadow-sm">
                    <div class="card-body">
                        <div class="icon-circle bg-info"><i class="fas fa-plane-departure"></i></div>
                        <div>
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Leaves Remaining</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?= $leaves_remaining ?></div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card stat-card shadow-sm">
                    <div class="card-body">
                        <div class="icon-circle bg-primary"><i class="fas fa-tasks"></i></div>
                        <div>
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Pending Tasks</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?= $pending_tasks ?></div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card stat-card shadow-sm">
                    <div class="card-body">
                        <div class="icon-circle bg-warning"><i class="fas fa-life-ring"></i></div>
                        <div>
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">My Open Tickets</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?= $open_tickets ?></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Content Row -->
        <div class="row">
            <!-- Recent Leave Requests -->
            <div class="col-lg-8 mb-4">
                <div class="card shadow-sm h-100">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h6 class="m-0 font-weight-bold">My Recent Leave Requests</h6>
                        <a href="leaves.php" class="btn btn-sm btn-outline-primary">View All</a>
                    </div>
                    <div class="card-body">
                        <div class="list-group list-group-flush">
                            <?php if (!empty($recent_leaves)):
                                foreach ($recent_leaves as $leave): ?>
                                    <div class="list-group-item d-flex justify-content-between align-items-center">
                                        <div>
                                            <strong><?= htmlspecialchars($leave['leave_type']) ?> Leave</strong>
                                            <small class="d-block text-muted">From
                                                <?= date('M j, Y', strtotime($leave['start_date'])) ?> to
                                                <?= date('M j, Y', strtotime($leave['end_date'])) ?></small>
                                        </div>
                                        <span
                                            class="badge text-bg-<?= $leave['status'] === 'approved' ? 'success' : 'danger'; ?>"><?= ucfirst($leave['status']) ?></span>
                                    </div>
                                <?php endforeach; else: ?>
                                <p class="text-center text-muted p-3 mb-0">You haven't applied for any leave yet.</p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="col-lg-4 mb-4">
                <div class="card shadow-sm h-100">
                    <div class="card-header">
                        <h6 class="m-0 font-weight-bold">Quick Actions</h6>
                    </div>
                    <div class="card-body quick-actions">
                        <div class="d-grid gap-2">
                            <a href="/hrms/company/leaves.php" class="btn btn-primary"><i class="fas fa-plane"></i>
                                Apply for
                                Leave</a>
                            <a href="profile.php" class="btn btn-info"><i class="fas fa-user-edit"></i> Update My
                                Profile</a>
                            <a href="support.php" class="btn btn-success"><i class="fas fa-life-ring"></i> Open a
                                Support Ticket</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- To-Do List -->
        <div class="row">
            <div class="col-12">
                <div class="card shadow-sm">
                    <div class="card-header">
                        <h6 class="m-0 font-weight-bold">My Personal To-Do List</h6>
                    </div>
                    <div class="card-body">
                        <form id="todoForm" class="d-flex mb-3">
                            <input type="text" name="task" class="form-control me-2"
                                placeholder="Add a new personal task..." required>
                            <button type="submit" class="btn btn-primary">Add</button>
                        </form>
                        <ul class="todo-list" id="todoList"></ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php require_once '../components/layout/footer.php'; ?>

<script>
    $(function () {
        // Initialize the modular To-Do list
        initializeTodoList('#todoForm', '#todoList');
    });

    // Helper function for badge colors
    function getStatusClass(status) {
        if (status === 'approved') return 'success';
        if (status === 'rejected') return 'danger';
        return 'warning'; // pending
    }
</script>