<?php
// Set the content type to JSON for all responses
header('Content-Type: application/json');

require_once '../config/db.php';
require_once '../includes/functions.php';

$response = ['success' => false, 'message' => 'An unknown error occurred.'];

// Security Check: Must be a logged-in Manager
if (!isLoggedIn() || $_SESSION['role_id'] !== 6) {
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
                COUNT(e.id) as total_members,
                COUNT(CASE WHEN u.status = 'active' THEN 1 END) as active_members,
                COUNT(CASE WHEN t.status IN ('pending', 'in_progress') THEN 1 END) as pending_tasks,
                COUNT(CASE WHEN t.status = 'completed' THEN 1 END) as completed_tasks,
                COUNT(CASE WHEN a.status = 'leave' AND a.date = CURDATE() THEN 1 END) as on_leave_today
            FROM employees e
            JOIN users u ON e.user_id = u.id
            LEFT JOIN tasks t ON e.id = t.employee_id
            LEFT JOIN attendance a ON e.id = a.employee_id AND a.date = CURDATE()
            WHERE e.department_id = ? AND e.id != ?
        ", [$manager_department_id, $manager_id]);

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
            WHERE e.department_id = ? AND e.id != ?
        ", [$manager_department_id, $manager_id]);

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
                WHERE l.id = ? AND e.department_id = ? AND l.status = 'pending'
            ", [$leave_id, $manager_department_id]);

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
                WHERE l.id = ? AND e.department_id = ? AND l.status = 'pending'
            ", [$leave_id, $manager_department_id]);

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
            SET l.status = 'approved', l.approved_by = ?
            WHERE e.department_id = ? AND l.status = 'pending'
        ", [$user_id, $manager_department_id]);

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
                SET l.status = ?, l.approved_by = ?
                WHERE l.id IN ($placeholders) AND e.department_id = ? AND l.status = 'pending'
            ", array_merge([$status, $user_id], $params));

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
            // Verify the employee belongs to the manager's team
            $verify_result = query($mysqli, "
                SELECT id FROM employees 
                WHERE id = ? AND department_id = ? AND status = 'active'
            ", [$employee_id, $manager_department_id]);

            if ($verify_result['success'] && !empty($verify_result['data'])) {
                $insert_result = query($mysqli, "
                    INSERT INTO tasks (employee_id, title, description, due_date, assigned_by, status)
                    VALUES (?, ?, ?, ?, ?, 'pending')
                ", [$employee_id, $title, $description, $due_date, $user_id]);

                if ($insert_result['success']) {
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

    case 'bulk_assign_tasks':
        $employee_ids = isset($_POST['employee_ids']) ? $_POST['employee_ids'] : [];
        $title = $_POST['title'] ?? '';
        $description = $_POST['description'] ?? '';
        $due_date = $_POST['due_date'] ?? null;
        
        if (!empty($employee_ids) && !empty($title)) {
            $success_count = 0;
            $error_count = 0;
            
            foreach ($employee_ids as $employee_id) {
                // Verify the employee belongs to the manager's team
                $verify_result = query($mysqli, "
                    SELECT id FROM employees 
                    WHERE id = ? AND department_id = ? AND status = 'active'
                ", [$employee_id, $manager_department_id]);

                if ($verify_result['success'] && !empty($verify_result['data'])) {
                    $insert_result = query($mysqli, "
                        INSERT INTO tasks (employee_id, title, description, due_date, assigned_by, status)
                        VALUES (?, ?, ?, ?, ?, 'pending')
                    ", [$employee_id, $title, $description, $due_date, $user_id]);

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
                WHERE t.id = ? AND e.department_id = ?
            ", [$task_id, $manager_department_id]);

            if ($task_result['success'] && !empty($task_result['data'])) {
                $response = ['success' => true, 'data' => $task_result['data'][0]];
            } else {
                $response['message'] = 'Task not found.';
            }
        } else {
            $response['message'] = 'Invalid task ID.';
        }
        break;

    case 'cancel_task':
        $task_id = isset($_POST['task_id']) ? (int) $_POST['task_id'] : 0;
        
        if ($task_id > 0) {
            // Verify the task belongs to a team member
            $verify_result = query($mysqli, "
                SELECT t.id FROM tasks t
                JOIN employees e ON t.employee_id = e.id
                WHERE t.id = ? AND e.department_id = ?
            ", [$task_id, $manager_department_id]);

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
                SELECT id FROM employees 
                WHERE id = ? AND department_id = ? AND status = 'active'
            ", [$employee_id, $manager_department_id]);

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

                if (($existing_result['success'] && !empty($existing_result['data']) && $update_result['success']) || 
                    ($existing_result['success'] && empty($existing_result['data']) && $insert_result['success'])) {
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
                WHERE a.id = ? AND e.department_id = ?
            ", [$attendance_id, $manager_department_id]);

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
                SELECT id FROM employees 
                WHERE id = ? AND department_id = ? AND status = 'active'
            ", [$employee_id, $manager_department_id]);

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
                WHERE p.id = ? AND e.department_id = ?
            ", [$performance_id, $manager_department_id]);

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
                WHERE p.id = ? AND e.department_id = ?
            ", [$performance_id, $manager_department_id]);

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
        $team_name = $_POST['name'] ?? '';
        $team_description = $_POST['description'] ?? '';
        
        if (!empty($team_name)) {
            $insert_result = query($mysqli, "
                INSERT INTO teams (company_id, name, description, created_by)
                VALUES (?, ?, ?, ?)
            ", [$company_id, $team_name, $team_description, $user_id]);

            if ($insert_result['success']) {
                $response = ['success' => true, 'message' => 'Team created successfully!'];
            } else {
                $response['message'] = 'Failed to create team.';
            }
        } else {
            $response['message'] = 'Please provide a team name.';
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
                    // Check if employee is already in the team
                    $check_existing = query($mysqli, "
                        SELECT id FROM team_members 
                        WHERE team_id = ? AND employee_id = ?
                    ", [$team_id, $employee_id]);

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
            // Get team details
            $team_result = query($mysqli, "
                SELECT t.*, COUNT(tm.id) as member_count
                FROM teams t
                LEFT JOIN team_members tm ON t.id = tm.team_id
                WHERE t.id = ? AND t.company_id = ? AND t.created_by = ?
                GROUP BY t.id
            ", [$team_id, $company_id, $user_id]);

            if ($team_result['success'] && !empty($team_result['data'])) {
                $team = $team_result['data'][0];
                
                // Get team members
                $members_result = query($mysqli, "
                    SELECT e.*, tm.role_in_team, tm.assigned_at
                    FROM team_members tm
                    JOIN employees e ON tm.employee_id = e.id
                    WHERE tm.team_id = ?
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

    default:
        $response['message'] = 'Invalid action specified.';
        break;
}

echo json_encode($response);
exit();
?>
