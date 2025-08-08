<?php

function isLoggedIn()
{
    return isset($_SESSION['user_id']);
}

function redirect($url)
{
    header("Location:" . $url);
    exit();
}

function getCurrentUser($mysqli)
{
    if (!isLoggedIn()) {
        return null;
    }
    //it will retrun details of the current user i think
    $stmt = $mysqli->prepare("SELECT u.*, r.name as role_name FROM users u JOIN roles r ON u.role_id = r.id WHERE u.id = ?");
    $stmt->bind_param("i", $_SESSION['user_id']);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_assoc();
}

function requireAuth()
{
    if (!isLoggedIn()) {
        // Check if we're already on the login page to prevent redirect loop
        $current_page = $_SERVER['PHP_SELF'] ?? '';
        if (strpos($current_page, 'login.php') === false) {
            header('Location: /hrms/auth/login.php');
            exit;
        }
    }
}

/**
 * Get user permissions based on role
 */
function getUserPermissions($role_name)
{
    $permissions = [
        'Super Admin' => [
            'user_management' => true,
            'company_settings' => true,
            'system_settings' => true,
            'audit_logs' => true,
            'email_templates' => true,
            'backup_restore' => true,
            'reports' => true,
            'security' => true,
            'employee_management' => true,
            'payroll_management' => true,
            'attendance_management' => true,
            'leave_management' => true
        ],
        'Company Admin' => [
            'user_management' => true,
            'company_settings' => true,
            'system_settings' => false,
            'audit_logs' => true,
            'email_templates' => true,
            'backup_restore' => false,
            'reports' => true,
            'security' => false,
            'employee_management' => true,
            'payroll_management' => true,
            'attendance_management' => true,
            'leave_management' => true
        ],
        'HR Manager' => [
            'user_management' => false,
            'company_settings' => false,
            'system_settings' => false,
            'audit_logs' => true,
            'email_templates' => true,
            'backup_restore' => false,
            'reports' => true,
            'security' => false,
            'employee_management' => true,
            'payroll_management' => true,
            'attendance_management' => true,
            'leave_management' => true
        ],
        'Employee' => [
            'user_management' => false,
            'company_settings' => false,
            'system_settings' => false,
            'audit_logs' => false,
            'email_templates' => false,
            'backup_restore' => false,
            'reports' => false,
            'security' => false,
            'employee_management' => false,
            'payroll_management' => false,
            'attendance_management' => false,
            'leave_management' => false
        ],
        'Auditor' => [
            'user_management' => false,
            'company_settings' => false,
            'system_settings' => false,
            'audit_logs' => true,
            'email_templates' => false,
            'backup_restore' => false,
            'reports' => true,
            'security' => false,
            'employee_management' => false,
            'payroll_management' => false,
            'attendance_management' => false,
            'leave_management' => false
        ]
    ];

    return $permissions[$role_name] ?? [];
}

/**
 * Check if current user has specific permission
 */
function hasPermission($permission)
{
    if (!isset($_SESSION['role'])) {
        return false;
    }

    $permissions = getUserPermissions($_SESSION['role']);
    return $permissions[$permission] ?? false;
}
/**
 * Generate random password
 */
function generateRandomPassword($length = 8)
{
    $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*';
    $password = '';
    for ($i = 0; $i < $length; $i++) {
        $password .= $chars[rand(0, strlen($chars) - 1)];
    }
    return $password;
}

/**
 * Check password complexity
 */
function checkPasswordComplexity($password)
{
    $errors = [];

    if (strlen($password) < 8) {
        $errors[] = 'Password must be at least 8 characters long';
    }

    if (!preg_match('/[A-Z]/', $password)) {
        $errors[] = 'Password must contain at least one uppercase letter';
    }

    if (!preg_match('/[a-z]/', $password)) {
        $errors[] = 'Password must contain at least one lowercase letter';
    }

    if (!preg_match('/[0-9]/', $password)) {
        $errors[] = 'Password must contain at least one number';
    }

    if (!preg_match('/[!@#$%^&*]/', $password)) {
        $errors[] = 'Password must contain at least one special character (!@#$%^&*)';
    }

    return $errors;
}

/**
 * Generate CSRF token
 */
function generateCSRFToken()
{
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Verify CSRF token
 */
function verifyCSRFToken($token)
{
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Require CSRF token for POST requests
 */
function requireCSRFToken()
{
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (!isset($_POST['csrf_token']) || !verifyCSRFToken($_POST['csrf_token'])) {
            http_response_code(403);
            die('CSRF token validation failed');
        }
    }
}

?>