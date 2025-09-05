<?php
header('Content-Type: application/json');
require_once '../config/db.php';
require_once '../includes/functions.php';

$response = ['success' => false, 'message' => 'An unknown error occurred.'];

// Security: Only logged-in employees can access this
if (!isLoggedIn() || $_SESSION['role_id'] !== 4) {
    $response['message'] = 'Unauthorized access.';
    echo json_encode($response);
    exit();
}

// Find the employee_id from the user_id in the session
$employee_id_result = query($mysqli, "SELECT id FROM employees WHERE user_id = ?", [$_SESSION['user_id']]);
if (!$employee_id_result['success'] || empty($employee_id_result['data'])) {
    $response['message'] = 'No associated employee record found for this user.';
    echo json_encode($response);
    exit();
}
$employee_id = $employee_id_result['data'][0]['id'];
$today = date('Y-m-d');
$now = date('H:i:s');

$action = $_REQUEST['action'] ?? '';

switch ($action) {
    case 'get_today_status':
        $result = query($mysqli, "SELECT check_in, check_out FROM attendance WHERE employee_id = ? AND date = ?", [$employee_id, $today]);
        if ($result['success']) {
            $response = ['success' => true, 'data' => $result['data'][0] ?? null];
        } else {
            $response['message'] = 'Could not fetch attendance status.';
        }
        break;

    case 'check_in':
        $today_record = query($mysqli, "SELECT id, check_in FROM attendance WHERE employee_id = ? AND date = ?", [$employee_id, $today])['data'][0] ?? null;
        if ($today_record && $today_record['check_in']) {
            $response['message'] = 'You have already checked in today.';
            break;
        }
        $result = query($mysqli, "INSERT INTO attendance (employee_id, date, check_in, status) VALUES (?, ?, ?, 'present')", [$employee_id, $today, $now]);
        if ($result['success']) {
            $response = ['success' => true, 'message' => 'Checked in successfully!', 'check_in_time' => date('h:i A', strtotime($now))];
        } else {
            $response['message'] = 'Failed to check in.';
        }
        break;

    case 'check_out':
        $today_record = query($mysqli, "SELECT id, check_in, check_out FROM attendance WHERE employee_id = ? AND date = ?", [$employee_id, $today])['data'][0] ?? null;
        if (!$today_record || !$today_record['check_in']) {
            $response['message'] = 'You have not checked in yet today.';
            break;
        }
        if ($today_record['check_out']) {
            $response['message'] = 'You have already checked out today.';
            break;
        }
        $result = query($mysqli, "UPDATE attendance SET check_out = ? WHERE id = ?", [$now, $today_record['id']]);
        if ($result['success']) {
            $response = ['success' => true, 'message' => 'Checked out successfully!', 'check_out_time' => date('h:i A', strtotime($now))];
        } else {
            $response['message'] = 'Failed to check out.';
        }
        break;

    default:
        $response['message'] = 'Invalid action specified.';
        break;
}

echo json_encode($response);
exit();
?>
