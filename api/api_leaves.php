<?php
/**
 * HRMS Leave Management API - Role-Based Approval Workflow
 * 
 * Roles:
 * - 1: Admin (Super Admin)
 * - 2: Company Owner (Approves HR only)
 * - 3: HR (Approves Manager & Employee)
 * - 4: Employee (Can apply only)
 * - 6: Manager (Approves employees in their department)
 */

header('Content-Type: application/json');
require_once '../config/db.php';
require_once '../includes/functions.php';
require_once '../includes/notification_helpers.php';

$response = ['success' => false, 'message' => 'An unknown error occurred.'];

if (!isLoggedIn()) {
    $response['message'] = 'Unauthorized access.';
    http_response_code(401);
    echo json_encode($response);
    exit();
}

// Session variables
$user_id = $_SESSION['user_id'];
$company_id = $_SESSION['company_id'];
$role_id = $_SESSION['role_id'];

// Get current user's employee info
$employee_info = query($mysqli, "SELECT id FROM employees WHERE user_id = ?", [$user_id]);
$employee_id = $employee_info['success'] && !empty($employee_info['data']) ? $employee_info['data'][0]['id'] : 0;

/**
 * Calculate actual leave days considering holidays and Saturday/Sunday policy
 */
function calculateActualLeaveDays($start_date, $end_date, $holidays = [], $saturday_policy = 'none')
{
    $actual_days = 0;
    $current = clone $start_date;
    $holiday_dates = array_flip($holidays);

    while ($current <= $end_date) {
        $date_str = $current->format('Y-m-d');
        $day_of_week = (int) $current->format('w');
        $day_of_month = (int) $current->format('d');

        // Check if it's a holiday
        if (isset($holiday_dates[$date_str])) {
            $current->modify('+1 day');
            continue;
        }

        // Always skip Sundays (day 0)
        if ($day_of_week === 0) {
            $current->modify('+1 day');
            continue;
        }

        // Check if it's a Saturday that should be skipped
        if ($day_of_week === 6) {
            if ($saturday_policy === 'all') {
                $current->modify('+1 day');
                continue;
            } elseif ($saturday_policy === '1st_3rd' || $saturday_policy === '2nd_4th') {
                // Calculate which Saturday of the month (1st, 2nd, 3rd, 4th, 5th)
                // Note: ceil() returns float, so cast to int for strict comparison
                $saturday_of_month = (int) ceil($day_of_month / 7);

                if (
                    ($saturday_policy === '1st_3rd' && ($saturday_of_month === 1 || $saturday_of_month === 3)) ||
                    ($saturday_policy === '2nd_4th' && ($saturday_of_month === 2 || $saturday_of_month === 4))
                ) {
                    $current->modify('+1 day');
                    continue;
                }
            }
        }

        $actual_days++;
        $current->modify('+1 day');
    }

    return $actual_days;
}

/**
 * Get approver's name from user_id
 */
function getApproverName($mysqli, $user_id)
{
    if (!$user_id)
        return null;

    $result = query(
        $mysqli,
        "SELECT e.first_name, e.last_name FROM employees e 
         WHERE e.user_id = ?",
        [$user_id]
    )['data'][0] ?? null;

    return $result ? "{$result['first_name']} {$result['last_name']}" : null;
}

/**
 * Check if user can approve a specific leave
 * Returns: [canApprove: bool, reason: string]
 */
function canApproveLeave($mysqli, $user_id, $leave_id, $role_id, $company_id)
{
    // Fetch leave with employee role information
    $leave_query = query($mysqli, "
        SELECT l.*, e.user_id as employee_user_id, e.id as emp_id, u.role_id as employee_role_id
        FROM leaves l
        JOIN employees e ON l.employee_id = e.id
        JOIN users u ON e.user_id = u.id
        WHERE l.id = ? AND u.company_id = ?
    ", [$leave_id, $company_id]);

    if (!$leave_query['success'] || empty($leave_query['data'])) {
        return ['allowed' => false, 'reason' => 'Leave not found'];
    }

    $leave = $leave_query['data'][0];

    // Admin can approve anything
    if ($role_id == 1) {
        return ['allowed' => true];
    }

    // Manager (Role 6): Can approve Employee leaves in their department or team
    if ($role_id == 6) {
        if ($leave['employee_role_id'] != 4) {
            return ['allowed' => false, 'reason' => 'Managers can only approve employee leave requests'];
        }

        // Check department match
        $mgr_info = query(
            $mysqli,
            "SELECT department_id FROM employees WHERE user_id = ?",
            [$user_id]
        )['data'][0] ?? null;

        $emp_info = query(
            $mysqli,
            "SELECT id, department_id FROM employees WHERE id = ?",
            [$leave['emp_id']]
        )['data'][0] ?? null;

        if (!$mgr_info || !$emp_info) {
            return ['allowed' => false, 'reason' => 'Employee information not found'];
        }

        // Check if employee is in same department
        $in_department = $mgr_info['department_id'] == $emp_info['department_id'];

        // Check if employee is in manager's team
        $in_team = false;
        if (!$in_department) {
            $team_check = query(
                $mysqli,
                "SELECT id FROM team_members tm
                 WHERE tm.employee_id = ? 
                 AND tm.team_id IN (
                    SELECT id FROM teams WHERE company_id = ? AND created_by = ?
                 )",
                [$emp_info['id'], $company_id, $user_id]
            );
            $in_team = $team_check['success'] && !empty($team_check['data']);
        }

        if (!$in_department && !$in_team) {
            return ['allowed' => false, 'reason' => 'Employee is not in your department or team'];
        }

        return ['allowed' => true];
    }

    // HR (Role 3): Can approve Employee (4) and Manager (6) leaves
    if ($role_id == 3) {
        if (!in_array($leave['employee_role_id'], [4, 6])) {
            return ['allowed' => false, 'reason' => 'HR can only approve Employee and Manager leave requests'];
        }

        return ['allowed' => true];
    }

    // Company Owner (Role 2): Can approve anyone's leaves (Employee, Manager, HR)
    if ($role_id == 2) {
        // Company Owner can approve leaves from any role
        return ['allowed' => true];
    }

    // Employees cannot approve anything
    if ($role_id == 4) {
        return ['allowed' => false, 'reason' => 'Employees cannot approve leave requests'];
    }

    return ['allowed' => false, 'reason' => 'Invalid role'];
}

/**
 * Get pending leaves based on approver role
 */
function getPendingLeavesForApprover($mysqli, $user_id, $role_id, $company_id)
{
    $employee_info = query(
        $mysqli,
        "SELECT id FROM employees WHERE user_id = ?",
        [$user_id]
    )['data'][0] ?? null;
    $employee_id = $employee_info['id'] ?? 0;

    if ($role_id == 6) { // Manager
        $dept_info = query(
            $mysqli,
            "SELECT department_id FROM employees WHERE user_id = ?",
            [$user_id]
        )['data'][0] ?? null;
        $dept_id = $dept_info['department_id'] ?? 0;

        // Get manager's team IDs
        $teams_result = query(
            $mysqli,
            "SELECT id FROM teams WHERE company_id = ? AND created_by = ?",
            [$company_id, $user_id]
        );
        $team_ids = array_column($teams_result['data'] ?? [], 'id');
        $team_ids_str = !empty($team_ids) ? implode(',', $team_ids) : '0';

        return query($mysqli, "
            SELECT l.*, 
                   e.first_name, e.last_name, e.id as emp_id,
                   u.role_id as employee_role_id
            FROM leaves l
            JOIN employees e ON l.employee_id = e.id
            JOIN users u ON e.user_id = u.id
            WHERE u.company_id = ?
              AND (l.status = 'pending' OR (l.status = 'approved' AND MONTH(l.start_date) = MONTH(CURDATE()) AND YEAR(l.start_date) = YEAR(CURDATE())))
              AND e.user_id != ?
              AND (
                (e.department_id = ? AND u.role_id = 4)
                OR (e.id IN (SELECT employee_id FROM team_members WHERE team_id IN ($team_ids_str)))
              )
            ORDER BY l.applied_at DESC
        ", [$company_id, $user_id, $dept_id])['data'] ?? [];
    }

    if ($role_id == 3) { // HR
        return query($mysqli, "
            SELECT l.*, 
                   e.first_name, e.last_name, e.id as emp_id,
                   u.role_id as employee_role_id
            FROM leaves l
            JOIN employees e ON l.employee_id = e.id
            JOIN users u ON e.user_id = u.id
            WHERE u.company_id = ?
              AND (l.status = 'pending' OR (l.status = 'approved' AND MONTH(l.start_date) = MONTH(CURDATE()) AND YEAR(l.start_date) = YEAR(CURDATE())))
              AND u.role_id IN (4, 6)
              AND e.user_id != ?
            ORDER BY l.applied_at DESC
        ", [$company_id, $user_id])['data'] ?? [];
    }

    if ($role_id == 2) { // Company Owner
        return query($mysqli, "
            SELECT l.*, 
                   e.first_name, e.last_name, e.id as emp_id,
                   u.role_id as employee_role_id
            FROM leaves l
            JOIN employees e ON l.employee_id = e.id
            JOIN users u ON e.user_id = u.id
            WHERE u.company_id = ?
              AND (l.status = 'pending' OR (l.status = 'approved' AND MONTH(l.start_date) = MONTH(CURDATE()) AND YEAR(l.start_date) = YEAR(CURDATE())))
              AND u.role_id IN (3, 4, 6)
            ORDER BY l.applied_at DESC
        ", [$company_id])['data'] ?? [];
    }

    return [];
}

/**
 * Update leave attendance records when approved
 */
function updateAttendanceForApprovedLeave($mysqli, $leave_data)
{
    $start = new DateTime($leave_data['start_date']);
    $end = new DateTime($leave_data['end_date']);

    while ($start <= $end) {
        $date = $start->format('Y-m-d');
        query(
            $mysqli,
            "INSERT INTO attendance (employee_id, date, status) 
             VALUES (?, ?, 'leave') 
             ON DUPLICATE KEY UPDATE status = 'leave'",
            [$leave_data['employee_id'], $date]
        );
        $start->modify('+1 day');
    }
}

// Handle actions
$action = $_REQUEST['action'] ?? '';

switch ($action) {
    // ====================== GET LEAVE SUMMARY ======================
    case 'get_leave_summary':
        $policies = query(
            $mysqli,
            "SELECT id, leave_type, days_per_year FROM leave_policies WHERE company_id = ?",
            [$company_id]
        )['data'] ?? [];

        // Get holidays
        $holidays_result = query(
            $mysqli,
            "SELECT holiday_date FROM holidays WHERE company_id = ?",
            [$company_id]
        );
        $holiday_dates = array_column($holidays_result['data'] ?? [], 'holiday_date');

        // Get Saturday policy
        $settings_result = query(
            $mysqli,
            "SELECT saturday_policy FROM company_holiday_settings WHERE company_id = ?",
            [$company_id]
        );
        $saturday_policy = $settings_result['success'] && !empty($settings_result['data'])
            ? $settings_result['data'][0]['saturday_policy']
            : 'none';

        // Get all approved leaves for current employee
        $approved_leaves = query($mysqli, "
            SELECT l.leave_type, l.start_date, l.end_date
            FROM leaves l
            WHERE l.employee_id = ? 
              AND l.status = 'approved' 
              AND YEAR(l.start_date) = YEAR(CURDATE())
            ORDER BY l.start_date
        ", [$employee_id])['data'] ?? [];

        // Calculate used days per leave type
        $used_map = [];
        foreach ($approved_leaves as $leave) {
            $start = new DateTime($leave['start_date'], new DateTimeZone('UTC'));
            $end = new DateTime($leave['end_date'], new DateTimeZone('UTC'));
            $actual_days = calculateActualLeaveDays($start, $end, $holiday_dates, $saturday_policy);

            if (!isset($used_map[$leave['leave_type']])) {
                $used_map[$leave['leave_type']] = 0;
            }
            $used_map[$leave['leave_type']] += $actual_days;
        }

        // Build balances
        $balances = [];
        foreach ($policies as $policy) {
            $used = $used_map[$policy['leave_type']] ?? 0;
            $balances[] = [
                'type' => $policy['leave_type'],
                'balance' => max(0, $policy['days_per_year'] - $used),
                'total' => $policy['days_per_year'],
                'used' => $used
            ];
        }

        // Get next holiday
        $next_holiday = query(
            $mysqli,
            "SELECT holiday_name, holiday_date FROM holidays 
             WHERE company_id = ? AND holiday_date >= CURDATE() 
             ORDER BY holiday_date ASC LIMIT 1",
            [$company_id]
        )['data'][0] ?? null;

        // Get policy document
        $policy_doc = query(
            $mysqli,
            "SELECT id, doc_name FROM documents 
             WHERE related_id = ? AND related_type = 'policy' 
             ORDER BY uploaded_at DESC LIMIT 1",
            [$company_id]
        )['data'][0] ?? null;

        $response = [
            'success' => true,
            'data' => [
                'balances' => $balances,
                'next_holiday' => $next_holiday,
                'policy_document' => $policy_doc
            ]
        ];
        break;

    // ====================== CALCULATE LEAVE DAYS ======================
    case 'get_leave_calculation':
        $start_date_str = $_GET['start_date'] ?? '';
        $end_date_str = $_GET['end_date'] ?? '';

        if (empty($start_date_str) || empty($end_date_str)) {
            $response['message'] = 'Missing dates';
            break;
        }

        // Get holidays and Saturday policy
        $holidays_result = query(
            $mysqli,
            "SELECT holiday_date FROM holidays WHERE company_id = ?",
            [$company_id]
        );
        $holiday_dates = array_column($holidays_result['data'] ?? [], 'holiday_date');

        $settings_result = query(
            $mysqli,
            "SELECT saturday_policy FROM company_holiday_settings WHERE company_id = ?",
            [$company_id]
        );
        $saturday_policy = $settings_result['success'] && !empty($settings_result['data'])
            ? $settings_result['data'][0]['saturday_policy']
            : 'none';

        // Calculate days
        $start = new DateTime($start_date_str);
        $end = new DateTime($end_date_str);
        $total_days = (int) $start->diff($end)->format('%a') + 1;
        $actual_days = calculateActualLeaveDays($start, $end, $holiday_dates, $saturday_policy);

        // Count holidays, Sundays and Saturdays skipped
        $holidays_skipped = 0;
        $sundays_skipped = 0;
        $saturdays_skipped = 0;
        $current = clone $start;
        $holiday_dates_flip = array_flip($holiday_dates);

        while ($current <= $end) {
            $date_str = $current->format('Y-m-d');
            $day_of_week = (int) $current->format('w');

            if (isset($holiday_dates_flip[$date_str])) {
                $holidays_skipped++;
            } elseif ($day_of_week === 0) {
                // Sunday
                $sundays_skipped++;
            } elseif ($day_of_week === 6) {
                // Saturday
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

        $response = [
            'success' => true,
            'total_days' => $total_days,
            'actual_days' => $actual_days,
            'holidays_skipped' => $holidays_skipped,
            'sundays_skipped' => $sundays_skipped,
            'saturdays_skipped' => $saturdays_skipped
        ];
        break;

    // ====================== GET MY LEAVES ======================
    case 'get_my_leaves':
        $result = query(
            $mysqli,
            "SELECT l.* FROM leaves l
             WHERE l.employee_id = ? 
             ORDER BY l.start_date DESC",
            [$employee_id]
        );

        $leaves = $result['data'] ?? [];

        // Enrich with approver names
        foreach ($leaves as &$leave) {
            $leave['approver_name'] = getApproverName($mysqli, $leave['approved_by']);
        }

        $response = ['success' => true, 'data' => $leaves];
        break;

    // ====================== GET PENDING REQUESTS FOR APPROVER ======================
    case 'get_pending_requests':
        // Only allow approvers
        if (!in_array($role_id, [1, 2, 3, 6])) {
            $response['message'] = 'You are not authorized to view approval requests.';
            http_response_code(403);
            break;
        }

        $pending_leaves = getPendingLeavesForApprover($mysqli, $user_id, $role_id, $company_id);

        // Enrich with approver names
        foreach ($pending_leaves as &$leave) {
            $leave['approver_name'] = getApproverName($mysqli, $leave['approved_by']);
        }

        $response = ['success' => true, 'data' => $pending_leaves];
        break;

    // ====================== APPLY LEAVE ======================
    case 'apply_leave':
        $start_date = $_POST['start_date'] ?? '';
        $end_date = $_POST['end_date'] ?? '';
        $leave_type = $_POST['leave_type'] ?? '';
        $reason = $_POST['reason'] ?? '';

        if (empty($start_date) || empty($end_date) || empty($leave_type)) {
            $response['message'] = 'Please fill all required fields.';
            break;
        }

        // Validate date format
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $start_date) || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $end_date)) {
            $response['message'] = 'Invalid date format.';
            break;
        }

        if ($end_date < $start_date) {
            $response['message'] = 'End date cannot be before start date.';
            break;
        }

        // Validate start date is not in past
        $today = date('Y-m-d');
        if ($start_date < $today) {
            $response['message'] = 'Leave start date cannot be in the past.';
            break;
        }

        // Validate leave type exists
        $policy_check = query(
            $mysqli,
            "SELECT id FROM leave_policies 
             WHERE company_id = ? AND leave_type = ?",
            [$company_id, $leave_type]
        );

        if (!$policy_check['success'] || empty($policy_check['data'])) {
            $response['message'] = 'Invalid leave type selected.';
            break;
        }

        // Only employees and managers can apply for leave
        if (!in_array($role_id, [4, 6, 3])) {
            $response['message'] = 'Your role does not have permission to apply for leave.';
            break;
        }

        // Insert leave with status='pending' and approved_by=NULL
        $result = query(
            $mysqli,
            "INSERT INTO leaves (employee_id, start_date, end_date, leave_type, reason, status, approved_by) 
             VALUES (?, ?, ?, ?, ?, 'pending', NULL)",
            [$employee_id, $start_date, $end_date, $leave_type, $reason]
        );

        if ($result['success']) {
            $new_leave_id = $mysqli->insert_id;

            // Notify relevant approvers based on role
            $approvers_to_notify = [];

            // Get employee info for notification message
            $emp_info = query($mysqli, "SELECT first_name, last_name FROM employees WHERE user_id = ?", [$user_id])['data'][0] ?? null;
            $emp_name = $emp_info ? "{$emp_info['first_name']} {$emp_info['last_name']}" : 'An employee';

            if ($role_id == 4) {
                // Employee: Notify Manager (via team_members or same dept) and HR

                // First, find managers via team_members (assigned_by)
                $team_managers = query(
                    $mysqli,
                    "SELECT DISTINCT tm.assigned_by as user_id FROM team_members tm
                     JOIN employees e ON tm.employee_id = e.id
                     JOIN users u ON tm.assigned_by = u.id
                     WHERE e.user_id = ? AND u.role_id = 6 AND u.company_id = ?",
                    [$user_id, $company_id]
                )['data'] ?? [];
                foreach ($team_managers as $mgr) {
                    $approvers_to_notify[] = $mgr['user_id'];
                }

                // Also check department-level managers
                $dept_info = query($mysqli, "SELECT department_id FROM employees WHERE user_id = ?", [$user_id])['data'][0] ?? null;
                if ($dept_info && $dept_info['department_id']) {
                    $dept_managers = query(
                        $mysqli,
                        "SELECT u.id FROM users u 
                         JOIN employees e ON u.id = e.user_id 
                         WHERE u.role_id = 6 AND u.company_id = ? AND e.department_id = ?",
                        [$company_id, $dept_info['department_id']]
                    )['data'] ?? [];
                    foreach ($dept_managers as $mgr) {
                        $approvers_to_notify[] = $mgr['id'];
                    }
                }
                // Also notify HR
                $hr_users = query($mysqli, "SELECT id FROM users WHERE role_id = 3 AND company_id = ?", [$company_id])['data'] ?? [];
                foreach ($hr_users as $hr) {
                    $approvers_to_notify[] = $hr['id'];
                }
            } elseif ($role_id == 6) {
                // Manager: Notify HR
                $hr_users = query($mysqli, "SELECT id FROM users WHERE role_id = 3 AND company_id = ?", [$company_id])['data'] ?? [];
                foreach ($hr_users as $hr) {
                    $approvers_to_notify[] = $hr['id'];
                }
            } elseif ($role_id == 3) {
                // HR: Notify Company Owner
                $owner_users = query($mysqli, "SELECT id FROM users WHERE role_id = 2 AND company_id = ?", [$company_id])['data'] ?? [];
                foreach ($owner_users as $owner) {
                    $approvers_to_notify[] = $owner['id'];
                }
            }

            // Send notifications to all approvers
            foreach (array_unique($approvers_to_notify) as $approver_id) {
                createNotificationIfEnabled(
                    $mysqli,
                    $approver_id,
                    'leave',
                    'New Leave Request',
                    "{$emp_name} has requested {$leave_type} from {$start_date} to {$end_date}",
                    $new_leave_id,
                    'leave'
                );
            }

            $response = [
                'success' => true,
                'message' => 'Leave request submitted successfully!',
                'leave_id' => $new_leave_id
            ];
        } else {
            $response['message'] = 'Failed to submit leave request.';
        }
        break;

    // ====================== APPROVE OR REJECT LEAVE ======================
    case 'approve_or_reject':
        $leave_id = (int) ($_POST['leave_id'] ?? 0);
        $action_type = $_POST['action_type'] ?? ''; // 'approve' or 'reject'

        if (!$leave_id) {
            $response['message'] = 'Missing leave ID.';
            break;
        }

        if (!in_array($action_type, ['approve', 'reject'])) {
            $response['message'] = 'Invalid action.';
            break;
        }

        // Check authorization
        $auth_check = canApproveLeave($mysqli, $user_id, $leave_id, $role_id, $company_id);

        if (!$auth_check['allowed']) {
            $response['message'] = $auth_check['reason'] ?? 'You are not authorized to approve this leave.';
            http_response_code(403);
            break;
        }

        // Get leave details
        $leave_result = query(
            $mysqli,
            "SELECT l.* FROM leaves l WHERE l.id = ?",
            [$leave_id]
        );

        if (!$leave_result['success'] || empty($leave_result['data'])) {
            $response['message'] = 'Leave not found.';
            break;
        }

        $leave_data = $leave_result['data'][0];

        // Verify status is pending
        if ($leave_data['status'] !== 'pending') {
            $response['message'] = "Cannot modify a leave with status: {$leave_data['status']}";
            break;
        }

        // Update leave record
        $new_status = $action_type === 'approve' ? 'approved' : 'rejected';
        $update_result = query(
            $mysqli,
            "UPDATE leaves 
             SET status = ?, approved_by = ? 
             WHERE id = ?",
            [$new_status, $user_id, $leave_id]
        );

        if (!$update_result['success']) {
            $response['message'] = 'Failed to update leave status.';
            break;
        }

        // If approved, update attendance records
        if ($action_type === 'approve') {
            updateAttendanceForApprovedLeave($mysqli, $leave_data);
        }

        // Notify the employee about the decision
        $leave_emp_info = query(
            $mysqli,
            "SELECT e.user_id, e.first_name, e.last_name FROM employees e WHERE e.id = ?",
            [$leave_data['employee_id']]
        )['data'][0] ?? null;

        if ($leave_emp_info) {
            $notification_title = $action_type === 'approve' ? 'Leave Approved' : 'Leave Rejected';
            $notification_message = "Your {$leave_data['leave_type']} request from {$leave_data['start_date']} to {$leave_data['end_date']} has been {$new_status}.";

            createNotificationIfEnabled(
                $mysqli,
                $leave_emp_info['user_id'],
                'leave',
                $notification_title,
                $notification_message,
                $leave_id,
                'leave'
            );
        }

        $response = [
            'success' => true,
            'message' => "Leave request has been {$new_status}!",
            'status' => $new_status,
            'approved_by_user_id' => $user_id
        ];
        break;

    // ====================== CANCEL LEAVE ======================
    case 'cancel_leave':
        $leave_id = (int) ($_POST['leave_id'] ?? 0);

        // Verify leave exists and belongs to current employee
        $leave_check = query(
            $mysqli,
            "SELECT status FROM leaves WHERE id = ? AND employee_id = ?",
            [$leave_id, $employee_id]
        );

        if (!$leave_check['success'] || empty($leave_check['data'])) {
            $response['message'] = 'Leave not found or unauthorized.';
            break;
        }

        $current_status = $leave_check['data'][0]['status'];

        // Only pending leaves can be cancelled
        if ($current_status !== 'pending') {
            $response['message'] = "Cannot cancel leave with status: {$current_status}";
            break;
        }

        $result = query(
            $mysqli,
            "UPDATE leaves SET status = 'cancelled' WHERE id = ? AND employee_id = ?",
            [$leave_id, $employee_id]
        );

        if ($result['success'] && $result['affected_rows'] > 0) {
            $response = [
                'success' => true,
                'message' => 'Your leave request has been cancelled.'
            ];
        } else {
            $response['message'] = 'Could not cancel request.';
        }
        break;

    // ====================== BACKWARD COMPATIBILITY: UPDATE_STATUS ======================
    case 'update_status':
        // Legacy endpoint for backward compatibility
        // Maps old 'status' parameter to new 'action_type' format
        $leave_id = (int) ($_POST['leave_id'] ?? 0);
        $status = $_POST['status'] ?? ''; // 'approved' or 'rejected'

        if (!$leave_id || !$status) {
            $response['message'] = 'Missing leave ID or status.';
            break;
        }

        if (!in_array($status, ['approved', 'rejected'])) {
            $response['message'] = 'Invalid status.';
            break;
        }

        // Convert old status format to new action_type format
        $action_type = $status === 'approved' ? 'approve' : 'reject';

        // Check authorization
        $auth_check = canApproveLeave($mysqli, $user_id, $leave_id, $role_id, $company_id);

        if (!$auth_check['allowed']) {
            $response['message'] = $auth_check['reason'] ?? 'You are not authorized to approve this leave.';
            http_response_code(403);
            break;
        }

        // Get leave details
        $leave_result = query(
            $mysqli,
            "SELECT l.* FROM leaves l WHERE l.id = ?",
            [$leave_id]
        );

        if (!$leave_result['success'] || empty($leave_result['data'])) {
            $response['message'] = 'Leave not found.';
            break;
        }

        $leave_data = $leave_result['data'][0];

        // Verify status is pending
        if ($leave_data['status'] !== 'pending') {
            $response['message'] = "Cannot modify a leave with status: {$leave_data['status']}";
            break;
        }

        // Update leave record
        $new_status = $status; // Keep original status format for backward compatibility
        $update_result = query(
            $mysqli,
            "UPDATE leaves 
             SET status = ?, approved_by = ? 
             WHERE id = ?",
            [$new_status, $user_id, $leave_id]
        );

        if (!$update_result['success']) {
            $response['message'] = 'Failed to update leave status.';
            break;
        }

        // If approved, update attendance records
        if ($action_type === 'approve') {
            updateAttendanceForApprovedLeave($mysqli, $leave_data);
        }

        $response = [
            'success' => true,
            'message' => "Leave request has been {$new_status}!",
            'status' => $new_status,
            'approved_by_user_id' => $user_id
        ];
        break;

    // ====================== INVALID ACTION ======================
    default:
        $response['message'] = 'Invalid action specified.';
        http_response_code(400);
        break;
}

echo json_encode($response);
exit();
?>