<?php
require_once '../config/db.php';
require_once '../includes/functions.php';
$title = "System Configuration";

if (!isLoggedIn() || $_SESSION['role_id'] !== 1) { // Super Admin Only
    redirect("/hrms/pages/unauthorized.php");
}

$settings_result = query($mysqli, "SELECT * FROM system_settings");
$settings = [];
if ($settings_result['success']) {
    foreach ($settings_result['data'] as $row) {
        $settings[$row['setting_key']] = $row;
    }
}

// Helper function to render a setting field
function renderSettingField($setting)
{
    $key = htmlspecialchars($setting['setting_key']);
    $value = htmlspecialchars($setting['setting_value']);
    $label = ucwords(str_replace('_', ' ', $key));
    $description = htmlspecialchars($setting['description']);

    echo "<div class='mb-3'><label for='$key' class='form-label'>$label</label>";
    if ($key === 'maintenance_mode') {
        echo "<select class='form-select' id='$key' name='$key'>";
        echo "<option value='0'" . ($value == '0' ? ' selected' : '') . ">Off</option>";
        echo "<option value='1'" . ($value == '1' ? ' selected' : '') . ">On</option>";
        echo "</select>";
    } else {
        echo "<input type='text' class='form-control' id='$key' name='$key' value='$value'>";
    }
    echo "<div class='form-text'>$description</div></div>";
}

require_once '../components/layout/header.php';
?>

<div class="d-flex">
    <?php require_once '../components/layout/sidebar.php'; ?>
    <div class="p-3 p-md-4" style="flex: 1;">
        <h2 class="h3 mb-4"><i class="fas fa-cogs me-2"></i>System Configuration</h2>
        <div class="row">
            <div class="col-lg-6 mb-4">
                <div class="card shadow-sm h-100">
                    <div class="card-header">
                        <h6 class="m-0">Application Settings</h6>
                    </div>
                    <div class="card-body">
                        <form id="settingsForm">
                            <?php foreach ($settings as $setting)
                                renderSettingField($setting); ?>
                            <button type="submit" class="btn btn-primary">Save Settings</button>
                        </form>
                    </div>
                </div>
            </div>
            <div class="col-lg-6 mb-4">
                <div class="card shadow-sm h-100">
                    <div class="card-header">
                        <h6 class="m-0">Global Holiday Management</h6>
                    </div>
                    <div class="card-body">
                        <form id="importHolidaysForm" class="mb-3">
                            <label class="form-label">Import Indian Public Holidays for Year:</label>
                            <div class="input-group">
                                <select name="year" class="form-select">
                                    <?php for ($y = date('Y') - 1; $y <= date('Y') + 2; $y++): ?>
                                        <option value="<?= $y ?>" <?= $y == date('Y') ? 'selected' : '' ?>><?= $y ?></option>
                                    <?php endfor; ?>
                                </select>
                                <button type="submit" class="btn btn-primary">Import</button>
                            </div>
                        </form>
                        <hr>
                        <table class="table table-sm" id="globalHolidaysTable" width="100%">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Holiday</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once '../components/layout/footer.php'; ?>

<script>
    $(function () {
        $('#settingsForm').on('submit', function (e) {
            e.preventDefault();
            const formData = new FormData(this);
            formData.append('action', 'save_settings');
            fetch('/hrms/api/api_settings.php', { method: 'POST', body: formData })
                .then(res => res.json()).then(result => {
                    if (result.success) showToast(result.message, 'success');
                    else showToast(result.message, 'error');
                });
        });

        const holidaysTable = $('#globalHolidaysTable').DataTable({
            ajax: { url: '/hrms/api/api_settings.php?action=get_global_holidays', dataSrc: 'data' },
            columns: [
                { data: 'holiday_date' },
                { data: 'holiday_name' },
                { data: null, orderable: false, render: (d, t, r) => `<button class="btn btn-sm btn-outline-danger" onclick="deleteGlobalHoliday(${r.id})"><i class="fas fa-trash"></i></button>` }
            ],
            order: [[0, 'asc']],
            pageLength: 5,
            lengthChange: false
        });

        $('#importHolidaysForm').on('submit', function (e) {
            e.preventDefault();
            const formData = new FormData(this);
            formData.append('action', 'import_holidays');
            const btn = $(this).find('button');
            btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span>');

            fetch('/hrms/api/api_settings.php', { method: 'POST', body: formData })
                .then(res => res.json()).then(result => {
                    showToast(result.message, result.success ? 'success' : 'error');
                    holidaysTable.ajax.reload();
                }).finally(() => {
                    btn.prop('disabled', false).text('Import');
                });
        });
    });

    function deleteGlobalHoliday(id) {
        if (confirm('Are you sure you want to delete this global holiday?')) {
            const formData = new FormData();
            formData.append('action', 'delete_global_holiday');
            formData.append('id', id);
            fetch('/hrms/api/api_settings.php', { method: 'POST', body: formData })
                .then(res => res.json()).then(result => {
                    if (result.success) {
                        showToast(result.message, 'success');
                        $('#globalHolidaysTable').DataTable().ajax.reload();
                    } else { showToast(result.message, 'error'); }
                });
        }
    }
</script>