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
                <div class="card stat-card shadow-sm h-100">
                    <div class="card-body">
                        <div class="icon-circle bg-primary"><i class="ti ti-calendar"></i></div>
                        <div>
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Leave Balance</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800" id="stat-leave-balance">--</div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card stat-card shadow-sm h-100">
                    <div class="card-body">
                        <div class="icon-circle bg-warning"><i class="ti ti-clock"></i></div>
                        <div>
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Pending Leaves</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800" id="stat-pending-leaves">--</div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card stat-card shadow-sm h-100">
                    <div class="card-body">
                        <div class="icon-circle bg-info"><i class="ti ti-checklist"></i></div>
                        <div>
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Pending Tasks</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800" id="stat-pending-tasks">--</div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card stat-card shadow-sm h-100">
                    <div class="card-body">
                        <div class="icon-circle bg-success"><i class="ti ti-circle-check"></i></div>
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
                            <button class="btn btn-success" id="checkInBtn"><i class="ti ti-login-2 me-2"></i>Check
                                In</button>
                            <button class="btn btn-danger" id="checkOutBtn"><i class="ti ti-logout-2 me-2"></i>Check
                                Out</button>
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
                        <form id="todoForm" class="d-flex mb-3"><input type="text" name="task" class="form-control me-2"
                                placeholder="Add a new task..." required><button type="submit"
                                class="btn btn-primary">Add</button></form>
                        <ul class="list-group" id="todoList"></ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php require_once '../components/layout/footer.php'; ?>
<script>
    $(function () {
        initializeTodoList('#todoForm', '#todoList');
        loadDashboardStats();
        loadAttendanceStatus();

        $('#checkInBtn').on('click', () => handleAttendanceAction('check_in'));
        $('#checkOutBtn').on('click', () => handleAttendanceAction('check_out'));
    });

    function loadDashboardStats() {
        fetch('/hrms/api/api_emp.php?action=get_stats')
            .then(res => res.json())
            .then(result => {
                if (result.success) {
                    const stats = result.data;
                    $('#stat-leave-balance').text(`${stats.leave_balance} Days`);
                    $('#stat-pending-leaves').text(stats.pending_leaves);
                    $('#stat-pending-tasks').text(stats.pending_tasks);
                    $('#stat-completed-tasks').text(stats.completed_tasks);
                }
            });
    }

    function loadAttendanceStatus() {
        fetch('/hrms/api/api_employee_attendance.php?action=get_today_status')
            .then(res => res.json())
            .then(result => {
                const statusDiv = $('#attendance-status');
                const checkInBtn = $('#checkInBtn');
                const checkOutBtn = $('#checkOutBtn');

                // Always reset buttons to default state first
                checkInBtn.prop('disabled', false).html('<i class="ti ti-login-2 me-2"></i>Check In');
                checkOutBtn.prop('disabled', false).html('<i class="ti ti-logout-2 me-2"></i>Check Out');

                if (result.success) {
                    const data = result.data;
                    let statusHTML = '';

                    if (data && data.check_in) {
                        const checkInStatus = data.check_in_status ? `<span class="badge bg-warning text-dark ms-2">${data.check_in_status}</span>` : '';
                        statusHTML += `Checked In: <strong>${formatTime(data.check_in)}</strong>${checkInStatus}<br>`;

                        if (data.check_out) {
                            const checkOutStatus = data.check_out_status ? `<span class="badge bg-danger ms-2">${data.check_out_status}</span>` : '';
                            const durationStatus = data.duration_status ? `<br><span class="badge bg-danger mt-1">${data.duration_status}</span>` : '';
                            statusHTML += `Checked Out: <strong>${formatTime(data.check_out)}</strong>${checkOutStatus}${durationStatus}`;
                            checkInBtn.prop('disabled', true);
                            checkOutBtn.prop('disabled', true);
                        } else {
                            statusHTML += `Status: <strong>Present</strong>`;
                            checkInBtn.prop('disabled', true);
                            checkOutBtn.prop('disabled', false);
                        }
                    } else {
                        statusHTML = `You have not checked in today.`;
                        checkInBtn.prop('disabled', false);
                        checkOutBtn.prop('disabled', true);
                    }
                    statusDiv.html(statusHTML);
                } else {
                    statusDiv.html('<span class="text-danger">Could not load attendance status.</span>');
                    checkInBtn.prop('disabled', true);
                    checkOutBtn.prop('disabled', true);
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
                    loadAttendanceStatus(); // Reload status to reset button on error
                }
            })
            .catch(error => {
                showToast('A network error occurred. Please try again.', 'error');
                loadAttendanceStatus();
            });
    }

    function formatTime(timeString) {
        if (!timeString) return '';
        const [hour, minute] = timeString.split(':');
        const date = new Date();
        date.setHours(hour, minute);
        return date.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit', hour12: true });
    }
</script>