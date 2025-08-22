<?php
require_once '../config/db.php';
require_once '../includes/functions.php';
$title = "Attendance Management";

// --- SECURITY & SESSION ---
if (!isLoggedIn() || !in_array($_SESSION['role_id'], [2, 3])) {
    redirect("/hrms/unauthorized.php");
}
$company_id = $_SESSION['company_id'];

require_once '../components/layout/header.php';
?>
<div class="d-flex">
    <?php require_once '../components/layout/sidebar.php'; ?>
    <div class="p-3 p-md-4 flex-grow-1">
        <h2 class="h3 mb-4"><i class="fas fa-calendar-check me-2"></i>Attendance Management</h2>

        <div class="card shadow-sm mb-4">
            <div class="card-header d-flex flex-wrap justify-content-between align-items-center">
                <div class="d-flex align-items-center my-1">
                    <button id="prevMonth" class="btn btn-sm btn-outline-secondary"><i
                            class="fas fa-chevron-left"></i></button>
                    <h5 id="currentMonth" class="m-0 mx-3"></h5>
                    <button id="nextMonth" class="btn btn-sm btn-outline-secondary"><i
                            class="fas fa-chevron-right"></i></button>
                </div>
                <div class="my-1">
                    <input type="search" id="employeeSearch" class="form-control form-control-sm"
                        placeholder="Search Employee...">
                </div>
            </div>
        </div>

        <div class="row attendance-dashboard mb-4" id="dashboardStats"></div>

        <div id="loadingSpinner" class="text-center p-5">
            <div class="spinner-border text-primary" role="status" style="width: 3rem; height: 3rem;">
                <span class="visually-hidden">Loading...</span>
            </div>
        </div>

        <div class="attendance-grid" id="attendanceGrid" style="display:none;"></div>

        <div id="noResults" class="alert alert-info text-center" style="display:none;">
            <i class="fas fa-info-circle me-2"></i>No attendance records found for the selected month.
        </div>
    </div>
</div>

<div class="modal fade" id="attendanceModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="attendanceForm">
                <div class="modal-header">
                    <h5 class="modal-title" id="attendanceModalLabel">Update Attendance</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="action" value="update_attendance">
                    <input type="hidden" name="employee_id" id="modalEmployeeId">
                    <input type="hidden" name="date" id="modalDate">
                    <p>Select status for <strong id="modalEmployeeName"></strong> on <strong
                            id="modalDateDisplay"></strong>:</p>
                    <div class="d-grid gap-2" id="statusButtons">
                        <button type="submit" name="status" value="present" class="btn btn-success"><i
                                class="fas fa-check me-2"></i>Present</button>
                        <button type="submit" name="status" value="absent" class="btn btn-danger"><i
                                class="fas fa-times me-2"></i>Absent</button>
                        <button type="submit" name="status" value="leave" class="btn btn-warning text-dark"><i
                                class="fas fa-plane-departure me-2"></i>On Leave</button>
                        <button type="submit" name="status" value="holiday" class="btn btn-info text-dark"><i
                                class="fas fa-gift me-2"></i>Holiday</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require_once '../components/layout/footer.php'; ?>

<script>
    // All JavaScript from the previous response remains the same.
    // The changes below are only within the HTML-generating functions.
    let currentMonth;
    const attendanceModal = new bootstrap.Modal('#attendanceModal');

    $(function () {
        currentMonth = new Date();
        loadAttendanceData();

        $('#prevMonth').on('click', () => { currentMonth.setMonth(currentMonth.getMonth() - 1); loadAttendanceData(); });
        $('#nextMonth').on('click', () => { currentMonth.setMonth(currentMonth.getMonth() + 1); loadAttendanceData(); });

        $('#employeeSearch').on('input', function () { filterEmployeeCards($(this).val()); });

        $('#attendanceForm').on('submit', function (e) {
            e.preventDefault();
            const status = e.originalEvent.submitter.value;
            if (!status) return;
            const formData = new FormData(this);
            formData.append('status', status);

            fetch('/hrms/api/api_attendance.php', { method: 'POST', body: formData })
                .then(res => res.ok ? res.json() : Promise.reject('Network response was not ok.'))
                .then(data => {
                    if (data.success) {
                        showToast(data.message, 'success');
                        attendanceModal.hide();
                        loadAttendanceData();
                    } else { showToast(data.message || 'An unknown error occurred.', 'error'); }
                })
                .catch(error => {
                    console.error('Submission error:', error);
                    showToast('Failed to update attendance. Please try again.', 'error');
                });
        });

        $('#attendanceGrid').on('click', '.day-square:not(.status-future, .status-weekend)', function () {
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
            .then(res => res.json())
            .then(result => {
                if (result.error || !result.employees) {
                    showToast(result.error || 'Failed to parse data.', 'error');
                    $('#noResults').show();
                    return;
                }
                renderDashboard(result.summary);
                renderEmployeeCards(result.employees, result.month_details);
                $('#employeeSearch').val('');
            }).catch(err => {
                console.error("Fetch Error:", err);
                showToast('A network error occurred while loading data.', 'error');
                $('#noResults').show();
            }).finally(() => { $('#loadingSpinner').hide(); });
    }

    function renderDashboard(summary) {
        const statsHtml = `
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card border-start border-primary border-4 shadow-sm h-100 py-2">
                    <div class="card-body">
                        <div class="row g-0 align-items-center">
                            <div class="col">
                                <div class="text-xs fw-bold text-primary text-uppercase mb-1">Attendance (Month)</div>
                                <div class="h5 mb-0 fw-bold text-body">${summary.overall_percentage}%</div>
                            </div>
                            <div class="col-auto"><i class="fas fa-percentage fa-2x text-body-tertiary"></i></div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card border-start border-success border-4 shadow-sm h-100 py-2">
                    <div class="card-body">
                         <div class="row g-0 align-items-center">
                            <div class="col">
                                <div class="text-xs fw-bold text-success text-uppercase mb-1">Total Present</div>
                                <div class="h5 mb-0 fw-bold text-body">${summary.total_present}</div>
                            </div>
                            <div class="col-auto"><i class="fas fa-check-circle fa-2x text-body-tertiary"></i></div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card border-start border-danger border-4 shadow-sm h-100 py-2">
                    <div class="card-body">
                        <div class="row g-0 align-items-center">
                            <div class="col">
                                <div class="text-xs fw-bold text-danger text-uppercase mb-1">Total Absent</div>
                                <div class="h5 mb-0 fw-bold text-body">${summary.total_absent}</div>
                            </div>
                            <div class="col-auto"><i class="fas fa-times-circle fa-2x text-body-tertiary"></i></div>
                        </div>
                    </div>
                </div>
            </div>
             <div class="col-xl-3 col-md-6 mb-4">
                <div class="card border-start border-warning border-4 shadow-sm h-100 py-2">
                    <div class="card-body">
                        <div class="row g-0 align-items-center">
                            <div class="col">
                                <div class="text-xs fw-bold text-warning text-uppercase mb-1">Total On Leave</div>
                                <div class="h5 mb-0 fw-bold text-body">${summary.total_leave}</div>
                            </div>
                            <div class="col-auto"><i class="fas fa-plane-departure fa-2x text-body-tertiary"></i></div>
                        </div>
                    </div>
                </div>
            </div>
        `;
        $('#dashboardStats').html(statsHtml);
    }

    function renderEmployeeCards(employees, monthDetails) {
        const grid = $('#attendanceGrid');
        grid.html('');

        if (!employees || employees.length === 0) {
            $('#noResults').show();
            grid.hide();
            return;
        }

        const daysInMonth = monthDetails.days_in_month;
        const startDayOfWeek = monthDetails.start_day_of_week;
        const todayDate = new Date().toISOString().slice(0, 10);

        employees.forEach(emp => {
            let presentCount = 0, absentCount = 0, leaveCount = 0, holidayCount = 0;
            let calendarHtml = '';

            for (let i = 0; i < startDayOfWeek; i++) {
                calendarHtml += `<div class="day-square" style="background-color: transparent; border: none; cursor: default;"></div>`;
            }

            for (let day = 1; day <= daysInMonth; day++) {
                const dateStr = `${monthDetails.year}-${String(monthDetails.month).padStart(2, '0')}-${String(day).padStart(2, '0')}`;
                const status = emp.attendance[dateStr]?.status || 'empty';
                const dayOfWeek = new Date(dateStr).getDay();
                let statusClass = `status-${status}`;
                if (status === 'empty' && (dayOfWeek === 0 || dayOfWeek === 6)) { statusClass = 'status-weekend'; }
                if (dateStr > todayDate) { statusClass = 'status-future'; }
                if (status === 'present') presentCount++;
                else if (status === 'absent') absentCount++;
                else if (status === 'leave') leaveCount++;
                else if (status === 'holiday') holidayCount++;
                calendarHtml += `<div class="day-square ${statusClass}" data-date="${dateStr}" data-employee-id="${emp.id}" data-employee-name="${emp.name}">${day}</div>`;
            }

            const cardHtml = `
                <div class="card shadow-sm hover-shadow-lg employee-card h-100" data-name="${emp.name.toLowerCase()}">
                    <div class="card-body d-flex flex-column">
                        <div class="d-flex align-items-center mb-3">
                            <div>
                                <h6 class="mb-0 fw-bold text-primary">${emp.name}</h6>
                                <small class="text-body-secondary">${emp.designation || 'N/A'}</small>
                            </div>
                        </div>
                        <div class="d-flex justify-content-around text-center small mb-3">
                            <div><strong class="text-success">${presentCount}</strong><br><span>Present</span></div>
                            <div><strong class="text-danger">${absentCount}</strong><br><span>Absent</span></div>
                            <div><strong class="text-warning">${leaveCount}</strong><br><span>Leave</span></div>
                            <div><strong class="text-info">${holidayCount}</strong><br><span>Holiday</span></div>
                        </div>
                        <div class="mini-cal mt-auto">${calendarHtml}</div>
                    </div>
                </div>
            `;
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
            if (employeeName.includes(lowerCaseQuery)) {
                $(this).show();
                visibleCount++;
            } else {
                $(this).hide();
            }
        });
        if (visibleCount === 0 && $('#attendanceGrid').is(':visible')) {
            if (!$('#searchNoResults').length) {
                $('#attendanceGrid').after('<div id="searchNoResults" class="alert alert-warning text-center">No employees match your search.</div>');
            }
        } else {
            $('#searchNoResults').remove();
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