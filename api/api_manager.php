<?php
// Set the content type to JSON for all responses
header('Content-Type: application/json');

require_once '../config/db.php';
require_once '../includes/functions.php';
require_once '../includes/notification_helpers.php';

$response = ['success' => false, 'message' => 'An unknown error occurred.'];

// Security Check: Must be a logged-in Manager or Employee
if (!isLoggedIn() || !in_array($_SESSION['role_id'], [6, 4])) {
    $response['message'] = 'Unauthorized access.';
    echo json_encode($response);
    exit();
}

$user_id = $_SESSION['user_id'];
$company_id = $_SESSION['company_id'];

// Get manager's employee record
$manager_result = query($mysqli, "SELECT * FROM employees WHERE user_id = ?", [$user_id]);
$manager = $manager_result['success'] ? $manager_result['data'][0] : null;

if (!$manager) {
    $response['message'] = 'Manager record not found.';
    echo json_encode($response);
    exit();
}

$manager_id = $manager['id'];
$manager_department_id = $manager['department_id'];

$action = $_REQUEST['action'] ?? '';

switch ($action) {
    case 'get_team_stats':
        // Get team statistics
        $stats_result = query($mysqli, "
            SELECT 
                COUNT(DISTINCT e.id) as total_members,
                COUNT(DISTINCT CASE WHEN u.status = 'active' THEN e.id END) as active_members,
                COUNT(DISTINCT CASE WHEN t.status IN ('pending', 'in_progress') THEN t.id END) as pending_tasks,
                COUNT(DISTINCT CASE WHEN t.status = 'completed' THEN t.id END) as completed_tasks,
                COUNT(DISTINCT CASE WHEN a.status = 'leave' THEN a.id END) as on_leave_today
            FROM employees e
            JOIN users u ON e.user_id = u.id
            JOIN team_members tm ON e.id = tm.employee_id
            LEFT JOIN tasks t ON e.id = t.employee_id
            LEFT JOIN attendance a ON e.id = a.employee_id AND a.date = CURDATE()
            WHERE tm.assigned_by = ?
        ", [$user_id]);

        if ($stats_result['success']) {
            $response = ['success' => true, 'data' => $stats_result['data'][0]];
        } else {
            $response['message'] = 'Failed to fetch team statistics.';
        }
        break;

    case 'get_team_report':
        // Get comprehensive team report
        $report_result = query($mysqli, "
            SELECT 
                COUNT(e.id) as total_members,
                COUNT(CASE WHEN u.status = 'active' THEN 1 END) as active_members,
                COUNT(CASE WHEN t.status = 'completed' THEN 1 END) as completed_tasks,
                COUNT(CASE WHEN t.status IN ('pending', 'in_progress') THEN 1 END) as pending_tasks,
                CASE 
                    WHEN COUNT(t.id) > 0 THEN ROUND((COUNT(CASE WHEN t.status = 'completed' THEN 1 END) * 100.0 / COUNT(t.id)), 2)
                    ELSE 0 
                END as task_completion_rate
            FROM employees e
            JOIN users u ON e.user_id = u.id
            LEFT JOIN tasks t ON e.id = t.employee_id
            JOIN team_members tm ON e.id = tm.employee_id
            WHERE tm.assigned_by = ?
        ", [$user_id]);

        if ($report_result['success']) {
            $response = ['success' => true, 'data' => $report_result['data'][0]];
        } else {
            $response['message'] = 'Failed to fetch team report.';
        }
        break;

    case 'approve_leave':
        $leave_id = isset($_POST['leave_id']) ? (int) $_POST['leave_id'] : 0;

        if ($leave_id > 0) {
            // Verify the leave belongs to a team member
            $verify_result = query($mysqli, "
                SELECT l.id FROM leaves l
                JOIN employees e ON l.employee_id = e.id
                JOIN team_members tm ON e.id = tm.employee_id
                WHERE l.id = ? AND tm.assigned_by = ? AND l.status = 'pending'
            ", [$leave_id, $user_id]);

            if ($verify_result['success'] && !empty($verify_result['data'])) {
                $update_result = query($mysqli, "
                    UPDATE leaves 
                    SET status = 'approved', approved_by = ?
                    WHERE id = ?
                ", [$user_id, $leave_id]);

                if ($update_result['success']) {
                    $response = ['success' => true, 'message' => 'Leave request approved successfully!'];
                } else {
                    $response['message'] = 'Failed to approve leave request.';
                }
            } else {
                $response['message'] = 'Leave request not found or already processed.';
            }
        } else {
            $response['message'] = 'Invalid leave ID.';
        }
        break;

    case 'reject_leave':
        $leave_id = isset($_POST['leave_id']) ? (int) $_POST['leave_id'] : 0;
        $reason = $_POST['reason'] ?? '';

        if ($leave_id > 0) {
            // Verify the leave belongs to a team member
            $verify_result = query($mysqli, "
                SELECT l.id FROM leaves l
                JOIN employees e ON l.employee_id = e.id
                JOIN team_members tm ON e.id = tm.employee_id
                WHERE l.id = ? AND tm.assigned_by = ? AND l.status = 'pending'
            ", [$leave_id, $user_id]);

            if ($verify_result['success'] && !empty($verify_result['data'])) {
                $update_result = query($mysqli, "
                    UPDATE leaves 
                    SET status = 'rejected', approved_by = ?
                    WHERE id = ?
                ", [$user_id, $leave_id]);

                if ($update_result['success']) {
                    $response = ['success' => true, 'message' => 'Leave request rejected successfully!'];
                } else {
                    $response['message'] = 'Failed to reject leave request.';
                }
            } else {
                $response['message'] = 'Leave request not found or already processed.';
            }
        } else {
            $response['message'] = 'Invalid leave ID.';
        }
        break;

    case 'approve_all_pending':
        $update_result = query($mysqli, "
            UPDATE leaves l
            JOIN employees e ON l.employee_id = e.id
            JOIN team_members tm ON e.id = tm.employee_id
            SET l.status = 'approved', l.approved_by = ?
            WHERE tm.assigned_by = ? AND l.status = 'pending'
        ", [$user_id, $user_id]);

        if ($update_result['success']) {
            $response = ['success' => true, 'message' => 'All pending leave requests approved successfully!'];
        } else {
            $response['message'] = 'Failed to approve leave requests.';
        }
        break;

    case 'update_leave_status':
        $leave_id = isset($_POST['leave_id']) ? (int) $_POST['leave_id'] : 0;
        $status = $_POST['status'] ?? '';
        $reason = $_POST['reason'] ?? '';

        if ($leave_id > 0 && in_array($status, ['approved', 'rejected'])) {
            // Verify the leave belongs to a team member
            $verify_result = query($mysqli, "
                SELECT l.id FROM leaves l
                JOIN employees e ON l.employee_id = e.id
                WHERE l.id = ? AND e.department_id = ? AND l.status = 'pending'
            ", [$leave_id, $manager_department_id]);

            if ($verify_result['success'] && !empty($verify_result['data'])) {
                $update_result = query($mysqli, "
                    UPDATE leaves 
                    SET status = ?, approved_by = ?
                    WHERE id = ?
                ", [$status, $user_id, $leave_id]);

                if ($update_result['success']) {
                    $status_text = $status === 'approved' ? 'approved' : 'rejected';
                    $response = ['success' => true, 'message' => "Leave request {$status_text} successfully!"];
                } else {
                    $response['message'] = 'Failed to update leave request.';
                }
            } else {
                $response['message'] = 'Leave request not found or already processed.';
            }
        } else {
            $response['message'] = 'Invalid leave ID or status.';
        }
        break;

    case 'bulk_update_leave_status':
        $leave_ids = isset($_POST['leave_ids']) ? json_decode($_POST['leave_ids'], true) : [];
        $status = $_POST['status'] ?? '';
        $reason = $_POST['reason'] ?? '';

        if (!empty($leave_ids) && in_array($status, ['approved', 'rejected'])) {
            $placeholders = str_repeat('?,', count($leave_ids) - 1) . '?';
            $params = array_merge($leave_ids, [$manager_department_id]);

            $update_result = query($mysqli, "
                UPDATE leaves l
                JOIN employees e ON l.employee_id = e.id
                JOIN team_members tm ON e.id = tm.employee_id
                SET l.status = ?, l.approved_by = ?
                WHERE l.id IN ($placeholders) AND tm.assigned_by = ? AND l.status = 'pending'
            ", array_merge([$status, $user_id], $params, [$user_id]));

            if ($update_result['success']) {
                $status_text = $status === 'approved' ? 'approved' : 'rejected';
                $response = ['success' => true, 'message' => "Leave requests {$status_text} successfully!"];
            } else {
                $response['message'] = 'Failed to update leave requests.';
            }
        } else {
            $response['message'] = 'Invalid leave IDs or status.';
        }
        break;

    case 'get_leave_details':
        $leave_id = isset($_GET['leave_id']) ? (int) $_GET['leave_id'] : 0;

        if ($leave_id > 0) {
            $leave_result = query($mysqli, "
                SELECT l.*, e.first_name, e.last_name, e.employee_code,
                       des.name as designation_name, d.name as department_name
                FROM leaves l
                JOIN employees e ON l.employee_id = e.id
                LEFT JOIN designations des ON e.designation_id = des.id
                LEFT JOIN departments d ON e.department_id = d.id
                WHERE l.id = ? AND e.department_id = ?
            ", [$leave_id, $manager_department_id]);

            if ($leave_result['success'] && !empty($leave_result['data'])) {
                $response = ['success' => true, 'data' => $leave_result['data'][0]];
            } else {
                $response['message'] = 'Leave request not found.';
            }
        } else {
            $response['message'] = 'Invalid leave ID.';
        }
        break;

    case 'assign_task':
        $employee_id = isset($_POST['employee_id']) ? (int) $_POST['employee_id'] : 0;
        $title = $_POST['title'] ?? '';
        $description = $_POST['description'] ?? '';
        $due_date = $_POST['due_date'] ?? null;

        if ($employee_id > 0 && !empty($title)) {
            // Verify the employee belongs to the manager's team OR team leader's team
            if ($_SESSION['role_id'] == 6) {
                // Manager validation
                $verify_result = query($mysqli, "
                    SELECT e.id FROM employees e
                    JOIN team_members tm ON e.id = tm.employee_id
                    WHERE e.id = ? AND tm.assigned_by = ? AND e.status = 'active'
                ", [$employee_id, $user_id]);
            } else {
                // Employee (Team Leader) validation
                $current_emp = query($mysqli, "SELECT id FROM employees WHERE user_id = ?", [$user_id]);
                $current_employee_id = $current_emp['success'] && !empty($current_emp['data']) ? $current_emp['data'][0]['id'] : 0;

                // Check if current user is a team leader and target employee is in their team
                $verify_result = query($mysqli, "
                    SELECT e.id 
                    FROM employees e
                    JOIN team_members tm1 ON e.id = tm1.employee_id
                    JOIN team_members tm2 ON tm1.team_id = tm2.team_id
                    WHERE e.id = ? 
                    AND tm2.employee_id = ?
                    AND (tm2.role_in_team LIKE '%leader%' OR tm2.role_in_team LIKE '%lead%')
                    AND e.status = 'active'
                ", [$employee_id, $current_employee_id]);
            }

            if ($verify_result['success'] && !empty($verify_result['data'])) {

                $team_id = isset($_POST['team_id']) && !empty($_POST['team_id']) ? (int) $_POST['team_id'] : null;

                // If team_id is provided, verify employee belongs to that team
                if ($team_id) {
                    $team_check = query($mysqli, "SELECT id FROM team_members WHERE employee_id = ? AND team_id = ?", [$employee_id, $team_id]);
                    if (!$team_check['success'] || count($team_check['data']) === 0) {
                        $team_id = null; // Invalid team, reset to null
                    }
                }

                // Validate Due Date
                if ($due_date && $due_date < date('Y-m-d')) {
                    echo json_encode(['success' => false, 'message' => 'Due date cannot be in the past.']);
                    exit;
                }

                $insert_result = query($mysqli, "
                    INSERT INTO tasks (employee_id, title, description, due_date, assigned_by, status, team_id)
                    VALUES (?, ?, ?, ?, ?, 'pending', ?)
                ", [$employee_id, $title, $description, $due_date, $user_id, $team_id]);

                if ($insert_result['success']) {
                    // Notify the employee about the new task
                    $emp_user_info = query($mysqli, "SELECT user_id FROM employees WHERE id = ?", [$employee_id])['data'][0] ?? null;
                    if ($emp_user_info) {
                        $manager_name_res = query($mysqli, "SELECT first_name, last_name FROM employees WHERE user_id = ?", [$user_id])['data'][0] ?? null;
                        $manager_name = $manager_name_res ? "{$manager_name_res['first_name']} {$manager_name_res['last_name']}" : 'Your manager';
                        createNotificationIfEnabled(
                            $mysqli,
                            $emp_user_info['user_id'],
                            'task',
                            'New Task Assigned',
                            "{$manager_name} has assigned you a task: {$title}",
                            $mysqli->insert_id,
                            'task'
                        );
                    }
                    $response = ['success' => true, 'message' => 'Task assigned successfully!'];
                } else {
                    $response['message'] = 'Failed to assign task.';
                }
            } else {
                $response['message'] = 'Employee not found or not in your team.';
            }
        } else {
            $response['message'] = 'Please provide valid employee ID and task title.';
        }
        break;

    case 'get_employee_teams':
        $target_employee_id = isset($_GET['employee_id']) ? (int) $_GET['employee_id'] : 0;
        if ($target_employee_id > 0) {
            $teams_result = query($mysqli, "
                SELECT t.id, t.name 
                FROM teams t
                JOIN team_members tm ON t.id = tm.team_id
                WHERE tm.employee_id = ?
                ORDER BY t.name ASC
            ", [$target_employee_id]);

            if ($teams_result['success']) {
                $response = ['success' => true, 'data' => $teams_result['data']];
            } else {
                $response = ['success' => false, 'message' => 'Failed to fetch teams.'];
            }
        } else {
            $response['message'] = 'Invalid employee ID.';
        }
        break;

    case 'get_team_members':
        $team_id = isset($_GET['team_id']) ? (int) $_GET['team_id'] : 0;
        if ($team_id) {
            $members = query($mysqli, "SELECT employee_id FROM team_members WHERE team_id = ?", [$team_id]);
            if ($members['success']) {
                $response = ['success' => true, 'data' => array_column($members['data'], 'employee_id')];
            } else {
                $response = ['success' => false, 'message' => 'Failed to fetch members'];
            }
        }
        break;

    case 'get_common_teams':
        $employee_ids = isset($_POST['employee_ids']) ? $_POST['employee_ids'] : [];

        if (!empty($employee_ids)) {
            // Validate: Get teams that HAVE ALL selected employees
            // We can do this by finding teams where the count of selected members equals the count of input employees
            $placeholders = implode(',', array_fill(0, count($employee_ids), '?'));
            $sql = "
                SELECT t.id, t.name
                FROM teams t
                JOIN team_members tm ON t.id = tm.team_id
                WHERE tm.employee_id IN ($placeholders)
                GROUP BY t.id
                HAVING COUNT(DISTINCT tm.employee_id) = ?
            ";

            $params = array_merge($employee_ids, [count($employee_ids)]);
            $common_teams_result = query($mysqli, $sql, $params);

            if ($common_teams_result['success']) {
                $response = ['success' => true, 'data' => $common_teams_result['data']];
            } else {
                $response = ['success' => false, 'message' => 'Error checking teams.'];
            }
        } else {
            $response = ['success' => false, 'message' => 'No employees selected.', 'data' => []];
        }
        break;

    case 'bulk_assign_tasks':
        $employee_ids = isset($_POST['employee_ids']) ? $_POST['employee_ids'] : [];
        $title = $_POST['title'] ?? '';
        $description = $_POST['description'] ?? '';
        $due_date = $_POST['due_date'] ?? null;
        $team_id = isset($_POST['team_id']) && !empty($_POST['team_id']) ? (int) $_POST['team_id'] : null;

        if (!empty($employee_ids) && !empty($title)) {
            $success_count = 0;
            $error_count = 0;

            // Optional: Backend validation that all employees belong to the selected team
            if ($team_id) {
                foreach ($employee_ids as $eid) {
                    $check = query($mysqli, "SELECT 1 FROM team_members WHERE team_id = ? AND employee_id = ?", [$team_id, $eid]);
                    if (!$check['success'] || empty($check['data'])) {
                        echo json_encode(['success' => false, 'message' => 'Security Error: One or more employees do not belong to the selected team.']);
                        exit;
                    }
                }
            }

            // Validate Due Date
            if ($due_date && $due_date < date('Y-m-d')) {
                echo json_encode(['success' => false, 'message' => 'Due date cannot be in the past.']);
                exit;
            }

            foreach ($employee_ids as $employee_id) {
                // Verify the employee belongs to the manager's team (or departments)
                // Existing check:
                $verify_result = query($mysqli, "
                    SELECT e.id FROM employees e
                    JOIN team_members tm ON e.id = tm.employee_id
                    WHERE e.id = ? AND tm.assigned_by = ? AND e.status = 'active'
                ", [$employee_id, $user_id]);

                // Also allow if in manager's department (legacy/hybrid support)
                if (empty($verify_result['data'])) {
                    $verify_result = query($mysqli, "SELECT id FROM employees WHERE id = ? AND department_id = ?", [$employee_id, $manager_department_id]);
                }

                if ($verify_result['success'] && !empty($verify_result['data'])) {
                    $insert_result = query($mysqli, "
                        INSERT INTO tasks (employee_id, title, description, due_date, assigned_by, status, team_id)
                        VALUES (?, ?, ?, ?, ?, 'pending', ?)
                    ", [$employee_id, $title, $description, $due_date, $user_id, $team_id]);

                    if ($insert_result['success']) {
                        $success_count++;
                    } else {
                        $error_count++;
                    }
                } else {
                    $error_count++;
                }
            }

            if ($success_count > 0) {
                $response = ['success' => true, 'message' => "Tasks assigned to {$success_count} employee(s) successfully!"];
            } else {
                $response['message'] = 'Failed to assign tasks to any employee.';
            }
        } else {
            $response['message'] = 'Please select employees and provide task title.';
        }
        break;

    case 'get_task_details':
        $task_id = isset($_GET['task_id']) ? (int) $_GET['task_id'] : 0;

        if ($task_id > 0) {
            $task_result = query($mysqli, "
                SELECT t.*, e.first_name, e.last_name, e.employee_code,
                       des.name as designation_name, d.name as department_name
                FROM tasks t
                JOIN employees e ON t.employee_id = e.id
                LEFT JOIN designations des ON e.designation_id = des.id
                LEFT JOIN departments d ON e.department_id = d.id
                JOIN team_members tm ON e.id = tm.employee_id
                WHERE t.id = ? AND tm.assigned_by = ?
            ", [$task_id, $user_id]);

            if ($task_result['success'] && !empty($task_result['data'])) {
                $response = ['success' => true, 'data' => $task_result['data'][0]];
            } else {
                $response['message'] = 'Task not found.';
            }
        } else {
            $response['message'] = 'Invalid task ID.';
        }
        break;

    case 'update_task':
        $task_id = isset($_POST['task_id']) ? (int) $_POST['task_id'] : 0;
        $title = $_POST['title'] ?? '';
        $description = $_POST['description'] ?? '';
        $due_date = $_POST['due_date'] ?? null;

        if ($task_id > 0 && !empty($title)) {
            // Verify the task belongs to the manager's team
            $verify_result = query($mysqli, "
                SELECT t.id FROM tasks t
                JOIN employees e ON t.employee_id = e.id
                JOIN team_members tm ON e.id = tm.employee_id
                WHERE t.id = ? AND tm.assigned_by = ?
            ", [$task_id, $user_id]);

            if ($verify_result['success'] && !empty($verify_result['data'])) {
                // Validate Due Date
                if ($due_date && $due_date < date('Y-m-d')) {
                    echo json_encode(['success' => false, 'message' => 'Due date cannot be in the past.']);
                    exit;
                }

                $update_result = query($mysqli, "
                    UPDATE tasks 
                    SET title = ?, description = ?, due_date = ?, updated_at = NOW()
                    WHERE id = ?
                ", [$title, $description, $due_date, $task_id]);

                if ($update_result['success']) {
                    $response = ['success' => true, 'message' => 'Task updated successfully!'];
                } else {
                    $response['message'] = 'Failed to update task.';
                }
            } else {
                $response['message'] = 'Task not found or you do not have permission to edit it.';
            }
        } else {
            $response['message'] = 'Invalid task ID or missing title.';
        }
        break;

    case 'cancel_task':
        $task_id = isset($_POST['task_id']) ? (int) $_POST['task_id'] : 0;

        if ($task_id > 0) {
            // Verify the task belongs to a team member
            $verify_result = query($mysqli, "
                SELECT t.id FROM tasks t
                JOIN employees e ON t.employee_id = e.id
                JOIN team_members tm ON e.id = tm.employee_id
                WHERE t.id = ? AND tm.assigned_by = ?
            ", [$task_id, $user_id]);

            if ($verify_result['success'] && !empty($verify_result['data'])) {
                $update_result = query($mysqli, "
                    UPDATE tasks 
                    SET status = 'cancelled'
                    WHERE id = ?
                ", [$task_id]);

                if ($update_result['success']) {
                    $response = ['success' => true, 'message' => 'Task cancelled successfully!'];
                } else {
                    $response['message'] = 'Failed to cancel task.';
                }
            } else {
                $response['message'] = 'Task not found or not in your team.';
            }
        } else {
            $response['message'] = 'Invalid task ID.';
        }
        break;

    case 'mark_attendance':
        $employee_id = isset($_POST['employee_id']) ? (int) $_POST['employee_id'] : 0;
        $date = $_POST['date'] ?? '';
        $check_in = $_POST['check_in'] ?? null;
        $check_out = $_POST['check_out'] ?? null;
        $status = $_POST['status'] ?? 'present';
        $remarks = $_POST['remarks'] ?? '';

        if ($employee_id > 0 && !empty($date)) {
            // Verify the employee belongs to the manager's team
            $verify_result = query($mysqli, "
                SELECT e.id FROM employees e
                JOIN team_members tm ON e.id = tm.employee_id
                WHERE e.id = ? AND tm.assigned_by = ? AND e.status = 'active'
            ", [$employee_id, $user_id]);

            if ($verify_result['success'] && !empty($verify_result['data'])) {
                // Check if attendance record already exists
                $existing_result = query($mysqli, "
                    SELECT id FROM attendance 
                    WHERE employee_id = ? AND date = ?
                ", [$employee_id, $date]);

                if ($existing_result['success'] && !empty($existing_result['data'])) {
                    // Update existing record
                    $update_result = query($mysqli, "
                        UPDATE attendance 
                        SET check_in = ?, check_out = ?, status = ?, remarks = ?
                        WHERE employee_id = ? AND date = ?
                    ", [$check_in, $check_out, $status, $remarks, $employee_id, $date]);
                } else {
                    // Insert new record
                    $insert_result = query($mysqli, "
                        INSERT INTO attendance (employee_id, date, check_in, check_out, status, remarks)
                        VALUES (?, ?, ?, ?, ?, ?)
                    ", [$employee_id, $date, $check_in, $check_out, $status, $remarks]);
                }

                if (
                    ($existing_result['success'] && !empty($existing_result['data']) && $update_result['success']) ||
                    ($existing_result['success'] && empty($existing_result['data']) && $insert_result['success'])
                ) {
                    $response = ['success' => true, 'message' => 'Attendance marked successfully!'];
                } else {
                    $response['message'] = 'Failed to mark attendance.';
                }
            } else {
                $response['message'] = 'Employee not found or not in your team.';
            }
        } else {
            $response['message'] = 'Please provide valid employee ID and date.';
        }
        break;

    case 'get_attendance_details':
        $attendance_id = isset($_GET['attendance_id']) ? (int) $_GET['attendance_id'] : 0;

        if ($attendance_id > 0) {
            $attendance_result = query($mysqli, "
                SELECT a.*, e.first_name, e.last_name, e.employee_code,
                       des.name as designation_name, s.name as shift_name
                FROM attendance a
                JOIN employees e ON a.employee_id = e.id
                LEFT JOIN designations des ON e.designation_id = des.id
                LEFT JOIN shifts s ON e.shift_id = s.id
                JOIN team_members tm ON e.id = tm.employee_id
                WHERE a.id = ? AND tm.assigned_by = ?
            ", [$attendance_id, $user_id]);

            if ($attendance_result['success'] && !empty($attendance_result['data'])) {
                $response = ['success' => true, 'data' => $attendance_result['data'][0]];
            } else {
                $response['message'] = 'Attendance record not found.';
            }
        } else {
            $response['message'] = 'Invalid attendance ID.';
        }
        break;

    case 'add_performance':
        $employee_id = isset($_POST['employee_id']) ? (int) $_POST['employee_id'] : 0;
        $period = $_POST['period'] ?? '';
        $score = isset($_POST['score']) ? (int) $_POST['score'] : 0;
        $remarks = $_POST['remarks'] ?? '';

        if ($employee_id > 0 && !empty($period) && $score >= 0 && $score <= 100) {
            // Verify the employee belongs to the manager's team
            $verify_result = query($mysqli, "
                SELECT e.id FROM employees e
                JOIN team_members tm ON e.id = tm.employee_id
                WHERE e.id = ? AND tm.assigned_by = ? AND e.status = 'active'
            ", [$employee_id, $user_id]);

            if ($verify_result['success'] && !empty($verify_result['data'])) {
                $insert_result = query($mysqli, "
                    INSERT INTO performance (employee_id, evaluator_id, period, score, remarks)
                    VALUES (?, ?, ?, ?, ?)
                ", [$employee_id, $user_id, $period, $score, $remarks]);

                if ($insert_result['success']) {
                    $response = ['success' => true, 'message' => 'Performance review added successfully!'];
                } else {
                    $response['message'] = 'Failed to add performance review.';
                }
            } else {
                $response['message'] = 'Employee not found or not in your team.';
            }
        } else {
            $response['message'] = 'Please provide valid employee ID, period, and score (0-100).';
        }
        break;

    case 'get_performance_details':
        $performance_id = isset($_GET['performance_id']) ? (int) $_GET['performance_id'] : 0;

        if ($performance_id > 0) {
            $performance_result = query($mysqli, "
                SELECT p.*, e.first_name, e.last_name, e.employee_code,
                       des.name as designation_name, u.username as evaluator_name
                FROM performance p
                JOIN employees e ON p.employee_id = e.id
                LEFT JOIN designations des ON e.designation_id = des.id
                LEFT JOIN users u ON p.evaluator_id = u.id
                JOIN team_members tm ON e.id = tm.employee_id
                WHERE p.id = ? AND tm.assigned_by = ?
            ", [$performance_id, $user_id]);

            if ($performance_result['success'] && !empty($performance_result['data'])) {
                $response = ['success' => true, 'data' => $performance_result['data'][0]];
            } else {
                $response['message'] = 'Performance review not found.';
            }
        } else {
            $response['message'] = 'Invalid performance ID.';
        }
        break;

    case 'delete_performance':
        $performance_id = isset($_POST['performance_id']) ? (int) $_POST['performance_id'] : 0;

        if ($performance_id > 0) {
            // Verify the performance belongs to a team member
            $verify_result = query($mysqli, "
                SELECT p.id FROM performance p
                JOIN employees e ON p.employee_id = e.id
                JOIN team_members tm ON e.id = tm.employee_id
                WHERE p.id = ? AND tm.assigned_by = ?
            ", [$performance_id, $user_id]);

            if ($verify_result['success'] && !empty($verify_result['data'])) {
                $delete_result = query($mysqli, "
                    DELETE FROM performance 
                    WHERE id = ?
                ", [$performance_id]);

                if ($delete_result['success']) {
                    $response = ['success' => true, 'message' => 'Performance review deleted successfully!'];
                } else {
                    $response['message'] = 'Failed to delete performance review.';
                }
            } else {
                $response['message'] = 'Performance review not found or not in your team.';
            }
        } else {
            $response['message'] = 'Invalid performance ID.';
        }
        break;

    case 'create_team':
        $team_name = trim($_POST['name'] ?? '');
        $team_description = trim($_POST['description'] ?? '');

        // Validation
        if (empty($team_name)) {
            $response['message'] = 'Team name is required.';
        } elseif (strlen($team_name) > 100) {
            $response['message'] = 'Team name cannot exceed 100 characters.';
        } elseif (strlen($team_description) > 500) {
            $response['message'] = 'Description cannot exceed 500 characters.';
        } else {
            // Check for duplicate team name
            $dup_check = query($mysqli, "
                SELECT id FROM teams 
                WHERE company_id = ? AND name = ?
            ", [$company_id, $team_name]);

            if ($dup_check['success'] && count($dup_check['data']) > 0) {
                $response['message'] = 'A team with this name already exists.';
            } else {
                $insert_result = query($mysqli, "
                    INSERT INTO teams (company_id, name, description, created_by)
                    VALUES (?, ?, ?, ?)
                ", [$company_id, $team_name, $team_description, $user_id]);

                if ($insert_result['success']) {
                    $response = [
                        'success' => true,
                        'message' => 'Team created successfully!',
                        'team_id' => $mysqli->insert_id
                    ];
                } else {
                    $response['message'] = 'Failed to create team.';
                }
            }
        }
        break;

    case 'assign_team_members':
        $team_id = isset($_POST['team_id']) ? (int) $_POST['team_id'] : 0;
        $employee_ids = isset($_POST['employee_ids']) ? $_POST['employee_ids'] : [];

        if ($team_id > 0 && !empty($employee_ids)) {
            // Verify the team belongs to the manager
            $verify_team = query($mysqli, "
                SELECT id FROM teams 
                WHERE id = ? AND company_id = ? AND created_by = ?
            ", [$team_id, $company_id, $user_id]);

            if ($verify_team['success'] && !empty($verify_team['data'])) {
                $success_count = 0;
                $error_count = 0;

                foreach ($employee_ids as $employee_id) {
                    // Check if employee is already in ANY team
                    $check_existing = query($mysqli, "
                        SELECT id FROM team_members 
                        WHERE employee_id = ?
                    ", [$employee_id]);

                    if ($check_existing['success'] && empty($check_existing['data'])) {
                        // Add member to team
                        $insert_result = query($mysqli, "
                            INSERT INTO team_members (team_id, employee_id, assigned_by)
                            VALUES (?, ?, ?)
                        ", [$team_id, $employee_id, $user_id]);

                        if ($insert_result['success']) {
                            $success_count++;
                        } else {
                            $error_count++;
                        }
                    } else {
                        $error_count++;
                    }
                }

                if ($success_count > 0) {
                    $response = ['success' => true, 'message' => "Successfully assigned {$success_count} member(s) to the team!"];
                } else {
                    $response['message'] = 'Failed to assign any members to the team.';
                }
            } else {
                $response['message'] = 'Team not found or you do not have permission to manage this team.';
            }
        } else {
            $response['message'] = 'Please select a team and at least one employee.';
        }
        break;

    case 'get_team_details':
        $team_id = isset($_GET['team_id']) ? (int) $_GET['team_id'] : 0;

        if ($team_id > 0) {
            // Check authorization: Manager who created it OR Employee who is team leader
            if ($_SESSION['role_id'] == 6) {
                // Manager authorization
                $auth_query = "SELECT t.*, COUNT(tm.id) as member_count
                    FROM teams t
                    LEFT JOIN team_members tm ON t.id = tm.team_id
                    WHERE t.id = ? AND t.company_id = ? AND t.created_by = ?
                    GROUP BY t.id";
                $team_result = query($mysqli, $auth_query, [$team_id, $company_id, $user_id]);
            } else {
                // Employee (Team Leader) authorization
                $emp_result = query($mysqli, "SELECT id FROM employees WHERE user_id = ?", [$user_id]);
                $employee_id = $emp_result['success'] && !empty($emp_result['data']) ? $emp_result['data'][0]['id'] : 0;

                $auth_query = "SELECT t.*, COUNT(tm2.id) as member_count
                    FROM teams t
                    JOIN team_members tm ON t.id = tm.team_id
                    LEFT JOIN team_members tm2 ON t.id = tm2.team_id
                    WHERE t.id = ? 
                    AND tm.employee_id = ?
                    AND (tm.role_in_team LIKE '%leader%' OR tm.role_in_team LIKE '%lead%')
                    GROUP BY t.id";
                $team_result = query($mysqli, $auth_query, [$team_id, $employee_id]);
            }

            if ($team_result['success'] && !empty($team_result['data'])) {
                $team = $team_result['data'][0];

                // Get team members with stats
                $members_result = query($mysqli, "
                    SELECT e.id, e.first_name, e.last_name, e.employee_code, e.contact,
                           tm.role_in_team, tm.assigned_at, tm.employee_id,
                           des.name as designation_name, d.name as department_name,
                           COUNT(DISTINCT t.id) as total_tasks,
                           COUNT(DISTINCT CASE WHEN t.status = 'completed' THEN t.id END) as completed_tasks,
                           ROUND(AVG(p.score), 1) as avg_performance
                    FROM team_members tm
                    JOIN employees e ON tm.employee_id = e.id
                    LEFT JOIN designations des ON e.designation_id = des.id
                    LEFT JOIN departments d ON e.department_id = d.id
                    LEFT JOIN tasks t ON e.id = t.employee_id
                    LEFT JOIN performance p ON e.id = p.employee_id
                    WHERE tm.team_id = ?
                    GROUP BY e.id
                    ORDER BY tm.assigned_at DESC
                ", [$team_id]);

                $team['members'] = $members_result['success'] ? $members_result['data'] : [];

                $response = ['success' => true, 'data' => $team];
            } else {
                $response['message'] = 'Team not found or you do not have permission to view this team.';
            }
        } else {
            $response['message'] = 'Invalid team ID.';
        }
        break;

    case 'delete_team':
        $team_id = isset($_POST['team_id']) ? (int) $_POST['team_id'] : 0;

        if ($team_id > 0) {
            // Verify the team belongs to the manager
            $verify_team = query($mysqli, "
                SELECT id FROM teams 
                WHERE id = ? AND company_id = ? AND created_by = ?
            ", [$team_id, $company_id, $user_id]);

            if ($verify_team['success'] && !empty($verify_team['data'])) {
                // Delete team members first
                $delete_members = query($mysqli, "
                    DELETE FROM team_members WHERE team_id = ?
                ", [$team_id]);

                // Delete the team
                $delete_team = query($mysqli, "
                    DELETE FROM teams WHERE id = ?
                ", [$team_id]);

                if ($delete_team['success']) {
                    $response = ['success' => true, 'message' => 'Team deleted successfully!'];
                } else {
                    $response['message'] = 'Failed to delete team.';
                }
            } else {
                $response['message'] = 'Team not found or you do not have permission to delete this team.';
            }
        } else {
            $response['message'] = 'Invalid team ID.';
        }
        break;

    case 'remove_team_member':
        $team_id = isset($_POST['team_id']) ? (int) $_POST['team_id'] : 0;
        $employee_id = isset($_POST['employee_id']) ? (int) $_POST['employee_id'] : 0;

        if ($team_id > 0 && $employee_id > 0) {
            // Verify the team belongs to the manager
            $verify_team = query($mysqli, "
                SELECT id FROM teams 
                WHERE id = ? AND company_id = ? AND created_by = ?
            ", [$team_id, $company_id, $user_id]);

            if ($verify_team['success'] && !empty($verify_team['data'])) {
                $delete_result = query($mysqli, "
                    DELETE FROM team_members 
                    WHERE team_id = ? AND employee_id = ?
                ", [$team_id, $employee_id]);

                if ($delete_result['success']) {
                    $response = ['success' => true, 'message' => 'Team member removed successfully!'];
                } else {
                    $response['message'] = 'Failed to remove team member.';
                }
            } else {
                $response['message'] = 'Team not found or you do not have permission to manage this team.';
            }
        } else {
            $response['message'] = 'Invalid team ID or employee ID.';
        }
        break;

    case 'update_team':
        $team_id = isset($_POST['team_id']) ? (int) $_POST['team_id'] : 0;
        $team_name = $_POST['name'] ?? '';
        $team_description = $_POST['description'] ?? '';

        if ($team_id > 0 && !empty($team_name)) {
            // Verify the team belongs to the manager
            $verify_team = query($mysqli, "
                SELECT id FROM teams 
                WHERE id = ? AND company_id = ? AND created_by = ?
            ", [$team_id, $company_id, $user_id]);

            if ($verify_team['success'] && !empty($verify_team['data'])) {
                $update_result = query($mysqli, "
                    UPDATE teams 
                    SET name = ?, description = ?, updated_by = ?, updated_at = NOW()
                    WHERE id = ?
                ", [$team_name, $team_description, $user_id, $team_id]);

                if ($update_result['success']) {
                    $response = ['success' => true, 'message' => 'Team updated successfully!'];
                } else {
                    $response['message'] = 'Failed to update team.';
                }
            } else {
                $response['message'] = 'Team not found or you do not have permission to update this team.';
            }
        } else {
            $response['message'] = 'Please provide a valid team ID and name.';
        }
        break;

    case 'get_available_employees':
        $team_id = isset($_GET['team_id']) ? (int) $_GET['team_id'] : 0;

        // Base query to get all employees (Role ID 4 only)
        $sql = "
            SELECT e.id, e.first_name, e.last_name, e.employee_code, e.designation_id, 
                   des.name as designation_name, d.name as department_name, e.date_of_joining
            FROM employees e
            JOIN users u ON e.user_id = u.id
            LEFT JOIN designations des ON e.designation_id = des.id
            LEFT JOIN departments d ON e.department_id = d.id
            WHERE e.status = 'active' AND u.role_id = 4
        ";

        $params = [];

        // If team_id is provided, exclude employees already in that team
        // Filter out employees who are in ANY team
        $sql .= " AND e.id NOT IN (SELECT employee_id FROM team_members)";

        $sql .= " ORDER BY e.first_name ASC";

        $employees_result = query($mysqli, $sql, $params);

        if ($employees_result['success']) {
            $response = ['success' => true, 'data' => $employees_result['data']];
        } else {
            $response['message'] = 'Failed to fetch employees.';
        }
        break;

    case 'get_team_members_stats':
        $team_id = isset($_GET['team_id']) ? (int) $_GET['team_id'] : 0;

        if ($team_id > 0) {
            // Verify the team belongs to the manager
            $verify_team = query($mysqli, "
                SELECT id FROM teams 
                WHERE id = ? AND company_id = ? AND created_by = ?
            ", [$team_id, $company_id, $user_id]);

            if ($verify_team['success'] && !empty($verify_team['data'])) {
                // Get team members with their stats
                $members_result = query($mysqli, "
                    SELECT 
                        e.id, e.first_name, e.last_name, e.employee_code,
                        tm.role_in_team,
                        des.name as designation_name,
                        d.name as department_name,
                        COUNT(DISTINCT t.id) as total_tasks,
                        COUNT(DISTINCT CASE WHEN t.status = 'completed' THEN t.id END) as completed_tasks,
                        COUNT(DISTINCT CASE WHEN t.status IN ('pending', 'in_progress') THEN t.id END) as pending_tasks,
                        COALESCE(AVG(p.score), 0) as avg_performance,
                        COUNT(DISTINCT CASE WHEN a.status = 'present' AND a.date >= DATE_SUB(CURDATE(), INTERVAL 30 DAY) THEN a.id END) as attendance_count
                    FROM team_members tm
                    JOIN employees e ON tm.employee_id = e.id
                    LEFT JOIN designations des ON e.designation_id = des.id
                    LEFT JOIN departments d ON e.department_id = d.id
                    LEFT JOIN tasks t ON e.id = t.employee_id
                    LEFT JOIN performance p ON e.id = p.employee_id
                    LEFT JOIN attendance a ON e.id = a.employee_id
                    WHERE tm.team_id = ?
                    GROUP BY e.id, e.first_name, e.last_name, e.employee_code, tm.role_in_team, des.name, d.name
                    ORDER BY tm.role_in_team DESC, e.first_name
                ", [$team_id]);

                if ($members_result['success']) {
                    $response = ['success' => true, 'data' => $members_result['data']];
                } else {
                    $response['message'] = 'Failed to fetch team member stats.';
                }
            } else {
                $response['message'] = 'Team not found or you do not have permission to view this team.';
            }
        } else {
            $response['message'] = 'Invalid team ID.';
        }
        break;

    case 'get_team_leaders':
        $team_id = isset($_GET['team_id']) ? (int) $_GET['team_id'] : 0;

        if ($team_id > 0) {
            // Verify the team belongs to the manager
            $verify_team = query($mysqli, "
                SELECT id FROM teams 
                WHERE id = ? AND company_id = ? AND created_by = ?
            ", [$team_id, $company_id, $user_id]);

            if ($verify_team['success'] && !empty($verify_team['data'])) {
                // Get team leaders (role_in_team contains leader/lead)
                $leaders_result = query($mysqli, "
                    SELECT e.id, e.first_name, e.last_name, e.employee_code, tm.role_in_team
                    FROM team_members tm
                    JOIN employees e ON tm.employee_id = e.id
                    WHERE tm.team_id = ?
                    AND (
                        LOWER(tm.role_in_team) LIKE '%leader%' 
                        OR LOWER(tm.role_in_team) LIKE '%lead%'
                    )
                ", [$team_id]);

                if ($leaders_result['success']) {
                    $response = ['success' => true, 'data' => $leaders_result['data']];
                } else {
                    $response['message'] = 'Failed to fetch team leaders.';
                }
            } else {
                $response['message'] = 'Team not found or you do not have permission to view this team.';
            }
        } else {
            $response['message'] = 'Invalid team ID.';
        }
        break;

    case 'assign_team_task':
        $team_id = isset($_POST['team_id']) ? (int) $_POST['team_id'] : 0;
        $title = $_POST['title'] ?? '';
        $description = $_POST['description'] ?? '';
        $due_date = $_POST['due_date'] ?? null;

        if ($team_id > 0 && !empty($title)) {
            // Verify the team belongs to the manager
            $verify_team = query($mysqli, "
                SELECT id FROM teams 
                WHERE id = ? AND company_id = ? AND created_by = ?
            ", [$team_id, $company_id, $user_id]);

            if ($verify_team['success'] && !empty($verify_team['data'])) {
                // Get team leaders first
                $leaders_result = query($mysqli, "
                    SELECT employee_id
                    FROM team_members
                    WHERE team_id = ?
                    AND (
                        LOWER(role_in_team) LIKE '%leader%' 
                        OR LOWER(role_in_team) LIKE '%lead%'
                    )
                ", [$team_id]);

                $target_employees = [];

                if ($leaders_result['success'] && !empty($leaders_result['data'])) {
                    // Assign to team leaders
                    foreach ($leaders_result['data'] as $leader) {
                        $target_employees[] = $leader['employee_id'];
                    }
                } else {
                    // No leaders found, assign to all team members
                    $all_members_result = query($mysqli, "
                        SELECT employee_id
                        FROM team_members
                        WHERE team_id = ?
                    ", [$team_id]);

                    if ($all_members_result['success']) {
                        foreach ($all_members_result['data'] as $member) {
                            $target_employees[] = $member['employee_id'];
                        }
                    }
                }

                if (!empty($target_employees)) {
                    $success_count = 0;
                    foreach ($target_employees as $employee_id) {
                        $insert_result = query($mysqli, "
                            INSERT INTO tasks (employee_id, title, description, due_date, assigned_by, status)
                            VALUES (?, ?, ?, ?, ?, 'pending')
                        ", [$employee_id, $title, $description, $due_date, $user_id]);

                        if ($insert_result['success']) {
                            $success_count++;
                        }
                    }

                    if ($success_count > 0) {
                        $response = [
                            'success' => true,
                            'message' => "Task assigned to {$success_count} team member(s) successfully!",
                            'assigned_count' => $success_count
                        ];
                    } else {
                        $response['message'] = 'Failed to assign task to any team member.';
                    }
                } else {
                    $response['message'] = 'No team members found to assign the task.';
                }
            } else {
                $response['message'] = 'Team not found or you do not have permission to manage this team.';
            }
        } else {
            $response['message'] = 'Please provide valid team ID and task title.';
        }
        break;

    case 'update_team_member_role':
        $team_id = isset($_POST['team_id']) ? (int) $_POST['team_id'] : 0;
        $employee_id = isset($_POST['employee_id']) ? (int) $_POST['employee_id'] : 0;
        $role_in_team = $_POST['role_in_team'] ?? '';

        if ($team_id > 0 && $employee_id > 0) {
            // Verify the team belongs to the manager
            $verify_team = query($mysqli, "
                SELECT id FROM teams 
                WHERE id = ? AND company_id = ? AND created_by = ?
            ", [$team_id, $company_id, $user_id]);

            if ($verify_team['success'] && !empty($verify_team['data'])) {
                $update_result = query($mysqli, "
                    UPDATE team_members 
                    SET role_in_team = ?
                    WHERE team_id = ? AND employee_id = ?
                ", [$role_in_team, $team_id, $employee_id]);

                if ($update_result['success']) {
                    $response = ['success' => true, 'message' => 'Team member role updated successfully!'];
                } else {
                    $response['message'] = 'Failed to update team member role.';
                }
            } else {
                $response['message'] = 'Team not found or you do not have permission to manage this team.';
            }
        } else {
            $response['message'] = 'Invalid team ID or employee ID.';
        }
        break;

    case 'get_member_tasks':
        $target_employee_id = isset($_GET['employee_id']) ? (int) $_GET['employee_id'] : 0;

        if ($target_employee_id > 0) {
            // Verify permission: Manager or Team Leader of the employee
            $has_permission = false;
            $current_role_id = $_SESSION['role_id'];

            if ($current_role_id == 6) {
                $has_permission = true;
            } elseif ($current_role_id == 4) {
                // Check if user is a leader of the team this employee belongs to
                $check_permission = query($mysqli, "
                    SELECT tm_leader.id 
                    FROM team_members tm_target
                    JOIN team_members tm_leader ON tm_target.team_id = tm_leader.team_id
                    JOIN employees e_leader ON tm_leader.employee_id = e_leader.id
                    WHERE tm_target.employee_id = ? 
                    AND e_leader.user_id = ?
                    AND (
                        LOWER(tm_leader.role_in_team) LIKE '%leader%' 
                        OR LOWER(tm_leader.role_in_team) LIKE '%lead%'
                    )
                ", [$target_employee_id, $user_id]);

                if ($check_permission['success'] && count($check_permission['data']) > 0) {
                    $has_permission = true;
                }
            }

            if ($has_permission) {
                $tasks_result = query($mysqli, "
                    SELECT t.*, 
                           CONCAT(e_by.first_name, ' ', e_by.last_name) as assigned_by_name
                    FROM tasks t
                    LEFT JOIN users u_by ON t.assigned_by = u_by.id
                    LEFT JOIN employees e_by ON u_by.id = e_by.user_id
                    WHERE t.employee_id = ?
                    ORDER BY 
                        CASE WHEN t.status = 'pending' THEN 1 WHEN t.status = 'in_progress' THEN 2 ELSE 3 END,
                        t.due_date ASC
                ", [$target_employee_id]);

                if ($tasks_result['success']) {
                    $response = ['success' => true, 'data' => $tasks_result['data']];
                } else {
                    $response['message'] = 'Failed to fetch tasks.';
                }
            } else {
                $response['message'] = 'You do not have permission to view tasks for this employee.';
            }
        } else {
            $response['message'] = 'Invalid employee ID.';
        }
        break;

    case 'get_tasks':
        $status_filter = $_GET['status'] ?? 'all';
        $employee_filter = $_GET['employee_id'] ?? '';

        // Manager context
        $manager_result = query($mysqli, "SELECT id, department_id FROM employees WHERE user_id = ?", [$user_id]);
        if (!$manager_result['success'] || empty($manager_result['data'])) {
            $response['message'] = 'Manager profile not found.';
            break;
        }
        $manager = $manager_result['data'][0];
        $manager_department_id = $manager['department_id'];

        $where_conditions = ["(e.department_id = ? OR t.assigned_by = ?)"];
        $params = [$manager_department_id, $user_id];

        if ($status_filter !== 'all') {
            $where_conditions[] = "t.status = ?";
            $params[] = $status_filter;
        }

        if (!empty($employee_filter)) {
            $where_conditions[] = "t.employee_id = ?";
            $params[] = $employee_filter;
        }

        $where_clause = implode(' AND ', $where_conditions);

        $sql = "
            SELECT t.*, e.first_name, e.last_name, e.employee_code,
                   des.name as designation_name, d.name as department_name,
                   teams.name as assigned_team_name, teams.id as assigned_team_id
            FROM tasks t
            JOIN employees e ON t.employee_id = e.id
            LEFT JOIN teams ON t.team_id = teams.id
            LEFT JOIN designations des ON e.designation_id = des.id
            LEFT JOIN departments d ON e.department_id = d.id
            WHERE $where_clause
            ORDER BY t.created_at DESC
        ";

        $tasks_result = query($mysqli, $sql, $params);

        if ($tasks_result['success']) {
            $response = ['success' => true, 'data' => $tasks_result['data']];
        } else {
            $response['message'] = 'Failed to fetch tasks.';
        }
        break;

    default:
        $response['message'] = 'Invalid action specified.';
        break;
}

echo json_encode($response);
exit();
?>