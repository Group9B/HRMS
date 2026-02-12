<?php
header('Content-Type: application/json');

require_once '../config/db.php';
require_once '../includes/functions.php';

// Security Check: Employee (4)
if (!isLoggedIn() || !isset($_SESSION['role_id']) || $_SESSION['role_id'] !== 4) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access.']);
    exit();
}

$user_id = $_SESSION['user_id'];

// Get employee record
$emp_res = query($mysqli, "SELECT id FROM employees WHERE user_id = ?", [$user_id]);
if (!$emp_res['success'] || empty($emp_res['data'])) {
    echo json_encode(['success' => false, 'message' => 'Employee record not found.']);
    exit();
}
$emp_id = $emp_res['data'][0]['id'];

$response = [
    'success' => true,
    'data' => [
        'myAttendance' => ['labels' => [], 'data' => []],
        'taskEfficiency' => [],
        'payrollProgress' => ['labels' => [], 'data' => []],
        'leaveBalance' => []
    ]
];

// 1. Personal Attendance (Last 15 days)
$labels = [];
$att_data = [];
for ($i = 14; $i >= 0; $i--) {
    $date = date('Y-m-d', strtotime("-$i days"));
    $labels[] = date('M d', strtotime($date));

    $check_res = query($mysqli, "SELECT status FROM attendance WHERE date = ? AND employee_id = ?", [$date, $emp_id]);
    $att_data[] = ($check_res['success'] && !empty($check_res['data']) && $check_res['data'][0]['status'] === 'present') ? 100 : 0;
}
$response['data']['myAttendance'] = ['labels' => $labels, 'data' => $att_data];

// 2. Task Efficiency
$task_res = query($mysqli, "
    SELECT 
        status as label,
        COUNT(*) as value
    FROM tasks 
    WHERE employee_id = ?
    GROUP BY status
", [$emp_id]);
$response['data']['taskEfficiency'] = $task_res['success'] ? $task_res['data'] : [];

// 3. Payroll Progress (Last 6 months)
$p_labels = [];
$p_data = [];
for ($i = 5; $i >= 0; $i--) {
    $month = date('Y-m', strtotime("-$i months"));
    $p_labels[] = date('M Y', strtotime($month . '-01'));

    $pay_res = query($mysqli, "SELECT net_salary FROM payroll WHERE employee_id = ? AND month = ?", [$emp_id, $month]);
    $p_data[] = ($pay_res['success'] && !empty($pay_res['data'])) ? (float) $pay_res['data'][0]['net_salary'] : 0;
}
$response['data']['payrollProgress'] = ['labels' => $p_labels, 'data' => $p_data];

// 4. Leave Balance
$leave_stats = query($mysqli, "
    SELECT 
        (SELECT SUM(total_days) FROM leaves WHERE employee_id = ? AND status = 'approved') as used,
        (SELECT leave_balance FROM employees WHERE id = ?) as remaining
", [$emp_id, $emp_id]);

if ($leave_stats['success'] && !empty($leave_stats['data'])) {
    $row = $leave_stats['data'][0];
    $response['data']['leaveBalance'] = [
        ['label' => 'Used', 'value' => (int) $row['used']],
        ['label' => 'Remaining', 'value' => (int) $row['remaining']]
    ];
}

echo json_encode($response);
exit();
