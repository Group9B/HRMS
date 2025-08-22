<?php
header('Content-Type: application/json');
require_once '../config/db.php';
require_once '../includes/functions.php';

$response = ['success' => false, 'message' => 'An unknown error occurred.'];

// Security Check: Must be a logged-in Company Admin
if (!isLoggedIn() || !isset($_SESSION['role_id']) || $_SESSION['role_id'] !== 2) {
    $response['message'] = 'Unauthorized access.';
    echo json_encode($response);
    exit();
}

$action = $_REQUEST['action'] ?? '';
$company_id = $_SESSION['company_id'];

switch ($action) {
    // --- DEPARTMENT ACTIONS ---
    case 'get_departments':
        $result = query($mysqli, "SELECT * FROM departments WHERE company_id = ? ORDER BY name ASC", [$company_id]);
        if ($result['success']) {
            $response = ['success' => true, 'data' => $result['data']];
        }
        break;

    case 'add_edit_department':
        $dept_id = isset($_POST['department_id']) ? (int) $_POST['department_id'] : 0;
        $name = $_POST['name'] ?? '';
        $description = $_POST['description'] ?? '';

        if (empty($name)) {
            $response['message'] = 'Department name is required.';
            break;
        }

        if ($dept_id === 0) { // Add
            $sql = "INSERT INTO departments (company_id, name, description) VALUES (?, ?, ?)";
            $result = query($mysqli, $sql, [$company_id, $name, $description]);
        } else { // Edit
            $sql = "UPDATE departments SET name = ?, description = ? WHERE id = ? AND company_id = ?";
            $result = query($mysqli, $sql, [$name, $description, $dept_id, $company_id]);
        }
        if ($result['success']) {
            $response = ['success' => true, 'message' => 'Department saved successfully!'];
        }
        break;

    case 'delete_department':
        $dept_id = isset($_POST['department_id']) ? (int) $_POST['department_id'] : 0;
        $result = query($mysqli, "DELETE FROM departments WHERE id = ? AND company_id = ?", [$dept_id, $company_id]);
        if ($result['success']) {
            $response = ['success' => true, 'message' => 'Department deleted successfully!'];
        }
        break;

    // --- DESIGNATION ACTIONS ---
    case 'get_designations':
        $sql = "SELECT d.*, dept.name as department_name FROM designations d JOIN departments dept ON d.department_id = dept.id WHERE dept.company_id = ? ORDER BY d.name ASC";
        $result = query($mysqli, $sql, [$company_id]);
        if ($result['success']) {
            $response = ['success' => true, 'data' => $result['data']];
        }
        break;

    case 'add_edit_designation':
        $des_id = isset($_POST['designation_id']) ? (int) $_POST['designation_id'] : 0;
        $dept_id = $_POST['department_id'] ?? 0;
        $name = $_POST['name'] ?? '';
        $description = $_POST['description'] ?? '';

        if (empty($name) || empty($dept_id)) {
            $response['message'] = 'Designation name and department are required.';
            break;
        }

        if ($des_id === 0) { // Add
            $sql = "INSERT INTO designations (department_id, name, description) VALUES (?, ?, ?)";
            $result = query($mysqli, $sql, [$dept_id, $name, $description]);
        } else { // Edit
            $sql = "UPDATE designations SET department_id = ?, name = ?, description = ? WHERE id = ?";
            $result = query($mysqli, $sql, [$dept_id, $name, $description, $des_id]);
        }
        if ($result['success']) {
            $response = ['success' => true, 'message' => 'Designation saved successfully!'];
        }
        break;

    case 'delete_designation':
        $des_id = isset($_POST['designation_id']) ? (int) $_POST['designation_id'] : 0;
        $result = query($mysqli, "DELETE FROM designations WHERE id = ?", [$des_id]);
        if ($result['success']) {
            $response = ['success' => true, 'message' => 'Designation deleted successfully!'];
        }
        break;

    default:
        $response['message'] = 'Invalid action specified.';
        break;
}

echo json_encode($response);
exit();
?>