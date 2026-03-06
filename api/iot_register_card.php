<?php
/**
 * IoT Card Registration API
 * 
 * Called by ESP32 devices to submit scanned RFID card UIDs.
 * Works with the web UI: HR selects a device → activates add_card_mode →
 * device scans card → POSTs UID here → HR polls and gets UID → assigns to employee.
 *
 * Authentication: Bearer token (device_token from iot_devices table)
 * Method: POST
 * Content-Type: application/json
 * 
 * Request Body: { "card_uid": "0A8F8005" }
 */

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Authorization, Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

require_once __DIR__ . '/../middleware/verifyDevice.php';

// Ensure MySQL timezone matches PHP (IST)
$mysqli->query("SET time_zone = '+05:30'");

// ─────────────────────────────────────────────────────────────
// Step 1: Verify Device Authentication
// ─────────────────────────────────────────────────────────────
$device = verifyDevice();

// ─────────────────────────────────────────────────────────────
// Step 2: Validate Request Method
// ─────────────────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendIotError(405, 'Method not allowed. Use POST.');
}

// ─────────────────────────────────────────────────────────────
// Step 3: Parse and Validate JSON Body
// ─────────────────────────────────────────────────────────────
$rawInput = file_get_contents('php://input');
$input = json_decode($rawInput, true);

if (json_last_error() !== JSON_ERROR_NONE) {
    sendIotError(400, 'Invalid JSON body: ' . json_last_error_msg());
}

$cardUid = isset($input['card_uid']) ? strtoupper(trim($input['card_uid'])) : '';

if (empty($cardUid)) {
    sendIotError(400, 'Missing required field: card_uid');
}

// Validate hex format
if (!preg_match('/^[A-F0-9]{4,20}$/', $cardUid)) {
    sendIotError(400, 'Invalid card UID format. Must be 4-20 hex characters.');
}

// ─────────────────────────────────────────────────────────────
// Step 4: Check if card is already assigned to an employee
// ─────────────────────────────────────────────────────────────
$dupCheck = query(
    $mysqli,
    "SELECT ec.id, ec.employee_id, e.first_name, e.last_name 
     FROM employee_credentials ec 
     LEFT JOIN employees e ON ec.employee_id = e.id 
     WHERE ec.type = 'rfid' AND ec.identifier_value = ?",
    [$cardUid]
);

if ($dupCheck['success'] && !empty($dupCheck['data'])) {
    $existing = $dupCheck['data'][0];
    if ($existing['employee_id']) {
        $empName = trim($existing['first_name'] . ' ' . $existing['last_name']);
        sendIotError(409, "Card already assigned to: {$empName}", 'card_already_assigned');
    }
}

// ─────────────────────────────────────────────────────────────
// Step 5: Store scanned UID on the device record for HR to pick up
// ─────────────────────────────────────────────────────────────
$updateResult = query(
    $mysqli,
    "UPDATE iot_devices 
     SET pending_card_uid = ?, 
         card_scanned_at = NOW(), 
         add_card_mode = 0 
     WHERE id = ?",
    [$cardUid, $device['id']]
);

if (!$updateResult['success']) {
    sendIotError(500, 'Failed to store card UID. Please try again.');
}

// ─────────────────────────────────────────────────────────────
// Step 6: Success Response
// ─────────────────────────────────────────────────────────────
sendIotSuccess('card_registered', 'Card scanned successfully! HR can now assign it.', [
    'card_uid' => $cardUid,
    'device_name' => $device['device_name'],
    'device_location' => $device['location'] ?? '',
    'timestamp' => date('Y-m-d H:i:s')
]);