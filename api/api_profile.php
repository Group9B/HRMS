<?php
header('Content-Type: application/json');
require_once '../config/db.php';
require_once '../includes/functions.php';

$response = ['success' => false, 'message' => 'An unknown error occurred.'];

// Security Check: Must be a logged-in user
if (!isLoggedIn()) {
    $response['message'] = 'Unauthorized access. Please log in.';
    echo json_encode($response);
    exit();
}

$action = $_REQUEST['action'] ?? '';
$user_id = $_SESSION['user_id'];

switch ($action) {
    case 'get_profile_data':
        // This single, efficient query fetches all necessary data for the profile page
        $sql = "
            SELECT 
                e.*, 
                u.username, 
                u.email,
                d.name as department_name,
                des.name as designation_name
            FROM employees e
            JOIN users u ON e.user_id = u.id
            LEFT JOIN departments d ON e.department_id = d.id
            LEFT JOIN designations des ON e.designation_id = des.id
            WHERE e.user_id = ?
        ";
        $result = query($mysqli, $sql, [$user_id]);

        if ($result['success'] && !empty($result['data'])) {
            $response = ['success' => true, 'data' => $result['data'][0]];
        } else {
            $response['message'] = 'Failed to fetch profile data.';
        }
        break;

    case 'update_personal_info':
        // Sanitize and retrieve POST data
        $first_name = $_POST['first_name'] ?? '';
        $last_name = $_POST['last_name'] ?? '';
        $dob = !empty($_POST['dob']) ? $_POST['dob'] : null;
        $gender = $_POST['gender'] ?? '';
        $contact = $_POST['contact'] ?? '';
        $address = $_POST['address'] ?? '';
        $emergency_contact = $_POST['emergency_contact'] ?? '';

        if (empty($first_name) || empty($last_name)) {
            $response['message'] = 'First name and last name are required.';
            break;
        }

        // Securely update the employee record for the logged-in user
        $sql = "UPDATE employees SET first_name = ?, last_name = ?, dob = ?, gender = ?, contact = ?, address = ?, emergency_contact = ? WHERE user_id = ?";
        $params = [$first_name, $last_name, $dob, $gender, $contact, $address, $emergency_contact, $user_id];
        $result = query($mysqli, $sql, $params);

        if ($result['success']) {
            $response = ['success' => true, 'message' => 'Profile updated successfully!'];
        } else {
            $response['message'] = 'Failed to update profile. Error: ' . $result['error'];
        }
        break;
        
    default:
        $response['message'] = 'Invalid action specified.';
        break;
}

echo json_encode($response);
exit();
?>
