<?php

function isLoggedIn()
{
    return isset($_SESSION['user_id']);
}

/**
 * Require current user to have one of the allowed role names.
 */
function requireRole(array $allowedRoleNames)
{
    if (!isLoggedIn()) {
        http_response_code(401);
        die(json_encode(['success' => false, 'message' => 'Unauthorized']));
    }
    // Fetch role name for current user
    global $mysqli;
    $user = getCurrentUser($mysqli);
    $roleName = $user['role_name'] ?? null;
    if (!$roleName || !in_array($roleName, $allowedRoleNames, true)) {
        http_response_code(403);
        die(json_encode(['success' => false, 'message' => 'Forbidden']));
    }
}

/**
 * Simple placeholder renderer: replaces {{key}} in template with given values.
 */
function renderTemplateString(string $template, array $data): string
{
    $replacements = [];
    foreach ($data as $key => $value) {
        if (is_array($value) || is_object($value)) {
            $replacements['{{' . $key . '}}'] = '';
        } else {
            $replacements['{{' . $key . '}}'] = (string)$value;
        }
    }
    return strtr($template, $replacements);
}

/**
 * Build HTML table rows for earnings/deductions arrays.
 * Each array item: ['name' => string, 'amount' => number]
 */
function buildAmountRows(array $items): string
{
    $rows = '';
    foreach ($items as $item) {
        $name = htmlspecialchars((string)($item['name'] ?? ''), ENT_QUOTES, 'UTF-8');
        $amount = number_format((float)($item['amount'] ?? 0), 2);
        $rows .= '<tr><td>' . $name . '</td><td align="right">' . $amount . '</td></tr>';
    }
    if ($rows === '') {
        $rows = '<tr><td colspan="2" align="center">-</td></tr>';
    }
    return $rows;
}

/**
 * Render payslip HTML using a template and structured data.
 */
function renderPayslipHTML(string $templateHtml, array $data): string
{
    $earningsRows = buildAmountRows($data['earnings'] ?? []);
    $deductionsRows = buildAmountRows($data['deductions'] ?? []);
    $baseData = $data;
    $baseData['earnings_rows'] = $earningsRows;
    $baseData['deductions_rows'] = $deductionsRows;
    return renderTemplateString($templateHtml, $baseData);
}

/**
 * Fetch company details by id
 */
function getCompanyById(mysqli $mysqli, int $companyId): ?array
{
    $res = query($mysqli, "SELECT * FROM companies WHERE id = ?", [$companyId]);
    if ($res['success'] && !empty($res['data'])) {
        return $res['data'][0];
    }
    return null;
}

function redirect(string $url): void
{
    header("Location: $url");
    exit();
}

/**
 * A utility function to execute mysqli prepared statements securely.
 *
 * This function prepares, binds parameters, executes, and fetches results,
 * handling different query types (SELECT, INSERT, UPDATE, DELETE) appropriately.
 *
 * @param mysqli $mysqli The active database connection object.
 * @param string $sql The SQL query with '?' placeholders for parameters.
 * @param array $params An array of parameters to bind to the query. The types (i, s, d, b) are determined automatically.
 * @return array An associative array containing the result of the operation.
 * - For SELECT: ['success' => true, 'data' => array_of_rows]
 * - For INSERT: ['success' => true, 'insert_id' => new_id]
 * - For UPDATE/DELETE: ['success' => true, 'affected_rows' => number_of_affected_rows]
 * - On Failure: ['success' => false, 'error' => 'Error message']
 */
function query(mysqli $mysqli, string $sql, array $params = []): array
{
    try {
        // Prepare the statement
        $stmt = $mysqli->prepare($sql);
        if ($stmt === false) {
            throw new Exception("Unable to prepare statement: " . $mysqli->error);
        }

        // Bind parameters if they exist
        if (!empty($params)) {
            $types = '';
            // Dynamically determine the type for each parameter
            foreach ($params as $param) {
                if (is_int($param)) {
                    $types .= 'i'; // Integer
                } elseif (is_double($param)) {
                    $types .= 'd'; // Double
                } elseif (is_string($param)) {
                    $types .= 's'; // String
                } else {
                    $types .= 'b'; // Blob for other types
                }
            }
            $stmt->bind_param($types, ...$params);
        }

        // Execute the statement
        $stmt->execute();

        // Determine the query type to return the correct result format
        $query_type = strtoupper(substr(trim($sql), 0, 6));

        if ($query_type === 'SELECT') {
            $result = $stmt->get_result();
            $data = $result->fetch_all(MYSQLI_ASSOC);
            return ['success' => true, 'data' => $data];
        } elseif ($query_type === 'INSERT') {
            return ['success' => true, 'insert_id' => $stmt->insert_id];
        } else { // UPDATE, DELETE, etc.
            return ['success' => true, 'affected_rows' => $stmt->affected_rows];
        }

    } catch (Exception $e) {
        // In a production environment, you might want to log this error instead of exposing it.
        return ['success' => false, 'error' => $e->getMessage()];
    } finally {
        // Ensure the statement is always closed
        if (isset($stmt)) {
            $stmt->close();
        }
    }
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



function toastBgClass($type)
{
    switch ($type) {
        case 'success':
            return 'success';
        case 'error':
            return 'danger';
        case 'warning':
            return 'warning';
        case 'info':
            return 'info';
        default:
            return 'secondary';
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
    if (!isset($_SESSION['role_id'])) {
        return false;
    }

    $permissions = getUserPermissions($_SESSION['role_id']);
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