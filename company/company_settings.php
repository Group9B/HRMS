<?php
require_once '../config/db.php';
require_once '../includes/functions.php';
$title = "Company Profile & Settings";

// --- SECURITY & SESSION ---
if (!isLoggedIn() || $_SESSION['role_id'] !== 2) {
    redirect("/hrms/pages/unauthorized.php");
}
$company_id = $_SESSION['company_id'];

// Fetch the company's current details to pre-populate the form
$company_result = query($mysqli, "SELECT * FROM companies WHERE id = ?", [$company_id]);
$company = ($company_result['success'] && !empty($company_result['data'])) ? $company_result['data'][0] : null;

if (!$company) {
    // Handle case where company data might be missing
    die("Error: Could not retrieve company information.");
}

require_once '../components/layout/header.php';
?>

<div class="d-flex">
    <?php require_once '../components/layout/sidebar.php'; ?>
    <div class="p-3 p-md-4" style="flex: 1;">
        <h2 class="h3 mb-4 text-gray-800"><i class="ti ti-building me-2"></i>Company Profile & Settings</h2>

        <div class="card shadow-sm">
            <div class="card-header">
                <h6 class="m-0 font-weight-bold">Company Information</h6>
            </div>
            <div class="card-body">
                <form id="companySettingsForm">
                    <input type="hidden" name="action" value="update_settings">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="name" class="form-label">Company Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="name" name="name"
                                value="<?= htmlspecialchars($company['name']) ?>" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="email" class="form-label">Public Email <span
                                    class="text-danger">*</span></label>
                            <input type="email" class="form-control" id="email" name="email"
                                value="<?= htmlspecialchars($company['email']) ?>" required>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="address" class="form-label">Company Address</label>
                        <textarea class="form-control" id="address" name="address"
                            rows="3"><?= htmlspecialchars($company['address']) ?></textarea>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="phone" class="form-label">Phone Number</label>
                            <input type="tel" class="form-control" id="phone" name="phone"
                                value="<?= htmlspecialchars($company['phone']) ?>">
                        </div>
                    </div>

                    <hr class="my-4">

                    <button type="submit" class="btn btn-primary">
                        <i class="ti ti-device-floppy me-2"></i>Save Changes
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
<?php require_once '../components/layout/footer.php'; ?>

<script>
    $(function () {
        $('#companySettingsForm').on('submit', function (e) {
            e.preventDefault();
            const formData = new FormData(this);
            const saveButton = $(this).find('button[type="submit"]');
            const originalButtonText = saveButton.html();

            saveButton.prop('disabled', true).html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Saving...');

            fetch('/hrms/api/api_company_settings.php', {
                method: 'POST',
                body: formData
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showToast(data.message, 'success');
                    } else {
                        showToast(data.message || 'An error occurred.', 'error');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showToast('An unexpected network error occurred.', 'error');
                })
                .finally(() => {
                    saveButton.prop('disabled', false).html(originalButtonText);
                });
        });
    });
</script>