<?php
require_once "../config/db.php";
require_once "../includes/functions.php";
$error = "";

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = trim($_POST["email"]) ?? '';
    $password = trim($_POST["password"]) ?? '';
    if (empty($email) || empty($password)) {
        $error = "Please fill in all fields.";
    } else {
        // Prepare statement
        $stmt = $mysqli->prepare("SELECT id, company_id, role_id, username, email, password, status 
                                  FROM users WHERE email = ?");
        if (!$stmt) {
            $error = "Database error: " . $mysqli->error;
        } else {
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows === 1) {
                $user = $result->fetch_assoc();

                if ($user['status'] !== 'active') {
                    $error = "Your account is inactive. Please contact admin.";
                } elseif (password_verify($password, $user['password'])) {
                    // Store session data
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['username'] = $user['username'];
                    $_SESSION['role_id'] = $user['role_id'];
                    $_SESSION['company_id'] = $user['company_id'];

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
                        default:
                            http_response_code(404);
                            break;
                    }
                    exit();
                } else {
                    $error = "Invalid email or password.";
                }
            } else {
                $error = "Invalid email or password.";
            }
        }
    }
}
require_once "../components/layout/header.php";
?>
<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-4">
            <div class="card shadow-sm">
                <div class="card-header text-center bg-primary text-white">
                    <h4>HRMS Login</h4>
                </div>
                <div class="card-body">
                    <?php if (!empty($error)): ?>
                        <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
                    <?php endif; ?>

                    <form method="POST" action="">
                        <div class="mb-3">
                            <label for="email" class="form-label">Email address</label>
                            <input type="email" class="form-control" name="email" id="email" required>
                        </div>

                        <div class="mb-3">
                            <label for="password" class="form-label">Password</label>
                            <div class="input-group">
                                <input type="password" class="form-control" name="password" id="passwordInput" required>
                                <button class="btn btn-outline-secondary" type="button" id="togglePassword">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary w-100">Login</button>
                    </form>
                </div>
                <div class="card-footer text-center small">
                    &copy; <?php echo date("Y"); ?> HRMS System
                </div>
            </div>
        </div>
    </div>
</div>
<?php
require_once "../components/layout/footer.php";
?>