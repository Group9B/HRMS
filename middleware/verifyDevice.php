<?php
/**
 * IoT Device Authentication Middleware
 * 
 * Verifies ESP32/IoT devices using Bearer token authentication.
 * Include this file in any IoT API endpoint that requires device auth.
 * 
 * Usage:
 *   require_once '../middleware/verifyDevice.php';
 *   $device = verifyDevice(); // Returns device row or exits with 401
 */

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/functions.php';

/**
 * Extract Bearer token from Authorization header
 * 
 * @return string|null The token or null if not found
 */
function getBearerToken(): ?string
{
    $headers = null;

    // Try getallheaders() first (Apache)
    if (function_exists('getallheaders')) {
        $headers = getallheaders();
        // Normalize header keys to lowercase for consistency
        $headers = array_change_key_case($headers, CASE_LOWER);
    }

    // Fallback: Check $_SERVER for Authorization header
    if (empty($headers['authorization'])) {
        if (isset($_SERVER['HTTP_AUTHORIZATION'])) {
            $headers['authorization'] = $_SERVER['HTTP_AUTHORIZATION'];
        } elseif (isset($_SERVER['REDIRECT_HTTP_AUTHORIZATION'])) {
            // Some Apache configs use this
            $headers['authorization'] = $_SERVER['REDIRECT_HTTP_AUTHORIZATION'];
        }
    }

    // No authorization header found
    if (empty($headers['authorization'])) {
        return null;
    }

    $authHeader = $headers['authorization'];

    // Check if it's a Bearer token
    if (preg_match('/Bearer\s+(.+)/i', $authHeader, $matches)) {
        return trim($matches[1]);
    }

    return null;
}

/**
 * Verify IoT device by Bearer token
 * 
 * - Checks if token exists and device is active
 * - Updates last_heartbeat timestamp on success
 * - Returns device data array on success
 * - Sends 401 JSON response and exits on failure
 * 
 * @return array The verified device row from iot_devices table
 */
function verifyDevice(): array
{
    global $mysqli;

    // Set JSON content type for error responses
    header('Content-Type: application/json; charset=utf-8');

    // Extract Bearer token
    $token = getBearerToken();

    if (empty($token)) {
        http_response_code(401);
        echo json_encode([
            'success' => false,
            'message' => 'Missing or invalid Authorization header. Use: Bearer <device_token>',
            'action' => null,
            'data' => null
        ]);
        exit();
    }

    // Query for active device with this token
    $result = query(
        $mysqli,
        "SELECT id, company_id, device_name, location, status, last_heartbeat, created_at 
         FROM iot_devices 
         WHERE device_token = ? AND status = 'active'",
        [$token]
    );

    // Check if device was found
    if (!$result['success'] || empty($result['data'])) {
        http_response_code(401);
        echo json_encode([
            'success' => false,
            'message' => 'Unauthorized device. Token invalid or device inactive.',
            'action' => null,
            'data' => null
        ]);
        exit();
    }

    $device = $result['data'][0];

    // Update last_heartbeat to current timestamp
    // This helps track device connectivity status
    query(
        $mysqli,
        "UPDATE iot_devices SET last_heartbeat = NOW() WHERE id = ?",
        [$device['id']]
    );

    // Return verified device data
    return $device;
}

/**
 * Send a standardized JSON error response
 * 
 * @param int $httpCode HTTP status code
 * @param string $message Error message
 * @param string|null $action Action context (if any)
 */
function sendIotError(int $httpCode, string $message, ?string $action = null): void
{
    http_response_code($httpCode);
    echo json_encode([
        'success' => false,
        'message' => $message,
        'action' => $action,
        'data' => null
    ]);
    exit();
}

/**
 * Send a standardized JSON success response
 * 
 * @param string $action The action performed (checked_in, checked_out, etc.)
 * @param string $message Human-readable message for display
 * @param array $data Additional data payload
 */
function sendIotSuccess(string $action, string $message, array $data = []): void
{
    http_response_code(200);
    echo json_encode([
        'success' => true,
        'message' => $message,
        'action' => $action,
        'data' => $data
    ]);
    exit();
}
