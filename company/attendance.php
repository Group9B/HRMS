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
                            class="fas fa-chevron-left"></i></button>
                    <h5 id="currentMonth" class="m-0 mx-3"></h5>
                    <button id="nextMonth" class="btn btn-sm btn-outline-secondary"><i
                            class="fas fa-chevron-right"></i></button>
                </div>
                <div class="d-flex align-items-center flex-wrap gap-2 my-1">
                    <button class="btn bg-dark-subtle btn-sm" id="openBulkModalBtn"><i
                            class="fas fa-gifts me-2"></i>Bulk
                        Actions</button>
                    <input type="search" id="employeeSearch" class="form-control form-control-sm"
                        placeholder="Search Employee..." style="width: 200px;">
                </div>
            </div>
        </div>

        <div class="row attendance-dashboard mb-4" id="dashboardStats"></div>
        <div id="loadingSpinner" class="text-center p-5">
            <div class="spinner-border text-primary" role="status" style="width: 3rem; height: 3rem;"><span
                    class="visually-hidden">Loading...</span></div>
        </div>
        <div class="attendance-grid" id="attendanceGrid" style="display:none;"></div>
        <div id="noResults" class="alert alert-info text-center" style="display:none;"><i
                class="fas fa-info-circle me-2"></i>No attendance records found for the selected month.</div>
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
                                class="fas fa-check me-2"></i>Present</button><button type="submit" name="status"
                            value="absent" class="btn btn-danger bg-danger-subtle text-danger-emphasis"><i
                                class="fas fa-times me-2"></i>Absent</button><button type="submit" name="status"
                            value="half-day" class="btn btn-info  bg-info-subtle text-info-emphasis"><i
                                class="fas fa-star-half-alt me-2"></i>Half
                            Day</button><button type="submit" name="status" value="leave"
                            class="btn btn-warning bg-warning-subtle text-warning-emphasis"><i
                                class="fas fa-plane-departure me-2"></i>On
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
    const attendanceModal = new bootstrap.Modal('#attendanceModal');
    const bulkActionsModal = new bootstrap.Modal('#bulkActionsModal');

    $(function () {
        currentMonth = new Date();
        loadAttendanceData();

        $('#prevMonth').on('click', () => { currentMonth.setMonth(currentMonth.getMonth() - 1); loadAttendanceData(); });
        $('#nextMonth').on('click', () => { currentMonth.setMonth(currentMonth.getMonth() + 1); loadAttendanceData(); });
        $('#employeeSearch').on('input', function () { filterEmployeeCards($(this).val()); });
        $('#openBulkModalBtn').on('click', () => bulkActionsModal.show());

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
                if (data.success) { showToast(data.message, 'success'); attendanceModal.hide(); loadAttendanceData(); }
                else { showToast(data.message || 'An error occurred.', 'error'); }
            });
        });

        $('#attendanceGrid').on('click', '.day-square:not(.status-disabled)', function () {
            const el = $(this);
            openAttendanceModal(el.data('employee-id'), el.data('employee-name'), el.data('date'));
        });
    });

    function loadAttendanceData() {
        const monthString = currentMonth.toISOString().slice(0, 7);
        $('#currentMonth').text(currentMonth.toLocaleString('default', { month: 'long', year: 'numeric' }));
        $('#loadingSpinner').show();
        $('#attendanceGrid, #noResults').hide();
        $('#dashboardStats').empty();

        fetch(`/hrms/api/api_attendance.php?action=get_attendance_data&month=${monthString}`)
            .then(res => res.json()).then(result => {
                if (result.error || !result.employees) { showToast(result.error || 'Failed to load data.', 'error'); $('#noResults').show(); return; }
                renderDashboard(result.summary);
                renderEmployeeCards(result);
                $('#employeeSearch').val('');
            }).catch(err => { showToast('A network error occurred.', 'error'); $('#noResults').show(); })
            .finally(() => { $('#loadingSpinner').hide(); });
    }

    function renderDashboard(summary) {
        const stats = [
            { label: 'Attendance (Month)', value: `${summary.overall_percentage}%`, color: 'primary', icon: 'percentage' },
            { label: 'Total Present', value: summary.total_present, color: 'success', icon: 'check-circle' },
            { label: 'Total Absent', value: summary.total_absent, color: 'danger', icon: 'times-circle' },
            { label: 'Total On Leave', value: summary.total_leave, color: 'warning', icon: 'plane-departure' }
        ];
        let statsHtml = '';
        stats.forEach(stat => {
            statsHtml += `<div class="col-xl-3 col-md-6 mb-4"><div class="card border-start border-${stat.color} border-4 shadow-sm h-100 py-2"><div class="card-body"><div class="row g-0 align-items-center"><div class="col"><div class="text-xs fw-bold text-${stat.color} text-uppercase mb-1">${stat.label}</div><div class="h5 mb-0 fw-bold text-body">${stat.value}</div></div><div class="col-auto"><i class="fas fa-${stat.icon} fa-2x text-body-tertiary"></i></div></div></div></div></div>`;
        });
        $('#dashboardStats').html(statsHtml);
    }

    function isSaturdayHoliday(day, policy) {
        const weekNum = Math.ceil(day / 7);
        return policy === 'all' ||
            (policy === '1st_3rd' && (weekNum === 1 || weekNum === 3)) ||
            (policy === '2nd_4th' && (weekNum === 2 || weekNum === 4));
    }

    function renderEmployeeCards({ employees, month_details, company_holidays, saturday_policy, employee_leaves, company_created_at }) {
        const grid = $('#attendanceGrid');
        grid.html('');

        const viewingDate = new Date(month_details.year, month_details.month - 1, 1);
        const companyCreationDate = new Date(company_created_at);

        if (viewingDate < companyCreationDate && viewingDate.getMonth() !== companyCreationDate.getMonth()) {
            $('#noResults').html('<i class="fas fa-info-circle me-2"></i>Attendance tracking began after your company was created.').show();
            grid.hide();
            return;
        }

        if (!employees || employees.length === 0) { $('#noResults').html('<i class="fas fa-info-circle me-2"></i>No employees found to display attendance.').show(); grid.hide(); return; }

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
                const dateObj = new Date(month_details.year, month_details.month - 1, day);
                const dateStr = dateObj.toISOString().slice(0, 10);

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

                const isDisabled = dateObj > today ||
                    dateStr < emp.date_of_joining ||
                    dateStr < company_created_at ||
                    isWeekendOff ||
                    status === 'holiday' ||
                    status === 'leave';

                classes += ` status-${status}`;
                if (isWeekendOff && status === 'empty') {
                    classes += ' status-disabled';
                    if (dayOfWeek === 0) classes += ' status-sunday';
                }
                if (isDisabled) {
                    classes += ' status-disabled';
                }
                if (status === 'holiday') {
                    classes = classes.replace(' status-disabled', '');
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

    function filterEmployeeCards(query) {
        const lowerCaseQuery = query.toLowerCase();
        let visibleCount = 0;
        $('.employee-card').each(function () {
            const employeeName = $(this).data('name');
            if (employeeName.includes(lowerCaseQuery)) { $(this).show(); visibleCount++; }
            else { $(this).hide(); }
        });
        $('#searchNoResults').remove();
        if (visibleCount === 0 && $('#attendanceGrid').is(':visible')) {
            $('#attendanceGrid').after('<div id="searchNoResults" class="alert alert-warning text-center">No employees match your search.</div>');
        }
    }

    function openAttendanceModal(employeeId, employeeName, date) {
        $('#modalEmployeeId').val(employeeId);
        $('#modalEmployeeName').text(employeeName);
        $('#modalDate').val(date);
        $('#modalDateDisplay').text(new Date(date + 'T00:00:00').toLocaleDateString('en-GB', { day: 'numeric', month: 'long', year: 'numeric' }));
        attendanceModal.show();
    }
</script>