<?php
require_once '../config/db.php';
require_once '../includes/functions.php';
$title = "Team Attendance";

if (!isLoggedIn() || $_SESSION['role_id'] !== 6) {
    redirect("/hrms/auth/login.php");
}

$user_id = $_SESSION['user_id'];
$company_id = $_SESSION['company_id'];

// Get manager's employee record
$manager_result = query($mysqli, "SELECT * FROM employees WHERE user_id = ?", [$user_id]);
$manager = $manager_result['success'] ? $manager_result['data'][0] : null;

if (!$manager) {
    redirect("/hrms/pages/unauthorized.php");
}

$manager_id = $manager['id'];
$manager_department_id = $manager['department_id'];

// Get filter parameters
$date_filter = $_GET['date'] ?? date('Y-m-d');
$employee_filter = $_GET['employee_id'] ?? '';
$status_filter = $_GET['status'] ?? '';

// Build query conditions
$where_conditions = ["e.department_id = ?", "a.date = ?"];
$params = [$manager_department_id, $date_filter];

if (!empty($employee_filter)) {
    $where_conditions[] = "a.employee_id = ?";
    $params[] = $employee_filter;
}

if (!empty($status_filter)) {
    $where_conditions[] = "a.status = ?";
    $params[] = $status_filter;
}

$where_clause = implode(' AND ', $where_conditions);

// Get attendance records
$attendance_result = query($mysqli, "
    SELECT a.*, e.first_name, e.last_name, e.employee_code,
           des.name as designation_name, s.name as shift_name
    FROM attendance a
    JOIN employees e ON a.employee_id = e.id
    LEFT JOIN designations des ON e.designation_id = des.id
    LEFT JOIN shifts s ON e.shift_id = s.id
    WHERE $where_clause
    ORDER BY e.first_name ASC
", $params);

$attendance_records = $attendance_result['success'] ? $attendance_result['data'] : [];

// Get team members for filter dropdown
$team_members_result = query($mysqli, "
    SELECT e.id, e.first_name, e.last_name, e.employee_code
    FROM employees e
    WHERE e.department_id = ? AND e.status = 'active'
    ORDER BY e.first_name ASC
", [$manager_department_id]);

$team_members = $team_members_result['success'] ? $team_members_result['data'] : [];

// Get attendance statistics for the selected date
$stats_result = query($mysqli, "
    SELECT 
        COUNT(CASE WHEN a.status = 'present' THEN 1 END) as present,
        COUNT(CASE WHEN a.status = 'absent' THEN 1 END) as absent,
        COUNT(CASE WHEN a.status = 'leave' THEN 1 END) as on_leave,
        COUNT(CASE WHEN a.status = 'half-day' THEN 1 END) as half_day,
        COUNT(CASE WHEN a.status = 'holiday' THEN 1 END) as holiday
    FROM attendance a
    JOIN employees e ON a.employee_id = e.id
    WHERE e.department_id = ? AND a.date = ?
", [$manager_department_id, $date_filter]);

$stats = $stats_result['success'] ? $stats_result['data'][0] : [
    'present' => 0,
    'absent' => 0,
    'on_leave' => 0,
    'half_day' => 0,
    'holiday' => 0
];

// Get total team members count
$total_team_result = query($mysqli, "
    SELECT COUNT(*) as total
    FROM employees e
    WHERE e.department_id = ? AND e.status = 'active'
", [$manager_department_id]);

$total_team = $total_team_result['success'] ? $total_team_result['data'][0]['total'] : 0;

require_once '../components/layout/header.php';
?>

<div class="d-flex">
    <?php require_once '../components/layout/sidebar.php'; ?>
    <div class="p-3 p-md-4" style="flex: 1;">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="h3 text-gray-800">
                <i class="fas fa-calendar-check me-2"></i>Team Attendance
            </h2>
            <div>
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#markAttendanceModal">
                    <i class="fas fa-plus me-2"></i>Mark Attendance
                </button>
                <button class="btn btn-success" onclick="exportAttendance()">
                    <i class="fas fa-download me-2"></i>Export Report
                </button>
            </div>
        </div>

        <!-- Statistics Cards -->
        <div class="row mb-4">
            <div class="col-xl-2 col-md-4 mb-4">
                <div class="card stat-card shadow-sm">
                    <div class="card-body">
                        <div class="icon-circle bg-success"><i class="fas fa-check"></i></div>
                        <div>
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Present</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?= $stats['present'] ?></div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-2 col-md-4 mb-4">
                <div class="card stat-card shadow-sm">
                    <div class="card-body">
                        <div class="icon-circle bg-danger"><i class="fas fa-times"></i></div>
                        <div>
                            <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">Absent</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?= $stats['absent'] ?></div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-2 col-md-4 mb-4">
                <div class="card stat-card shadow-sm">
                    <div class="card-body">
                        <div class="icon-circle bg-warning"><i class="fas fa-plane"></i></div>
                        <div>
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">On Leave</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?= $stats['on_leave'] ?></div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-2 col-md-4 mb-4">
                <div class="card stat-card shadow-sm">
                    <div class="card-body">
                        <div class="icon-circle bg-info"><i class="fas fa-clock"></i></div>
                        <div>
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Half Day</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?= $stats['half_day'] ?></div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-2 col-md-4 mb-4">
                <div class="card stat-card shadow-sm">
                    <div class="card-body">
                        <div class="icon-circle bg-secondary"><i class="fas fa-calendar"></i></div>
                        <div>
                            <div class="text-xs font-weight-bold text-secondary text-uppercase mb-1">Holiday</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?= $stats['holiday'] ?></div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-2 col-md-4 mb-4">
                <div class="card stat-card shadow-sm">
                    <div class="card-body">
                        <div class="icon-circle bg-primary"><i class="fas fa-users"></i></div>
                        <div>
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Total</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?= $total_team ?></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filters -->
        <div class="card shadow-sm mb-4">
            <div class="card-body">
                <form method="GET" class="row g-3">
                    <div class="col-md-3">
                        <label for="date" class="form-label">Date</label>
                        <input type="date" class="form-control" id="date" name="date" value="<?= htmlspecialchars($date_filter) ?>">
                    </div>
                    <div class="col-md-3">
                        <label for="employee_id" class="form-label">Employee</label>
                        <select class="form-select" id="employee_id" name="employee_id">
                            <option value="">All Employees</option>
                            <?php foreach ($team_members as $member): ?>
                                <option value="<?= $member['id'] ?>" <?= $employee_filter == $member['id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($member['first_name'] . ' ' . $member['last_name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label for="status" class="form-label">Status</label>
                        <select class="form-select" id="status" name="status">
                            <option value="">All Status</option>
                            <option value="present" <?= $status_filter === 'present' ? 'selected' : '' ?>>Present</option>
                            <option value="absent" <?= $status_filter === 'absent' ? 'selected' : '' ?>>Absent</option>
                            <option value="leave" <?= $status_filter === 'leave' ? 'selected' : '' ?>>On Leave</option>
                            <option value="half-day" <?= $status_filter === 'half-day' ? 'selected' : '' ?>>Half Day</option>
                            <option value="holiday" <?= $status_filter === 'holiday' ? 'selected' : '' ?>>Holiday</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">&nbsp;</label>
                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary">Filter</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Attendance Records Table -->
        <div class="card shadow-sm">
            <div class="card-header">
                <h6 class="m-0 font-weight-bold">Attendance Records - <?= date('F j, Y', strtotime($date_filter)) ?></h6>
            </div>
            <div class="card-body">
                <?php if (!empty($attendance_records)): ?>
                    <div class="table-responsive">
                        <table class="table table-hover" id="attendanceTable">
                            <thead>
                                <tr>
                                    <th>Employee</th>
                                    <th>Check In</th>
                                    <th>Check Out</th>
                                    <th>Status</th>
                                    <th>Working Hours</th>
                                    <th>Remarks</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($attendance_records as $record): ?>
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="avatar-circle me-3">
                                                    <?= strtoupper(substr($record['first_name'], 0, 1) . substr($record['last_name'], 0, 1)) ?>
                                                </div>
                                                <div>
                                                    <div class="fw-bold"><?= htmlspecialchars($record['first_name'] . ' ' . $record['last_name']) ?></div>
                                                    <small class="text-muted"><?= htmlspecialchars($record['employee_code'] ?? 'N/A') ?></small>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <?php if ($record['check_in']): ?>
                                                <span class="text-success fw-bold"><?= date('g:i A', strtotime($record['check_in'])) ?></span>
                                            <?php else: ?>
                                                <span class="text-muted">--</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if ($record['check_out']): ?>
                                                <span class="text-danger fw-bold"><?= date('g:i A', strtotime($record['check_out'])) ?></span>
                                            <?php else: ?>
                                                <span class="text-muted">--</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php
                                            $status_classes = [
                                                'present' => 'success',
                                                'absent' => 'danger',
                                                'leave' => 'warning',
                                                'half-day' => 'info',
                                                'holiday' => 'secondary'
                                            ];
                                            $status_class = $status_classes[$record['status']] ?? 'secondary';
                                            $status_text = str_replace('-', ' ', ucfirst($record['status']));
                                            ?>
                                            <span class="badge bg-<?= $status_class ?>"><?= $status_text ?></span>
                                        </td>
                                        <td>
                                            <?php if ($record['check_in'] && $record['check_out']): ?>
                                                <?php
                                                $check_in = new DateTime($record['check_in']);
                                                $check_out = new DateTime($record['check_out']);
                                                $diff = $check_out->diff($check_in);
                                                $hours = $diff->h + ($diff->i / 60);
                                                ?>
                                                <span class="fw-bold"><?= number_format($hours, 1) ?>h</span>
                                            <?php else: ?>
                                                <span class="text-muted">--</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <div class="text-truncate" style="max-width: 150px;" title="<?= htmlspecialchars($record['remarks']) ?>">
                                                <?= htmlspecialchars($record['remarks'] ?: '--') ?>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="btn-group btn-group-sm">
                                                <button class="btn btn-outline-primary" onclick="editAttendance(<?= $record['id'] ?>)" title="Edit">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <button class="btn btn-outline-info" onclick="viewAttendanceDetails(<?= $record['id'] ?>)" title="View Details">
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="text-center text-muted p-5">
                        <i class="fas fa-calendar-times fa-3x mb-3"></i>
                        <h5>No Attendance Records Found</h5>
                        <p>No attendance records match your current filters for this date.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Mark Attendance Modal -->
<div class="modal fade" id="markAttendanceModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Mark Attendance</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="attendanceForm">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="attendance_employee" class="form-label">Employee *</label>
                        <select class="form-select" id="attendance_employee" name="employee_id" required>
                            <option value="">Select Employee</option>
                            <?php foreach ($team_members as $member): ?>
                                <option value="<?= $member['id'] ?>"><?= htmlspecialchars($member['first_name'] . ' ' . $member['last_name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="attendance_date" class="form-label">Date *</label>
                        <input type="date" class="form-control" id="attendance_date" name="date" value="<?= $date_filter ?>" required>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="check_in_time" class="form-label">Check In Time</label>
                                <input type="time" class="form-control" id="check_in_time" name="check_in">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="check_out_time" class="form-label">Check Out Time</label>
                                <input type="time" class="form-control" id="check_out_time" name="check_out">
                            </div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="attendance_status" class="form-label">Status *</label>
                        <select class="form-select" id="attendance_status" name="status" required>
                            <option value="present">Present</option>
                            <option value="absent">Absent</option>
                            <option value="leave">On Leave</option>
                            <option value="half-day">Half Day</option>
                            <option value="holiday">Holiday</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="attendance_remarks" class="form-label">Remarks</label>
                        <textarea class="form-control" id="attendance_remarks" name="remarks" rows="2"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Mark Attendance</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Attendance Details Modal -->
<div class="modal fade" id="attendanceDetailsModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Attendance Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="attendanceDetailsContent">
                <!-- Content will be loaded here -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<?php require_once '../components/layout/footer.php'; ?>

<style>
.avatar-circle {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background: linear-gradient(45deg, #4e73df, #36b9cc);
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: bold;
    font-size: 14px;
}

.stat-card {
    border: 1px solid rgba(255,255,255,0.06);
    border-radius: 14px;
    background: linear-gradient(180deg, rgba(255,255,255,0.02), rgba(255,255,255,0.01));
}
.stat-card .card-body {
    display: flex;
    align-items: center;
    gap: 14px;
    padding-top: 14px;
    padding-bottom: 14px;
}

.icon-circle {
    width: 48px;
    height: 48px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-right: 6px;
    color: white;
    font-size: 20px;
    box-shadow: 0 6px 16px rgba(0,0,0,0.15);
}

.stat-card .text-xs {
    letter-spacing: .03em;
    opacity: .9;
}

.stat-card .h5 {
    margin: 0;
}
</style>

<script>
$(document).ready(function() {
    // Initialize DataTable
    $('#attendanceTable').DataTable({
        responsive: true,
        pageLength: 10,
        order: [[0, 'asc']] // Sort by employee name
    });

    // Handle attendance form submission
    $('#attendanceForm').on('submit', function(e) {
        e.preventDefault();
        markAttendance();
    });
});

function markAttendance() {
    const formData = new FormData(document.getElementById('attendanceForm'));
    formData.append('action', 'mark_attendance');

    fetch('/hrms/api/api_manager.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showToast(data.message, 'success');
            $('#markAttendanceModal').modal('hide');
            document.getElementById('attendanceForm').reset();
            location.reload();
        } else {
            showToast(data.message, 'error');
        }
    })
    .catch(error => {
        showToast('An error occurred. Please try again.', 'error');
    });
}

function editAttendance(attendanceId) {
    // Load details and prefill the existing Mark Attendance modal for inline editing
    fetch(`/hrms/api/api_manager.php?action=get_attendance_details&attendance_id=${attendanceId}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const a = data.data;
                $('#attendance_employee').val(a.employee_id);
                $('#attendance_date').val(a.date);
                $('#check_in_time').val(a.check_in || '');
                $('#check_out_time').val(a.check_out || '');
                $('#attendance_status').val(a.status);
                $('#attendance_remarks').val(a.remarks || '');
                $('#markAttendanceModal').modal('show');
            } else {
                showToast(data.message || 'Failed to load attendance details', 'error');
            }
        })
        .catch(() => showToast('Failed to load attendance details', 'error'));
}

function viewAttendanceDetails(attendanceId) {
    fetch(`/hrms/api/api_manager.php?action=get_attendance_details&attendance_id=${attendanceId}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                displayAttendanceDetails(data.data);
                $('#attendanceDetailsModal').modal('show');
            } else {
                showToast(data.message, 'error');
            }
        })
        .catch(error => {
            showToast('An error occurred. Please try again.', 'error');
        });
}

function displayAttendanceDetails(attendance) {
    const html = `
        <div class="row">
            <div class="col-md-6">
                <h6>Employee Information</h6>
                <p><strong>Name:</strong> ${attendance.first_name} ${attendance.last_name}</p>
                <p><strong>Employee Code:</strong> ${attendance.employee_code || 'N/A'}</p>
                <p><strong>Designation:</strong> ${attendance.designation_name || 'N/A'}</p>
                <p><strong>Shift:</strong> ${attendance.shift_name || 'N/A'}</p>
            </div>
            <div class="col-md-6">
                <h6>Attendance Information</h6>
                <p><strong>Date:</strong> ${new Date(attendance.date).toLocaleDateString()}</p>
                <p><strong>Check In:</strong> ${attendance.check_in ? new Date('2000-01-01 ' + attendance.check_in).toLocaleTimeString() : 'Not recorded'}</p>
                <p><strong>Check Out:</strong> ${attendance.check_out ? new Date('2000-01-01 ' + attendance.check_out).toLocaleTimeString() : 'Not recorded'}</p>
                <p><strong>Status:</strong> <span class="badge bg-${getStatusClass(attendance.status)}">${attendance.status.charAt(0).toUpperCase() + attendance.status.slice(1).replace('-', ' ')}</span></p>
            </div>
        </div>
        <div class="row mt-3">
            <div class="col-12">
                <h6>Remarks</h6>
                <p class="border p-3 rounded">${attendance.remarks || 'No remarks provided'}</p>
            </div>
        </div>
    `;
    
    $('#attendanceDetailsContent').html(html);
}

function getStatusClass(status) {
    const classes = {
        'present': 'success',
        'absent': 'danger',
        'leave': 'warning',
        'half-day': 'info',
        'holiday': 'secondary'
    };
    return classes[status] || 'secondary';
}

function exportAttendance() {
    // Implement export functionality
    const params = new URLSearchParams(window.location.search);
    params.set('export', '1');
    window.open(`/hrms/manager/attendance.php?${params.toString()}`, '_blank');
}
</script>
