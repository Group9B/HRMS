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

// Get Saturday policy for client-side calculations
$saturday_policy_result = query(
    $mysqli,
    "SELECT saturday_policy FROM company_holiday_settings WHERE company_id = ?",
    [$_SESSION['company_id']]
);
$saturday_policy = $saturday_policy_result['success'] && !empty($saturday_policy_result['data'])
    ? $saturday_policy_result['data'][0]['saturday_policy']
    : 'none';

require_once '../components/layout/header.php';
?>

<div class="d-flex" data-saturday-policy="<?= htmlspecialchars($saturday_policy) ?>">
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
                        <div class="card-body"><table class="table table-hover" id="approveRequestsTable" width="100%"><thead><tr><th>Employee</th><th>Type</th><th>Dates</th><th>Days</th><th>Reason</th><th>Status</th><th>Actions</th></tr></thead></table></div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<div class="modal fade" id="applyLeaveModal" tabindex="-1"><div class="modal-dialog"><div class="modal-content"><form id="applyLeaveForm"><div class="modal-header"><h5 class="modal-title">Apply for Leave</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div><div class="modal-body"><input type="hidden" name="action" value="apply_leave"><div class="row"><div class="col-md-6 mb-3"><label class="form-label">Start Date *</label><input type="date" class="form-control" name="start_date" id="startDate" required></div><div class="col-md-6 mb-3"><label class="form-label">End Date *</label><input type="date" class="form-control" name="end_date" id="endDate" required></div></div><div class="mb-3" id="dateErrorContainer" style="display: none;"><div class="alert alert-danger mb-0" id="dateError"></div></div><div class="mb-3" id="leaveDaysCalculation" style="display: none;"><div class="alert alert-info mb-0"><small><strong>Calculation:</strong> <span id="totalDaysText">0</span> calendar days</small><br><small id="holidaysText" style="display: none;"></small><small id="sundaysText" style="display: none;"></small><small id="saturdaysText" style="display: none;"></small><br><small class="text-primary"><strong>Actual days to deduct:</strong> <span id="actualDaysText">0</span></small></div></div><div class="mb-3"><label class="form-label">Leave Type *</label><select class="form-select" name="leave_type" id="leaveTypeSelect" required><option value="">-- Select --</option><?php foreach ($leave_types as $type) : ?><option value="<?= htmlspecialchars($type['leave_type']) ?>"><?= htmlspecialchars($type['leave_type']) ?></option><?php endforeach; ?></select></div><div class="mb-3" id="balanceWarningContainer" style="display: none;"></div><div class="mb-3"><label class="form-label">Reason</label><textarea class="form-control" name="reason" rows="3"></textarea></div></div><div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button><button type="submit" class="btn btn-primary" id="submitLeaveBtn">Submit Request</button></div></form></div></div></div>

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
        
        // Validate dates and check balance on change
        $('#startDate, #endDate').on('change', function() {
            validateLeaveDates();
            checkLeaveBalance();
        });
        
        // Check balance when leave type changes
        $('#leaveTypeSelect').on('change', checkLeaveBalance);
        if (hasMyRequestsTab) {
            SkeletonFactory.showTable('myRequestsTable', 5, 5);
            myRequestsTable = $('#myRequestsTable').DataTable({
                initComplete: function() { SkeletonFactory.hideTable('myRequestsTable'); },
                responsive: true, ajax: { url: '/hrms/api/api_leaves.php?action=get_my_leaves', dataSrc: 'data' },
                columns: [
                    { data: 'leave_type' },
                    { data: null, render: (d, t, r) => `${formatDate(r.start_date)} to ${formatDate(r.end_date)}` },
                    { data: null, render: (d, t, r) => {
                        const saturPolicy = document.querySelector('[data-saturday-policy]')?.dataset?.saturdayPolicy || 'none';
                        return getActualLeaveDays(r.start_date, r.end_date, saturPolicy);
                    }},
                    { data: 'status', render: (d) => `<span class="badge bg-${getStatusClass(d)}-subtle text-${getStatusClass(d)}-emphasis">${capitalize(d)}</span>` },
                    { data: null, orderable: false, render: (d, t, r) => r.status === 'pending' ? `<button class="btn btn-sm btn-outline-danger cancel-leave-btn" data-leave-id="${escapeHTML(r.id)}" title="Cancel">Cancel</button>` : '---' }
                ], order: [[1, 'desc']]
            });
        }
        if (canApprove) {
            SkeletonFactory.showTable('approveRequestsTable', 5, 7);
            approveRequestsTable = $('#approveRequestsTable').DataTable({
                initComplete: function() { SkeletonFactory.hideTable('approveRequestsTable'); },
                responsive: true, ajax: { url: '/hrms/api/api_leaves.php?action=get_pending_requests', dataSrc: 'data' },
                columns: [
                    { data: null, render: (d, t, r) => `<a href="/hrms/employee/profile.php?emp_id=${r.emp_id}" class="text-decoration-none text-body">${escapeHTML(r.first_name)} ${escapeHTML(r.last_name)}<i class="ti ti-arrow-up-right"></i></a>` },
                    { data: 'leave_type' },
                    { data: null, render: (d, t, r) => `${formatDate(r.start_date)} to ${formatDate(r.end_date)}` },
                    { data: null, render: (d, t, r) => {
                        const saturPolicy = document.querySelector('[data-saturday-policy]')?.dataset?.saturdayPolicy || 'none';
                        return getActualLeaveDays(r.start_date, r.end_date, saturPolicy);
                    }},
                    { data: 'reason', render: d => {
                        const safeReason = d ? escapeHTML(d) : 'N/A';
                        return `<small>${safeReason}</small>`;
                    } },
                    { data: 'status', render: d => `<span class="badge bg-${getStatusClass(d)}-subtle bg-opacity-10 text-${getStatusClass(d)}-emphasis">${capitalize(d)}</span>` },
                    { data: null, orderable: false, render: (d, t, r) => r.status === 'pending' ? `<div class="btn-group btn-group-sm"><button class="btn btn-outline-success approve-leave-btn" data-leave-id="${escapeHTML(r.id)}" data-action="approved" title="Approve">Approve</button><button class="btn btn-outline-danger reject-leave-btn" data-leave-id="${escapeHTML(r.id)}" data-action="rejected" title="Reject">Reject</button></div>` : 'Actioned' }
                ], order: [[2, 'asc']]
            });
        }
        $('#applyLeaveForm').on('submit', function (e) {
            e.preventDefault();
            if (!validateLeaveDates()) return;
            const submitBtn = $(this).find('button[type="submit"]');
            const restoreBtn = UIController.showButtonLoading(submitBtn[0], 'Submitting...');
            fetch('/hrms/api/api_leaves.php', { method: 'POST', body: new FormData(this) })
            .then(res => res.json()).then(result => {
                if (result.success) {
                    // Show warning if balance exceeded
                    if (result.warning) {
                        showToast('Leave submitted! Warning: ' + result.warning, 'warning');
                    } else {
                        showToast(result.message, 'success');
                    }
                    applyLeaveModal.hide(); this.reset();
                    if(myRequestsTable) myRequestsTable.ajax.reload();
                    if(approveRequestsTable) approveRequestsTable.ajax.reload();
                    if(hasMyRequestsTab) loadLeaveSummary(); 
                } else { showToast(result.message, 'error'); }
            }).finally(() => restoreBtn());
        });
    });

    function loadLeaveSummary() {
        $('#leave-summary-row').empty();
        SkeletonFactory.show('#leave-summary-row', 'stat-card', 4); // Show skeleton
        fetch('/hrms/api/api_leaves.php?action=get_leave_summary')
        .then(res => res.json()).then(async result => {
            const balancesRaw = Array.isArray(result?.data?.balances) ? result.data.balances : [];
            const balances = balancesRaw.map(b => ({
                ...b,
                type: b.type || 'Leave',
                balance: Number(b.balance) || 0,
                total: Number(b.total) || 0,
                used: b.used !== undefined ? Number(b.used) : undefined
            }));

            if(result.success) {
                const { next_holiday, policy_document } = result.data || {};
                
                // Calculate total remaining days
                const totalRemaining = balances.reduce((sum, b) => sum + b.balance, 0);
                const totalAllotted = balances.reduce((sum, b) => sum + b.total, 0);
                
                // Find Annual Leave for priority display
                const annualLeave = balances.find(b => (b.type || '').toLowerCase().includes('annual'));
                
                // Build redesigned summary
                let summaryHTML = `
                <div class="col-12 mb-4">
                    <!-- Summary Header -->
                    <div class="card shadow-sm border-0 mb-3">
                        <div class="card-body py-3">
                            <div class="row align-items-center">
                                <div class="col-md-6">
                                    <div class="d-flex align-items-center">
                                        <div class="rounded-circle bg-primary bg-opacity-10 p-3 me-3">
                                            <i class="ti ti-calendar-stats fs-3 text-primary"></i>
                                        </div>
                                        <div>
                                            <h4 class="mb-0 fw-bold">You have <span class="text-primary">${totalRemaining}</span> leave days remaining</h4>
                                            <small class="text-muted">Out of ${totalAllotted} total days allocated this year</small>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6 mt-3 mt-md-0">
                                    <div class="d-flex justify-content-md-end align-items-center gap-3 flex-wrap">
                                        ${next_holiday ? `
                                        <div class="d-flex align-items-center bg-body-secondary rounded-3 px-3 py-2">
                                            <i class="ti ti-beach text-success me-2"></i>
                                            <div>
                                                <small class="text-muted d-block" style="font-size: 0.7rem;">NEXT HOLIDAY</small>
                                                <span class="fw-semibold small">${escapeHTML(next_holiday.holiday_name)}</span>
                                                <span class="text-muted small ms-1">(${formatDate(next_holiday.holiday_date)})</span>
                                            </div>
                                        </div>` : ''}
                                        ${policy_document ? `
                                        <button class="btn btn-outline-secondary btn-sm view-policy-btn" data-doc-id="${escapeHTML(policy_document.id)}">
                                            <i class="ti ti-file-description me-1"></i> Policy
                                        </button>` : ''}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Leave Balance Grid -->
                    <div class="row g-3">`;
                
                // Sort balances: Annual leave first, then by remaining balance (lowest first for urgency)
                const sortedBalances = [...balances].sort((a, b) => {
                    const aType = (a.type || '').toLowerCase();
                    const bType = (b.type || '').toLowerCase();
                    const aIsAnnual = aType.includes('annual');
                    const bIsAnnual = bType.includes('annual');
                    if (aIsAnnual && !bIsAnnual) return -1;
                    if (!aIsAnnual && bIsAnnual) return 1;
                    return a.balance - b.balance; // Lower balance = higher priority
                });
                
                sortedBalances.forEach((b, index) => {
                    const used = b.used !== undefined ? b.used : (b.total - b.balance);
                    const percentageUsed = b.total > 0 ? Math.round((used / b.total) * 100) : 0;
                    const percentageRemaining = 100 - percentageUsed;
                    const safeType = b.type || 'Leave';
                    const isAnnual = safeType.toLowerCase().includes('annual');
                    
                    // Determine urgency level
                    let urgencyClass, urgencyBg, urgencyText, urgencyIcon, microGuidance;
                    if (b.balance === 0) {
                        urgencyClass = 'danger';
                        urgencyBg = 'bg-danger bg-opacity-10';
                        urgencyText = 'text-danger';
                        urgencyIcon = 'ti-alert-circle';
                        microGuidance = '<span class="badge bg-danger-subtle text-danger-emphasis small"><i class="ti ti-alert-circle me-1"></i>Exhausted</span>';
                    } else if (b.balance <= b.total * 0.25) {
                        urgencyClass = 'warning';
                        urgencyBg = 'bg-warning bg-opacity-10';
                        urgencyText = 'text-warning';
                        urgencyIcon = 'ti-alert-triangle';
                        microGuidance = '<span class="badge bg-warning-subtle text-warning-emphasis small"><i class="ti ti-alert-triangle me-1"></i>Almost exhausted</span>';
                    } else if (b.balance <= b.total * 0.5) {
                        urgencyClass = 'info';
                        urgencyBg = 'bg-info bg-opacity-10';
                        urgencyText = 'text-info';
                        urgencyIcon = 'ti-info-circle';
                        microGuidance = '<span class="badge bg-info-subtle text-info-emphasis small"><i class="ti ti-info-circle me-1"></i>Running low</span>';
                    } else {
                        urgencyClass = 'success';
                        urgencyBg = 'bg-success bg-opacity-10';
                        urgencyText = 'text-success';
                        urgencyIcon = 'ti-circle-check';
                        microGuidance = '';
                    }
                    
                    // Card size: Annual leave gets full width on mobile, half on desktop
                    const colClass = isAnnual ? 'col-12 col-md-6' : 'col-6 col-md-3';
                    const cardBorder = isAnnual ? `border-start border-4 border-${urgencyClass}` : '';
                    
                    summaryHTML += `
                        <div class="${colClass}">
                            <div class="card h-100 shadow-sm ${cardBorder}" style="transition: transform 0.2s;">
                                <div class="card-body p-3">
                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                        <div class="d-flex align-items-center">
                                            ${isAnnual ? `<span class="badge bg-primary-subtle text-primary-emphasis me-2 small">Primary</span>` : ''}
                                            <h6 class="mb-0 ${isAnnual ? 'fw-bold' : 'fw-medium'} ${!isAnnual ? 'text-muted small' : ''}">${escapeHTML(safeType)}</h6>
                                        </div>
                                        ${microGuidance}
                                    </div>
                                    
                                    <!-- Remaining Days - Primary Focus -->
                                    <div class="d-flex align-items-baseline mb-2">
                                        <span class="fs-${isAnnual ? '2' : '4'} fw-bold ${urgencyText}">${b.balance}</span>
                                        <span class="text-muted ms-1 ${isAnnual ? '' : 'small'}">day${b.balance !== 1 ? 's' : ''} remaining</span>
                                    </div>
                                    
                                    <!-- Progress Bar -->
                                    <div class="progress mb-2" style="height: ${isAnnual ? '8px' : '6px'};">
                                        <div class="progress-bar bg-${urgencyClass}" role="progressbar" style="width: ${percentageUsed}%;" aria-valuenow="${used}" aria-valuemin="0" aria-valuemax="${b.total}"></div>
                                    </div>
                                    
                                    <!-- Secondary Info -->
                                    <div class="d-flex justify-content-between align-items-center">
                                        <small class="text-muted">${used} of ${b.total} used</small>
                                        <small class="${urgencyText} fw-semibold">${percentageRemaining}% left</small>
                                    </div>
                                </div>
                            </div>
                        </div>`;
                });
                
                summaryHTML += `
                    </div>
                </div>`;
                
                await SkeletonFactory.hide('#leave-summary-row', summaryHTML);
                
                // Add hover effect via JS
                $('#leave-summary-row .card').hover(
                    function() { $(this).css('transform', 'translateY(-2px)'); },
                    function() { $(this).css('transform', 'translateY(0)'); }
                );
            }
            else {
                SkeletonFactory.hide('#leave-summary-row', '<div class="col-12"><div class="alert alert-warning mb-0">Unable to load leave summary right now.</div></div>');
            }
        }).catch(() => {
            SkeletonFactory.hide('#leave-summary-row', '<div class="col-12"><div class="alert alert-warning mb-0">Unable to load leave summary right now.</div></div>');
        });
    }

    // Check leave balance and show warning if exceeded
    function checkLeaveBalance() {
        const startDate = $('#startDate').val();
        const endDate = $('#endDate').val();
        const leaveType = $('#leaveTypeSelect').val();
        const balanceContainer = $('#balanceWarningContainer');
        
        // Hide container if any field is missing
        if (!startDate || !endDate || !leaveType) {
            balanceContainer.hide();
            return;
        }
        
        // Calculate requested days
        const saturPolicy = document.querySelector('[data-saturday-policy]')?.dataset?.saturdayPolicy || 'none';
        const requestedDays = getActualLeaveDays(startDate, endDate, saturPolicy);
        
        // Fetch current balance from API
        fetch('/hrms/api/api_leaves.php?action=get_leave_summary')
            .then(res => res.json())
            .then(result => {
                if (result.success && result.data.balances) {
                    const balance = result.data.balances.find(b => b.type === leaveType);
                    if (balance) {
                        const remaining = Number(balance.balance) || 0;
                        const total = Number(balance.total) || 0;
                        const used = Number(balance.used ?? (total - remaining)) || 0;
                        const afterRequest = used + requestedDays;
                        const willExceed = afterRequest > total;
                        
                        let html = `
                            <div class="alert ${willExceed ? 'alert-warning' : 'alert-success'} py-2 mb-0">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <strong>${escapeHTML(leaveType)} Balance:</strong>
                                        <span class="ms-2">${remaining} days remaining</span>
                                        <small class="text-muted ms-2">(${used}/${total} used)</small>
                                    </div>
                                </div>
                                ${willExceed ? `
                                <hr class="my-2">
                                <div class="d-flex align-items-center">
                                    <i class="ti ti-alert-triangle text-warning me-2"></i>
                                    <small><strong>Warning:</strong> This request (${requestedDays} days) exceeds your remaining balance by ${afterRequest - total} day(s). Extra days will be charged/deducted from salary.</small>
                                </div>
                                ` : `
                                <hr class="my-2">
                                <div class="d-flex align-items-center text-success">
                                    <i class="ti ti-circle-check me-2"></i>
                                    <small>After this request: ${afterRequest}/${total} days used</small>
                                </div>
                                `}
                            </div>
                        `;
                        balanceContainer.html(html).show();
                    } else {
                        balanceContainer.hide();
                    }
                }
            })
            .catch(() => balanceContainer.hide());
    }

    // Event delegation for policy document link
    $(document).on('click', '.view-policy-btn', function(e) {
        e.preventDefault();
        const docId = $(this).data('doc-id');
        if (docId) {
            window.open('/hrms/pages/pdf_viewer.php?id=' + encodeURIComponent(docId), '_blank');
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

    // Approve leave function for dropdown action
    function approveLeave(leaveId) {
        if (!leaveId) {
            showToast('Invalid leave request.', 'error');
            return;
        }
        
        const formData = new FormData();
        formData.append('action', 'approve_or_reject');
        formData.append('leave_id', leaveId);
        formData.append('action_type', 'approve');
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
            })
            .catch(err => showToast('Error approving leave', 'error'));
    }

    // Reject leave function for dropdown action
    function rejectLeave(leaveId) {
        if (!leaveId) {
            showToast('Invalid leave request.', 'error');
            return;
        }
        
        const formData = new FormData();
        formData.append('action', 'approve_or_reject');
        formData.append('leave_id', leaveId);
        formData.append('action_type', 'reject');
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
            })
            .catch(err => showToast('Error rejecting leave', 'error'));
    }

    // Event delegation for approve/reject buttons
    $(document).on('click', '.approve-leave-btn, .reject-leave-btn', function() {
        const leaveId = $(this).data('leave-id');
        const isApprove = $(this).hasClass('approve-leave-btn');
        
        if (!leaveId) {
            showToast('Invalid leave request.', 'error');
            return;
        }

        const action = isApprove ? 'approve' : 'reject';
        const btnClass = isApprove ? 'btn-success' : 'btn-danger';
        
        // Get the row data from DataTable to show balance info
        const row = approveRequestsTable.rows().data().toArray().find(r => r.id == leaveId);
        let confirmMessage = `Are you sure you want to ${action} this leave request?`;
        
        // Add balance info for approval confirmation
        if (isApprove && row && row.balance_info) {
            const bal = row.balance_info;
            const saturPolicy = document.querySelector('[data-saturday-policy]')?.dataset?.saturdayPolicy || 'none';
            const requestDays = getActualLeaveDays(row.start_date, row.end_date, saturPolicy);
            const afterApproval = bal.approved_days + requestDays;
            const willExceed = afterApproval > bal.total_allowed;
            
            confirmMessage = `
                <div class="text-start">
                    <p><strong>Employee Leave Balance (${escapeHTML(bal.leave_type)}):</strong></p>
                    <ul class="mb-2">
                        <li>Total Allowed: <strong>${bal.total_allowed} days</strong></li>
                        <li>Already Approved: <strong>${bal.approved_days} days</strong></li>
                        <li>Other Pending: <strong>${bal.pending_days - requestDays} days</strong></li>
                        <li>This Request: <strong>${requestDays} days</strong></li>
                    </ul>
                    ${willExceed ? 
                        `<div class="alert alert-warning py-2 mb-2"><i class="ti ti-alert-triangle me-1"></i><strong>Warning:</strong> Approving this will exceed the allowed limit by ${afterApproval - bal.total_allowed} day(s). Extra days may be charged.</div>` 
                        : `<div class="alert alert-success py-2 mb-2"><i class="ti ti-circle-check me-1"></i>Within allowed limit. After approval: ${afterApproval}/${bal.total_allowed} days used.</div>`
                    }
                    <p>Do you want to ${action} this request?</p>
                </div>
            `;
        }
        
        showConfirmationModal(
            confirmMessage,
            () => {
                const formData = new FormData();
                formData.append('action', 'approve_or_reject');
                formData.append('leave_id', leaveId);
                formData.append('action_type', action);
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
        // Parse dates correctly using UTC to avoid timezone issues
        const startDate = new Date(startDateStr + 'T00:00:00Z');
        const endDate = new Date(endDateStr + 'T00:00:00Z');
        
        // Calculate total calendar days
        const totalDays = Math.floor((endDate - startDate) / (1000 * 60 * 60 * 24)) + 1;
        
        // Initialize counters
        let sundaysSkipped = 0;
        let saturdaysSkipped = 0;
        let actualDays = 0;
        
        // Get Saturday policy from data attribute
        const saturdayPolicy = document.querySelector('[data-saturday-policy]')?.dataset?.saturdayPolicy || 'none';
        
        // Loop through each day
        let currentDate = new Date(startDate);
        while (currentDate <= endDate) {
            const dayOfWeek = currentDate.getUTCDay(); // 0 = Sunday, 6 = Saturday
            
            // Check if it's a Sunday
            if (dayOfWeek === 0) {
                sundaysSkipped++;
            }
            // Check if it's a Saturday
            else if (dayOfWeek === 6) {
                if (saturdayPolicy === 'all') {
                    saturdaysSkipped++;
                } else if (saturdayPolicy === '1st_3rd' || saturdayPolicy === '2nd_4th') {
                    const dayOfMonth = currentDate.getUTCDate();
                    const saturdayOfMonth = Math.ceil(dayOfMonth / 7);
                    if (
                        (saturdayPolicy === '1st_3rd' && (saturdayOfMonth === 1 || saturdayOfMonth === 3)) ||
                        (saturdayPolicy === '2nd_4th' && (saturdayOfMonth === 2 || saturdayOfMonth === 4))
                    ) {
                        saturdaysSkipped++;
                    } else {
                        actualDays++;
                    }
                } else {
                    actualDays++;
                }
            } else {
                actualDays++;
            }
            
            currentDate.setUTCDate(currentDate.getUTCDate() + 1);
        }
        
        // Update UI
        $('#totalDaysText').text(totalDays);
        
        if (sundaysSkipped > 0) {
            $('#sundaysText').text('- ' + sundaysSkipped + ' Sunday(s)').show();
        } else {
            $('#sundaysText').hide();
        }
        
        if (saturdaysSkipped > 0) {
            $('#saturdaysText').text('- ' + saturdaysSkipped + ' Saturday(s)').show();
        } else {
            $('#saturdaysText').hide();
        }
        
        $('#holidaysText').hide(); 
        $('#actualDaysText').text(actualDays);
        $('#leaveDaysCalculation').show();
    }

    function getActualLeaveDays(startDateStr, endDateStr, saturdayPolicy) {
        const startDate = new Date(startDateStr + 'T00:00:00Z');
        const endDate = new Date(endDateStr + 'T00:00:00Z');
        
        // Calculate total calendar days
        const totalDays = Math.floor((endDate - startDate) / (1000 * 60 * 60 * 24)) + 1;
        
        // Initialize counters
        let sundaysSkipped = 0;
        let saturdaysSkipped = 0;
        let actualDays = 0;
        
        // Loop through each day
        let currentDate = new Date(startDate);
        while (currentDate <= endDate) {
            const dayOfWeek = currentDate.getUTCDay(); // 0 = Sunday, 6 = Saturday
            
            // Check if it's a Sunday - always skip
            if (dayOfWeek === 0) {
                sundaysSkipped++;
            }
            // Check if it's a Saturday
            else if (dayOfWeek === 6) {
                // Skip based on company policy
                if (saturdayPolicy === 'all') {
                    saturdaysSkipped++;
                } else if (saturdayPolicy === '1st_3rd' && isFirst3rdSaturday(currentDate)) {
                    saturdaysSkipped++;
                } else if (saturdayPolicy === '2nd_4th' && isSecond4thSaturday(currentDate)) {
                    saturdaysSkipped++;
                } else {
                    actualDays++;
                }
            } else {
                actualDays++;
            }
            
            currentDate.setUTCDate(currentDate.getUTCDate() + 1);
        }
        
        return actualDays;
    }

    function isFirst3rdSaturday(date) {
        const saturdays = [];
        const month = date.getUTCMonth();
        const year = date.getUTCFullYear();
        
        let current = new Date(Date.UTC(year, month, 1));
        while (current.getUTCMonth() === month) {
            if (current.getUTCDay() === 6) {
                saturdays.push(new Date(current));
            }
            current.setUTCDate(current.getUTCDate() + 1);
        }
        
        return saturdays.length >= 3 && (date.getTime() === saturdays[0].getTime() || date.getTime() === saturdays[2].getTime());
    }

    function isSecond4thSaturday(date) {
        const saturdays = [];
        const month = date.getUTCMonth();
        const year = date.getUTCFullYear();
        
        let current = new Date(Date.UTC(year, month, 1));
        while (current.getUTCMonth() === month) {
            if (current.getUTCDay() === 6) {
                saturdays.push(new Date(current));
            }
            current.setUTCDate(current.getUTCDate() + 1);
        }
        
        return saturdays.length >= 4 && (date.getTime() === saturdays[1].getTime() || date.getTime() === saturdays[3].getTime());
    }
 </script>

