<?php
header('Content-Type: application/json');
require_once '../config/db.php';
require_once '../includes/functions.php';

$response = ['success' => false, 'message' => 'An unknown error occurred.'];

if (!isLoggedIn()) {
    $response['message'] = 'Unauthorized access.';
    echo json_encode($response);
    exit();
}

/**
 * Calculate actual leave days considering holidays and Saturday policy
 * @param DateTime $start_date
 * @param DateTime $end_date
 * @param array $holidays - Array of holiday dates (YYYY-MM-DD format)
 * @param string $saturday_policy - 'none', '1st_3rd', '2nd_4th', 'all'
 * @return int - Number of actual leave days to deduct
 */
function calculateActualLeaveDays($start_date, $end_date, $holidays = [], $saturday_policy = 'none')
{
    $actual_days = 0;
    $current = clone $start_date;
    $holiday_dates = array_flip($holidays); // For O(1) lookup
    $week_count = 0;
    $current_week_saturdays = [];

    while ($current <= $end_date) {
        $date_str = $current->format('Y-m-d');
        $day_of_week = (int) $current->format('w'); // 0=Sunday, 6=Saturday

        // Check if it's a holiday
        if (isset($holiday_dates[$date_str])) {
            $current->modify('+1 day');
            continue;
        }

        // Check if it's a Saturday that should be skipped
        if ($day_of_week === 6) { // Saturday
            if ($saturday_policy === 'all') {
                $current->modify('+1 day');
                continue;
            } elseif ($saturday_policy === '1st_3rd' || $saturday_policy === '2nd_4th') {
                // Calculate which Saturday of the month
                $saturday_of_month = ceil($current->format('d') / 7);
                $should_skip = false;

                if ($saturday_policy === '1st_3rd' && ($saturday_of_month === 1 || $saturday_of_month === 3)) {
                    $should_skip = true;
                } elseif ($saturday_policy === '2nd_4th' && ($saturday_of_month === 2 || $saturday_of_month === 4)) {
                    $should_skip = true;
                }

                if ($should_skip) {
                    $current->modify('+1 day');
                    continue;
                }
            }
        }

        // It's a working day - count it
        $actual_days++;
        $current->modify('+1 day');
    }

    return $actual_days;
}

$action = $_REQUEST['action'] ?? '';
$user_id = $_SESSION['user_id'];
$company_id = $_SESSION['company_id'];
$role_id = $_SESSION['role_id'];

$employee_info = query($mysqli, "SELECT id FROM employees WHERE user_id = ?", [$user_id]);
$employee_id = $employee_info['success'] && !empty($employee_info['data']) ? $employee_info['data'][0]['id'] : 0;

switch ($action) {
    case 'get_leave_summary':
        $policies = query($mysqli, "SELECT id, leave_type, days_per_year FROM leave_policies WHERE company_id = ?", [$company_id])['data'] ?? [];

        // Get all holidays for this company
        $holidays_result = query($mysqli, "SELECT holiday_date FROM holidays WHERE company_id = ?", [$company_id]);
        $holiday_dates = array_column($holidays_result['data'] ?? [], 'holiday_date');

        // Get Saturday policy
        $settings_result = query($mysqli, "SELECT saturday_policy FROM company_holiday_settings WHERE company_id = ?", [$company_id]);
        $saturday_policy = $settings_result['success'] && !empty($settings_result['data']) ? $settings_result['data'][0]['saturday_policy'] : 'none';

        // Get all approved leaves for this employee
        $all_leaves_result = query($mysqli, "
            SELECT l.leave_type, l.start_date, l.end_date
            FROM leaves l
            WHERE l.employee_id = ? AND l.status = 'approved' AND YEAR(l.start_date) = YEAR(CURDATE())
            ORDER BY l.start_date
        ", [$employee_id])['data'] ?? [];

        // Calculate actual days used for each leave type
        $used_map = [];
        foreach ($all_leaves_result as $leave) {
            $start = new DateTime($leave['start_date']);
            $end = new DateTime($leave['end_date']);
            $actual_days = calculateActualLeaveDays($start, $end, $holiday_dates, $saturday_policy);

            if (!isset($used_map[$leave['leave_type']])) {
                $used_map[$leave['leave_type']] = 0;
            }
            $used_map[$leave['leave_type']] += $actual_days;
        }

        $balances = [];
        foreach ($policies as $policy) {
            $used = $used_map[$policy['leave_type']] ?? 0;
            $balances[] = ['type' => $policy['leave_type'], 'balance' => $policy['days_per_year'] - $used, 'total' => $policy['days_per_year']];
        }

        $next_holiday_result = query($mysqli, "SELECT holiday_name, holiday_date FROM holidays WHERE company_id = ? AND holiday_date >= CURDATE() ORDER BY holiday_date ASC LIMIT 1", [$company_id]);
        $next_holiday = $next_holiday_result['success'] && !empty($next_holiday_result['data']) ? $next_holiday_result['data'][0] : null;

        $policy_doc_result = query($mysqli, "SELECT id, doc_name FROM documents WHERE company_id = ? AND related_type = 'policy' ORDER BY uploaded_at DESC LIMIT 1", [$company_id]);
        $policy_document = $policy_doc_result['success'] && !empty($policy_doc_result['data']) ? $policy_doc_result['data'][0] : null;

        $response = ['success' => true, 'data' => ['balances' => $balances, 'next_holiday' => $next_holiday, 'policy_document' => $policy_document]];
        break;

    case 'get_leave_calculation':
        $start_date_str = $_GET['start_date'] ?? '';
        $end_date_str = $_GET['end_date'] ?? '';

        if (empty($start_date_str) || empty($end_date_str)) {
            $response['message'] = 'Missing dates';
            break;
        }

        // Get holidays and Saturday policy
        $holidays_result = query($mysqli, "SELECT holiday_date FROM holidays WHERE company_id = ?", [$company_id]);
        $holiday_dates = array_column($holidays_result['data'] ?? [], 'holiday_date');

        $settings_result = query($mysqli, "SELECT saturday_policy FROM company_holiday_settings WHERE company_id = ?", [$company_id]);
        $saturday_policy = $settings_result['success'] && !empty($settings_result['data']) ? $settings_result['data'][0]['saturday_policy'] : 'none';

        // Calculate days
        $start = new DateTime($start_date_str);
        $end = new DateTime($end_date_str);
        $total_days = (int) $start->diff($end)->format('%a') + 1;

        $actual_days = calculateActualLeaveDays($start, $end, $holiday_dates, $saturday_policy);

        // Count holidays and Saturdays skipped
        $holidays_skipped = 0;
        $saturdays_skipped = 0;
        $current = clone $start;
        $holiday_dates_flip = array_flip($holiday_dates);

        while ($current <= $end) {
            $date_str = $current->format('Y-m-d');
            $day_of_week = (int) $current->format('w');

            if (isset($holiday_dates_flip[$date_str])) {
                $holidays_skipped++;
            } elseif ($day_of_week === 6) { // Saturday
                if ($saturday_policy === 'all') {
                    $saturdays_skipped++;
                } elseif ($saturday_policy === '1st_3rd' || $saturday_policy === '2nd_4th') {
                    $saturday_of_month = ceil($current->format('d') / 7);
                    if (
                        ($saturday_policy === '1st_3rd' && ($saturday_of_month === 1 || $saturday_of_month === 3)) ||
                        ($saturday_policy === '2nd_4th' && ($saturday_of_month === 2 || $saturday_of_month === 4))
                    ) {
                        $saturdays_skipped++;
                    }
                }
            }

            $current->modify('+1 day');
        }

        $response = ['success' => true, 'total_days' => $total_days, 'actual_days' => $actual_days, 'holidays_skipped' => $holidays_skipped, 'saturdays_skipped' => $saturdays_skipped];
        break;

    case 'get_my_leaves':
        $result = query($mysqli, "SELECT * FROM leaves WHERE employee_id = ? ORDER BY start_date DESC", [$employee_id]);
        $response = ['success' => true, 'data' => $result['data'] ?? []];
        break;

    case 'get_pending_requests':
        // BUG FIX 4: Use 'applied_at' for ordering
        $sql = "SELECT l.*, e.first_name, e.last_name 
                FROM leaves l 
                JOIN employees e ON l.employee_id = e.id
                JOIN users u ON e.user_id = u.id
                WHERE u.company_id = ? AND l.employee_id != ?
                ORDER BY l.applied_at DESC";
        $params = [$company_id, $employee_id];

        if ($role_id == 6) { // Manager role
            $manager_dept_info = query($mysqli, "SELECT department_id FROM employees WHERE id = ?", [$employee_id]);
            $manager_dept_id = $manager_dept_info['data'][0]['department_id'] ?? 0;
            if ($manager_dept_id > 0) {
                $sql = "SELECT l.*, e.first_name, e.last_name FROM leaves l JOIN employees e ON l.employee_id = e.id JOIN users u ON e.user_id = u.id WHERE u.company_id = ? AND l.employee_id != ? AND e.department_id = ? ORDER BY l.applied_at DESC";
                $params = [$company_id, $employee_id, $manager_dept_id];
            }
        }

        $result = query($mysqli, $sql, $params);
        $response = ['success' => true, 'data' => $result['data'] ?? []];
        break;

    case 'apply_leave':
        // ... (rest of the case remains the same)
        $start_date = $_POST['start_date'] ?? '';
        $end_date = $_POST['end_date'] ?? '';
        $leave_type = $_POST['leave_type'] ?? '';
        $reason = $_POST['reason'] ?? '';

        if (empty($start_date) || empty($end_date) || empty($leave_type)) {
            $response['message'] = 'Please fill all required fields.';
            break;
        }

        // Validate dates are in correct format (YYYY-MM-DD)
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $start_date) || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $end_date)) {
            $response['message'] = 'Invalid date format.';
            break;
        }

        if ($end_date < $start_date) {
            $response['message'] = 'End date cannot be before start date.';
            break;
        }

        // Validate that start date is not in the past
        $today = date('Y-m-d');
        if ($start_date < $today) {
            $response['message'] = 'Leave start date cannot be in the past. Please select a date from today onwards.';
            break;
        }

        $result = query($mysqli, "INSERT INTO leaves (employee_id, start_date, end_date, leave_type, reason) VALUES (?, ?, ?, ?, ?)", [$employee_id, $start_date, $end_date, $leave_type, $reason]);
        if ($result['success']) {
            $response = ['success' => true, 'message' => 'Leave request submitted successfully!'];
        } else {
            $response['message'] = 'Failed to submit leave request.';
        }
        break;

    case 'update_status':
        // ... (rest of the case remains the same)
        $leave_id = (int) ($_POST['leave_id'] ?? 0);
        $status = $_POST['status'] ?? '';

        if (!in_array($status, ['approved', 'rejected'])) {
            $response['message'] = 'Invalid status.';
            break;
        }

        // Get leave details before updating
        $leave_details = query($mysqli, "SELECT l.employee_id, l.start_date, l.end_date FROM leaves l WHERE l.id = ?", [$leave_id]);
        $leave_data = $leave_details['success'] && !empty($leave_details['data']) ? $leave_details['data'][0] : null;

        $sql = "UPDATE leaves l JOIN employees e ON l.employee_id = e.id SET l.status = ?, l.approved_by = ? WHERE l.id = ? AND e.user_id IN (SELECT id FROM users WHERE company_id = ?)";
        $result = query($mysqli, $sql, [$status, $user_id, $leave_id, $company_id]);

        if ($result['success'] && $result['affected_rows'] > 0) {
            // If approved, mark all days as "leave" in attendance table
            if ($status === 'approved' && $leave_data) {
                $start = new DateTime($leave_data['start_date']);
                $end = new DateTime($leave_data['end_date']);

                while ($start <= $end) {
                    $date = $start->format('Y-m-d');
                    // Insert or update attendance record with status 'leave'
                    query(
                        $mysqli,
                        "INSERT INTO attendance (employee_id, date, status) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE status = 'leave'",
                        [$leave_data['employee_id'], $date, 'leave']
                    );
                    $start->modify('+1 day');
                }
            }
            // If rejected, remove the leave status from attendance (optional - can be modified per policy)
            // For now, we'll just leave the attendance records as they are

            $response = ['success' => true, 'message' => "Request has been $status."];
        } else {
            $response['message'] = 'Failed to update status or unauthorized.';
        }
        break;

    case 'cancel_leave':
        // ... (rest of the case remains the same)
        $leave_id = (int) ($_POST['leave_id'] ?? 0);
        $sql = "UPDATE leaves SET status = 'cancelled' WHERE id = ? AND employee_id = ? AND status = 'pending'";
        $result = query($mysqli, $sql, [$leave_id, $employee_id]);

        if ($result['success'] && $result['affected_rows'] > 0) {
            $response = ['success' => true, 'message' => 'Your leave request has been cancelled.'];
        } else {
            $response['message'] = 'Could not cancel request. It might have already been actioned.';
        }
        break;

    default:
        $response['message'] = 'Invalid action specified.';
        break;
}

echo json_encode($response);

exit();
?>