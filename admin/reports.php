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
    <div class="p-3 p-md-4" style="flex: 1;">
        <div class="row">
            <div class="col-xl-6 mb-4">
                <div class="card shadow-sm h-100">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold">Top 10 Companies by User Count</h6>
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
            })
            .catch(error => {
                console.error('Error fetching report data:', error);
                showToast('An unexpected network error occurred.', 'error');
            });

        function populateCompanyDirectory(data) {
            if (companyDirectoryTable) {
                companyDirectoryTable.destroy();
            }

            companyDirectoryTable = $('#companyDirectoryTable').DataTable({
                data: data,
                order: [[0, 'asc']],
                columns: [
                    { data: 'name', render: (d) => `<strong>${escapeHTML(d)}</strong>` },
                    { data: 'email', render: (d) => escapeHTML(d || 'N/A') },
                    { data: 'phone', render: (d) => escapeHTML(d || 'N/A') },
                    { data: 'address', render: (d) => escapeHTML(d || 'N/A') },
                    {
                        data: 'created_at',
                        render: function (data) {
                            return new Date(data).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
                        }
                    }
                ]
            });
        }

        function initCompanyUsageChart(data) {
            const ctx = document.getElementById('companyUsageChart').getContext('2d');
            new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: data.map(c => c.name),
                    datasets: [{
                        label: 'User Count',
                        data: data.map(c => c.user_count),
                        backgroundColor: 'rgba(78, 115, 223, 0.8)',
                        borderColor: 'rgba(78, 115, 223, 1)',
                        borderWidth: 1
                    }]
                },
                options: {
                    indexAxis: 'y',
                    maintainAspectRatio: false,
                    responsive: true,
                    plugins: { legend: { display: false } },
                    scales: { x: { ticks: { stepSize: 1 } } }
                }
            });
        }

        function initUserActivityChart(data) {
            const ctx = document.getElementById('userActivityChart').getContext('2d');
            new Chart(ctx, {
                type: 'line',
                data: {
                    labels: data.labels,
                    datasets: [{
                        label: "New Users",
                        tension: 0.3,
                        backgroundColor: "rgba(28, 200, 138, 0.05)",
                        borderColor: "rgba(28, 200, 138, 1)",
                        pointRadius: 3,
                        data: data.data,
                    }],
                },
                options: {
                    maintainAspectRatio: false,
                    responsive: true,
                    scales: { y: { beginAtZero: true, ticks: { stepSize: 1 } } },
                    plugins: { legend: { display: false } }
                }
            });
        }

        function initEmployeeStatusChart(data) {
            const ctx = document.getElementById('employeeStatusChart');
            if (!ctx) return;

            new Chart(ctx.getContext('2d'), {
                type: 'doughnut',
                data: {
                    labels: Object.keys(data),
                    datasets: [{
                        data: Object.values(data),
                        backgroundColor: [
                            'rgba(28, 200, 138, 0.8)',  // active - green
                            'rgba(220, 53, 69, 0.8)',   // inactive - red
                            'rgba(255, 193, 7, 0.8)'    // on_leave - yellow
                        ],
                        borderColor: [
                            'rgba(28, 200, 138, 1)',
                            'rgba(220, 53, 69, 1)',
                            'rgba(255, 193, 7, 1)'
                        ],
                        borderWidth: 2
                    }]
                },
                options: {
                    maintainAspectRatio: false,
                    responsive: true,
                    plugins: { legend: { position: 'bottom' } }
                }
            });
        }

        function initCompanyStatusChart(data) {
            const ctx = document.getElementById('companyStatusChart');
            if (!ctx) return;

            new Chart(ctx.getContext('2d'), {
                type: 'doughnut',
                data: {
                    labels: Object.keys(data),
                    datasets: [{
                        data: Object.values(data),
                        backgroundColor: [
                            'rgba(28, 200, 138, 0.8)',  // active - green
                            'rgba(220, 53, 69, 0.8)',   // inactive - red
                            'rgba(255, 193, 7, 0.8)'    // pending - yellow
                        ],
                        borderColor: [
                            'rgba(28, 200, 138, 1)',
                            'rgba(220, 53, 69, 1)',
                            'rgba(255, 193, 7, 1)'
                        ],
                        borderWidth: 2
                    }]
                },
                options: {
                    maintainAspectRatio: false,
                    responsive: true,
                    plugins: { legend: { position: 'bottom' } }
                }
            });
        }

        function initUserRoleChart(data) {
            const ctx = document.getElementById('userRoleChart');
            if (!ctx) return;

            new Chart(ctx.getContext('2d'), {
                type: 'pie',
                data: {
                    labels: Object.keys(data),
                    datasets: [{
                        data: Object.values(data),
                        backgroundColor: [
                            'rgba(78, 115, 223, 0.8)',   // blue
                            'rgba(28, 200, 138, 0.8)',   // green
                            'rgba(255, 159, 64, 0.8)',   // orange
                            'rgba(153, 102, 255, 0.8)'   // purple
                        ],
                        borderColor: [
                            'rgba(78, 115, 223, 1)',
                            'rgba(28, 200, 138, 1)',
                            'rgba(255, 159, 64, 1)',
                            'rgba(153, 102, 255, 1)'
                        ],
                        borderWidth: 2
                    }]
                },
                options: {
                    maintainAspectRatio: false,
                    responsive: true,
                    plugins: { legend: { position: 'bottom' } }
                }
            });
        }
    });
</script>