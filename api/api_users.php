<?php
// Set the content type to JSON for all responses
header('Content-Type: application/json');

require_once '../config/db.php';
require_once '../includes/functions.php'; // Ensure your query() function is here

// Get the request method and action
$method = $_SERVER['REQUEST_METHOD'];
$action = $_REQUEST['action'] ?? '';
$response = ['success' => false, 'message' => 'An unknown error occurred.'];

// Use a switch to handle different actions
switch ($action) {
    case 'add':
    case 'edit':
        if ($method === 'POST') {
            $user_id = isset($_POST['user_id']) ? (int) $_POST['user_id'] : 0;
            $username = $_POST['username'] ?? '';
            $email = $_POST['email'] ?? '';
            $password = $_POST['password'] ?? '';
            $company_id = !empty($_POST['company_id']) ? (int) $_POST['company_id'] : null;
            $role_id = isset($_POST['role_id']) ? (int) $_POST['role_id'] : 0;
            $status = $_POST['status'] ?? 'active';

            if (empty($username) || empty($email) || empty($role_id)) {
                $response['message'] = 'Username, email, and role are required.';
                break;
            }

            if ($action === 'add') {
                if (empty($password)) {
                    $response['message'] = 'Password is required for new users.';
                    break;
                }
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                $sql = "INSERT INTO users (username, email, password, company_id, role_id, status) VALUES (?, ?, ?, ?, ?, ?)";
                $result = query($mysqli, $sql, [$username, $email, $hashed_password, $company_id, $role_id, $status]);
            } else { // Edit
                if (!empty($password)) {
                    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                    $sql = "UPDATE users SET username = ?, email = ?, password = ?, company_id = ?, role_id = ?, status = ? WHERE id = ?";
                    $result = query($mysqli, $sql, [$username, $email, $hashed_password, $company_id, $role_id, $status, $user_id]);
                } else {
                    $sql = "UPDATE users SET username = ?, email = ?, company_id = ?, role_id = ?, status = ? WHERE id = ?";
                    $result = query($mysqli, $sql, [$username, $email, $company_id, $role_id, $status, $user_id]);
                }
            }

            if ($result['success']) {
                $new_user_id = $result['insert_id'] ?? $user_id;

                // Fetch the complete user data (with joins) to send back to the frontend
                $user_query = "SELECT u.*, c.name as company_name, r.name as role_name FROM users u LEFT JOIN companies c ON u.company_id = c.id LEFT JOIN roles r ON u.role_id = r.id WHERE u.id = ?";
                $user_query_result = query($mysqli, $user_query, [$new_user_id]);

                if ($user_query_result['success'] && !empty($user_query_result['data'])) {
                    $response = ['success' => true, 'message' => 'User saved successfully!', 'user' => $user_query_result['data'][0]];
                } else {
                    $response['message'] = 'User saved, but failed to fetch updated data.';
                }
            } else {
                $response['message'] = 'Database error: ' . $result['error'];
            }
        }
        break;

    case 'delete':
        if ($method === 'POST') {
            $user_id = isset($_POST['user_id']) ? (int) $_POST['user_id'] : 0;
            if ($user_id > 0) {
                $sql = "DELETE FROM users WHERE id = ?";
                $result = query($mysqli, $sql, [$user_id]);
                if ($result['success']) {
                    $response = ['success' => true, 'message' => 'User deleted successfully!'];
                } else {
                    $response['message'] = 'Failed to delete user: ' . $result['error'];
                }
            }
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