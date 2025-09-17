<?php
header('Content-Type: application/json');
require_once '../config/db.php';
require_once '../includes/functions.php';

$response = ['success' => false, 'message' => 'An unknown error occurred.'];

if (!isLoggedIn() || $_SESSION['role_id'] !== 1) { // Super Admin only
    $response['message'] = 'Unauthorized access.';
    echo json_encode($response);
    exit();
}

$action = $_REQUEST['action'] ?? '';
$user_id = $_SESSION['user_id'];

switch ($action) {
    case 'get_settings':
        $result = query($mysqli, "SELECT setting_key, setting_value FROM system_settings");
        if ($result['success']) {
            $settings = [];
            foreach ($result['data'] as $row) {
                $settings[$row['setting_key']] = $row['setting_value'];
            }
            $response = ['success' => true, 'data' => $settings];
        }
        break;

    case 'save_settings':
        foreach ($_POST as $key => $value) {
            if ($key !== 'action') {
                query($mysqli, "UPDATE system_settings SET setting_value = ?, updated_by = ? WHERE setting_key = ?", [$value, $user_id, $key]);
            }
        }
        $response = ['success' => true, 'message' => 'Settings saved successfully!'];
        break;

    // --- NEW: Global Holiday Actions ---
    case 'get_global_holidays':
        $result = query($mysqli, "SELECT * FROM global_holidays ORDER BY holiday_date ASC");
        $response = ['success' => true, 'data' => $result['data'] ?? []];
        break;

    case 'import_holidays':
        $year = (int) ($_POST['year'] ?? date('Y'));
        $api_url = "https://date.nager.at/api/v3/PublicHolidays/{$year}/IN";

        // Use file_get_contents with a stream context to handle potential errors
        $context = stream_context_create(['http' => ['ignore_errors' => true]]);
        $json_data = @file_get_contents($api_url, false, $context);

        if ($json_data === false || empty($json_data)) {
            $response['message'] = 'Could not connect to the holiday API service. Please try again later.';
            break;
        }

        $holidays = json_decode($json_data, true);
        if (json_last_error() !== JSON_ERROR_NONE || !is_array($holidays)) {
            $response['message'] = 'Received invalid data from the holiday API.';
            break;
        }

        $imported_count = 0;
        foreach ($holidays as $holiday) {
            // Check if holiday already exists for that date to prevent duplicates
            $check = query($mysqli, "SELECT id FROM global_holidays WHERE holiday_date = ?", [$holiday['date']]);
            if (empty($check['data'])) {
                query($mysqli, "INSERT INTO global_holidays (holiday_name, holiday_date) VALUES (?, ?)", [$holiday['name'], $holiday['date']]);
                $imported_count++;
            }
        }
        $response = ['success' => true, 'message' => "Successfully imported {$imported_count} new holidays for {$year}!"];
        break;

    case 'delete_global_holiday':
        $id = (int) ($_POST['id'] ?? 0);
        $result = query($mysqli, "DELETE FROM global_holidays WHERE id = ?", [$id]);
        if ($result['success']) {
            $response = ['success' => true, 'message' => 'Global holiday deleted.'];
        }
        break;

    default:
        $response['message'] = 'Invalid action specified.';
        break;
}

echo json_encode($response);
exit();
?>