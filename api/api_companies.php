<?php
// Set the content type to JSON for all responses
header('Content-Type: application/json');

require_once '../config/db.php';
require_once '../includes/functions.php';

// Get the request method and action
$method = $_SERVER['REQUEST_METHOD'];
$action = $_REQUEST['action'] ?? '';
$response = ['success' => false, 'message' => 'An unknown error occurred.'];

// Use a switch to handle different actions
switch ($action) {
    case 'add':
    case 'edit':
        if ($method === 'POST') {
            $company_id = isset($_POST['company_id']) ? (int) $_POST['company_id'] : 0;
            $name = $_POST['name'] ?? '';
            $address = $_POST['address'] ?? '';
            $email = $_POST['email'] ?? '';
            $phone = $_POST['phone'] ?? '';

            if (empty($name)) {
                $response['message'] = 'Company name is required.';
                break;
            }

            if ($action === 'add') {
                $sql = "INSERT INTO companies (name, address, email, phone) VALUES (?, ?, ?, ?)";
                $stmt = $mysqli->prepare($sql);
                $stmt->bind_param('ssss', $name, $address, $email, $phone);
            } else { // Edit
                $sql = "UPDATE companies SET name = ?, address = ?, email = ?, phone = ? WHERE id = ?";
                $stmt = $mysqli->prepare($sql);
                $stmt->bind_param('ssssi', $name, $address, $email, $phone, $company_id);
            }

            if ($stmt->execute()) {
                $new_company_id = ($action === 'add') ? $mysqli->insert_id : $company_id;

                // Fetch the complete company data to send back to the frontend
                $company_query = "SELECT * FROM companies WHERE id = ?";
                $company_stmt = $mysqli->prepare($company_query);
                $company_stmt->bind_param('i', $new_company_id);
                $company_stmt->execute();
                $result = $company_stmt->get_result();
                $company_data = $result->fetch_assoc();

                $response = ['success' => true, 'message' => 'Company saved successfully!', 'company' => $company_data];
            } else {
                $response['message'] = 'Database error: ' . $stmt->error;
            }
        }
        break;

    case 'delete':
        if ($method === 'POST') {
            $company_id = isset($_POST['company_id']) ? (int) $_POST['company_id'] : 0;
            if ($company_id > 0) {
                $sql = "DELETE FROM companies WHERE id = ?";
                $stmt = $mysqli->prepare($sql);
                $stmt->bind_param('i', $company_id);
                if ($stmt->execute()) {
                    $response = ['success' => true, 'message' => 'Company deleted successfully!'];
                } else {
                    $response['message'] = 'Failed to delete company.';
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