<?php
// Set the content type to JSON for all responses
header('Content-Type: application/json');

require_once '../config/db.php';
require_once '../includes/functions.php';

$response = ['success' => false, 'message' => 'An unknown error occurred.'];

// Security Check: Ensure the user is a logged-in Super Admin
if (!isLoggedIn() || !isset($_SESSION['role_id']) || $_SESSION['role_id'] !== 1) {
    $response['message'] = 'Unauthorized access.';
    echo json_encode($response);
    exit();
}

// Handle POST request to update settings
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $errors = [];
    // Loop through each submitted setting and update it in the database
    foreach ($_POST as $key => $value) {
        // Sanitize the key to be safe, although it comes from our form
        $setting_key = htmlspecialchars($key);
        $sql = "UPDATE system_settings SET setting_value = ? WHERE setting_key = ?";
        $result = query($mysqli, $sql, [$value, $setting_key]);

        if (!$result['success']) {
            $errors[] = "Failed to update setting: " . $setting_key;
        }
    }

    if (empty($errors)) {
        $response = ['success' => true, 'message' => 'System settings saved successfully!'];
    } else {
        $response['message'] = 'Some settings could not be saved.';
        $response['errors'] = $errors;
    }
} else {
    $response['message'] = 'Invalid request method.';
}

// Echo the final JSON response
echo json_encode($response);
exit();
?>