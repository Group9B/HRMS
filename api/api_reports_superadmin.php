<?php
// Set the content type to JSON for all responses
header('Content-Type: application/json');

require_once '../config/db.php';
require_once '../includes/functions.php';

// Security Check: Ensure the user is a logged-in Super Admin
if (!isLoggedIn() || !isset($_SESSION['role_id']) || $_SESSION['role_id'] !== 1) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access.']);
    exit();
}

$response = [
    'success' => true,
    'data' => [
        'companyDirectory' => [],
        'companyUsage' => [],
        'userRegistrationActivity' => ['labels' => [], 'data' => []],
        'employeeStatus' => [],
        'companyStatus' => [],
        'userRole' => []
    ]
];

// --- 1. Company Directory Report ---
// Fetches all companies along with their details.
$directory_query = "SELECT id, name, email, phone, address, created_at FROM companies ORDER BY name ASC";
$directory_result = query($mysqli, $directory_query);
if ($directory_result['success'] && !empty($directory_result['data'])) {
    $response['data']['companyDirectory'] = $directory_result['data'];
}

// --- 2. Company Usage Report (Users per Company) ---
// Counts the number of users associated with each company.
$usage_query = "
    SELECT c.name, COUNT(u.id) as user_count 
    FROM companies c 
    LEFT JOIN users u ON c.id = u.company_id 
    GROUP BY c.name 
    HAVING user_count > 0
    ORDER BY user_count DESC 
    LIMIT 10
";
$usage_result = query($mysqli, $usage_query);
if ($usage_result['success']) {
    $response['data']['companyUsage'] = $usage_result['data'];
}

// --- 3. Company Activity Report (New User Registrations Over Time) ---
// Tracks new user sign-ups across all companies for the last 12 months.
$activity_query = "
    SELECT 
        DATE_FORMAT(created_at, '%Y-%m') as join_month, 
        COUNT(id) as new_users 
    FROM users 
    WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL 12 MONTH) AND company_id IS NOT NULL
    GROUP BY join_month 
    ORDER BY join_month ASC
";
$activity_result = query($mysqli, $activity_query);
if ($activity_result['success']) {
    $labels = [];
    $data = [];
    $results_map = array_column($activity_result['data'], 'new_users', 'join_month');

    for ($i = 11; $i >= 0; $i--) {
        $month = date('Y-m', strtotime("-$i months"));
        $labels[] = date('M Y', strtotime($month . '-01'));
        $data[] = $results_map[$month] ?? 0;
    }
    $response['data']['userRegistrationActivity'] = ['labels' => $labels, 'data' => $data];
}

// --- 4. Employee Status Distribution ---
// Shows count of employees by their status (active, inactive, on_leave, etc.)
$emp_status_query = "
    SELECT COALESCE(status, 'active') as status, COUNT(id) as count 
    FROM employees 
    GROUP BY status
";
$emp_status_result = query($mysqli, $emp_status_query);
if ($emp_status_result['success'] && !empty($emp_status_result['data'])) {
    $emp_status_data = [];
    foreach ($emp_status_result['data'] as $row) {
        $emp_status_data[ucfirst($row['status'])] = (int) $row['count'];
    }
    if (!empty($emp_status_data)) {
        $response['data']['employeeStatus'] = $emp_status_data;
    }
}

// --- 5. Companies by Active Status ---
// Shows count of companies (simplified as all companies are active by default)
$company_count_query = "SELECT COUNT(id) as total_companies FROM companies";
$company_count_result = query($mysqli, $company_count_query);
if ($company_count_result['success'] && !empty($company_count_result['data'])) {
    $total = (int) $company_count_result['data'][0]['total_companies'];
    if ($total > 0) {
        $response['data']['companyStatus'] = ['Active' => $total];
    }
}

// --- 6. User Role Distribution ---
// Shows count of users by their role (Super Admin, Admin, Manager, Employee)
$role_query = "
    SELECT r.name, COUNT(u.id) as count 
    FROM users u 
    LEFT JOIN roles r ON u.role_id = r.id 
    GROUP BY r.id, r.name
";
$role_result = query($mysqli, $role_query);
if ($role_result['success']) {
    $role_data = [];
    foreach ($role_result['data'] as $row) {
        $role_data[$row['name'] ?? 'Unassigned'] = (int) $row['count'];
    }
    $response['data']['userRole'] = $role_data;
}

// Echo the final JSON response
echo json_encode($response);
exit();
?>