<?php
require_once "../config/db.php";
require_once "../includes/functions.php";
$hideHeader = true;

$error = "";
$success = "";

// Check for flash messages
if (isset($_SESSION['flash_success'])) {
    $success = $_SESSION['flash_success'];
    unset($_SESSION['flash_success']);
}
if (isset($_SESSION['flash_error'])) {
    $error = $_SESSION['flash_error'];
    unset($_SESSION['flash_error']);
}

$token = $_GET['token'] ?? '';
$email = $_GET['email'] ?? '';

if (isLoggedIn()) {
    redirect("/hrms/includes/redirect.php");
}

// Basic validation of link parameters
if ((empty($token) || empty($email)) && empty($success)) {
    $error = "Invalid password reset link.";
}

$csrf_token = generateCsrfToken();

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] === "POST" && empty($error)) {
    $password = $_POST["password"] ?? '';
    $confirm_password = $_POST["confirm_password"] ?? '';
    $token_input = $_POST['token'] ?? '';
    $email_input = $_POST['email'] ?? '';
    $csrf_input = $_POST['csrf_token'] ?? '';

    // Verify CSRF
    if (!verifyCsrfToken($csrf_input)) {
        $_SESSION['flash_error'] = "Invalid request or session expired. Please refresh the page.";
        redirect("reset_password.php?token=" . urlencode($token_input) . "&email=" . urlencode($email_input));
    }
    // Re-validate inputs
    elseif (empty($password) || empty($confirm_password)) {
        $_SESSION['flash_error'] = "Please enter and confirm your new password.";
        redirect("reset_password.php?token=" . urlencode($token_input) . "&email=" . urlencode($email_input));
    } elseif (strlen($password) < 6) {
        $_SESSION['flash_error'] = "Password must be at least 6 characters long.";
        redirect("reset_password.php?token=" . urlencode($token_input) . "&email=" . urlencode($email_input));
    } elseif ($password !== $confirm_password) {
        $_SESSION['flash_error'] = "Passwords do not match.";
        redirect("reset_password.php?token=" . urlencode($token_input) . "&email=" . urlencode($email_input));
    } else {
        // Verify token against database
        $token_hash = hash('sha256', $token_input);

        $query = "SELECT id FROM users WHERE email = ? AND reset_token_hash = ? AND reset_token_expires_at > NOW()";
        $result = query($mysqli, $query, [$email_input, $token_hash]);

        if (!$result['success'] || empty($result['data'])) {
            $_SESSION['flash_error'] = "Invalid or expired password reset link. Please request a new one.";
            redirect("reset_password.php?token=" . urlencode($token_input) . "&email=" . urlencode($email_input));
        } else {
            $user_id = $result['data'][0]['id'];

            // Hash new password
            $password_hash = password_hash($password, PASSWORD_DEFAULT);

            // Update user password and clear reset token
            $update_query = "UPDATE users SET password = ?, reset_token_hash = NULL, reset_token_expires_at = NULL WHERE id = ?";
            $update_result = query($mysqli, $update_query, [$password_hash, $user_id]);

            if ($update_result['success']) {
                $success = "Your password has been successfully reset. You can now login with your new password.";
                // Log the activity
                logActivity($user_id, 'Password Reset Successful', 'User reset their password via email link');

                // Redirect back to same page but show success message (which hides form)
                $_SESSION['flash_success'] = $success;
                redirect("reset_password.php");
            } else {
                $_SESSION['flash_error'] = "Failed to reset password. Please try again later.";
                error_log("Password reset failed for user ID $user_id: " . $update_result['error']);
                redirect("reset_password.php?token=" . urlencode($token_input) . "&email=" . urlencode($email_input));
            }
        }
    }
}

require_once "../components/layout/header.php";
?>
<div class="body d-flex justify-content-center align-items-center vh-100">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-4">
                <div class="card login-card shadow-sm">
                    <div class="card-body">

                        <h5 class="text-center mb-3 border-bottom pb-2 border-2 border-primary-subtle">Set New Password
                        </h5>

                        <?php if (!empty($success)): ?>
                            <div class="alert alert-success py-3">
                                <i class="ti ti-check-circle me-2"></i><?php echo htmlspecialchars($success); ?>
                            </div>
                            <div class="text-center mt-3">
                                <a href="login.php" class="btn btn-primary w-100">Go to Login</a>
                            </div>
                        <?php else: ?>

                            <?php if (!empty($error)): ?>
                                <div class="alert alert-danger py-2">
                                    <?php echo htmlspecialchars($error); ?>
                                </div>
                            <?php endif; ?>

                            <?php if (empty($token) || empty($email)): ?>
                                <div class="text-center">
                                    <p class="text-muted">Missing reset token or email information.</p>
                                    <a href="forgot_password.php" class="btn btn-secondary btn-sm">Request New Link</a>
                                </div>
                            <?php else: ?>
                                <form method="POST" action="">
                                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">
                                    <input type="hidden" name="token" value="<?php echo htmlspecialchars($token); ?>">
                                    <input type="hidden" name="email" value="<?php echo htmlspecialchars($email); ?>">

                                    <div class="mb-3">
                                        <label for="password" class="form-label">New Password</label>
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="ti ti-lock"></i></span>
                                            <input type="password" class="form-control" name="password" id="password"
                                                placeholder="Enter new password" required minlength="6">
                                            <button class="btn btn-outline-secondary" type="button" id="togglePasswordBtn">
                                                <i class="ti ti-eye"></i>
                                            </button>
                                        </div>
                                    </div>

                                    <div class="mb-4">
                                        <label for="confirm_password" class="form-label">Confirm Password</label>
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="ti ti-lock"></i></span>
                                            <input type="password" class="form-control" name="confirm_password"
                                                id="confirm_password" placeholder="Confirm new password" required minlength="6">
                                        </div>
                                    </div>

                                    <button type="submit" class="btn btn-primary w-100 mb-3">Reset Password</button>
                                </form>
                            <?php endif; ?>

                        <?php endif; ?>
                    </div>
                </div>

                <?php if (empty($success)): ?>
                    <div class="text-center mt-3">
                        <a href="login.php" class="text-decoration-none text-muted small">
                            <i class="ti ti-arrow-left me-1"></i>Back to Login
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script>
    // Simple password toggle script
    document.addEventListener('DOMContentLoaded', function () {
        const toggleBtn = document.getElementById('togglePasswordBtn');
        if (toggleBtn) {
            toggleBtn.addEventListener('click', function () {
                const passwordInput = document.getElementById('password');
                const icon = this.querySelector('i');

                if (passwordInput.type === 'password') {
                    passwordInput.type = 'text';
                    icon.classList.remove('ti-eye');
                    icon.classList.add('ti-eye-off');
                } else {
                    passwordInput.type = 'password';
                    icon.classList.remove('ti-eye-off');
                    icon.classList.add('ti-eye');
                }
            });
        }
    });
</script>

<?php
require_once "../components/layout/footer.php";
?>