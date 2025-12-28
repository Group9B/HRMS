<?php
require_once '../config/db.php';
require_once '../includes/functions.php';
$title = "HR Dashboard";

if (!isLoggedIn()) {
  redirect("/hrms/auth/login.php");
}
// This dashboard is for Human Resources (role_id = 3)
if ($_SESSION['role_id'] !== 3) {
  redirect("/hrms/pages/unauthorized.php");
}
$company_id = $_SESSION['company_id'];
$user_id = $_SESSION['user_id'];

// --- DATA FETCHING for HR Manager ---
$total_employees = query($mysqli, "SELECT COUNT(e.id) as count FROM employees e JOIN departments d ON e.department_id = d.id WHERE d.company_id = ?", [$company_id])['data'][0]['count'] ?? 0;
$pending_leaves = query($mysqli, "SELECT COUNT(l.id) as count FROM leaves l JOIN employees e ON l.employee_id = e.id JOIN departments d ON e.department_id = d.id WHERE l.status = 'pending' AND d.company_id = ?", [$company_id])['data'][0]['count'] ?? 0;
$new_candidate_applications = query($mysqli, "SELECT COUNT(a.id) as count FROM job_applications a JOIN job_postings j ON a.job_id = j.id WHERE a.status = 'pending' AND j.company_id = ?", [$company_id])['data'][0]['count'] ?? 0;
$new_hires_this_month = query($mysqli, "SELECT COUNT(e.id) as count FROM employees e JOIN departments d ON e.department_id = d.id WHERE MONTH(e.date_of_joining) = MONTH(CURDATE()) AND YEAR(e.date_of_joining) = YEAR(CURDATE()) AND d.company_id = ?", [$company_id])['data'][0]['count'] ?? 0;

// Recent Hires List
$recent_hires_result = query($mysqli, "SELECT e.first_name, e.last_name, des.name as designation_name FROM employees e JOIN departments d ON e.department_id = d.id LEFT JOIN designations des ON e.designation_id = des.id WHERE d.company_id = ? ORDER BY e.date_of_joining DESC LIMIT 5", [$company_id]);
$recent_hires = $recent_hires_result['success'] ? $recent_hires_result['data'] : [];

// Pending Leave Requests List
$pending_leaves_list_result = query($mysqli, "SELECT l.*, e.first_name, e.last_name FROM leaves l JOIN employees e ON l.employee_id = e.id JOIN departments d ON e.department_id = d.id WHERE l.status = 'pending' AND d.company_id = ? ORDER BY l.start_date ASC LIMIT 5", [$company_id]);
$pending_leaves_list = $pending_leaves_list_result['success'] ? $pending_leaves_list_result['data'] : [];

require_once '../components/layout/header.php';
$additionalScripts = ['attendance-calendar.js', 'attendance-checkin.js'];
?>

<div class="d-flex">
  <?php require_once '../components/layout/sidebar.php'; ?>
  <div class="p-3 p-md-4" style="flex: 1;">
    <div class="row" id="statCardsContainer"></div>

    <!-- Clock In/Out Card -->
    <div class="row">
      <div class="col-lg-5 mb-4">
        <div class="card shadow-sm h-100">
          <div class="card-header">
            <h6 class="m-0 font-weight-bold">Today's Work Hours</h6>
          </div>
          <div class="card-body d-flex flex-column justify-content-center">
            <div id="hrCheckInContainer"></div>
          </div>
        </div>
      </div>
      <div class="col-lg-7 mb-4">
        <div class="card shadow-sm h-100">
          <div class="card-header d-flex justify-content-between align-items-center">
            <h6 class="m-0 font-weight-bold">Pending Leave Requests</h6>
            <a href="leaves.php" class="btn btn-sm btn-outline-primary">View All</a>
          </div>
          <div class="card-body">
            <div class="list-group list-group-flush">
              <?php if (!empty($pending_leaves_list)):
                foreach ($pending_leaves_list as $leave): ?>
                  <div class="list-group-item d-flex justify-content-between align-items-center">
                    <div>
                      <strong><?= htmlspecialchars($leave['first_name'] . ' ' . $leave['last_name']) ?></strong>
                      <small class="d-block text-muted"><?= htmlspecialchars($leave['leave_type']) ?> Leave:
                        <?= date('M j', strtotime($leave['start_date'])) ?> to
                        <?= date('M j', strtotime($leave['end_date'])) ?></small>
                    </div>
                    <span class="badge text-bg-warning">Pending</span>
                  </div>
                <?php endforeach; else: ?>
                <p class="text-center text-muted p-3 mb-0">No pending leave requests.</p>
              <?php endif; ?>
            </div>
          </div>
        </div>
      </div>
      <div class="col-lg-6 mb-4">
        <div class="card shadow-sm h-100">
          <div class="card-header">
            <h6 class="m-0 font-weight-bold">Recent Hires</h6>
          </div>
          <div class="card-body">
            <div class="list-group list-group-flush">
              <?php if (!empty($recent_hires)):
                foreach ($recent_hires as $hire): ?>
                  <div class="list-group-item">
                    <strong><?= htmlspecialchars($hire['first_name'] . ' ' . $hire['last_name']) ?></strong>
                    <small class="d-block text-muted"><?= htmlspecialchars($hire['designation_name'] ?? 'N/A') ?></small>
                  </div>
                <?php endforeach; else: ?>
                <p class="text-center text-muted p-3 mb-0">No recent hires this month.</p>
              <?php endif; ?>
            </div>
          </div>
        </div>
      </div>

      <div class="col-lg-4 mb-4">
        <div class="card shadow-sm h-100">
          <div class="card-header">
            <h6 class="m-0 font-weight-bold">Quick Actions</h6>
          </div>
          <div class="card-body quick-actions">
            <div class="d-grid gap-2">
              <a href="/hrms/company/employees.php" class="btn btn-secondary"><i class="ti ti-user-plus"></i> Add
                Employee</a>
              <a href="/hrms/company/attendance.php" class="btn btn-secondary"><i class="ti ti-calendar-check"></i> Mark
                Attendance</a>
              <a href="/hrms/company/leaves.php" class="btn btn-secondary"><i class="ti ti-check"></i> Approve
                Leaves</a>
            </div>
          </div>
        </div>
      </div>
      <div class="col-lg-4 mb-4">
        <div class="card shadow-sm">
          <div class="card-header">
            <h6 class="m-0 font-weight-bold">My Attendance (Current Month)</h6>
          </div>
          <div class="card-body">
            <div id="attendanceCalendarContainer"></div>
          </div>
        </div>
      </div>
      <div class="col-lg-4 mb-4">
        <div class="card shadow-sm">
          <div class="card-header">
            <h6 class="m-0 font-weight-bold">My To-Do List</h6>
          </div>
          <div class="card-body">
            <form id="todoForm" class="d-flex mb-3">
              <input type="text" name="task" class="form-control me-2" placeholder="Add a new task..." required>
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

    // Initialize attendance check-in
    new AttendanceCheckIn({
      containerId: 'hrCheckInContainer',
      allowCheckIn: true,
      allowCheckOut: true,
      refreshInterval: 60000
    });

    // Initialize attendance calendar
    new AttendanceCalendar({
      containerId: 'attendanceCalendarContainer',
      showMonthNavigation: true,
      employeeId: null, // Will be handled by API with current_user_only
      onlyCurrentEmployee: true
    });

    // Render stat cards using the renderStatCards function
    const stats = [
      {
        label: 'Total Employees',
        value: <?= $total_employees ?>,
        color: 'primary',
        icon: 'users'
      },
      {
        label: 'Pending Leaves',
        value: <?= $pending_leaves ?>,
        color: 'info',
        icon: 'plane'
      },
      {
        label: 'New Applications',
        value: <?= $new_candidate_applications ?>,
        color: 'warning',
        icon: 'briefcase'
      },
      {
        label: 'New Hires (Month)',
        value: <?= $new_hires_this_month ?>,
        color: 'success',
        icon: 'user-plus'
      }
    ];
    renderStatCards('statCardsContainer', stats);
  });
</script>