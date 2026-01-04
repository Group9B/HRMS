<?php

/**
 * User Preferences API
 * 
 * Handles all preference-related operations:
 * - GET: Retrieve user preferences
 * - POST/PUT: Update user preferences
 */

require_once '../config/db.php';
require_once '../includes/functions.php';
require_once '../includes/preferences.php';

header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$action = $_GET['action'] ?? '';
$user_id = (int) $_SESSION['user_id'];

try {
    switch ($action) {
        case 'read':
            handleRead($user_id);
            break;

        case 'update':
            handleUpdate($user_id);
            break;

        case 'read_notification':
            handleReadNotification($user_id);
            break;

        case 'read_privacy':
            handleReadPrivacy($user_id);
            break;

        case 'update_notification':
            handleUpdateNotification($user_id);
            break;

        case 'update_privacy':
            handleUpdatePrivacy($user_id);
            break;

        case 'request_deactivation':
            handleDeactivationRequest($user_id);
            break;

        case 'cancel_deactivation':
            handleCancelDeactivation($user_id);
            break;

        default:
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Invalid action']);
            break;
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Server error: ' . $e->getMessage()]);
    error_log("Preferences API Error: " . $e->getMessage());
}

/**
 * GET - Read all user preferences
 */
function handleRead($user_id)
{
    global $mysqli;

    $preferences = getUserPreferences($mysqli, $user_id);

    if (!empty($preferences)) {
        echo json_encode([
            'success' => true,
            'data' => $preferences
        ]);
    } else {
        http_response_code(404);
        echo json_encode([
            'success' => false,
            'message' => 'No preferences found for user'
        ]);
    }
}

/**
 * POST/PUT - Update one or more preferences
 * 
 * Expected JSON:
 * {
 *   "preferences": {
 *     "preference_key": "value",
 *     ...
 *   }
 * }
 */
function handleUpdate($user_id)
{
    global $mysqli;

    $input = json_decode(file_get_contents('php://input'), true);

    if (!isset($input['preferences']) || !is_array($input['preferences'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Invalid input format']);
        return;
    }

    $result = updateUserPreferences($mysqli, $user_id, $input['preferences']);

    http_response_code($result['success'] ? 200 : 400);
    echo json_encode($result);
}

/**
 * GET - Read notification preferences
 */
function handleReadNotification($user_id)
{
    global $mysqli;

    $notifications = getNotificationPreferences($mysqli, $user_id);

    echo json_encode([
        'success' => true,
        'data' => $notifications
    ]);
}

/**
 * POST/PUT - Update notification preferences
 * 
 * Expected JSON:
 * {
 *   "notification_types": {
 *     "leave_status": true,
 *     "attendance": true,
 *     ...
 *   }
 * }
 */
function handleUpdateNotification($user_id)
{
    global $mysqli;

    $input = json_decode(file_get_contents('php://input'), true);

    $updates = [];

    // Update notification types
    if (isset($input['notification_types'])) {
        foreach ($input['notification_types'] as $key => $value) {
            $pref_key = 'notif_' . $key;
            $updates[$pref_key] = $value ? '1' : '0';
        }
    }

    if (empty($updates)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'No preferences to update']);
        return;
    }

    $result = updateUserPreferences($mysqli, $user_id, $updates);
    http_response_code($result['success'] ? 200 : 400);
    echo json_encode($result);
}

/**
 * GET - Read privacy preferences
 */
function handleReadPrivacy($user_id)
{
    global $mysqli;

    $privacy = getPrivacyPreferences($mysqli, $user_id);

    echo json_encode([
        'success' => true,
        'data' => $privacy
    ]);
}

/**
 * POST/PUT - Update privacy preferences
 * 
 * Expected JSON:
 * {
 *   "profile_visible": true,
 *   "phone_visible": true,
 *   "email_visible": true
 * }
 */
function handleUpdatePrivacy($user_id)
{
    global $mysqli;

    $input = json_decode(file_get_contents('php://input'), true);

    $updates = [];

    if (isset($input['profile_visible'])) {
        $updates['privacy_profile_visible'] = $input['profile_visible'] ? '1' : '0';
    }
    if (isset($input['phone_visible'])) {
        $updates['privacy_phone_visible'] = $input['phone_visible'] ? '1' : '0';
    }
    if (isset($input['email_visible'])) {
        $updates['privacy_email_visible'] = $input['email_visible'] ? '1' : '0';
    }

    if (empty($updates)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'No preferences to update']);
        return;
    }

    $result = updateUserPreferences($mysqli, $user_id, $updates);
    http_response_code($result['success'] ? 200 : 400);
    echo json_encode($result);
}

/**
 * POST - Request account deactivation
 * 
 * Expected JSON:
 * {
 *   "reason": "string",
 *   "comments": "string"
 * }
 */
function handleDeactivationRequest($user_id)
{
    global $mysqli;

    $input = json_decode(file_get_contents('php://input'), true);

    $reason = $input['reason'] ?? '';
    $comments = $input['comments'] ?? '';

    // Validate inputs
    if (empty($reason)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Reason is required']);
        return;
    }

    if (strlen($reason) > 100) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Reason must be less than 100 characters']);
        return;
    }

    if (strlen($comments) > 500) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Comments must be less than 500 characters']);
        return;
    }

    // Update preferences
    $updates = [
        'deactivation_requested' => '1',
        'deactivation_reason' => $reason,
        'deactivation_comments' => $comments
    ];

    $result = updateUserPreferences($mysqli, $user_id, $updates);

    if ($result['success']) {
        // Log the deactivation request
        $stmt = $mysqli->prepare("
            INSERT INTO activity_logs (user_id, action, details, ip_address)
            VALUES (?, ?, ?, ?)
        ");
        $action = 'DEACTIVATION_REQUEST';
        $details = "Reason: {$reason}";
        $ip = $_SERVER['REMOTE_ADDR'];
        $stmt->bind_param("isss", $user_id, $action, $details, $ip);
        $stmt->execute();
        $stmt->close();
    }

    http_response_code($result['success'] ? 200 : 400);
    echo json_encode($result);
}

/**
 * POST - Cancel account deactivation request
 */
function handleCancelDeactivation($user_id)
{
    global $mysqli;

    $updates = [
        'deactivation_requested' => '0',
        'deactivation_reason' => '',
        'deactivation_comments' => ''
    ];

    $result = updateUserPreferences($mysqli, $user_id, $updates);

    if ($result['success']) {
        // Log the cancellation
        $stmt = $mysqli->prepare("
            INSERT INTO activity_logs (user_id, action, details, ip_address)
            VALUES (?, ?, ?, ?)
        ");
        $action = 'DEACTIVATION_CANCELLED';
        $details = 'User cancelled deactivation request';
        $ip = $_SERVER['REMOTE_ADDR'];
        $stmt->bind_param("isss", $user_id, $action, $details, $ip);
        $stmt->execute();
        $stmt->close();
    }

    http_response_code($result['success'] ? 200 : 400);
    echo json_encode($result);
}
