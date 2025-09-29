<?php
require_once '../config/db.php';
require_once '../includes/functions.php';
$title = "Leave Management";

if (!isLoggedIn()) {
    redirect("/hrms/auth/login.php");
}

$role_id = $_SESSION['role_id'];
$is_manager_or_hr = in_array($role_id, [2, 3, 6]);

$leave_types = query($mysqli, "SELECT leave_type FROM leave_policies WHERE company_id = ?", [$_SESSION['company_id']])['data'] ?? [];

require_once '../components/layout/header.php';
?>

<div class="d-flex">
    <?php require_once '../components/layout/sidebar.php'; ?>
    <div class="p-3 p-md-4" style="flex: 1;">

        <?php if ($role_id !== 2) : ?>
            <div class="row" id="leave-summary-row">
                <div class="col-lg-4 col-md-6 mb-4">
                    <div class="card shadow-sm h-100">
                        <div class="card-header"><h6 class="m-0">Upcoming Holiday</h6></div>
                        <div class="card-body text-center d-flex flex-column justify-content-center" id="upcoming-holiday-card">
                            <div class="spinner-border spinner-border-sm"></div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6 mb-4">
                    <div class="card shadow-sm h-100">
                        <div class="card-header"><h6 class="m-0">Company Policy</h6></div>
                        <div class="card-body text-center d-flex flex-column justify-content-center" id="policy-document-card">
                            <div class="spinner-border spinner-border-sm"></div>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <ul class="nav nav-tabs" id="leaveTabs" role="tablist">
            <?php if ($role_id !== 2) : ?>
                <li class="nav-item" role="presentation"><button class="nav-link active" data-bs-toggle="tab" data-bs-target="#my-requests" type="button">My Requests</button></li>
            <?php endif; ?>
            <?php if ($is_manager_or_hr) : ?>
                <li class="nav-item" role="presentation"><button class="nav-link <?= $role_id === 2 ? 'active' : '' ?>" data-bs-toggle="tab" data-bs-target="#approve-requests" type="button">Approve Requests</button></li>
            <?php endif; ?>
        </ul>

        <div class="tab-content" id="leaveTabsContent">
            <?php if ($role_id !== 2) : ?>
                <div class="tab-pane fade show active" id="my-requests" role="tabpanel">
                    <div class="card shadow-sm mt-3">
                        <div class="card-header d-flex justify-content-between align-items-center"><h6 class="m-0">My Leave Request History</h6><button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#applyLeaveModal"><i class="fas fa-plus me-1"></i> New Request</button></div>
                        <div class="card-body"><table class="table table-hover" id="myRequestsTable" width="100%"><thead><tr><th>Type</th><th>Dates</th><th>Days</th><th>Status</th><th>Actions</th></tr></thead></table></div>
                    </div>
                </div>
            <?php endif; ?>

            <?php if ($is_manager_or_hr) : ?>
                <div class="tab-pane fade <?= $role_id === 2 ? 'show active' : '' ?>" id="approve-requests" role="tabpanel">
                    <div class="card shadow-sm mt-3">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h6 class="m-0">Employee Leave Requests</h6>
                            <a href="/hrms/company/leave_policy.php" class="btn btn-outline-secondary btn-sm"><i class="fas fa-cog me-1"></i> Manage Policies</a>
                        </div>
                        <div class="card-body"><table class="table table-hover" id="approveRequestsTable" width="100%"><thead><tr><th>Employee</th><th>Type</th><th>Dates</th><th>Reason</th><th>Status</th><th>Actions</th></tr></thead></table></div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<div class="modal fade" id="applyLeaveModal" tabindex="-1"><div class="modal-dialog"><div class="modal-content"><form id="applyLeaveForm"><div class="modal-header"><h5 class="modal-title">Apply for Leave</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div><div class="modal-body"><input type="hidden" name="action" value="apply_leave"><div class="row"><div class="col-md-6 mb-3"><label class="form-label">Start Date *</label><input type="date" class="form-control" name="start_date" required></div><div class="col-md-6 mb-3"><label class="form-label">End Date *</label><input type="date" class="form-control" name="end_date" required></div></div><div class="mb-3"><label class="form-label">Leave Type *</label><select class="form-select" name="leave_type" required><option value="">-- Select --</option><?php foreach ($leave_types as $type) : ?><option value="<?= htmlspecialchars($type['leave_type']) ?>"><?= htmlspecialchars($type['leave_type']) ?></option><?php endforeach; ?></select></div><div class="mb-3"><label class="form-label">Reason</label><textarea class="form-control" name="reason" rows="3"></textarea></div></div><div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button><button type="submit" class="btn btn-primary">Submit Request</button></div></form></div></div></div>

<?php require_once '../components/layout/footer.php'; ?>
<script>
    let myRequestsTable, approveRequestsTable;
    const applyLeaveModal = new bootstrap.Modal(document.getElementById('applyLeaveModal'));
    const roleId = <?= json_encode($_SESSION['role_id']) ?>;
    const isManagerOrHr = [2, 3, 6].includes(roleId);

    $(function() {
        if (roleId !== 2) { loadLeaveSummary(); }
        if (roleId !== 2) {
            myRequestsTable = $('#myRequestsTable').DataTable({
                responsive: true, ajax: { url: '/hrms/api/api_leaves.php?action=get_my_leaves', dataSrc: 'data' },
                columns: [
                    { data: 'leave_type' },
                    { data: null, render: (d, t, r) => `${formatDate(r.start_date)} to ${formatDate(r.end_date)}` },
                    { data: null, render: (d, t, r) => countDays(r.start_date, r.end_date) },
                    { data: 'status', render: (d) => `<span class="badge bg-${getStatusClass(d)}-subtle text-${getStatusClass(d)}-emphasis">${capitalize(d)}</span>` },
                    { data: null, orderable: false, render: (d, t, r) => r.status === 'pending' ? `<button class="btn btn-sm btn-outline-danger" onclick="cancelRequest(${r.id})">Cancel</button>` : '---' }
                ], order: [[1, 'desc']]
            });
        }
        if (isManagerOrHr) {
            approveRequestsTable = $('#approveRequestsTable').DataTable({
                responsive: true, ajax: { url: '/hrms/api/api_leaves.php?action=get_pending_requests', dataSrc: 'data' },
                columns: [
                    { data: null, render: (d, t, r) => `${escapeHTML(r.first_name)} ${escapeHTML(r.last_name)}` },
                    { data: 'leave_type' },
                    { data: null, render: (d, t, r) => `${formatDate(r.start_date)} to ${formatDate(r.end_date)}` },
                    { data: 'reason', render: d => `<small>${escapeHTML(d) || 'N/A'}</small>` },
                    { data: 'status', render: d => `<span class="badge bg-${getStatusClass(d)}-subtle bg-opacity-10 text-${getStatusClass(d)}-emphasis">${capitalize(d)}</span>` },
                    { data: null, orderable: false, render: (d, t, r) => r.status === 'pending' ? `<div class="btn-group btn-group-sm"><button class="btn btn-outline-success" onclick="updateStatus(${r.id}, 'approved')">Approve</button><button class="btn btn-outline-danger" onclick="updateStatus(${r.id}, 'rejected')">Reject</button></div>` : 'Actioned' }
                ], order: [[2, 'asc']]
            });
        }
        $('#applyLeaveForm').on('submit', function (e) {
            e.preventDefault();
            fetch('/hrms/api/api_leaves.php', { method: 'POST', body: new FormData(this) })
            .then(res => res.json()).then(result => {
                if (result.success) {
                    showToast(result.message, 'success');
                    applyLeaveModal.hide(); this.reset();
                    if(myRequestsTable) myRequestsTable.ajax.reload();
                    if(approveRequestsTable) approveRequestsTable.ajax.reload();
                    if(roleId !== 2) loadLeaveSummary(); 
                } else { showToast(result.message, 'error'); }
            });
        });
    });

    function loadLeaveSummary() {
        $('#leave-summary-row .leave-balance-card').remove();
        fetch('/hrms/api/api_leaves.php?action=get_leave_summary')
        .then(res => res.json()).then(result => {
            if(result.success) {
                const { balances, next_holiday, policy_document } = result.data;
                balances.forEach(b => {
                    $('#leave-summary-row').prepend(`<div class="col-lg-4 col-md-6 mb-4 leave-balance-card"><div class="card shadow-sm h-100"><div class="card-header"><h6 class="m-0">${escapeHTML(b.type)}</h6></div><div class="card-body text-center d-flex flex-column justify-content-center"><p class="fs-2 fw-bold mb-0">${b.balance} / <small class="text-muted">${b.total}</small></p><p class="text-muted mb-0">Days Remaining</p></div></div></div>`);
                });
                $('#upcoming-holiday-card').html(next_holiday ? `<p class="fs-4 fw-bold mb-1">${escapeHTML(next_holiday.holiday_name)}</p><p class="text-muted mb-0">${formatDate(next_holiday.holiday_date, true)}</p>` : '<p class="text-muted">No upcoming holidays.</p>');
                $('#policy-document-card').html(policy_document ? `<a href="/hrms/pages/view_document.php?id=${policy_document.id}" target="_blank" class="btn btn-outline-primary"><i class="fas fa-file-pdf me-2"></i>View Policy</a>` : '<p class="text-muted">No policy document uploaded.</p>');
            }
        });
    }

    function cancelRequest(leaveId) { if (confirm('Are you sure you want to cancel?')) { const f = new FormData(); f.append('action', 'cancel_leave'); f.append('leave_id', leaveId); fetch('/hrms/api/api_leaves.php', { method: 'POST', body: f }).then(r => r.json()).then(d => { if (d.success) { showToast(d.message, 'success'); myRequestsTable.ajax.reload(); loadLeaveSummary(); } else { showToast(d.message, 'error'); } }); } }
    function updateStatus(leaveId, status) { if (confirm(`Are you sure you want to ${status}?`)) { const f = new FormData(); f.append('action', 'update_status'); f.append('leave_id', leaveId); f.append('status', status); fetch('/hrms/api/api_leaves.php', { method: 'POST', body: f }).then(r => r.json()).then(d => { if (d.success) { showToast(d.message, 'success'); if(approveRequestsTable) approveRequestsTable.ajax.reload(); if(myRequestsTable) myRequestsTable.ajax.reload(); loadLeaveSummary(); } else { showToast(d.message, 'error'); } }); } }
 </script>

