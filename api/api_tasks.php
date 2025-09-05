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
    case 'get_assigned_tasks':
        // Get employee ID for the current user
        $employee_result = query($mysqli, "SELECT id FROM employees WHERE user_id = ?", [$user_id]);
        if (!$employee_result['success'] || empty($employee_result['data'])) {
            $response['message'] = 'Employee profile not found.';
            echo json_encode($response);
            exit();
        }
        $employee_id = $employee_result['data'][0]['id'];

        // Get assigned tasks
        $sql = "
            SELECT t.*, e.first_name as assigned_by_name
            FROM tasks t
            LEFT JOIN employees e ON t.assigned_by = e.user_id
            WHERE t.employee_id = ?
            ORDER BY t.due_date ASC
        ";
        $result = query($mysqli, $sql, [$employee_id]);

        if ($result['success']) {
            $response = ['success' => true, 'data' => $result['data']];
        } else {
            $response['message'] = 'Failed to fetch assigned tasks.';
        }
        break;

    case 'get_personal_todos':
        // Get personal todo items
        $sql = "
            SELECT * FROM todo_list 
            WHERE user_id = ? 
            ORDER BY created_at DESC
        ";
        $result = query($mysqli, $sql, [$user_id]);

        if ($result['success']) {
            $response = ['success' => true, 'data' => $result['data']];
        } else {
            $response['message'] = 'Failed to fetch personal tasks.';
        }
        break;

    case 'add_personal_todo':
        $task = $_POST['task'] ?? '';
        
        if (empty($task)) {
            $response['message'] = 'Task description is required.';
            echo json_encode($response);
            exit();
        }

        $sql = "INSERT INTO todo_list (user_id, task) VALUES (?, ?)";
        $result = query($mysqli, $sql, [$user_id, $task]);

        if ($result['success']) {
            $response = ['success' => true, 'message' => 'Personal task added successfully.'];
        } else {
            $response['message'] = 'Failed to add personal task.';
        }
        break;

    case 'complete_personal_todo':
        $todo_id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
        
        if ($todo_id <= 0) {
            $response['message'] = 'Invalid task ID.';
            echo json_encode($response);
            exit();
        }

        $sql = "UPDATE todo_list SET is_completed = 1 WHERE id = ? AND user_id = ?";
        $result = query($mysqli, $sql, [$todo_id, $user_id]);

        if ($result['success']) {
            $response = ['success' => true, 'message' => 'Personal task marked as completed.'];
        } else {
            $response['message'] = 'Failed to update personal task.';
        }
        break;

    case 'delete_personal_todo':
        $todo_id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
        
        if ($todo_id <= 0) {
            $response['message'] = 'Invalid task ID.';
            echo json_encode($response);
            exit();
        }

        $sql = "DELETE FROM todo_list WHERE id = ? AND user_id = ?";
        $result = query($mysqli, $sql, [$todo_id, $user_id]);

        if ($result['success']) {
            $response = ['success' => true, 'message' => 'Personal task deleted successfully.'];
        } else {
            $response['message'] = 'Failed to delete personal task.';
        }
        break;

    case 'update_task_status':
        $task_id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
        $status = $_POST['status'] ?? '';
        
        if ($task_id <= 0 || empty($status)) {
            $response['message'] = 'Invalid task ID or status.';
            echo json_encode($response);
            exit();
        }

        // Get employee ID for the current user
        $employee_result = query($mysqli, "SELECT id FROM employees WHERE user_id = ?", [$user_id]);
        if (!$employee_result['success'] || empty($employee_result['data'])) {
            $response['message'] = 'Employee profile not found.';
            echo json_encode($response);
            exit();
        }
        $employee_id = $employee_result['data'][0]['id'];

        $sql = "UPDATE tasks SET status = ? WHERE id = ? AND employee_id = ?";
        $result = query($mysqli, $sql, [$status, $task_id, $employee_id]);

        if ($result['success']) {
            $response = ['success' => true, 'message' => 'Task status updated successfully.'];
        } else {
            $response['message'] = 'Failed to update task status.';
        }
        break;

    default:
        $response['message'] = 'Invalid action specified.';
        break;
}

echo json_encode($response);
?>