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
            $name = trim($_POST['name'] ?? '');
            $address = trim($_POST['address'] ?? '');
            $email = trim($_POST['email'] ?? '');
            $phone = trim($_POST['phone'] ?? '');

            // Validation
            $validation_errors = [];

            // Name validation
            if (empty($name)) {
                $validation_errors['name'] = 'Company name is required.';
            } elseif (strlen($name) < 2) {
                $validation_errors['name'] = 'Company name must be at least 2 characters.';
            } elseif (strlen($name) > 100) {
                $validation_errors['name'] = 'Company name must not exceed 100 characters.';
            }

            // Email validation (if provided)
            if (!empty($email)) {
                if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    $validation_errors['email'] = 'Please enter a valid email address.';
                } elseif (strlen($email) > 100) {
                    $validation_errors['email'] = 'Email must not exceed 100 characters.';
                }
            }

            // Phone validation (if provided)
            if (!empty($phone)) {
                if (!preg_match('/^[\d\s\-()\.+]{10,20}$/', $phone)) {
                    $validation_errors['phone'] = 'Phone must be between 10-20 digits/characters.';
                }
            }

            // Address validation (optional but check length)
            if (strlen($address) > 255) {
                $validation_errors['address'] = 'Address must not exceed 255 characters.';
            }

            // If validation fails, return errors
            if (!empty($validation_errors)) {
                $response['success'] = false;
                $response['message'] = 'Validation failed. Please check the errors below.';
                $response['errors'] = $validation_errors;
                echo json_encode($response);
                exit();
            }

            if ($action === 'add') {
                $sql = "INSERT INTO companies (name, address, email, phone) VALUES (?, ?, ?, ?)";
                $result = query($mysqli, $sql, [$name, $address, $email, $phone]);
            } else { // Edit
                $sql = "UPDATE companies SET name = ?, address = ?, email = ?, phone = ? WHERE id = ?";
                $result = query($mysqli, $sql, [$name, $address, $email, $phone, $company_id]);
            }

            if ($result['success']) {
                $new_company_id = ($action === 'add') ? $result['insert_id'] : $company_id;

                // Fetch the complete company data to send back to the frontend
                $company_query = query($mysqli, "SELECT * FROM companies WHERE id = ?", [$new_company_id]);

                if ($company_query['success'] && !empty($company_query['data'])) {
                    $company_data = $company_query['data'][0];
                    $response = ['success' => true, 'message' => 'Company saved successfully!', 'company' => $company_data];
                } else {
                    $response = ['success' => false, 'message' => 'Company saved but could not retrieve updated data.'];
                }
            } else {
                $response['message'] = 'Database error: ' . ($result['error'] ?? 'Unknown error');
            }
        }
        break;

    case 'delete':
        if ($method === 'POST') {
            $company_id = isset($_POST['company_id']) ? (int) $_POST['company_id'] : 0;

            // Validation
            if ($company_id <= 0) {
                $response['message'] = 'Invalid company ID provided.';
                break;
            }

            // Check if company exists before deletion
            $check_result = query($mysqli, "SELECT id FROM companies WHERE id = ?", [$company_id]);
            if (!$check_result['success'] || empty($check_result['data'])) {
                $response['message'] = 'Company not found.';
                break;
            }

            $delete_result = query($mysqli, "DELETE FROM companies WHERE id = ?", [$company_id]);

            if ($delete_result['success']) {
                $response = ['success' => true, 'message' => 'Company deleted successfully!'];
            } else {
                $response['message'] = 'Failed to delete company: ' . ($delete_result['error'] ?? 'Unknown error');
            }
        }
        break;

    // ─── IoT Device Management ─────────────────────────────────
    case 'list_devices':
        $company_id = isset($_REQUEST['company_id']) ? (int) $_REQUEST['company_id'] : 0;
        if ($company_id <= 0) {
            $response['message'] = 'Invalid company ID.';
            break;
        }
        $result = query(
            $mysqli,
            "SELECT id, device_name, device_token, location, status, last_heartbeat, created_at
             FROM iot_devices WHERE company_id = ? ORDER BY created_at DESC",
            [$company_id]
        );
        if ($result['success']) {
            $response = ['success' => true, 'devices' => $result['data'] ?? []];
        } else {
            $response['message'] = 'Failed to fetch devices.';
        }
        break;

    case 'add_device':
        if ($method === 'POST') {
            $company_id = isset($_POST['company_id']) ? (int) $_POST['company_id'] : 0;
            $device_name = trim($_POST['device_name'] ?? '');
            $location = trim($_POST['location'] ?? '');

            $errors = [];
            if ($company_id <= 0) {
                $errors['company_id'] = 'Invalid company ID.';
            }
            if (empty($device_name)) {
                $errors['device_name'] = 'Device name is required.';
            } elseif (strlen($device_name) > 100) {
                $errors['device_name'] = 'Device name must not exceed 100 characters.';
            }
            if (strlen($location) > 100) {
                $errors['location'] = 'Location must not exceed 100 characters.';
            }

            if (!empty($errors)) {
                $response['message'] = 'Validation failed.';
                $response['errors'] = $errors;
                echo json_encode($response);
                exit();
            }

            // Verify company exists
            $check = query($mysqli, "SELECT id FROM companies WHERE id = ?", [$company_id]);
            if (!$check['success'] || empty($check['data'])) {
                $response['message'] = 'Company not found.';
                break;
            }

            // Generate a secure unique device token
            $device_token = bin2hex(random_bytes(32));

            $insert = query(
                $mysqli,
                "INSERT INTO iot_devices (company_id, device_name, device_token, location) VALUES (?, ?, ?, ?)",
                [$company_id, $device_name, $device_token, $location ?: null]
            );

            if ($insert['success']) {
                $new_id = $insert['insert_id'];
                $device_query = query(
                    $mysqli,
                    "SELECT id, device_name, device_token, location, status, last_heartbeat, created_at
                     FROM iot_devices WHERE id = ?",
                    [$new_id]
                );
                $response = [
                    'success' => true,
                    'message' => 'IoT device added successfully!',
                    'device' => ($device_query['success'] && !empty($device_query['data'])) ? $device_query['data'][0] : null
                ];
            } else {
                $response['message'] = 'Failed to add device: ' . ($insert['error'] ?? 'Unknown error');
            }
        }
        break;

    case 'delete_device':
        if ($method === 'POST') {
            $device_id = isset($_POST['device_id']) ? (int) $_POST['device_id'] : 0;
            if ($device_id <= 0) {
                $response['message'] = 'Invalid device ID.';
                break;
            }
            $del = query($mysqli, "DELETE FROM iot_devices WHERE id = ?", [$device_id]);
            if ($del['success']) {
                $response = ['success' => true, 'message' => 'Device deleted successfully!'];
            } else {
                $response['message'] = 'Failed to delete device.';
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