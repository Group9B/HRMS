<?php
header('Content-Type: application/json');
require_once '../config/db.php';
require_once '../includes/functions.php';

$response = ['success' => false, 'message' => 'An unknown error occurred.'];

// Security Check: Must be a logged-in manager (Role ID 6, or adjust as needed)
if (!isLoggedIn() || $_SESSION['role_id'] !== 6) {
    $response['message'] = 'Unauthorized access.';
    echo json_encode($response);
    exit();
}

$manager_user_id = $_SESSION['user_id'];

// Get the manager's department ID to scope all queries
$manager_info = query($mysqli, "SELECT department_id FROM employees WHERE user_id = ?", [$manager_user_id]);
if (!$manager_info['success'] || empty($manager_info['data'])) {
    $response['message'] = 'Manager profile not found.';
    echo json_encode($response);
    exit();
}
$manager_department_id = $manager_info['data'][0]['department_id'];

$action = $_REQUEST['action'] ?? '';

switch ($action) {
    case 'get_performance_data':
        $employee_filter = $_GET['employee_id'] ?? '';
        $period_filter = $_GET['period'] ?? date('Y-m');

        $params = [$manager_department_id];
        $sql_where = "e.department_id = ?";

        if (!empty($employee_filter)) {
            $sql_where .= " AND p.employee_id = ?";
            $params[] = $employee_filter;
        }
        if (!empty($period_filter)) {
            $sql_where .= " AND p.period = ?";
            $params[] = $period_filter;
        }

        $sql = "SELECT p.*, e.first_name, e.last_name, u.username as evaluator_name
                FROM performance p
                JOIN employees e ON p.employee_id = e.id
                LEFT JOIN users u ON p.evaluator_id = u.id
                WHERE $sql_where ORDER BY p.created_at DESC";

        $result = query($mysqli, $sql, $params);
        $chart_stats = query($mysqli, "
            SELECT
                COUNT(CASE WHEN p.score >= 80 THEN 1 END) as excellent,
                COUNT(CASE WHEN p.score >= 60 AND p.score < 80 THEN 1 END) as good,
                COUNT(CASE WHEN p.score >= 40 AND p.score < 60 THEN 1 END) as average,
                COUNT(CASE WHEN p.score < 40 THEN 1 END) as poor
            FROM performance p JOIN employees e ON p.employee_id = e.id
            WHERE e.department_id = ? AND p.period = ?
        ", [$manager_department_id, $period_filter]);

        if ($result['success'] && $chart_stats['success']) {
            $response = [
                'success' => true,
                'data' => $result['data'],
                'chart_data' => $chart_stats['data'][0] ?? ['excellent' => 0, 'good' => 0, 'average' => 0, 'poor' => 0]
            ];
        } else {
            $response['message'] = 'Failed to fetch performance data.';
        }
        break;

    case 'add_edit_performance':
        $id = (int) ($_POST['id'] ?? 0);
        $employee_id = (int) ($_POST['employee_id'] ?? 0);
        $period = $_POST['period'] ?? '';
        $score = (int) ($_POST['score'] ?? 0);
        $remarks = $_POST['remarks'] ?? '';

        // Security check: Ensure the employee belongs to the manager's department
        $emp_check = query($mysqli, "SELECT id FROM employees WHERE id = ? AND department_id = ?", [$employee_id, $manager_department_id]);
        if (!$emp_check['success'] || empty($emp_check['data'])) {
            $response['message'] = 'You can only review employees in your department.';
            break;
        }

        if ($id === 0) { // Add
            $sql = "INSERT INTO performance (employee_id, evaluator_id, period, score, remarks) VALUES (?, ?, ?, ?, ?)";
            $params = [$employee_id, $manager_user_id, $period, $score, $remarks];
        } else { // Edit
            $sql = "UPDATE performance SET employee_id = ?, period = ?, score = ?, remarks = ? WHERE id = ?";
            $params = [$employee_id, $period, $score, $remarks, $id];
        }
        $result = query($mysqli, $sql, $params);
        if ($result['success']) {
            $response = ['success' => true, 'message' => 'Performance review saved successfully!'];
        } else {
            $response['message'] = 'Failed to save review.';
        }
        break;

    case 'get_performance_details':
        $id = (int) ($_GET['id'] ?? 0);
        $sql = "SELECT p.*, e.first_name, e.last_name, e.employee_code, d.name as designation_name, u.username as evaluator_name
                FROM performance p
                JOIN employees e ON p.employee_id = e.id
                LEFT JOIN designations d ON e.designation_id = d.id
                LEFT JOIN users u ON p.evaluator_id = u.id
                WHERE p.id = ? AND e.department_id = ?";
        $result = query($mysqli, $sql, [$id, $manager_department_id]);
        if ($result['success'] && !empty($result['data'])) {
            $response = ['success' => true, 'data' => $result['data'][0]];
        } else {
            $response['message'] = 'Could not find performance review.';
        }
        break;

    case 'delete_performance':
        $id = (int) ($_POST['id'] ?? 0);
        // Security check is implicit in the JOIN
        $sql = "DELETE p FROM performance p JOIN employees e ON p.employee_id = e.id WHERE p.id = ? AND e.department_id = ?";
        $result = query($mysqli, $sql, [$id, $manager_department_id]);
        if ($result['success'] && $result['affected_rows'] > 0) {
            $response = ['success' => true, 'message' => 'Review deleted successfully!'];
        } else {
            $response['message'] = 'Failed to delete review.';
        }
        break;

    default:
        $response['message'] = 'Invalid action.';
        break;
}

echo json_encode($response);

exit();
?>