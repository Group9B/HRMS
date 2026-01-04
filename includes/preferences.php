<?php

require_once __DIR__ . '/../config/preferences_config.php';

/**
 * Initialize default user preferences when a new user is created
 * 
 * @param mysqli $mysqli Database connection
 * @param int $userId The ID of the newly created user
 * @return bool True if preferences initialized successfully, false otherwise
 */
function initializeUserPreferences($mysqli, $userId)
{
    // Get defaults from config file
    $defaultPreferences = getDefaultPreferences();

    $success = true;

    foreach ($defaultPreferences as $key => $value) {
        $stmt = $mysqli->prepare("
            INSERT INTO user_preferences (user_id, preference_key, preference_value)
            VALUES (?, ?, ?)
            ON DUPLICATE KEY UPDATE preference_value = ?
        ");

        if (!$stmt) {
            error_log("Prepare failed for preference {$key}: " . $mysqli->error);
            $success = false;
            continue;
        }

        $stmt->bind_param("isss", $userId, $key, $value, $value);

        if (!$stmt->execute()) {
            error_log("Execute failed for preference {$key}: " . $stmt->error);
            $success = false;
        }

        $stmt->close();
    }

    return $success;
}

/**
 * Get a single user preference
 * 
 * @param mysqli $mysqli Database connection
 * @param int $userId The user ID
 * @param string $key The preference key
 * @param string $default Default value if preference not found
 * @return string The preference value
 */
function getUserPreference($mysqli, $userId, $key, $default = '')
{
    $stmt = $mysqli->prepare("
        SELECT preference_value FROM user_preferences 
        WHERE user_id = ? AND preference_key = ?
    ");

    if (!$stmt) {
        return $default;
    }

    $stmt->bind_param("is", $userId, $key);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        return $row['preference_value'];
    }

    $stmt->close();
    return $default;
}

/**
 * Get all user preferences
 * 
 * @param mysqli $mysqli Database connection
 * @param int $userId The user ID
 * @return array Associative array of all preferences
 */
function getUserPreferences($mysqli, $userId)
{
    $stmt = $mysqli->prepare("
        SELECT preference_key, preference_value FROM user_preferences 
        WHERE user_id = ?
        ORDER BY preference_key
    ");

    if (!$stmt) {
        return [];
    }

    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();

    $preferences = [];
    while ($row = $result->fetch_assoc()) {
        $preferences[$row['preference_key']] = $row['preference_value'];
    }

    $stmt->close();
    return $preferences;
}

/**
 * Update a single user preference
 * 
 * @param mysqli $mysqli Database connection
 * @param int $userId The user ID
 * @param string $key The preference key
 * @param string $value The preference value
 * @return bool True if update successful, false otherwise
 */
function updateUserPreference($mysqli, $userId, $key, $value)
{
    $stmt = $mysqli->prepare("
        UPDATE user_preferences 
        SET preference_value = ?, updated_at = NOW()
        WHERE user_id = ? AND preference_key = ?
    ");

    if (!$stmt) {
        return false;
    }

    $stmt->bind_param("sis", $value, $userId, $key);
    $result = $stmt->execute();
    $stmt->close();

    return $result;
}

/**
 * Update multiple user preferences at once
 * 
 * @param mysqli $mysqli Database connection
 * @param int $userId The user ID
 * @param array $preferences Associative array of preferences to update
 * @return array Success status and message
 */
function updateUserPreferences($mysqli, $userId, $preferences)
{
    $errors = [];

    foreach ($preferences as $key => $value) {
        // Sanitize key to prevent injection
        if (!preg_match('/^[a-z_]+$/', $key)) {
            $errors[] = "Invalid preference key: {$key}";
            continue;
        }

        if (!updateUserPreference($mysqli, $userId, $key, $value)) {
            $errors[] = "Failed to update {$key}";
        }
    }

    if (empty($errors)) {
        return [
            'success' => true,
            'message' => 'Preferences updated successfully'
        ];
    }

    return [
        'success' => false,
        'message' => 'Some preferences failed to update: ' . implode(', ', $errors)
    ];
}

/**
 * Get notification preferences
 * 
 * @param mysqli $mysqli Database connection
 * @param int $userId The user ID
 * @return array Notification preferences
 */
function getNotificationPreferences($mysqli, $userId)
{
    $prefs = getUserPreferences($mysqli, $userId);

    return [
        'notification_types' => [
            'leave_status' => (bool) ($prefs['notif_leave_status'] ?? true),
            'attendance' => (bool) ($prefs['notif_attendance'] ?? true),
            'payslip' => (bool) ($prefs['notif_payslip'] ?? true),
            'announcements' => (bool) ($prefs['notif_announcements'] ?? true),
            'system' => (bool) ($prefs['notif_system'] ?? true),
        ]
    ];
}

/**
 * Get privacy preferences
 * 
 * @param mysqli $mysqli Database connection
 * @param int $userId The user ID
 * @return array Privacy preferences
 */
function getPrivacyPreferences($mysqli, $userId)
{
    $prefs = getUserPreferences($mysqli, $userId);

    return [
        'profile_visible' => (bool) ($prefs['privacy_profile_visible'] ?? true),
        'phone_visible' => (bool) ($prefs['privacy_phone_visible'] ?? true),
        'email_visible' => (bool) ($prefs['privacy_email_visible'] ?? true),
    ];
}

/**
 * Get deactivation request status
 * 
 * @param mysqli $mysqli Database connection
 * @param int $userId The user ID
 * @return array Deactivation request details
 */
function getDeactivationStatus($mysqli, $userId)
{
    $prefs = getUserPreferences($mysqli, $userId);

    return [
        'requested' => (bool) ($prefs['deactivation_requested'] ?? false),
        'reason' => $prefs['deactivation_reason'] ?? '',
        'comments' => $prefs['deactivation_comments'] ?? '',
    ];
}
