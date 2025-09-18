<?php
header('Content-Type: application/json');
session_start();
require_once '../config/db.php';
require_once '../includes/functions.php';

$response = ['success' => false, 'message' => 'An unknown error occurred.'];

// Security Check: Must be a logged-in user
if (!isLoggedIn()) {
    $response['message'] = 'Unauthorized access. Please log in.';
    echo json_encode($response);
    exit();
}

$action = $_REQUEST['action'] ?? '';
$user_id = $_SESSION['user_id'];

switch ($action) {
    case 'list_templates':
        // HR or Admins only
        requireRole(['HR Manager', 'Company Admin', 'Super Admin']);
        $companyId = $_SESSION['company_id'] ?? null;
        $params = [];
        $sql = "SELECT * FROM payslip_templates WHERE is_active = 1 AND (company_id IS NULL";
        if ($companyId) {
            $sql .= " OR company_id = ?";
            $params[] = (int)$companyId;
        }
        $sql .= ") ORDER BY company_id IS NULL DESC, id DESC";
        $result = query($mysqli, $sql, $params);
        if ($result['success']) {
            $response = ['success' => true, 'data' => $result['data']];
        } else {
            $response['message'] = 'Failed to fetch templates.';
        }
        break;

    case 'create_template':
        // HR or Admins only
        requireRole(['HR Manager', 'Company Admin', 'Super Admin']);
        $currentUser = getCurrentUser($mysqli);
        $companyId = $currentUser['company_id'] ?? null;
        $name = trim($_POST['name'] ?? '');
        $subject = trim($_POST['subject'] ?? 'Your payslip for {{period}}');
        $bodyHtml = $_POST['body_html'] ?? '';
        $components = json_decode($_POST['components'] ?? '[]', true);
        $isActive = isset($_POST['is_active']) ? (int)($_POST['is_active'] ? 1 : 0) : 1;
        if ($name === '' || $bodyHtml === '') { $response['message'] = 'Name and body are required.'; break; }
        $basePlaceholders = ['company_name','period','employee_name','employee_code','department_name','designation_name','earnings_rows','deductions_rows','gross_salary','net_salary','currency','generated_at'];
        $selected = is_array($components) ? array_values(array_unique(array_filter($components, 'is_string'))) : [];
        $placeholders = json_encode(array_values(array_unique(array_merge($basePlaceholders, $selected))));
        $insert = query($mysqli, "INSERT INTO payslip_templates (company_id, name, subject, body_html, placeholders, is_active, created_by) VALUES (?,?,?,?,?,?,?)", [
            $companyId, $name, $subject, $bodyHtml, $placeholders, $isActive, $user_id
        ]);
        if ($insert['success']) {
            $response = ['success' => true, 'data' => ['template_id' => $insert['insert_id']]];
        } else {
            $response['success'] = false; $response['message'] = 'Failed to create template';
        }
        break;

    case 'update_template':
        // HR or Admins only
        requireRole(['HR Manager', 'Company Admin', 'Super Admin']);
        $currentUser = getCurrentUser($mysqli);
        $companyId = $currentUser['company_id'] ?? null;
        $templateId = (int)($_POST['id'] ?? 0);
        $name = trim($_POST['name'] ?? '');
        $subject = trim($_POST['subject'] ?? 'Your payslip for {{period}}');
        $bodyHtml = $_POST['body_html'] ?? '';
        $components = json_decode($_POST['components'] ?? '[]', true);
        $isActive = isset($_POST['is_active']) ? (int)($_POST['is_active'] ? 1 : 0) : 1;
        if ($templateId <= 0) { $response['message'] = 'Invalid template id'; break; }
        if ($name === '' || $bodyHtml === '') { $response['message'] = 'Name and body are required.'; break; }
        $basePlaceholders = ['company_name','period','employee_name','employee_code','department_name','designation_name','earnings_rows','deductions_rows','gross_salary','net_salary','currency','generated_at'];
        $selected = is_array($components) ? array_values(array_unique(array_filter($components, 'is_string'))) : [];
        $placeholders = json_encode(array_values(array_unique(array_merge($basePlaceholders, $selected))));
        // Restrict update within company scope (or allow Super Admin to update any)
        if (($currentUser['role_name'] ?? '') === 'Super Admin') {
            $upd = query($mysqli, "UPDATE payslip_templates SET name=?, subject=?, body_html=?, placeholders=?, is_active=?, updated_by=? WHERE id=?", [
                $name, $subject, $bodyHtml, $placeholders, $isActive, $user_id, $templateId
            ]);
        } else {
            $upd = query($mysqli, "UPDATE payslip_templates SET name=?, subject=?, body_html=?, placeholders=?, is_active=?, updated_by=? WHERE id=? AND (company_id = ? OR company_id IS NULL)", [
                $name, $subject, $bodyHtml, $placeholders, $isActive, $user_id, $templateId, $companyId
            ]);
        }
        if ($upd['success']) { $response = ['success' => true]; } else { $response['message'] = 'Failed to update template'; }
        break;

    case 'preview_payslip':
        // HR or Admins only
        requireRole(['HR Manager', 'Company Admin', 'Super Admin']);
        $employeeId = (int)($_POST['employee_id'] ?? 0);
        $period = trim($_POST['period'] ?? '');
        $templateId = isset($_POST['template_id']) ? (int)$_POST['template_id'] : null;
        $currency = trim($_POST['currency'] ?? 'INR');
        $earnings = json_decode($_POST['earnings'] ?? '[]', true) ?: [];
        $deductions = json_decode($_POST['deductions'] ?? '[]', true) ?: [];

        if ($employeeId <= 0 || $period === '') {
            $response['message'] = 'Missing employee_id or period.';
            break;
        }

        // Fetch employee and related details
        $empRes = query($mysqli, "SELECT e.*, d.name AS department_name, g.name AS designation_name, u.company_id, u.email FROM employees e LEFT JOIN departments d ON e.department_id = d.id LEFT JOIN designations g ON e.designation_id = g.id JOIN users u ON e.user_id = u.id WHERE e.id = ?", [$employeeId]);
        if (!$empRes['success'] || empty($empRes['data'])) {
            $response['message'] = 'Employee not found.';
            break;
        }
        $emp = $empRes['data'][0];
        $company = $emp['company_id'] ? getCompanyById($mysqli, (int)$emp['company_id']) : null;

        // Load template (company specific first, then global)
        if ($templateId) {
            $tplRes = query($mysqli, "SELECT * FROM payslip_templates WHERE id = ? AND is_active = 1", [$templateId]);
        } else {
            $tplRes = query($mysqli, "SELECT * FROM payslip_templates WHERE is_active = 1 AND (company_id = ? OR company_id IS NULL) ORDER BY company_id IS NULL DESC, id DESC LIMIT 1", [(int)($emp['company_id'] ?? 0)]);
        }
        if (!$tplRes['success'] || empty($tplRes['data'])) {
            $response['message'] = 'No active template found.';
            break;
        }
        $tpl = $tplRes['data'][0];

        $gross = 0.0; foreach ($earnings as $it) { $gross += (float)($it['amount'] ?? 0); }
        $ded = 0.0; foreach ($deductions as $it) { $ded += (float)($it['amount'] ?? 0); }
        $net = $gross - $ded;

        $html = renderPayslipHTML($tpl['body_html'], [
            'company_name' => $company['name'] ?? 'Company',
            'period' => $period,
            'employee_name' => trim(($emp['first_name'] ?? '') . ' ' . ($emp['last_name'] ?? '')),
            'employee_code' => $emp['employee_code'] ?? '',
            'department_name' => $emp['department_name'] ?? '',
            'designation_name' => $emp['designation_name'] ?? '',
            'gross_salary' => number_format($gross, 2),
            'net_salary' => number_format($net, 2),
            'currency' => $currency,
            'generated_at' => date('Y-m-d H:i:s'),
            'earnings' => $earnings,
            'deductions' => $deductions,
        ]);

        $response = [
            'success' => true,
            'data' => [
                'html' => $html,
                'subject' => renderTemplateString($tpl['subject'], ['period' => $period]),
                'gross_salary' => $gross,
                'net_salary' => $net,
            ]
        ];
        break;

    case 'generate_payslip':
        // HR only generation
        requireRole(['HR Manager', 'Company Admin', 'Super Admin']);
        $employeeId = (int)($_POST['employee_id'] ?? 0);
        $period = trim($_POST['period'] ?? '');
        $templateId = isset($_POST['template_id']) ? (int)$_POST['template_id'] : null;
        $currency = trim($_POST['currency'] ?? 'INR');
        $earnings = json_decode($_POST['earnings'] ?? '[]', true) ?: [];
        $deductions = json_decode($_POST['deductions'] ?? '[]', true) ?: [];

        if ($employeeId <= 0 || $period === '') {
            $response['message'] = 'Missing employee_id or period.';
            break;
        }

        // Reuse preview logic to compute values and template
        $_POST['employee_id'] = $employeeId;
        $_POST['period'] = $period;
        $_POST['template_id'] = $templateId;
        $_POST['currency'] = $currency;
        $_POST['earnings'] = json_encode($earnings);
        $_POST['deductions'] = json_encode($deductions);

        // Fetch employee for company id
        $empRes = query($mysqli, "SELECT e.*, u.company_id FROM employees e JOIN users u ON e.user_id = u.id WHERE e.id = ?", [$employeeId]);
        if (!$empRes['success'] || empty($empRes['data'])) {
            $response['message'] = 'Employee not found.';
            break;
        }
        $emp = $empRes['data'][0];

        // Select template
        if ($templateId) {
            $tplRes = query($mysqli, "SELECT * FROM payslip_templates WHERE id = ? AND is_active = 1", [$templateId]);
        } else {
            $tplRes = query($mysqli, "SELECT * FROM payslip_templates WHERE is_active = 1 AND (company_id = ? OR company_id IS NULL) ORDER BY company_id IS NULL DESC, id DESC LIMIT 1", [(int)($emp['company_id'] ?? 0)]);
        }
        if (!$tplRes['success'] || empty($tplRes['data'])) {
            $response['message'] = 'No active template found.';
            break;
        }
        $tpl = $tplRes['data'][0];

        $gross = 0.0; foreach ($earnings as $it) { $gross += (float)($it['amount'] ?? 0); }
        $ded = 0.0; foreach ($deductions as $it) { $ded += (float)($it['amount'] ?? 0); }
        $net = $gross - $ded;

        $insert = query($mysqli, "INSERT INTO payslips (company_id, employee_id, period, currency, earnings_json, deductions_json, gross_salary, net_salary, template_id, generated_by) VALUES (?,?,?,?,?,?,?,?,?,?)", [
            (int)($emp['company_id'] ?? null), $employeeId, $period, $currency,
            json_encode($earnings), json_encode($deductions), $gross, $net, $tpl['id'] ?? null, $user_id
        ]);
        if (!$insert['success']) {
            $response['message'] = 'Failed to generate payslip.';
            break;
        }

        // Return the freshly created payslip row with joined fields for live update
        $rowRes = query($mysqli, "SELECT p.*, e.first_name, e.last_name, e.employee_code FROM payslips p JOIN employees e ON p.employee_id = e.id WHERE p.id = ?", [$insert['insert_id']]);
        if ($rowRes['success'] && !empty($rowRes['data'])) {
            $response = ['success' => true, 'data' => ['payslip_id' => $insert['insert_id'], 'payslip' => $rowRes['data'][0]]];
        } else {
            $response = ['success' => true, 'data' => ['payslip_id' => $insert['insert_id']]];
        }
        break;

    case 'send_payslip':
        // HR only send
        requireRole(['HR Manager', 'Company Admin', 'Super Admin']);
        $payslipId = (int)($_POST['payslip_id'] ?? 0);
        $toEmployee = isset($_POST['to_employee']) ? (bool)$_POST['to_employee'] : true;
        $toManager = isset($_POST['to_manager']) ? (bool)$_POST['to_manager'] : false;
        $managerEmailInput = trim($_POST['manager_email'] ?? '');
        if ($payslipId <= 0) {
            $response['message'] = 'Missing payslip_id.';
            break;
        }

        // Load payslip, employee, template
        $psRes = query($mysqli, "SELECT p.*, e.first_name, e.last_name, e.employee_code, u.email, u.company_id, u.id AS employee_user_id, d.name AS department_name, g.name AS designation_name, t.subject, t.body_html FROM payslips p JOIN employees e ON p.employee_id = e.id JOIN users u ON e.user_id = u.id LEFT JOIN departments d ON e.department_id = d.id LEFT JOIN designations g ON e.designation_id = g.id LEFT JOIN payslip_templates t ON p.template_id = t.id WHERE p.id = ?", [$payslipId]);
        if (!$psRes['success'] || empty($psRes['data'])) {
            $response['message'] = 'Payslip not found.';
            break;
        }
        $ps = $psRes['data'][0];
        $company = $ps['company_id'] ? getCompanyById($mysqli, (int)$ps['company_id']) : null;

        $earnings = json_decode($ps['earnings_json'] ?? '[]', true) ?: [];
        $deductions = json_decode($ps['deductions_json'] ?? '[]', true) ?: [];
        $html = renderPayslipHTML($ps['body_html'], [
            'company_name' => $company['name'] ?? 'Company',
            'period' => $ps['period'],
            'employee_name' => trim(($ps['first_name'] ?? '') . ' ' . ($ps['last_name'] ?? '')),
            'employee_code' => $ps['employee_code'] ?? '',
            'department_name' => $ps['department_name'] ?? '',
            'designation_name' => $ps['designation_name'] ?? '',
            'gross_salary' => number_format((float)$ps['gross_salary'], 2),
            'net_salary' => number_format((float)$ps['net_salary'], 2),
            'currency' => $ps['currency'] ?? 'INR',
            'generated_at' => $ps['generated_at'] ?? date('Y-m-d H:i:s'),
            'earnings' => $earnings,
            'deductions' => $deductions,
        ]);
        $subject = renderTemplateString($ps['subject'] ?? 'Your payslip for {{period}}', ['period' => $ps['period']]);

        // Determine sender
        $emailFromSetting = query($mysqli, "SELECT setting_value FROM system_settings WHERE setting_key = 'company_email' LIMIT 1");
        $emailFrom = ($emailFromSetting['success'] && !empty($emailFromSetting['data'])) ? ($emailFromSetting['data'][0]['setting_value']) : 'no-reply@localhost';

        // Recipients
        $recipients = [];
        if ($toEmployee && !empty($ps['email'])) {
            $recipients[] = ['user_id' => (int)$ps['employee_user_id'], 'email' => $ps['email']];
        }
        if ($toManager) {
            $managerEmail = $managerEmailInput;
            if ($managerEmail === '') {
                // Try to find a manager in the same company
                $mgrRes = query($mysqli, "SELECT email, id FROM users WHERE company_id = ? AND role_id = 6 ORDER BY id ASC LIMIT 1", [(int)($ps['company_id'] ?? 0)]);
                if ($mgrRes['success'] && !empty($mgrRes['data'])) {
                    $managerEmail = $mgrRes['data'][0]['email'];
                    $recipients[] = ['user_id' => (int)$mgrRes['data'][0]['id'], 'email' => $managerEmail];
                }
            } else {
                $recipients[] = ['user_id' => null, 'email' => $managerEmail];
            }
        }

        $errors = [];
        if (empty($recipients)) {
            $errors[] = 'No recipient emails found.';
        }
        $allLogged = true;
        foreach ($recipients as $rcpt) {
            if (empty($rcpt['email'])) { $allLogged = false; $errors[] = 'Empty email for a recipient'; continue; }
            $logRes = query($mysqli, "INSERT INTO email_logs (user_id, email_to, email_from, subject, body, template_id, status, sent_at) VALUES (?,?,?,?,?,?, 'sent', NOW())", [
                $rcpt['user_id'], $rcpt['email'], $emailFrom, $subject, $html, null
            ]);
            if (!$logRes['success']) { $allLogged = false; $errors[] = $logRes['error'] ?? 'log failed'; }

            // Create notifications for known users
            if (!empty($rcpt['user_id'])) {
                query($mysqli, "INSERT INTO notifications (user_id, type, title, message, related_id, related_type) VALUES (?, 'payroll', 'Payslip', ?, ?, 'payslip')", [
                    (int)$rcpt['user_id'], $subject, $payslipId
                ]);
            }
        }

        // Update payslip status
        $upd = query($mysqli, "UPDATE payslips SET status = 'sent', sent_at = NOW() WHERE id = ?", [$payslipId]);

        if ($allLogged && $upd['success']) {
            // Fetch updated row for UI without full reload
            $rowRes = query($mysqli, "SELECT p.*, e.first_name, e.last_name, e.employee_code FROM payslips p JOIN employees e ON p.employee_id = e.id WHERE p.id = ?", [$payslipId]);
            $response = ['success' => true, 'message' => 'Payslip sent.', 'data' => $rowRes['success'] && !empty($rowRes['data']) ? $rowRes['data'][0] : null];
        } else {
            $response = ['success' => false, 'message' => 'Failed to send payslip.', 'errors' => $errors];
        }
        break;

    case 'get_company_payslips':
        // HR and Company Admin can see all company payslips; Manager can see team members' payslips
        if (!isLoggedIn()) { $response['message'] = 'Unauthorized.'; break; }
        $currentUser = getCurrentUser($mysqli);
        $roleName = $currentUser['role_name'] ?? '';
        $companyId = $currentUser['company_id'] ?? null;
        if (in_array($roleName, ['HR Manager', 'Company Admin', 'Super Admin'], true)) {
            if (!$companyId && $roleName !== 'Super Admin') { $response['message'] = 'No company scope.'; break; }
            $params = [];
            $sql = "SELECT p.*, e.first_name, e.last_name, e.employee_code FROM payslips p JOIN employees e ON p.employee_id = e.id";
            if ($roleName === 'Super Admin') {
                $sql .= " ORDER BY p.generated_at DESC";
            } else {
                $sql .= " WHERE p.company_id = ? ORDER BY p.generated_at DESC";
                $params[] = (int)$companyId;
            }
            $res = query($mysqli, $sql, $params);
            $response = $res['success'] ? ['success' => true, 'data' => $res['data']] : ['success' => false, 'message' => 'Failed to fetch company payslips'];
        } elseif ($roleName === 'Manager') {
            // Team-scoped: employees assigned by this manager in team_members
            $res = query($mysqli, "SELECT p.*, e.first_name, e.last_name, e.employee_code FROM payslips p JOIN employees e ON p.employee_id = e.id WHERE e.id IN (SELECT tm.employee_id FROM team_members tm WHERE tm.assigned_by = ?) ORDER BY p.generated_at DESC", [
                (int)$currentUser['id']
            ]);
            $response = $res['success'] ? ['success' => true, 'data' => $res['data']] : ['success' => false, 'message' => 'Failed to fetch team payslips'];
        } else {
            http_response_code(403);
            $response['message'] = 'Forbidden';
        }
        break;
    case 'get_payslip':
        $payslip_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        if ($payslip_id <= 0) { $response['message'] = 'Invalid payslip ID.'; break; }

        // Get employee ID for the current user
        $employee_result = query($mysqli, "SELECT id FROM employees WHERE user_id = ?", [$user_id]);
        if (!$employee_result['success'] || empty($employee_result['data'])) { $response['message'] = 'Employee profile not found.'; break; }
        $employee_id = (int)$employee_result['data'][0]['id'];

        // Load generated payslip with template and employee details
        $sql = "SELECT p.*, e.first_name, e.last_name, e.employee_code, u.company_id, d.name AS department_name, g.name AS designation_name, t.subject, t.body_html
                FROM payslips p
                JOIN employees e ON p.employee_id = e.id
                JOIN users u ON e.user_id = u.id
                LEFT JOIN departments d ON e.department_id = d.id
                LEFT JOIN designations g ON e.designation_id = g.id
                LEFT JOIN payslip_templates t ON p.template_id = t.id
                WHERE p.id = ? AND p.employee_id = ?";
        $result = query($mysqli, $sql, [$payslip_id, $employee_id]);
        if (!$result['success'] || empty($result['data'])) { $response['message'] = 'Payslip not found or access denied.'; break; }
        $ps = $result['data'][0];
        $company = $ps['company_id'] ? getCompanyById($mysqli, (int)$ps['company_id']) : null;
        $earnings = json_decode($ps['earnings_json'] ?? '[]', true) ?: [];
        $deductions = json_decode($ps['deductions_json'] ?? '[]', true) ?: [];
        $templateHtml = $ps['body_html'];
        if (empty($templateHtml)) {
            $templateHtml = '<div style="font-family:Arial,sans-serif; padding:16px">\n  <h2>{{company_name}}</h2>\n  <h3>Payslip - {{period}}</h3>\n  <p><strong>Employee:</strong> {{employee_name}} ({{employee_code}})</p>\n  <p><strong>Department:</strong> {{department_name}} | <strong>Designation:</strong> {{designation_name}}</p>\n  <hr/>\n  <h4>Earnings</h4>\n  <table width="100%" cellspacing="0" cellpadding="6" border="1">\n    <tr><th align="left">Component</th><th align="right">Amount</th></tr>\n    {{earnings_rows}}\n  </table>\n  <h4 style="margin-top:16px">Deductions</h4>\n  <table width="100%" cellspacing="0" cellpadding="6" border="1">\n    <tr><th align="left">Component</th><th align="right">Amount</th></tr>\n    {{deductions_rows}}\n  </table>\n  <hr/>\n  <p><strong>Gross:</strong> {{currency}} {{gross_salary}} &nbsp; | &nbsp; <strong>Net Pay:</strong> {{currency}} {{net_salary}}</p>\n  <p><small>Generated on {{generated_at}}</small></p>\n</div>';
        }
        $html = renderPayslipHTML($templateHtml, [
            'company_name' => $company['name'] ?? 'Company',
            'period' => $ps['period'],
            'employee_name' => trim(($ps['first_name'] ?? '') . ' ' . ($ps['last_name'] ?? '')),
            'employee_code' => $ps['employee_code'] ?? '',
            'department_name' => $ps['department_name'] ?? '',
            'designation_name' => $ps['designation_name'] ?? '',
            'gross_salary' => number_format((float)$ps['gross_salary'], 2),
            'net_salary' => number_format((float)$ps['net_salary'], 2),
            'currency' => $ps['currency'] ?? 'INR',
            'generated_at' => $ps['generated_at'] ?? date('Y-m-d H:i:s'),
            'earnings' => $earnings,
            'deductions' => $deductions,
        ]);
        $response = ['success' => true, 'data' => [
            'id' => $ps['id'],
            'period' => $ps['period'],
            'status' => $ps['status'],
            'gross_salary' => $ps['gross_salary'],
            'net_salary' => $ps['net_salary'],
            'html' => $html,
        ]];
        break;

    case 'get_payslips':
        // Get employee ID for the current user
        $employee_result = query($mysqli, "SELECT id FROM employees WHERE user_id = ?", [$user_id]);
        if (!$employee_result['success'] || empty($employee_result['data'])) {
            $response['message'] = 'Employee profile not found.';
            echo json_encode($response);
            exit();
        }
        $employee_id = $employee_result['data'][0]['id'];

        // Get all payslips for the employee
        $sql = "
            SELECT p.*, e.first_name, e.last_name, e.employee_code
            FROM payroll p
            JOIN employees e ON p.employee_id = e.id
            WHERE p.employee_id = ?
            ORDER BY p.period DESC
        ";
        $result = query($mysqli, $sql, [$employee_id]);

        if ($result['success']) {
            $response = ['success' => true, 'data' => $result['data']];
        } else {
            $response['message'] = 'Failed to fetch payslips.';
        }
        break;

    default:
        $response['message'] = 'Invalid action specified.';
        break;
}

echo json_encode($response);
?>