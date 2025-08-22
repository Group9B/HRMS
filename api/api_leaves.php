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
$is_manager = in_array($_SESSION['role_id'], [2, 3]); // Company Admin or HR Manager

switch ($action) {
    case 'get_leaves':
        // Managers see all requests from their company; employees see only their own.
        if ($is_manager) {
            $sql = "SELECT l.*, e.first_name, e.last_name FROM leaves l JOIN employees e ON l.employee_id = e.id JOIN departments d ON e.department_id = d.id WHERE d.company_id = ? ORDER BY l.applied_at DESC";
            $result = query($mysqli, $sql, [$company_id]);
        } else {
            // Find the employee_id associated with the current user_id
            $emp_id_res = query($mysqli, "SELECT id FROM employees WHERE user_id = ?", [$user_id]);
            if ($emp_id_res['success'] && !empty($emp_id_res['data'])) {
                $employee_id = $emp_id_res['data'][0]['id'];
                $sql = "SELECT * FROM leaves WHERE employee_id = ? ORDER BY applied_at DESC";
                $result = query($mysqli, $sql, [$employee_id]);
            } else {
                $result = ['success' => true, 'data' => []]; // No employee profile found
            }
        }
        if ($result['success']) {
            $response = ['success' => true, 'data' => $result['data']];
        }
        break;

    case 'apply_leave':
        $start_date = $_POST['start_date'] ?? '';
        $end_date = $_POST['end_date'] ?? '';
        $leave_type = $_POST['leave_type'] ?? 'Annual';
        $reason = $_POST['reason'] ?? '';

        $emp_id_res = query($mysqli, "SELECT id FROM employees WHERE user_id = ?", [$user_id]);
        if (!$emp_id_res['success'] || empty($emp_id_res['data'])) {
            $response['message'] = 'You must have an employee profile to apply for leave.';
            break;
        }
        $employee_id = $emp_id_res['data'][0]['id'];

        if (empty($start_date) || empty($end_date)) {
            $response['message'] = 'Start and end dates are required.';
            break;
        }

        $sql = "INSERT INTO leaves (employee_id, leave_type, start_date, end_date, reason) VALUES (?, ?, ?, ?, ?)";
        $result = query($mysqli, $sql, [$employee_id, $leave_type, $start_date, $end_date, $reason]);
        if ($result['success']) {
            $response = ['success' => true, 'message' => 'Leave request submitted successfully!'];
        }
        break;

    case 'update_status':
        if (!$is_manager) {
            $response['message'] = 'You do not have permission to perform this action.';
            break;
        }

        $leave_id = isset($_POST['leave_id']) ? (int) $_POST['leave_id'] : 0;
        $status = $_POST['status'] ?? '';

        if ($leave_id > 0 && in_array($status, ['approved', 'rejected'])) {
            $sql = "UPDATE leaves SET status = ?, approved_by = ? WHERE id = ?";
            $result = query($mysqli, $sql, [$status, $user_id, $leave_id]);
            if ($result['success']) {
                $response = ['success' => true, 'message' => 'Leave request has been ' . $status . '.'];
            }
        } else {
            $response['message'] = 'Invalid leave ID or status provided.';
        }
        break;

    default:
        $response['message'] = 'Invalid action specified.';
        break;
}

echo json_encode($response);
exit();
?>