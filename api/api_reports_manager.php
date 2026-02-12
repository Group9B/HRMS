<?php
header('Content-Type: application/json');

require_once '../config/db.php';
require_once '../includes/functions.php';

// Security Check: Manager (6)
if (!isLoggedIn() || !isset($_SESSION['role_id']) || $_SESSION['role_id'] !== 6) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access.']);
    exit();
}

$user_id = $_SESSION['user_id'];

// Get manager's employee record to find their department
$manager_res = query($mysqli, "SELECT id, department_id FROM employees WHERE user_id = ?", [$user_id]);
if (!$manager_res['success'] || empty($manager_res['data'])) {
    echo json_encode(['success' => false, 'message' => 'Manager record not found.']);
    exit();
}
$manager = $manager_res['data'][0];
$manager_id = $manager['id'];
$dept_id = $manager['department_id'];

// Get team member IDs (direct department or team members)
$team_res = query($mysqli, "
    SELECT DISTINCT e.id 
    FROM employees e
    LEFT JOIN team_members tm ON e.id = tm.employee_id
    LEFT JOIN teams t ON tm.team_id = t.id
    WHERE (e.department_id = ? OR t.created_by = ?) 
    AND e.id != ? AND e.status = 'active'
", [$dept_id, $user_id, $manager_id]);

$team_member_ids = [];
if ($team_res['success']) {
    $team_member_ids = array_column($team_res['data'], 'id');
}

$id_list = !empty($team_member_ids) ? implode(',', array_map('intval', $team_member_ids)) : '0';

$response = [
    'success' => true,
    'data' => [
        'attendanceTrends' => ['labels' => [], 'data' => []],
        'taskVelocity' => [],
        'leaveImpact' => [],
        'performanceDist' => []
    ]
];

// 1. Team Attendance Trends (Last 15 days)
$labels = [];
$attendance_data = [];
for ($i = 14; $i >= 0; $i--) {
    $date = date('Y-m-d', strtotime("-$i days"));
    $labels[] = date('M d', strtotime($date));

    $presence_res = query($mysqli, "
        SELECT 
            COUNT(CASE WHEN status = 'present' THEN 1 END) as present,
            COUNT(*) as total
        FROM attendance 
        WHERE date = ? AND employee_id IN ($id_list)
    ", [$date]);

    $count = 0;
    if ($presence_res['success'] && $presence_res['data'][0]['total'] > 0) {
        $count = round(($presence_res['data'][0]['present'] / $presence_res['data'][0]['total']) * 100);
    }
    $attendance_data[] = $count;
}
$response['data']['attendanceTrends'] = ['labels' => $labels, 'data' => $attendance_data];

// 2. Task Velocity (Completed vs Pending per employee)
$task_res = query($mysqli, "
    SELECT 
        CONCAT(e.first_name, ' ', e.last_name) as name,
        COUNT(CASE WHEN t.status = 'completed' THEN 1 END) as completed,
        COUNT(CASE WHEN t.status != 'completed' THEN 1 END) as pending
    FROM employees e
    LEFT JOIN tasks t ON e.id = t.employee_id
    WHERE e.id IN ($id_list)
    GROUP BY e.id
    LIMIT 10
", []);
$response['data']['taskVelocity'] = $task_res['success'] ? $task_res['data'] : [];

// 3. Leave Impact (Next 30 days)
$leave_res = query($mysqli, "
    SELECT 
        leave_type as name,
        COUNT(*) as count
    FROM leaves 
    WHERE employee_id IN ($id_list) 
    AND start_date >= CURDATE() 
    AND start_date <= DATE_ADD(CURDATE(), INTERVAL 30 DAY)
    AND status = 'approved'
    GROUP BY leave_type
", []);
$response['data']['leaveImpact'] = $leave_res['success'] ? $leave_res['data'] : [];

// 4. Performance Distribution
$perf_res = query($mysqli, "
    SELECT 
        CASE 
            WHEN rating >= 90 THEN 'Exceeds'
            WHEN rating >= 70 THEN 'Meets'
            WHEN rating >= 50 THEN 'Needs Impr.'
            ELSE 'Poor'
        END as label,
        COUNT(*) as value
    FROM performance_reviews
    WHERE employee_id IN ($id_list)
    GROUP BY label
", []);
$response['data']['performanceDist'] = $perf_res['success'] ? $perf_res['data'] : [];

echo json_encode($response);
exit();
