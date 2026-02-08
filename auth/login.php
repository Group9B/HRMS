<?php
require_once "../config/db.php";
require_once "../includes/functions.php";
$error = "";
if (isLoggedIn()) {
    redirect("/hrms/includes/redirect.php");
}

errorLog("Login PAge Loaded");
// Check for logout messages
if (isset($_GET['msg']) && $_GET['msg'] === 'expired') {
    $error = "<strong>Trial Expired!</strong><br>You have been logged out because your free trial has ended.<br>Please subscribe to continue.";
}

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = trim($_POST["email"] ?? '');
    $password = trim($_POST["password"] ?? '');

    // Input validation
    if (empty($email) || empty($password)) {
        $error = "Please fill in all fields.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Please enter a valid email address.";
    } else {
        // Fetch user from database using query helper
        $user_result = query($mysqli, "SELECT id, company_id, role_id, username, email, password, status FROM users WHERE email = ?", [$email]);

        if (!$user_result['success']) {
            $error = "Unable to process login request. " . ($user_result['error'] ?? "Please try again later.");
        } elseif (empty($user_result['data'])) {
            $error = "Invalid email or password.";
        } else {
            $user = $user_result['data'][0];

            // 0. Check Trial Expiry (Before checking other statuses)
            // Need to fetch company trial data first
            $company_result = query($mysqli, "SELECT subscription_status, trial_ends_at FROM companies WHERE id = ?", [$user['company_id']]);
            if ($company_result['success'] && !empty($company_result['data'])) {
                $company_data = $company_result['data'][0];

                // Check if manually marked as expired or time passed
                $is_expired = ($company_data['subscription_status'] === 'expired');

                if (!$is_expired && $company_data['subscription_status'] === 'trial' && !empty($company_data['trial_ends_at'])) {
                    $trial_end = new DateTime($company_data['trial_ends_at']);
                    $now = new DateTime();
                    if ($now > $trial_end) {
                        $is_expired = true;
                        // Optionally update DB here to keep it consistent, though redundant with middleware
                        query($mysqli, "UPDATE companies SET subscription_status = 'expired' WHERE id = ?", [$user['company_id']]);
                    }
                }

                if ($is_expired) {
                    $error = "<strong>Trial Expired!</strong><br>Your 14-day free trial has ended.<br> <a href='../subscription/purchase.php?email=" . urlencode($email) . "' class='btn btn-sm btn-success mt-2'>Click here to Subscribe</a><br><small>And continue from where you stopped.</small>";
                    // Prevent login by setting user to null or skip session logic
                    $user = null;
                }
            }

            if ($user) {
                // Verify account status
                if ($user['status'] !== 'active') {
                    $error = "Your account is inactive. Please contact your administrator for assistance.";
                } elseif (!password_verify($password, $user['password'])) {
                    // Password verification failed
                    $error = "Invalid email or password.";
                } else {
                    // Password is correct - now check for employee record (except for Super Admin role_id = 1)
                    if (in_array($user['role_id'], [3, 4, 5, 6, 7])) {
                        // Check if employee record exists for non-admin users
                        $emp_result = query($mysqli, "SELECT id, status FROM employees WHERE user_id = ? LIMIT 1", [$user['id']]);

                        if (!$emp_result['success']) {
                            $error = "Unable to retrieve employee profile. " . ($emp_result['error'] ?? "Please try again later.");
                        } elseif (empty($emp_result['data'])) {
                            // No employee record found
                            $error = "Your employee profile is not yet created in the system. Please contact your HR department to complete your onboarding.";
                        } else {
                            $employee = $emp_result['data'][0];

                            // Check if employee record is active
                            if ($employee['status'] !== 'active') {
                                $error = "Your employee record is currently inactive. Please contact your HR department for more information.";
                            } else {
                                // All checks passed - proceed with login
                                $_SESSION['user_id'] = $user['id'];
                                $_SESSION['username'] = $user['username'];
                                $_SESSION['role_id'] = $user['role_id'];
                                $_SESSION['company_id'] = $user['company_id'];
                                $_SESSION['employee_id'] = $employee['id'];

                                // Redirect based on role
                                switch ($user['role_id']) {
                                    case 1: // Admin
                                        redirect("/hrms/admin/");
                                        break;
                                    case 2: // Company Owner
                                        redirect("/hrms/company/");
                                        break;
                                    case 3: // Human Resource
                                        redirect("/hrms/hr/");
                                        break;
                                    case 4: // Employee
                                        redirect("/hrms/employee/");
                                        break;
                                    case 5: // Auditor
                                        redirect("/hrms/auditor/");
                                        break;
                                    case 6: // Manager
                                        redirect("/hrms/manager/");
                                        break;
                                    case 7: // Candidate (Future Implementation)
                                        // redirect("/hrms/candidate/");
                                        break;
                                    default:
                                        http_response_code(404);
                                        redirect("/hrms/pages/404.php");
                                        break;
                                }
                                exit();
                            }
                        }
                    } else {
                        // Admin role - proceed with login directly
                        $_SESSION['user_id'] = $user['id'];
                        $_SESSION['username'] = $user['username'];
                        $_SESSION['role_id'] = $user['role_id'];
                        $_SESSION['company_id'] = $user['company_id'];

                        redirect("/hrms/includes/redirect.php");
                        exit();
                    }
                }
            } // Close if ($user)
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

                        <h5 class="text-center mb-3 border-bottom pb-2 border-2 border-primary-subtle">Welcome Back..!
                        </h5>

                        <?php if (!empty($error)): ?>
                            <div class="alert alert-danger py-2"><?php echo $error; ?></div>
                        <?php endif; ?>

                        <form method="POST" action="">
                            <div class="mb-3">
                                <label for="email" class="form-label">Email Address</label>
                                <div class="input-group">
                                    <span class="input-group-text" id="basic-addon1">@</span>

                                    <input type="email" class="form-control" name="email" id="email"
                                        placeholder="Enter your email" required>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="passwordInput" class="form-label">Password</label>
                                <div class="input-group">

                                    <span class="input-group-text" id="basic-addon1"><i class="ti ti-lock"></i></span>
                                    <input type="password" class="form-control" name="password" id="passwordInput"
                                        placeholder="Enter password" required>
                                    <button class="btn btn-outline-secondary" type="button" id="togglePassword">
                                        <i class="ti ti-eye fs-4" id="icon"></i>
                                    </button>
                                </div>
                                <div class="d-flex justify-content-end mt-1">
                                    <a href="forgot_password.php" class="text-decoration-none small">Forgot
                                        Password?</a>
                                </div>
                            </div>

                            <button type="submit" class="btn btn-primary w-100">Login</button>

                            <div class="text-center mt-3">
                                <span class="small text-muted">Don't have an account?</span>
                                <a href="../register.php" class="text-decoration-none small fw-bold">Book a Free
                                    Demo</a>
                            </div>
                        </form>
                    </div>

                    <div class="card-footer text-center footer-text text-muted">
                        &copy; <?php echo date("Y"); ?> StaffSync HRMS
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php
require_once "../components/layout/footer.php";
?>