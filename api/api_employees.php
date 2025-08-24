<?php
// Set the content type to JSON for all responses
header('Content-Type: application/json');

require_once '../config/db.php';
require_once '../includes/functions.php';

$response = ['success' => false, 'message' => 'An unknown error occurred.'];

// Security Check: Must be a logged-in Company Admin
if (!isLoggedIn() && !in_array($_SESSION['role_id'], [2, 3])) {
    $response['message'] = 'Unauthorized access.';
    echo json_encode($response);
    exit();
}

$action = $_REQUEST['action'] ?? '';
$company_id = $_SESSION['company_id']; // All operations are scoped to this company

switch ($action) {
    case 'get_employees':
        $sql = "
            SELECT e.*, d.name as department_name, des.name as designation_name, s.name as shift_name
            FROM employees e
            LEFT JOIN departments d ON e.department_id = d.id
            LEFT JOIN designations des ON e.designation_id = des.id
            LEFT JOIN shifts s ON e.shift_id = s.id
            WHERE d.company_id = ?
            ORDER BY e.first_name ASC
        ";
        $result = query($mysqli, $sql, [$company_id]);
        if ($result['success']) {
            $response = ['success' => true, 'data' => $result['data']];
        } else {
            $response['message'] = 'Failed to fetch employees.';
        }
        break;

    case 'add_edit':
        $employee_id = isset($_POST['employee_id']) ? (int) $_POST['employee_id'] : 0;
        $user_id = isset($_POST['user_id']) ? (int) $_POST['user_id'] : 0;
        $first_name = $_POST['first_name'] ?? '';
        $last_name = $_POST['last_name'] ?? '';
        $department_id = $_POST['department_id'] ?? null;
        $designation_id = $_POST['designation_id'] ?? null;
        $shift_id = $_POST['shift_id'] ?? null; // Added shift_id
        $date_of_joining = $_POST['date_of_joining'] ?? null;
        $status = $_POST['status'] ?? 'active';

        if (empty($first_name) || empty($last_name) || empty($user_id) || empty($department_id)) {
            $response['message'] = 'Please fill in all required fields.' . $user_id . $department_id;
            break;
        }

        if ($employee_id === 0) { // Add new employee
            $sql = "INSERT INTO employees (user_id, first_name, last_name, department_id, designation_id, shift_id, date_of_joining, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
            $params = [$user_id, $first_name, $last_name, $department_id, $designation_id, $shift_id, $date_of_joining, $status];
        } else { // Edit existing employee
            $sql = "UPDATE employees SET user_id = ?, first_name = ?, last_name = ?, department_id = ?, designation_id = ?, shift_id = ?, date_of_joining = ?, status = ? WHERE id = ? AND user_id IN (SELECT id FROM users WHERE company_id = ?)";
            $params = [$user_id, $first_name, $last_name, $department_id, $designation_id, $shift_id, $date_of_joining, $status, $employee_id, $company_id];
        }

        $result = query($mysqli, $sql, $params);
        if ($result['success']) {
            $response = ['success' => true, 'message' => 'Employee saved successfully!'];
        } else {
            $response['message'] = 'Database error: ' . $result['error'];
        }
        break;

    case 'delete':
        $employee_id = isset($_POST['employee_id']) ? (int) $_POST['employee_id'] : 0;
        if ($employee_id > 0) {
            $sql = "DELETE e FROM employees e JOIN departments d ON e.department_id = d.id WHERE e.id = ? AND d.company_id = ?";
            $result = query($mysqli, $sql, [$employee_id, $company_id]);
            if ($result['success'] && $result['affected_rows'] > 0) {
                $response = ['success' => true, 'message' => 'Employee deleted successfully!'];
            } else {
                $response['message'] = 'Failed to delete employee or employee not found.';
            }
        }
        break;

    default:
        $response['message'] = 'Invalid action specified.';
        break;
}

echo json_encode($response);
exit();
?>