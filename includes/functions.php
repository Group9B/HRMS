<?php

/**
 * Check if a user is logged in
 * 
 * @return bool True if user is logged in, false otherwise
 */
function isLoggedIn()
{
    return isset($_SESSION['user_id']);
}

/**
 * Require current user to have one of the allowed role IDs
 * 
 * @param array $allowedRoleIds Array of allowed role IDs
 * @return void Dies with JSON error if unauthorized
 */
function requireRole(array $allowedRoleIds)
{
    if (!isLoggedIn()) {
        http_response_code(401);
        die(json_encode(['success' => false, 'message' => 'Unauthorized']));
    }
    // Fetch role ID for current user
    global $mysqli;
    $user = getCurrentUser($mysqli);
    $roleId = $user['role_id'] ?? null;
    if (!$roleId || !in_array($roleId, $allowedRoleIds, true)) {
        http_response_code(403);
        die(json_encode(['success' => false, 'message' => 'Forbidden']));
    }
}

/**
 * Render a setting field for admin settings page
 * 
 * @param array $setting Setting configuration array with keys: setting_key, setting_value, description
 * @return string HTML form field markup
 */
function render_setting_field($setting)
{
    $key = htmlspecialchars($setting['setting_key']);
    $value = htmlspecialchars($setting['setting_value']);
    $label = ucwords(str_replace('_', ' ', $key));
    $description = htmlspecialchars($setting['description']);

    $html = "<div class='mb-3'>";
    $html .= "<label for='$key' class='form-label'>$label</label>";

    if ($key === 'maintenance_mode') {
        $html .= "<select class='form-select' id='$key' name='$key'>";
        $html .= "<option value='0'" . ($value == '0' ? ' selected' : '') . ">Off</option>";
        $html .= "<option value='1'" . ($value == '1' ? ' selected' : '') . ">On</option>";
        $html .= "</select>";
    } else {
        $html .= "<input type='text' class='form-control' id='$key' name='$key' value='$value'>";
    }

    $html .= "<div class='form-text'>$description</div></div>";

    return $html;
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
            $replacements['{{' . $key . '}}'] = (string) $value;
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
        $name = htmlspecialchars((string) ($item['name'] ?? ''), ENT_QUOTES, 'UTF-8');
        $amount = number_format((float) ($item['amount'] ?? 0), 2);
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
 * Generates a unique, sequential employee code for a given company.
 * The format is [DynamicPrefix]-[Year]-[SequentialNumber].
 * Example: TPL-2025-001, TPL-2025-002 for "Test Pvt. Ltd."
 *
 * @param mysqli $mysqli The database connection object.
 * @param int $company_id The ID of the company for which to generate the code.
 * @param string $date_of_joining The employee's date of joining (e.g., '2025-09-24').
 * @return string The generated employee code.
 */
function generateEmployeeCode($mysqli, $company_id, $date_of_joining)
{
    // Step 1: Get the company's full name from the database.
    $name_sql = "SELECT name FROM companies WHERE id = ? LIMIT 1";
    $name_result = query($mysqli, $name_sql, [$company_id]);

    $company_prefix = 'EMP'; // Default fallback prefix

    if ($name_result['success'] && !empty($name_result['data'])) {
        $company_name = $name_result['data'][0]['name'];

        // LOGIC TO DYNAMICALLY CREATE A PREFIX
        $words = preg_split("/[\s,.-]+/", $company_name); // Split by spaces, commas, dots, hyphens
        $prefix = "";
        if (count($words) > 1) {
            // Use the first letter of each major word
            foreach ($words as $word) {
                if (strlen($word) > 0) {
                    $prefix .= strtoupper(substr($word, 0, 1));
                }
            }
        } else {
            // For single-word names, use the first 3 letters
            $prefix = strtoupper(substr($company_name, 0, 3));
        }

        // Use the generated prefix if it's valid
        if (!empty($prefix)) {
            $company_prefix = preg_replace('/[^A-Z0-9]/', '', $prefix); // Sanitize
        }
    }

    // Step 2: Extract the year from the joining date.
    $joining_year = date('Y', strtotime($date_of_joining));

    // Step 3: Get total employees created this year for this company.
    // Join with users table to get company_id since employees.department_id is often NULL
    $count_sql = "
        SELECT COUNT(*) as total_count
        FROM employees e
        INNER JOIN users u ON e.user_id = u.id
        WHERE u.company_id = ? AND YEAR(e.created_at) = ?";

    $max_result = query($mysqli, $count_sql, [$company_id, $joining_year]);

    $next_sequence = 1;
    if ($max_result['success'] && !empty($max_result['data'])) {
        $count = (int) $max_result['data'][0]['total_count'];
        $next_sequence = $count + 1;
    }

    // Step 4: Format with leading zeros (001, 002, 003, etc.)
    $padded_sequence = str_pad($next_sequence, 3, '0', STR_PAD_LEFT);

    // Step 5: Return the employee code.
    return "{$company_prefix}-{$joining_year}-{$padded_sequence}";
}/**
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

/**
 * Redirect to a specified URL and exit
 * 
 * @param string $url The URL to redirect to
 * @return void Never returns (exits after redirect)
 */
function redirect(string $url): void
{
    header("Location: $url");
    exit();
}

/**
 * Add a flash message to session for display on next page load
 * 
 * @param string $type Message type (success, error, warning, info)
 * @param string $message The message to display
 * @return void
 */
function flash(string $type, string $message): void
{
    if (!isset($_SESSION['toasts'])) {
        $_SESSION['toasts'] = [];
    }

    $_SESSION['toasts'][] = [
        'type' => $type,
        'message' => $message
    ];
}

/**
 * Get icon class for a given file type
 * 
 * @param string $file_type File extension
 * @return string Icon class name
 */
function getFileIcon($file_type)
{
    switch ($file_type) {
        case "pdf":
            return "pdf text-danger";
        case "docx":
            return "word";
        case "pptx":
            return "powerpoint";
        case "txt":
            return "alt text-warning";
        case "jpg":
        case "jpeg":
        case "png":
            return "image text-info";
        default:
            return "invoice";
    }
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

/**
 * Get current logged-in user details with role information
 * 
 * @param mysqli $mysqli Database connection object
 * @return array|null User details array or null if not logged in
 */
function getCurrentUser($mysqli)
{
    if (!isLoggedIn()) {
        return null;
    }

    // Use the query() function for proper resource management
    $result = query($mysqli, "SELECT u.*, r.name as role_name FROM users u JOIN roles r ON u.role_id = r.id WHERE u.id = ?", [$_SESSION['user_id']]);

    if ($result['success'] && !empty($result['data'])) {
        return $result['data'][0];
    }

    return null;
}

/**
 * Require authentication - redirect to login if not authenticated
 * Prevents redirect loops by checking if already on login page
 * 
 * @return void Exits if redirection is needed
 */
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
 * Get Bootstrap background class for toast notification type
 * 
 * @param string $type Toast type (success, error, warning, info)
 * @return string Bootstrap bg class
 */
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
 * Get user permissions based on role ID
 */
function getUserPermissions($role_id)
{
    $permissions = [
        1 => [ // Admin
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
        2 => [ // Company Owner
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
        3 => [ // Human Resource
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
        4 => [ // Employee
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
        6 => [ // Manager
            'user_management' => false,
            'company_settings' => false,
            'system_settings' => false,
            'audit_logs' => false,
            'email_templates' => false,
            'backup_restore' => false,
            'reports' => true, // Managers perform reporting
            'security' => false,
            'employee_management' => true, // Managers manage their team
            'payroll_management' => false,
            'attendance_management' => true, // Managers approve attendance
            'leave_management' => true // Managers approve leaves
        ],
        5 => [ // Auditor
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

    return $permissions[$role_id] ?? [];
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

function render_stat_card(string $title, $value, string $icon, string $color): string
{
    $title_safe = htmlspecialchars($title);
    $value_safe = htmlspecialchars($value);
    $icon_safe = htmlspecialchars($icon);
    $color_safe = htmlspecialchars($color);

    return <<<HTML
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card stat-card shadow-sm bg-{$color_safe}-subtle">
            <div class="card-body">
                <div class="icon-circle bg-{$color_safe}"><i class="{$icon_safe} text-white"></i></div>
                <div>
                    <div class="text-xs font-weight-bold text-uppercase mb-1">{$title_safe}</div>
                    <div class="h5 mb-0 font-weight-bold text-muted">{$value_safe}</div>
                </div>
            </div>
        </div>
    </div>
HTML;
}

/**
 * Renders a "Quick Actions" panel with a list of links.
 *
 * @param array $actions An array of actions, where each action is an associative array
 * with keys 'title', 'url', 'icon', and optional 'onclick'.
 * @param string $card_title The title for the quick actions card.
 * @return string The HTML for the quick actions panel.
 */
function render_quick_actions(array $actions, string $card_title = 'Quick Actions'): string
{
    $card_title_safe = htmlspecialchars($card_title);
    $links_html = '';

    foreach ($actions as $action) {
        $title = htmlspecialchars($action['title'] ?? '');
        $url = htmlspecialchars($action['url'] ?? '#');
        $icon = htmlspecialchars($action['icon'] ?? '');
        $onclick = isset($action['onclick']) ? 'onclick="' . htmlspecialchars($action['onclick']) . '"' : '';

        $links_html .= <<<HTML
        <a href="{$url}" class="btn btn-secondary btn-sm" {$onclick}><i class="{$icon}"></i> {$title}</a>
HTML;
    }

    return <<<HTML
    <div class="col-lg-12 mb-4">
        <div class="card main-content-card shadow-sm">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold">{$card_title_safe}</h6>
            </div>
            <div class="card-body quick-actions">
                <div class="d-grid gap-2">
                    {$links_html}
                </div>
            </div>
        </div>
    </div>
HTML;
}

function render_todo_list_widget(string $card_title = 'My To-Do List'): string
{
    $card_title_safe = htmlspecialchars($card_title);

    return <<<HTML
    <div class="card main-content-card shadow-sm">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold">{$card_title_safe}</h6>
        </div>
        <div class="card-body">
            <form id="todo-form" class="mb-3 d-flex gap-2">
                <input type="text" name="task" class="form-control" placeholder="Add a new task..." required>
                <button type="submit" class="btn btn-primary"><i class="ti ti-plus"></i></button>
            </form>
            <ul id="todo-list" class="list-group list-group-flush">
                <!-- Tasks will be loaded here by JavaScript -->
            </ul>
        </div>
    </div>
HTML;
}

?>