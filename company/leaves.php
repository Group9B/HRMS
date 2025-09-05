<?php
require_once '../config/db.php';
require_once '../includes/functions.php';
$title = "Leave Management";

if (!isLoggedIn()) {
    redirect("/hrms/auth/login.php");
}

$is_manager = in_array($_SESSION['role_id'], [2, 3, 4]);

require_once '../components/layout/header.php';
?>

<div class="d-flex">
    <?php require_once '../components/layout/sidebar.php'; ?>
    <div class="p-3 p-md-4" style="flex: 1;">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="h3 mb-0 text-gray-800"><i class="fas fa-plane-departure me-2"></i>Leave Management</h2>
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#applyLeaveModal">
                <i class="fas fa-plus me-2"></i>Apply for Leave
            </button>
        </div>

        <div class="card shadow-sm mb-4">
            <div class="card-header">
                <h6 class="m-0 font-weight-bold">My Leave Summary</h6>
            </div>
            <div class="card-body">
                <div class="row text-center">
                    <div class="col-md-4">
                        <h5>Annual Leave</h5>
                        <p class="fs-4 fw-bold" id="annual-balance">12 / 15</p>
                    </div>
                    <div class="col-md-4">
                        <h5>Sick Leave</h5>
                        <p class="fs-4 fw-bold" id="sick-balance">8 / 10</p>
                    </div>
                    <div class="col-md-4">
                        <h5>Leave Policy</h5>
                        <p><a href="#">View Company Policy</a></p>
                    </div>
                </div>
            </div>
        </div>


        <ul class="nav nav-tabs" id="leaveTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="my-requests-tab" data-bs-toggle="tab" data-bs-target="#my-requests"
                    type="button" role="tab">My Requests</button>
            </li>
            <?php if ($is_manager): ?>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="approve-requests-tab" data-bs-toggle="tab"
                        data-bs-target="#approve-requests" type="button" role="tab">Approve Requests</button>
                </li>
            <?php endif; ?>
        </ul>

        <div class="tab-content" id="leaveTabsContent">
            <div class="tab-pane fade show active" id="my-requests" role="tabpanel">
                <div class="card shadow-sm mt-3">
                    <div class="card-body">
                        <table class="table table-hover" id="myRequestsTable" width="100%">
                            <thead class="table">
                                <tr>
                                    <th>Type</th>
                                    <th>Dates</th>
                                    <th>Days</th>
                                    <th>Reason</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                        </table>
                    </div>
                </div>
            </div>

            <?php if ($is_manager): ?>
                <div class="tab-pane fade" id="approve-requests" role="tabpanel">
                    <div class="card shadow-sm mt-3">
                        <div class="card-body">
                            <table class="table table-hover" id="approveRequestsTable" width="100%">
                                <thead class="table">
                                    <tr>
                                        <th>Employee</th>
                                        <th>Type</th>
                                        <th>Dates</th>
                                        <th>Reason</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                            </table>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<div class="modal fade" id="applyLeaveModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="applyLeaveForm">
                <div class="modal-header">
                    <h5 class="modal-title">Apply for Leave</h5><button type="button" class="btn-close"
                        data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="action" value="apply_leave">
                    <div class="row">
                        <div class="col-md-6 mb-3"><label class="form-label">Start Date <span
                                    class="text-danger">*</span></label><input type="date" class="form-control"
                                name="start_date" required></div>
                        <div class="col-md-6 mb-3"><label class="form-label">End Date <span
                                    class="text-danger">*</span></label><input type="date" class="form-control"
                                name="end_date" required></div>
                    </div>
                    <div class="mb-3"><label class="form-label">Leave Type</label><select class="form-select"
                            name="leave_type">
                            <option>Annual</option>
                            <option>Sick</option>
                            <option>Unpaid</option>
                            <option>Maternity</option>
                        </select></div>
                    <div class="mb-3"><label class="form-label">Reason</label><textarea class="form-control"
                            name="reason" rows="3"></textarea></div>
                </div>
                <div class="modal-footer"><button type="button" class="btn btn-secondary"
                        data-bs-dismiss="modal">Cancel</button><button type="submit" class="btn btn-primary">Submit
                        Request</button></div>
            </form>
        </div>
    </div>
</div>


<?php require_once '../components/layout/footer.php'; ?>

<script>
    let myRequestsTable, approveRequestsTable;
    const applyLeaveModal = new bootstrap.Modal('#applyLeaveModal');
    const isManager = <?= json_encode($is_manager) ?>;

    $(function () {
        // Initialize My Requests Table
        // UPDATED: Now calls a specific API action to get only the user's leaves.
        myRequestsTable = $('#myRequestsTable').DataTable({
            ajax: { url: '/hrms/api/api_leaves.php?action=get_my_leaves', dataSrc: 'data' },
            columns: [
                { data: 'leave_type' },
                { data: null, render: (d, t, r) => `${formatDate(r.start_date)} to ${formatDate(r.end_date)}` },
                { data: null, render: (d, t, r) => countDays(r.start_date, r.end_date) },
                { data: 'reason' },
                { data: 'status', render: (d) => `<span class="badge text-bg-${getStatusClass(d)}">${capitalize(d)}</span>` },
                { // NEW: Actions column for cancelling
                    data: null, orderable: false, render: (d, t, r) => {
                        if (r.status === 'pending') {
                            return `<button class="btn btn-sm btn-outline-danger" onclick="cancelRequest(${r.id})">Cancel</button>`;
                        }
                        return '---';
                    }
                }
            ],
            order: [[4, 'asc']]
        });

        // Initialize Approve Requests Table if manager
        // This now correctly fetches requests from other employees.
        if (isManager) {
            approveRequestsTable = $('#approveRequestsTable').DataTable({
                ajax: { url: '/hrms/api/api_leaves.php?action=get_leaves', dataSrc: 'data' },
                columns: [
                    { data: null, render: (d, t, r) => `${escapeHTML(r.first_name)} ${escapeHTML(r.last_name)}` },
                    { data: 'leave_type' },
                    { data: null, render: (d, t, r) => `${formatDate(r.start_date)} to ${formatDate(r.end_date)}` },
                    { data: 'reason' },
                    { data: 'status', render: (d) => `<span class="badge text-bg-${getStatusClass(d)}">${capitalize(d)}</span>` },
                    {
                        data: null, orderable: false, render: (d, t, r) => {
                            if (r.status === 'pending') {
                                return `<div class="btn-group"><button class="btn btn-sm btn-success" onclick="updateStatus(${r.id}, 'approved')">Approve</button><button class="btn btn-sm btn-danger" onclick="updateStatus(${r.id}, 'rejected')">Reject</button></div>`;
                            }
                            return 'Actioned';
                        }
                    }
                ],
                order: [[4, 'asc']]
            });
        }

        $('#applyLeaveForm').on('submit', function (e) {
            e.preventDefault();
            fetch('/hrms/api/api_leaves.php', { method: 'POST', body: new FormData(this) })
                .then(res => res.json()).then(data => {
                    if (data.success) {
                        showToast(data.message, 'success');
                        applyLeaveModal.hide();
                        myRequestsTable.ajax.reload();
                        if (isManager) { approveRequestsTable.ajax.reload(); }
                    } else { showToast(data.message, 'error'); }
                });
        });
    });

    // NEW: Function to cancel a leave request
    function cancelRequest(leaveId) {
        if (confirm('Are you sure you want to cancel this leave request?')) {
            const formData = new FormData();
            formData.append('action', 'cancel_leave');
            formData.append('leave_id', leaveId);
            fetch('/hrms/api/api_leaves.php', { method: 'POST', body: formData })
                .then(res => res.json()).then(data => {
                    if (data.success) {
                        showToast(data.message, 'success');
                        myRequestsTable.ajax.reload();
                    } else { showToast(data.message, 'error'); }
                });
        }
    }

    function updateStatus(leaveId, status) {
        if (confirm(`Are you sure you want to ${status} this request?`)) {
            const formData = new FormData();
            formData.append('action', 'update_status');
            formData.append('leave_id', leaveId);
            formData.append('status', status);
            fetch('/hrms/api/api_leaves.php', { method: 'POST', body: formData })
                .then(res => res.json()).then(data => {
                    if (data.success) {
                        showToast(data.message, 'success');
                        approveRequestsTable.ajax.reload();
                        myRequestsTable.ajax.reload(); // Also reload my requests table
                    } else { showToast(data.message, 'error'); }
                });
        }
    }

    function getStatusClass(status) {
        if (status === 'approved') return 'success';
        if (status === 'rejected') return 'danger';
        return 'warning'; // pending
    }

    function formatDate(dateString) {
        return new Date(dateString + 'T00:00:00').toLocaleDateString('en-CA'); // YYYY-MM-DD format
    }

    function capitalize(str) { return str.charAt(0).toUpperCase() + str.slice(1); }

    // NEW: Helper to count days in a leave request
    function countDays(start, end) {
        const startDate = new Date(start);
        const endDate = new Date(end);
        const diffTime = Math.abs(endDate - startDate);
        const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24)) + 1; // +1 to include the start day
        return diffDays;
    }
</script>