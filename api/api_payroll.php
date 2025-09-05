<?php
header('Content-Type: application/json');
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
    case 'get_payslip':
        $payslip_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        
        if ($payslip_id <= 0) {
            $response['message'] = 'Invalid payslip ID.';
            echo json_encode($response);
            exit();
        }

        // Get employee ID for the current user
        $employee_result = query($mysqli, "SELECT id, first_name, last_name, employee_code FROM employees WHERE user_id = ?", [$user_id]);
        if (!$employee_result['success'] || empty($employee_result['data'])) {
            $response['message'] = 'Employee profile not found.';
            echo json_encode($response);
            exit();
        }
        $employee = $employee_result['data'][0];
        $employee_id = $employee['id'];

        // Get payslip details
        $sql = "
            SELECT p.*, e.first_name, e.last_name, e.employee_code
            FROM payroll p
            JOIN employees e ON p.employee_id = e.id
            WHERE p.id = ? AND p.employee_id = ?
        ";
        $result = query($mysqli, $sql, [$payslip_id, $employee_id]);

        if ($result['success'] && !empty($result['data'])) {
            $payslip = $result['data'][0];
            $payslip['employee_name'] = $payslip['first_name'] . ' ' . $payslip['last_name'];
            $response = ['success' => true, 'data' => $payslip];
        } else {
            $response['message'] = 'Payslip not found or access denied.';
        }
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
            ORDER BY p.year DESC, p.month DESC
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