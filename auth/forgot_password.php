<?php
require_once "../config/db.php";
require_once "../includes/functions.php";

$error = "";
$success_msg = "";

// Check for flash messages
if (isset($_SESSION['flash_success'])) {
    $success_msg = $_SESSION['flash_success'];
    unset($_SESSION['flash_success']);
}
if (isset($_SESSION['flash_error'])) {
    $error = $_SESSION['flash_error'];
    unset($_SESSION['flash_error']);
}

if (isLoggedIn()) {
    redirect("/hrms/includes/redirect.php");
}

$csrf_token = generateCsrfToken();

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = trim($_POST["email"] ?? '');
    $token_input = $_POST['csrf_token'] ?? '';
    $honeypot = $_POST['website'] ?? ''; // Honeypot field

    // Check Honeypot (if filled, it's a bot)
    if (!empty($honeypot)) {
        // Silently fail or generic error
        die("Invalid request.");
    }

    // CSRF Check
    if (!verifyCsrfToken($token_input)) {
        $_SESSION['flash_error'] = "Session expired or invalid request. Please refresh and try again.";
        redirect("forgot_password.php");
    }
    // Rate Limit Check (5 attempts per 15 minutes)
    elseif (!checkRateLimit('Password Reset Attempt', 5, 15)) {
        $_SESSION['flash_error'] = "Too many requests. Please wait 15 minutes before trying again.";
        redirect("forgot_password.php");
    }
    // Input validation
    elseif (empty($email)) {
        $_SESSION['flash_error'] = "Please enter your email address.";
        redirect("forgot_password.php");
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['flash_error'] = "Please enter a valid email address.";
        redirect("forgot_password.php");
    } else {
        // Log the attempt for rate limiting (failures count too)
        // We log with NULL user_id initially
        logActivity(null, 'Password Reset Attempt', "Attempt for email: " . substr($email, 0, 3) . '***');

        // Fetch user from database
        $user_result = query($mysqli, "SELECT id, username, email, status FROM users WHERE email = ?", [$email]);

        if (!$user_result['success']) {
            $_SESSION['flash_error'] = "Unable to process request. Please try again later.";
            redirect("forgot_password.php");
        } elseif (empty($user_result['data'])) {
            // Security: Don't reveal if email exists or not
            $_SESSION['flash_error'] = "If an account exists for that email, we have sent password reset instructions.";
            redirect("forgot_password.php");
        } else {
            $user = $user_result['data'][0];

            if ($user['status'] !== 'active') {
                $_SESSION['flash_error'] = "Your account is inactive. Please contact your administrator.";
                redirect("forgot_password.php");
            } else {
                // Log the activity
                logActivity($user['id'], 'Password Reset Requested', 'User requested password reset via ' . $email);

                // Generate a token
                $token = bin2hex(random_bytes(32));
                $token_hash = hash('sha256', $token);
                $expiry = date('Y-m-d H:i:s', time() + 60 * 60); // 1 hour expiration

                // Store token hash in database (make sure to run the migration adding these columns)
                // We use update query. Note: columns reset_token_hash and reset_token_expires_at must exist.
                // If they don't exist yet, this might fail, so ensure db is updated.
                $update_result = query($mysqli, "UPDATE users SET reset_token_hash = ?, reset_token_expires_at = ? WHERE id = ?", [$token_hash, $expiry, $user['id']]);

                if (!$update_result['success']) {
                    // Fallback if columns don't exist or query failed -> proceed sending email but token validation will fail on reset page if db not updated.
                    // For now, logging error.
                    error_log("Failed to store reset token: " . $update_result['error']);
                }

                // Construct reset link
                $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http";
                $host = $_SERVER['HTTP_HOST'];
                // Only send the token and email (email is for UX/verification)
                $resetLink = "$protocol://$host/hrms/auth/reset_password.php?token=$token&email=" . urlencode($email);

                // Send email using MailService (queued for async delivery)
                require_once "../includes/mail/MailService.php";
                $mailService = new MailService();

                if ($mailService->queuePasswordReset($user['email'], $user['username'], $resetLink)) {
                    $_SESSION['flash_success'] = "We have sent a password reset link to " . htmlspecialchars($email) . ". Please check your inbox.";
                    redirect("forgot_password.php");
                } else {
                    $_SESSION['flash_error'] = "Failed to send reset email. Please try again later or contact support.";
                    error_log("Failed to queue password reset email to " . $email);
                    redirect("forgot_password.php");
                }
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

                        <h5 class="text-center mb-3 border-bottom pb-2 border-2 border-primary-subtle">Reset Password
                        </h5>

                        <p class="text-muted text-center small mb-4">Enter your email address and we'll send you a link
                            to reset your password.</p>

                        <?php if (!empty($error)): ?>
                            <div class="alert alert-danger py-2">
                                <?php echo htmlspecialchars($error); ?>
                            </div>
                        <?php endif; ?>

                        <?php if (!empty($success_msg)): ?>
                            <div class="alert alert-success py-2">
                                <?php echo htmlspecialchars($success_msg); ?>
                            </div>
                        <?php endif; ?>

                        <form method="POST" action="">
                            <!-- CSRF Token -->
                            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">

                            <!-- Honeypot Field (Hidden from humans) -->
                            <div style="display:none; visibility:hidden;">
                                <label>Don't fill this out if you're human: <input type="text" name="website"
                                        tabindex="-1" autocomplete="off"></label>
                            </div>

                            <div class="mb-3">
                                <label for="email" class="form-label">Email Address</label>
                                <div class="input-group">
                                    <span class="input-group-text" id="basic-addon1">@</span>

                                    <input type="email" class="form-control" name="email" id="email"
                                        placeholder="Enter your email" required
                                        value="<?php echo isset($email) ? htmlspecialchars($email) : ''; ?>">
                                </div>
                            </div>

                            <button type="submit" class="btn btn-primary w-100 mb-3">Send Reset Link</button>

                            <div class="text-center">
                                <a href="login.php" class="text-decoration-none">
                                    <i class="ti ti-arrow-left me-1"></i>Back to Login
                                </a>
                            </div>
                        </form>
                    </div>

                    <div class="card-footer text-center footer-text text-muted">
                        &copy;
                        <?php echo date("Y"); ?> StaffSync HRMS
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php
require_once "../components/layout/footer.php";
?>