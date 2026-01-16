<?php
/**
 * Notifications API
 * 
 * Endpoints:
 * - get_notifications: Fetch notifications for the current user
 * - get_unread_count: Get count of unread notifications
 * - mark_read: Mark a single notification as read
 * - mark_all_read: Mark all notifications as read
 */

header('Content-Type: application/json');
require_once '../config/db.php';
require_once '../includes/functions.php';
require_once '../includes/notification_helpers.php';

$response = ['success' => false, 'message' => 'An unknown error occurred.'];

if (!isLoggedIn()) {
    $response['message'] = 'Unauthorized access.';
    http_response_code(401);
    echo json_encode($response);
    exit();
}

$user_id = $_SESSION['user_id'];
$role_id = $_SESSION['role_id'];
$action = $_REQUEST['action'] ?? '';

switch ($action) {
    case 'get_notifications':
        $limit = (int) ($_GET['limit'] ?? 20);
        $unread_only = isset($_GET['unread_only']) && $_GET['unread_only'] === 'true';

        $notifications = getNotifications($mysqli, $user_id, $limit, $unread_only);

        // Add link to each notification
        foreach ($notifications as &$notification) {
            $notification['link'] = getNotificationLink(
                $notification['type'],
                $notification['related_type'],
                $notification['related_id'],
                $role_id
            );
            // Format the date for display
            $notification['time_ago'] = timeAgo($notification['created_at']);
        }

        $response = [
            'success' => true,
            'data' => $notifications,
            'unread_count' => getUnreadNotificationCount($mysqli, $user_id)
        ];
        break;

    case 'get_unread_count':
        $count = getUnreadNotificationCount($mysqli, $user_id);
        $response = [
            'success' => true,
            'count' => $count
        ];
        break;

    case 'mark_read':
        $notification_id = (int) ($_POST['notification_id'] ?? 0);

        if (!$notification_id) {
            $response['message'] = 'Notification ID is required.';
            break;
        }

        $result = markNotificationAsRead($mysqli, $notification_id, $user_id);

        if ($result) {
            $response = [
                'success' => true,
                'message' => 'Notification marked as read.'
            ];
        } else {
            $response['message'] = 'Failed to mark notification as read.';
        }
        break;

    case 'mark_all_read':
        $result = markAllNotificationsAsRead($mysqli, $user_id);

        if ($result) {
            $response = [
                'success' => true,
                'message' => 'All notifications marked as read.'
            ];
        } else {
            $response['message'] = 'Failed to mark notifications as read.';
        }
        break;

    default:
        $response['message'] = 'Invalid action specified.';
        http_response_code(400);
        break;
}

echo json_encode($response);
exit();

/**
 * Helper function to format time ago
 */
function timeAgo($datetime)
{
    $time = strtotime($datetime);
    $now = time();
    $diff = $now - $time;

    if ($diff < 60) {
        return 'Just now';
    } elseif ($diff < 3600) {
        $mins = floor($diff / 60);
        return $mins . ' min' . ($mins > 1 ? 's' : '') . ' ago';
    } elseif ($diff < 86400) {
        $hours = floor($diff / 3600);
        return $hours . ' hour' . ($hours > 1 ? 's' : '') . ' ago';
    } elseif ($diff < 604800) {
        $days = floor($diff / 86400);
        return $days . ' day' . ($days > 1 ? 's' : '') . ' ago';
    } else {
        return date('M j, Y', $time);
    }
}
?>