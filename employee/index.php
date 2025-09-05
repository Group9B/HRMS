<?php
require_once '../config/db.php';
require_once '../includes/functions.php';
$title = "My Dashboard";

// --- SECURITY & SESSION ---
if (!isLoggedIn()) { redirect("/hrms/auth/login.php"); }
// This dashboard is for Employees (role_id = 4)
if ($_SESSION['role_id'] !== 4) { redirect("/hrms/unauthorized.php"); }
$user_id = $_SESSION['user_id'];

// Find the employee_id associated with the current user_id
$employee_profile = query($mysqli, "SELECT id, first_name FROM employees WHERE user_id = ?", [$user_id]);
if (!$employee_profile['success'] || empty($employee_profile['data'])) {
    // Optional: Redirect to profile page if employee record doesn't exist yet
    // redirect("/hrms/admin/profile.php");
    die("Employee profile not found for this user account.");
}
$employee_id = $employee_profile['data'][0]['id'];
$employee_name = $employee_profile['data'][0]['first_name'];

// --- DATA FETCHING for Employee ---
$days_present = query($mysqli, "SELECT COUNT(id) as count FROM attendance WHERE employee_id = ? AND status = 'present' AND MONTH(date) = MONTH(CURDATE()) AND YEAR(date) = YEAR(CURDATE())", [$employee_id])['data'][0]['count'] ?? 0;
$approved_leaves_this_year = query($mysqli, "SELECT COUNT(id) as count FROM leaves WHERE employee_id = ? AND status = 'approved' AND YEAR(start_date) = YEAR(CURDATE())", [$employee_id])['data'][0]['count'] ?? 0;
$annual_leave_allowance = 20; // Assuming a standard allowance
$leaves_remaining = $annual_leave_allowance - $approved_leaves_this_year;
$pending_tasks = query($mysqli, "SELECT COUNT(id) as count FROM tasks WHERE employee_id = ? AND status = 'pending'", [$employee_id])['data'][0]['count'] ?? 0;
$open_tickets = query($mysqli, "SELECT COUNT(id) as count FROM support_tickets WHERE user_id = ? AND status = 'open'", [$user_id])['data'][0]['count'] ?? 0;

// Recent Leave Requests List
$recent_leaves_result = query($mysqli, "SELECT * FROM leaves WHERE employee_id = ? ORDER BY created_at DESC LIMIT 5", [$employee_id]);
$recent_leaves = $recent_leaves_result['success'] ? $recent_leaves_result['data'] : [];

require_once '../components/layout/header.php';
?>
<style>
    .todo-list { list-style: none; padding: 0; }
    .todo-list li { display: flex; align-items: center; padding: 0.75rem; border-bottom: 1px solid var(--bs-border-color); }
    .todo-list li:last-child { border-bottom: none; }
    .todo-list .form-check-input { margin-right: 1rem; }
    .todo-list .task-text.completed { text-decoration: line-through; color: var(--bs-secondary-color); }
    .todo-list .delete-btn { margin-left: auto; }
</style>

<div class="d-flex">
    <?php require_once '../components/layout/sidebar.php'; ?>
    <div class="p-3 p-md-4" style="flex: 1;">
        <h2 class="h3 mb-4 text-gray-800">Welcome back, <?= htmlspecialchars($employee_name) ?>!</h2>

        <!-- Stat Cards Row -->
        <div class="row">
            <div class="col-xl-3 col-md-6 mb-4"><div class="card stat-card shadow-sm"><div class="card-body"><div class="icon-circle bg-success"><i class="fas fa-calendar-check"></i></div><div><div class="text-xs font-weight-bold text-success text-uppercase mb-1">Days Present (Month)</div><div class="h5 mb-0 font-weight-bold text-gray-800"><?= $days_present ?></div></div></div></div></div>
            <div class="col-xl-3 col-md-6 mb-4"><div class="card stat-card shadow-sm"><div class="card-body"><div class="icon-circle bg-info"><i class="fas fa-plane-departure"></i></div><div><div class="text-xs font-weight-bold text-info text-uppercase mb-1">Leaves Remaining</div><div class="h5 mb-0 font-weight-bold text-gray-800"><?= $leaves_remaining ?></div></div></div></div></div>
            <div class="col-xl-3 col-md-6 mb-4"><div class="card stat-card shadow-sm"><div class="card-body"><div class="icon-circle bg-primary"><i class="fas fa-tasks"></i></div><div><div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Pending Tasks</div><div class="h5 mb-0 font-weight-bold text-gray-800"><?= $pending_tasks ?></div></div></div></div></div>
            <div class="col-xl-3 col-md-6 mb-4"><div class="card stat-card shadow-sm"><div class="card-body"><div class="icon-circle bg-warning"><i class="fas fa-life-ring"></i></div><div><div class="text-xs font-weight-bold text-warning text-uppercase mb-1">My Open Tickets</div><div class="h5 mb-0 font-weight-bold text-gray-800"><?= $open_tickets ?></div></div></div></div></div>
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
                            <?php if (!empty($recent_leaves)): foreach ($recent_leaves as $leave): ?>
                                <div class="list-group-item d-flex justify-content-between align-items-center">
                                    <div>
                                        <strong><?= htmlspecialchars($leave['leave_type']) ?> Leave</strong>
                                        <small class="d-block text-muted">From <?= date('M j, Y', strtotime($leave['start_date'])) ?> to <?= date('M j, Y', strtotime($leave['end_date'])) ?></small>
                                    </div>
                                    <span class="badge text-bg-<?php
                                        $status = strtolower($leave['status']);
                                        echo match($status) {
                                            'approved' => 'success',
                                            'rejected' => 'danger',
                                            default => 'warning'
                                        };
                                    ?>"><?= ucfirst($leave['status']) ?></span>
                                </div>
                            <?php endforeach; else: ?>
                                <p class="text-center text-muted p-3 mb-0">You haven't applied for any leave yet.</p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Today's Attendance -->
            <div class="col-lg-4 mb-4">
                <div class="card shadow-sm h-100">
                    <div class="card-header"><h6 class="m-0 font-weight-bold">Today's Attendance</h6></div>
                    <div class="card-body d-flex flex-column justify-content-center align-items-center text-center">
                        <div id="attendance-status" class="mb-3"><div class="spinner-border spinner-border-sm"></div></div>
                        <div class="d-flex gap-2">
                            <button class="btn btn-success" id="checkInBtn"><i class="fas fa-sign-in-alt me-2"></i>Check In</button>
                            <button class="btn btn-danger" id="checkOutBtn"><i class="fas fa-sign-out-alt me-2"></i>Check Out</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- To-Do List -->
        <div class="row">
            <div class="col-12">
                <div class="card shadow-sm">
                    <div class="card-header"><h6 class="m-0 font-weight-bold">My Personal To-Do List</h6></div>
                    <div class="card-body">
                        <form id="todoForm" class="d-flex mb-3">
                            <input type="text" name="task" class="form-control me-2" placeholder="Add a new personal task..." required>
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
$(function() {
    initializeTodoList('#todoForm', '#todoList', '/hrms/api/todo.php');
    loadAttendanceStatus();

    $('#checkInBtn').on('click', () => handleAttendanceAction('check_in'));
    $('#checkOutBtn').on('click', () => handleAttendanceAction('check_out'));
});

// Helper function for badge colors
function getStatusClass(status) {
    if (status === 'approved') return 'success';
    if (status === 'rejected') return 'danger';
    return 'warning'; // pending
}

function loadAttendanceStatus() {
    fetch('/hrms/api/api_employee_attendance.php?action=get_today_status')
    .then(res => res.json())
    .then(result => {
        if(result.success) {
            const data = result.data;
            const statusDiv = $('#attendance-status');
            const checkInBtn = $('#checkInBtn');
            const checkOutBtn = $('#checkOutBtn');

            if (data && data.check_in && data.check_out) {
                statusDiv.html(`Checked In: <strong>${formatTime(data.check_in)}</strong><br>Checked Out: <strong>${formatTime(data.check_out)}</strong>`);
                checkInBtn.prop('disabled', true);
                checkOutBtn.prop('disabled', true);
            } else if (data && data.check_in) {
                statusDiv.html(`Checked In: <strong>${formatTime(data.check_in)}</strong><br>Status: <strong>Present</strong>`);
                checkInBtn.prop('disabled', true);
                checkOutBtn.prop('disabled', false);
            } else {
                statusDiv.html(`You have not checked in today.`);
                checkInBtn.prop('disabled', false);
                checkOutBtn.prop('disabled', true);
            }
        } else {
            showToast(result.message || 'Could not load attendance status.', 'error');
        }
    });
}

function handleAttendanceAction(action) {
    const btn = $(`#${action === 'check_in' ? 'checkInBtn' : 'checkOutBtn'}`);
    btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span>');

    fetch(`/hrms/api/api_employee_attendance.php?action=${action}`, { method: 'POST' })
    .then(res => res.json())
    .then(result => {
        if (result.success) {
            showToast(result.message, 'success');
            loadAttendanceStatus();
        } else {
            showToast(result.message, 'error');
            btn.prop('disabled', false).html(action === 'check_in' ? '<i class="fas fa-sign-in-alt me-2"></i>Check In' : '<i class="fas fa-sign-out-alt me-2"></i>Check Out');
        }
    });
}

function formatTime(timeString) {
    if (!timeString) return '';
    const [hour, minute] = timeString.split(':');
    const date = new Date();
    date.setHours(hour, minute);
    return date.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
}
</script>



