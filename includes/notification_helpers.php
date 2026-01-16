<?php
/**
 * Notification Helper Functions
 * 
 * Provides functions for creating and managing notifications.
 */

/**
 * Create a new notification for a user.
 *
 * @param mysqli $mysqli Database connection
 * @param int $user_id The ID of the user to notify
 * @param string $type Notification type (system, leave, attendance, payroll, etc.)
 * @param string $title Short title for the notification
 * @param string $message Detailed message
 * @param int|null $related_id Optional ID of the related entity
 * @param string|null $related_type Optional type of the related entity
 * @return bool True on success, false on failure
 */
function createNotification($mysqli, $user_id, $type, $title, $message, $related_id = null, $related_type = null)
{
    $stmt = $mysqli->prepare(
        "INSERT INTO notifications (user_id, type, title, message, related_id, related_type, is_read, created_at) 
         VALUES (?, ?, ?, ?, ?, ?, 0, NOW())"
    );

    if (!$stmt) {
        return false;
    }

    $stmt->bind_param("isssis", $user_id, $type, $title, $message, $related_id, $related_type);
    $result = $stmt->execute();
    $stmt->close();

    return $result;
}

/**
 * Get the count of unread notifications for a user.
 *
 * @param mysqli $mysqli Database connection
 * @param int $user_id The user ID
 * @return int The count of unread notifications
 */
function getUnreadNotificationCount($mysqli, $user_id)
{
    $stmt = $mysqli->prepare("SELECT COUNT(*) as count FROM notifications WHERE user_id = ? AND is_read = 0");
    if (!$stmt) {
        return 0;
    }

    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $stmt->close();

    return (int) ($row['count'] ?? 0);
}

/**
 * Get notifications for a user.
 *
 * @param mysqli $mysqli Database connection
 * @param int $user_id The user ID
 * @param int $limit Maximum number of notifications to fetch
 * @param bool $unread_only If true, only fetch unread notifications
 * @return array Array of notification records
 */
function getNotifications($mysqli, $user_id, $limit = 20, $unread_only = false)
{
    $sql = "SELECT id, type, title, message, related_id, related_type, is_read, created_at 
            FROM notifications 
            WHERE user_id = ?";

    if ($unread_only) {
        $sql .= " AND is_read = 0";
    }

    $sql .= " ORDER BY created_at DESC LIMIT ?";

    $stmt = $mysqli->prepare($sql);
    if (!$stmt) {
        return [];
    }

    $stmt->bind_param("ii", $user_id, $limit);
    $stmt->execute();
    $result = $stmt->get_result();
    $notifications = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();

    return $notifications;
}

/**
 * Mark a single notification as read.
 *
 * @param mysqli $mysqli Database connection
 * @param int $notification_id The notification ID
 * @param int $user_id The user ID (for security)
 * @return bool True on success
 */
function markNotificationAsRead($mysqli, $notification_id, $user_id)
{
    $stmt = $mysqli->prepare(
        "UPDATE notifications SET is_read = 1, read_at = NOW() WHERE id = ? AND user_id = ?"
    );
    if (!$stmt) {
        return false;
    }

    $stmt->bind_param("ii", $notification_id, $user_id);
    $result = $stmt->execute();
    $stmt->close();

    return $result;
}

/**
 * Mark all notifications as read for a user.
 *
 * @param mysqli $mysqli Database connection
 * @param int $user_id The user ID
 * @return bool True on success
 */
function markAllNotificationsAsRead($mysqli, $user_id)
{
    $stmt = $mysqli->prepare(
        "UPDATE notifications SET is_read = 1, read_at = NOW() WHERE user_id = ? AND is_read = 0"
    );
    if (!$stmt) {
        return false;
    }

    $stmt->bind_param("i", $user_id);
    $result = $stmt->execute();
    $stmt->close();

    return $result;
}

/**
 * Helper to get the link for a notification based on type and related entity.
 *
 * @param string $type Notification type
 * @param string|null $related_type Related entity type
 * @param int|null $related_id Related entity ID
 * @param int $role_id User's role ID for proper routing
 * @return string The link URL
 */
function getNotificationLink($type, $related_type, $related_id, $role_id)
{
    $base = '/hrms';

    switch ($type) {
        case 'leave':
            // Both approvers and employees go to company leaves page
            return $base . '/company/leaves.php';

        case 'payroll':
            return $base . '/employee/payslips.php';

        case 'attendance':
            return $base . '/employee/attendance.php';

        case 'performance':
            return $base . '/employee/performance.php';

        case 'task':
            if ($role_id == 6) {
                return $base . '/manager/task_management.php';
            }
            return $base . '/employee/tasks.php';

        default:
            return $base . '/index.php';
    }
}
?>