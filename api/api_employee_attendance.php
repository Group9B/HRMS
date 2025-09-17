<?php
header('Content-Type: application/json');
require_once '../config/db.php';
require_once '../includes/functions.php';

$response = ['success' => false, 'message' => 'An unknown error occurred.'];

if (!isLoggedIn() || $_SESSION['role_id'] !== 4) {
    $response['message'] = 'Unauthorized access.';
    echo json_encode($response);
    exit();
}

$user_id = $_SESSION['user_id'];
$today = date('Y-m-d');

// Get employee_id for the current user
$employee_result = query($mysqli, "SELECT id FROM employees WHERE user_id = ?", [$user_id]);
if (!$employee_result['success'] || empty($employee_result['data'])) {
    $response['message'] = 'Employee profile not found.';
    echo json_encode($response);
    exit();
}
$employee_id = $employee_result['data'][0]['id'];

$action = $_REQUEST['action'] ?? '';

switch ($action) {
    case 'get_today_status':
        $sql = "
            SELECT 
                a.check_in, a.check_out, a.status,
                s.start_time, s.end_time
            FROM employees e
            LEFT JOIN shifts s ON e.shift_id = s.id
            LEFT JOIN attendance a ON e.id = a.employee_id AND a.date = ?
            WHERE e.id = ?
        ";
        $result = query($mysqli, $sql, [$today, $employee_id]);

        if ($result['success']) {
            $data = $result['data'][0] ?? null;
            if ($data) {
                // Add status flags based on shift timings
                $data['check_in_status'] = '';
                $data['check_out_status'] = '';
                $data['duration_status'] = '';

                if ($data['start_time'] && $data['check_in'] > $data['start_time']) {
                    $data['check_in_status'] = 'Late';
                }
                if ($data['end_time'] && $data['check_out'] && $data['check_out'] < $data['end_time']) {
                    $data['check_out_status'] = 'Early Out';
                }

                // Half-day validation (assuming half-day is 4 hours)
                if ($data['status'] === 'half-day' && $data['check_in'] && $data['check_out']) {
                    $check_in_time = new DateTime($data['check_in']);
                    $check_out_time = new DateTime($data['check_out']);
                    $interval = $check_in_time->diff($check_out_time);
                    $hours_worked = $interval->h + ($interval->i / 60);
                    if ($hours_worked < 4) {
                        $data['duration_status'] = 'Incomplete Half Day';
                    }
                }
            }
            $response = ['success' => true, 'data' => $data];
        } else {
            $response['message'] = 'Could not retrieve attendance status.';
        }
        break;

    case 'check_in':
    case 'check_out':
        $now = date('H:i:s');
        $field = ($action === 'check_in') ? 'check_in' : 'check_out';

        // Check if a record for today already exists
        $existing_record = query($mysqli, "SELECT id, check_in FROM attendance WHERE employee_id = ? AND date = ?", [$employee_id, $today]);

        if ($existing_record['success'] && !empty($existing_record['data'])) {
            // Record exists, so UPDATE it
            $record_id = $existing_record['data'][0]['id'];
            if ($action === 'check_in' && !is_null($existing_record['data'][0]['check_in'])) {
                $response['message'] = 'You have already checked in today.';
                break;
            }
            $sql = "UPDATE attendance SET $field = ? WHERE id = ?";
            $params = [$now, $record_id];
        } else {
            // No record, so INSERT a new one
            if ($action === 'check_out') {
                $response['message'] = 'You must check in before you can check out.';
                break;
            }
            $sql = "INSERT INTO attendance (employee_id, date, $field, status) VALUES (?, ?, ?, 'present')";
            $params = [$employee_id, $today, $now];
        }

        $result = query($mysqli, $sql, $params);
        if ($result['success']) {
            $message = ($action === 'check_in') ? 'Successfully checked in!' : 'Successfully checked out!';
            $response = ['success' => true, 'message' => $message];
        } else {
            $response['message'] = 'Database error. Could not record time.';
        }
        break;

    default:
        $response['message'] = 'Invalid action specified.';
        break;
}

echo json_encode($response);
exit();
?>