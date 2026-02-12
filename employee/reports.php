<?php
require_once '../config/db.php';
require_once '../includes/functions.php';
$title = "My Career Analytics";

if (!isLoggedIn() || $_SESSION['role_id'] !== 4) {
    redirect("/hrms/pages/unauthorized.php");
}

require_once '../components/layout/header.php';
?>

<div class="d-flex">
    <?php require_once '../components/layout/sidebar.php'; ?>
    <div class="p-3 p-md-4 flex-grow-1" style="overflow-x: hidden;">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h4 class="m-0 fw-bold">My Personal Analytics</h4>
            <div class="text-muted small">Tracking your professional growth and metrics</div>
        </div>

        <div class="row">
            <div class="col-xl-8 mb-4">
                <div class="card shadow-sm h-100">
                    <div class="card-header py-3">
                        <h6 class="m-0 fw-bold">My Attendance Presence (Last 15 Days)</h6>
                    </div>
                    <div class="card-body">
                        <div id="attChartParent" style="position: relative; height:300px">
                            <canvas id="attChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-4 mb-4">
                <div class="card shadow-sm h-100">
                    <div class="card-header py-3">
                        <h6 class="m-0 fw-bold">Leave Balance Summary</h6>
                    </div>
                    <div class="card-body">
                        <div id="leaveChartParent" style="position: relative; height:300px">
                            <canvas id="leaveChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-xl-7 mb-4">
                <div class="card shadow-sm h-100">
                    <div class="card-header py-3">
                        <h6 class="m-0 fw-bold">Payroll Progress (Last 6 Months)</h6>
                    </div>
                    <div class="card-body">
                        <div id="payChartParent" style="position: relative; height:300px">
                            <canvas id="payChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-5 mb-4">
                <div class="card shadow-sm h-100">
                    <div class="card-header py-3">
                        <h6 class="m-0 fw-bold">Task Completion Status</h6>
                    </div>
                    <div class="card-body">
                        <div id="taskChartParent" style="position: relative; height:300px">
                            <canvas id="taskChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once '../components/layout/footer.php'; ?>

<script>
    const COLORS = {
        primary: '#4e73df',
        success: '#1cc88a',
        info: '#36b9cc',
        warning: '#f6c23e',
        danger: '#e74a3b',
        secondary: '#858796'
    };
    let charts = {};

    $(document).ready(function () {
        loadEmpReportData();
        const observer = new MutationObserver(() => Object.values(charts).forEach(c => updateChartTheme(c)));
        observer.observe(document.documentElement, { attributes: true });
    });

    function getChartTheme() {
        const isDark = document.documentElement.getAttribute('data-bs-theme') === 'dark';
        return { textColor: isDark ? '#adb5bd' : '#6e707e', gridColor: isDark ? 'rgba(255, 255, 255, 0.1)' : 'rgba(0, 0, 0, 0.05)' };
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
            ['x', 'y'].forEach(a => { if (chart.options.scales[a]) { chart.options.scales[a].grid.color = theme.gridColor; chart.options.scales[a].ticks.color = theme.textColor; } });
        }
        if (chart.options.plugins?.legend) chart.options.plugins.legend.labels.color = theme.textColor;
        chart.update();
    }

    async function loadEmpReportData() {
        const cs = ['#attChartParent', '#leaveChartParent', '#payChartParent', '#taskChartParent'];
        cs.forEach(c => SkeletonFactory.replace(c, 'rect', { size: 'sk-rect-xl' }));
        try {
            const r = await fetch('/hrms/api/api_reports_employee.php');
            const res = await r.json();
            await Promise.all(cs.map(c => SkeletonFactory.restore(c)));
            if (res.success) {
                initAttChart(res.data.myAttendance);
                initLeaveChart(res.data.leaveBalance);
                initPayChart(res.data.payrollProgress);
                initTaskChart(res.data.taskEfficiency);
            }
        } catch (e) { }
    }

    function initAttChart(d) {
        const ctx = document.getElementById('attChart').getContext('2d');
        const theme = getChartTheme();
        charts.att = new Chart(ctx, {
            type: 'line', data: { labels: d.labels, datasets: [{ label: 'I was present', data: d.data, borderColor: COLORS.success, backgroundColor: hexToRgba(COLORS.success, 0.1), fill: true, tension: 0.3 }] },
            options: { maintainAspectRatio: false, scales: { y: { beginAtZero: true, max: 100, grid: { color: theme.gridColor }, ticks: { color: theme.textColor, callback: v => v + '%' } }, x: { grid: { color: theme.gridColor }, ticks: { color: theme.textColor } } } }
        });
    }

    function initLeaveChart(d) {
        const ctx = document.getElementById('leaveChart').getContext('2d');
        const theme = getChartTheme();
        charts.leave = new Chart(ctx, {
            type: 'doughnut', data: { labels: d.map(x => x.label), datasets: [{ data: d.map(x => x.value), backgroundColor: [COLORS.danger, COLORS.success] }] },
            options: { maintainAspectRatio: false, plugins: { legend: { position: 'bottom', labels: { color: theme.textColor } } } }
        });
    }

    function initPayChart(d) {
        const ctx = document.getElementById('payChart').getContext('2d');
        const theme = getChartTheme();
        charts.pay = new Chart(ctx, {
            type: 'line', data: { labels: d.labels, datasets: [{ label: 'Net Salary Payout', data: d.data, borderColor: COLORS.primary, backgroundColor: hexToRgba(COLORS.primary, 0.1), fill: true }] },
            options: { maintainAspectRatio: false, scales: { y: { grid: { color: theme.gridColor }, ticks: { color: theme.textColor } }, x: { grid: { color: theme.gridColor }, ticks: { color: theme.textColor } } } }
        });
    }

    function initTaskChart(d) {
        const ctx = document.getElementById('taskChart').getContext('2d');
        const theme = getChartTheme();
        charts.task = new Chart(ctx, {
            type: 'pie', data: { labels: d.map(x => x.label), datasets: [{ data: d.map(x => x.value), backgroundColor: [COLORS.primary, COLORS.success, COLORS.warning, COLORS.danger] }] },
            options: { maintainAspectRatio: false, plugins: { legend: { position: 'bottom', labels: { color: theme.textColor } } } }
        });
    }
</script>