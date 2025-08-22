<?php
require_once '../config/db.php';
require_once '../includes/functions.php';
$title = "Leave Management";

if (!isLoggedIn()) {
    redirect("/hrms/auth/login.php");
}

$is_manager = in_array($_SESSION['role_id'], [2, 3]);

require_once '../components/layout/header.php';
?>

<div class="d-flex">
    <?php require_once '../components/layout/sidebar.php'; ?>
    <div class="p-3 p-md-4" style="flex: 1;">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="h3 mb-0 text-gray-800"><i class="fas fa-plane-departure me-2"></i>Leave Management</h2>
            <?php if ($_SESSION['role_id'] !== 2): ?>
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#applyLeaveModal">
                    <i class="fas fa-plus me-2"></i>Apply for Leave
                </button>
            <?php endif; ?>
        </div>

        <!-- Nav Tabs -->
        <ul class="nav nav-tabs" id="leaveTabs" role="tablist">
            <?php if (!$is_manager): ?>
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="my-requests-tab" data-bs-toggle="tab" data-bs-target="#my-requests"
                        type="button" role="tab">My Requests</button>
                </li>
            <?php endif; ?>
            <?php if ($is_manager): ?>
                <li class="nav-item" role="presentation">
                    <button class="nav-link <?php echo $is_manager ? 'active' : ''; ?>" id="approve-requests-tab"
                        data-bs-toggle="tab" data-bs-target="#approve-requests" type="button" role="tab">Approve
                        Requests</button>
                </li>
            <?php endif; ?>
        </ul>

        <!-- Tab Content -->
        <div class="tab-content" id="leaveTabsContent">
            <!-- My Requests Tab -->
            <?php if (!$is_manager): ?>
                <div class="tab-pane fade show active" id="my-requests" role="tabpanel">
                    <div class="card shadow-sm mt-3">
                        <div class="card-body">
                            <table class="table table-hover" id="myRequestsTable" width="100%">
                                <thead class=" ">
                                    <tr>
                                        <th>Type</th>
                                        <th>Dates</th>
                                        <th>Reason</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                            </table>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Approve Requests Tab (for managers) -->
            <?php if ($is_manager): ?>
                <div class="tab-pane fade show active" id="approve-requests" role="tabpanel">
                    <div class="card shadow-sm mt-3">
                        <div class="card-body">
                            <table class="table table-hover" id="approveRequestsTable" width="100%">
                                <thead class=" ">
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

<!-- Apply Leave Modal -->
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
        myRequestsTable = $('#myRequestsTable').DataTable({
            ajax: { url: '/hrms/api/api_leaves.php?action=get_leaves', dataSrc: 'data' },
            columns: [
                { data: 'leave_type' },
                { data: null, render: (d, t, r) => `${formatDate(r.start_date)} to ${formatDate(r.end_date)}` },
                { data: 'reason' },
                { data: 'status', render: (d) => `<span class="badge text-bg-${getStatusClass(d)}">${capitalize(d)}</span>` }
            ],
            order: [[3, 'asc']]
        });

        // Initialize Approve Requests Table if manager
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
                    } else { showToast(data.message, 'error'); }
                });
        });
    });

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
</script>