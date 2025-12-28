<?php
header('Content-Type: application/json');
require_once '../config/db.php';
require_once '../includes/functions.php';

$response = ['success' => false, 'message' => 'An unknown error occurred.'];

// Allow roles: 4 (Employee), 6 (Manager), 3 (HR Manager), 2 (Company Admin)
if (!isLoggedIn() || !in_array($_SESSION['role_id'], [2, 3, 4, 6])) {
    $response['message'] = 'Unauthorized access.';
    echo json_encode($response);
    exit();
}

$user_id = $_SESSION['user_id'];
$role_id = $_SESSION['role_id'];
$company_id = $_SESSION['company_id'];
$today = date('Y-m-d');

// Get employee_id for the current user or from request
$employee_id = null;
if (isset($_REQUEST['employee_id']) && (int) $_REQUEST['employee_id'] > 0) {
    $employee_id = (int) $_REQUEST['employee_id'];
    // Verify access: only HR/Admin can view others, Managers can see their team
    if ($role_id == 4) {
        // Employees can only view themselves
        $employee_result = query($mysqli, "SELECT id FROM employees WHERE user_id = ?", [$user_id]);
        if ($employee_result['success'] && !empty($employee_result['data'])) {
            if ($employee_result['data'][0]['id'] != $employee_id) {
                $response['message'] = 'You can only view your own attendance.';
                echo json_encode($response);
                exit();
            }
        }
    }
} else {
    // Get the employee_id for the current logged-in user
    $employee_result = query($mysqli, "SELECT id FROM employees WHERE user_id = ?", [$user_id]);
    if (!$employee_result['success'] || empty($employee_result['data'])) {
        $response['message'] = 'Employee profile not found.';
        echo json_encode($response);
        exit();
    }
    $employee_id = $employee_result['data'][0]['id'];
}

$action = $_REQUEST['action'] ?? '';

switch ($action) {
    case 'get_today_status':
        $sql = "
            SELECT 
                a.check_in, a.check_out, a.status,
                s.start_time, s.end_time,
                e.first_name, e.last_name
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
                $data['work_hours_decimal'] = 0;

                if ($data['start_time'] && $data['check_in'] && $data['check_in'] > $data['start_time']) {
                    $data['check_in_status'] = 'Late';
                }

                if ($data['check_in'] && $data['check_out']) {
                    // Calculate work hours
                    $checkInTime = new DateTime($data['check_in']);
                    $checkOutTime = new DateTime($data['check_out']);
                    $interval = $checkInTime->diff($checkOutTime);
                    $hours_worked = $interval->h + ($interval->i / 60);
                    $data['work_hours_decimal'] = $hours_worked;

                    if ($data['end_time'] && $data['check_out'] < $data['end_time']) {
                        $data['check_out_status'] = 'Early Out';
                    }

                    // Check if it qualifies as half-day
                    // Assuming shift is 8 hours (28800 seconds) - mark half-day if < 4 hours worked
                    if ($hours_worked < 4) {
                        $data['duration_status'] = 'Half Day Worked';
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
        // FINAL PROPER APPROACH:
        // Server records the CLIENT'S LOCAL TIME (with timezone validation)
        // 1. Client sends: UTC timestamp + timezone offset
        // 2. Convert UTC to client's local time
        // 3. Validate: local time must match client's device time (within reason)
        // 4. Record the local HH:MM:SS that user actually submitted

        $clientTimestamp = isset($_GET['client_timestamp']) ? intval($_GET['client_timestamp']) : null;
        $clientTimezoneOffset = isset($_GET['tz_offset']) ? intval($_GET['tz_offset']) : null;

        $now = date('H:i:s'); // Fallback to server time

        // CRITICAL: Validate and convert client timestamp
        if ($clientTimestamp !== null && $clientTimezoneOffset !== null) {
            try {
                // Validation: Client timestamp must be within ±5 minutes of server's UTC time
                // This catches obvious device clock manipulation
                $serverTimestamp = time();
                $timeDiff = $serverTimestamp - $clientTimestamp;

                if ($timeDiff > 300) {
                    // Device time is 5+ minutes in the past - REJECT
                    $response['message'] = 'Your device time is too far in the past. Please sync your system clock.';
                    break;
                }

                if ($timeDiff < -30) {
                    // Device time is 30+ seconds in future - REJECT
                    $response['message'] = 'Your device time is in the future. Please sync your system clock.';
                    break;
                }

                // Validation passed!
                // Convert UTC timestamp to client's local timezone
                // getTimezoneOffset() returns: minutes BEHIND UTC (positive for west, negative for east)
                // UTC+5:30 = -330 (330 minutes ahead = -330 from UTC)
                // So to convert FROM UTC TO local: timestamp - (offset * 60)

                $localTimestamp = $clientTimestamp - ($clientTimezoneOffset * 60);
                $localDateTime = new DateTime("@$localTimestamp");
                $now = $localDateTime->format('H:i:s');

            } catch (Exception $e) {
                error_log("Error in time conversion: " . $e->getMessage());
                // Use server's local time as fallback
            }
        }

        $field = ($action === 'check_in') ? 'check_in' : 'check_out';

        // Check if a record for today already exists
        $existing_record = query($mysqli, "SELECT id, check_in, check_out FROM attendance WHERE employee_id = ? AND date = ?", [$employee_id, $today]);

        if ($existing_record['success'] && !empty($existing_record['data'])) {
            // Record exists
            $record_id = $existing_record['data'][0]['id'];
            $existing_checkin = $existing_record['data'][0]['check_in'];
            $existing_checkout = $existing_record['data'][0]['check_out'];

            if ($action === 'check_in' && !is_null($existing_checkin)) {
                $response['message'] = 'You have already clocked in today.';
                break;
            }

            if ($action === 'check_out') {
                if (is_null($existing_checkin)) {
                    $response['message'] = 'You must clock in before you can clock out.';
                    break;
                }

                if (!is_null($existing_checkout)) {
                    $response['message'] = 'You have already clocked out today.';
                    break;
                }

                // Calculate work hours to determine if it's half-day
                $checkInTime = new DateTime($existing_checkin);
                $checkOutTime = new DateTime($now);
                $interval = $checkInTime->diff($checkOutTime);
                $hours_worked = $interval->h + ($interval->i / 60);

                // Get shift info
                $shift_query = query(
                    $mysqli,
                    "SELECT start_time, end_time FROM shifts 
                     WHERE id = (SELECT shift_id FROM employees WHERE id = ?)",
                    [$employee_id]
                );

                $shift_start = '09:00';
                $shift_end = '17:00';
                if ($shift_query['success'] && !empty($shift_query['data'])) {
                    $shift_start = $shift_query['data'][0]['start_time'];
                    $shift_end = $shift_query['data'][0]['end_time'];
                }

                // Calculate expected shift duration in hours
                $shiftStartTime = DateTime::createFromFormat('H:i:s', $shift_start);
                $shiftEndTime = DateTime::createFromFormat('H:i:s', $shift_end);
                $shiftInterval = $shiftStartTime->diff($shiftEndTime);
                $expected_hours = $shiftInterval->h + ($shiftInterval->i / 60);

                // Mark as half-day if worked less than 50% of expected shift
                $work_percentage = ($hours_worked / $expected_hours) * 100;
                $status = ($work_percentage < 50) ? 'half-day' : 'present';

                $sql = "UPDATE attendance SET $field = ?, status = ? WHERE id = ?";
                $params = [$now, $status, $record_id];
            } else {
                $sql = "UPDATE attendance SET $field = ? WHERE id = ?";
                $params = [$now, $record_id];
            }
        } else {
            // No record, so INSERT a new one
            if ($action === 'check_out') {
                $response['message'] = 'You must clock in before you can clock out.';
                break;
            }
            $sql = "INSERT INTO attendance (employee_id, date, $field, status) VALUES (?, ?, ?, 'present')";
            $params = [$employee_id, $today, $now];
        }

        $result = query($mysqli, $sql, $params);
        if ($result['success']) {
            $message = ($action === 'check_in')
                ? 'Successfully clocked in!'
                : 'Successfully clocked out! Attendance has been recorded.';
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