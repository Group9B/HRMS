<?php
require_once '../config/db.php';
require_once '../includes/functions.php';
require_once '../includes/notification_helpers.php';

header('Content-Type: application/json');

// Allow both Managers (6) and Employees (4) - employees can be team leaders
if (!isLoggedIn() || !in_array($_SESSION['role_id'], [6, 4])) {
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

        $where_msg = "tm.assigned_by = ? AND p.period = ?";
        $params = [$user_id, $period_filter];

        if (!empty($employee_filter)) {
            $where_msg .= " AND p.employee_id = ?";
            $params[] = $employee_filter;
        }

        $query = "
            SELECT p.*, e.first_name, e.last_name, 
                   CONCAT(m.first_name, ' ', m.last_name) as evaluator_name
            FROM performance p
            JOIN employees e ON p.employee_id = e.id
            LEFT JOIN employees m ON p.evaluator_id = m.id
            JOIN team_members tm ON e.id = tm.employee_id
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
            FROM performance p
            JOIN employees e ON p.employee_id = e.id
            LEFT JOIN employees m ON p.evaluator_id = m.id
            JOIN team_members tm ON e.id = tm.employee_id
            WHERE p.id = ? AND tm.assigned_by = ?
        ", [$id, $user_id]);

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

        if ($period > date('Y-m')) {
            echo json_encode(['success' => false, 'message' => 'Cannot add review for future period']);
            exit;
        }

        // Validate employee belongs to manager's team OR team leader's team
        if ($_SESSION['role_id'] == 6) {
            // Manager validation
            $emp_check = query($mysqli, "
                SELECT e.id FROM employees e
                JOIN team_members tm ON e.id = tm.employee_id
                WHERE e.id = ? AND tm.assigned_by = ?
            ", [$employee_id, $user_id]);
        } else {
            // Employee (Team Leader) validation
            $current_emp = query($mysqli, "SELECT id FROM employees WHERE user_id = ?", [$user_id]);
            $current_employee_id = $current_emp['success'] && !empty($current_emp['data']) ? $current_emp['data'][0]['id'] : 0;

            // Check if current user is a team leader and target employee is in their team
            $emp_check = query($mysqli, "
                SELECT e.id 
                FROM employees e
                JOIN team_members tm1 ON e.id = tm1.employee_id
                JOIN team_members tm2 ON tm1.team_id = tm2.team_id
                WHERE e.id = ? 
                AND tm2.employee_id = ?
                AND (tm2.role_in_team LIKE '%leader%' OR tm2.role_in_team LIKE '%lead%')
                AND e.id != ?
            ", [$employee_id, $current_employee_id, $current_employee_id]); // Prevent self-review
        }

        if (!$emp_check['success'] || empty($emp_check['data'])) {
            echo json_encode(['success' => false, 'message' => 'Invalid employee or insufficient permissions']);
            exit;
        }

        // Get evaluator ID (manager's User ID, as per FK constraint)
        $evaluator_id = $user_id;

        if ($id > 0) {
            // Update
            $res = query($mysqli, "
                UPDATE performance 
                SET employee_id = ?, period = ?, score = ?, remarks = ?
                WHERE id = ?
            ", [$employee_id, $period, $score, $remarks, $id]);
        } else {
            // Insert
            $res = query($mysqli, "
                INSERT INTO performance (employee_id, evaluator_id, period, score, remarks, created_at)
                VALUES (?, ?, ?, ?, ?, NOW())
            ", [$employee_id, $evaluator_id, $period, $score, $remarks]);
        }

        if ($res['success']) {
            // Notify the employee about their performance review
            if ($id == 0) { // Only for new reviews, not updates
                $emp_user_info = query($mysqli, "SELECT user_id FROM employees WHERE id = ?", [$employee_id])['data'][0] ?? null;
                if ($emp_user_info) {
                    $evaluator_info = query($mysqli, "SELECT first_name, last_name FROM employees WHERE user_id = ?", [$user_id])['data'][0] ?? null;
                    $evaluator_name = $evaluator_info ? "{$evaluator_info['first_name']} {$evaluator_info['last_name']}" : 'Your manager';
                    createNotificationIfEnabled(
                        $mysqli,
                        $emp_user_info['user_id'],
                        'performance',
                        'Performance Review Added',
                        "{$evaluator_name} has added a performance review for {$period}. Score: {$score}%",
                        $res['insert_id'] ?? $id,
                        'performance'
                    );
                }
            }
            echo json_encode(['success' => true, 'message' => 'Performance review saved successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Database error: ' . $res['error']]);
        }
        break;

    case 'delete_performance':
        // requireCSRFToken();
        $id = isset($_POST['id']) ? (int) $_POST['id'] : 0;

        // Validate ownership via department join
        $check = query($mysqli, "
            SELECT p.id FROM performance p
            JOIN employees e ON p.employee_id = e.id
            JOIN team_members tm ON e.id = tm.employee_id
            WHERE p.id = ? AND tm.assigned_by = ?
        ", [$id, $user_id]);

        if ($check['success'] && !empty($check['data'])) {
            $del = query($mysqli, "DELETE FROM performance WHERE id = ?", [$id]);
            if ($del['success']) {
                echo json_encode(['success' => true, 'message' => 'Review deleted successfully']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Delete failed']);
            }
        } else {
            echo json_encode(['success' => false, 'message' => 'Review not found or unauthorized']);
        }
        break;

    case 'get_team_performance_data':
        $team_filter = $_GET['team_id'] ?? '';
        $period_filter = $_GET['period'] ?? date('Y-m');

        $where_sql = "t.created_by = ? AND tp.period = ?";
        $params = [$user_id, $period_filter];

        if (!empty($team_filter)) {
            $where_sql .= " AND tp.team_id = ?";
            $params[] = $team_filter;
        }

        $query = "
            SELECT tp.*, t.name as team_name, t.description as team_description,
                   COUNT(tm.id) as member_count,
                   CONCAT(e.first_name, ' ', e.last_name) as evaluator_name
            FROM team_performance tp
            JOIN teams t ON tp.team_id = t.id
            LEFT JOIN team_members tm ON t.id = tm.team_id
            LEFT JOIN users u ON tp.evaluated_by = u.id
            LEFT JOIN employees e ON u.id = e.user_id
            WHERE $where_sql
            GROUP BY tp.id
            ORDER BY tp.created_at DESC
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

    case 'get_team_performance_details':
        $id = isset($_GET['id']) ? (int) $_GET['id'] : 0;

        $result = query($mysqli, "
            SELECT tp.*, t.name as team_name, t.description as team_description,
                   COUNT(tm.id) as member_count,
                   CONCAT(e.first_name, ' ', e.last_name) as evaluator_name
            FROM team_performance tp
            JOIN teams t ON tp.team_id = t.id
            LEFT JOIN team_members tm ON t.id = tm.team_id
            LEFT JOIN users u ON tp.evaluated_by = u.id
            LEFT JOIN employees e ON u.id = e.user_id
            WHERE tp.id = ? AND t.created_by = ?
            GROUP BY tp.id
        ", [$id, $user_id]);

        if ($result['success'] && !empty($result['data'])) {
            echo json_encode(['success' => true, 'data' => $result['data'][0]]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Team review not found']);
        }
        break;

    case 'add_edit_team_performance':
        $id = isset($_POST['id']) ? (int) $_POST['id'] : 0;
        $team_id = $_POST['team_id'] ?? 0;
        $period = $_POST['period'] ?? '';
        $score = $_POST['score'] ?? 0;
        $collaboration_score = $_POST['collaboration_score'] ?? null;
        $achievements = $_POST['achievements'] ?? '';
        $challenges = $_POST['challenges'] ?? '';
        $remarks = $_POST['remarks'] ?? '';

        if ($period > date('Y-m')) {
            echo json_encode(['success' => false, 'message' => 'Cannot add review for future period']);
            exit;
        }

        // Validate team belongs to manager
        $team_check = query($mysqli, "
            SELECT id FROM teams WHERE id = ? AND created_by = ?
        ", [$team_id, $user_id]);

        if (!$team_check['success'] || empty($team_check['data'])) {
            echo json_encode(['success' => false, 'message' => 'Invalid team']);
            exit;
        }

        if ($id > 0) {
            // Update
            $res = query($mysqli, "
                UPDATE team_performance 
                SET team_id = ?, period = ?, score = ?, collaboration_score = ?, 
                    achievements = ?, challenges = ?, remarks = ?
                WHERE id = ?
            ", [$team_id, $period, $score, $collaboration_score, $achievements, $challenges, $remarks, $id]);
        } else {
            // Insert
            $res = query($mysqli, "
                INSERT INTO team_performance 
                (team_id, period, score, collaboration_score, achievements, challenges, remarks, evaluated_by, created_at)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())
            ", [$team_id, $period, $score, $collaboration_score, $achievements, $challenges, $remarks, $user_id]);
        }

        if ($res['success']) {
            echo json_encode(['success' => true, 'message' => 'Team performance review saved successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Database error: ' . $res['error']]);
        }
        break;

    case 'delete_team_performance':
        $id = isset($_POST['id']) ? (int) $_POST['id'] : 0;

        // Validate ownership
        $check = query($mysqli, "
            SELECT tp.id FROM team_performance tp
            JOIN teams t ON tp.team_id = t.id
            WHERE tp.id = ? AND t.created_by = ?
        ", [$id, $user_id]);

        if ($check['success'] && !empty($check['data'])) {
            $del = query($mysqli, "DELETE FROM team_performance WHERE id = ?", [$id]);
            if ($del['success']) {
                echo json_encode(['success' => true, 'message' => 'Team review deleted successfully']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Delete failed']);
            }
        } else {
            echo json_encode(['success' => false, 'message' => 'Team review not found or unauthorized']);
        }
        break;

    default:
        echo json_encode(['success' => false, 'message' => 'Invalid action']);
        break;
}
?>