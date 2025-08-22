<?php
header('Content-Type: application/json');
require_once '../config/db.php';
require_once '../includes/functions.php';

$response = ['success' => false, 'message' => 'An unknown error occurred.'];

// Security Check: Must be a logged-in Super Admin
if (!isLoggedIn() || !isset($_SESSION['role_id']) || $_SESSION['role_id'] !== 1) {
    $response['message'] = 'Unauthorized access.';
    echo json_encode($response);
    exit();
}

$action = $_REQUEST['action'] ?? '';
$user_id = $_SESSION['user_id'];

switch ($action) {
    case 'get_storage_usage':
        // Define the path to your main uploads directory
        $upload_dir = realpath(__DIR__ . '/../uploads');
        $total_space_gb = 5; // 5 GB total allocation
        $total_space_bytes = $total_space_gb * 1024 * 1024 * 1024;

        // Function to calculate directory size
        function getDirectorySize($path)
        {
            $bytes = 0;
            if (is_dir($path)) {
                $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($path, FilesystemIterator::SKIP_DOTS));
                foreach ($iterator as $file) {
                    $bytes += $file->getSize();
                }
            }
            return $bytes;
        }

        $used_space_bytes = $upload_dir ? getDirectorySize($upload_dir) : 0;
        $free_space_bytes = $total_space_bytes - $used_space_bytes;

        $response = [
            'success' => true,
            'data' => [
                'used_gb' => round($used_space_bytes / (1024 * 1024 * 1024), 2),
                'free_gb' => round($free_space_bytes / (1024 * 1024 * 1024), 2),
                'total_gb' => $total_space_gb
            ]
        ];
        break;

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