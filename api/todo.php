<?php
header('Content-Type: application/json');
require_once '../config/db.php';
require_once '../includes/functions.php';

$response = ['success' => false, 'message' => 'An unknown error occurred.'];

// Security Check: Any logged-in user can manage their own to-do list.
if (!isLoggedIn()) {
    $response['message'] = 'Unauthorized access.';
    echo json_encode($response);
    exit();
}

$action = $_REQUEST['action'] ?? '';
$user_id = $_SESSION['user_id'];

switch ($action) {
    case 'get_todos':
        $result = query($mysqli, "SELECT id, task, is_completed FROM todo_list WHERE user_id = ? ORDER BY created_at DESC", [$user_id]);
        if ($result['success']) {
            $response = ['success' => true, 'data' => $result['data']];
        }
        break;

    case 'add_todo':
        $task = $_POST['task'] ?? '';
        if (!empty($task)) {
            $result = query($mysqli, "INSERT INTO todo_list (user_id, task) VALUES (?, ?)", [$user_id, $task]);
            if ($result['success']) {
                $response = ['success' => true, 'message' => 'To-do item added!'];
            }
        }
        break;

    case 'update_todo_status':
        $task_id = isset($_POST['task_id']) ? (int) $_POST['task_id'] : 0;
        $is_completed = isset($_POST['is_completed']) ? (int) $_POST['is_completed'] : 0;
        $result = query($mysqli, "UPDATE todo_list SET is_completed = ? WHERE id = ? AND user_id = ?", [$is_completed, $task_id, $user_id]);
        if ($result['success']) {
            $response = ['success' => true, 'message' => 'To-do status updated!'];
        }
        break;

    case 'delete_todo':
        $task_id = isset($_POST['task_id']) ? (int) $_POST['task_id'] : 0;
        $result = query($mysqli, "DELETE FROM todo_list WHERE id = ? AND user_id = ?", [$task_id, $user_id]);
        if ($result['success']) {
            $response = ['success' => true, 'message' => 'To-do item deleted!'];
        }
        break;

    default:
        $response['message'] = 'Invalid action specified.';
        break;
}

echo json_encode($response);
exit();
?>