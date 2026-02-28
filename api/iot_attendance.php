<?php
/**
 * IoT Attendance API - Mark Attendance via RFID/Fingerprint/FaceID
 * 
 * This endpoint is called by ESP32 devices to toggle employee check-in/check-out.
 * 
 * Authentication: Bearer token (device_token from iot_devices table)
 * Method: POST
 * Content-Type: application/json
 * 
 * Request Body:
 * {
 *   "auth_type": "rfid" | "fingerprint" | "face_id",
 *   "identifier_value": "RFID_UID_OR_FINGERPRINT_ID"
 * }
 * 
 * Response:
 * {
 *   "success": true/false,
 *   "action": "checked_in" | "checked_out" | null,
 *   "message": "Welcome, John Doe" | "Goodbye, John Doe" | "Error message",
 *   "data": { "employee_name": "...", "employee_code": "...", "timestamp": "HH:MM:SS", "device": "..." }
 * }
 */

// Set response headers early
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Authorization, Content-Type');

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Include device authentication middleware
require_once __DIR__ . '/../middleware/verifyDevice.php';

// Ensure MySQL timezone matches PHP (IST)
$mysqli->query("SET time_zone = '+05:30'");

// ─────────────────────────────────────────────────────────────
// Step 1: Verify Device Authentication
// ─────────────────────────────────────────────────────────────
$device = verifyDevice(); // Exits with 401 if invalid

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

// Validate required fields
$authType = $input['auth_type'] ?? null;
$identifierValue = $input['identifier_value'] ?? null;

if (empty($authType) || empty($identifierValue)) {
    sendIotError(400, 'Missing required fields: auth_type and identifier_value');
}

// Validate auth_type is a valid enum value
$validAuthTypes = ['rfid', 'fingerprint', 'face_id'];
if (!in_array($authType, $validAuthTypes, true)) {
    sendIotError(400, 'Invalid auth_type. Must be: rfid, fingerprint, or face_id');
}

// ─────────────────────────────────────────────────────────────
// Step 4: Look Up Employee by Credential
// ─────────────────────────────────────────────────────────────
$credResult = query(
    $mysqli,
    "SELECT 
        ec.employee_id,
        e.first_name,
        e.last_name,
        e.employee_code,
        e.shift_id,
        e.status as employee_status,
        u.company_id
     FROM employee_credentials ec
     JOIN employees e ON ec.employee_id = e.id
     JOIN users u ON e.user_id = u.id
     WHERE ec.type = ? AND ec.identifier_value = ?",
    [$authType, $identifierValue]
);

if (!$credResult['success'] || empty($credResult['data'])) {
    // Credential not found - unregistered card/fingerprint
    sendIotError(404, 'Credential not registered. Please contact HR.', 'access_denied');
}

$employee = $credResult['data'][0];
$employeeId = (int) $employee['employee_id'];
$employeeName = trim($employee['first_name'] . ' ' . $employee['last_name']);
$employeeCode = $employee['employee_code'] ?? '';

// ─────────────────────────────────────────────────────────────
// Step 5: Security Check - Employee belongs to same company as device
// ─────────────────────────────────────────────────────────────
if ((int) $employee['company_id'] !== (int) $device['company_id']) {
    sendIotError(403, 'Access denied. Employee not authorized for this device.', 'access_denied');
}

// ─────────────────────────────────────────────────────────────
// Step 6: Check if Employee is Active
// ─────────────────────────────────────────────────────────────
if ($employee['employee_status'] !== 'active') {
    sendIotError(403, 'Access denied. Employee account is inactive.', 'access_denied');
}

// ─────────────────────────────────────────────────────────────
// Step 7: Query Today's Last Attendance Record
// ─────────────────────────────────────────────────────────────
$today = date('Y-m-d');
$currentTime = date('H:i:s');

$attendanceResult = query(
    $mysqli,
    "SELECT id, check_in, check_out, status 
     FROM attendance 
     WHERE employee_id = ? AND date = CURDATE()
     ORDER BY id DESC 
     LIMIT 1",
    [$employeeId]
);

$lastAttendance = $attendanceResult['success'] && !empty($attendanceResult['data'])
    ? $attendanceResult['data'][0]
    : null;

// ─────────────────────────────────────────────────────────────
// Step 8: Toggle Logic - Check In or Check Out
// ─────────────────────────────────────────────────────────────

$action = null;
$message = '';
$timestamp = $currentTime;

if ($lastAttendance === null || $lastAttendance['check_out'] !== null) {
    // ─────────────────────────────────────────────────────────
    // CASE A: No record today OR last record already checked out
    //         → INSERT new check-in
    // ─────────────────────────────────────────────────────────

    $insertResult = query(
        $mysqli,
        "INSERT INTO attendance (employee_id, date, check_in, status, device_id, auth_method) 
         VALUES (?, CURDATE(), CURTIME(), 'present', ?, ?)",
        [$employeeId, $device['id'], $authType]
    );

    if (!$insertResult['success']) {
        sendIotError(500, 'Failed to record check-in. Please try again.');
    }

    $action = 'checked_in';
    $message = "Welcome, $employeeName";

} elseif ($lastAttendance['check_in'] !== null && $lastAttendance['check_out'] === null) {
    // ─────────────────────────────────────────────────────────
    // CASE B: Has check-in but no check-out
    //         → UPDATE with check-out time
    // ─────────────────────────────────────────────────────────

    $attendanceId = (int) $lastAttendance['id'];
    $checkInTime = $lastAttendance['check_in'];

    // Calculate hours worked for half-day determination
    $checkInDateTime = new DateTime("$today $checkInTime");
    $checkOutDateTime = new DateTime("$today $currentTime");
    $interval = $checkInDateTime->diff($checkOutDateTime);
    $hoursWorked = $interval->h + ($interval->i / 60);

    // Determine status based on hours worked
    // Default: 8 hour shift, half-day if < 4 hours (50%)
    $newStatus = 'present';

    // Fetch shift info if available
    if (!empty($employee['shift_id'])) {
        $shiftResult = query(
            $mysqli,
            "SELECT start_time, end_time FROM shifts WHERE id = ?",
            [$employee['shift_id']]
        );

        if ($shiftResult['success'] && !empty($shiftResult['data'])) {
            $shift = $shiftResult['data'][0];
            $shiftStart = new DateTime($shift['start_time']);
            $shiftEnd = new DateTime($shift['end_time']);
            $expectedHours = ($shiftEnd->getTimestamp() - $shiftStart->getTimestamp()) / 3600;

            // If worked less than 50% of expected hours, mark as half-day
            if ($expectedHours > 0 && ($hoursWorked / $expectedHours) < 0.5) {
                $newStatus = 'half-day';
            }
        }
    } else {
        // No shift assigned - use default 8 hour check
        if ($hoursWorked < 4) {
            $newStatus = 'half-day';
        }
    }

    $updateResult = query(
        $mysqli,
        "UPDATE attendance 
         SET check_out = CURTIME(), status = ?
         WHERE id = ?",
        [$newStatus, $attendanceId]
    );

    if (!$updateResult['success']) {
        sendIotError(500, 'Failed to record check-out. Please try again.');
    }

    $action = 'checked_out';
    $message = "Goodbye, $employeeName";

    // Include hours worked in response
    $hoursWorkedFormatted = sprintf('%d:%02d', floor($hoursWorked), ($hoursWorked - floor($hoursWorked)) * 60);
}

// ─────────────────────────────────────────────────────────────
// Step 9: Send Success Response
// ─────────────────────────────────────────────────────────────
sendIotSuccess($action, $message, [
    'employee_id' => $employeeId,
    'employee_name' => $employeeName,
    'employee_code' => $employeeCode,
    'timestamp' => $timestamp,
    'date' => $today,
    'device' => $device['device_name'],
    'device_location' => $device['location'] ?? '',
    'hours_worked' => $hoursWorkedFormatted ?? null
]);
