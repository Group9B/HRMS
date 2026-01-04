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

<div class="modal fade" id="applyLeaveModal" tabindex="-1"><div class="modal-dialog"><div class="modal-content"><form id="applyLeaveForm"><div class="modal-header"><h5 class="modal-title">Apply for Leave</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div><div class="modal-body"><input type="hidden" name="action" value="apply_leave"><div class="row"><div class="col-md-6 mb-3"><label class="form-label">Start Date *</label><input type="date" class="form-control" name="start_date" id="startDate" required></div><div class="col-md-6 mb-3"><label class="form-label">End Date *</label><input type="date" class="form-control" name="end_date" id="endDate" required></div></div><div class="mb-3" id="dateErrorContainer" style="display: none;"><div class="alert alert-danger mb-0" id="dateError"></div></div><div class="mb-3" id="leaveDaysCalculation" style="display: none;"><div class="alert alert-info mb-0"><small><strong>Calculation:</strong> <span id="totalDaysText">0</span> calendar days</small><br><small id="holidaysText" style="display: none;"></small><small id="sundaysText" style="display: none;"></small><small id="saturdaysText" style="display: none;"></small><br><small class="text-primary"><strong>Actual days to deduct:</strong> <span id="actualDaysText">0</span></small></div></div><div class="mb-3"><label class="form-label">Leave Type *</label><select class="form-select" name="leave_type" required><option value="">-- Select --</option><?php foreach ($leave_types as $type) : ?><option value="<?= htmlspecialchars($type['leave_type']) ?>"><?= htmlspecialchars($type['leave_type']) ?></option><?php endforeach; ?></select></div><div class="mb-3"><label class="form-label">Reason</label><textarea class="form-control" name="reason" rows="3"></textarea></div></div><div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button><button type="submit" class="btn btn-primary" id="submitLeaveBtn">Submit Request</button></div></form></div></div></div>

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
                    { data: null, render: (d, t, r) => {
                        const saturPolicy = document.querySelector('[data-saturday-policy]').dataset.saturdayPolicy;
                        return getActualLeaveDays(r.start_date, r.end_date, saturPolicy);
                    }},
                    { data: 'status', render: (d) => `<span class="badge bg-${getStatusClass(d)}-subtle text-${getStatusClass(d)}-emphasis">${capitalize(d)}</span>` },
                    { data: null, orderable: false, render: (d, t, r) => r.status === 'pending' ? `<button class="btn btn-sm btn-outline-danger cancel-leave-btn" data-leave-id="${escapeHTML(r.id)}" title="Cancel">Cancel</button>` : '---' }
                ], order: [[1, 'desc']]
            });
        }
        if (canApprove) {
            approveRequestsTable = $('#approveRequestsTable').DataTable({
                responsive: true, ajax: { url: '/hrms/api/api_leaves.php?action=get_pending_requests', dataSrc: 'data' },
                columns: [
                    { data: null, render: (d, t, r) => `<a href="/hrms/employee/profile.php?emp_id=${r.emp_id}" class="text-decoration-none text-body">${escapeHTML(r.first_name)} ${escapeHTML(r.last_name)}<i class="ti ti-arrow-up-right"></i></a>` },
                    { data: 'leave_type' },
                    { data: null, render: (d, t, r) => `${formatDate(r.start_date)} to ${formatDate(r.end_date)}` },
                    { data: null, render: (d, t, r) => {
                        const saturPolicy = document.querySelector('[data-saturday-policy]').dataset.saturdayPolicy;
                        return getActualLeaveDays(r.start_date, r.end_date, saturPolicy);
                    }},
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
                
                // Create single accordion containing all three cards
                let summaryHTML = `<div class="col-12 mb-4">
                    <div class="accordion shadow-sm" id="leaveSummaryAccordion">
                        <div class="accordion-item">
                            <h2 class="accordion-header">
                                <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#allSummaryCollapse" aria-expanded="true" aria-controls="allSummaryCollapse">
                                    <i class="ti ti-layout-grid me-2"></i> Leave Summary
                                </button>
                            </h2>
                            <div id="allSummaryCollapse" class="accordion-collapse collapse show" data-bs-parent="#leaveSummaryAccordion">
                                <div class="accordion-body p-3">
                                    <div class="row">`;
                
                // Card 1 - Leave Balance
                if (balances.length > 0) {
                    summaryHTML += `<div class="col-lg-6 col-md-12 mb-3">
                        <div class="card h-100">
                            <div class="card-header">
                                <h6 class="mb-0"><i class="ti ti-wallet me-2"></i>Leave Balance</h6>
                            </div>
                            <div class="card-body p-0">
                                <ul class="list-group list-group-flush">`;
                    balances.forEach((b) => {
                        const used = b.used !== undefined ? b.used : (b.total - b.balance);
                        const percentage = (used / b.total * 100).toFixed(0);
                        const progressColor = b.balance > b.total * 0.5 ? 'success' : (b.balance > b.total * 0.25 ? 'warning' : 'danger');
                        summaryHTML += `<li class="list-group-item d-flex justify-content-between align-items-center py-3">
                            <div class="flex-grow-1">
                                <p class="mb-2 fw-medium small">${escapeHTML(b.type)}</p>
                                <div class="progress" style="height: 6px;">
                                    <div class="progress-bar bg-${progressColor}" role="progressbar" style="width: ${percentage}%;" aria-valuenow="${used}" aria-valuemin="0" aria-valuemax="${b.total}"></div>
                                </div>
                                <small class="text-muted">${used} of ${b.total} days used</small>
                            </div>
                            <div class="text-end ms-3">
                                <p class="fs-6 fw-bold mb-0"><span class="text-${progressColor}">${b.balance}</span></p>
                            </div>
                        </li>`;
                    });
                    summaryHTML += `</ul>
                            </div>
                        </div>
                    </div>`;
                }
                
                // Card 2 - Upcoming Holiday
                summaryHTML += `<div class="col-lg-3 col-md-12 mb-3">
                    <div class="card h-100">
                        <div class="card-header">
                            <h6 class="mb-0"><i class="ti ti-calendar me-2"></i>Upcoming Holiday</h6>
                        </div>
                        <div class="card-body d-flex flex-column justify-content-center text-center">
                            ${next_holiday ? `<p class="fs-6 fw-bold mb-2">${escapeHTML(next_holiday.holiday_name)}</p><p class="text-muted mb-0 small">${formatDate(next_holiday.holiday_date, true)}</p>` : '<p class="text-muted small">No upcoming holidays</p>'}
                        </div>
                    </div>
                </div>`;
                
                // Card 3 - Company Policy
                summaryHTML += `<div class="col-lg-3 col-md-12 mb-3">
                    <div class="card h-100">
                        <div class="card-header">
                            <h6 class="mb-0"><i class="ti ti-file-text me-2"></i>Company Policy</h6>
                        </div>
                        <div class="card-body d-flex flex-column justify-content-center align-items-center">
                            ${policy_document ? `<button class="btn btn-outline-primary btn-sm view-policy-btn" data-doc-id="${escapeHTML(policy_document.id)}"><i class="ti ti-file-pdf me-2"></i>View Policy</button>` : '<p class="text-muted small mb-0">No policy available</p>'}
                        </div>
                    </div>
                </div>
                
                    </div>
                </div>
            </div>
        </div></div>`;
                
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
        
        showConfirmationModal(
            `Are you sure you want to ${action} this leave request?`,
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
        const saturdayPolicy = document.querySelector('[data-saturday-policy]')?.dataset.saturdayPolicy || 'none';
        
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

