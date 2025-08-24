<?php
header('Content-Type: application/json');
require_once '../config/db.php';
require_once '../includes/functions.php';

$response = ['success' => false, 'message' => 'An unknown error occurred.'];

// Security Check: Must be a logged-in Company Admin or HR Manager
if (!isLoggedIn() || !in_array($_SESSION['role_id'], [2, 3])) {
    $response['message'] = 'Unauthorized access.';
    echo json_encode($response);
    exit();
}

$action = $_REQUEST['action'] ?? '';
$company_id = $_SESSION['company_id'];
$user_id = $_SESSION['user_id'];

switch ($action) {
    // --- DEPARTMENT ACTIONS ---
    case 'get_departments':
        $result = query($mysqli, "SELECT * FROM departments WHERE company_id = ? ORDER BY name ASC", [$company_id]);
        if ($result['success']) {
            $response = ['success' => true, 'data' => $result['data']];
        }
        break;
    case 'add_edit_department':
        $id = isset($_POST['id']) ? (int) $_POST['id'] : 0;
        $name = $_POST['name'] ?? '';
        $description = $_POST['description'] ?? '';
        if (empty($name)) {
            $response['message'] = 'Department name is required.';
            break;
        }
        if ($id === 0) {
            $result = query($mysqli, "INSERT INTO departments (company_id, name, description) VALUES (?, ?, ?)", [$company_id, $name, $description]);
        } else {
            $result = query($mysqli, "UPDATE departments SET name = ?, description = ? WHERE id = ? AND company_id = ?", [$name, $description, $id, $company_id]);
        }
        if ($result['success']) {
            $response = ['success' => true, 'message' => 'Department saved!'];
        }
        break;
    case 'delete_department':
        $id = isset($_POST['id']) ? (int) $_POST['id'] : 0;
        $result = query($mysqli, "DELETE FROM departments WHERE id = ? AND company_id = ?", [$id, $company_id]);
        if ($result['success']) {
            $response = ['success' => true, 'message' => 'Department deleted!'];
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
        $id = isset($_POST['id']) ? (int) $_POST['id'] : 0;
        $dept_id = $_POST['department_id'] ?? 0;
        $name = $_POST['name'] ?? '';
        $description = $_POST['description'] ?? '';
        if (empty($name) || empty($dept_id)) {
            $response['message'] = 'Designation name and department are required.';
            break;
        }
        if ($id === 0) {
            $result = query($mysqli, "INSERT INTO designations (department_id, name, description) VALUES (?, ?, ?)", [$dept_id, $name, $description]);
        } else {
            $result = query($mysqli, "UPDATE designations SET department_id = ?, name = ?, description = ? WHERE id = ?", [$dept_id, $name, $description, $id]);
        }
        if ($result['success']) {
            $response = ['success' => true, 'message' => 'Designation saved!'];
        }
        break;
    case 'delete_designation':
        $id = isset($_POST['id']) ? (int) $_POST['id'] : 0;
        $result = query($mysqli, "DELETE FROM designations WHERE id = ?", [$id]);
        if ($result['success']) {
            $response = ['success' => true, 'message' => 'Designation deleted!'];
        }
        break;

    // --- TEAM ACTIONS ---
    case 'get_teams':
        $sql = "SELECT t.*, (SELECT COUNT(*) FROM team_members tm WHERE tm.team_id = t.id) as member_count FROM teams t WHERE t.company_id = ? ORDER BY t.name ASC";
        $result = query($mysqli, $sql, [$company_id]);
        if ($result['success']) {
            $response = ['success' => true, 'data' => $result['data']];
        }
        break;
    case 'add_edit_team':
        $id = isset($_POST['id']) ? (int) $_POST['id'] : 0;
        $name = $_POST['name'] ?? '';
        $description = $_POST['description'] ?? '';
        if (empty($name)) {
            $response['message'] = 'Team name is required.';
            break;
        }
        if ($id === 0) {
            $result = query($mysqli, "INSERT INTO teams (company_id, name, description, created_by) VALUES (?, ?, ?, ?)", [$company_id, $name, $description, $user_id]);
        } else {
            $result = query($mysqli, "UPDATE teams SET name = ?, description = ?, updated_by = ? WHERE id = ? AND company_id = ?", [$name, $description, $user_id, $id, $company_id]);
        }
        if ($result['success']) {
            $response = ['success' => true, 'message' => 'Team saved!'];
        }
        break;
    case 'delete_team':
        $id = isset($_POST['id']) ? (int) $_POST['id'] : 0;
        $result = query($mysqli, "DELETE FROM teams WHERE id = ? AND company_id = ?", [$id, $company_id]);
        if ($result['success']) {
            $response = ['success' => true, 'message' => 'Team deleted!'];
        }
        break;
    case 'get_team_members':
        $team_id = isset($_GET['team_id']) ? (int) $_GET['team_id'] : 0;
        $sql = "SELECT tm.id, e.id as employee_id, e.first_name, e.last_name FROM team_members tm JOIN employees e ON tm.employee_id = e.id WHERE tm.team_id = ?";
        $result = query($mysqli, $sql, [$team_id]);
        if ($result['success']) {
            $response = ['success' => true, 'data' => $result['data']];
        }
        break;
    case 'add_team_member':
        $team_id = isset($_POST['team_id']) ? (int) $_POST['team_id'] : 0;
        $employee_id = isset($_POST['employee_id']) ? (int) $_POST['employee_id'] : 0;
        $result = query($mysqli, "INSERT INTO team_members (team_id, employee_id, assigned_by) VALUES (?, ?, ?)", [$team_id, $employee_id, $user_id]);
        if ($result['success']) {
            $response = ['success' => true, 'message' => 'Member added!'];
        }
        break;
    case 'remove_team_member':
        $member_id = isset($_POST['member_id']) ? (int) $_POST['member_id'] : 0;
        $result = query($mysqli, "DELETE FROM team_members WHERE id = ?", [$member_id]);
        if ($result['success']) {
            $response = ['success' => true, 'message' => 'Member removed!'];
        }
        break;

    // --- SHIFT ACTIONS ---
    case 'get_shifts':
        $result = query($mysqli, "SELECT * FROM shifts WHERE company_id = ? ORDER BY name ASC", [$company_id]);
        if ($result['success']) {
            $response = ['success' => true, 'data' => $result['data']];
        }
        break;
    case 'add_edit_shift':
        $id = isset($_POST['id']) ? (int) $_POST['id'] : 0;
        $name = $_POST['name'] ?? '';
        $start_time = $_POST['start_time'] ?? '';
        $end_time = $_POST['end_time'] ?? '';
        $description = $_POST['description'] ?? '';
        if (empty($name) || empty($start_time) || empty($end_time)) {
            $response['message'] = 'Shift name, start time, and end time are required.';
            break;
        }
        if ($id === 0) {
            $result = query($mysqli, "INSERT INTO shifts (company_id, name, start_time, end_time, description) VALUES (?, ?, ?, ?, ?)", [$company_id, $name, $start_time, $end_time, $description]);
        } else {
            $result = query($mysqli, "UPDATE shifts SET name = ?, start_time = ?, end_time = ?, description = ? WHERE id = ? AND company_id = ?", [$name, $start_time, $end_time, $description, $id, $company_id]);
        }
        if ($result['success']) {
            $response = ['success' => true, 'message' => 'Shift saved!'];
        }
        break;
    case 'delete_shift':
        $id = isset($_POST['id']) ? (int) $_POST['id'] : 0;
        $result = query($mysqli, "DELETE FROM shifts WHERE id = ? AND company_id = ?", [$id, $company_id]);
        if ($result['success']) {
            $response = ['success' => true, 'message' => 'Shift deleted!'];
        }
        break;

    default:
        $response['message'] = 'Invalid action specified.';
        break;
}

echo json_encode($response);
exit();
?>