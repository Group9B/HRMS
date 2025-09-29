<?php
header('Content-Type: application/json');
require_once '../config/db.php';
require_once '../includes/functions.php';

$response = ['success' => false, 'message' => 'An unknown error occurred.'];

if (!isLoggedIn()) {
    $response['message'] = 'Unauthorized access.';
    echo json_encode($response);
    exit();
}

$action = $_REQUEST['action'] ?? '';
$user_id = $_SESSION['user_id'];
$company_id = $_SESSION['company_id'];
$role_id = $_SESSION['role_id'];

$employee_info = query($mysqli, "SELECT id FROM employees WHERE user_id = ?", [$user_id]);
$employee_id = $employee_info['success'] && !empty($employee_info['data']) ? $employee_info['data'][0]['id'] : 0;

switch ($action) {
    case 'get_leave_summary':
        $policies = query($mysqli, "SELECT id, leave_type, days_per_year FROM leave_policies WHERE company_id = ?", [$company_id])['data'] ?? [];

        // BUG FIX 1: Correctly SUM the number of days for multi-day leaves
        $used_leaves_result = query($mysqli, "
            SELECT p.leave_type, SUM(DATEDIFF(l.end_date, l.start_date) + 1) as days_taken
            FROM leaves l
            JOIN leave_policies p ON l.leave_type = p.leave_type
            WHERE l.employee_id = ? AND l.status = 'approved' AND YEAR(l.start_date) = YEAR(CURDATE()) AND p.company_id = ?
            GROUP BY p.leave_type
        ", [$employee_id, $company_id])['data'] ?? [];

        $used_map = array_column($used_leaves_result, 'days_taken', 'leave_type');

        $balances = [];
        foreach ($policies as $policy) {
            $used = $used_map[$policy['leave_type']] ?? 0;
            $balances[] = ['type' => $policy['leave_type'], 'balance' => $policy['days_per_year'] - $used, 'total' => $policy['days_per_year']];
        }

        $next_holiday_result = query($mysqli, "SELECT holiday_name, holiday_date FROM holidays WHERE company_id = ? AND holiday_date >= CURDATE() ORDER BY holiday_date ASC LIMIT 1", [$company_id]);
        $next_holiday = $next_holiday_result['success'] && !empty($next_holiday_result['data']) ? $next_holiday_result['data'][0] : null;

        $policy_doc_result = query($mysqli, "SELECT id, doc_name FROM documents WHERE company_id = ? AND related_type = 'policy' ORDER BY uploaded_at DESC LIMIT 1", [$company_id]);
        $policy_document = $policy_doc_result['success'] && !empty($policy_doc_result['data']) ? $policy_doc_result['data'][0] : null;

        $response = ['success' => true, 'data' => ['balances' => $balances, 'next_holiday' => $next_holiday, 'policy_document' => $policy_document]];
        break;

    case 'get_my_leaves':
        $result = query($mysqli, "SELECT * FROM leaves WHERE employee_id = ? ORDER BY start_date DESC", [$employee_id]);
        $response = ['success' => true, 'data' => $result['data'] ?? []];
        break;

    case 'get_pending_requests':
        // BUG FIX 4: Use 'applied_at' for ordering
        $sql = "SELECT l.*, e.first_name, e.last_name 
                FROM leaves l 
                JOIN employees e ON l.employee_id = e.id
                JOIN users u ON e.user_id = u.id
                WHERE u.company_id = ? AND l.employee_id != ?
                ORDER BY l.applied_at DESC";
        $params = [$company_id, $employee_id];

        if ($role_id == 6) { // Manager role
            $manager_dept_info = query($mysqli, "SELECT department_id FROM employees WHERE id = ?", [$employee_id]);
            $manager_dept_id = $manager_dept_info['data'][0]['department_id'] ?? 0;
            if ($manager_dept_id > 0) {
                $sql = "SELECT l.*, e.first_name, e.last_name FROM leaves l JOIN employees e ON l.employee_id = e.id JOIN users u ON e.user_id = u.id WHERE u.company_id = ? AND l.employee_id != ? AND e.department_id = ? ORDER BY l.applied_at DESC";
                $params = [$company_id, $employee_id, $manager_dept_id];
            }
        }

        $result = query($mysqli, $sql, $params);
        $response = ['success' => true, 'data' => $result['data'] ?? []];
        break;

    case 'apply_leave':
        // ... (rest of the case remains the same)
        $start_date = $_POST['start_date'] ?? '';
        $end_date = $_POST['end_date'] ?? '';
        $leave_type = $_POST['leave_type'] ?? '';
        $reason = $_POST['reason'] ?? '';

        if (empty($start_date) || empty($end_date) || empty($leave_type)) {
            $response['message'] = 'Please fill all required fields.';
            break;
        }
        if ($end_date < $start_date) {
            $response['message'] = 'End date cannot be before start date.';
            break;
        }

        $result = query($mysqli, "INSERT INTO leaves (employee_id, start_date, end_date, leave_type, reason) VALUES (?, ?, ?, ?, ?)", [$employee_id, $start_date, $end_date, $leave_type, $reason]);
        if ($result['success']) {
            $response = ['success' => true, 'message' => 'Leave request submitted successfully!'];
        } else {
            $response['message'] = 'Failed to submit leave request.';
        }
        break;

    case 'update_status':
        // ... (rest of the case remains the same)
        $leave_id = (int) ($_POST['leave_id'] ?? 0);
        $status = $_POST['status'] ?? '';

        if (!in_array($status, ['approved', 'rejected'])) {
            $response['message'] = 'Invalid status.';
            break;
        }
        $sql = "UPDATE leaves l JOIN employees e ON l.employee_id = e.id SET l.status = ?, l.approved_by = ? WHERE l.id = ? AND e.user_id IN (SELECT id FROM users WHERE company_id = ?)";
        $result = query($mysqli, $sql, [$status, $user_id, $leave_id, $company_id]);

        if ($result['success'] && $result['affected_rows'] > 0) {
            $response = ['success' => true, 'message' => "Request has been $status."];
        } else {
            $response['message'] = 'Failed to update status or unauthorized.';
        }
        break;

    case 'cancel_leave':
        // ... (rest of the case remains the same)
        $leave_id = (int) ($_POST['leave_id'] ?? 0);
        $sql = "UPDATE leaves SET status = 'cancelled' WHERE id = ? AND employee_id = ? AND status = 'pending'";
        $result = query($mysqli, $sql, [$leave_id, $employee_id]);

        if ($result['success'] && $result['affected_rows'] > 0) {
            $response = ['success' => true, 'message' => 'Your leave request has been cancelled.'];
        } else {
            $response['message'] = 'Could not cancel request. It might have already been actioned.';
        }
        break;

    default:
        $response['message'] = 'Invalid action specified.';
        break;
}

echo json_encode($response);

exit();
?>