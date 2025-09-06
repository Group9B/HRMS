<?php
require_once '../config/db.php';
require_once '../includes/functions.php';
$title = "Super Admin Reports";

// Security Check: Ensure the user is a logged-in Super Admin
if (!isLoggedIn() || $_SESSION['role_id'] !== 1) {
    redirect("/hrms/pages/unauthorized.php");
}

require_once '../components/layout/header.php';
?>
<!-- Include Chart.js library -->
<div class="d-flex">
    <?php require_once '../components/layout/sidebar.php'; ?>
    <div class="p-3 p-md-4 overflow-x-scroll" style="flex: 1;">
        <h2 class="h3 mb-4 text-gray-800"><i class="fas fa-shield-alt me-2"></i>Super Admin Analytics</h2>

        <!-- Company Usage & Activity Row -->
        <div class="row">
            <div class="col-xl-6 mb-4">
                <div class="card shadow-sm h-100">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold">Top 10 Companies by User Count</h6>
                    </div>
                    <div class="card-body"><canvas id="companyUsageChart"></canvas></div>
                </div>
            </div>
            <div class="col-xl-6 mb-4">
                <div class="card shadow-sm h-100">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold">New User Registrations (12 Months)</h6>
                    </div>
                    <div class="card-body"><canvas id="userActivityChart"></canvas></div>
                </div>
            </div>
        </div>

        <!-- Company Directory Report -->
        <div class="row">
            <div class="col-12">
                <div class="card shadow-sm">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold">Company Directory Report</h6>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover" id="companyDirectoryTable">
                                <thead class="table">
                                    <tr>
                                        <th>Company Name</th>
                                        <th>Email</th>
                                        <th>Phone</th>
                                        <th>Status</th>
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

        // Fetch all analytics data from the new Super Admin API
        fetch('/hrms/api/api_reports_superadmin.php')
            .then(response => response.json())
            .then(result => {
                if (result.success) {
                    const data = result.data;
                    initCompanyUsageChart(data.companyUsage || []);
                    initUserActivityChart(data.userRegistrationActivity || { labels: [], data: [] });
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
            companyDirectoryTable = $('#companyDirectoryTable').DataTable({
                data: data,
                order: [[0, 'asc']],
                columns: [
                    { data: 'name', render: (d) => `<strong>${escapeHTML(d)}</strong>` },
                    { data: 'email', render: (d) => escapeHTML(d) },
                    { data: 'phone', render: (d) => escapeHTML(d) },
                    {
                        data: 'status',
                        render: function (data) {
                            const status = escapeHTML(data) || 'active';
                            let badgeClass = 'secondary';
                            if (status === 'active') badgeClass = 'success';
                            if (status === 'inactive') badgeClass = 'danger';
                            if (status === 'pending') badgeClass = 'warning';
                            return `<span class="badge text-bg-${badgeClass}">${status.charAt(0).toUpperCase() + status.slice(1)}</span>`;
                        }
                    },
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
    });
</script>