<?php
header('Content-Type: application/json');
require_once '../config/db.php';
require_once '../includes/functions.php';

$response = ['success' => false, 'message' => 'An unknown error occurred.'];

// Security Check: Only Super Admin (Role ID 1) can access
if (!isLoggedIn() || $_SESSION['role_id'] !== 1) {
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
        $settings = $_POST['settings'] ?? [];
        $all_success = true;
        foreach ($settings as $key => $value) {
            $result = query($mysqli, "UPDATE system_settings SET setting_value = ?, updated_by = ? WHERE setting_key = ?", [$value, $user_id, $key]);
            if (!$result['success']) {
                $all_success = false;
            }
        }
        if ($all_success) {
            $response = ['success' => true, 'message' => 'Settings saved successfully!'];
        } else {
            $response['message'] = 'Could not save all settings.';
        }
        break;

    case 'import_holidays':
        $year = (int) ($_POST['year'] ?? date('Y'));
        $api_key = getenv('google_calender_api');

        if (empty($api_key)) {
            $response['message'] = 'Google Calendar API key is not configured on the server.';
            break;
        }

        $calendar_id = urlencode('en.indian#holiday@group.v.calendar.google.com');
        $time_min = "{$year}-01-01T00:00:00Z";
        $time_max = "{$year}-12-31T23:59:59Z";

        $url = "https://www.googleapis.com/calendar/v3/calendars/{$calendar_id}/events?key={$api_key}&timeMin={$time_min}&timeMax={$time_max}&orderBy=startTime&singleEvents=true";

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $api_response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($http_code !== 200) {
            $response['message'] = 'Failed to connect to Google Calendar API. Please check the API key and calendar ID.';
            break;
        }

        $data = json_decode($api_response, true);

        if (isset($data['items']) && is_array($data['items'])) {
            $holidays = [];
            foreach ($data['items'] as $item) {
                if (isset($item['summary']) && isset($item['start']['date'])) {
                    $holidays[] = ['name' => $item['summary'], 'date' => $item['start']['date']];
                }
            }

            if (!empty($holidays)) {
                $sql = "INSERT INTO global_holidays (holiday_name, holiday_date) VALUES (?, ?) ON DUPLICATE KEY UPDATE holiday_name = VALUES(holiday_name)";
                $stmt = $mysqli->prepare($sql);
                $imported_count = 0;
                foreach ($holidays as $holiday) {
                    $stmt->bind_param('ss', $holiday['name'], $holiday['date']);
                    if ($stmt->execute()) {
                        $imported_count++;
                    }
                }
                $stmt->close();
                $response = ['success' => true, 'message' => "Successfully imported {$imported_count} holidays for {$year}."];
            } else {
                $response['message'] = "No holidays found for the year {$year}.";
            }
        } else {
            $response['message'] = 'Received an invalid response from the Google Calendar API.';
            if (isset($data['error']['message'])) {
                $response['message'] .= ' Error: ' . $data['error']['message'];
            }
        }
        break;

    case 'get_global_holidays':
        $result = query($mysqli, "SELECT id, holiday_name, holiday_date FROM global_holidays ORDER BY holiday_date ASC");
        if ($result['success']) {
            $response = ['success' => true, 'data' => $result['data']];
        } else {
            $response['message'] = 'Failed to fetch global holidays.';
        }
        break;

    case 'add_edit_global_holiday':
        $id = (int) ($_POST['id'] ?? 0);
        $name = $_POST['holiday_name'] ?? '';
        $date = $_POST['holiday_date'] ?? '';

        if (empty($name) || empty($date)) {
            $response['message'] = 'Holiday name and date are required.';
            break;
        }

        if ($id === 0) { // Add new
            $sql = "INSERT INTO global_holidays (holiday_name, holiday_date) VALUES (?, ?)";
            $params = [$name, $date];
        } else { // Edit existing
            $sql = "UPDATE global_holidays SET holiday_name = ?, holiday_date = ? WHERE id = ?";
            $params = [$name, $date, $id];
        }

        $result = query($mysqli, $sql, $params);
        if ($result['success']) {
            $response = ['success' => true, 'message' => 'Holiday saved successfully!'];
        } else {
            $response['message'] = 'Failed to save holiday.';
        }
        break;

    case 'delete_global_holiday':
        $id = (int) ($_POST['id'] ?? 0);
        if ($id > 0) {
            $result = query($mysqli, "DELETE FROM global_holidays WHERE id = ?", [$id]);
            if ($result['success']) {
                $response = ['success' => true, 'message' => 'Holiday deleted successfully!'];
            } else {
                $response['message'] = 'Failed to delete holiday.';
            }
        }
        break;

    default:
        $response['message'] = 'Invalid action specified.';
        break;
}

echo json_encode($response);

exit();
?>