<?php
header('Content-Type: application/json');
require_once '../config/db.php';
require_once '../includes/functions.php';

$response = ['success' => false, 'message' => 'An unknown error occurred.'];

if (!isLoggedIn() || !in_array($_SESSION['role_id'], [2, 3, 4, 6])) {
    http_response_code(403);
    echo json_encode(['error' => 'Unauthorized access.']);
    exit();
}

$action = $_REQUEST['action'] ?? '';
$company_id = $_SESSION['company_id'];
$logged_in_user_id = $_SESSION['user_id'];
$logged_in_role_id = $_SESSION['role_id'];

switch ($action) {
    case 'get_attendance_data':
        $month = $_GET['month'] ?? date('Y-m');
        $single_employee_id = isset($_GET['employee_id']) ? (int) $_GET['employee_id'] : 0;
        $start_date = $month . '-01';
        $end_date = date('Y-m-t', strtotime($start_date));

        $company_info = query($mysqli, "SELECT created_at FROM companies WHERE id = ?", [$company_id])['data'][0] ?? null;
        $company_created_at = $company_info ? date('Y-m-d', strtotime($company_info['created_at'])) : '1970-01-01';

        $settings_result = query($mysqli, "SELECT saturday_policy FROM company_holiday_settings WHERE company_id = ?", [$company_id]);
        $saturday_policy = $settings_result['success'] && !empty($settings_result['data']) ? $settings_result['data'][0]['saturday_policy'] : 'none';

        $holidays_result = query($mysqli, "SELECT holiday_date, holiday_name FROM holidays WHERE company_id = ? AND holiday_date BETWEEN ? AND ?", [$company_id, $start_date, $end_date]);
        $company_holidays = array_column($holidays_result['data'] ?? [], 'holiday_name', 'holiday_date');

        $leaves_result = query($mysqli, "
            SELECT employee_id, start_date, end_date 
            FROM leaves l
            JOIN employees e ON l.employee_id = e.id
            JOIN users u ON e.user_id = u.id
            WHERE l.status = 'approved' AND u.company_id = ? AND l.start_date <= ? AND l.end_date >= ?
        ", [$company_id, $end_date, $start_date]);

        $employee_leaves = [];
        if ($leaves_result['success']) {
            foreach ($leaves_result['data'] as $leave) {
                $current = new DateTime($leave['start_date']);
                $end = new DateTime($leave['end_date']);
                while ($current <= $end) {
                    $employee_leaves[$leave['employee_id']][$current->format('Y-m-d')] = true;
                    $current->modify('+1 day');
                }
            }
        }

        $sql_where_conditions = "u.company_id = ?";
        $sql_params = [$company_id];

        $current_user_only = isset($_GET['current_user_only']) && $_GET['current_user_only'] == 1;

        // Role-based filtering
        // Role 2: Company Admin - can see all employees
        // Role 3: HR Manager - can see all employees except themselves (unless current_user_only=1)
        // Role 4: Employee - can only see themselves
        // Role 6: Manager - can see their team members (or only themselves if current_user_only=1)

        if ($logged_in_role_id == 3 && !$current_user_only) {
            // HR Manager sees others
            $sql_where_conditions .= " AND e.user_id != ?";
            $sql_params[] = $logged_in_user_id;
        } elseif ($logged_in_role_id == 4) {
            // Employee can only see their own attendance
            $user_employee_query = query($mysqli, "SELECT id FROM employees WHERE user_id = ?", [$logged_in_user_id]);
            if ($user_employee_query['success'] && !empty($user_employee_query['data'])) {
                $employee_id = $user_employee_query['data'][0]['id'];
                $sql_where_conditions .= " AND e.id = ?";
                $sql_params[] = $employee_id;
            } else {
                echo json_encode(['error' => 'No employee record found for current user.']);
                exit();
            }
        } elseif ($logged_in_role_id == 6 && !$current_user_only) {
            // Manager can see their team members
            $team_members = query(
                $mysqli,
                "SELECT e.id FROM employees e 
                 WHERE e.manager_id = (SELECT id FROM employees WHERE user_id = ?) 
                 AND e.department_id IN (
                    SELECT id FROM departments WHERE company_id = ?
                 )",
                [$logged_in_user_id, $company_id]
            );

            if ($team_members['success'] && !empty($team_members['data'])) {
                $team_ids = array_column($team_members['data'], 'id');
                $placeholders = implode(',', array_fill(0, count($team_ids), '?'));
                $sql_where_conditions .= " AND e.id IN ($placeholders)";
                $sql_params = array_merge($sql_params, $team_ids);
            } else {
                // Manager has no team, show only themselves
                $user_employee_query = query($mysqli, "SELECT id FROM employees WHERE user_id = ?", [$logged_in_user_id]);
                if ($user_employee_query['success'] && !empty($user_employee_query['data'])) {
                    $employee_id = $user_employee_query['data'][0]['id'];
                    $sql_where_conditions .= " AND e.id = ?";
                    $sql_params[] = $employee_id;
                }
            }
        }

        if ($current_user_only) {
            // Override: Get only the current user's attendance
            $user_employee_query = query($mysqli, "SELECT id FROM employees WHERE user_id = ?", [$logged_in_user_id]);
            if ($user_employee_query['success'] && !empty($user_employee_query['data'])) {
                $employee_id = $user_employee_query['data'][0]['id'];
                $sql_where_conditions .= " AND e.id = ?";
                $sql_params[] = $employee_id;
            } else {
                echo json_encode(['error' => 'No employee record found for current user.']);
                exit();
            }
        }

        if ($single_employee_id > 0) {
            $sql_where_conditions .= " AND e.id = ?";
            $sql_params[] = $single_employee_id;
        }

        $final_params = array_merge([$start_date, $end_date], $sql_params);

        $sql = "SELECT e.id as employee_id, e.first_name, e.last_name, e.date_of_joining, e.department_id, d.name as department_name, des.name as designation, a.date, a.status
                FROM employees e
                JOIN users u ON e.user_id = u.id
                LEFT JOIN departments d ON e.department_id = d.id
                LEFT JOIN designations des ON e.designation_id = des.id
                LEFT JOIN attendance a ON e.id = a.employee_id AND a.date BETWEEN ? AND ?
                WHERE " . $sql_where_conditions . " ORDER BY COALESCE(d.id, 0), e.first_name, e.last_name, a.date";

        $result = query($mysqli, $sql, $final_params);

        if ($result['success']) {
            $employees = [];
            $summary = ['total_present' => 0, 'total_absent' => 0, 'total_leave' => 0, 'total_holiday' => 0, 'total_half_day' => 0];
            foreach ($result['data'] as $row) {
                if (!isset($employees[$row['employee_id']])) {
                    $employees[$row['employee_id']] = [
                        'id' => (int) $row['employee_id'],
                        'name' => $row['first_name'] . ' ' . $row['last_name'],
                        'department_id' => (int) $row['department_id'],
                        'department_name' => $row['department_name'] ?? 'Unassigned',
                        'designation' => $row['designation'] ?? 'N/A',
                        'date_of_joining' => $row['date_of_joining'],
                        'attendance' => []
                    ];
                }
                if ($row['date']) {
                    $employees[$row['employee_id']]['attendance'][$row['date']] = ['status' => $row['status']];
                    $key = 'total_' . str_replace('-', '_', $row['status']);
                    if (array_key_exists($key, $summary)) {
                        $summary[$key]++;
                    }
                }
            }

            foreach ($employee_leaves as $emp_id => $dates) {
                foreach ($dates as $date => $is_leave) {
                    $has_attendance = false;
                    foreach ($result['data'] as $row) {
                        if ($row['employee_id'] == $emp_id && $row['date'] == $date && $row['status']) {
                            $has_attendance = true;
                            break;
                        }
                    }
                    if (!$has_attendance) {
                        $summary['total_leave']++;
                    }
                }
            }

            $total_records = $summary['total_present'] + $summary['total_absent'] + $summary['total_leave'] + $summary['total_half_day'];
            $summary['overall_percentage'] = $total_records > 0 ? round((($summary['total_present'] + $summary['total_half_day'] * 0.5) / $total_records) * 100, 1) : 0;

            $date_obj = new DateTime($start_date);
            $month_details = ['year' => (int) $date_obj->format('Y'), 'month' => (int) $date_obj->format('m'), 'days_in_month' => (int) $date_obj->format('t')];

            $response = ['success' => true, 'summary' => $summary, 'month_details' => $month_details, 'employees' => array_values($employees), 'company_holidays' => $company_holidays, 'saturday_policy' => $saturday_policy, 'employee_leaves' => $employee_leaves, 'company_created_at' => $company_created_at];
        } else {
            $response['error'] = 'Database Query Failed: ' . ($result['error'] ?? 'Unknown error');
        }
        break;

    case 'update_attendance':
        $employee_id = (int) ($_POST['employee_id'] ?? 0);
        $date = $_POST['date'] ?? '';
        $status = $_POST['status'] ?? '';
        $response = ['success' => false, 'message' => 'Invalid data provided.'];

        if ($employee_id > 0 && !empty($date) && in_array($status, ['present', 'absent', 'leave', 'holiday', 'half-day'])) {
            $sql = "INSERT INTO attendance (employee_id, date, status) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE status = VALUES(status)";
            $result = query($mysqli, $sql, [$employee_id, $date, $status]);
            if ($result['success']) {
                $response = ['success' => true, 'message' => 'Attendance updated!'];
            } else {
                $response['message'] = 'Database error: ' . $result['error'];
            }
        }
        break;

    case 'bulk_update':
        $date = $_POST['date'] ?? '';
        $status = $_POST['status'] ?? '';
        if (empty($date) || $status !== 'holiday') {
            $response['message'] = 'Invalid data for bulk update.';
            break;
        }

        $employees_result = query($mysqli, "SELECT id FROM employees e JOIN departments d ON e.department_id = d.id WHERE d.company_id = ?", [$company_id]);
        if ($employees_result['success'] && !empty($employees_result['data'])) {
            $sql = "INSERT INTO attendance (employee_id, date, status) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE status = VALUES(status)";
            $stmt = $mysqli->prepare($sql);
            $count = 0;
            foreach ($employees_result['data'] as $employee) {
                $stmt->bind_param("iss", $employee['id'], $date, $status);
                if ($stmt->execute())
                    $count++;
            }
            $stmt->close();
            $response = ['success' => true, 'message' => "Marked holiday for $count employees."];
        } else {
            $response['message'] = 'No employees found for this company.';
        }
        break;

    default:
        $response = ['error' => 'Invalid action specified.'];
        break;
}

echo json_encode($response);
exit();

