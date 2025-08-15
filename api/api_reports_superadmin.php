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
    'data' => []
];

// --- 1. Company Directory Report ---
// Fetches all companies along with their status.
$directory_query = "SELECT id, name, email, phone, created_at, status FROM companies ORDER BY name ASC";
$directory_result = query($mysqli, $directory_query);
if ($directory_result['success']) {
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

// Echo the final JSON response
echo json_encode($response);
exit();
?>