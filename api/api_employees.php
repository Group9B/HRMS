<?php
// Set the content type to JSON for all responses
header('Content-Type: application/json');

require_once '../config/db.php';
require_once '../includes/functions.php';

$response = ['success' => false, 'message' => 'An unknown error occurred.'];

// Security Check: Must be a logged-in Company Admin
if (!isLoggedIn() && !in_array($_SESSION['role_id'], [2, 3])) {
    $response['message'] = 'Unauthorized access.';
    echo json_encode($response);
    exit();
}

$action = $_REQUEST['action'] ?? '';
$company_id = $_SESSION['company_id']; // All operations are scoped to this company

switch ($action) {
    case 'get_employees':
        $sql = "
            SELECT e.*, d.name as department_name, des.name as designation_name, s.name as shift_name, s.start_time, s.end_time
            FROM employees e
            LEFT JOIN departments d ON e.department_id = d.id
            LEFT JOIN designations des ON e.designation_id = des.id
            LEFT JOIN shifts s ON e.shift_id = s.id
            WHERE d.company_id = ?
            ORDER BY e.first_name ASC
        ";
        $result = query($mysqli, $sql, [$company_id]);
        if ($result['success']) {
            $response = ['success' => true, 'data' => $result['data']];
        } else {
            $response['message'] = 'Failed to fetch employees.';
        }
        break;

    case 'add_edit':
        $employee_id = isset($_POST['employee_id']) ? (int) $_POST['employee_id'] : 0;
        $user_id = isset($_POST['user_id']) ? (int) $_POST['user_id'] : 0;
        $first_name = $_POST['first_name'] ?? '';
        $last_name = $_POST['last_name'] ?? '';
        $department_id = $_POST['department_id'] ?? null;
        $designation_id = $_POST['designation_id'] ?? null;
        $shift_id = $_POST['shift_id'] ?? null;
        $date_of_joining = $_POST['date_of_joining'] ?? date('Y-m-d');
        $status = $_POST['status'] ?? 'active';

        // Frontend and Backend Validation
        $validation_errors = [];

        // Regex for names (only letters, spaces, hyphens, and apostrophes - no numbers)
        $name_regex = '/^[a-zA-Z\s\-\']+$/';

        if (empty($first_name)) {
            $validation_errors[] = 'First name is required.';
        } else if (!preg_match($name_regex, $first_name)) {
            $validation_errors[] = 'First name cannot contain numbers or special characters.';
        }

        if (empty($last_name)) {
            $validation_errors[] = 'Last name is required.';
        } else if (!preg_match($name_regex, $last_name)) {
            $validation_errors[] = 'Last name cannot contain numbers or special characters.';
        }

        if (empty($user_id)) {
            $validation_errors[] = 'User account is required.';
        }
        if (empty($department_id)) {
            $validation_errors[] = 'Department is required.';
        } else {
            // Verify that the selected department belongs to the current company
            $dept_check = query($mysqli, "SELECT id FROM departments WHERE id = ? AND company_id = ?", [$department_id, $company_id]);
            if (!$dept_check['success'] || empty($dept_check['data'])) {
                $validation_errors[] = 'Invalid department selected.';
            }
        }

        if (!empty($designation_id)) {
            // Verify that the selected designation belongs to the selected department
            $des_check = query($mysqli, "SELECT d.id FROM designations d JOIN departments dept ON d.department_id = dept.id WHERE d.id = ? AND dept.id = ? AND dept.company_id = ?", [$designation_id, $department_id, $company_id]);
            if (!$des_check['success'] || empty($des_check['data'])) {
                $validation_errors[] = 'Invalid designation selected for the chosen department.';
            }
        }

        if (!empty($shift_id)) {
            // Verify that the selected shift belongs to the current company
            $shift_check = query($mysqli, "SELECT id FROM shifts WHERE id = ? AND company_id = ?", [$shift_id, $company_id]);
            if (!$shift_check['success'] || empty($shift_check['data'])) {
                $validation_errors[] = 'Invalid shift selected.';
            }
        }

        if (!empty($validation_errors)) {
            $response['message'] = implode(' ', $validation_errors);
            break;
        }

        // Validate date of joining range with special handling for edits
        $date_validation_errors = [];
        if (!empty($date_of_joining)) {
            $joining_date = strtotime($date_of_joining);
            $today = strtotime(date('Y-m-d'));
            $yesterday = $today - (24 * 60 * 60);
            $max_date = strtotime('+3 months', $today);

            if ($employee_id === 0) {
                // For new employees: allow yesterday to 3 months ahead
                if ($joining_date < $yesterday) {
                    $date_validation_errors[] = 'Date of joining cannot be in the past (before yesterday).';
                } elseif ($joining_date > $max_date) {
                    $date_validation_errors[] = 'Date of joining cannot be more than 3 months from today.';
                }
            } else {
                // For editing existing employees
                // Get the current joining date from database
                $current_emp_query = query($mysqli, "SELECT date_of_joining FROM employees WHERE id = ?", [$employee_id]);
                if ($current_emp_query['success'] && !empty($current_emp_query['data'])) {
                    $current_joining_date = strtotime($current_emp_query['data'][0]['date_of_joining']);

                    if ($current_joining_date < $today) {
                        // If current date is in the past, completely block any date changes
                        if ($joining_date !== $current_joining_date) {
                            $date_validation_errors[] = 'Cannot modify joining date for employees who have already joined. Contact admin for assistance.';
                        }
                    } else {
                        // If current date is today or future, allow normal range
                        if ($joining_date < $yesterday) {
                            $date_validation_errors[] = 'Date of joining cannot be in the past (before yesterday).';
                        } elseif ($joining_date > $max_date) {
                            $date_validation_errors[] = 'Date of joining cannot be more than 3 months from today.';
                        }
                    }
                }
            }
        }

        if (!empty($date_validation_errors)) {
            $response['message'] = implode(' ', $date_validation_errors);
            break;
        }

        if ($employee_id === 0) { // Add new employee
            // Call the revised function to generate a unique employee code
            $employee_code = generateEmployeeCode($mysqli, $company_id, $date_of_joining);

            if ($employee_code === null) {
                $response['message'] = 'Error: Could not generate a unique employee code.';
                break;
            }

            // Add 'employee_code' to the INSERT statement
            $sql = "INSERT INTO employees (employee_code, user_id, first_name, last_name, department_id, designation_id, shift_id, date_of_joining, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $params = [$employee_code, $user_id, $first_name, $last_name, $department_id, $designation_id, $shift_id, $date_of_joining, $status];

        } else { // Edit existing employee
            // The employee_code is not updated. It's a permanent identifier.
            $sql = "UPDATE employees SET user_id = ?, first_name = ?, last_name = ?, department_id = ?, designation_id = ?, shift_id = ?, date_of_joining = ?, status = ? WHERE id = ? AND user_id IN (SELECT id FROM users WHERE company_id = ?)";
            $params = [$user_id, $first_name, $last_name, $department_id, $designation_id, $shift_id, $date_of_joining, $status, $employee_id, $company_id];
        }

        $result = query($mysqli, $sql, $params);
        if ($result['success']) {
            $response = ['success' => true, 'message' => 'Employee saved successfully!'];
        } else {
            if ($mysqli->errno == 1062) {
                $response['message'] = 'Database Error: A duplicate entry was detected. The employee code may already exist.';
            } else {
                $response['message'] = 'Database error: ' . $result['error'];
            }
        }
        break;

    case 'delete':
        $employee_id = isset($_POST['employee_id']) ? (int) $_POST['employee_id'] : 0;
        if ($employee_id > 0) {
            $sql = "DELETE e FROM employees e JOIN departments d ON e.department_id = d.id WHERE e.id = ? AND d.company_id = ?";
            $result = query($mysqli, $sql, [$employee_id, $company_id]);
            if ($result['success'] && $result['affected_rows'] > 0) {
                $response = ['success' => true, 'message' => 'Employee deleted successfully!'];
            } else {
                $response['message'] = 'Failed to delete employee or employee not found.';
            }
        }
        break;

    case 'get_designations_by_department':
        $department_id = isset($_GET['department_id']) ? (int) $_GET['department_id'] : 0;

        if ($department_id <= 0) {
            $response['message'] = 'Invalid department ID.';
            break;
        }

        // Verify that the department belongs to the current company
        $dept_check = query($mysqli, "SELECT id FROM departments WHERE id = ? AND company_id = ?", [$department_id, $company_id]);
        if (!$dept_check['success'] || empty($dept_check['data'])) {
            $response['message'] = 'Department not found or unauthorized.';
            break;
        }

        // Fetch designations for the selected department
        $sql = "SELECT id, name FROM designations WHERE department_id = ? ORDER BY name ASC";
        $result = query($mysqli, $sql, [$department_id]);

        if ($result['success']) {
            $response = ['success' => true, 'data' => $result['data']];
        } else {
            $response['message'] = 'Failed to fetch designations.';
        }
        break;

    // ─────────────────────────────────────────────────────────────
    // IoT Credential Management (RFID / Fingerprint / Face ID)
    // ─────────────────────────────────────────────────────────────

    case 'get_credentials':
        $employee_id = isset($_GET['employee_id']) ? (int) $_GET['employee_id'] : 0;

        if ($employee_id <= 0) {
            $response['message'] = 'Invalid employee ID.';
            break;
        }

        // Verify employee belongs to this company
        $emp_check = query($mysqli, "SELECT e.id FROM employees e JOIN users u ON e.user_id = u.id WHERE e.id = ? AND u.company_id = ?", [$employee_id, $company_id]);
        if (!$emp_check['success'] || empty($emp_check['data'])) {
            $response['message'] = 'Employee not found or unauthorized.';
            break;
        }

        $sql = "SELECT id, type, identifier_value, created_at FROM employee_credentials WHERE employee_id = ? ORDER BY created_at DESC";
        $result = query($mysqli, $sql, [$employee_id]);

        if ($result['success']) {
            $response = ['success' => true, 'data' => $result['data'] ?? []];
        } else {
            $response['message'] = 'Failed to fetch credentials.';
        }
        break;

    case 'add_credential':
        $employee_id = isset($_POST['employee_id']) ? (int) $_POST['employee_id'] : 0;
        $type = $_POST['credential_type'] ?? '';
        $identifier_value = trim($_POST['identifier_value'] ?? '');

        // Validate inputs
        if ($employee_id <= 0) {
            $response['message'] = 'Invalid employee ID.';
            break;
        }

        $valid_types = ['rfid', 'fingerprint', 'face_id'];
        if (!in_array($type, $valid_types, true)) {
            $response['message'] = 'Invalid credential type. Must be: rfid, fingerprint, or face_id.';
            break;
        }

        if (empty($identifier_value)) {
            $response['message'] = 'Identifier value is required.';
            break;
        }

        // For RFID: validate hex format and normalize to uppercase
        if ($type === 'rfid') {
            $identifier_value = strtoupper(preg_replace('/[^a-fA-F0-9]/', '', $identifier_value));
            if (empty($identifier_value) || strlen($identifier_value) < 4 || strlen($identifier_value) > 20) {
                $response['message'] = 'RFID UID must be 4-20 hex characters (e.g., 0A8F8005).';
                break;
            }
        }

        // Verify employee belongs to this company
        $emp_check = query($mysqli, "SELECT e.id FROM employees e JOIN users u ON e.user_id = u.id WHERE e.id = ? AND u.company_id = ?", [$employee_id, $company_id]);
        if (!$emp_check['success'] || empty($emp_check['data'])) {
            $response['message'] = 'Employee not found or unauthorized.';
            break;
        }

        // Check if credential already exists (unique constraint)
        $dup_check = query($mysqli, "SELECT ec.id, e.first_name, e.last_name FROM employee_credentials ec JOIN employees e ON ec.employee_id = e.id WHERE ec.type = ? AND ec.identifier_value = ?", [$type, $identifier_value]);
        if ($dup_check['success'] && !empty($dup_check['data'])) {
            $existing = $dup_check['data'][0];
            $response['message'] = "This {$type} credential is already assigned to {$existing['first_name']} {$existing['last_name']}.";
            break;
        }

        $sql = "INSERT INTO employee_credentials (employee_id, type, identifier_value) VALUES (?, ?, ?)";
        $result = query($mysqli, $sql, [$employee_id, $type, $identifier_value]);

        if ($result['success']) {
            $response = ['success' => true, 'message' => ucfirst($type) . ' credential added successfully!'];
        } else {
            if ($mysqli->errno == 1062) {
                $response['message'] = 'This credential is already registered to another employee.';
            } else {
                $response['message'] = 'Failed to add credential: ' . ($result['error'] ?? 'Unknown error');
            }
        }
        break;

    case 'delete_credential':
        $credential_id = isset($_POST['credential_id']) ? (int) $_POST['credential_id'] : 0;

        if ($credential_id <= 0) {
            $response['message'] = 'Invalid credential ID.';
            break;
        }

        // Verify credential belongs to an employee in this company
        $cred_check = query($mysqli, "SELECT ec.id FROM employee_credentials ec JOIN employees e ON ec.employee_id = e.id JOIN users u ON e.user_id = u.id WHERE ec.id = ? AND u.company_id = ?", [$credential_id, $company_id]);
        if (!$cred_check['success'] || empty($cred_check['data'])) {
            $response['message'] = 'Credential not found or unauthorized.';
            break;
        }

        $sql = "DELETE FROM employee_credentials WHERE id = ?";
        $result = query($mysqli, $sql, [$credential_id]);

        if ($result['success'] && $result['affected_rows'] > 0) {
            $response = ['success' => true, 'message' => 'Credential removed successfully!'];
        } else {
            $response['message'] = 'Failed to remove credential.';
        }
        break;

    default:
        $response['message'] = 'Invalid action specified.';
        break;
}

echo json_encode($response);
exit();
?>