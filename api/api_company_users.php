<?php
header('Content-Type: application/json');
require_once '../config/db.php';
require_once '../includes/functions.php';

$response = ['success' => false, 'message' => 'An unknown error occurred.'];

// Security Check: Must be a logged-in Company Admin or HR Manager
if (!isLoggedIn() || !in_array($_SESSION['role_id'], [2, 3])) {
    $response['message'] = 'Unauthorized access.';
    echo json_encode($response);
    exit();
}

$action = $_REQUEST['action'] ?? '';
$company_id = $_SESSION['company_id'];
$is_c_admin = $_SESSION['role_id'] === 2;

switch ($action) {
    case 'add_user':
        $username = $_POST['username'] ?? '';
        $email = $_POST['email'] ?? '';
        $password = $_POST['password'] ?? '';
        // Get the role from the form, default to 'Employee' (4) if not provided
        $role_id = isset($_POST['role_id']) ? (int) $_POST['role_id'] : 4;

        // --- Security Check ---
        // Only Company Admin can create HR Manager (4)
        if ($role_id === 4 && !$is_c_admin) {
            $response['message'] = 'Only Company Admin can create HR Manager accounts.';
            break;
        }

        // A Company Admin or HR Manager can create Employees (3) and Managers (6)
        if (!in_array($role_id, [3, 4, 6])) {
            $response['message'] = 'You do not have permission to create a user with this role.';
            break;
        }

        // Server-side validation
        if (empty($username) || empty($email) || empty($password)) {
            $response['message'] = 'Username, email, and password are required.';
            break;
        }

        // Validate username length
        if (strlen($username) < 3) {
            $response['message'] = 'Username must be at least 3 characters long.';
            break;
        }

        // Validate email format
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $response['message'] = 'Invalid email format.';
            break;
        }

        // Check if username or email already exists
        $check_sql = "SELECT id FROM users WHERE username = ? OR email = ?";
        $check_result = query($mysqli, $check_sql, [$username, $email]);
        if ($check_result['success'] && !empty($check_result['data'])) {
            $response['message'] = 'A user with that username or email already exists.';
            break;
        }

        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        $sql = "INSERT INTO users (username, email, password, company_id, role_id, status) VALUES (?, ?, ?, ?, ?, 'active')";
        $result = query($mysqli, $sql, [$username, $email, $hashed_password, $company_id, $role_id]);

        if ($result['success']) {
            $new_user_id = $result['insert_id'];
            $response = [
                'success' => true,
                'message' => 'User account created successfully!',
                'user' => ['id' => $new_user_id, 'username' => $username]
            ];
        } else {
            $response['message'] = 'Database error: ' . $result['error'];
        }
        break;

    case 'check_username':
        $username = $_GET['username'] ?? '';

        if (empty($username)) {
            $response['message'] = 'Username is required.';
            break;
        }

        // Check if username already exists
        $check_sql = "SELECT id FROM users WHERE username = ?";
        $check_result = query($mysqli, $check_sql, [$username]);

        if ($check_result['success']) {
            $response = [
                'available' => empty($check_result['data'])
            ];
        } else {
            $response['message'] = 'Error checking username availability.';
        }
        break;

    default:
        $response['message'] = 'Invalid action specified.';
        break;
}

echo json_encode($response);
exit();
?>