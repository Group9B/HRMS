<?php
/**
 * IoT Heartbeat API - Device Health Check & Time Sync
 * 
 * ESP32 devices ping this endpoint every 30 seconds to:
 * 1. Confirm device is still authorized and active
 * 2. Get server time for OLED display synchronization
 * 3. Update last_heartbeat timestamp (done by verifyDevice)
 * 
 * Authentication: Bearer token (device_token from iot_devices table)
 * Method: GET or POST
 * 
 * Response:
 * {
 *   "success": true,
 *   "action": "heartbeat",
 *   "message": "Device online",
 *   "data": { 
 *     "server_time": "HH:MM:SS",
 *     "server_date": "YYYY-MM-DD",
 *     "device_name": "...",
 *     "device_location": "..."
 *   }
 * }
 */

// Set response headers
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
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
// Verify Device Authentication
// This also updates last_heartbeat automatically
// ─────────────────────────────────────────────────────────────
$device = verifyDevice(); // Exits with 401 if invalid

// ─────────────────────────────────────────────────────────────
// Return Success Response with Server Time
// ─────────────────────────────────────────────────────────────
sendIotSuccess('heartbeat', 'Device online', [
    'server_time' => date('H:i:s'),
    'server_date' => date('Y-m-d'),
    'server_datetime' => date('Y-m-d H:i:s'),
    'timezone' => 'Asia/Kolkata',
    'device_id' => (int) $device['id'],
    'device_name' => $device['device_name'],
    'device_location' => $device['location'] ?? ''
]);
