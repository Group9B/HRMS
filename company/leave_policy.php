<?php
require_once '../config/db.php';
require_once '../includes/functions.php';
$title = "Leave & Holiday Policy";

if (!isLoggedIn() || !in_array($_SESSION['role_id'], [2, 3])) {
    redirect("/hrms/pages/unauthorized.php");
}

require_once '../components/layout/header.php';
?>

<div class="d-flex">
    <?php require_once '../components/layout/sidebar.php'; ?>
    <div class="p-3 p-md-4" style="flex: 1;">

        <ul class="nav nav-tabs" id="policyTabs" role="tablist">
            <li class="nav-item" role="presentation"><button class="nav-link active" data-bs-toggle="tab"
                    data-bs-target="#policies-tab" type="button">Leave Policies</button></li>
            <li class="nav-item" role="presentation"><button class="nav-link" data-bs-toggle="tab"
                    data-bs-target="#holidays-tab" type="button">Holiday Calendar</button></li>
            <li class="nav-item" role="presentation"><button class="nav-link" data-bs-toggle="tab"
                    data-bs-target="#documents-tab" type="button">Policy Documents</button></li>
        </ul>

        <div class="tab-content" id="policyTabsContent">
            <!-- Leave Policies Tab -->
            <div class="tab-pane fade show active" id="policies-tab" role="tabpanel">
                <div class="card shadow-sm mt-3">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h6 class="m-0">Manage Leave Types</h6><button class="btn btn-primary btn-sm"
                            onclick="preparePolicyModal()"><i class="ti ti-plus me-1"></i> Add Policy</button>
                    </div>
                    <div class="card-body">
                        <table class="table table-hover" id="policiesTable" width="100%">
                            <thead>
                                <tr>
                                    <th>Leave Type</th>
                                    <th>Days/Year</th>
                                    <th>Accruable</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Holidays Tab -->
            <div class="tab-pane fade" id="holidays-tab" role="tabpanel">
                <div class="row mt-3">
                    <div class="col-lg-8">
                        <div class="card shadow-sm h-100">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h6 class="m-0">My Company's Holidays</h6>
                                <div>
                                    <button class="btn btn-secondary btn-sm me-2" onclick="openImportModal()"><i
                                            class="ti ti-download me-1"></i> Import</button>
                                    <button class="btn btn-primary btn-sm" onclick="prepareHolidayModal()"><i
                                            class="ti ti-plus me-1"></i> Add Custom</button>
                                </div>
                            </div>
                            <div class="card-body">
                                <table class="table table-hover" id="holidaysTable" width="100%">
                                    <thead>
                                        <tr>
                                            <th>Date</th>
                                            <th>Holiday Name</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                </table>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-4 mt-sm-4 mt-lg-0">
                        <div class="card shadow-sm h-100">
                            <div class="card-header">
                                <h6 class="m-0">Work Week Settings</h6>
                            </div>
                            <div class="card-body">
                                <form id="holidaySettingsForm">
                                    <div class="mb-3">
                                        <label class="form-label">Saturday Policy</label>
                                        <select name="saturday_policy" class="form-select">
                                            <option value="none">All Saturdays are working days</option>
                                            <option value="1st_3rd">1st & 3rd Saturdays are holidays</option>
                                            <option value="2nd_4th">2nd & 4th Saturdays are holidays</option>
                                            <option value="all">All Saturdays are holidays</option>
                                        </select>
                                    </div>
                                    <button type="submit" class="btn btn-success">Save Settings</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Documents Tab -->
            <div class="tab-pane fade" id="documents-tab" role="tabpanel">
                <div class="row mt-3">
                    <div class="col-lg-5 mb-4">
                        <div class="card shadow-sm h-100">
                            <div class="card-header">
                                <h6 class="m-0">Upload New Document</h6>
                            </div>
                            <div class="card-body">
                                <form id="uploadDocForm" enctype="multipart/form-data">
                                    <div class="mb-3"><label class="form-label">Document Name *</label><input
                                            type="text" name="document_name" class="form-control"
                                            placeholder="e.g., Company Leave Policy 2025" required></div>
                                    <div class="mb-3"><label class="form-label">Policy File (PDF, max 5MB)
                                            *</label><input type="file" name="policy_document" class="form-control"
                                            accept=".pdf" required></div>
                                    <button type="submit" class="btn btn-primary">Upload</button>
                                </form>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-7 mb-4">
                        <div class="card shadow-sm h-100">
                            <div class="card-header">
                                <h6 class="m-0">Uploaded Documents</h6>
                            </div>
                            <div class="card-body">
                                <table class="table" id="documentsTable" width="100%">
                                    <thead>
                                        <tr>
                                            <th>Name</th>
                                            <th>Uploaded</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modals -->
<div class="modal fade" id="policyModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="policyForm">
                <div class="modal-header">
                    <h5 class="modal-title" id="policyModalLabel"></h5><button type="button" class="btn-close"
                        data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body"><input type="hidden" name="id" id="policyId">
                    <div class="mb-3"><label class="form-label">Leave Type *</label><input type="text"
                            class="form-control" name="leave_type" required></div>
                    <div class="mb-3"><label class="form-label">Days Per Year *</label><input type="number"
                            class="form-control" name="days_per_year" min="1" required></div>
                    <div class="form-check mb-3"><input class="form-check-input" type="checkbox" name="is_accruable"
                            id="isAccruable"><label class="form-check-label" for="isAccruable">Allow carry-over /
                            encashment</label></div>
                </div>
                <div class="modal-footer"><button type="button" class="btn btn-secondary"
                        data-bs-dismiss="modal">Cancel</button><button type="submit" class="btn btn-primary">Save
                        Policy</button></div>
            </form>
        </div>
    </div>
</div>
<div class="modal fade" id="holidayModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="holidayForm">
                <div class="modal-header">
                    <h5 class="modal-title">Add Custom Holiday</h5><button type="button" class="btn-close"
                        data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3"><label class="form-label">Holiday Name *</label><input type="text"
                            class="form-control" name="holiday_name" required></div>
                    <div class="mb-3"><label class="form-label">Date *</label><input type="date" class="form-control"
                            name="holiday_date" required></div>
                </div>
                <div class="modal-footer"><button type="button" class="btn btn-secondary"
                        data-bs-dismiss="modal">Cancel</button><button type="submit"
                        class="btn btn-primary">Add</button></div>
            </form>
        </div>
    </div>
</div>
<div class="modal fade" id="importHolidayModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form id="importHolidayForm">
                <div class="modal-header">
                    <h5 class="modal-title">Import Global Holidays</h5><button type="button" class="btn-close"
                        data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="d-flex justify-content-end mb-2"><button type="button" class="btn btn-link btn-sm"
                            id="selectAllHolidays">Select All</button><button type="button" class="btn btn-link btn-sm"
                            id="deselectAllHolidays">Deselect All</button></div>
                    <div id="importHolidayList" style="max-height: 50vh; overflow-y: auto;"></div>
                </div>
                <div class="modal-footer"><button type="button" class="btn btn-secondary"
                        data-bs-dismiss="modal">Cancel</button><button type="submit" class="btn btn-primary">Add
                        Selected to My Calendar</button></div>
            </form>
        </div>
    </div>
</div>

<?php require_once '../components/layout/footer.php'; ?>
<script>
    let tables = {}, modals = {};

    $(function () {
        Object.assign(modals, { policy: new bootstrap.Modal('#policyModal'), holiday: new bootstrap.Modal('#holidayModal'), import: new bootstrap.Modal('#importHolidayModal') });
        Object.assign(tables, {
            policies: $('#policiesTable').DataTable({ responsive: true, ajax: { url: '/hrms/api/api_policies.php?action=get_policies', dataSrc: 'data' }, columns: [{ data: 'leave_type' }, { data: 'days_per_year', className: 'text-center' }, { data: 'is_accruable', className: 'text-center', render: d => parseInt(d) ? '<i class="ti ti-circle-check text-success"></i>' : '<i class="ti ti-x text-danger"></i>' }, { data: null, orderable: false, render: (d, t, r) => `<div class="btn-group btn-group-sm"><button class="btn btn-outline-primary" onclick='preparePolicyModal(${JSON.stringify(r)})'><i class="ti ti-edit"></i></button><button class="btn btn-outline-danger" onclick="deleteItem('policy', ${r.id})"><i class="ti ti-trash"></i></button></div>` }] }),
            holidays: $('#holidaysTable').DataTable({ responsive: true, ajax: { url: '/hrms/api/api_policies.php?action=get_holidays', dataSrc: 'data' }, columns: [{ data: 'holiday_date', render: d => new Date(d + 'T00:00:00').toLocaleDateString('en-GB', { day: '2-digit', month: 'long', year: 'numeric' }) }, { data: 'holiday_name' }, { data: null, orderable: false, render: (d, t, r) => `<button class="btn btn-sm btn-outline-danger" onclick="deleteItem('holiday', ${r.id})"><i class="ti ti-trash"></i></button>` }], order: [[0, 'asc']] }),
            documents: $('#documentsTable').DataTable({ responsive: true, ajax: { url: '/hrms/api/api_policies.php?action=get_documents', dataSrc: 'data' }, columns: [{ data: 'document_name' }, { data: 'uploaded_at', render: d => new Date(d).toLocaleDateString() }, { data: null, orderable: false, render: (d, t, r) => `<div class="btn-group btn-group-sm"><a href="${r.file_path}" target="_blank" class="btn btn-outline-primary"><i class="ti ti-download"></i></a><button class="btn btn-outline-danger" onclick="deleteItem('document', ${r.id})"><i class="ti ti-trash"></i></button></div>` }] })
        });

        $('#policyForm').on('submit', handleFormSubmit('policy', 'add_edit_policy'));
        $('#holidayForm').on('submit', handleFormSubmit('holiday', 'add_holiday'));
        $('#uploadDocForm').on('submit', handleFormSubmit('document', 'upload_document'));
        $('#importHolidayForm').on('submit', handleFormSubmit('import', 'batch_add_holidays'));
        $('#holidaySettingsForm').on('submit', handleFormSubmit('settings', 'save_holiday_settings'));

        $('#selectAllHolidays').on('click', () => $('#importHolidayList input[type="checkbox"]').prop('checked', true));
        $('#deselectAllHolidays').on('click', () => $('#importHolidayList input[type="checkbox"]').prop('checked', false));

        loadHolidaySettings();
    });

    function handleFormSubmit(type, action) {
        return function (e) {
            e.preventDefault();
            const formData = new FormData(this);
            formData.append('action', action);
            fetch('/hrms/api/api_policies.php', { method: 'POST', body: formData })
                .then(res => res.json()).then(result => {
                    if (result.success) {
                        showToast(result.message, 'success');
                        if (modals[type]) modals[type].hide();
                        this.reset();
                        // Reload all tables on any success to ensure consistency
                        Object.values(tables).forEach(t => t.ajax.reload());
                    } else {
                        showToast(result.message, 'error');
                    }
                });
        }
    }

    function loadHolidaySettings() {
        fetch('/hrms/api/api_policies.php?action=get_holiday_settings')
            .then(res => res.json()).then(result => {
                if (result.success) {
                    $('[name="saturday_policy"]').val(result.data.saturday_policy);
                }
            });
    }

    function openImportModal() {
        const list = $('#importHolidayList');
        list.html('<div class="text-center"><div class="spinner-border"></div></div>');
        modals.import.show();
        fetch('/hrms/api/api_policies.php?action=get_unadded_global_holidays').then(res => res.json()).then(result => {
            list.empty();
            if (result.success && result.data.length > 0) {
                result.data.forEach(h => {
                    list.append(`<div class="form-check"><input class="form-check-input" type="checkbox" name="holiday_ids[]" value="${h.id}" id="h${h.id}"><label class="form-check-label" for="h${h.id}">${h.holiday_name} (${new Date(h.holiday_date + 'T00:00:00').toLocaleDateString()})</label></div>`);
                });
            } else {
                list.html('<p class="text-muted text-center">No new global holidays to import.</p>');
            }
        });
    }

    function preparePolicyModal(data = null) { $('#policyForm').trigger('reset'); if (data) { $('#policyModalLabel').text('Edit Leave Policy'); $('#policyId').val(data.id); $('[name="leave_type"]').val(data.leave_type); $('[name="days_per_year"]').val(data.days_per_year); $('#isAccruable').prop('checked', parseInt(data.is_accruable) === 1); } else { $('#policyModalLabel').text('Add Leave Policy'); $('#policyId').val(0); } modals.policy.show(); }
    function prepareHolidayModal() { $('#holidayForm').trigger('reset'); modals.holiday.show(); }
    function deleteItem(type, id) { if (confirm(`Are you sure you want to delete this ${type}?`)) { const formData = new FormData(); formData.append('action', `delete_${type}`); formData.append('id', id); fetch('/hrms/api/api_policies.php', { method: 'POST', body: formData }).then(res => res.json()).then(result => { if (result.success) { showToast(result.message, 'success'); tables[`${type}s`].ajax.reload(); } else { showToast(result.message, 'error'); } }); } }
</script>