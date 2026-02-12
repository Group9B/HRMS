<?php
header('Content-Type: application/json');

require_once '../config/db.php';
require_once '../includes/functions.php';

// Security Check: Must be Company Owner (2) or HR (3)
if (!isLoggedIn() || !isset($_SESSION['role_id']) || !in_array($_SESSION['role_id'], [2, 3])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access.']);
    exit();
}

$company_id = $_SESSION['company_id'];

$response = [
    'success' => true,
    'data' => [
        'deptDistribution' => [],
        'desigDistribution' => [],
        'attendanceTrends' => ['labels' => [], 'data' => []],
        'leaveDistribution' => [],
        'payrollTrends' => ['labels' => [], 'data' => []],
        'recruitmentFunnel' => []
    ]
];

// 1. Department Distribution
$dept_query = "
    SELECT d.name, COUNT(e.id) as count 
    FROM departments d 
    LEFT JOIN employees e ON d.id = e.department_id AND e.status = 'active'
    WHERE d.company_id = ? 
    GROUP BY d.id, d.name
    ORDER BY count DESC
";
$dept_result = query($mysqli, $dept_query, [$company_id]);
if ($dept_result['success']) {
    $response['data']['deptDistribution'] = $dept_result['data'];
}

// 2. Designation Distribution
$desig_query = "
    SELECT ds.name, COUNT(e.id) as count 
    FROM designations ds 
    INNER JOIN departments d ON ds.department_id = d.id
    LEFT JOIN employees e ON ds.id = e.designation_id AND e.status = 'active'
    WHERE d.company_id = ? 
    GROUP BY ds.id, ds.name
    HAVING count > 0
    ORDER BY count DESC
";
$desig_result = query($mysqli, $desig_query, [$company_id]);
if ($desig_result['success']) {
    $response['data']['desigDistribution'] = $desig_result['data'];
}

// 3. Attendance Trends (Last 15 days of activity)
$attendance_query = "
    SELECT date, 
           COUNT(CASE WHEN status = 'present' THEN 1 END) as present_count,
           COUNT(id) as total_attendance
    FROM attendance 
    WHERE employee_id IN (SELECT id FROM employees WHERE company_id = ?)
      AND date >= DATE_SUB(CURDATE(), INTERVAL 15 DAY)
    GROUP BY date 
    ORDER BY date ASC
";
$attendance_result = query($mysqli, $attendance_query, [$company_id]);
if ($attendance_result['success']) {
    $labels = [];
    $data = [];
    foreach ($attendance_result['data'] as $row) {
        $labels[] = date('M j', strtotime($row['date']));
        $perc = $row['total_attendance'] > 0 ? ($row['present_count'] / $row['total_attendance']) * 100 : 0;
        $data[] = round($perc, 1);
    }
    $response['data']['attendanceTrends'] = ['labels' => $labels, 'data' => $data];
}

// 4. Leave Distribution
$leave_query = "
    SELECT status, COUNT(id) as count 
    FROM leaves 
    WHERE employee_id IN (SELECT id FROM employees WHERE company_id = ?)
    GROUP BY status
";
$leave_result = query($mysqli, $leave_query, [$company_id]);
if ($leave_result['success']) {
    $leave_data = [];
    foreach ($leave_result['data'] as $row) {
        $leave_data[ucfirst($row['status'])] = (int) $row['count'];
    }
    $response['data']['leaveDistribution'] = $leave_data;
}

// 5. Payroll Trends (Last 6 Months)
$payroll_query = "
    SELECT period, SUM(net_salary) as total_payout 
    FROM payslips 
    WHERE company_id = ? AND status != 'cancelled'
      AND period >= DATE_FORMAT(DATE_SUB(CURDATE(), INTERVAL 6 MONTH), '%Y-%m')
    GROUP BY period 
    ORDER BY period ASC
";
$payroll_result = query($mysqli, $payroll_query, [$company_id]);
if ($payroll_result['success']) {
    $labels = [];
    $data = [];
    foreach ($payroll_result['data'] as $row) {
        $labels[] = date('M Y', strtotime($row['period'] . '-01'));
        $data[] = (float) $row['total_payout'];
    }
    $response['data']['payrollTrends'] = ['labels' => $labels, 'data' => $data];
}

// 6. Recruitment Funnel
$recruitment_query = "
    SELECT status, COUNT(id) as count 
    FROM job_applications 
    WHERE job_id IN (SELECT id FROM jobs WHERE company_id = ?)
    GROUP BY status
";
$recruitment_result = query($mysqli, $recruitment_query, [$company_id]);
if ($recruitment_result['success']) {
    $funnel_data = [];
    foreach ($recruitment_result['data'] as $row) {
        $funnel_data[ucfirst($row['status'])] = (int) $row['count'];
    }
    $response['data']['recruitmentFunnel'] = $funnel_data;
}

echo json_encode($response);
exit();
?>