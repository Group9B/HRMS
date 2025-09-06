<?php
require_once '../config/db.php';
require_once '../includes/functions.php';
$title = "Company Dashboard";

// --- SECURITY & SESSION ---
if (!isLoggedIn()) {
    redirect("/hrms/auth/login.php");
}
// This page is for Company Admins (role_id = 2)
if ($_SESSION['role_id'] !== 2) {
    redirect("/hrms/pages/unauthorized.php");
}
// Get the company_id from the logged-in user's session
$company_id = $_SESSION['company_id'];


// --- DATA FETCHING (Scoped to the Company Admin's Company) ---

// Stat Card: Total Employees in the company
$employees_result = query($mysqli, "SELECT COUNT(e.id) as count FROM employees e JOIN departments d ON e.department_id = d.id WHERE d.company_id = ?", [$company_id]);
$total_employees = $employees_result['success'] ? $employees_result['data'][0]['count'] : 0;

// Stat Card: Total Users in the company
$users_result = query($mysqli, "SELECT COUNT(id) as count FROM users WHERE company_id = ?", [$company_id]);
$total_users = $users_result['success'] ? $users_result['data'][0]['count'] : 0;

// Stat Card: Total Departments in the company
$departments_result = query($mysqli, "SELECT COUNT(id) as count FROM departments WHERE company_id = ?", [$company_id]);
$total_departments = $departments_result['success'] ? $departments_result['data'][0]['count'] : 0;

// Stat Card: Employees on Leave Today in the company
$today = date('Y-m-d');
$on_leave_query = "
    SELECT COUNT(a.id) as count 
    FROM attendance a 
    JOIN employees e ON a.employee_id = e.id 
    JOIN departments d ON e.department_id = d.id 
    WHERE a.date = ? AND a.status = 'leave' AND d.company_id = ?
";
$on_leave_result = query($mysqli, $on_leave_query, [$today, $company_id]);
$on_leave_today = $on_leave_result['success'] ? $on_leave_result['data'][0]['count'] : 0;

// Recent Hires List
$recent_hires_query = "
    SELECT e.first_name, e.last_name, e.date_of_joining, des.name as designation_name
    FROM employees e
    JOIN departments d ON e.department_id = d.id
    LEFT JOIN designations des ON e.designation_id = des.id
    WHERE d.company_id = ?
    ORDER BY e.date_of_joining DESC
    LIMIT 5
";
$recent_hires_result = query($mysqli, $recent_hires_query, [$company_id]);
$recent_hires = $recent_hires_result['success'] ? $recent_hires_result['data'] : [];


require_once '../components/layout/header.php';
?>

<div class="d-flex">
    <?php require_once '../components/layout/sidebar.php'; ?>
    <div class="p-3 p-md-4" style="flex: 1;">

        <!-- Page Heading -->
        <h2 class="h3 mb-4 text-gray-800"><i class="fas fa-tachometer-alt me-2"></i>Company Dashboard</h2>

        <!-- Stat Cards Row -->
        <div class="row">
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card stat-card shadow-sm">
                    <div class="card-body">
                        <div class="icon-circle bg-primary"><i class="fas fa-users-cog"></i></div>
                        <div>
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Total Employees</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?= $total_employees ?></div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card stat-card shadow-sm">
                    <div class="card-body">
                        <div class="icon-circle bg-success"><i class="fas fa-user-check"></i></div>
                        <div>
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Total Users</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?= $total_users ?></div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card stat-card shadow-sm">
                    <div class="card-body">
                        <div class="icon-circle bg-info"><i class="fas fa-sitemap"></i></div>
                        <div>
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Departments</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?= $total_departments ?></div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card stat-card shadow-sm">
                    <div class="card-body">
                        <div class="icon-circle bg-warning"><i class="fas fa-user-clock"></i></div>
                        <div>
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">On Leave Today</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?= $on_leave_today ?></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Content Row -->
        <div class="row">

            <!-- Recent Hires Column -->
            <div class="col-lg-8 mb-4">
                <div class="card main-content-card shadow-sm">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold">Recent Hires</h6>
                    </div>
                    <div class="card-body">
                        <div class="recent-companies-list">
                            <?php if (!empty($recent_hires)): ?>
                                <?php foreach ($recent_hires as $hire): ?>
                                    <div class="list-item">
                                        <div>
                                            <div class="company-name">
                                                <?= htmlspecialchars($hire['first_name'] . ' ' . $hire['last_name']); ?>
                                            </div>
                                            <div class="user-count text-muted">
                                                <?= htmlspecialchars($hire['designation_name'] ?? 'N/A'); ?>
                                            </div>
                                        </div>
                                        <div class="created-at text-muted">
                                            Hired on <?= date('F j, Y', strtotime($hire['date_of_joining'])); ?>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <div class="text-center text-muted p-4">No recent hires to display.</div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quick Actions Column -->
            <div class="col-lg-4 mb-4">
                <div class="card main-content-card shadow-sm">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold">Quick Actions</h6>
                    </div>
                    <div class="card-body quick-actions">
                        <div class="d-grid gap-2">
                            <a href="employees.php" class="btn btn-primary"><i class="fas fa-user-plus"></i> Add New
                                Employee</a>
                            <a href="departments.php" class="btn btn-success"><i class="fas fa-sitemap"></i> Manage
                                Departments</a>
                            <a href="attendance.php" class="btn btn-info"><i class="fas fa-calendar-check"></i> View
                                Attendance</a>
                            <a href="#" class="btn btn-warning text-dark"
                                onclick="alert('This Feature will be Available soon..!');"><i
                                    class="fas fa-chart-bar"></i>
                                View Reports</a>
                        </div>
                    </div>
                </div>
            </div>

        </div>

    </div>
</div>
<?php require_once '../components/layout/footer.php'; ?>