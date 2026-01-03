<?php
// Set the content type to JSON for all responses
header('Content-Type: application/json');

require_once '../config/db.php';
require_once '../includes/functions.php';

$response = ['success' => false, 'message' => 'An unknown error occurred.'];

// Security Check: Ensure the user is logged in for any action
if (!isLoggedIn()) {
    $response['message'] = 'Unauthorized access.';
    echo json_encode($response);
    exit();
}

$action = $_REQUEST['action'] ?? '';
$user_id = $_SESSION['user_id'];
$is_super_admin = ($_SESSION['role_id'] === 1);

switch ($action) {
    case 'get_tickets':
        // Admins see all tickets; regular users see only their own.
        if ($is_super_admin) {
            $sql = "SELECT t.*, u.username FROM support_tickets t JOIN users u ON t.user_id = u.id ORDER BY t.updated_at DESC";
            $result = query($mysqli, $sql);
        } else {
            $sql = "SELECT t.*, u.username FROM support_tickets t JOIN users u ON t.user_id = u.id WHERE t.user_id = ? ORDER BY t.updated_at DESC";
            $result = query($mysqli, $sql, [$user_id]);
        }

        if ($result['success']) {
            $response = ['success' => true, 'data' => $result['data']];
        } else {
            $response['message'] = 'Failed to fetch support tickets.';
        }
        break;

    case 'submit_ticket':
        $subject = $_POST['subject'] ?? '';
        $message = $_POST['message'] ?? '';
        $priority = $_POST['priority'] ?? 'medium';

        if (empty($subject) || empty($message)) {
            $response['message'] = 'Subject and message are required.';
            break;
        }

        $sql = "INSERT INTO support_tickets (user_id, subject, message, priority) VALUES (?, ?, ?, ?)";
        $result = query($mysqli, $sql, [$user_id, $subject, $message, $priority]);

        if ($result['success']) {
            $response = ['success' => true, 'message' => 'Support ticket submitted successfully!'];
        } else {
            $response['message'] = 'Database error: ' . $result['error'];
        }
        break;

    case 'create_ticket':
        $subject = trim($_POST['subject'] ?? '');
        $message = trim($_POST['message'] ?? '');
        $priority = trim($_POST['priority'] ?? 'medium');

        // Validation
        if (empty($subject) || empty($message)) {
            $response['message'] = 'Subject and message are required.';
            break;
        }

        // Sanitize priority
        $validPriorities = ['low', 'medium', 'high'];
        if (!in_array($priority, $validPriorities)) {
            $priority = 'medium';
        }

        // Insert ticket with correct columns
        $insert = query(
            $mysqli,
            "INSERT INTO support_tickets (user_id, subject, message, priority, status) 
             VALUES (?, ?, ?, ?, ?)",
            [$user_id, $subject, $message, $priority, 'open']
        );

        if ($insert['success']) {
            $response = [
                'success' => true,
                'message' => 'Support ticket submitted successfully.',
                'data' => ['ticket_id' => $insert['insert_id']]
            ];
        } else {
            $response['message'] = 'Unable to submit ticket. Please try again.';
        }
        break;

    case 'get_ticket':
        $ticket_id = (int) ($_GET['id'] ?? 0);

        if ($ticket_id <= 0) {
            $response['message'] = 'Invalid ticket ID.';
            break;
        }

        // Verify ticket belongs to logged-in user or user is admin
        if ($is_super_admin) {
            $ticketRes = query(
                $mysqli,
                "SELECT * FROM support_tickets WHERE id = ?",
                [$ticket_id]
            );
        } else {
            $ticketRes = query(
                $mysqli,
                "SELECT * FROM support_tickets WHERE id = ? AND user_id = ?",
                [$ticket_id, $user_id]
            );
        }

        if (!$ticketRes['success'] || empty($ticketRes['data'])) {
            http_response_code(403);
            $response['message'] = 'Ticket not found or access denied.';
            break;
        }

        $response = [
            'success' => true,
            'data' => $ticketRes['data'][0]
        ];
        break;

    case 'update_status':
        // Only Super Admins can update a ticket's status.
        if (!$is_super_admin) {
            $response['message'] = 'You do not have permission to perform this action.';
            break;
        }

        $ticket_id = isset($_POST['ticket_id']) ? (int) $_POST['ticket_id'] : 0;
        $status = $_POST['status'] ?? '';

        if ($ticket_id > 0 && in_array($status, ['open', 'in_progress', 'closed'])) {
            $sql = "UPDATE support_tickets SET status = ? WHERE id = ?";
            $result = query($mysqli, $sql, [$status, $ticket_id]);
            if ($result['success']) {
                $response = ['success' => true, 'message' => 'Ticket status updated.'];
            } else {
                $response['message'] = 'Failed to update ticket status.';
            }
        } else {
            $response['message'] = 'Invalid ticket ID or status provided.';
        }
        break;

    default:
        $response['message'] = 'Invalid action specified.';
        break;
}

// Echo the final JSON response
echo json_encode($response);
exit();
?>