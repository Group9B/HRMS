<?php
// Set the content type to JSON for all responses
header('Content-Type: application/json');

require_once '../config/db.php';
require_once '../includes/functions.php';

$response = ['success' => false, 'message' => 'An unknown error occurred.'];

// Security Check: Must be a logged-in Employee (Role 4)
if (!isLoggedIn() || $_SESSION['role_id'] !== 4) {
    $response['message'] = 'Unauthorized access.';
    echo json_encode($response);
    exit();
}

$action = $_REQUEST['action'] ?? '';
$user_id = $_SESSION['user_id'];

// Get the corresponding employee_id once for all queries
$employee_id_result = query($mysqli, "SELECT id FROM employees WHERE user_id = ?", [$user_id]);
if (!$employee_id_result['success'] || empty($employee_id_result['data'])) {
    $response['message'] = 'Employee profile not found.';
    echo json_encode($response);
    exit();
}
$employee_id = $employee_id_result['data'][0]['id'];


switch ($action) {
    case 'get_stats':
        // 1. Leave Balance (Assuming 20 days per year as a default)
        // This calculates the sum of days for all approved leaves this year.
        $total_leave_allowance = 20;
        $year = date('Y');
        $approved_leaves_result = query(
            $mysqli,
            "SELECT SUM(DATEDIFF(end_date, start_date) + 1) as days_taken FROM leaves WHERE employee_id = ? AND status = 'approved' AND YEAR(start_date) = ?",
            [$employee_id, $year]
        );
        $days_taken = $approved_leaves_result['data'][0]['days_taken'] ?? 0;
        $leave_balance = $total_leave_allowance - $days_taken;

        // 2. Pending Leaves
        $pending_leaves_result = query($mysqli, "SELECT COUNT(*) as count FROM leaves WHERE employee_id = ? AND status = 'pending'", [$employee_id]);
        $pending_leaves = $pending_leaves_result['data'][0]['count'] ?? 0;

        // 3. Total Tasks
        $total_tasks_result = query($mysqli, "SELECT COUNT(*) as count FROM tasks WHERE employee_id = ?", [$employee_id]);
        $total_tasks = $total_tasks_result['data'][0]['count'] ?? 0;

        // 4. Completed Tasks
        $completed_tasks_result = query($mysqli, "SELECT COUNT(*) as count FROM tasks WHERE employee_id = ? AND status = 'completed'", [$employee_id]);
        $completed_tasks = $completed_tasks_result['data'][0]['count'] ?? 0;

        // Consolidate all stats into a single response
        $response = [
            'success' => true,
            'data' => [
                'leave_balance' => $leave_balance,
                'pending_leaves' => $pending_leaves,
                'total_tasks' => $total_tasks,
                'completed_tasks' => $completed_tasks,
            ]
        ];
        break;

    default:
        $response['message'] = 'Invalid action specified for employee dashboard.';
        break;
}

echo json_encode($response);
exit();
?>