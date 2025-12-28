<?php
/**
 * QueryHandler.php
 * NexusBot Query Handler - Executes database queries with security filters
 * 
 * Handles all data retrieval with role-based access control
 */

require_once __DIR__ . '/SecurityFilter.php';
require_once __DIR__ . '/KnowledgeBase.php';

class QueryHandler
{
    private $mysqli;
    private $security;
    private $knowledge;
    private $userId;
    private $companyId;
    private $employeeId;

    public function __construct($mysqli, SecurityFilter $security)
    {
        $this->mysqli = $mysqli;
        $this->security = $security;
        $this->knowledge = new KnowledgeBase();
        $this->employeeId = $security->getEmployeeId();
        $this->companyId = $security->getCompanyId();
    }

    /**
     * Get user's attendance data
     */
    public function getAttendance(array $context, string $subIntent): array
    {
        // Security check for non-self queries
        if ($subIntent !== 'self' && $this->security->canAccessOwnDataOnly()) {
            return [
                'success' => false,
                'message' => $this->security->getAccessDeniedMessage('attendance')
            ];
        }

        $timeFilter = $this->getTimeFilter($context['time_context'] ?? 'this_month');

        if ($subIntent === 'self') {
            return $this->getOwnAttendance($timeFilter);
        } elseif ($subIntent === 'team' && $this->security->canAccessTeamData()) {
            return $this->getTeamAttendance($timeFilter);
        } elseif ($this->security->canAccessCompanyData()) {
            return $this->getAllAttendance($timeFilter);
        }

        return [
            'success' => false,
            'message' => $this->security->getAccessDeniedMessage('attendance')
        ];
    }

    /**
     * Get own attendance
     */
    private function getOwnAttendance(array $timeFilter): array
    {
        if (!$this->employeeId) {
            return ['success' => false, 'message' => 'Employee profile not found.'];
        }

        $sql = "SELECT date, check_in, check_out, status, remarks 
                FROM attendance 
                WHERE employee_id = ? 
                AND date BETWEEN ? AND ?
                ORDER BY date DESC
                LIMIT 30";

        $stmt = $this->mysqli->prepare($sql);
        $stmt->bind_param("iss", $this->employeeId, $timeFilter['start'], $timeFilter['end']);
        $stmt->execute();
        $result = $stmt->get_result();
        $records = $result->fetch_all(MYSQLI_ASSOC);
        $stmt->close();

        if (empty($records)) {
            return [
                'success' => true,
                'data' => [],
                'message' => $this->knowledge->getNoDataResponse('attendance')
            ];
        }

        // Calculate summary
        $summary = $this->calculateAttendanceSummary($records);

        return [
            'success' => true,
            'data' => $records,
            'summary' => $summary,
            'message' => $this->formatAttendanceResponse($records, $summary, $timeFilter)
        ];
    }

    /**
     * Get team attendance (for managers)
     */
    private function getTeamAttendance(array $timeFilter): array
    {
        $sql = "SELECT e.first_name, e.last_name, a.date, a.status
                FROM attendance a
                INNER JOIN employees e ON a.employee_id = e.id
                INNER JOIN team_members tm ON e.id = tm.employee_id
                INNER JOIN teams t ON tm.team_id = t.id
                WHERE t.created_by = ? AND t.company_id = ?
                AND a.date BETWEEN ? AND ?
                ORDER BY a.date DESC, e.first_name
                LIMIT 50";

        $stmt = $this->mysqli->prepare($sql);
        $stmt->bind_param("iiss", $this->userId, $this->companyId, $timeFilter['start'], $timeFilter['end']);
        $stmt->execute();
        $result = $stmt->get_result();
        $records = $result->fetch_all(MYSQLI_ASSOC);
        $stmt->close();

        if (empty($records)) {
            return [
                'success' => true,
                'data' => [],
                'message' => "No team attendance records found for the specified period."
            ];
        }

        return [
            'success' => true,
            'data' => $records,
            'message' => $this->formatTeamAttendanceResponse($records)
        ];
    }

    /**
     * Get all company attendance (for HR/Admin)
     */
    private function getAllAttendance(array $timeFilter): array
    {
        $sql = "SELECT e.first_name, e.last_name, e.employee_code, a.date, a.status
                FROM attendance a
                INNER JOIN employees e ON a.employee_id = e.id
                INNER JOIN users u ON e.user_id = u.id
                WHERE u.company_id = ?
                AND a.date BETWEEN ? AND ?
                ORDER BY a.date DESC, e.first_name
                LIMIT 100";

        $stmt = $this->mysqli->prepare($sql);
        $stmt->bind_param("iss", $this->companyId, $timeFilter['start'], $timeFilter['end']);
        $stmt->execute();
        $result = $stmt->get_result();
        $records = $result->fetch_all(MYSQLI_ASSOC);
        $stmt->close();

        return [
            'success' => true,
            'data' => $records,
            'message' => "Found " . count($records) . " attendance records for the company."
        ];
    }

    /**
     * Get leave balance
     */
    public function getLeaveBalance(array $context, string $subIntent): array
    {
        if ($subIntent !== 'self' && $this->security->canAccessOwnDataOnly()) {
            return [
                'success' => false,
                'message' => $this->security->getAccessDeniedMessage('leave balance')
            ];
        }

        if (!$this->employeeId) {
            return ['success' => false, 'message' => 'Employee profile not found.'];
        }

        // Get leave policies for company
        $policySql = "SELECT lp.id, lp.leave_type, lp.days_per_year
                      FROM leave_policies lp
                      INNER JOIN users u ON lp.company_id = u.company_id
                      INNER JOIN employees e ON u.id = e.user_id
                      WHERE e.id = ?";

        $stmt = $this->mysqli->prepare($policySql);
        $stmt->bind_param("i", $this->employeeId);
        $stmt->execute();
        $policies = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();

        // Get leaves taken this year
        $currentYear = date('Y');
        $usedSql = "SELECT leave_type, SUM(DATEDIFF(end_date, start_date) + 1) as used_days
                    FROM leaves
                    WHERE employee_id = ? 
                    AND status = 'approved'
                    AND YEAR(start_date) = ?
                    GROUP BY leave_type";

        $stmt = $this->mysqli->prepare($usedSql);
        $stmt->bind_param("ii", $this->employeeId, $currentYear);
        $stmt->execute();
        $usedResult = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();

        $usedMap = [];
        foreach ($usedResult as $used) {
            $usedMap[$used['leave_type']] = $used['used_days'];
        }

        // Calculate balance
        $balance = [];
        foreach ($policies as $policy) {
            $used = $usedMap[$policy['leave_type']] ?? 0;
            $balance[] = [
                'type' => $policy['leave_type'],
                'total' => $policy['days_per_year'],
                'used' => $used,
                'remaining' => $policy['days_per_year'] - $used
            ];
        }

        if (empty($balance)) {
            return [
                'success' => true,
                'data' => [],
                'message' => $this->knowledge->getNoDataResponse('leave_balance')
            ];
        }

        return [
            'success' => true,
            'data' => $balance,
            'message' => $this->formatLeaveBalanceResponse($balance)
        ];
    }

    /**
     * Get leave requests
     */
    public function getLeaves(array $context, string $subIntent): array
    {
        if ($subIntent !== 'self' && $this->security->canAccessOwnDataOnly()) {
            return [
                'success' => false,
                'message' => $this->security->getAccessDeniedMessage('leave requests')
            ];
        }

        if (!$this->employeeId) {
            return ['success' => false, 'message' => 'Employee profile not found.'];
        }

        $sql = "SELECT l.id, l.leave_type, l.start_date, l.end_date, l.reason, l.status, l.applied_at
                FROM leaves l
                WHERE l.employee_id = ?
                ORDER BY l.applied_at DESC
                LIMIT 10";

        $stmt = $this->mysqli->prepare($sql);
        $stmt->bind_param("i", $this->employeeId);
        $stmt->execute();
        $leaves = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();

        if (empty($leaves)) {
            return [
                'success' => true,
                'data' => [],
                'message' => $this->knowledge->getNoDataResponse('leave')
            ];
        }

        return [
            'success' => true,
            'data' => $leaves,
            'message' => $this->formatLeavesResponse($leaves)
        ];
    }

    /**
     * Get payslip information
     */
    public function getPayslips(array $context, string $subIntent): array
    {
        if ($subIntent !== 'self' && $this->security->canAccessOwnDataOnly()) {
            return [
                'success' => false,
                'message' => $this->security->getAccessDeniedMessage('payslip')
            ];
        }

        if (!$this->employeeId) {
            return ['success' => false, 'message' => 'Employee profile not found.'];
        }

        $sql = "SELECT period, currency, gross_salary, net_salary, status, generated_at
                FROM payslips
                WHERE employee_id = ?
                ORDER BY period DESC
                LIMIT 6";

        $stmt = $this->mysqli->prepare($sql);
        $stmt->bind_param("i", $this->employeeId);
        $stmt->execute();
        $payslips = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();

        if (empty($payslips)) {
            return [
                'success' => true,
                'data' => [],
                'message' => $this->knowledge->getNoDataResponse('payslip')
            ];
        }

        // Filter out any sensitive data
        $payslips = $this->security->filterCredentialFields($payslips);

        return [
            'success' => true,
            'data' => $payslips,
            'message' => $this->formatPayslipsResponse($payslips)
        ];
    }

    /**
     * Get user profile
     */
    public function getProfile(array $context, string $subIntent): array
    {
        if ($subIntent !== 'self' && $this->security->canAccessOwnDataOnly()) {
            return [
                'success' => false,
                'message' => $this->security->getAccessDeniedMessage('profile')
            ];
        }

        $sql = "SELECT e.employee_code, e.first_name, e.last_name, e.dob, e.gender, e.contact,
                       e.date_of_joining, e.status,
                       d.name as department, des.name as designation, s.name as shift,
                       s.start_time as shift_start, s.end_time as shift_end,
                       u.email, u.username
                FROM employees e
                INNER JOIN users u ON e.user_id = u.id
                LEFT JOIN departments d ON e.department_id = d.id
                LEFT JOIN designations des ON e.designation_id = des.id
                LEFT JOIN shifts s ON e.shift_id = s.id
                WHERE e.user_id = ?";

        $stmt = $this->mysqli->prepare($sql);
        $stmt->bind_param("i", $this->userId);
        $stmt->execute();
        $result = $stmt->get_result();
        $profile = $result->fetch_assoc();
        $stmt->close();

        if (!$profile) {
            return [
                'success' => false,
                'message' => 'Profile not found. Please contact HR.'
            ];
        }

        // Filter credentials
        $profile = $this->security->filterCredentialFields($profile);

        return [
            'success' => true,
            'data' => $profile,
            'message' => $this->formatProfileResponse($profile)
        ];
    }

    /**
     * Get tasks
     */
    public function getTasks(array $context, string $subIntent): array
    {
        if ($subIntent !== 'self' && $this->security->canAccessOwnDataOnly()) {
            return [
                'success' => false,
                'message' => $this->security->getAccessDeniedMessage('tasks')
            ];
        }

        if (!$this->employeeId) {
            return ['success' => false, 'message' => 'Employee profile not found.'];
        }

        $sql = "SELECT t.title, t.description, t.due_date, t.status, t.created_at,
                       CONCAT(e.first_name, ' ', e.last_name) as assigned_by_name
                FROM tasks t
                LEFT JOIN users u ON t.assigned_by = u.id
                LEFT JOIN employees e ON u.id = e.user_id
                WHERE t.employee_id = ?
                ORDER BY t.due_date ASC, t.created_at DESC
                LIMIT 10";

        $stmt = $this->mysqli->prepare($sql);
        $stmt->bind_param("i", $this->employeeId);
        $stmt->execute();
        $tasks = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();

        if (empty($tasks)) {
            return [
                'success' => true,
                'data' => [],
                'message' => $this->knowledge->getNoDataResponse('task')
            ];
        }

        return [
            'success' => true,
            'data' => $tasks,
            'message' => $this->formatTasksResponse($tasks)
        ];
    }

    /**
     * Get holidays
     */
    public function getHolidays(array $context): array
    {
        $today = date('Y-m-d');
        $timeContext = $context['time_context'] ?? null;
        $timeFilter = $timeContext ? $this->getTimeFilter($timeContext) : ['start' => $today, 'end' => date('Y-12-31')];

        $sql = "SELECT holiday_name, holiday_date
                FROM holidays
                WHERE company_id = ? AND holiday_date BETWEEN ? AND ?
                ORDER BY holiday_date ASC
                LIMIT 10";

        $stmt = $this->mysqli->prepare($sql);
        $stmt->bind_param("iss", $this->companyId, $timeFilter['start'], $timeFilter['end']);
        $stmt->execute();
        $holidays = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();

        if (empty($holidays)) {
            return [
                'success' => true,
                'data' => [],
                'message' => $this->knowledge->getNoDataResponse('holiday')
            ];
        }

        return [
            'success' => true,
            'data' => $holidays,
            'message' => $this->formatHolidaysResponse($holidays)
        ];
    }

    /**
     * Get shift information
     */
    public function getShift(array $context): array
    {
        $sql = "SELECT s.name as shift_name, s.start_time, s.end_time, s.description
                FROM shifts s
                INNER JOIN employees e ON e.shift_id = s.id
                WHERE e.user_id = ?";

        $stmt = $this->mysqli->prepare($sql);
        $stmt->bind_param("i", $this->userId);
        $stmt->execute();
        $shift = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if (!$shift) {
            return [
                'success' => true,
                'data' => [],
                'message' => 'No shift assigned. Please contact HR.'
            ];
        }

        return [
            'success' => true,
            'data' => $shift,
            'message' => $this->formatShiftResponse($shift)
        ];
    }

    /**
     * Get performance records
     */
    public function getPerformance(array $context, string $subIntent): array
    {
        if ($subIntent !== 'self' && $this->security->canAccessOwnDataOnly()) {
            return [
                'success' => false,
                'message' => $this->security->getAccessDeniedMessage('performance')
            ];
        }

        if (!$this->employeeId) {
            return ['success' => false, 'message' => 'Employee profile not found.'];
        }

        $sql = "SELECT p.period, p.score, p.remarks, p.created_at,
                       CONCAT(e.first_name, ' ', e.last_name) as evaluator_name
                FROM performance p
                LEFT JOIN users u ON p.evaluator_id = u.id
                LEFT JOIN employees e ON u.id = e.user_id
                WHERE p.employee_id = ?
                ORDER BY p.period DESC
                LIMIT 6";

        $stmt = $this->mysqli->prepare($sql);
        $stmt->bind_param("i", $this->employeeId);
        $stmt->execute();
        $records = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();

        if (empty($records)) {
            return [
                'success' => true,
                'data' => [],
                'message' => $this->knowledge->getNoDataResponse('performance')
            ];
        }

        return [
            'success' => true,
            'data' => $records,
            'message' => $this->formatPerformanceResponse($records)
        ];
    }

    /**
     * Get team members (for managers)
     */
    public function getTeam(array $context): array
    {
        if ($this->security->canAccessOwnDataOnly()) {
            return [
                'success' => false,
                'message' => $this->security->getAccessDeniedMessage('team data')
            ];
        }

        $sql = "SELECT e.first_name, e.last_name, e.employee_code, des.name as designation
                FROM team_members tm
                INNER JOIN employees e ON tm.employee_id = e.id
                INNER JOIN teams t ON tm.team_id = t.id
                LEFT JOIN designations des ON e.designation_id = des.id
                WHERE t.created_by = ? AND t.company_id = ?";

        $stmt = $this->mysqli->prepare($sql);
        $stmt->bind_param("ii", $this->userId, $this->companyId);
        $stmt->execute();
        $members = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();

        if (empty($members)) {
            return [
                'success' => true,
                'data' => [],
                'message' => $this->knowledge->getNoDataResponse('team')
            ];
        }

        return [
            'success' => true,
            'data' => $members,
            'message' => $this->formatTeamResponse($members)
        ];
    }

    // ============ Helper Methods ============

    /**
     * Get time filter based on context
     */
    private function getTimeFilter(?string $timeContext): array
    {
        $today = date('Y-m-d');

        switch ($timeContext) {
            case 'today':
                return ['start' => $today, 'end' => $today, 'label' => 'today'];
            case 'yesterday':
                $yesterday = date('Y-m-d', strtotime('-1 day'));
                return ['start' => $yesterday, 'end' => $yesterday, 'label' => 'yesterday'];
            case 'this_week':
                $start = date('Y-m-d', strtotime('monday this week'));
                return ['start' => $start, 'end' => $today, 'label' => 'this week'];
            case 'last_week':
                $start = date('Y-m-d', strtotime('monday last week'));
                $end = date('Y-m-d', strtotime('sunday last week'));
                return ['start' => $start, 'end' => $end, 'label' => 'last week'];
            case 'last_month':
                $start = date('Y-m-01', strtotime('first day of last month'));
                $end = date('Y-m-t', strtotime('last day of last month'));
                return ['start' => $start, 'end' => $end, 'label' => 'last month'];
            case 'this_year':
                $start = date('Y-01-01');
                return ['start' => $start, 'end' => $today, 'label' => 'this year'];
            case 'next_month':
                $start = date('Y-m-01', strtotime('+1 month'));
                $end = date('Y-m-t', strtotime('+1 month'));
                return ['start' => $start, 'end' => $end, 'label' => 'next month'];
            case 'this_month':
            default:
                $start = date('Y-m-01');
                return ['start' => $start, 'end' => $today, 'label' => 'this month'];
        }
    }

    /**
     * Calculate attendance summary
     */
    private function calculateAttendanceSummary(array $records): array
    {
        $summary = [
            'total' => count($records),
            'present' => 0,
            'absent' => 0,
            'leave' => 0,
            'half_day' => 0,
            'holiday' => 0
        ];

        foreach ($records as $record) {
            $status = strtolower($record['status']);
            if (isset($summary[$status])) {
                $summary[$status]++;
            } elseif ($status === 'half-day') {
                $summary['half_day']++;
            }
        }

        return $summary;
    }

    // ============ Response Formatters ============

    private function formatAttendanceResponse(array $records, array $summary, array $timeFilter): string
    {
        $response = "📅 **Your Attendance ({$timeFilter['label']})**\n\n";
        $response .= "**Summary:**\n";
        $response .= "• ✅ Present: {$summary['present']} days\n";
        $response .= "• ❌ Absent: {$summary['absent']} days\n";
        $response .= "• 🏖️ Leave: {$summary['leave']} days\n";
        $response .= "• ⏰ Half-day: {$summary['half_day']} days\n";

        if (count($records) <= 5) {
            $response .= "\n**Details:**\n";
            foreach ($records as $rec) {
                $emoji = $this->knowledge->getAttendanceEmoji($rec['status']);
                $date = $this->knowledge->formatDate($rec['date'], 'D, d M');
                $response .= "{$emoji} {$date}: " . ucfirst($rec['status']) . "\n";
            }
        }

        return $response;
    }

    private function formatTeamAttendanceResponse(array $records): string
    {
        $response = "👥 **Team Attendance**\n\n";

        $byDate = [];
        foreach ($records as $rec) {
            $date = $rec['date'];
            if (!isset($byDate[$date])) {
                $byDate[$date] = [];
            }
            $byDate[$date][] = $rec;
        }

        foreach (array_slice($byDate, 0, 3, true) as $date => $recs) {
            $response .= "**" . $this->knowledge->formatDate($date, 'D, d M') . ":**\n";
            foreach ($recs as $rec) {
                $emoji = $this->knowledge->getAttendanceEmoji($rec['status']);
                $response .= "  {$emoji} {$rec['first_name']} {$rec['last_name']}: " . ucfirst($rec['status']) . "\n";
            }
        }

        return $response;
    }

    private function formatLeaveBalanceResponse(array $balance): string
    {
        $response = "🏖️ **Your Leave Balance**\n\n";

        foreach ($balance as $leave) {
            $response .= "**{$leave['type']}:**\n";
            $response .= "  • Total: {$leave['total']} days\n";
            $response .= "  • Used: {$leave['used']} days\n";
            $response .= "  • Remaining: **{$leave['remaining']} days**\n\n";
        }

        return $response;
    }

    private function formatLeavesResponse(array $leaves): string
    {
        $response = "📋 **Your Leave History**\n\n";

        foreach ($leaves as $leave) {
            $emoji = $this->knowledge->getLeaveStatusEmoji($leave['status']);
            $startDate = $this->knowledge->formatDate($leave['start_date'], 'd M');
            $endDate = $this->knowledge->formatDate($leave['end_date'], 'd M Y');

            $response .= "{$emoji} **{$leave['leave_type']}**\n";
            $response .= "   {$startDate} - {$endDate}\n";
            $response .= "   Status: " . ucfirst($leave['status']) . "\n\n";
        }

        return $response;
    }

    private function formatPayslipsResponse(array $payslips): string
    {
        $response = "💰 **Your Payslips**\n\n";

        foreach ($payslips as $slip) {
            $period = $this->knowledge->formatDate($slip['period'] . '-01', 'M Y');
            $netSalary = $this->knowledge->formatCurrency($slip['net_salary'], $slip['currency']);

            $response .= "**{$period}**\n";
            $response .= "  • Net Salary: {$netSalary}\n";
            $response .= "  • Status: " . ucfirst($slip['status']) . "\n\n";
        }

        $response .= "_View detailed payslips in the Payroll section._";

        return $response;
    }

    private function formatProfileResponse(array $profile): string
    {
        $response = "👤 **Your Profile**\n\n";

        $fullName = trim(($profile['first_name'] ?? '') . ' ' . ($profile['last_name'] ?? ''));
        $response .= "**Name:** {$fullName}\n";

        if (!empty($profile['employee_code'])) {
            $response .= "**Employee Code:** {$profile['employee_code']}\n";
        }
        if (!empty($profile['email'])) {
            $response .= "**Email:** {$profile['email']}\n";
        }
        if (!empty($profile['department'])) {
            $response .= "**Department:** {$profile['department']}\n";
        }
        if (!empty($profile['designation'])) {
            $response .= "**Designation:** {$profile['designation']}\n";
        }
        if (!empty($profile['shift'])) {
            $response .= "**Shift:** {$profile['shift']} ({$profile['shift_start']} - {$profile['shift_end']})\n";
        }
        if (!empty($profile['date_of_joining'])) {
            $response .= "**Joined:** " . $this->knowledge->formatDate($profile['date_of_joining']) . "\n";
        }

        return $response;
    }

    private function formatTasksResponse(array $tasks): string
    {
        $response = "📋 **Your Tasks**\n\n";

        foreach ($tasks as $task) {
            $emoji = $this->knowledge->getTaskStatusEmoji($task['status']);
            $dueDate = $task['due_date'] ? $this->knowledge->formatDate($task['due_date'], 'd M Y') : 'No due date';

            $response .= "{$emoji} **{$task['title']}**\n";
            $response .= "   Due: {$dueDate}\n";
            $response .= "   Status: " . ucfirst(str_replace('_', ' ', $task['status'])) . "\n";
            if (!empty($task['assigned_by_name'])) {
                $response .= "   Assigned by: {$task['assigned_by_name']}\n";
            }
            $response .= "\n";
        }

        return $response;
    }

    private function formatHolidaysResponse(array $holidays): string
    {
        $response = "📆 **Upcoming Holidays**\n\n";

        foreach ($holidays as $holiday) {
            $date = $this->knowledge->formatDate($holiday['holiday_date'], 'D, d M Y');
            $response .= "🎉 **{$holiday['holiday_name']}**\n";
            $response .= "   {$date}\n\n";
        }

        return $response;
    }

    private function formatShiftResponse(array $shift): string
    {
        $response = "⏰ **Your Shift Information**\n\n";
        $response .= "**Shift:** {$shift['shift_name']}\n";
        $response .= "**Start Time:** {$shift['start_time']}\n";
        $response .= "**End Time:** {$shift['end_time']}\n";

        if (!empty($shift['description'])) {
            $response .= "**Note:** {$shift['description']}\n";
        }

        return $response;
    }

    private function formatPerformanceResponse(array $records): string
    {
        $response = "📊 **Your Performance Reviews**\n\n";

        foreach ($records as $rec) {
            $period = $this->knowledge->formatDate($rec['period'] . '-01', 'M Y');
            $response .= "**{$period}**\n";
            $response .= "  • Score: {$rec['score']}/100\n";
            if (!empty($rec['remarks'])) {
                $response .= "  • Remarks: {$rec['remarks']}\n";
            }
            if (!empty($rec['evaluator_name'])) {
                $response .= "  • Reviewed by: {$rec['evaluator_name']}\n";
            }
            $response .= "\n";
        }

        return $response;
    }

    private function formatTeamResponse(array $members): string
    {
        $response = "👥 **Your Team Members**\n\n";

        foreach ($members as $member) {
            $response .= "• **{$member['first_name']} {$member['last_name']}**";
            if (!empty($member['employee_code'])) {
                $response .= " ({$member['employee_code']})";
            }
            if (!empty($member['designation'])) {
                $response .= " - {$member['designation']}";
            }
            $response .= "\n";
        }

        return $response;
    }
}
?>