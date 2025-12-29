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
$additionalScripts = ['attendance-calendar.js', 'attendance-checkin.js'];
?>
<div class="d-flex">
    <?php require_once '../components/layout/sidebar.php'; ?>
    <div class="p-3 p-md-4" style="flex: 1;">
        <div class="row" id="statCardsContainer"></div>

        <!-- Main Content Row -->
        <div class="row">
            <div class="col-lg-5 mb-4">
                <div class="card shadow-sm h-100">
                    <div class="card-header">
                        <h6 class="m-0 font-weight-bold">Today's Attendance</h6>
                    </div>
                    <div class="card-body">
                        <div id="attendanceCheckInContainer"></div>
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

        <div class="row">
            <div class="col-lg-4 mb-4">
                <div class="card shadow-sm h-100">
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
        initializeTodoList('#todoForm', '#todoList');
        loadDashboardStats();

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
            onlyCurrentEmployee: true
        });
    });

    function loadDashboardStats() {
        fetch('/hrms/api/api_emp.php?action=get_stats')
            .then(res => res.json())
            .then(result => {
                if (result.success) {
                    const stats = result.data;
                    const statCards = [
                        {
                            label: 'Leave Balance',
                            value: `${stats.leave_balance} Days`,
                            color: 'primary',
                            icon: 'calendar'
                        },
                        {
                            label: 'Pending Leaves',
                            value: stats.pending_leaves,
                            color: 'warning',
                            icon: 'clock'
                        },
                        {
                            label: 'Pending Tasks',
                            value: stats.pending_tasks,
                            color: 'info',
                            icon: 'checklist'
                        },
                        {
                            label: 'Completed Tasks',
                            value: stats.completed_tasks,
                            color: 'success',
                            icon: 'circle-check'
                        }
                    ];

                    renderStatCards('statCardsContainer', statCards);
                }
            });
    }
</script>