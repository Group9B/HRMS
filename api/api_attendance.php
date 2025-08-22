<?php
// Set the content type to application/json for all responses from this file.
header('Content-Type: application/json');

require_once '../config/db.php';
require_once '../includes/functions.php';

// --- SECURITY & SESSION ---
// Security Check: Must be a logged-in Company Admin or HR Manager
if (!isLoggedIn() || !in_array($_SESSION['role_id'], [2, 3])) {
    // Return a JSON error and stop execution if not authorized.
    http_response_code(403); // Forbidden
    echo json_encode(['error' => 'Unauthorized access.']);
    exit();
}

$action = $_REQUEST['action'] ?? '';
$company_id = $_SESSION['company_id'];
$response = ['error' => 'An unknown error occurred.']; // Default error response

switch ($action) {
    case 'get_attendance_data':
        $month = $_GET['month'] ?? date('Y-m');
        $start_date = $month . '-01';
        $end_date = date('Y-m-t', strtotime($start_date));

        // MODIFIED: SQL query now joins the 'users' table to get 'photo_path'
        // based on the provided 'employees' table schema.
        $sql = "
            SELECT 
                e.id as employee_id, 
                e.first_name, 
                e.last_name,
                d.name as department_name,
                des.name as designation,
                a.date, 
                a.status
            FROM employees e
            JOIN users u ON e.user_id = u.id
            JOIN departments d ON e.department_id = d.id
            LEFT JOIN designations des ON e.designation_id = des.id
            LEFT JOIN attendance a ON e.id = a.employee_id AND a.date BETWEEN ? AND ?
            WHERE d.company_id = ?
            ORDER BY e.first_name, e.last_name, a.date
        ";

        $result = query($mysqli, $sql, [$start_date, $end_date, $company_id]);

        if ($result['success']) {
            $employees = [];
            $summary = [
                'total_present' => 0,
                'total_absent' => 0,
                'total_leave' => 0,
                'total_holiday' => 0,
            ];

            // Process the raw SQL data into a structured format.
            foreach ($result['data'] as $row) {
                $employee_id = $row['employee_id'];

                // If this is the first time we see this employee, initialize their record.
                if (!isset($employees[$employee_id])) {
                    $employees[$employee_id] = [
                        'id' => (int) $employee_id,
                        'name' => $row['first_name'] . ' ' . $row['last_name'],
                        'designation' => $row['designation'] ?? 'N/A',
                        'attendance' => []
                    ];
                }

                // If there is an attendance record for this row, add it.
                if ($row['date']) {
                    $employees[$employee_id]['attendance'][$row['date']] = ['status' => $row['status']];

                    // Increment summary counters based on status.
                    switch ($row['status']) {
                        case 'present':
                            $summary['total_present']++;
                            break;
                        case 'absent':
                            $summary['total_absent']++;
                            break;
                        case 'leave':
                            $summary['total_leave']++;
                            break;
                        case 'holiday':
                            $summary['total_holiday']++;
                            break;
                    }
                }
            }

            // Calculate the overall attendance percentage for the month.
            $total_working_days_records = $summary['total_present'] + $summary['total_absent'] + $summary['total_leave'];
            $summary['overall_percentage'] = ($total_working_days_records > 0)
                ? round(($summary['total_present'] / $total_working_days_records) * 100, 1)
                : 0;

            // Get details about the requested month.
            $date_obj = new DateTime($start_date);
            $month_details = [
                'year' => (int) $date_obj->format('Y'),
                'month' => (int) $date_obj->format('m'),
                'days_in_month' => (int) $date_obj->format('t'),
                'start_day_of_week' => (int) $date_obj->format('w') // 0 for Sunday, 6 for Saturday
            ];

            // Assemble the final response object.
            $response = [
                'summary' => $summary,
                'month_details' => $month_details,
                'employees' => array_values($employees) // Re-index the array to ensure it's a JSON array.
            ];

        } else {
            // MODIFIED: Pass the specific database error back to the frontend for easier debugging.
            $response = ['error' => 'Database Query Failed: ' . ($result['error'] ?? 'Unknown error')];
        }
        break;

    case 'update_attendance':
        // This part remains the same as it already handles updates and returns JSON.
        $employee_id = isset($_POST['employee_id']) ? (int) $_POST['employee_id'] : 0;
        $date = $_POST['date'] ?? '';
        $status = $_POST['status'] ?? '';
        $response = ['success' => false, 'message' => 'Invalid data provided.']; // Default response for this action

        if ($employee_id > 0 && !empty($date) && in_array($status, ['present', 'absent', 'leave', 'holiday'])) {
            // Use INSERT ... ON DUPLICATE KEY UPDATE to either create a new record or update an existing one.
            $sql = "INSERT INTO attendance (employee_id, date, status) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE status = VALUES(status)";
            $result = query($mysqli, $sql, [$employee_id, $date, $status]);

            if ($result['success']) {
                $response = ['success' => true, 'message' => 'Attendance updated successfully!'];
            } else {
                $response['message'] = 'Database error: ' . $result['error'];
            }
        }
        break;

    default:
        $response = ['error' => 'Invalid action specified.'];
        break;
}

// Output the final response as a JSON string.
// sleep(); // Simulate a delay for better UX
echo json_encode($response);
exit();
?>