<?php
require_once '../config/db.php';
require_once '../includes/functions.php';
$title = "Attendance Management";

if (!isLoggedIn() || !in_array($_SESSION['role_id'], [2, 3])) {
    redirect("/hrms/pages/unauthorized.php");
}
require_once '../components/layout/header.php';
?>

<div class="d-flex">
    <?php require_once '../components/layout/sidebar.php'; ?>
    <div class="p-3 p-md-4 flex-grow-1">
        <div class="card shadow-sm mb-4">
            <div class="card-header d-flex flex-wrap justify-content-between align-items-center">
                <div class="d-flex align-items-center my-1">
                    <button id="prevMonth" class="btn btn-sm btn-outline-secondary"><i
                            class="ti ti-chevron-left"></i></button>
                    <h5 id="currentMonth" class="m-0 mx-3"></h5>
                    <button id="nextMonth" class="btn btn-sm btn-outline-secondary"><i
                            class="ti ti-chevron-right"></i></button>
                </div>
                <div class="d-flex align-items-center flex-wrap gap-2 my-1">
                    <div class="badge bg-info-subtle text-info-emphasis"><i
                            class="ti ti-calendar-off me-1"></i>Holidays:
                        <span id="holidayBadge">0</span>
                    </div>
                    <button class="btn bg-dark-subtle btn-sm" id="openBulkModalBtn"><i
                            class="ti ti-loader-3 me-2"></i>Bulk
                        Actions</button>
                    <input type="search" id="employeeSearch" class="form-control form-control-sm"
                        placeholder="Search Department or Employee..." style="width: 200px;">
                </div>
            </div>
        </div>

        <div class="accordion shadow-sm mb-4" id="dashboardAccordion">
            <div class="accordion-item">
                <h2 class="accordion-header">
                    <button class="accordion-button" type="button" data-bs-toggle="collapse"
                        data-bs-target="#statsCollapse">
                        <i class="ti ti-chart-bar me-2"></i> <strong>Dashboard Statistics</strong>
                    </button>
                </h2>
                <div id="statsCollapse" class="accordion-collapse collapse show" data-bs-parent="#dashboardAccordion">
                    <div class="accordion-body">
                        <div class="row" id="dashboardStats"></div>
                    </div>
                </div>
            </div>

            <div class="accordion-item">
                <h2 class="accordion-header">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                        data-bs-target="#chartsCollapse">
                        <i class="ti ti-chart-line me-2"></i> <strong>Analytics & Charts</strong>
                    </button>
                </h2>
                <div id="chartsCollapse" class="accordion-collapse collapse" data-bs-parent="#dashboardAccordion">
                    <div class="accordion-body">
                        <div class="row">
                            <div class="col-lg-6 mb-3">
                                <div class="card shadow-sm">
                                    <div class="card-header">
                                        <h6 class="m-0 font-weight-bold">Attendance by Status</h6>
                                    </div>
                                    <div class="card-body" style="height: 300px;" id="statusChartBody">
                                        <canvas id="attendanceStatusChart"></canvas>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-6 mb-3">
                                <div class="card shadow-sm">
                                    <div class="card-header">
                                        <h6 class="m-0 font-weight-bold">Department Attendance %</h6>
                                    </div>
                                    <div class="card-body" style="height: 300px;" id="deptChartBody">
                                        <canvas id="departmentAttendanceChart"></canvas>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-lg-4 mb-3">
                                <div class="card shadow-sm">
                                    <div class="card-header">
                                        <h6 class="m-0 font-weight-bold">Attendance Distribution</h6>
                                    </div>
                                    <div class="card-body" style="height: 300px;" id="distChartBody">
                                        <canvas id="attendanceDistributionChart"></canvas>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-4 mb-3">
                                <div class="card shadow-sm">
                                    <div class="card-header">
                                        <h6 class="m-0 font-weight-bold">Daily Trend</h6>
                                    </div>
                                    <div class="card-body" style="height: 300px;" id="trendChartBody">
                                        <canvas id="dailyTrendChart"></canvas>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-4 mb-3">
                                <div class="card shadow-sm">
                                    <div class="card-header">
                                        <h6 class="m-0 font-weight-bold">Top Employees (Attendance)</h6>
                                    </div>
                                    <div class="card-body" style="height: 300px;" id="topEmpChartBody">
                                        <canvas id="topEmployeesChart"></canvas>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Spinner removed -->
        <div id="loadingSpinner" class="text-center p-5" style="display:none"></div>
        <div class="attendance-grid" id="departmentGrid" style="display:none;"></div>
        <div class="attendance-grid" id="employeeGrid" style="display:none;"></div>
        <div id="noResults" class="alert alert-info text-center" style="display:none;"><i
                class="ti ti-info-circle me-2"></i>No attendance records found for the selected month.</div>

        <!-- Back Button (Hidden by default) -->
        <div id="backToDepartments" style="display:none;" class="mb-3">
            <button class="btn btn-secondary btn-sm"><i class="ti ti-arrow-left me-2"></i>Back to
                Departments</button>
        </div>
    </div>
</div>

<div class="modal fade" id="attendanceModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="attendanceForm">
                <div class="modal-header">
                    <h5 class="modal-title" id="attendanceModalLabel">Update Attendance</h5><button type="button"
                        class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body"><input type="hidden" name="action" value="update_attendance"><input
                        type="hidden" name="employee_id" id="modalEmployeeId"><input type="hidden" name="date"
                        id="modalDate">
                    <p>Select status for <strong id="modalEmployeeName"></strong> on <strong
                            id="modalDateDisplay"></strong>:</p>
                    <div class="d-grid gap-2" id="statusButtons"><button type="submit" name="status" value="present"
                            class="btn btn-success bg-success-subtle text-success-emphasis"><i
                                class="ti ti-check me-2"></i>Present</button><button type="submit" name="status"
                            value="absent" class="btn btn-danger bg-danger-subtle text-danger-emphasis"><i
                                class="ti ti-x me-2"></i>Absent</button><button type="submit" name="status"
                            value="half-day" class="btn btn-info  bg-info-subtle text-info-emphasis"><i
                                class="ti ti-subtract me-2"></i>Half
                            Day</button><button type="submit" name="status" value="leave"
                            class="btn btn-warning bg-warning-subtle text-warning-emphasis"><i
                                class="ti ti-plane me-2"></i>On
                            Leave</button></div>
                </div>
            </form>
        </div>
    </div>
</div>
<div class="modal fade" id="bulkActionsModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Bulk Actions</h5><button type="button" class="btn-close"
                    data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Select a date and mark it as a holiday for all employees in this company.</p>
                <div class="input-group"><input type="date" id="bulkHolidayDate" class="form-control"><button
                        class="btn btn-primary" id="bulkHolidayBtn">Apply</button></div>
            </div>
        </div>
    </div>
</div>

<?php require_once '../components/layout/footer.php'; ?>

<script>
    let currentMonth;
    let allAttendanceData = null;
    let currentViewMode = 'departments';
    let selectedDepartmentId = null;
    let attendanceStatusChartInstance = null;
    let departmentAttendanceChartInstance = null;
    let attendanceDistributionChartInstance = null;
    let dailyTrendChartInstance = null;
    let topEmployeesChartInstance = null;
    const attendanceModal = new bootstrap.Modal('#attendanceModal');
    const bulkActionsModal = new bootstrap.Modal('#bulkActionsModal');

    $(function () {
        currentMonth = new Date();
        loadAttendanceData();

        $('#prevMonth').on('click', () => { currentMonth.setMonth(currentMonth.getMonth() - 1); loadAttendanceData(); });
        $('#nextMonth').on('click', () => { currentMonth.setMonth(currentMonth.getMonth() + 1); loadAttendanceData(); });
        $('#employeeSearch').on('input', function () { filterCards($(this).val()); });
        $('#openBulkModalBtn').on('click', () => bulkActionsModal.show());
        $('#backToDepartments').on('click', 'button', goBackToDepartments);

        $('#bulkHolidayBtn').on('click', function () {
            const date = $('#bulkHolidayDate').val();
            if (!date) { showToast('Please select a date.', 'error'); return; }
            if (confirm(`Mark ${date} as a holiday for all employees? This will override existing statuses.`)) {
                const formData = new FormData();
                formData.append('action', 'bulk_update');
                formData.append('date', date);
                formData.append('status', 'holiday');
                fetch('/hrms/api/api_attendance.php', { method: 'POST', body: formData }).then(r => r.json()).then(res => {
                    if (res.success) { showToast(res.message, 'success'); bulkActionsModal.hide(); loadAttendanceData(); }
                    else { showToast(res.message, 'error'); }
                });
            }
        });

        $('#attendanceForm').on('submit', function (e) {
            e.preventDefault();
            const status = e.originalEvent.submitter.value;
            if (!status) return;
            const formData = new FormData(this);
            formData.append('status', status);
            fetch('/hrms/api/api_attendance.php', { method: 'POST', body: formData }).then(r => r.json()).then(data => {
                if (data.success) {
                    showToast(data.message, 'success');
                    attendanceModal.hide();
                    const monthString = currentMonth.toISOString().slice(0, 7);
                    fetch(`/hrms/api/api_attendance.php?action=get_attendance_data&month=${monthString}`)
                        .then(res => res.json())
                        .then(result => {
                            if (result.error || !result.employees) {
                                return;
                            }
                            allAttendanceData = result;
                            if (currentViewMode === 'employees') {
                                const deptEmployees = result.employees.filter(emp => (emp.department_id || 'unassigned') === selectedDepartmentId);
                                renderEmployeeCards({
                                    employees: deptEmployees,
                                    month_details: result.month_details,
                                    company_holidays: result.company_holidays,
                                    saturday_policy: result.saturday_policy,
                                    employee_leaves: result.employee_leaves,
                                    company_created_at: result.company_created_at
                                });
                            } else {
                                renderDepartmentCards(result);
                            }
                        })
                        .catch(err => { });
                }
                else { showToast(data.message || 'An error occurred.', 'error'); }
            }).catch(err => { });
        });

        $(document).on('click', '.day-square:not(.status-disabled):not(.empty)', function () {
            const el = $(this);
            openAttendanceModal(el.data('employee-id'), el.data('employee-name'), el.data('date'));
        });

        $(document).on('click', '.dept-card', function () {
            const deptId = $(this).data('department-id');
            const deptName = $(this).data('department-name');
            showEmployeeView(deptId, deptName);
        });
    });

    function loadAttendanceData() {
        const monthString = currentMonth.toISOString().slice(0, 7);
        $('#currentMonth').text(currentMonth.toLocaleString('default', { month: 'long', year: 'numeric' }));

        // Skeleton Loading
        SkeletonFactory.show('#dashboardStats', 'stat-card', 4);
        SkeletonFactory.replace('#statusChartBody', 'rect', { size: 'sk-rect-xl', animation: 'pulse' });
        SkeletonFactory.replace('#deptChartBody', 'rect', { size: 'sk-rect-xl', animation: 'pop' }); // varied animation
        SkeletonFactory.replace('#distChartBody', 'rect', { size: 'sk-rect-xl', animation: 'shimmer' });
        SkeletonFactory.replace('#trendChartBody', 'rect', { size: 'sk-rect-xl' });
        SkeletonFactory.replace('#topEmpChartBody', 'rect', { size: 'sk-rect-xl' });

        // For grids, we don't know if we will show them until data loads, but we can show stats skeleton
        // Actually, we can show a placeholder grid or just let stats load first since they are key.

        $('#departmentGrid, #employeeGrid, #noResults').hide();
        currentViewMode = 'departments';
        selectedDepartmentId = null;
        $('#backToDepartments').hide();

        const restoreSkeletons = async () => {
            await Promise.all([
                SkeletonFactory.hide('#dashboardStats'),
                SkeletonFactory.restore('#statusChartBody'),
                SkeletonFactory.restore('#deptChartBody'),
                SkeletonFactory.restore('#distChartBody'),
                SkeletonFactory.restore('#trendChartBody'),
                SkeletonFactory.restore('#topEmpChartBody')
            ]);
        };

        fetch(`/hrms/api/api_attendance.php?action=get_attendance_data&month=${monthString}`)
            .then(res => res.json()).then(async result => {
                await restoreSkeletons();

                if (result.error || !result.employees) { showToast(result.error || 'Failed to load data.', 'error'); $('#noResults').show(); return; }
                allAttendanceData = result;

                const viewingDate = new Date(result.month_details.year, result.month_details.month - 1, 1);
                const companyCreationDate = new Date(result.company_created_at);
                const today = new Date();
                today.setHours(0, 0, 0, 0);
                const firstDayOfCurrentMonth = new Date(today.getFullYear(), today.getMonth(), 1);

                if (viewingDate < companyCreationDate && viewingDate.getMonth() !== companyCreationDate.getMonth()) {
                    $('#noResults').html('<i class="ti ti-info-circle me-2"></i>Attendance tracking began after your company was created.').show();
                    $('#dashboardAccordion').hide();
                    return;
                }

                if (viewingDate > firstDayOfCurrentMonth) {
                    $('#noResults').html('<i class="ti ti-info-circle me-2"></i>No attendance data available for upcoming months.').show();
                    $('#dashboardAccordion').hide();
                    return;
                }

                $('#dashboardAccordion').show();
                renderDashboard(result.summary);
                renderDepartmentCards(result);
                $('#employeeSearch').val('');
            }).catch(async err => {
                await restoreSkeletons();
                showToast('A network error occurred.', 'error');
                $('#noResults').show();
            });
    }


    function renderDashboard(summary) {
        const stats = [
            { label: 'Attendance Rate', value: `${summary.overall_percentage}%`, color: 'primary-subtle', icon: 'square-rounded-percentage', textColor: 'primary' },
            { label: 'Total Present', value: summary.total_present, color: 'success-subtle', icon: 'circle-check', textColor: 'success' },
            { label: 'Total Absent', value: summary.total_absent, color: 'danger-subtle', icon: 'square-rounded-x', textColor: 'danger' },
            { label: 'Total On Leave', value: summary.total_leave, color: 'warning-subtle', icon: 'plane', textColor: 'warning' }
        ];
        let statsHtml = '';
        stats.forEach(stat => {
            statsHtml += `<div class="col-xl-3 col-md-6 mb-3">
                <div class="card shadow-sm bg-${stat.color}">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <div class="text-xs font-weight-bold text-uppercase mb-1">${stat.label}</div>
                                <div class="h5 mb-0 font-weight-bold" style="color: var(--bs-${stat.textColor});">${stat.value}</div>
                            </div>
                            <i class="ti ti-${stat.icon} text-${stat.textColor}" style="font-size: 2.5rem; opacity: 0.5;"></i>
                        </div>
                    </div>
                </div>
            </div>`;
        });
        $('#dashboardStats').html(statsHtml);

        // Render all charts
        renderAttendanceCharts(summary);
        renderDistributionChart(summary);
        renderDailyTrendChart();
        renderTopEmployeesChart();
    }

    function renderAttendanceCharts(summary) {
        const statusCtx = document.getElementById('attendanceStatusChart');
        if (statusCtx) {
            const ctx = statusCtx.getContext('2d');
            if (attendanceStatusChartInstance) {
                attendanceStatusChartInstance.destroy();
            }
            attendanceStatusChartInstance = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: ['Present', 'Absent', 'Half-Day', 'Leave'],
                    datasets: [{
                        label: 'Count',
                        data: [
                            summary.total_present || 0,
                            summary.total_absent || 0,
                            summary.total_half_day || 0,
                            summary.total_leave || 0
                        ],
                        backgroundColor: [
                            '#8BAE66', '#F2613F', '#007880', '#FEC260'
                        ]
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    indexAxis: 'y',
                    plugins: { legend: { display: false } },
                    scales: { x: { beginAtZero: true } }
                }
            });
        }

        // Department Attendance Chart (will be populated when we have department data)
        const deptCtx = document.getElementById('departmentAttendanceChart');
        if (deptCtx && allAttendanceData && allAttendanceData.employees) {
            const ctx = deptCtx.getContext('2d');
            if (departmentAttendanceChartInstance) {
                departmentAttendanceChartInstance.destroy();
            }

            // Calculate attendance percentage by department
            const deptMap = {};
            allAttendanceData.employees.forEach(emp => {
                const deptId = emp.department_id || 'unassigned';
                const deptName = emp.department_name || 'Unassigned';
                if (!deptMap[deptId]) {
                    deptMap[deptId] = { name: deptName, present: 0, total: 0 };
                }
                Object.entries(emp.attendance).forEach(([date, record]) => {
                    if (record.status === 'present' || record.status === 'half-day') {
                        deptMap[deptId].present += record.status === 'half-day' ? 0.5 : 1;
                    }
                    deptMap[deptId].total += 1;
                });
            });

            const deptLabels = Object.values(deptMap).map(d => d.name);
            const deptPercentages = Object.values(deptMap).map(d => d.total > 0 ? Math.round((d.present / d.total) * 100) : 0);

            departmentAttendanceChartInstance = new Chart(ctx, {
                type: 'doughnut',
                data: {
                    labels: deptLabels,
                    datasets: [{
                        data: deptPercentages,
                        backgroundColor: [
                            '#8BAE66', '#007880', '#FEC260', '#F2613F', '#6d68de', '#fd7e14'
                        ],
                        borderColor: '#fff',
                        borderWidth: 2
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { position: 'bottom' },
                        tooltip: {
                            callbacks: {
                                label: function (context) {
                                    return context.label + ': ' + context.parsed + '%';
                                }
                            }
                        }
                    }
                }
            });
        }
    }

    function renderDistributionChart(summary) {
        const distCtx = document.getElementById('attendanceDistributionChart');
        if (distCtx) {
            const ctx = distCtx.getContext('2d');
            if (attendanceDistributionChartInstance) {
                attendanceDistributionChartInstance.destroy();
            }
            attendanceDistributionChartInstance = new Chart(ctx, {
                type: 'doughnut',
                data: {
                    labels: ['Present', 'Absent', 'Half-Day', 'Leave'],
                    datasets: [{
                        data: [
                            summary.total_present || 0,
                            summary.total_absent || 0,
                            summary.total_half_day || 0,
                            summary.total_leave || 0
                        ],
                        backgroundColor: [
                            '#8BAE66', '#F2613F', '#007880', '#FEC260'
                        ],
                        borderColor: '#fff',
                        borderWidth: 2
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { position: 'bottom' }
                    }
                }
            });
        }
    }

    function renderDailyTrendChart() {
        const trendCtx = document.getElementById('dailyTrendChart');
        if (trendCtx && allAttendanceData) {
            const ctx = trendCtx.getContext('2d');
            if (dailyTrendChartInstance) {
                dailyTrendChartInstance.destroy();
            }

            // Calculate daily attendance counts
            const dailyMap = {};
            const days = allAttendanceData.month_details.days_in_month;
            for (let day = 1; day <= days; day++) {
                const dateObj = new Date(allAttendanceData.month_details.year, allAttendanceData.month_details.month - 1, day);
                const dateStr = dateObj.toISOString().slice(0, 10);
                dailyMap[dateStr] = { present: 0, absent: 0 };
            }

            allAttendanceData.employees.forEach(emp => {
                Object.entries(emp.attendance).forEach(([date, record]) => {
                    if (dailyMap[date]) {
                        if (record.status === 'present' || record.status === 'half-day') {
                            dailyMap[date].present++;
                        } else if (record.status === 'absent') {
                            dailyMap[date].absent++;
                        }
                    }
                });
            });

            const dates = Object.keys(dailyMap).sort();
            const presentData = dates.map(d => dailyMap[d].present);
            const absentData = dates.map(d => dailyMap[d].absent);

            dailyTrendChartInstance = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: dates.map(d => new Date(d).getDate()),
                    datasets: [
                        {
                            label: 'Present',
                            data: presentData,
                            borderColor: '#8BAE66',
                            backgroundColor: 'rgba(117, 183, 152, 0.1)',
                            tension: 0.4
                        },
                        {
                            label: 'Absent',
                            data: absentData,
                            borderColor: '#F2613F',
                            backgroundColor: 'rgba(234, 133, 142, 0.1)',
                            tension: 0.4
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { position: 'top' }
                    },
                    scales: {
                        y: { beginAtZero: true }
                    }
                }
            });
        }
    }

    function renderTopEmployeesChart() {
        const empCtx = document.getElementById('topEmployeesChart');
        if (empCtx && allAttendanceData && allAttendanceData.employees) {
            const ctx = empCtx.getContext('2d');
            if (topEmployeesChartInstance) {
                topEmployeesChartInstance.destroy();
            }

            // Calculate attendance % for each employee
            const empAttendance = allAttendanceData.employees.map(emp => {
                let present = 0, total = 0;
                Object.entries(emp.attendance).forEach(([date, record]) => {
                    if (record.status === 'present' || record.status === 'half-day') {
                        present += record.status === 'half-day' ? 0.5 : 1;
                    }
                    total += 1;
                });
                return {
                    name: emp.name,
                    percentage: total > 0 ? Math.round((present / total) * 100) : 0
                };
            }).sort((a, b) => b.percentage - a.percentage).slice(0, 8);

            topEmployeesChartInstance = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: empAttendance.map(e => e.name),
                    datasets: [{
                        label: 'Attendance %',
                        data: empAttendance.map(e => e.percentage),
                        backgroundColor: '#8BAE66'
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    indexAxis: 'y',
                    plugins: {
                        legend: { display: false }
                    },
                    scales: {
                        x: { beginAtZero: true, max: 100 }
                    }
                }
            });
        }
    }

    function renderDepartmentCards(data) {
        const { employees, month_details, company_holidays, saturday_policy, employee_leaves, company_created_at } = data;
        const grid = $('#departmentGrid');
        grid.html('');

        // Display holidays count in header badge
        const totalMonthHolidays = Object.keys(company_holidays).length;
        $('#holidayBadge').text(totalMonthHolidays);

        // Re-render all charts with updated data
        renderDepartmentAttendanceChart();
        renderDailyTrendChart();
        renderTopEmployeesChart();

        // Group employees by department
        const departmentMap = {};
        employees.forEach(emp => {
            const deptId = emp.department_id || 'unassigned';
            const deptName = emp.department_name || 'Unassigned';
            if (!departmentMap[deptId]) {
                departmentMap[deptId] = { name: deptName, employees: [] };
            }
            departmentMap[deptId].employees.push(emp);
        });

        // Create placeholder cards for lazy loading
        const departmentEntries = Object.entries(departmentMap);
        departmentEntries.forEach(([deptId, deptData], index) => {
            const placeholderId = `dept-${deptId}`;
            const employeeCount = deptData.employees.length;
            const cardHtml = `
                <div class="mb-3" data-dept-id="${deptId}" id="${placeholderId}">
                    <div class="card shadow-sm dept-card" data-department-id="${deptId}" data-department-name="${deptData.name}">
                        <div class="card-body">
                            <div class="row mb-3">
                                <div class="col">
                                    <h6 class="mb-0 fw-bold">${deptData.name}</h6>
                                </div>
                                <div class="col-auto">
                                    <span class="badge bg-primary">${employeeCount}</span>
                                </div>
                            </div>
                            <div class="skeleton-loader"></div>
                        </div>
                    </div>
                </div>
            `;
            grid.append(cardHtml);
        });

        grid.show();
        $('#noResults').hide();

        // Implement Intersection Observer for lazy loading
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const deptId = entry.target.getAttribute('data-dept-id');
                    const deptData = departmentMap[deptId];

                    let totalPresent = 0, totalAbsent = 0, totalHalfDay = 0, totalLeave = 0;

                    deptData.employees.forEach(emp => {
                        Object.entries(emp.attendance).forEach(([dateStr, record]) => {
                            const status = record.status;
                            if (status === 'present') totalPresent++;
                            else if (status === 'absent') totalAbsent++;
                            else if (status === 'half-day') totalHalfDay++;
                            else if (status === 'leave') totalLeave++;
                        });

                        // Also count approved leaves that don't have an attendance record
                        if (employee_leaves[emp.id]) {
                            Object.keys(employee_leaves[emp.id]).forEach(dateStr => {
                                if (!emp.attendance[dateStr]) {
                                    totalLeave++;
                                }
                            });
                        }
                    });

                    const contentHtml = `
                        <div class="row g-2">
                            <div class="col">
                                <div class="text-center">
                                    <div class="fw-bold fs-6 text-success">${totalPresent}</div>
                                    <small class="text-muted d-block">Present</small>
                                </div>
                            </div>
                            <div class="col">
                                <div class="text-center">
                                    <div class="fw-bold fs-6 text-danger">${totalAbsent}</div>
                                    <small class="text-muted d-block">Absent</small>
                                </div>
                            </div>
                            <div class="col">
                                <div class="text-center">
                                    <div class="fw-bold fs-6 text-info">${totalHalfDay}</div>
                                    <small class="text-muted d-block">Half-day</small>
                                </div>
                            </div>
                            <div class="col">
                                <div class="text-center">
                                    <div class="fw-bold fs-6 text-warning">${totalLeave}</div>
                                    <small class="text-muted d-block">Leave</small>
                                </div>
                            </div>
                        </div>
                    `;

                    const skeletonEl = entry.target.querySelector('.skeleton-loader');
                    const tempDiv = document.createElement('div');
                    tempDiv.innerHTML = contentHtml;
                    skeletonEl.parentNode.replaceChild(tempDiv.firstElementChild, skeletonEl);
                    observer.unobserve(entry.target);
                }
            });
        }, { rootMargin: '50px' });

        document.querySelectorAll('[data-dept-id]').forEach(el => observer.observe(el));
    }

    function renderDepartmentAttendanceChart() {
        const deptCtx = document.getElementById('departmentAttendanceChart');
        if (deptCtx && allAttendanceData && allAttendanceData.employees) {
            const ctx = deptCtx.getContext('2d');
            if (departmentAttendanceChartInstance) {
                departmentAttendanceChartInstance.destroy();
            }

            // Calculate attendance percentage by department
            const deptMap = {};
            allAttendanceData.employees.forEach(emp => {
                const deptId = emp.department_id || 'unassigned';
                const deptName = emp.department_name || 'Unassigned';
                if (!deptMap[deptId]) {
                    deptMap[deptId] = { name: deptName, present: 0, total: 0 };
                }
                Object.entries(emp.attendance).forEach(([date, record]) => {
                    if (record.status === 'present' || record.status === 'half-day') {
                        deptMap[deptId].present += record.status === 'half-day' ? 0.5 : 1;
                    }
                    deptMap[deptId].total += 1;
                });
            });

            const deptLabels = Object.values(deptMap).map(d => d.name);
            const deptPercentages = Object.values(deptMap).map(d => d.total > 0 ? Math.round((d.present / d.total) * 100) : 0);

            departmentAttendanceChartInstance = new Chart(ctx, {
                type: 'doughnut',
                data: {
                    labels: deptLabels,
                    datasets: [{
                        data: deptPercentages,
                        backgroundColor: [
                            '#8BAE66', '#007880', '#FEC260', '#F2613F', '#6d68de', '#fd7e14'
                        ],
                        borderColor: '#fff',
                        borderWidth: 2
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { position: 'bottom' },
                        tooltip: {
                            callbacks: {
                                label: function (context) {
                                    return context.label + ': ' + context.parsed + '%';
                                }
                            }
                        }
                    }
                }
            });
        }
    }

    function showEmployeeView(departmentId, departmentName) {
        currentViewMode = 'employees';
        selectedDepartmentId = departmentId;
        $('#departmentGrid').hide();
        $('#backToDepartments').show();

        const deptEmployees = allAttendanceData.employees.filter(emp =>
            (emp.department_id || 'unassigned') === departmentId
        );

        renderEmployeeCards({
            employees: deptEmployees,
            month_details: allAttendanceData.month_details,
            company_holidays: allAttendanceData.company_holidays,
            saturday_policy: allAttendanceData.saturday_policy,
            employee_leaves: allAttendanceData.employee_leaves,
            company_created_at: allAttendanceData.company_created_at
        });
    }

    function goBackToDepartments() {
        currentViewMode = 'departments';
        selectedDepartmentId = null;
        $('#backToDepartments').hide();
        $('#employeeGrid').hide();
        $('#departmentGrid').show();
        $('#employeeSearch').val('');
    }

    function filterCards(query) {
        const lowerCaseQuery = query.toLowerCase();
        let visibleCount = 0;

        if (currentViewMode === 'departments') {
            $('.dept-card').each(function () {
                const deptName = $(this).data('department-name').toLowerCase();
                if (deptName.includes(lowerCaseQuery)) { $(this).closest('.mb-3').show(); visibleCount++; }
                else { $(this).closest('.mb-3').hide(); }
            });
        } else {
            $('.employee-card').each(function () {
                const employeeName = $(this).data('name');
                if (employeeName.includes(lowerCaseQuery)) { $(this).show(); visibleCount++; }
                else { $(this).hide(); }
            });
        }

        $('#searchNoResults').remove();
        if (visibleCount === 0 && ($('#departmentGrid').is(':visible') || $('#employeeGrid').is(':visible'))) {
            const gridId = currentViewMode === 'departments' ? '#departmentGrid' : '#employeeGrid';
            $(gridId).after('<div id="searchNoResults" class="alert alert-warning text-center">No results match your search.</div>');
        }
    }

    function isSaturdayHoliday(day, policy) {
        const weekNum = Math.ceil(day / 7);
        return policy === 'all' ||
            (policy === '1st_3rd' && (weekNum === 1 || weekNum === 3)) ||
            (policy === '2nd_4th' && (weekNum === 2 || weekNum === 4));
    }

    function renderEmployeeCards({ employees, month_details, company_holidays, saturday_policy, employee_leaves, company_created_at }) {
        const grid = $('#employeeGrid');
        grid.html('');

        const viewingDate = new Date(month_details.year, month_details.month - 1, 1);
        const companyCreationDate = new Date(company_created_at);

        if (viewingDate < companyCreationDate && viewingDate.getMonth() !== companyCreationDate.getMonth()) {
            $('#noResults').html('<i class="ti ti-info-circle me-2"></i>Attendance tracking began after your company was created.').show();
            grid.hide();
            return;
        }

        if (!employees || employees.length === 0) { $('#noResults').html('<i class="ti ti-info-circle me-2"></i>No employees found in this department.').show(); grid.hide(); return; }

        const today = new Date();
        today.setHours(0, 0, 0, 0);

        employees.forEach(emp => {
            let p = 0, a = 0, l = 0, h = 0, hd = 0;
            let calendarHtml = '';

            const firstDayOfMonth = new Date(month_details.year, month_details.month - 1, 1);
            const startDayOffset = firstDayOfMonth.getDay();
            for (let i = 0; i < startDayOffset; i++) {
                calendarHtml += `<div class="day-square empty"></div>`;
            }

            for (let day = 1; day <= month_details.days_in_month; day++) {
                // Create date in local timezone and convert to YYYY-MM-DD without timezone conversion
                const dateObj = new Date(month_details.year, month_details.month - 1, day);
                // Use local date components instead of ISO string to avoid timezone shifts
                const year = dateObj.getFullYear();
                const month = String(dateObj.getMonth() + 1).padStart(2, '0');
                const date = String(dateObj.getDate()).padStart(2, '0');
                const dateStr = `${year}-${month}-${date}`;

                let status = emp.attendance[dateStr]?.status || 'empty';
                let classes = 'day-square';
                let title = '';

                if (company_holidays[dateStr]) {
                    status = 'holiday';
                    title = company_holidays[dateStr];
                } else if (employee_leaves[emp.id]?.[dateStr]) {
                    status = 'leave';
                }

                const dayOfWeek = dateObj.getDay();
                const isWeekendOff = (dayOfWeek === 0) || (dayOfWeek === 6 && isSaturdayHoliday(day, saturday_policy));

                const companyCreatedAtDate = company_created_at.split('T')[0];

                // Can only be disabled for: future dates, before joining date, before company creation, weekends (with no recorded attendance), holidays, or leaves
                const isDisabled = dateObj > today ||
                    dateStr < emp.date_of_joining ||
                    dateStr < companyCreatedAtDate ||
                    (isWeekendOff && status === 'empty') ||
                    (status === 'holiday' && !emp.attendance[dateStr]) ||
                    (status === 'leave' && !emp.attendance[dateStr]);

                classes += ` status-${status}`;
                if (isWeekendOff && status === 'empty') {
                    classes += ' status-disabled';
                    if (dayOfWeek === 0) classes += ' status-sunday';
                }
                if (isDisabled) {
                    classes += ' status-disabled';
                }

                if (status !== 'empty') {
                    if (status === 'present') p++; else if (status === 'absent') a++;
                    else if (status === 'leave') l++; else if (status === 'holiday') h++;
                    else if (status === 'half-day') hd++;
                }

                calendarHtml += `<div class="${classes.trim()}" title="${title}" data-date="${dateStr}" data-employee-id="${emp.id}" data-employee-name="${emp.name}">${day}</div>`;
            }

            const cardHtml = `<div class="card shadow-sm hover-shadow-lg employee-card h-100" data-name="${emp.name.toLowerCase()}"><div class="card-body d-flex flex-column"><div><h6 class="mb-0 fw-bold text-primary">${emp.name}</h6><small class="text-body-secondary">${emp.designation || 'N/A'}</small></div><div class="d-flex justify-content-around text-center small my-3"><div><strong class="text-success">${p}</strong><br><span>P</span></div><div><strong class="text-danger">${a}</strong><br><span>A</span></div><div><strong class="text-primary">${hd}</strong><br><span>HD</span></div><div><strong class="text-warning">${l}</strong><br><span>L</span></div><div><strong class="text-info">${h}</strong><br><span>H</span></div></div><div class="mini-cal mt-auto">${calendarHtml}</div></div></div>`;
            grid.append(cardHtml);
        });

        grid.show();
        $('#noResults').hide();
    }

    function openAttendanceModal(employeeId, employeeName, date) {
        $('#modalEmployeeId').val(employeeId);
        $('#modalEmployeeName').text(employeeName);
        $('#modalDate').val(date);
        $('#modalDateDisplay').text(new Date(date + 'T00:00:00').toLocaleDateString('en-GB', { day: 'numeric', month: 'long', year: 'numeric' }));
        attendanceModal.show();
    }
</script>