<?php
header('Content-Type: application/json');
require_once '../config/db.php';
require_once '../includes/functions.php';

$response = ['success' => false, 'message' => 'An unknown error occurred.'];

// Security Check: Must be a logged-in user
if (!isLoggedIn()) {
    $response['message'] = 'Unauthorized access.';
    echo json_encode($response);
    exit();
}

$action = $_REQUEST['action'] ?? '';
$user_id = $_SESSION['user_id'];

// Get the employee_id for the current user, as it's needed for all actions here.
$employee_result = query($mysqli, "SELECT id FROM employees WHERE user_id = ?", [$user_id]);
if (!$employee_result['success'] || empty($employee_result['data'])) {
    $response['message'] = 'An associated employee profile could not be found.';
    echo json_encode($response);
    exit();
}
$employee_id = $employee_result['data'][0]['id'];


switch ($action) {
    case 'get_assigned_tasks':
        $sql = "
            SELECT t.*, u.username as assigned_by_name
            FROM tasks t
            LEFT JOIN users u ON t.assigned_by = u.id
            WHERE t.employee_id = ?
            ORDER BY t.due_date ASC, t.created_at DESC
        ";
        $result = query($mysqli, $sql, [$employee_id]);
        if ($result['success']) {
            $response = ['success' => true, 'data' => $result['data']];
        } else {
            $response['message'] = 'Failed to fetch assigned tasks.';
        }
        break;

    case 'update_task_status':
        $task_id = (int) ($_POST['task_id'] ?? 0);
        $status = $_POST['status'] ?? '';
        $allowed_statuses = ['pending', 'in_progress', 'completed'];

        if ($task_id > 0 && in_array($status, $allowed_statuses)) {
            // Security check: ensure the task belongs to this employee before updating
            $sql = "UPDATE tasks SET status = ? WHERE id = ? AND employee_id = ?";
            $result = query($mysqli, $sql, [$status, $task_id, $employee_id]);

            if ($result['success'] && $result['affected_rows'] > 0) {
                $response = ['success' => true, 'message' => 'Task status updated!'];
            } else {
                $response['message'] = 'Failed to update task or permission denied.';
            }
        } else {
            $response['message'] = 'Invalid task ID or status provided.';
        }
        break;

    default:
        $response['message'] = 'Invalid action specified.';
        break;
}

echo json_encode($response);
exit();
?>