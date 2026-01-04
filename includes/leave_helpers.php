<?php
/**
 * HRMS Leave Management Helper Functions
 * 
 * These functions provide role-based authorization and data retrieval
 * for the leave approval workflow.
 * 
 * Usage: require_once 'leave_helpers.php' in api_leaves.php
 */

/**
 * Check if user role can apply for leaves
 * 
 * @param int $role_id User's role ID
 * @return bool True if role can apply for leave
 */
function canApplyForLeave($role_id)
{
    return in_array($role_id, [3, 4, 6]); // HR, Employee, Manager
}

/**
 * Check if user role can approve leaves (any leaves)
 * 
 * @param int $role_id User's role ID
 * @return bool True if role can approve
 */
function canApproveLeaves($role_id)
{
    return in_array($role_id, [1, 2, 3, 6]); // Admin, Owner, HR, Manager
}

/**
 * Get role name for display
 * 
 * @param int $role_id
 * @return string Role name
 */
function getRoleName($role_id)
{
    $roles = [
        1 => 'Admin',
        2 => 'Company Owner',
        3 => 'Human Resources',
        4 => 'Employee',
        5 => 'Candidate',
        6 => 'Manager'
    ];

    return $roles[$role_id] ?? 'Unknown';
}

/**
 * Get employee's department ID
 * 
 * @param mysqli $mysqli Database connection
 * @param int $employee_id
 * @return int|null Department ID or null
 */
function getEmployeeDepartment($mysqli, $employee_id)
{
    $result = query(
        $mysqli,
        "SELECT department_id FROM employees WHERE id = ?",
        [$employee_id]
    );

    return $result['success'] && !empty($result['data'])
        ? $result['data'][0]['department_id']
        : null;
}

/**
 * Check if two employees are in same department
 * 
 * @param mysqli $mysqli Database connection
 * @param int $employee_id_1
 * @param int $employee_id_2
 * @return bool True if in same department
 */
function isInSameDepartment($mysqli, $employee_id_1, $employee_id_2)
{
    $dept1 = getEmployeeDepartment($mysqli, $employee_id_1);
    $dept2 = getEmployeeDepartment($mysqli, $employee_id_2);

    return $dept1 && $dept2 && $dept1 === $dept2;
}

/**
 * Get employee ID from user ID
 * 
 * @param mysqli $mysqli Database connection
 * @param int $user_id
 * @return int|null Employee ID or null
 */
function getEmployeeIdFromUserId($mysqli, $user_id)
{
    $result = query(
        $mysqli,
        "SELECT id FROM employees WHERE user_id = ?",
        [$user_id]
    );

    return $result['success'] && !empty($result['data'])
        ? $result['data'][0]['id']
        : null;
}

/**
 * Get user's role ID from user ID
 * 
 * @param mysqli $mysqli Database connection
 * @param int $user_id
 * @return int|null Role ID or null
 */
function getUserRoleId($mysqli, $user_id)
{
    $result = query(
        $mysqli,
        "SELECT role_id FROM users WHERE id = ?",
        [$user_id]
    );

    return $result['success'] && !empty($result['data'])
        ? $result['data'][0]['role_id']
        : null;
}

/**
 * Check if leave type is valid for company
 * 
 * @param mysqli $mysqli Database connection
 * @param int $company_id
 * @param string $leave_type
 * @return bool True if valid
 */
function isValidLeaveType($mysqli, $company_id, $leave_type)
{
    $result = query(
        $mysqli,
        "SELECT id FROM leave_policies 
         WHERE company_id = ? AND leave_type = ?",
        [$company_id, $leave_type]
    );

    return $result['success'] && !empty($result['data']);
}

/**
 * Get leave policy for type
 * 
 * @param mysqli $mysqli Database connection
 * @param int $company_id
 * @param string $leave_type
 * @return array|null Policy data or null
 */
function getLeavePolicy($mysqli, $company_id, $leave_type)
{
    $result = query(
        $mysqli,
        "SELECT * FROM leave_policies 
         WHERE company_id = ? AND leave_type = ?",
        [$company_id, $leave_type]
    );

    return $result['success'] && !empty($result['data'])
        ? $result['data'][0]
        : null;
}

/**
 * Calculate days between dates
 * 
 * @param string $start_date YYYY-MM-DD format
 * @param string $end_date YYYY-MM-DD format
 * @return int Number of days (inclusive)
 */
function getDateDifference($start_date, $end_date)
{
    $start = new DateTime($start_date);
    $end = new DateTime($end_date);
    return (int) $start->diff($end)->format('%a') + 1;
}

/**
 * Check if date is in past
 * 
 * @param string $date YYYY-MM-DD format
 * @return bool True if in past
 */
function isDateInPast($date)
{
    return $date < date('Y-m-d');
}

/**
 * Get company's Saturday policy
 * 
 * @param mysqli $mysqli Database connection
 * @param int $company_id
 * @return string Policy: 'none', '1st_3rd', '2nd_4th', 'all'
 */
function getCompanySaturdayPolicy($mysqli, $company_id)
{
    $result = query(
        $mysqli,
        "SELECT saturday_policy FROM company_holiday_settings WHERE company_id = ?",
        [$company_id]
    );

    return $result['success'] && !empty($result['data'])
        ? $result['data'][0]['saturday_policy']
        : 'none';
}

/**
 * Get company holidays
 * 
 * @param mysqli $mysqli Database connection
 * @param int $company_id
 * @return array Array of holiday dates (YYYY-MM-DD format)
 */
function getCompanyHolidays($mysqli, $company_id)
{
    $result = query(
        $mysqli,
        "SELECT holiday_date FROM holidays WHERE company_id = ?",
        [$company_id]
    );

    return array_column($result['data'] ?? [], 'holiday_date');
}

/**
 * Get current leave balance for employee
 * 
 * @param mysqli $mysqli Database connection
 * @param int $employee_id
 * @param string $leave_type
 * @param int $company_id
 * @return int Remaining balance
 */
function getLeaveBalance($mysqli, $employee_id, $leave_type, $company_id)
{
    $policy = getLeavePolicy($mysqli, $company_id, $leave_type);

    if (!$policy) {
        return 0;
    }

    $total_allowed = $policy['days_per_year'];

    // Get used days
    $used_result = query(
        $mysqli,
        "SELECT SUM(DATEDIFF(end_date, start_date) + 1) as days_used 
         FROM leaves 
         WHERE employee_id = ? 
           AND leave_type = ? 
           AND status = 'approved' 
           AND YEAR(start_date) = YEAR(CURDATE())",
        [$employee_id, $leave_type]
    );

    $days_used = 0;
    if ($used_result['success'] && !empty($used_result['data'])) {
        $days_used = (int) ($used_result['data'][0]['days_used'] ?? 0);
    }

    return max(0, $total_allowed - $days_used);
}

/**
 * Check if employee has sufficient leave balance
 * 
 * @param mysqli $mysqli Database connection
 * @param int $employee_id
 * @param string $leave_type
 * @param string $start_date
 * @param string $end_date
 * @param int $company_id
 * @return array ['sufficient' => bool, 'balance' => int, 'required' => int]
 */
function checkLeaveBalance($mysqli, $employee_id, $leave_type, $start_date, $end_date, $company_id)
{
    $balance = getLeaveBalance($mysqli, $employee_id, $leave_type, $company_id);
    $required = getDateDifference($start_date, $end_date);

    return [
        'sufficient' => $balance >= $required,
        'balance' => $balance,
        'required' => $required
    ];
}

/**
 * Get approver information
 * 
 * @param mysqli $mysqli Database connection
 * @param int $user_id User ID of approver
 * @return array|null Array with first_name, last_name, role_id or null
 */
function getApproverInfo($mysqli, $user_id)
{
    if (!$user_id)
        return null;

    $result = query(
        $mysqli,
        "SELECT e.first_name, e.last_name, u.role_id 
         FROM employees e 
         JOIN users u ON e.user_id = u.id 
         WHERE u.id = ?",
        [$user_id]
    );

    return $result['success'] && !empty($result['data'])
        ? $result['data'][0]
        : null;
}

/**
 * Log leave action (audit trail)
 * Optional: requires audit_log table
 * 
 * @param mysqli $mysqli Database connection
 * @param int $leave_id
 * @param int $user_id
 * @param string $action 'apply', 'approve', 'reject', 'cancel'
 * @param string $notes Optional notes
 * @return bool Success
 */
function logLeaveAction($mysqli, $leave_id, $user_id, $action, $notes = '')
{
    // Only log if audit_log table exists (optional feature)
    // You can extend this table schema if needed

    $result = query(
        $mysqli,
        "INSERT INTO audit_log (leave_id, user_id, action, notes, created_at) 
         VALUES (?, ?, ?, ?, NOW())",
        [$leave_id, $user_id, $action, $notes]
    );

    return $result['success'] ?? false;
}

/**
 * Get all leaves for manager's team
 * 
 * @param mysqli $mysqli Database connection
 * @param int $manager_user_id
 * @param int $company_id
 * @param string $status Filter by status (optional)
 * @return array Array of leave records
 */
function getTeamLeaves($mysqli, $manager_user_id, $company_id, $status = null)
{
    $manager_emp = query(
        $mysqli,
        "SELECT id, department_id FROM employees WHERE user_id = ?",
        [$manager_user_id]
    )['data'][0] ?? null;

    if (!$manager_emp) {
        return [];
    }

    $sql = "SELECT l.* FROM leaves l
            JOIN employees e ON l.employee_id = e.id
            JOIN users u ON e.user_id = u.id
            WHERE u.company_id = ? 
              AND e.department_id = ?
              AND e.user_id != ?";

    $params = [$company_id, $manager_emp['department_id'], $manager_user_id];

    if ($status) {
        $sql .= " AND l.status = ?";
        $params[] = $status;
    }

    $sql .= " ORDER BY l.applied_at DESC";

    return query($mysqli, $sql, $params)['data'] ?? [];
}

/**
 * Get all leaves for HR
 * 
 * @param mysqli $mysqli Database connection
 * @param int $company_id
 * @param string $status Filter by status (optional)
 * @param array $employee_roles Filter by employee roles (optional)
 * @return array Array of leave records
 */
function getAllLeavesForHR($mysqli, $company_id, $status = null, $employee_roles = [4, 6])
{
    $sql = "SELECT l.* FROM leaves l
            JOIN employees e ON l.employee_id = e.id
            JOIN users u ON e.user_id = u.id
            WHERE u.company_id = ?
              AND u.role_id IN (" . implode(',', array_fill(0, count($employee_roles), '?')) . ")";

    $params = [$company_id, ...$employee_roles];

    if ($status) {
        $sql .= " AND l.status = ?";
        $params[] = $status;
    }

    $sql .= " ORDER BY l.applied_at DESC";

    return query($mysqli, $sql, $params)['data'] ?? [];
}

/**
 * Get all HR leaves for Company Owner
 * 
 * @param mysqli $mysqli Database connection
 * @param int $company_id
 * @param string $status Filter by status (optional)
 * @return array Array of leave records
 */
function getHRLeavesForOwner($mysqli, $company_id, $status = null)
{
    $sql = "SELECT l.* FROM leaves l
            JOIN employees e ON l.employee_id = e.id
            JOIN users u ON e.user_id = u.id
            WHERE u.company_id = ? AND u.role_id = 3"; // HR only

    $params = [$company_id];

    if ($status) {
        $sql .= " AND l.status = ?";
        $params[] = $status;
    }

    $sql .= " ORDER BY l.applied_at DESC";

    return query($mysqli, $sql, $params)['data'] ?? [];
}

/**
 * Validate leave dates
 * 
 * @param string $start_date YYYY-MM-DD
 * @param string $end_date YYYY-MM-DD
 * @return array ['valid' => bool, 'error' => string|null]
 */
function validateLeaveDates($start_date, $end_date)
{
    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $start_date) || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $end_date)) {
        return ['valid' => false, 'error' => 'Invalid date format'];
    }

    if ($end_date < $start_date) {
        return ['valid' => false, 'error' => 'End date cannot be before start date'];
    }

    if (isDateInPast($start_date)) {
        return ['valid' => false, 'error' => 'Start date cannot be in the past'];
    }

    return ['valid' => true, 'error' => null];
}

/**
 * Get leave request details with full information
 * 
 * @param mysqli $mysqli Database connection
 * @param int $leave_id
 * @return array|null Leave record with enriched data
 */
function getLeaveDetailsFullInfo($mysqli, $leave_id)
{
    $result = query(
        $mysqli,
        "SELECT l.*, 
                e.first_name as emp_first_name, e.last_name as emp_last_name,
                u.role_id as emp_role_id
         FROM leaves l
         JOIN employees e ON l.employee_id = e.id
         JOIN users u ON e.user_id = u.id
         WHERE l.id = ?",
        [$leave_id]
    );

    if (!$result['success'] || empty($result['data'])) {
        return null;
    }

    $leave = $result['data'][0];

    // Add approver info if approved
    if ($leave['approved_by']) {
        $leave['approver_info'] = getApproverInfo($mysqli, $leave['approved_by']);
    }

    return $leave;
}
?>