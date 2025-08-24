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

// A helper function to get the current user's employee ID
function getEmployeeId($mysqli, $user_id)
{
    $emp_id_res = query($mysqli, "SELECT id FROM employees WHERE user_id = ?", [$user_id]);
    if ($emp_id_res['success'] && !empty($emp_id_res['data'])) {
        return $emp_id_res['data'][0]['id'];
    }
    return null;
}

$employee_id = getEmployeeId($mysqli, $user_id);

switch ($action) {
    case 'get_leaves':
        if ($is_manager) {
            // UPDATED: Managers now see all requests *except their own* in this list.
            $sql = "SELECT l.*, e.first_name, e.last_name FROM leaves l 
                    JOIN employees e ON l.employee_id = e.id 
                    JOIN departments d ON e.department_id = d.id 
                    WHERE d.company_id = ? AND l.employee_id != ? 
                    ORDER BY l.applied_at DESC";
            $result = query($mysqli, $sql, [$company_id, $employee_id ?? 0]);
        } else {
            // Non-manager employees see only their own requests.
            if ($employee_id) {
                $sql = "SELECT * FROM leaves WHERE employee_id = ? ORDER BY applied_at DESC";
                $result = query($mysqli, $sql, [$employee_id]);
            } else {
                $result = ['success' => true, 'data' => []]; // No employee profile found
            }
        }
        if (isset($result) && $result['success']) {
            $response = ['success' => true, 'data' => $result['data']];
        }
        break;

    case 'get_my_leaves':
        // NEW: An endpoint specifically for the "My Requests" tab to avoid conflicts.
        if ($employee_id) {
            $sql = "SELECT * FROM leaves WHERE employee_id = ? ORDER BY applied_at DESC";
            $result = query($mysqli, $sql, [$employee_id]);
            if ($result['success']) {
                $response = ['success' => true, 'data' => $result['data']];
            }
        } else {
            $response = ['success' => true, 'data' => []];
        }
        break;

    case 'apply_leave':
        $start_date = $_POST['start_date'] ?? '';
        $end_date = $_POST['end_date'] ?? '';
        $leave_type = $_POST['leave_type'] ?? 'Annual';
        $reason = $_POST['reason'] ?? '';

        if (!$employee_id) {
            $response['message'] = 'You must have an employee profile to apply for leave.';
            break;
        }

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

    case 'cancel_leave':
        // NEW: Action to allow an employee to cancel their own PENDING leave request.
        $leave_id = isset($_POST['leave_id']) ? (int) $_POST['leave_id'] : 0;

        if (!$employee_id) {
            $response['message'] = 'Employee profile not found.';
            break;
        }
        if ($leave_id <= 0) {
            $response['message'] = 'Invalid leave ID provided.';
            break;
        }

        // Ensure the user can only delete their own pending request
        $sql = "DELETE FROM leaves WHERE id = ? AND employee_id = ? AND status = 'pending'";
        $result = query($mysqli, $sql, [$leave_id, $employee_id]);

        if ($result['success'] && $result['affected_rows'] > 0) {
            $response = ['success' => true, 'message' => 'Leave request has been cancelled.'];
        } else {
            $response['message'] = 'Could not cancel request. It may have already been actioned or does not exist.';
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