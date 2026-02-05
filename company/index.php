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

// --- UI Component Data ---

// Define the quick actions
$quick_actions = [
    ['title' => 'Hire New Employee', 'url' => 'recruitment.php', 'icon' => 'ti ti-user-plus'],
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
        <div class="row" id="companyStatsContainer"></div>

        <div class="row">
            <div class="col-lg-7 mb-4">
                <div class="card main-content-card shadow-sm h-100">
                    <div class="card-header py-3 d-flex justify-content-between align-items-center">
                        <h6 class="m-0 font-weight-bold">Recent Hires & Trends (Last 3 Months)</h6>
                        <a href="employees.php" class="btn btn-sm btn-primary"><i class="ti ti-user-plus"></i> Add
                            Employee</a>
                    </div>
                    <div class="card-body">
                        <div class="mb-4" id="hiresChartParent" style="position: relative; height:250px">
                            <canvas id="hiresChart"></canvas>
                        </div>
                        <hr>
                        <div class="recent-companies-list" id="recentHiresList">
                            <!-- List items will be rendered here -->
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

        // Load Dashboard Data with Skeletons
        loadDashboardData();
    });

    async function loadDashboardData() {
        const statsContainer = '#companyStatsContainer';
        const chartContainer = '#hiresChartParent';
        const listContainer = '#recentHiresList';

        // Show skeletons
        SkeletonFactory.show(statsContainer, 'stat-card', 4);
        SkeletonFactory.replace(chartContainer, 'rect', { size: 'sk-rect-xl', animation: 'pulse' });
        SkeletonFactory.show(listContainer, 'list-item', 5);

        try {
            const response = await fetch('/hrms/api/api_dashboard.php?action=get_dashboard_data');
            const result = await response.json();

            // 2. Wait for minimum duration then hide/restore
            await Promise.all([
                SkeletonFactory.hide(statsContainer),
                SkeletonFactory.restore(chartContainer),
                SkeletonFactory.hide(listContainer)
            ]);

            if (result.success) {
                const data = result.data;

                // Render Stats
                renderStatCards('companyStatsContainer', data.stats);

                // Render Chart
                renderChart(data.chart);

                // Render Recent Hires
                renderRecentHires(listContainer, data.recent_hires);
            } else {
                console.error(result.message);
                showToast(result.message, 'error');
            }

        } catch (error) {
            console.error('Error loading dashboard:', error);
            await Promise.all([
                SkeletonFactory.hide(statsContainer),
                SkeletonFactory.restore(chartContainer),
                SkeletonFactory.hide(listContainer)
            ]);
            showToast('Failed to load dashboard data', 'error');
        }
    }

    function renderChart(chartData) {
        const ctx = document.getElementById('hiresChart');
        if (ctx) {
            new Chart(ctx, {
                type: 'line',
                data: {
                    labels: chartData.labels,
                    datasets: [{
                        label: 'New Hires',
                        data: chartData.data,
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
                        legend: { display: false }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: { stepSize: 1, precision: 0 }
                        }
                    }
                }
            });
        }
    }

    function renderRecentHires(containerSelector, hires) {
        const container = document.querySelector(containerSelector);
        if (!hires || hires.length === 0) {
            container.innerHTML = `
            <div class="text-center text-muted p-4">
                <i class="ti ti-users" style="font-size: 2rem; opacity: 0.5; display: block; margin-bottom: 0.5rem"></i>
                No recent hires to display.
            </div>`;
            return;
        }

        let html = '';
        hires.forEach(hire => {
            const date = new Date(hire.date_of_joining).toLocaleDateString('en-US', {
                year: 'numeric', month: 'long', day: 'numeric'
            });

            // Use shared avatar generator (falls back gracefully if data missing)
            const avatarSource = {
                id: hire.user_id*6 || hire.employee_id*3 || `${hire.first_name}${hire.last_name}`,
                username: hire.username || `${hire.first_name}.${hire.last_name}`
            };
            const avatar = generateAvatarData(avatarSource);
            console.log(avatar);
            html += `
            <div class="list-item d-flex align-items-center p-3 border-bottom">
                <div class="avatar text-white rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 40px; height: 40px; font-weight: bold; background-color: ${avatar.color};">
                    ${avatar.initials}
                </div>
                <div class="flex-grow-1">
                    <div class="company-name font-weight-bold">
                        ${hire.first_name} ${hire.last_name}
                    </div>
                    <div class="user-count text-muted small">
                        ${hire.designation_name || 'N/A'}
                    </div>
                </div>
                <div class="created-at text-muted small">
                    Hired on ${date}
                </div>
            </div>
        `;
        });
        container.innerHTML = html;
    }
</script>

<?php require_once '../components/layout/footer.php'; ?>