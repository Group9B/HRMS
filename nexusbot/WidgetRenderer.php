<?php
/**
 * WidgetRenderer.php
 * Generates structured widget data for rich bot responses
 */

class WidgetRenderer
{
    private $mysqli;
    private $employeeId;
    private $userId;

    public function __construct($mysqli, $employeeId, $userId)
    {
        $this->mysqli = $mysqli;
        $this->employeeId = $employeeId;
        $this->userId = $userId;
    }

    /**
     * Render attendance widget
     */
    public function attendanceWidget(): array
    {
        $today = date('Y-m-d');
        $empId = $this->employeeId;

        // Get today's attendance
        $query = "SELECT check_in, check_out, status FROM attendance WHERE employee_id = ? AND date = ?";
        $stmt = $this->mysqli->prepare($query);
        $stmt->bind_param("is", $empId, $today);
        $stmt->execute();
        $result = $stmt->get_result();
        $todayData = $result->fetch_assoc();

        // Get weekly summary
        $weekStart = date('Y-m-d', strtotime('monday this week'));
        $weekQuery = "SELECT COUNT(*) as days_present, 
                      SUM(TIMESTAMPDIFF(HOUR, check_in, IFNULL(check_out, NOW()))) as total_hours
                      FROM attendance 
                      WHERE employee_id = ? AND date >= ? AND date <= ?";
        $stmt2 = $this->mysqli->prepare($weekQuery);
        $stmt2->bind_param("iss", $empId, $weekStart, $today);
        $stmt2->execute();
        $weekData = $stmt2->get_result()->fetch_assoc();

        return [
            'type' => 'attendance',
            'today' => [
                'status' => $todayData ? ($todayData['check_out'] ? 'completed' : 'active') : 'not_started',
                'check_in' => $todayData['check_in'] ?? null,
                'check_out' => $todayData['check_out'] ?? null
            ],
            'week' => [
                'days_present' => (int) ($weekData['days_present'] ?? 0),
                'total_hours' => round($weekData['total_hours'] ?? 0, 1)
            ],
            'actions' => $this->getAttendanceActions($todayData)
        ];
    }

    private function getAttendanceActions($todayData): array
    {
        if (!$todayData) {
            return [['label' => 'Clock In', 'action' => 'clock_in', 'style' => 'primary']];
        }
        if ($todayData['check_in'] && !$todayData['check_out']) {
            return [['label' => 'Clock Out', 'action' => 'clock_out', 'style' => 'warning']];
        }
        return [];
    }

    /**
     * Render leave balance widget
     */
    public function leaveWidget(): array
    {
        $empId = $this->employeeId;
        $year = date('Y');

        $query = "SELECT lt.name, lb.total_days, lb.used_days, (lb.total_days - lb.used_days) as remaining
                  FROM leave_balances lb
                  JOIN leave_types lt ON lb.leave_type_id = lt.id
                  WHERE lb.employee_id = ? AND lb.year = ?";
        $stmt = $this->mysqli->prepare($query);
        $stmt->bind_param("ii", $empId, $year);
        $stmt->execute();
        $result = $stmt->get_result();

        $balances = [];
        while ($row = $result->fetch_assoc()) {
            $balances[] = [
                'type' => $row['name'],
                'total' => (int) $row['total_days'],
                'used' => (int) $row['used_days'],
                'remaining' => (int) $row['remaining'],
                'percentage' => $row['total_days'] > 0 ? round(($row['used_days'] / $row['total_days']) * 100) : 0
            ];
        }

        return [
            'type' => 'leave',
            'balances' => $balances,
            'actions' => [['label' => 'Apply Leave', 'action' => 'apply_leave', 'style' => 'primary']]
        ];
    }

    /**
     * Render tasks widget
     */
    public function tasksWidget(): array
    {
        $empId = $this->employeeId;

        $query = "SELECT id, title, priority, status, due_date 
                  FROM tasks 
                  WHERE assigned_to = ? AND status != 'completed'
                  ORDER BY FIELD(priority, 'high', 'medium', 'low'), due_date ASC
                  LIMIT 5";
        $stmt = $this->mysqli->prepare($query);
        $stmt->bind_param("i", $empId);
        $stmt->execute();
        $result = $stmt->get_result();

        $tasks = [];
        while ($row = $result->fetch_assoc()) {
            $tasks[] = [
                'id' => $row['id'],
                'title' => $row['title'],
                'priority' => $row['priority'],
                'status' => $row['status'],
                'due_date' => $row['due_date'],
                'overdue' => strtotime($row['due_date']) < strtotime('today')
            ];
        }

        return [
            'type' => 'tasks',
            'tasks' => $tasks,
            'total_pending' => count($tasks)
        ];
    }

    /**
     * Render team widget (for managers)
     */
    public function teamWidget(): array
    {
        $managerId = $this->employeeId;
        $today = date('Y-m-d');

        $query = "SELECT e.id, CONCAT(e.first_name, ' ', e.last_name) as name, 
                  d.name as department,
                  (SELECT check_in FROM attendance WHERE employee_id = e.id AND date = ? LIMIT 1) as today_checkin
                  FROM employees e
                  JOIN departments d ON e.department_id = d.id
                  JOIN team_members tm ON e.id = tm.employee_id
                  JOIN teams t ON tm.team_id = t.id
                  WHERE t.leader_id = ?
                  LIMIT 10";
        $stmt = $this->mysqli->prepare($query);
        $stmt->bind_param("si", $today, $managerId);
        $stmt->execute();
        $result = $stmt->get_result();

        $members = [];
        $presentCount = 0;
        while ($row = $result->fetch_assoc()) {
            $isPresent = !empty($row['today_checkin']);
            if ($isPresent)
                $presentCount++;
            $members[] = [
                'name' => $row['name'],
                'department' => $row['department'],
                'status' => $isPresent ? 'present' : 'absent'
            ];
        }

        return [
            'type' => 'team',
            'members' => $members,
            'summary' => [
                'total' => count($members),
                'present' => $presentCount,
                'absent' => count($members) - $presentCount
            ]
        ];
    }
}
