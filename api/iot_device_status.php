<?php
/**
 * IoT Device Status API - Get Device Information
 * 
 * Returns current device status and configuration.
 * Useful for ESP32 initial boot to confirm connection.
 * 
 * Authentication: Bearer token
 * Method: GET
 */

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Authorization, Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

require_once __DIR__ . '/../middleware/verifyDevice.php';

$device = verifyDevice();

// Get company name for display
$companyResult = query(
    $mysqli,
    "SELECT name FROM companies WHERE id = ?",
    [$device['company_id']]
);

$companyName = $companyResult['success'] && !empty($companyResult['data'])
    ? $companyResult['data'][0]['name']
    : 'Unknown Company';

// Count registered credentials for this company
$credCountResult = query(
    $mysqli,
    "SELECT COUNT(*) as count FROM employee_credentials ec
     JOIN employees e ON ec.employee_id = e.id
     JOIN users u ON e.user_id = u.id
     WHERE u.company_id = ?",
    [$device['company_id']]
);

$registeredCredentials = $credCountResult['success'] && !empty($credCountResult['data'])
    ? (int) $credCountResult['data'][0]['count']
    : 0;

sendIotSuccess('device_info', 'Device configured', [
    'device_id' => (int) $device['id'],
    'device_name' => $device['device_name'],
    'device_location' => $device['location'] ?? '',
    'company_id' => (int) $device['company_id'],
    'company_name' => $companyName,
    'registered_credentials' => $registeredCredentials,
    'status' => $device['status'],
    'last_heartbeat' => $device['last_heartbeat'],
    'server_time' => date('Y-m-d H:i:s'),
    'timezone' => 'Asia/Kolkata'
]);
