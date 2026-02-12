<?php
header('Content-Type: application/json');
require_once '../config/db.php';
require_once '../includes/functions.php';

$response = ['success' => false, 'message' => 'An unknown error occurred.'];

// Security Check: Company Admin or HR Manager
if (!isLoggedIn() || !in_array($_SESSION['role_id'], [2, 3])) {
    $response['message'] = 'Unauthorized access.';
    echo json_encode($response);
    exit();
}

$company_id = $_SESSION['company_id'];
$action = $_REQUEST['action'] ?? '';

// Skeleton Loading: Simulate latency
if (defined('SKELETON_DEV_DELAY') && SKELETON_DEV_DELAY > 0) {
    usleep(SKELETON_DEV_DELAY * 1000);
}

try {
    if ($action === 'get_dashboard_data') {
        // 1. Stats
        // Total Employees
        $employees_result = query($mysqli, "SELECT COUNT(e.id) as count FROM employees e JOIN departments d ON e.department_id = d.id WHERE d.company_id = ? AND e.status = 'active'", [$company_id]);
        $total_employees = $employees_result['success'] ? $employees_result['data'][0]['count'] : 0;

        // Total Departments
        $departments_result = query($mysqli, "SELECT COUNT(id) as count FROM departments WHERE company_id = ?", [$company_id]);
        $total_departments = $departments_result['success'] ? $departments_result['data'][0]['count'] : 0;

        // Pending Leaves
        $pending_leaves_result = query($mysqli, "SELECT COUNT(l.id) as count FROM leaves l JOIN employees e ON l.employee_id = e.id JOIN departments d ON e.department_id = d.id WHERE d.company_id = ? AND l.status = 'pending'", [$company_id]);
        $pending_leaves = $pending_leaves_result['success'] ? $pending_leaves_result['data'][0]['count'] : 0;

        // On Leave Today
        $today = date('Y-m-d');
        $on_leave_query = "SELECT COUNT(a.id) as count FROM attendance a JOIN employees e ON a.employee_id = e.id JOIN departments d ON e.department_id = d.id WHERE a.date = ? AND a.status = 'leave' AND d.company_id = ?";
        $on_leave_result = query($mysqli, $on_leave_query, [$today, $company_id]);
        $on_leave_today = $on_leave_result['success'] ? $on_leave_result['data'][0]['count'] : 0;

        // 2. Recent Hires
        $recent_hires_query = "
            SELECT e.id AS employee_id, e.user_id, u.username, e.first_name, e.last_name, e.date_of_joining, des.name as designation_name
            FROM employees e
            JOIN users u ON e.user_id = u.id
            JOIN departments d ON e.department_id = d.id
            LEFT JOIN designations des ON e.designation_id = des.id
            WHERE d.company_id = ?
            ORDER BY e.date_of_joining DESC
            LIMIT 5
        ";
        $recent_hires_result = query($mysqli, $recent_hires_query, [$company_id]);
        $recent_hires = $recent_hires_result['success'] ? $recent_hires_result['data'] : [];

        // 3. Hiring Trends Chart
        $hires_chart_query = "
            SELECT DATE_FORMAT(e.date_of_joining, '%b %Y') AS month, COUNT(e.id) AS hires_count
            FROM employees e
            JOIN departments d ON e.department_id = d.id
            WHERE d.company_id = ? AND e.date_of_joining >= DATE_SUB(CURDATE(), INTERVAL 3 MONTH)
            GROUP BY month
            ORDER BY e.date_of_joining ASC
        ";
        $hires_chart_result = query($mysqli, $hires_chart_query, [$company_id]);
        $chart_labels = [];
        $chart_data = [];
        if ($hires_chart_result['success']) {
            foreach ($hires_chart_result['data'] as $row) {
                $chart_labels[] = $row['month'];
                $chart_data[] = $row['hires_count'];
            }
        }

        $response['success'] = true;
        $response['data'] = [
            'stats' => [
                ['label' => 'Active Employees', 'value' => $total_employees, 'icon' => 'users', 'color' => 'success'],
                ['label' => 'Departments', 'value' => $total_departments, 'icon' => 'sitemap', 'color' => 'primary'],
                ['label' => 'Pending Leaves', 'value' => $pending_leaves, 'icon' => 'hourglass-empty', 'color' => 'danger'],
                ['label' => 'On Leave Today', 'value' => $on_leave_today, 'icon' => 'user-clock', 'color' => 'warning']
            ],
            'recent_hires' => $recent_hires,
            'chart' => [
                'labels' => $chart_labels,
                'data' => $chart_data
            ]
        ];

    } else {
        $response['message'] = 'Invalid action.';
    }
} catch (Exception $e) {
    $response['message'] = 'Error: ' . $e->getMessage();
}

echo json_encode($response);
