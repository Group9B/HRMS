<?php
session_start();
require_once '../config/db.php';
require_once '../includes/functions.php';

requireAuth();

$title = "Change Password";
$user_id = $_SESSION['user_id'];

// Get current user details
$userRes = query($mysqli, "SELECT * FROM users WHERE id = ?", [$user_id]);
$user = $userRes['success'] && !empty($userRes['data']) ? $userRes['data'][0] : null;

if (!$user) {
    redirect("/hrms/pages/unauthorized.php");
}

// Handle password update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];

    if ($action === 'update_password') {
        $currentPassword = $_POST['current_password'] ?? '';
        $newPassword = $_POST['new_password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';

        if (empty($currentPassword) || empty($newPassword) || empty($confirmPassword)) {
            $_SESSION['error_message'] = 'All password fields are required.';
        } elseif ($newPassword !== $confirmPassword) {
            $_SESSION['error_message'] = 'New passwords do not match.';
        } elseif (strlen($newPassword) < 6) {
            $_SESSION['error_message'] = 'Password must be at least 6 characters.';
        } elseif (!password_verify($currentPassword, $user['password'])) {
            $_SESSION['error_message'] = 'Current password is incorrect.';
        } else {
            $hashedPassword = password_hash($newPassword, PASSWORD_BCRYPT);
            $updateRes = query($mysqli, "UPDATE users SET password = ? WHERE id = ?", [$hashedPassword, $user_id]);

            if ($updateRes['success']) {
                $_SESSION['success_message'] = 'Password changed successfully!';
                header('Location: /hrms/user/account.php');
                exit;
            } else {
                $_SESSION['error_message'] = 'Failed to change password.';
            }
        }
    }
}

require_once '../components/layout/header.php';
?>

<div class="d-flex">
    <?php require_once '../components/layout/sidebar.php'; ?>

    <div class="p-3 p-md-4" style="flex: 1;">
        <div class="row">
            <div class="col-12 col-lg-6 offset-lg-3">
                <div class="card shadow-sm">
                    <div class="card-header py-3 d-flex justify-content-between align-items-center">
                        <h5 class="mb-0"><i class="ti ti-lock me-2"></i>Change Password</h5>
                        <a href="/hrms/user/account.php" class="btn btn-outline-secondary btn-sm">
                            <i class="ti ti-arrow-left me-2"></i>Back
                        </a>
                    </div>
                    <div class="card-body">
                        <!-- Success/Error Messages -->
                        <?php if (!empty($_SESSION['success_message'])): ?>
                            <div class="alert alert-success alert-dismissible fade show" role="alert">
                                <i class="ti ti-circle-check me-2"></i>
                                <?= htmlspecialchars($_SESSION['success_message']) ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                            <?php unset($_SESSION['success_message']); ?>
                        <?php endif; ?>

                        <?php if (!empty($_SESSION['error_message'])): ?>
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                <i class="ti ti-alert-circle me-2"></i>
                                <?= htmlspecialchars($_SESSION['error_message']) ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                            <?php unset($_SESSION['error_message']); ?>
                        <?php endif; ?>

                        <form method="POST">
                            <input type="hidden" name="action" value="update_password">

                            <div class="mb-3">
                                <label class="form-label">Current Password <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <input type="password" class="form-control" id="currentPassword"
                                        name="current_password" required>
                                    <button class="btn btn-outline-secondary" type="button" id="toggleCurrentPassword">
                                        <i class="ti ti-eye"></i>
                                    </button>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">New Password <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <input type="password" class="form-control" id="newPassword" name="new_password"
                                        required>
                                    <button class="btn btn-outline-secondary" type="button" id="toggleNewPassword">
                                        <i class="ti ti-eye"></i>
                                    </button>
                                </div>
                                <small class="text-muted d-block mt-1">Minimum 6 characters</small>
                            </div>

                            <div class="mb-4">
                                <label class="form-label">Confirm New Password <span
                                        class="text-danger">*</span></label>
                                <div class="input-group">
                                    <input type="password" class="form-control" id="confirmPassword"
                                        name="confirm_password" required>
                                    <button class="btn btn-outline-secondary" type="button" id="toggleConfirmPassword">
                                        <i class="ti ti-eye"></i>
                                    </button>
                                </div>
                            </div>

                            <button type="submit" class="btn btn-primary w-100">
                                <i class="ti ti-lock-check me-2"></i>Change Password
                            </button>
                        </form>

                        <hr class="my-4">
                        <div class="alert alert-info small mb-0">
                            <i class="ti ti-info-circle me-2"></i>
                            <strong>Password Requirements:</strong>
                            <ul class="mb-0 ps-3 mt-2">
                                <li>Minimum 6 characters</li>
                                <li>Must match in both fields</li>
                                <li>Current password verification required</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once '../components/layout/footer.php'; ?>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        // Use the toggle function from main.js
        initializePasswordToggle('currentPassword', 'toggleCurrentPassword');
        initializePasswordToggle('newPassword', 'toggleNewPassword');
        initializePasswordToggle('confirmPassword', 'toggleConfirmPassword');
    });
</script>