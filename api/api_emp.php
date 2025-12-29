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
        $year = date('Y');

        // 1. Leave Balance - Calculate from leave_policies and actual approved leaves
        // Get employee's company_id directly from users table (via user_id)
        $emp_company_result = query(
            $mysqli,
            "SELECT u.company_id 
            FROM employees e
            JOIN users u ON e.user_id = u.id
            WHERE e.id = ?",
            [$employee_id]
        );
        $company_id = $emp_company_result['data'][0]['company_id'] ?? null;

        if ($company_id) {
            // Step 1: Get total allocated days from leave policies
            $policies_result = query(
                $mysqli,
                "SELECT SUM(days_per_year) as total_allocated FROM leave_policies WHERE company_id = ?",
                [$company_id]
            );
            $total_allocated = 0;
            if ($policies_result['success'] && !empty($policies_result['data'])) {
                $total_allocated = (int) ($policies_result['data'][0]['total_allocated'] ?? 0);
            }

            // Step 2: Get days taken from approved leaves in current year
            $leaves_result = query(
                $mysqli,
                "SELECT COALESCE(SUM(DATEDIFF(end_date, start_date) + 1), 0) as days_taken 
                FROM leaves 
                WHERE employee_id = ? AND status = 'approved' AND YEAR(start_date) = ?",
                [$employee_id, $year]
            );
            $days_taken = 0;
            if ($leaves_result['success'] && !empty($leaves_result['data'])) {
                $days_taken = (int) ($leaves_result['data'][0]['days_taken'] ?? 0);
            }

            // Step 3: Calculate balance
            $leave_balance = max(0, $total_allocated - $days_taken);
        } else {
            $leave_balance = 0;
        }

        // 2. Pending Leaves
        $pending_leaves_result = query(
            $mysqli,
            "SELECT COUNT(*) as count FROM leaves WHERE employee_id = ? AND status = 'pending'",
            [$employee_id]
        );
        $pending_leaves = $pending_leaves_result['data'][0]['count'] ?? 0;

        // 3. Pending Tasks (not completed or cancelled)
        $pending_tasks_result = query(
            $mysqli,
            "SELECT COUNT(*) as count FROM tasks WHERE employee_id = ? AND status IN ('pending', 'in_progress')",
            [$employee_id]
        );
        $pending_tasks = $pending_tasks_result['data'][0]['count'] ?? 0;

        // 4. Completed Tasks
        $completed_tasks_result = query(
            $mysqli,
            "SELECT COUNT(*) as count FROM tasks WHERE employee_id = ? AND status = 'completed'",
            [$employee_id]
        );
        $completed_tasks = $completed_tasks_result['data'][0]['count'] ?? 0;

        // Consolidate all stats into a single response
        $response = [
            'success' => true,
            'data' => [
                'leave_balance' => $leave_balance,
                'pending_leaves' => $pending_leaves,
                'pending_tasks' => $pending_tasks,
                'completed_tasks' => $completed_tasks,
            ]
        ];
        break;

    case 'get_profile':
        // Get complete profile data for the logged-in employee
        $profile_query = "SELECT e.*, u.username, u.email, u.id as user_id, d.name AS department_name, g.name AS designation_name, s.name AS shift_name, s.start_time, s.end_time
                         FROM employees e
                         JOIN users u ON e.user_id = u.id
                         LEFT JOIN departments d ON e.department_id = d.id
                         LEFT JOIN designations g ON e.designation_id = g.id
                         LEFT JOIN shifts s ON e.shift_id = s.id
                         WHERE e.id = ?";
        $profile_result = query($mysqli, $profile_query, [$employee_id]);

        if ($profile_result['success'] && !empty($profile_result['data'])) {
            $response = ['success' => true, 'data' => $profile_result['data'][0]];
        } else {
            $response['message'] = 'Failed to fetch profile data.';
        }
        break;

    default:
        $response['message'] = 'Invalid action specified for employee dashboard.';
        break;
}

echo json_encode($response);
exit();
?>