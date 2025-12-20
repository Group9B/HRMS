<?php
require_once '../config/db.php';
require_once '../includes/functions.php';
$title = "System Configuration";

if (!isLoggedIn() || $_SESSION['role_id'] !== 1) {
    redirect("/hrms/pages/unauthorized.php");
}

$settings_result = query($mysqli, "SELECT * FROM system_settings");
$settings = [];
if ($settings_result['success']) {
    foreach ($settings_result['data'] as $row) {
        $settings[$row['setting_key']] = $row;
    }
}

require_once '../components/layout/header.php';
?>

<div class="d-flex">
    <?php require_once '../components/layout/sidebar.php'; ?>
    <div class="p-3 p-md-4" style="flex: 1;">
        <h2 class="h3 mb-4"><i class="ti ti-settings me-2"></i>System Configuration</h2>

        <div class="row">
            <div class="col-lg-7 mb-4">
                <div class="card shadow-sm h-100">
                    <div class="card-header">
                        <h6 class="m-0 font-weight-bold">Application Settings</h6>
                    </div>
                    <div class="card-body">
                        <form id="settingsForm">
                            <?php foreach ($settings as $setting): ?>
                                <?= render_setting_field($setting) ?>
                            <?php endforeach; ?>
                            <button type="submit" class="btn btn-primary">Save Settings</button>
                        </form>
                    </div>
                </div>
            </div>
            <div class="col-lg-5 mb-4">
                <div class="card shadow-sm h-100">
                    <div class="card-header">
                        <h6 class="m-0 font-weight-bold">Global Holiday Management</h6>
                    </div>
                    <div class="card-body">
                        <p>Import official Indian public holidays for a specific year from Google Calendar.</p>
                        <form id="importHolidaysForm" class="d-flex mb-3">
                            <select name="year" class="form-select me-2">
                                <?php for ($y = date('Y') - 1; $y <= date('Y') + 2; $y++): ?>
                                    <option value="<?= $y ?>" <?= $y == date('Y') ? 'selected' : '' ?>><?= $y ?></option>
                                <?php endfor; ?>
                            </select>
                            <button type="submit" class="btn btn-info text-white">Import</button>
                        </form>
                        <hr>
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <h6 class="m-0">Master Holiday List</h6>
                            <button class="btn btn-primary btn-sm" onclick="prepareHolidayModal()"><i
                                    class="ti ti-plus me-1"></i> Add Manually</button>
                        </div>
                        <div class="table-responsive">
                            <table id="holidaysTable" class="table table-sm" width="100%">
                                <thead>
                                    <tr>
                                        <th>Holiday</th>
                                        <th>Date</th>
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

<!-- Holiday Modal -->
<div class="modal fade" id="holidayModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="holidayForm">
                <div class="modal-header">
                    <h5 class="modal-title" id="holidayModalLabel"></h5><button type="button" class="btn-close"
                        data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body"><input type="hidden" name="id" id="holidayId">
                    <div class="mb-3"><label class="form-label">Holiday Name *</label><input type="text"
                            class="form-control" name="holiday_name" required></div>
                    <div class="mb-3"><label class="form-label">Date *</label><input type="date" class="form-control"
                            name="holiday_date" required></div>
                </div>
                <div class="modal-footer"><button type="button" class="btn btn-secondary"
                        data-bs-dismiss="modal">Cancel</button><button type="submit" class="btn btn-primary">Save
                        Holiday</button></div>
            </form>
        </div>
    </div>
</div>

<?php require_once '../components/layout/footer.php'; ?>

<script>
    let holidayTable, holidayModal;
    $(function () {
        holidayModal = new bootstrap.Modal('#holidayModal');

        $('#settingsForm').on('submit', function (e) {
            e.preventDefault();
            const formData = new FormData();
            formData.append('action', 'save_settings');
            $(this).find('input, select').each(function () {
                formData.append(`settings[${this.name}]`, this.type === 'checkbox' ? (this.checked ? 1 : 0) : this.value);
            });
            fetch('/hrms/api/api_settings.php', { method: 'POST', body: formData })
                .then(res => res.json()).then(result => {
                    showToast(result.message, result.success ? 'success' : 'error');
                });
        });

        $('#importHolidaysForm').on('submit', function (e) {
            e.preventDefault();
            const btn = $(this).find('button');
            btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span>');
            const formData = new FormData(this);
            formData.append('action', 'import_holidays');
            fetch('/hrms/api/api_settings.php', { method: 'POST', body: formData })
                .then(res => res.json()).then(result => {
                    showToast(result.message, result.success ? 'success' : 'error');
                    if (result.success) holidayTable.ajax.reload();
                }).finally(() => {
                    btn.prop('disabled', false).text('Import');
                });
        });

        holidayTable = $('#holidaysTable').DataTable({
            ajax: { url: '/hrms/api/api_settings.php?action=get_global_holidays', dataSrc: 'data' },
            columns: [
                { data: 'holiday_name' },
                { data: 'holiday_date', render: d => new Date(d + 'T00:00:00').toLocaleDateString() },
                {
                    data: null, orderable: false, render: (d, t, r) => `
                <div class="btn-group btn-group-sm">
                    <button class="btn btn-outline-primary" onclick='prepareHolidayModal(${JSON.stringify(r)})'><i class="ti ti-edit"></i></button>
                    <button class="btn btn-outline-danger" onclick="deleteHoliday(${r.id})"><i class="ti ti-trash"></i></button>
                </div>`
                }
            ]
        });

        $('#holidayForm').on('submit', function (e) {
            e.preventDefault();
            const formData = new FormData(this);
            formData.append('action', 'add_edit_global_holiday');
            fetch('/hrms/api/api_settings.php', { method: 'POST', body: formData })
                .then(res => res.json()).then(result => {
                    if (result.success) {
                        showToast(result.message, 'success');
                        holidayModal.hide();
                        holidayTable.ajax.reload();
                    } else {
                        showToast(result.message, 'error');
                    }
                });
        });
    });

    function prepareHolidayModal(data = null) {
        $('#holidayForm').trigger('reset');
        if (data) {
            $('#holidayModalLabel').text('Edit Holiday');
            $('#holidayId').val(data.id);
            $('[name="holiday_name"]').val(data.holiday_name);
            $('[name="holiday_date"]').val(data.holiday_date);
        } else {
            $('#holidayModalLabel').text('Add Holiday Manually');
            $('#holidayId').val(0);
        }
        holidayModal.show();
    }

    function deleteHoliday(id) {
        if (confirm('Are you sure you want to delete this global holiday?')) {
            const formData = new FormData();
            formData.append('action', 'delete_global_holiday');
            formData.append('id', id);
            fetch('/hrms/api/api_settings.php', { method: 'POST', body: formData })
                .then(res => res.json()).then(result => {
                    showToast(result.message, result.success ? 'success' : 'error');
                    if (result.success) holidayTable.ajax.reload();
                });
        }
    }
</script>