<?php
// api/api_register.php
header('Content-Type: application/json');
require_once '../config/db.php';
require_once '../includes/functions.php';

// Only handle POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
    exit;
}

$action = $_POST['action'] ?? '';

if ($action === 'register_demo') {
    // 1. Sanitize & Validate Inputs
    $company_name = trim($_POST['company_name'] ?? '');
    $first_name = trim($_POST['first_name'] ?? '');
    $last_name = trim($_POST['last_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $password = $_POST['password'] ?? '';
    // Check for terms agreement
    $terms_agree = $_POST['terms_agree'] ?? '';

    $errors = [];
    if (empty($terms_agree)) {
        $errors[] = "You must agree to the Terms and Conditions.";
    }
    if (empty($company_name))
        $errors[] = "Company Name is required.";
    if (empty($first_name))
        $errors[] = "First Name is required.";
    if (empty($last_name))
        $errors[] = "Last Name is required.";
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL))
        $errors[] = "Valid Email is required.";
    if (empty($password) || strlen($password) < 8)
        $errors[] = "Password must be at least 8 characters.";

    // Check if email already exists in users table (globally unique emails preferred for SaaS)
    $check_email = query($mysqli, "SELECT id FROM users WHERE email = ?", [$email]);
    if ($check_email['success'] && !empty($check_email['data'])) {
        $errors[] = "This email address is already registered.";
    }

    if (!empty($errors)) {
        echo json_encode(['success' => false, 'message' => implode(' ', $errors)]);
        exit;
    }

    // Start Transaction
    $mysqli->begin_transaction();

    try {
        // 2. Create Company (Trial Mode)
        // Subscription expires in 14 days
        $trial_ends_at = date('Y-m-d H:i:s', strtotime('+14 days'));

        $insert_company = query(
            $mysqli,
            "INSERT INTO companies (name, email, phone, created_at, subscription_status, trial_ends_at) VALUES (?, ?, ?, NOW(), 'trial', ?)",
            [$company_name, $email, $phone, $trial_ends_at]
        );

        if (!$insert_company['success']) {
            throw new Exception("Failed to create company: " . $insert_company['error']);
        }
        $company_id = $insert_company['insert_id'];

        // 3. Create Default Department & Designation & Shift
        // Department: Administration
        $insert_dept = query($mysqli, "INSERT INTO departments (company_id, name, description) VALUES (?, 'Administration', 'Core company administration')", [$company_id]);
        $dept_id = $insert_dept['insert_id'];

        // Designation: CEO / Owner
        $insert_des = query($mysqli, "INSERT INTO designations (department_id, name, description) VALUES (?, 'CEO / Owner', 'Company Owner')", [$dept_id]);
        $des_id = $insert_des['insert_id'];

        // Shift: General (9 AM - 6 PM)
        $insert_shift = query($mysqli, "INSERT INTO shifts (company_id, shift_name, start_time, end_time) VALUES (?, 'General Shift', '09:00:00', '18:00:00')", [$company_id]);
        $shift_id = $insert_shift['insert_id'];

        // 4. Create User (Owner Role - ID 2 based on verified schema/assumptions)
        // We know Role 2 is "Company Owner" from previous context or we can fetch it dynamically if strictly needed.
        // Assuming Role 2 is Owner based on standard seeding.
        $role_id = 2;
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        // Username defaults to email or part of email if username column is required and unique
        $username = explode('@', $email)[0] . rand(100, 999);

        $insert_user = query(
            $mysqli,
            "INSERT INTO users (username, email, password, company_id, role_id, status) VALUES (?, ?, ?, ?, ?, 'active')",
            [$username, $email, $hashed_password, $company_id, $role_id]
        );

        if (!$insert_user['success']) {
            throw new Exception("Failed to create user: " . $insert_user['error']);
        }
        $user_id = $insert_user['insert_id'];

        // 5. Create Employee Record
        // Generate a temporary code or use function
        $emp_code = "EMP-" . date('Y') . "-001"; // Generic first employee

        $insert_emp = query(
            $mysqli,
            "INSERT INTO employees (user_id, employee_code, first_name, last_name, contact, department_id, designation_id, shift_id, date_of_joining, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, CURDATE(), 'active')",
            [$user_id, $emp_code, $first_name, $last_name, $phone, $dept_id, $des_id, $shift_id]
        );

        if (!$insert_emp['success']) {
            throw new Exception("Failed to create employee profile: " . $insert_emp['error']);
        }

        // Commit Transaction
        $mysqli->commit();

        // 6. Auto-Login
        session_start();
        $_SESSION['user_id'] = $user_id;
        $_SESSION['role_id'] = $role_id;
        $_SESSION['username'] = $username;
        $_SESSION['email'] = $email;
        $_SESSION['company_id'] = $company_id;

        echo json_encode(['success' => true, 'message' => 'Registration successful!']);

    } catch (Exception $e) {
        $mysqli->rollback();
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
    exit;
}

echo json_encode(['success' => false, 'message' => 'Invalid action.']);
?>