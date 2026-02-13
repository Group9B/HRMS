<?php
require_once '../config/db.php';
require_once '../includes/functions.php';
$title = "Company Reports & Analytics";

// Security Check: Company Owner (2) or HR (3)
if (!isLoggedIn() || !in_array($_SESSION['role_id'], [2, 3])) {
    redirect("/hrms/pages/unauthorized.php");
}

require_once '../components/layout/header.php';
?>

<div class="d-flex">
    <?php require_once '../components/layout/sidebar.php'; ?>
    <div class="p-3 p-md-4 flex-grow-1" style="overflow-x: hidden;" id="reportContent">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h4 class="m-0 fw-bold">Reports & Analytics</h4>
            <div class="d-flex align-items-center gap-2">
                <div class="text-muted small d-none d-md-block">Real-time data for your company</div>
                <button onclick="downloadPDF()" class="btn btn-sm btn-danger"><i
                        class="ti ti-file-type-pdf me-1"></i>Download PDF Report</button>
            </div>
        </div>
        <!-- Employee Distribution Section -->
        <div class="row">
            <div class="col-xl-6 mb-4">
                <div class="card shadow-sm h-100">
                    <div class="card-header py-3 d-flex justify-content-between align-items-center">
                        <h6 class="m-0 fw-bold">Employee Distribution by Department</h6>
                        <a href="/hrms/api/api_export_report_single.php?type=dept"
                            class="btn btn-sm btn-outline-success" title="Export Data"><i
                                class="ti ti-download"></i></a>
                    </div>
                    <div class="card-body">
                        <div id="deptChartParent" style="position: relative; height:300px">
                            <canvas id="deptChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-6 mb-4">
                <div class="card shadow-sm h-100">
                    <div class="card-header py-3 d-flex justify-content-between align-items-center">
                        <h6 class="m-0 fw-bold">Employee Distribution by Designation</h6>
                        <a href="/hrms/api/api_export_report_single.php?type=desig"
                            class="btn btn-sm btn-outline-success" title="Export Data"><i
                                class="ti ti-download"></i></a>
                    </div>
                    <div class="card-body">
                        <div id="desigChartParent" style="position: relative; height:300px">
                            <canvas id="desigChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Attendance & Recruitment Funnel -->
        <div class="row">
            <div class="col-xl-8 mb-4">
                <div class="card shadow-sm h-100">
                    <div class="card-header py-3 d-flex justify-content-between align-items-center">
                        <h6 class="m-0 fw-bold">Daily Presence Trend (Last 15 Days)</h6>
                        <a href="/hrms/api/api_export_report_single.php?type=attendance"
                            class="btn btn-sm btn-outline-success" title="Export Data"><i
                                class="ti ti-download"></i></a>
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
                    <div class="card-header py-3 d-flex justify-content-between align-items-center">
                        <h6 class="m-0 fw-bold">Recruitment Pipeline</h6>
                        <a href="/hrms/api/api_export_report_single.php?type=recruitment"
                            class="btn btn-sm btn-outline-success" title="Export Data"><i
                                class="ti ti-download"></i></a>
                    </div>
                    <div class="card-body">
                        <div id="recruitmentChartParent" style="position: relative; height:300px">
                            <canvas id="recruitmentChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Payroll & Leaves -->
        <div class="row">
            <div class="col-xl-7 mb-4">
                <div class="card shadow-sm h-100">
                    <div class="card-header py-3 d-flex justify-content-between align-items-center">
                        <h6 class="m-0 fw-bold">Payroll Expenditure (Last 6 Months)</h6>
                        <a href="/hrms/api/api_export_report_single.php?type=payroll"
                            class="btn btn-sm btn-outline-success" title="Export Data"><i
                                class="ti ti-download"></i></a>
                    </div>
                    <div class="card-body">
                        <div id="payrollChartParent" style="position: relative; height:300px">
                            <canvas id="payrollChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-5 mb-4">
                <div class="card shadow-sm h-100">
                    <div class="card-header py-3 d-flex justify-content-between align-items-center">
                        <h6 class="m-0 fw-bold">Leave Status Distribution</h6>
                        <a href="/hrms/api/api_export_report_single.php?type=leave"
                            class="btn btn-sm btn-outline-success" title="Export Data"><i
                                class="ti ti-download"></i></a>
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

<script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
<script>
    // Theme Colors (Matching organization.php)
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
        loadReportData();

        // Listen for theme changes to update chart styling
        const observer = new MutationObserver((mutations) => {
            mutations.forEach((mutation) => {
                if (mutation.attributeName === 'data-bs-theme') {
                    Object.values(charts).forEach(chart => {
                        updateChartTheme(chart);
                    });
                }
            });
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
            if (chart.options.scales.x) {
                chart.options.scales.x.grid.color = theme.gridColor;
                chart.options.scales.x.ticks.color = theme.textColor;
            }
            if (chart.options.scales.y) {
                chart.options.scales.y.grid.color = theme.gridColor;
                chart.options.scales.y.ticks.color = theme.textColor;
            }
        }

        if (chart.options.plugins && chart.options.plugins.legend) {
            chart.options.plugins.legend.labels.color = theme.textColor;
        }

        chart.update();
    }

    async function loadReportData() {
        const containers = [
            '#deptChartParent', '#desigChartParent',
            '#attendanceChartParent', '#recruitmentChartParent',
            '#payrollChartParent', '#leaveChartParent'
        ];

        containers.forEach(c => SkeletonFactory.replace(c, 'rect', { size: 'sk-rect-xl', animation: 'pulse' }));

        try {
            const response = await fetch('/hrms/api/api_reports_company.php');
            const result = await response.json();

            await Promise.all(containers.map(c => SkeletonFactory.restore(c)));

            if (result.success) {
                const data = result.data;
                initDeptChart(data.deptDistribution);
                initDesigChart(data.desigDistribution);
                initAttendanceChart(data.attendanceTrends);
                initRecruitmentChart(data.recruitmentFunnel);
                initPayrollChart(data.payrollTrends);
                initLeaveChart(data.leaveDistribution);
            } else {
                showToast(result.message || 'Failed to load report data', 'error');
            }
        } catch (error) {
            console.error('Error fetching report data:', error);
            await Promise.all(containers.map(c => SkeletonFactory.restore(c)));
            showToast('An unexpected network error occurred.', 'error');
        }
    }

    function initDeptChart(data) {
        const ctx = document.getElementById('deptChart').getContext('2d');
        const theme = getChartTheme();
        charts.dept = new Chart(ctx, {
            type: 'pie',
            data: {
                labels: data.map(d => d.name),
                datasets: [{
                    data: data.map(d => d.count),
                    backgroundColor: [
                        'rgba(54, 162, 235, 0.2)',
                        'rgba(75, 192, 192, 0.2)',
                        'rgba(255, 206, 86, 0.2)',
                        'rgba(153, 102, 255, 0.2)',
                        'rgba(255, 159, 64, 0.2)',
                        'rgba(201, 203, 207, 0.2)'
                    ],
                    borderColor: [
                        'rgba(54, 162, 235, 1)',
                        'rgba(75, 192, 192, 1)',
                        'rgba(255, 206, 86, 1)',
                        'rgba(153, 102, 255, 1)',
                        'rgba(255, 159, 64, 1)',
                        'rgba(201, 203, 207, 1)'
                    ],
                    borderWidth: 1.5
                }]
            },
            options: {
                maintainAspectRatio: false,
                responsive: true,
                plugins: {
                    legend: { position: 'bottom', labels: { color: theme.textColor } }
                }
            }
        });
    }

    function initDesigChart(data) {
        const ctx = document.getElementById('desigChart').getContext('2d');
        const theme = getChartTheme();
        charts.desig = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: data.map(d => d.name),
                datasets: [{
                    label: 'Employees',
                    data: data.map(d => d.count),
                    backgroundColor: 'rgba(75, 192, 192, 0.2)',
                    borderColor: 'rgba(75, 192, 192, 1)',
                    borderWidth: 1.5,
                    borderRadius: 5
                }]
            },
            options: {
                indexAxis: 'y',
                maintainAspectRatio: false,
                responsive: true,
                plugins: { legend: { display: false } },
                scales: {
                    x: { grid: { color: theme.gridColor }, ticks: { color: theme.textColor } },
                    y: { grid: { color: theme.gridColor }, ticks: { color: theme.textColor } }
                }
            }
        });
    }

    function initAttendanceChart(data) {
        const ctx = document.getElementById('attendanceChart').getContext('2d');
        const theme = getChartTheme();
        charts.attendance = new Chart(ctx, {
            type: 'line',
            data: {
                labels: data.labels,
                datasets: [{
                    label: 'Presence %',
                    data: data.data,
                    fill: true,
                    borderColor: 'rgba(75, 192, 192, 1)',
                    backgroundColor: 'rgba(75, 192, 192, 0.2)',
                    tension: 0.3,
                    borderWidth: 1.5
                }]
            },
            options: {
                maintainAspectRatio: false,
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true,
                        max: 100,
                        grid: { color: theme.gridColor },
                        ticks: { color: theme.textColor, callback: v => v + '%' }
                    },
                    x: {
                        grid: { color: theme.gridColor },
                        ticks: { color: theme.textColor }
                    }
                },
                plugins: {
                    legend: { labels: { color: theme.textColor } }
                }
            }
        });
    }

    function initRecruitmentChart(data) {
        const ctx = document.getElementById('recruitmentChart').getContext('2d');
        const theme = getChartTheme();
        const labels = Object.keys(data);
        charts.recruitment = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [{
                    data: Object.values(data),
                    backgroundColor: [
                        'rgba(54, 162, 235, 0.2)',
                        'rgba(75, 192, 192, 0.2)',
                        'rgba(255, 206, 86, 0.2)',
                        'rgba(153, 102, 255, 0.2)',
                        'rgba(255, 159, 64, 0.2)'
                    ],
                    borderColor: [
                        'rgba(54, 162, 235, 1)',
                        'rgba(75, 192, 192, 1)',
                        'rgba(255, 206, 86, 1)',
                        'rgba(153, 102, 255, 1)',
                        'rgba(255, 159, 64, 1)'
                    ],
                    borderWidth: 1.5
                }]
            },
            options: {
                maintainAspectRatio: false,
                responsive: true,
                plugins: { legend: { display: false } },
                scales: {
                    x: { grid: { color: theme.gridColor }, ticks: { color: theme.textColor } },
                    y: { grid: { color: theme.gridColor }, ticks: { color: theme.textColor } }
                }
            }
        });
    }

    function initPayrollChart(data) {
        const ctx = document.getElementById('payrollChart').getContext('2d');
        const theme = getChartTheme();
        charts.payroll = new Chart(ctx, {
            type: 'line',
            data: {
                labels: data.labels,
                datasets: [{
                    label: 'Total Payout',
                    data: data.data,
                    borderColor: 'rgba(54, 162, 235, 1)',
                    backgroundColor: 'rgba(54, 162, 235, 0.2)',
                    fill: true,
                    tension: 0.3,
                    borderWidth: 1.5
                }]
            },
            options: {
                maintainAspectRatio: false,
                responsive: true,
                scales: {
                    x: { grid: { color: theme.gridColor }, ticks: { color: theme.textColor } },
                    y: { grid: { color: theme.gridColor }, ticks: { color: theme.textColor } }
                },
                plugins: {
                    legend: { labels: { color: theme.textColor } }
                }
            }
        });
    }

    function initLeaveChart(data) {
        const ctx = document.getElementById('leaveChart').getContext('2d');
        const theme = getChartTheme();
        charts.leave = new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: Object.keys(data),
                datasets: [{
                    data: Object.values(data),
                    backgroundColor: [
                        'rgba(75, 192, 192, 0.2)',
                        'rgba(255, 206, 86, 0.2)',
                        'rgba(255, 99, 132, 0.2)',
                        'rgba(201, 203, 207, 0.2)'
                    ],
                    borderColor: [
                        'rgba(75, 192, 192, 1)',
                        'rgba(255, 206, 86, 1)',
                        'rgba(255, 99, 132, 1)',
                        'rgba(201, 203, 207, 1)'
                    ],
                    borderWidth: 1.5
                }]
            },
            options: {
                maintainAspectRatio: false,
                responsive: true,
                plugins: {
                    legend: { position: 'bottom', labels: { color: theme.textColor } }
                }
            }
        });
    }

    window.downloadPDF = async function () {
        const { jsPDF } = window.jspdf;
        const pdfBtn = document.querySelector('button[onclick="downloadPDF()"]');
        const originalTheme = document.documentElement.getAttribute('data-bs-theme');

        // UI Feedback
        if (pdfBtn) {
            pdfBtn.disabled = true;
            pdfBtn.innerHTML = '<i class="ti ti-loader animate-spin me-1"></i> Generating...';
        }

        try {
            showToast('Preparing PDF...', 'info');

            // 1. Force Light Mode for clean printing
            document.documentElement.setAttribute('data-bs-theme', 'light');
            // Wait for Chart.js theme updates to propagate (MutationObserver)
            await new Promise(resolve => setTimeout(resolve, 800));

            const pdf = new jsPDF('p', 'mm', 'a4');
            const pageWidth = pdf.internal.pageSize.getWidth();
            const pageHeight = pdf.internal.pageSize.getHeight();
            const margin = 15;
            const usefulWidth = pageWidth - (margin * 2);
            const dateStr = new Date().toLocaleDateString(undefined, { year: 'numeric', month: 'long', day: 'numeric' });

            // Helper to add header
            const addHeader = () => {
                pdf.setFontSize(16);
                pdf.setTextColor(40, 40, 40);
                pdf.text("Company Reports & Analytics", margin, 15);

                pdf.setFontSize(10);
                pdf.setTextColor(100, 100, 100);
                pdf.text(`Generated on: ${dateStr}`, pageWidth - margin - 40, 15);

                pdf.setDrawColor(200, 200, 200);
                pdf.line(margin, 20, pageWidth - margin, 20);
            };

            const chartsToCapture = [
                { id: 'deptChartParent', title: 'Employee Distribution by Department' },
                { id: 'desigChartParent', title: 'Employee Distribution by Designation' },
                { id: 'attendanceChartParent', title: 'Daily Presence Trend (Last 15 Days)' },
                { id: 'recruitmentChartParent', title: 'Recruitment Pipeline' },
                { id: 'payrollChartParent', title: 'Payroll Expenditure (Last 6 Months)' },
                { id: 'leaveChartParent', title: 'Leave Status Distribution' }
            ];

            let yOffset = 30; // Start below header
            let pageNum = 1;

            addHeader();

            for (let i = 0; i < chartsToCapture.length; i++) {
                const item = chartsToCapture[i];
                const target = document.querySelector(`#${item.id}`);

                // Capture high-res image
                const canvas = await html2canvas(target, {
                    scale: 2,
                    backgroundColor: '#ffffff',
                    padding: 10
                });
                const imgData = canvas.toDataURL('image/png');

                const imgProps = pdf.getImageProperties(imgData);
                const pdfImgWidth = usefulWidth;
                const pdfImgHeight = (imgProps.height * pdfImgWidth) / imgProps.width;

                // Check page break
                if (yOffset + pdfImgHeight + 15 > pageHeight - margin) {
                    pdf.addPage();
                    pageNum++;
                    addHeader();
                    yOffset = 30;
                }

                // Add Chart Title
                pdf.setFont("helvetica", "bold");
                pdf.setFontSize(12);
                pdf.setTextColor(0, 0, 0);
                pdf.text(item.title, margin, yOffset);
                yOffset += 7;

                // Add Chart Image
                pdf.addImage(imgData, 'PNG', margin, yOffset, pdfImgWidth, pdfImgHeight);
                yOffset += pdfImgHeight + 15; // Spacing

                // Add footer page num
                pdf.setFont("helvetica", "normal");
                pdf.setFontSize(8);
                pdf.setTextColor(150, 150, 150);
                pdf.text(`Page ${pageNum}`, pageWidth / 2, pageHeight - 10, { align: 'center' });
            }

            pdf.save(`Company_Reports_${new Date().toISOString().split('T')[0]}.pdf`);
            showToast('PDF downloaded successfully!', 'success');

        } catch (error) {
            console.error('PDF Generation Error:', error);
            showToast('Failed to generate PDF.', 'error');
        } finally {
            // Restore Original Theme and Button
            document.documentElement.setAttribute('data-bs-theme', originalTheme);
            if (pdfBtn) {
                pdfBtn.disabled = false;
                pdfBtn.innerHTML = '<i class="ti ti-file-type-pdf me-1"></i>Download PDF Report';
            }
        }
    };
</script>