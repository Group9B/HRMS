<?php
header('Content-Type: application/json');
require_once '../config/db.php';
require_once '../includes/functions.php';

// Use India Standard Time across all attendance operations
date_default_timezone_set('Asia/Kolkata');
$tz = new DateTimeZone('Asia/Kolkata');

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
$today = (new DateTime('now', $tz))->format('Y-m-d');

// Determine if today is eligible for attendance based on holidays, weekends, leave, and Saturday policy
function getAttendanceDayContext($mysqli, $company_id, $employee_id, DateTime $todayDate)
{
    $todayStr = $todayDate->format('Y-m-d');

    // Company holiday check
    $holiday = query(
        $mysqli,
        "SELECT holiday_name FROM holidays WHERE company_id = ? AND holiday_date = ?",
        [$company_id, $todayStr]
    );
    if ($holiday['success'] && !empty($holiday['data'])) {
        $name = $holiday['data'][0]['holiday_name'] ?? 'Company Holiday';
        return [
            'status' => 'holiday',
            'block' => true,
            'message' => "Today is a company holiday ({$name}). Attendance is locked.",
        ];
    }

    $weekday = (int) $todayDate->format('w'); // 0=Sun, 6=Sat

    if ($weekday === 0) {
        return [
            'status' => 'holiday',
            'block' => true,
            'message' => 'Today is Sunday. Attendance is not allowed.',
        ];
    }

    if ($weekday === 6) {
        $policyResult = query(
            $mysqli,
            "SELECT saturday_policy FROM company_holiday_settings WHERE company_id = ?",
            [$company_id]
        );
        $policy = ($policyResult['success'] && !empty($policyResult['data']))
            ? $policyResult['data'][0]['saturday_policy']
            : 'none';

        $dayOfMonth = (int) $todayDate->format('j');
        $weekNumber = (int) ceil($dayOfMonth / 7); // 1-based week within month
        $isOffSaturday = false;

        if ($policy === 'all') {
            $isOffSaturday = true;
        } elseif ($policy === '1st_3rd' && in_array($weekNumber, [1, 3], true)) {
            $isOffSaturday = true;
        } elseif ($policy === '2nd_4th' && in_array($weekNumber, [2, 4], true)) {
            $isOffSaturday = true;
        }

        if ($isOffSaturday) {
            return [
                'status' => 'holiday',
                'block' => true,
                'message' => 'Today is an off Saturday per company policy. Attendance is locked.',
            ];
        }
    }

    // Approved leave check
    $leave = query(
        $mysqli,
        "SELECT id FROM leaves WHERE employee_id = ? AND status = 'approved' AND start_date <= ? AND end_date >= ?",
        [$employee_id, $todayStr, $todayStr]
    );
    if ($leave['success'] && !empty($leave['data'])) {
        return [
            'status' => 'onleave',
            'block' => true,
            'message' => 'You are on approved leave today. Attendance is locked.',
        ];
    }

    return [
        'status' => 'working',
        'block' => false,
        'message' => '',
    ];
}

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
        $todayDate = new DateTime('now', $tz);
        $dayContext = getAttendanceDayContext($mysqli, $company_id, $employee_id, $todayDate);

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
            $data = $result['data'][0] ?? [];

            $data['check_in_status'] = '';
            $data['check_out_status'] = '';
            $data['duration_status'] = '';
            $data['work_hours_decimal'] = 0;

            if (!empty($data['check_in']) && !empty($data['start_time']) && $data['check_in'] > $data['start_time']) {
                $data['check_in_status'] = 'Late';
            }

            if (!empty($data['check_in']) && !empty($data['check_out'])) {
                $checkInTime = new DateTime($data['check_in'], $tz);
                $checkOutTime = new DateTime($data['check_out'], $tz);
                $interval = $checkInTime->diff($checkOutTime);
                $hours_worked = $interval->h + ($interval->i / 60) + ($interval->s / 3600);
                $data['work_hours_decimal'] = $hours_worked;

                if (!empty($data['end_time']) && $data['check_out'] < $data['end_time']) {
                    $data['check_out_status'] = 'Early Out';
                }

                if ($hours_worked < 4) {
                    $data['duration_status'] = 'Half Day Worked';
                }
            }

            // Backend is the single source of truth for status
            if ($dayContext['status'] !== 'working') {
                $data['day_status'] = $dayContext['status'];
            } elseif (!empty($data['status'])) {
                $data['day_status'] = $data['status'];
            } else {
                $data['day_status'] = '';
            }

            $data['day_message'] = $dayContext['message'];
            $data['can_check_in'] = !$dayContext['block'] && empty($data['check_in']);
            $data['can_check_out'] = !$dayContext['block'] && !empty($data['check_in']) && empty($data['check_out']);

            $response = ['success' => true, 'data' => $data];
        } else {
            $response['message'] = 'Could not retrieve attendance status.';
        }
        break;

    case 'check_in':
    case 'check_out':
        $todayDate = new DateTime('now', $tz);
        $dayContext = getAttendanceDayContext($mysqli, $company_id, $employee_id, $todayDate);

        if ($dayContext['block']) {
            $response['message'] = $dayContext['message'];
            break;
        }

        // Server records IST time; client time is not trusted
        $now = $todayDate->format('H:i:s');

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
                $checkInTime = new DateTime($existing_checkin, $tz);
                $checkOutTime = new DateTime($now, $tz);
                $interval = $checkInTime->diff($checkOutTime);
                $hours_worked = $interval->h + ($interval->i / 60) + ($interval->s / 3600);

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
                $shiftStartTime = DateTime::createFromFormat('H:i:s', $shift_start, $tz);
                $shiftEndTime = DateTime::createFromFormat('H:i:s', $shift_end, $tz);
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