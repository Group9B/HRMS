<?php
require_once '../config/db.php';
require_once '../includes/functions.php';
$title = "System Configuration";

// Security Check: Ensure the user is a logged-in Super Admin
if (!isLoggedIn() || $_SESSION['role_id'] !== 1) {
    redirect("/hrms/pages/unauthorized.php");
}

// Fetch all system settings from the database
$settings_result = query($mysqli, "SELECT setting_key, setting_value, description FROM system_settings ORDER BY id");
$settings_list = $settings_result['success'] ? $settings_result['data'] : [];

/**
 * Renders a form field for a given setting.
 * This function dynamically creates the correct HTML input based on the setting's key and value.
 *
 * @param array $setting An associative array for a single setting row.
 * @return void
 */
function render_setting_field(array $setting): void
{
    $key = htmlspecialchars($setting['setting_key']);
    $value = htmlspecialchars($setting['setting_value']);
    $description = htmlspecialchars($setting['description']);

    // Create a more readable label from the setting key
    $label = ucwords(str_replace('_', ' ', $key));

    echo '<div class="col-md-6 mb-4">';
    echo "<label for='{$key}' class='form-label'>{$label}</label>";

    // --- Smart Field Rendering Logic ---
    // Add more rules here as you create new types of settings

    // Render a dropdown for 'maintenance_mode'
    if ($key === 'maintenance_mode') {
        echo "<select class='form-select' id='{$key}' name='{$key}'>";
        echo "<option value='0'" . ($value == '0' ? ' selected' : '') . ">Off</option>";
        echo "<option value='1'" . ($value == '1' ? ' selected' : '') . ">On</option>";
        echo "</select>";

        // Render an email input if 'email' is in the key
    } elseif (strpos($key, 'email') !== false) {
        echo "<input type='email' class='form-control' id='{$key}' name='{$key}' value='{$value}'>";

        // Render a number input for keys related to numbers or limits
    } elseif (strpos($key, 'per_page') !== false || strpos($key, 'limit') !== false || strpos($key, 'size') !== false) {
        echo "<input type='number' class='form-control' id='{$key}' name='{$key}' value='{$value}'>";

        // Default to a standard text input
    } else {
        echo "<input type='text' class='form-control' id='{$key}' name='{$key}' value='{$value}'>";
    }

    echo "<small class='form-text text-muted'>{$description}</small>";
    echo '</div>';
}

require_once '../components/layout/header.php';
?>

<div class="d-flex">
    <?php require_once '../components/layout/sidebar.php'; ?>
    <div class="p-3 p-md-4" style="flex: 1;">
        <h2 class="h3 mb-4 text-gray-800"><i class="fas fa-cogs me-2"></i>System Configuration</h2>

        <div class="card shadow-sm">
            <div class="card-header">
                <h6 class="m-0 font-weight-bold">Application Settings</h6>
            </div>
            <div class="card-body">
                <form id="settingsForm">
                    <div class="row">
                        <?php
                        // Loop through the settings and render a field for each one
                        if (!empty($settings_list)) {
                            foreach ($settings_list as $setting) {
                                render_setting_field($setting);
                            }
                        } else {
                            echo '<p class="text-muted">No settings found in the database.</p>';
                        }
                        ?>
                    </div>

                    <hr class="my-3">

                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-2"></i>Save Changes
                    </button>
                </form>
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
            const saveButton = $(this).find('button[type="submit"]');
            const originalButtonText = saveButton.html();

            // Provide user feedback during submission
            saveButton.prop('disabled', true).html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Saving...');

            fetch('api_settings.php', {
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
                    // Restore button state
                    saveButton.prop('disabled', false).html(originalButtonText);
                });
        });
    });
</script>