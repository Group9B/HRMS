<?php
require_once '../config/db.php';
require_once '../includes/functions.php';
$title = "Leave Management";

if (!isLoggedIn()) {
    redirect("/hrms/auth/login.php");
}

$role_id = $_SESSION['role_id'];
$is_manager_or_hr = in_array($role_id, [1, 2, 3, 6]); // Admin, Company Owner, Human Resources, Manager

$leave_types = query($mysqli, "SELECT leave_type FROM leave_policies WHERE company_id = ?", [$_SESSION['company_id']])['data'] ?? [];

require_once '../components/layout/header.php';
?>

<div class="d-flex">
    <?php require_once '../components/layout/sidebar.php'; ?>
    <div class="p-3 p-md-4" style="flex: 1;">

        <?php if ($role_id !== 2) : ?>
            <div class="row" id="leave-summary-row">
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

        <div class="tab-content" id="leaveTabsContent" data-can-approve="<?= $is_manager_or_hr ? 'true' : 'false' ?>">
            <?php if ($role_id !== 2) : ?>
                <div class="tab-pane fade show active" id="my-requests" role="tabpanel">
                    <div class="card shadow-sm mt-3">
                        <div class="card-header d-flex justify-content-between align-items-center"><h6 class="m-0">My Leave Request History</h6><button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#applyLeaveModal"><i class="ti ti-plus me-1"></i> New Request</button></div>
                        <div class="card-body"><table class="table table-hover" id="myRequestsTable" width="100%"><thead><tr><th>Type</th><th>Dates</th><th>Days</th><th>Status</th><th>Actions</th></tr></thead></table></div>
                    </div>
                </div>
            <?php endif; ?>

            <?php if ($is_manager_or_hr) : ?>
                <div class="tab-pane fade <?= $role_id === 2 ? 'show active' : '' ?>" id="approve-requests" role="tabpanel">
                    <div class="card shadow-sm mt-3">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h6 class="m-0">Employee Leave Requests</h6>
                            <a href="/hrms/company/leave_policy.php" class="btn btn-outline-secondary btn-sm"><i class="ti ti-settings me-1"></i> Manage Policies</a>
                        </div>
                        <div class="card-body"><table class="table table-hover" id="approveRequestsTable" width="100%"><thead><tr><th>Employee</th><th>Type</th><th>Dates</th><th>Reason</th><th>Status</th><th>Actions</th></tr></thead></table></div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<div class="modal fade" id="applyLeaveModal" tabindex="-1"><div class="modal-dialog"><div class="modal-content"><form id="applyLeaveForm"><div class="modal-header"><h5 class="modal-title">Apply for Leave</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div><div class="modal-body"><input type="hidden" name="action" value="apply_leave"><div class="row"><div class="col-md-6 mb-3"><label class="form-label">Start Date *</label><input type="date" class="form-control" name="start_date" id="startDate" required></div><div class="col-md-6 mb-3"><label class="form-label">End Date *</label><input type="date" class="form-control" name="end_date" id="endDate" required></div></div><div class="mb-3" id="dateErrorContainer" style="display: none;"><div class="alert alert-danger mb-0" id="dateError"></div></div><div class="mb-3" id="leaveDaysCalculation" style="display: none;"><div class="alert alert-info mb-0"><small><strong>Calculation:</strong> <span id="totalDaysText">0</span> calendar days</small><br><small id="holidaysText" style="display: none;"></small><small id="saturdaysText" style="display: none;"></small><br><small class="text-primary"><strong>Actual days to deduct:</strong> <span id="actualDaysText">0</span></small></div></div><div class="mb-3"><label class="form-label">Leave Type *</label><select class="form-select" name="leave_type" required><option value="">-- Select --</option><?php foreach ($leave_types as $type) : ?><option value="<?= htmlspecialchars($type['leave_type']) ?>"><?= htmlspecialchars($type['leave_type']) ?></option><?php endforeach; ?></select></div><div class="mb-3"><label class="form-label">Reason</label><textarea class="form-control" name="reason" rows="3"></textarea></div></div><div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button><button type="submit" class="btn btn-primary" id="submitLeaveBtn">Submit Request</button></div></form></div></div></div>

<?php require_once '../components/layout/footer.php'; ?>
<script>
    let myRequestsTable, approveRequestsTable;
    const applyLeaveModal = new bootstrap.Modal(document.getElementById('applyLeaveModal'));
    const canApprove = document.getElementById('leaveTabsContent').dataset.canApprove === 'true';
    const isCompanyAdmin = document.querySelector('[data-bs-target="#approve-requests"]')?.classList.contains('active') === true;

    $(function() {
        const hasMyRequestsTab = document.getElementById('my-requests') !== null;
        if (hasMyRequestsTab) { loadLeaveSummary(); }
        
        // Set minimum date to today
        const today = new Date().toISOString().split('T')[0];
        $('#startDate').attr('min', today);
        $('#endDate').attr('min', today);
        
        // Validate dates on change
        $('#startDate, #endDate').on('change', validateLeaveDates);
        
        if (hasMyRequestsTab) {
            myRequestsTable = $('#myRequestsTable').DataTable({
                responsive: true, ajax: { url: '/hrms/api/api_leaves.php?action=get_my_leaves', dataSrc: 'data' },
                columns: [
                    { data: 'leave_type' },
                    { data: null, render: (d, t, r) => `${formatDate(r.start_date)} to ${formatDate(r.end_date)}` },
                    { data: null, render: (d, t, r) => countDays(r.start_date, r.end_date) },
                    { data: 'status', render: (d) => `<span class="badge bg-${getStatusClass(d)}-subtle text-${getStatusClass(d)}-emphasis">${capitalize(d)}</span>` },
                    { data: null, orderable: false, render: (d, t, r) => r.status === 'pending' ? `<button class="btn btn-sm btn-outline-danger cancel-leave-btn" data-leave-id="${escapeHTML(r.id)}" title="Cancel">Cancel</button>` : '---' }
                ], order: [[1, 'desc']]
            });
        }
        if (canApprove) {
            approveRequestsTable = $('#approveRequestsTable').DataTable({
                responsive: true, ajax: { url: '/hrms/api/api_leaves.php?action=get_pending_requests', dataSrc: 'data' },
                columns: [
                    { data: null, render: (d, t, r) => `${escapeHTML(r.first_name)} ${escapeHTML(r.last_name)}` },
                    { data: 'leave_type' },
                    { data: null, render: (d, t, r) => `${formatDate(r.start_date)} to ${formatDate(r.end_date)}` },
                    { data: 'reason', render: d => `<small>${escapeHTML(d) || 'N/A'}</small>` },
                    { data: 'status', render: d => `<span class="badge bg-${getStatusClass(d)}-subtle bg-opacity-10 text-${getStatusClass(d)}-emphasis">${capitalize(d)}</span>` },
                    { data: null, orderable: false, render: (d, t, r) => r.status === 'pending' ? `<div class="btn-group btn-group-sm"><button class="btn btn-outline-success approve-leave-btn" data-leave-id="${escapeHTML(r.id)}" data-action="approved" title="Approve">Approve</button><button class="btn btn-outline-danger reject-leave-btn" data-leave-id="${escapeHTML(r.id)}" data-action="rejected" title="Reject">Reject</button></div>` : 'Actioned' }
                ], order: [[2, 'asc']]
            });
        }
        $('#applyLeaveForm').on('submit', function (e) {
            e.preventDefault();
            if (!validateLeaveDates()) return;
            fetch('/hrms/api/api_leaves.php', { method: 'POST', body: new FormData(this) })
            .then(res => res.json()).then(result => {
                if (result.success) {
                    showToast(result.message, 'success');
                    applyLeaveModal.hide(); this.reset();
                    if(myRequestsTable) myRequestsTable.ajax.reload();
                    if(approveRequestsTable) approveRequestsTable.ajax.reload();
                    if(hasMyRequestsTab) loadLeaveSummary(); 
                } else { showToast(result.message, 'error'); }
            });
        });
    });

    function loadLeaveSummary() {
        $('#leave-summary-row').empty();
        fetch('/hrms/api/api_leaves.php?action=get_leave_summary')
        .then(res => res.json()).then(result => {
            if(result.success) {
                const { balances, next_holiday, policy_document } = result.data;
                
                // Create a grid layout with leave balances on left, holiday and policy on right
                let summaryHTML = '';
                
                // Left column - Leave balances
                if (balances.length > 0) {
                    summaryHTML += `<div class="col-lg-8 col-md-12 mb-4">
                        <div class="card shadow-sm h-100">
                            <div class="card-header"><h6 class="m-0">Leave Balance</h6></div>
                            <div class="card-body">
                                <div class="row" id="leave-balance-container">`;
                    balances.forEach(b => {
                        summaryHTML += `<div class="col-md-6 col-lg-4 mb-3">
                            <div class="text-center p-3 border rounded-3">
                                <p class="text-muted small mb-1">${escapeHTML(b.type)}</p>
                                <p class="fs-4 fw-bold mb-0"><span class="text-primary">${b.balance}</span> / <small class="text-muted">${b.total}</small></p>
                            </div>
                        </div>`;
                    });
                    summaryHTML += `</div></div></div></div>`;
                }
                
                // Right column - Holiday and Policy
                summaryHTML += `<div class="col-lg-4 col-md-12">
                    <div class="row">
                        <div class="col-12 mb-4">
                            <div class="card shadow-sm h-100">
                                <div class="card-header"><h6 class="m-0">Upcoming Holiday</h6></div>
                                <div class="card-body text-center d-flex flex-column justify-content-center">
                                    ${next_holiday ? `<p class="fs-5 fw-bold mb-1">${escapeHTML(next_holiday.holiday_name)}</p><p class="text-muted mb-0 small">${formatDate(next_holiday.holiday_date, true)}</p>` : '<p class="text-muted">No upcoming holidays</p>'}
                                </div>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="card shadow-sm h-100">
                                <div class="card-header"><h6 class="m-0">Company Policy</h6></div>
                                <div class="card-body text-center d-flex flex-column justify-content-center align-items-center">
                                    ${policy_document ? `<button class="btn btn-outline-primary btn-sm view-policy-btn" data-doc-id="${escapeHTML(policy_document.id)}"><i class="ti ti-file-pdf me-2"></i>View Policy</button>` : '<p class="text-muted small mb-0">No policy available</p>'}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>`;
                
                $('#leave-summary-row').html(summaryHTML);
            }
        });
    }

    // Event delegation for policy document link
    $(document).on('click', '.view-policy-btn', function(e) {
        e.preventDefault();
        const docId = $(this).data('doc-id');
        if (docId) {
            window.open('/hrms/pages/view_document.php?id=' + encodeURIComponent(docId), '_blank');
        }
    });

    // Event delegation for cancel button
    $(document).on('click', '.cancel-leave-btn', function() {
        const leaveId = $(this).data('leave-id');
        if (!leaveId) {
            showToast('Invalid leave request.', 'error');
            return;
        }
        showConfirmationModal(
            'Are you sure you want to cancel this leave request?',
            () => {
                const formData = new FormData();
                formData.append('action', 'cancel_leave');
                formData.append('leave_id', leaveId);
                fetch('/hrms/api/api_leaves.php', { method: 'POST', body: formData })
                    .then(r => r.json())
                    .then(d => {
                        if (d.success) {
                            showToast(d.message, 'success');
                            if(myRequestsTable) myRequestsTable.ajax.reload();
                            loadLeaveSummary();
                        } else {
                            showToast(d.message, 'error');
                        }
                    });
            },
            'Cancel Leave Request',
            'Cancel Request',
            'btn-danger'
        );
    });

    // Event delegation for approve/reject buttons
    $(document).on('click', '.approve-leave-btn, .reject-leave-btn', function() {
        const leaveId = $(this).data('leave-id');
        const status = $(this).data('action');
        
        if (!leaveId || !status) {
            showToast('Invalid leave request.', 'error');
            return;
        }

        const action = status === 'approved' ? 'approve' : 'reject';
        const btnClass = status === 'approved' ? 'btn-success' : 'btn-danger';
        
        showConfirmationModal(
            `Are you sure you want to ${action} this leave request?`,
            () => {
                const formData = new FormData();
                formData.append('action', 'update_status');
                formData.append('leave_id', leaveId);
                formData.append('status', status);
                fetch('/hrms/api/api_leaves.php', { method: 'POST', body: formData })
                    .then(r => r.json())
                    .then(d => {
                        if (d.success) {
                            showToast(d.message, 'success');
                            if(approveRequestsTable) approveRequestsTable.ajax.reload();
                            if(myRequestsTable) myRequestsTable.ajax.reload();
                            loadLeaveSummary();
                        } else {
                            showToast(d.message, 'error');
                        }
                    });
            },
            `${capitalize(action)} Leave Request`,
            capitalize(action),
            btnClass
        );
    });
    
    function validateLeaveDates() {
        const startDate = $('#startDate').val();
        const endDate = $('#endDate').val();
        const errorContainer = $('#dateErrorContainer');
        const errorMsg = $('#dateError');
        const submitBtn = $('#submitLeaveBtn');
        const calculationDiv = $('#leaveDaysCalculation');
        
        errorContainer.hide();
        calculationDiv.hide();
        
        // Validation checks
        if (!startDate || !endDate) {
            return true;
        }
        
        const today = new Date();
        today.setHours(0, 0, 0, 0);
        
        const start = new Date(startDate);
        const end = new Date(endDate);
        
        // Check if start date is in the past
        if (start < today) {
            errorMsg.text('Start date cannot be in the past. Please select a date from today onwards.');
            errorContainer.show();
            submitBtn.prop('disabled', true);
            return false;
        }
        
        // Check if end date is before start date
        if (end < start) {
            errorMsg.text('End date cannot be before start date.');
            errorContainer.show();
            submitBtn.prop('disabled', true);
            return false;
        }
        
        // Calculate actual leave days
        calculateLeaveDaysDisplay(startDate, endDate);
        
        submitBtn.prop('disabled', false);
        return true;
    }

    function calculateLeaveDaysDisplay(startDateStr, endDateStr) {
        // Fetch company holidays and Saturday policy via API
        fetch('/hrms/api/api_leaves.php?action=get_leave_calculation&start_date=' + startDateStr + '&end_date=' + endDateStr)
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    const totalDays = data.total_days;
                    const holidaysSkipped = data.holidays_skipped;
                    const saturdaysSkipped = data.saturdays_skipped;
                    const actualDays = data.actual_days;
                    
                    $('#totalDaysText').text(totalDays);
                    
                    if (holidaysSkipped > 0) {
                        $('#holidaysText').text('- ' + holidaysSkipped + ' holiday(s)').show();
                    } else {
                        $('#holidaysText').hide();
                    }
                    
                    if (saturdaysSkipped > 0) {
                        $('#saturdaysText').text('- ' + saturdaysSkipped + ' Saturday(s)').show();
                    } else {
                        $('#saturdaysText').hide();
                    }
                    
                    $('#actualDaysText').text(actualDays);
                    $('#leaveDaysCalculation').show();
                }
            })
            .catch(error => console.error('Error calculating leave days:', error));
    }
 </script>

