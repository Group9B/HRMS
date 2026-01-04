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
// Get all team members first (same logic as dashboard/tasks)
$team_members_result = query($mysqli, "
    SELECT DISTINCT e.id, e.first_name, e.last_name, e.employee_code
    FROM employees e
    LEFT JOIN team_members tm ON e.id = tm.employee_id
    LEFT JOIN teams t ON tm.team_id = t.id
    WHERE (e.department_id = ? OR t.created_by = ?) 
    AND e.status = 'active'
    ORDER BY e.first_name ASC
", [$manager_department_id, $user_id]);

$team_members = $team_members_result['success'] ? $team_members_result['data'] : [];
$team_member_ids = array_column($team_members, 'id');
$ids_placeholder = !empty($team_member_ids) ? implode(',', array_map('intval', $team_member_ids)) : '0';

// Build query conditions
$where_conditions = ["e.id IN ($ids_placeholder)", "a.date = ?"];
$params = [$date_filter];

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
    WHERE e.id IN ($ids_placeholder) AND a.date = ?
", [$date_filter]);

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
    WHERE e.id IN ($ids_placeholder) AND e.status = 'active'
", []);

$total_team = $total_team_result['success'] ? $total_team_result['data'][0]['total'] : 0;

require_once '../components/layout/header.php';
?>

<div class="d-flex">
    <?php require_once '../components/layout/sidebar.php'; ?>
    <div class="p-3 p-md-4" style="flex: 1;">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="h3 text-gray-800">
                <i class="ti ti-calendar-check me-2"></i>Team Attendance
            </h2>
            <div>
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#markAttendanceModal">
                    <i class="ti ti-plus me-2"></i>Mark Attendance
                </button>
                <button class="btn btn-success" onclick="exportAttendance()">
                    <i class="ti ti-download me-2"></i>Export Report
                </button>
            </div>
        </div>

        <!-- Statistics Cards -->
        <div id="attendanceStats" class="row mb-4"></div>

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
                                                    <i class="ti ti-edit"></i>
                                                </button>
                                                <button class="btn btn-outline-info" onclick="viewAttendanceDetails(<?= $record['id'] ?>)" title="View Details">
                                                    <i class="ti ti-eye"></i>
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
                        <i class="ti ti-calendar-x" style="font-size: 3rem;"></i>
                        <div class="mb-3"></div>
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



<script>
    // Render Stats using global function
    const attendanceStats = [
        { label: 'Present', value: '<?= $stats['present'] ?>', color: 'success', icon: 'check' },
        { label: 'Absent', value: '<?= $stats['absent'] ?>', color: 'danger', icon: 'x' },
        { label: 'On Leave', value: '<?= $stats['on_leave'] ?>', color: 'warning', icon: 'plane' },
        { label: 'Half Day', value: '<?= $stats['half_day'] ?>', color: 'info', icon: 'clock' },
        { label: 'Holiday', value: '<?= $stats['holiday'] ?>', color: 'secondary', icon: 'calendar' },
        { label: 'Total', value: '<?= $total_team ?>', color: 'primary', icon: 'users' }
    ];
    renderStatCards('attendanceStats', attendanceStats);

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
