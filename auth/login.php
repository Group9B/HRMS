<?php
require_once "../config/db.php";
require_once "../includes/functions.php";
$error = "";
if (isLoggedIn()) {
    redirect("/hrms/includes/redirect.php");
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

            // Verify account status
            if ($user['status'] !== 'active') {
                $error = "Your account is inactive. Please contact your administrator for assistance.";
            } elseif (!password_verify($password, $user['password'])) {
                // Password verification failed
                $error = "Invalid email or password.";
            } else {
                // Password is correct - now check for employee record (except for Super Admin role_id = 1)
                if ($user['role_id'] !== 1) {
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
                                case 1:
                                    redirect("/hrms/admin/");
                                    break;
                                case 2:
                                    redirect("/hrms/company/");
                                    break;
                                case 3:
                                    redirect("/hrms/hr/");
                                    break;
                                case 4:
                                    redirect("/hrms/employee/");
                                    break;
                                case 5:
                                    redirect("/hrms/auditor/");
                                    break;
                                case 6:
                                    redirect("/hrms/manager/");
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
                    // Super Admin role - proceed with login directly
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['username'] = $user['username'];
                    $_SESSION['role_id'] = $user['role_id'];
                    $_SESSION['company_id'] = $user['company_id'];

                    redirect("/hrms/admin/");
                    exit();
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

                        <h5 class="text-center mb-3 border-bottom pb-2 border-2 border-primary-subtle">Welcome Back..!
                        </h5>

                        <?php if (!empty($error)): ?>
                            <div class="alert alert-danger py-2"><?php echo htmlspecialchars($error); ?></div>
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
                                        placeholder="Enter password" style="border-right: none;" required>
                                    <button class="btn btn-outline-secondary" type="button" id="togglePassword"
                                        style="border-left: none;">
                                        <i class="ti ti-eye fs-4" id="icon"></i>
                                    </button>
                                </div>
                            </div>

                            <button type="submit" class="btn btn-primary w-100">Login</button>
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