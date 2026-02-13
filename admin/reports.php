<?php
require_once '../config/db.php';
require_once '../includes/functions.php';
$title = "Admin Reports";


if (!isLoggedIn() || $_SESSION['role_id'] !== 1) {
    redirect("/hrms/pages/unauthorized.php");
}

require_once '../components/layout/header.php';
?>
<div class="d-flex">
    <?php require_once '../components/layout/sidebar.php'; ?>
    <div class="p-3 p-md-4 overflow-x-hidden" style="flex: 1;">
        <div class="row">
            <div class="col-xl-6 mb-4">
                <div class="card shadow-sm h-100">
                    <div class="card-header py-3 d-flex justify-content-between align-items-center">
                        <h6 class="m-0 font-weight-bold">Top 10 Companies by User Count</h6>
                        <a href="/hrms/api/api_export_reports_superadmin.php" class="btn btn-sm btn-outline-success"><i
                                class="ti ti-file-spreadsheet me-1"></i>Export</a>
                    </div>
                    <div class="card-body" style="min-height: 300px;"><canvas id="companyUsageChart"></canvas></div>
                </div>
            </div>
            <div class="col-xl-6 mb-4">
                <div class="card shadow-sm h-100">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold">New User Registrations (12 Months)</h6>
                    </div>
                    <div class="card-body" style="min-height: 300px;"><canvas id="userActivityChart"></canvas></div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-xl-4 mb-4">
                <div class="card shadow-sm h-100">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold">Employee Status Distribution</h6>
                    </div>
                    <div class="card-body" style="min-height: 300px;"><canvas id="employeeStatusChart"></canvas></div>
                </div>
            </div>
            <div class="col-xl-4 mb-4">
                <div class="card shadow-sm h-100">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold">Companies by Status</h6>
                    </div>
                    <div class="card-body" style="min-height: 300px;"><canvas id="companyStatusChart"></canvas></div>
                </div>
            </div>
            <div class="col-xl-4 mb-4">
                <div class="card shadow-sm h-100">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold">User Role Distribution</h6>
                    </div>
                    <div class="card-body" style="min-height: 300px;"><canvas id="userRoleChart"></canvas></div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-12">
                <div class="card shadow-sm">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold">Company Directory Report</h6>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover table-bordered" id="companyDirectoryTable">
                                <thead class="">
                                    <tr>
                                        <th>Company Name</th>
                                        <th>Email</th>
                                        <th>Phone</th>
                                        <th>Address</th>
                                        <th>Registered On</th>
                                    </tr>
                                </thead>
                                <tbody>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>
<?php require_once '../components/layout/footer.php'; ?>

<script>
    $(function () {
        let companyDirectoryTable;
        let charts = {};
        const COLORS = {
            primary: '#4e73df',
            success: '#1cc88a',
            info: '#36b9cc',
            warning: '#f6c23e',
            danger: '#e74a3b',
            secondary: '#858796'
        };

        // Theme Support
        function getChartTheme() {
            const isDark = document.documentElement.getAttribute('data-bs-theme') === 'dark';
            return {
                textColor: isDark ? '#adb5bd' : '#6e707e',
                gridColor: isDark ? 'rgba(255, 255, 255, 0.1)' : 'rgba(0, 0, 0, 0.05)',
                borderColor: isDark ? '#444' : '#e3e6f0'
            };
        }

        function hexToRgba(hex, alpha) {
            const r = parseInt(hex.slice(1, 3), 16);
            const g = parseInt(hex.slice(3, 5), 16);
            const b = parseInt(hex.slice(5, 7), 16);
            return `rgba(${r}, ${g}, ${b}, ${alpha})`;
        }

        function updateChartTheme(chart) {
            const theme = getChartTheme();
            if (chart.options.scales) {
                ['x', 'y'].forEach(axis => {
                    if (chart.options.scales[axis]) {
                        chart.options.scales[axis].grid.color = theme.gridColor;
                        chart.options.scales[axis].ticks.color = theme.textColor;
                    }
                });
            }
            if (chart.options.plugins?.legend) chart.options.plugins.legend.labels.color = theme.textColor;
            chart.update();
        }

        const observer = new MutationObserver(() => Object.values(charts).forEach(c => updateChartTheme(c)));
        observer.observe(document.documentElement, { attributes: true });

        fetch('/hrms/api/api_reports_superadmin.php')
            .then(response => response.json())
            .then(result => {
                if (result.success) {
                    const data = result.data;
                    initCompanyUsageChart(data.companyUsage || []);
                    initUserActivityChart(data.userRegistrationActivity || { labels: [], data: [] });
                    initEmployeeStatusChart(data.employeeStatus || {});
                    initCompanyStatusChart(data.companyStatus || {});
                    initUserRoleChart(data.userRole || {});
                    populateCompanyDirectory(data.companyDirectory || []);
                } else {
                    showToast(result.message || 'Failed to load admin reports.', 'error');
                }
            });

        function populateCompanyDirectory(data) {
            if (companyDirectoryTable) companyDirectoryTable.destroy();
            companyDirectoryTable = $('#companyDirectoryTable').DataTable({
                data: data,
                columns: [
                    { data: 'name', render: (d) => `<strong>${escapeHTML(d)}</strong>` },
                    { data: 'email', render: (d) => escapeHTML(d || 'N/A') },
                    { data: 'phone', render: (d) => escapeHTML(d || 'N/A') },
                    { data: 'address', render: (d) => escapeHTML(d || 'N/A') },
                    { data: 'created_at', render: d => new Date(d).toLocaleDateString() }
                ]
            });
        }

        function initCompanyUsageChart(data) {
            const ctx = document.getElementById('companyUsageChart').getContext('2d');
            const theme = getChartTheme();
            charts.usage = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: data.map(c => c.name),
                    datasets: [{
                        label: 'Users',
                        data: data.map(c => c.user_count),
                        backgroundColor: 'rgba(54, 162, 235, 0.2)',
                        borderColor: 'rgba(54, 162, 235, 1)',
                        borderWidth: 1.5
                    }]
                },
                options: {
                    indexAxis: 'y', maintainAspectRatio: false,
                    scales: {
                        x: { grid: { color: theme.gridColor }, ticks: { color: theme.textColor, stepSize: 1 } },
                        y: { grid: { color: theme.gridColor }, ticks: { color: theme.textColor } }
                    },
                    plugins: { legend: { display: false } }
                }
            });
        }

        function initUserActivityChart(data) {
            const ctx = document.getElementById('userActivityChart').getContext('2d');
            const theme = getChartTheme();
            charts.activity = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: data.labels,
                    datasets: [{
                        label: "New Users",
                        tension: 0.3,
                        borderColor: 'rgba(75, 192, 192, 1)',
                        backgroundColor: 'rgba(75, 192, 192, 0.2)',
                        fill: true,
                        borderWidth: 1.5,
                        data: data.data
                    }]
                },
                options: {
                    maintainAspectRatio: false,
                    scales: {
                        y: { grid: { color: theme.gridColor }, ticks: { color: theme.textColor } },
                        x: { grid: { color: theme.gridColor }, ticks: { color: theme.textColor } }
                    },
                    plugins: { legend: { display: false } }
                }
            });
        }

        function initEmployeeStatusChart(data) {
            const ctx = document.getElementById('employeeStatusChart');
            const theme = getChartTheme();
            charts.empStatus = new Chart(ctx.getContext('2d'), {
                type: 'doughnut',
                data: {
                    labels: Object.keys(data),
                    datasets: [{
                        data: Object.values(data),
                        backgroundColor: [
                            'rgba(75, 192, 192, 0.2)',
                            'rgba(255, 99, 132, 0.2)',
                            'rgba(255, 206, 86, 0.2)'
                        ],
                        borderColor: [
                            'rgba(75, 192, 192, 1)',
                            'rgba(255, 99, 132, 1)',
                            'rgba(255, 206, 86, 1)'
                        ],
                        borderWidth: 1.5
                    }]
                },
                options: { maintainAspectRatio: false, plugins: { legend: { position: 'bottom', labels: { color: theme.textColor } } } }
            });
        }

        function initCompanyStatusChart(data) {
            const ctx = document.getElementById('companyStatusChart');
            const theme = getChartTheme();
            charts.compStatus = new Chart(ctx.getContext('2d'), {
                type: 'doughnut',
                data: {
                    labels: Object.keys(data),
                    datasets: [{
                        data: Object.values(data),
                        backgroundColor: [
                            'rgba(75, 192, 192, 0.2)',
                            'rgba(255, 99, 132, 0.2)',
                            'rgba(255, 206, 86, 0.2)'
                        ],
                        borderColor: [
                            'rgba(75, 192, 192, 1)',
                            'rgba(255, 99, 132, 1)',
                            'rgba(255, 206, 86, 1)'
                        ],
                        borderWidth: 1.5
                    }]
                },
                options: { maintainAspectRatio: false, plugins: { legend: { position: 'bottom', labels: { color: theme.textColor } } } }
            });
        }

        function initUserRoleChart(data) {
            const ctx = document.getElementById('userRoleChart');
            const theme = getChartTheme();
            charts.roles = new Chart(ctx.getContext('2d'), {
                type: 'pie',
                data: {
                    labels: Object.keys(data),
                    datasets: [{
                        data: Object.values(data),
                        backgroundColor: [
                            'rgba(54, 162, 235, 0.2)',
                            'rgba(75, 192, 192, 0.2)',
                            'rgba(255, 206, 86, 0.2)',
                            'rgba(153, 102, 255, 0.2)'
                        ],
                        borderColor: [
                            'rgba(54, 162, 235, 1)',
                            'rgba(75, 192, 192, 1)',
                            'rgba(255, 206, 86, 1)',
                            'rgba(153, 102, 255, 1)'
                        ],
                        borderWidth: 1.5
                    }]
                },
                options: { maintainAspectRatio: false, plugins: { legend: { position: 'bottom', labels: { color: theme.textColor } } } }
            });
        }
    });
</script>