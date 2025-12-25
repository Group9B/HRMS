<?php
require_once '../config/db.php';
require_once '../includes/functions.php';

header('Content-Type: application/json');

if (!isLoggedIn() || $_SESSION['role_id'] !== 6) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit;
}

$user_id = $_SESSION['user_id'];
$manager_result = query($mysqli, "SELECT department_id FROM employees WHERE user_id = ?", [$user_id]);
$manager_department_id = $manager_result['success'] ? $manager_result['data'][0]['department_id'] : 0;

$action = $_GET['action'] ?? $_POST['action'] ?? '';

switch ($action) {
    case 'get_performance_data':
        $employee_filter = $_GET['employee_id'] ?? '';
        $period_filter = $_GET['period'] ?? date('Y-m');

        $where_msg = "e.department_id = ? AND p.period = ?";
        $params = [$manager_department_id, $period_filter];

        if (!empty($employee_filter)) {
            $where_msg .= " AND p.employee_id = ?";
            $params[] = $employee_filter;
        }

        $query = "
            SELECT p.*, e.first_name, e.last_name, 
                   CONCAT(m.first_name, ' ', m.last_name) as evaluator_name
            FROM performance_reviews p
            JOIN employees e ON p.employee_id = e.id
            LEFT JOIN employees m ON p.evaluator_id = m.id
            WHERE $where_msg
            ORDER BY p.created_at DESC
        ";

        $result = query($mysqli, $query, $params);

        // Calculate chart data
        $chart_data = ['excellent' => 0, 'good' => 0, 'average' => 0, 'poor' => 0];
        if ($result['success']) {
            foreach ($result['data'] as $row) {
                if ($row['score'] >= 80)
                    $chart_data['excellent']++;
                elseif ($row['score'] >= 60)
                    $chart_data['good']++;
                elseif ($row['score'] >= 40)
                    $chart_data['average']++;
                else
                    $chart_data['poor']++;
            }
        }

        echo json_encode([
            'success' => true,
            'data' => $result['success'] ? $result['data'] : [],
            'chart_data' => $chart_data
        ]);
        break;

    case 'get_performance_details':
        $id = isset($_GET['id']) ? (int) $_GET['id'] : 0;

        $result = query($mysqli, "
            SELECT p.*, e.first_name, e.last_name,
                   CONCAT(m.first_name, ' ', m.last_name) as evaluator_name
            FROM performance_reviews p
            JOIN employees e ON p.employee_id = e.id
            LEFT JOIN employees m ON p.evaluator_id = m.id
            WHERE p.id = ? AND e.department_id = ?
        ", [$id, $manager_department_id]);

        if ($result['success'] && !empty($result['data'])) {
            echo json_encode(['success' => true, 'data' => $result['data'][0]]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Review not found']);
        }
        break;

    case 'add_edit_performance':
        // requireCSRFToken();
        $id = isset($_POST['id']) ? (int) $_POST['id'] : 0;
        $employee_id = $_POST['employee_id'] ?? 0;
        $period = $_POST['period'] ?? '';
        $score = $_POST['score'] ?? 0;
        $remarks = $_POST['remarks'] ?? '';

        // Validate employee belongs to manager's department
        $emp_check = query($mysqli, "SELECT id FROM employees WHERE id = ? AND department_id = ?", [$employee_id, $manager_department_id]);
        if (!$emp_check['success'] || empty($emp_check['data'])) {
            echo json_encode(['success' => false, 'message' => 'Invalid employee']);
            exit;
        }

        // Get evaluator ID (manager's employee ID)
        $evaluator_res = query($mysqli, "SELECT id FROM employees WHERE user_id = ?", [$user_id]);
        $evaluator_id = $evaluator_res['data'][0]['id'];

        if ($id > 0) {
            // Update
            $res = query($mysqli, "
                UPDATE performance_reviews 
                SET employee_id = ?, period = ?, score = ?, remarks = ?, updated_at = NOW()
                WHERE id = ?
            ", [$employee_id, $period, $score, $remarks, $id]);
        } else {
            // Insert
            $res = query($mysqli, "
                INSERT INTO performance_reviews (employee_id, evaluator_id, period, score, remarks, created_at)
                VALUES (?, ?, ?, ?, ?, NOW())
            ", [$employee_id, $evaluator_id, $period, $score, $remarks]);
        }

        if ($res['success']) {
            echo json_encode(['success' => true, 'message' => 'Performance review saved successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Database error']);
        }
        break;

    case 'delete_performance':
        // requireCSRFToken();
        $id = isset($_POST['id']) ? (int) $_POST['id'] : 0;

        // Validate ownership via department join
        $check = query($mysqli, "
            SELECT p.id FROM performance_reviews p
            JOIN employees e ON p.employee_id = e.id
            WHERE p.id = ? AND e.department_id = ?
        ", [$id, $manager_department_id]);

        if ($check['success'] && !empty($check['data'])) {
            $del = query($mysqli, "DELETE FROM performance_reviews WHERE id = ?", [$id]);
            if ($del['success']) {
                echo json_encode(['success' => true, 'message' => 'Review deleted successfully']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Delete failed']);
            }
        } else {
            echo json_encode(['success' => false, 'message' => 'Review not found or unauthorized']);
        }
        break;

    default:
        echo json_encode(['success' => false, 'message' => 'Invalid action']);
        break;
}
?>