<?php
require_once '../config/db.php';
require_once '../includes/functions.php';
$title = "Leave Approval";

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
$status_filter = $_GET['status'] ?? 'pending';
$date_from = $_GET['date_from'] ?? '';
$date_to = $_GET['date_to'] ?? '';

// Build query conditions
$where_conditions = ["e.department_id = ?"];
$params = [$manager_department_id];

if ($status_filter !== 'all') {
    $where_conditions[] = "l.status = ?";
    $params[] = $status_filter;
}

if (!empty($date_from)) {
    $where_conditions[] = "l.start_date >= ?";
    $params[] = $date_from;
}

if (!empty($date_to)) {
    $where_conditions[] = "l.end_date <= ?";
    $params[] = $date_to;
}

$where_clause = implode(' AND ', $where_conditions);

// Get leave requests
$leaves_result = query($mysqli, "
    SELECT l.*, e.first_name, e.last_name, e.employee_code, e.contact,
           des.name as designation_name, d.name as department_name
    FROM leaves l
    JOIN employees e ON l.employee_id = e.id
    LEFT JOIN designations des ON e.designation_id = des.id
    LEFT JOIN departments d ON e.department_id = d.id
    WHERE $where_clause
    ORDER BY l.applied_at DESC
", $params);

$leaves = $leaves_result['success'] ? $leaves_result['data'] : [];

// Get leave statistics
$stats_result = query($mysqli, "
    SELECT 
        COUNT(CASE WHEN l.status = 'pending' THEN 1 END) as pending,
        COUNT(CASE WHEN l.status = 'approved' THEN 1 END) as approved,
        COUNT(CASE WHEN l.status = 'rejected' THEN 1 END) as rejected,
        COUNT(CASE WHEN l.status = 'cancelled' THEN 1 END) as cancelled
    FROM leaves l
    JOIN employees e ON l.employee_id = e.id
    WHERE e.department_id = ?
", [$manager_department_id]);

$stats = $stats_result['success'] ? $stats_result['data'][0] : [
    'pending' => 0,
    'approved' => 0,
    'rejected' => 0,
    'cancelled' => 0
];

require_once '../components/layout/header.php';
?>

<div class="d-flex">
    <?php require_once '../components/layout/sidebar.php'; ?>
    <div class="p-3 p-md-4" style="flex: 1;">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="h3 text-gray-800">
                <i class="fas fa-calendar-check me-2"></i>Leave Approval
            </h2>
            <div>
                <button class="btn btn-success" onclick="approveAllPending()">
                    <i class="fas fa-check-double me-2"></i>Approve All Pending
                </button>
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#bulkActionModal">
                    <i class="fas fa-tasks me-2"></i>Bulk Actions
                </button>
            </div>
        </div>

        <!-- Statistics Cards -->
        <div class="row mb-4">
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card stat-card shadow-sm">
                    <div class="card-body">
                        <div class="icon-circle bg-warning"><i class="fas fa-clock"></i></div>
                        <div>
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Pending</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?= $stats['pending'] ?></div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card stat-card shadow-sm">
                    <div class="card-body">
                        <div class="icon-circle bg-success"><i class="fas fa-check"></i></div>
                        <div>
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Approved</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?= $stats['approved'] ?></div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card stat-card shadow-sm">
                    <div class="card-body">
                        <div class="icon-circle bg-danger"><i class="fas fa-times"></i></div>
                        <div>
                            <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">Rejected</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?= $stats['rejected'] ?></div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card stat-card shadow-sm">
                    <div class="card-body">
                        <div class="icon-circle bg-secondary"><i class="fas fa-ban"></i></div>
                        <div>
                            <div class="text-xs font-weight-bold text-secondary text-uppercase mb-1">Cancelled</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?= $stats['cancelled'] ?></div>
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
                        <label for="status" class="form-label">Status</label>
                        <select class="form-select" id="status" name="status">
                            <option value="all" <?= $status_filter === 'all' ? 'selected' : '' ?>>All Status</option>
                            <option value="pending" <?= $status_filter === 'pending' ? 'selected' : '' ?>>Pending</option>
                            <option value="approved" <?= $status_filter === 'approved' ? 'selected' : '' ?>>Approved</option>
                            <option value="rejected" <?= $status_filter === 'rejected' ? 'selected' : '' ?>>Rejected</option>
                            <option value="cancelled" <?= $status_filter === 'cancelled' ? 'selected' : '' ?>>Cancelled</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label for="date_from" class="form-label">From Date</label>
                        <input type="date" class="form-control" id="date_from" name="date_from" value="<?= htmlspecialchars($date_from) ?>">
                    </div>
                    <div class="col-md-3">
                        <label for="date_to" class="form-label">To Date</label>
                        <input type="date" class="form-control" id="date_to" name="date_to" value="<?= htmlspecialchars($date_to) ?>">
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

        <!-- Leave Requests Table -->
        <div class="card shadow-sm">
            <div class="card-header">
                <h6 class="m-0 font-weight-bold">Leave Requests</h6>
            </div>
            <div class="card-body">
                <?php if (!empty($leaves)): ?>
                    <div class="table-responsive">
                        <table class="table table-hover" id="leavesTable">
                            <thead>
                                <tr>
                                    <th>
                                        <input type="checkbox" id="selectAll" class="form-check-input">
                                    </th>
                                    <th>Employee</th>
                                    <th>Leave Type</th>
                                    <th>Duration</th>
                                    <th>Reason</th>
                                    <th>Applied Date</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($leaves as $leave): ?>
                                    <tr>
                                        <td>
                                            <input type="checkbox" class="form-check-input leave-checkbox" value="<?= $leave['id'] ?>">
                                        </td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="avatar-circle me-3">
                                                    <?= strtoupper(substr($leave['first_name'], 0, 1) . substr($leave['last_name'], 0, 1)) ?>
                                                </div>
                                                <div>
                                                    <div class="fw-bold"><?= htmlspecialchars($leave['first_name'] . ' ' . $leave['last_name']) ?></div>
                                                    <small class="text-muted"><?= htmlspecialchars($leave['employee_code'] ?? 'N/A') ?></small>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="badge bg-info"><?= htmlspecialchars($leave['leave_type']) ?></span>
                                        </td>
                                        <td>
                                            <div>
                                                <strong><?= date('M j, Y', strtotime($leave['start_date'])) ?></strong>
                                                <br>
                                                <small class="text-muted">to <?= date('M j, Y', strtotime($leave['end_date'])) ?></small>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="text-truncate" style="max-width: 200px;" title="<?= htmlspecialchars($leave['reason']) ?>">
                                                <?= htmlspecialchars($leave['reason'] ?: 'No reason provided') ?>
                                            </div>
                                        </td>
                                        <td><?= date('M j, Y g:i A', strtotime($leave['applied_at'])) ?></td>
                                        <td>
                                            <?php
                                            $status_classes = [
                                                'pending' => 'warning',
                                                'approved' => 'success',
                                                'rejected' => 'danger',
                                                'cancelled' => 'secondary'
                                            ];
                                            $status_class = $status_classes[$leave['status']] ?? 'secondary';
                                            ?>
                                            <span class="badge bg-<?= $status_class ?>"><?= ucfirst($leave['status']) ?></span>
                                        </td>
                                        <td>
                                            <div class="btn-group btn-group-sm">
                                                <?php if ($leave['status'] === 'pending'): ?>
                                                    <button class="btn btn-success btn-sm" onclick="approveLeave(<?= $leave['id'] ?>)" title="Approve">
                                                        <i class="fas fa-check"></i>
                                                    </button>
                                                    <button class="btn btn-danger btn-sm" onclick="rejectLeave(<?= $leave['id'] ?>)" title="Reject">
                                                        <i class="fas fa-times"></i>
                                                    </button>
                                                <?php endif; ?>
                                                <button class="btn btn-info btn-sm" onclick="viewLeaveDetails(<?= $leave['id'] ?>)" title="View Details">
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
                        <h5>No Leave Requests Found</h5>
                        <p>No leave requests match your current filters.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Bulk Action Modal -->
<div class="modal fade" id="bulkActionModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Bulk Actions</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label">Select Action:</label>
                    <select class="form-select" id="bulkAction">
                        <option value="">Choose action...</option>
                        <option value="approve">Approve Selected</option>
                        <option value="reject">Reject Selected</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label for="bulkReason" class="form-label">Reason (Optional)</label>
                    <textarea class="form-control" id="bulkReason" rows="3" placeholder="Enter reason for bulk action..."></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="executeBulkAction()">Execute Action</button>
            </div>
        </div>
    </div>
</div>

<!-- Leave Details Modal -->
<div class="modal fade" id="leaveDetailsModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Leave Request Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="leaveDetailsContent">
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

.stat-card .card-body {
    display: flex;
    align-items: center;
}

.icon-circle {
    width: 50px;
    height: 50px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-right: 15px;
    color: white;
    font-size: 20px;
}
</style>

<script>
$(document).ready(function() {
    // Initialize DataTable
    $('#leavesTable').DataTable({
        responsive: true,
        pageLength: 10,
        order: [[5, 'desc']] // Sort by applied date
    });

    // Handle select all checkbox
    $('#selectAll').on('change', function() {
        $('.leave-checkbox').prop('checked', this.checked);
    });

    // Handle individual checkboxes
    $('.leave-checkbox').on('change', function() {
        if (!this.checked) {
            $('#selectAll').prop('checked', false);
        } else {
            // Check if all checkboxes are checked
            if ($('.leave-checkbox:checked').length === $('.leave-checkbox').length) {
                $('#selectAll').prop('checked', true);
            }
        }
    });
});

function approveLeave(leaveId) {
    if (confirm('Are you sure you want to approve this leave request?')) {
        updateLeaveStatus(leaveId, 'approved');
    }
}

function rejectLeave(leaveId) {
    const reason = prompt('Please provide a reason for rejection (optional):');
    updateLeaveStatus(leaveId, 'rejected', reason);
}

function updateLeaveStatus(leaveId, status, reason = '') {
    const formData = new FormData();
    formData.append('action', 'update_leave_status');
    formData.append('leave_id', leaveId);
    formData.append('status', status);
    formData.append('reason', reason);

    fetch('/hrms/api/api_manager.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showToast(data.message, 'success');
            location.reload();
        } else {
            showToast(data.message, 'error');
        }
    })
    .catch(error => {
        showToast('An error occurred. Please try again.', 'error');
    });
}

function viewLeaveDetails(leaveId) {
    fetch(`/hrms/api/api_manager.php?action=get_leave_details&leave_id=${leaveId}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                displayLeaveDetails(data.data);
                $('#leaveDetailsModal').modal('show');
            } else {
                showToast(data.message, 'error');
            }
        })
        .catch(error => {
            showToast('An error occurred. Please try again.', 'error');
        });
}

function displayLeaveDetails(leave) {
    const html = `
        <div class="row">
            <div class="col-md-6">
                <h6>Employee Information</h6>
                <p><strong>Name:</strong> ${leave.first_name} ${leave.last_name}</p>
                <p><strong>Employee Code:</strong> ${leave.employee_code || 'N/A'}</p>
                <p><strong>Designation:</strong> ${leave.designation_name || 'N/A'}</p>
                <p><strong>Department:</strong> ${leave.department_name || 'N/A'}</p>
            </div>
            <div class="col-md-6">
                <h6>Leave Information</h6>
                <p><strong>Leave Type:</strong> ${leave.leave_type}</p>
                <p><strong>Start Date:</strong> ${new Date(leave.start_date).toLocaleDateString()}</p>
                <p><strong>End Date:</strong> ${new Date(leave.end_date).toLocaleDateString()}</p>
                <p><strong>Applied Date:</strong> ${new Date(leave.applied_at).toLocaleString()}</p>
                <p><strong>Status:</strong> <span class="badge bg-${getStatusClass(leave.status)}">${leave.status.charAt(0).toUpperCase() + leave.status.slice(1)}</span></p>
            </div>
        </div>
        <div class="row mt-3">
            <div class="col-12">
                <h6>Reason</h6>
                <p class="border p-3 rounded">${leave.reason || 'No reason provided'}</p>
            </div>
        </div>
    `;
    
    $('#leaveDetailsContent').html(html);
}

function getStatusClass(status) {
    const classes = {
        'pending': 'warning',
        'approved': 'success',
        'rejected': 'danger',
        'cancelled': 'secondary'
    };
    return classes[status] || 'secondary';
}

function approveAllPending() {
    if (confirm('Are you sure you want to approve all pending leave requests?')) {
        const formData = new FormData();
        formData.append('action', 'approve_all_pending');

        fetch('/hrms/api/api_manager.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showToast(data.message, 'success');
                location.reload();
            } else {
                showToast(data.message, 'error');
            }
        })
        .catch(error => {
            showToast('An error occurred. Please try again.', 'error');
        });
    }
}

function executeBulkAction() {
    const action = $('#bulkAction').val();
    const reason = $('#bulkReason').val();
    const selectedLeaves = $('.leave-checkbox:checked').map(function() {
        return this.value;
    }).get();

    if (!action) {
        showToast('Please select an action.', 'error');
        return;
    }

    if (selectedLeaves.length === 0) {
        showToast('Please select at least one leave request.', 'error');
        return;
    }

    if (confirm(`Are you sure you want to ${action} ${selectedLeaves.length} leave request(s)?`)) {
        const formData = new FormData();
        formData.append('action', 'bulk_update_leave_status');
        formData.append('leave_ids', JSON.stringify(selectedLeaves));
        formData.append('status', action);
        formData.append('reason', reason);

        fetch('/hrms/api/api_manager.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showToast(data.message, 'success');
                location.reload();
            } else {
                showToast(data.message, 'error');
            }
        })
        .catch(error => {
            showToast('An error occurred. Please try again.', 'error');
        });
    }
}
</script>
