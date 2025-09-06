<?php
require_once '../config/db.php';
require_once '../includes/functions.php';
$title = "My Dashboard";

if (!isLoggedIn() || $_SESSION['role_id'] !== 4) {
    redirect("/hrms/auth/login.php");
}
$user_id = $_SESSION['user_id'];

// Fetch employee's first name for the welcome message
$employee_name_result = query($mysqli, "SELECT first_name FROM employees WHERE user_id = ?", [$user_id]);
$employee_name = $employee_name_result['data'][0]['first_name'] ?? 'Employee';

require_once '../components/layout/header.php';
?>
<div class="d-flex">
    <?php require_once '../components/layout/sidebar.php'; ?>
    <div class="p-3 p-md-4" style="flex: 1;">
        <h2 class="h3 mb-4">Welcome, <?= htmlspecialchars($employee_name) ?>!</h2>

        <!-- Stat Cards Row -->
        <div class="row">
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card stat-card shadow-sm">
                    <div class="card-body">
                        <div class="icon-circle bg-primary"><i class="fas fa-calendar-alt"></i></div>
                        <div>
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Leave Balance</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800" id="stat-leave-balance">--</div>
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
                            <div class="h5 mb-0 font-weight-bold text-gray-800" id="stat-pending-leaves">--</div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card stat-card shadow-sm">
                    <div class="card-body">
                        <div class="icon-circle bg-info"><i class="fas fa-tasks"></i></div>
                        <div>
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Total Tasks</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800" id="stat-total-tasks">--</div>
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
                            <div class="h5 mb-0 font-weight-bold text-gray-800" id="stat-completed-tasks">--</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Content Row -->
        <div class="row">
            <div class="col-lg-5 mb-4">
                <div class="card shadow-sm h-100">
                    <div class="card-header">
                        <h6 class="m-0 font-weight-bold">Today's Attendance</h6>
                    </div>
                    <div class="card-body d-flex flex-column justify-content-center align-items-center text-center">
                        <div id="attendance-status" class="mb-3">
                            <div class="spinner-border spinner-border-sm"></div>
                        </div>
                        <div class="d-flex gap-2">
                            <button class="btn btn-success" id="checkInBtn"><i class="fas fa-sign-in-alt me-2"></i>Check
                                In</button>
                            <button class="btn btn-danger" id="checkOutBtn"><i
                                    class="fas fa-sign-out-alt me-2"></i>Check Out</button>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-7 mb-4">
                <div class="card shadow-sm h-100">
                    <div class="card-header">
                        <h6 class="m-0 font-weight-bold">My Personal To-Do List</h6>
                    </div>
                    <div class="card-body">
                        <div id="todo-list-container"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php require_once '../components/layout/footer.php'; ?>
<script>
    $(function () {
        initializeTodoList('#todo-list-container', '/hrms/api/todo.php');
        loadDashboardStats();
        loadAttendanceStatus();

        $('#checkInBtn').on('click', () => handleAttendanceAction('check_in'));
        $('#checkOutBtn').on('click', () => handleAttendanceAction('check_out'));
    });

    function loadDashboardStats() {
        fetch('/hrms/api/api_employee_dashboard.php?action=get_stats')
            .then(res => res.json())
            .then(result => {
                if (result.success) {
                    const stats = result.data;
                    $('#stat-leave-balance').text(`${stats.leave_balance} Days`);
                    $('#stat-pending-leaves').text(stats.pending_leaves);
                    $('#stat-total-tasks').text(stats.total_tasks);
                    $('#stat-completed-tasks').text(stats.completed_tasks);
                } else {
                    console.error("Failed to load dashboard stats.");
                }
            });
    }

    function loadAttendanceStatus() {
        fetch('/hrms/api/api_employee_attendance.php?action=get_today_status')
            .then(res => res.json())
            .then(result => {
                if (result.success) {
                    const data = result.data;
                    const statusDiv = $('#attendance-status');
                    const checkInBtn = $('#checkInBtn');
                    const checkOutBtn = $('#checkOutBtn');

                    // --- FIX: Always reset buttons to default state first ---
                    checkInBtn.prop('disabled', false).html('<i class="fas fa-sign-in-alt me-2"></i>Check In');
                    checkOutBtn.prop('disabled', false).html('<i class="fas fa-sign-out-alt me-2"></i>Check Out');

                    // Now, apply the logic based on current status
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
                    // If there's an error, we still reload the status to reset the button
                    loadAttendanceStatus();
                }
            })
            .catch(error => {
                showToast('A network error occurred. Please try again.', 'error');
                loadAttendanceStatus(); // Also reload on network error
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