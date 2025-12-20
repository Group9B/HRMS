<?php
require_once '../config/db.php';
require_once '../includes/functions.php';
$title = "Company Dashboard";
$page_name = "Company Dashboard";
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

// Stat Card: Total Employees
$employees_result = query($mysqli, "SELECT COUNT(e.id) as count FROM employees e JOIN departments d ON e.department_id = d.id WHERE d.company_id = ? AND e.status = 'active'", [$company_id]);
$total_employees = $employees_result['success'] ? $employees_result['data'][0]['count'] : 0;

// Stat Card: Total Departments
$departments_result = query($mysqli, "SELECT COUNT(id) as count FROM departments WHERE company_id = ?", [$company_id]);
$total_departments = $departments_result['success'] ? $departments_result['data'][0]['count'] : 0;

// Stat Card: Pending Leave Requests
$pending_leaves_result = query($mysqli, "SELECT COUNT(l.id) as count FROM leaves l JOIN employees e ON l.employee_id = e.id JOIN departments d ON e.department_id = d.id WHERE d.company_id = ? AND l.status = 'pending'", [$company_id]);
$pending_leaves = $pending_leaves_result['success'] ? $pending_leaves_result['data'][0]['count'] : 0;


// Stat Card: Employees on Leave Today
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

// Chart Data: New hires in the last 3 months
$hires_chart_query = "
    SELECT DATE_FORMAT(e.date_of_joining, '%b %Y') AS month, COUNT(e.id) AS hires_count
    FROM employees e
    JOIN departments d ON e.department_id = d.id
    WHERE d.company_id = ? AND e.date_of_joining >= DATE_SUB(CURDATE(), INTERVAL 3 MONTH)
    GROUP BY month
    ORDER BY e.date_of_joining ASC
";
$hires_chart_result = query($mysqli, $hires_chart_query, [$company_id]);
$chart_labels = [];
$chart_data = [];
if ($hires_chart_result['success']) {
    foreach ($hires_chart_result['data'] as $row) {
        $chart_labels[] = $row['month'];
        $chart_data[] = $row['hires_count'];
    }
}


// --- UI Component Data ---

// Define the stat cards
$stat_cards = [
    ['title' => 'Active Employees', 'value' => $total_employees, 'icon' => 'ti ti-users', 'color' => 'primary'],
    ['title' => 'Departments', 'value' => $total_departments, 'icon' => 'ti ti-sitemap', 'color' => 'info'],
    ['title' => 'Pending Leaves', 'value' => $pending_leaves, 'icon' => 'ti ti-hourglass-empty', 'color' => 'danger'],
    ['title' => 'On Leave Today', 'value' => $on_leave_today, 'icon' => 'ti ti-user-clock', 'color' => 'warning']
];

// Define the quick actions
$quick_actions = [
    ['title' => 'Add New Employee', 'url' => 'employees.php', 'icon' => 'ti ti-user-plus'],
    ['title' => 'Manage Departments', 'url' => 'organization.php', 'icon' => 'ti ti-sitemap'],
    ['title' => 'Approve Leaves', 'url' => 'leaves.php', 'icon' => 'ti ti-calendar-check'],
    ['title' => 'View Reports', 'url' => '#', 'icon' => 'ti ti-chart-bar', 'onclick' => "alert('This Feature will be Available soon..!');"]
];


require_once '../components/layout/header.php';
?>

<div class="d-flex">
    <?php require_once '../components/layout/sidebar.php'; ?>
    <div class="p-3 p-md-4" style="flex: 1;">

        <!-- Render Stat Cards -->
        <div class="row">
            <?php
            foreach ($stat_cards as $card) {
                echo render_stat_card($card['title'], $card['value'], $card['icon'], $card['color']);
            }
            ?>
        </div>

        <div class="row">
            <!-- Recent Hires and Trend Chart -->
            <div class="col-lg-7 mb-4">
                <div class="card main-content-card shadow-sm h-100">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold">Recent Hires & Trends (Last 3 Months)</h6>
                    </div>
                    <div class="card-body">
                        <div class="mb-4" style="position: relative; height:250px">
                            <canvas id="hiresChart"></canvas>
                        </div>
                        <hr>
                        <div class="recent-companies-list">
                            <?php if (!empty($recent_hires)): ?>
                                <?php foreach ($recent_hires as $hire): ?>
                                    <div class="list-item">
                                        <div>
                                            <div class="company-name">
                                                <?= htmlspecialchars(ucwords($hire['first_name'] . ' ' . $hire['last_name'])); ?>
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

            <!-- Right Column Widgets -->
            <div class="col-lg-5 mb-4">
                <!-- Quick Actions -->
                <?php echo render_quick_actions($quick_actions); ?>

                <!-- To-Do List -->
                <?php echo render_todo_list_widget('My To-Do List'); ?>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        // Initialize To-Do List
        initializeTodoList('#todo-form', '#todo-list');

        // Initialize Hires Chart
        const ctx = document.getElementById('hiresChart');
        if (ctx) {
            new Chart(ctx, {
                type: 'line',
                data: {
                    labels: <?= json_encode($chart_labels) ?>,
                    datasets: [{
                        label: 'New Hires',
                        data: <?= json_encode($chart_data) ?>,
                        fill: true,
                        borderColor: 'rgb(75, 192, 192)',
                        backgroundColor: 'rgba(75, 192, 192, 0.2)',
                        tension: 0.3
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false // Hides the legend
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                stepSize: 1,
                                precision: 0 // Ensures integer values on the axis
                            }
                        }
                    }
                }
            });
        }
    });
</script>

<?php require_once '../components/layout/footer.php'; ?>