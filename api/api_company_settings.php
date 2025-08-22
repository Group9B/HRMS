<?php
header('Content-Type: application/json');
require_once '../config/db.php';
require_once '../includes/functions.php';

$response = ['success' => false, 'message' => 'An unknown error occurred.'];

// Security Check: Must be a logged-in Company Admin
if (!isLoggedIn() || !isset($_SESSION['role_id']) || $_SESSION['role_id'] !== 2) {
    $response['message'] = 'Unauthorized access.';
    echo json_encode($response);
    exit();
}

$action = $_REQUEST['action'] ?? '';
$company_id = $_SESSION['company_id']; // All operations are scoped to the admin's company

switch ($action) {
    case 'update_settings':
        $name = $_POST['name'] ?? '';
        $address = $_POST['address'] ?? '';
        $email = $_POST['email'] ?? '';
        $phone = $_POST['phone'] ?? '';

        if (empty($name) || empty($email)) {
            $response['message'] = 'Company name and email are required.';
            break;
        }

        // The WHERE clause ensures a Company Admin can ONLY update their own company.
        $sql = "UPDATE companies SET name = ?, address = ?, email = ?, phone = ? WHERE id = ?";
        $result = query($mysqli, $sql, [$name, $address, $email, $phone, $company_id]);

        if ($result['success']) {
            $response = ['success' => true, 'message' => 'Company settings saved successfully!'];
        } else {
            $response['message'] = 'Database error: ' . $result['error'];
        }
        break;

    default:
        $response['message'] = 'Invalid action specified.';
        break;
}

echo json_encode($response);
exit();
?>