<?php
require_once '../config/db.php';
require_once '../includes/functions.php';
$title = "Team Reports & Analytics";

if (!isLoggedIn() || $_SESSION['role_id'] !== 6) {
    redirect("/hrms/pages/unauthorized.php");
}

require_once '../components/layout/header.php';
?>

<div class="d-flex">
    <?php require_once '../components/layout/sidebar.php'; ?>
    <div class="p-3 p-md-4 flex-grow-1" style="overflow-x: hidden;">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h4 class="m-0 fw-bold">Team Analytics</h4>
            <div class="text-muted small">Overview of your team's performance and activity</div>
        </div>

        <div class="row">
            <div class="col-xl-8 mb-4">
                <div class="card shadow-sm h-100">
                    <div class="card-header py-3">
                        <h6 class="m-0 fw-bold">Team Attendance Trend (Last 15 Days)</h6>
                    </div>
                    <div class="card-body">
                        <div id="attendanceChartParent" style="position: relative; height:300px">
                            <canvas id="attendanceChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-4 mb-4">
                <div class="card shadow-sm h-100">
                    <div class="card-header py-3">
                        <h6 class="m-0 fw-bold">Team Performance Overview</h6>
                    </div>
                    <div class="card-body">
                        <div id="perfChartParent" style="position: relative; height:300px">
                            <canvas id="perfChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-xl-6 mb-4">
                <div class="card shadow-sm h-100">
                    <div class="card-header py-3">
                        <h6 class="m-0 fw-bold">Task Velocity by Member</h6>
                    </div>
                    <div class="card-body">
                        <div id="taskChartParent" style="position: relative; height:300px">
                            <canvas id="taskChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-6 mb-4">
                <div class="card shadow-sm h-100">
                    <div class="card-header py-3">
                        <h6 class="m-0 fw-bold">Upcoming Leave Impact (Next 30 Days)</h6>
                    </div>
                    <div class="card-body">
                        <div id="leaveChartParent" style="position: relative; height:300px">
                            <canvas id="leaveChart"></canvas>
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
        loadManagerReportData();
        const observer = new MutationObserver(() => {
            Object.values(charts).forEach(chart => updateChartTheme(chart));
        });
        observer.observe(document.documentElement, { attributes: true });
    });

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

    async function loadManagerReportData() {
        const containers = ['#attendanceChartParent', '#perfChartParent', '#taskChartParent', '#leaveChartParent'];
        containers.forEach(c => SkeletonFactory.replace(c, 'rect', { size: 'sk-rect-xl' }));

        try {
            const res = await fetch('/hrms/api/api_reports_manager.php');
            const result = await res.json();
            await Promise.all(containers.map(c => SkeletonFactory.restore(c)));

            if (result.success) {
                const data = result.data;
                initAttendanceChart(data.attendanceTrends);
                initPerfChart(data.performanceDist);
                initTaskChart(data.taskVelocity);
                initLeaveChart(data.leaveImpact);
            }
        } catch (e) { showToast('Error fetching data', 'error'); }
    }

    function initAttendanceChart(data) {
        const ctx = document.getElementById('attendanceChart').getContext('2d');
        const theme = getChartTheme();
        charts.att = new Chart(ctx, {
            type: 'line',
            data: {
                labels: data.labels,
                datasets: [{
                    label: 'Team Presence %',
                    data: data.data,
                    borderColor: COLORS.success,
                    backgroundColor: hexToRgba(COLORS.success, 0.1),
                    fill: true,
                    tension: 0.3
                }]
            },
            options: {
                maintainAspectRatio: false,
                responsive: true,
                scales: {
                    y: { beginAtZero: true, max: 100, grid: { color: theme.gridColor }, ticks: { color: theme.textColor, callback: v => v + '%' } },
                    x: { grid: { color: theme.gridColor }, ticks: { color: theme.textColor } }
                }
            }
        });
    }

    function initPerfChart(data) {
        const ctx = document.getElementById('perfChart').getContext('2d');
        const theme = getChartTheme();
        charts.perf = new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: data.map(d => d.label),
                datasets: [{
                    data: data.map(d => d.value),
                    backgroundColor: [COLORS.success, COLORS.primary, COLORS.warning, COLORS.danger]
                }]
            },
            options: { maintainAspectRatio: false, plugins: { legend: { position: 'bottom', labels: { color: theme.textColor } } } }
        });
    }

    function initTaskChart(data) {
        const ctx = document.getElementById('taskChart').getContext('2d');
        const theme = getChartTheme();
        charts.task = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: data.map(d => d.name),
                datasets: [
                    { label: 'Completed', data: data.map(d => d.completed), backgroundColor: hexToRgba(COLORS.success, 0.2), borderColor: COLORS.success, borderWidth: 1.5 },
                    { label: 'Pending', data: data.map(d => d.pending), backgroundColor: hexToRgba(COLORS.warning, 0.2), borderColor: COLORS.warning, borderWidth: 1.5 }
                ]
            },
            options: {
                maintainAspectRatio: false,
                scales: {
                    x: { stacked: true, grid: { color: theme.gridColor }, ticks: { color: theme.textColor } },
                    y: { stacked: true, grid: { color: theme.gridColor }, ticks: { color: theme.textColor } }
                }
            }
        });
    }

    function initLeaveChart(data) {
        const ctx = document.getElementById('leaveChart').getContext('2d');
        const theme = getChartTheme();
        charts.leave = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: data.map(d => d.name),
                datasets: [{ label: 'Impact Count', data: data.map(d => d.count), backgroundColor: hexToRgba(COLORS.danger, 0.2), borderColor: COLORS.danger, borderWidth: 1.5 }]
            },
            options: {
                maintainAspectRatio: false, scales: {
                    y: { grid: { color: theme.gridColor }, ticks: { color: theme.textColor, stepSize: 1 } },
                    x: { grid: { color: theme.gridColor }, ticks: { color: theme.textColor } }
                }
            }
        });
    }
</script>