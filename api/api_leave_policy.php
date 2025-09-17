<?php
header('Content-Type: application/json');
require_once '../config/db.php';
require_once '../includes/functions.php';

$response = ['success' => false, 'message' => 'An unknown error occurred.'];

if (!isLoggedIn() || !in_array($_SESSION['role_id'], [2, 3])) {
    $response['message'] = 'Unauthorized access.';
    echo json_encode($response);
    exit();
}

$company_id = $_SESSION['company_id'];
$action = $_REQUEST['action'] ?? '';

switch ($action) {
    // --- Leave Policies ---
    case 'get_policies':
        $result = query($mysqli, "SELECT * FROM leave_policies WHERE company_id = ?", [$company_id]);
        $response = ['success' => true, 'data' => $result['data'] ?? []];
        break;

    case 'add_edit_policy':
        $id = (int) ($_POST['id'] ?? 0);
        $leave_type = $_POST['leave_type'] ?? '';
        $days_per_year = (int) ($_POST['days_per_year'] ?? 0);
        $is_accruable = isset($_POST['is_accruable']) ? 1 : 0;

        if (empty($leave_type) || $days_per_year <= 0) {
            $response['message'] = 'Leave Type and a valid number of days are required.';
            break;
        }

        if ($id === 0) {
            $sql = "INSERT INTO leave_policies (company_id, leave_type, days_per_year, is_accruable) VALUES (?, ?, ?, ?)";
            $params = [$company_id, $leave_type, $days_per_year, $is_accruable];
        } else {
            $sql = "UPDATE leave_policies SET leave_type = ?, days_per_year = ?, is_accruable = ? WHERE id = ? AND company_id = ?";
            $params = [$leave_type, $days_per_year, $is_accruable, $id, $company_id];
        }
        $result = query($mysqli, $sql, $params);
        if ($result['success']) {
            $response = ['success' => true, 'message' => 'Leave policy saved successfully!'];
        }
        break;

    case 'delete_policy':
        $id = (int) ($_POST['id'] ?? 0);
        query($mysqli, "DELETE FROM leave_policies WHERE id = ? AND company_id = ?", [$id, $company_id]);
        $response = ['success' => true, 'message' => 'Policy deleted.'];
        break;

    // --- Holidays ---
    case 'get_holidays':
        $result = query($mysqli, "SELECT * FROM holidays WHERE company_id = ? ORDER BY holiday_date ASC", [$company_id]);
        $response = ['success' => true, 'data' => $result['data'] ?? []];
        break;

    case 'add_holiday':
        // This is now for single, manual additions
        $holiday_name = $_POST['holiday_name'] ?? '';
        $holiday_date = $_POST['holiday_date'] ?? '';
        if (!empty($holiday_name) && !empty($holiday_date)) {
            query($mysqli, "INSERT INTO holidays (company_id, holiday_name, holiday_date) VALUES (?, ?, ?)", [$company_id, $holiday_name, $holiday_date]);
            $response = ['success' => true, 'message' => 'Holiday added successfully!'];
        }
        break;

    case 'batch_add_holidays':
        $holiday_ids = $_POST['holiday_ids'] ?? [];
        if (!is_array($holiday_ids) || empty($holiday_ids)) {
            $response['message'] = 'No holidays selected.';
            break;
        }

        $placeholders = implode(',', array_fill(0, count($holiday_ids), '?'));
        $global_holidays_result = query($mysqli, "SELECT holiday_name, holiday_date FROM global_holidays WHERE id IN ($placeholders)", $holiday_ids);

        if ($global_holidays_result['success'] && !empty($global_holidays_result['data'])) {
            $sql = "INSERT INTO holidays (company_id, holiday_name, holiday_date) VALUES ";
            $params = [];
            $rows = [];
            foreach ($global_holidays_result['data'] as $holiday) {
                $rows[] = "(?, ?, ?)";
                array_push($params, $company_id, $holiday['holiday_name'], $holiday['holiday_date']);
            }
            $sql .= implode(',', $rows);
            query($mysqli, $sql, $params);
            $response = ['success' => true, 'message' => 'Selected holidays have been added to your calendar.'];
        }
        break;

    case 'get_unadded_global_holidays':
        // Fetches global holidays that are NOT already in the company's holiday list for the current year
        $current_year = date('Y');
        $sql = "
            SELECT gh.id, gh.holiday_name, gh.holiday_date 
            FROM global_holidays gh
            LEFT JOIN holidays ch ON gh.holiday_date = ch.holiday_date AND ch.company_id = ?
            WHERE ch.id IS NULL AND YEAR(gh.holiday_date) >= ?
            ORDER BY gh.holiday_date ASC
        ";
        $result = query($mysqli, $sql, [$company_id, $current_year]);
        $response = ['success' => true, 'data' => $result['data'] ?? []];
        break;

    case 'delete_holiday':
        $id = (int) ($_POST['id'] ?? 0);
        query($mysqli, "DELETE FROM holidays WHERE id = ? AND company_id = ?", [$id, $company_id]);
        $response = ['success' => true, 'message' => 'Holiday deleted.'];
        break;

    // --- Policy Documents (unchanged) ---
    case 'get_documents':
        $result = query($mysqli, "SELECT id, document_name, file_path, uploaded_at FROM policy_documents WHERE company_id = ?", [$company_id]);
        $response = ['success' => true, 'data' => $result['data'] ?? []];
        break;
    case 'upload_document': // ... (code remains the same)
        if (isset($_FILES['policy_document']) && $_FILES['policy_document']['error'] == 0) {
            $upload_dir = '../../uploads/policies/';
            if (!is_dir($upload_dir))
                mkdir($upload_dir, 0777, true);
            $file_name = uniqid() . '-' . basename($_FILES['policy_document']['name']);
            $target_file = $upload_dir . $file_name;
            $document_name = $_POST['document_name'] ?? pathinfo($_FILES['policy_document']['name'], PATHINFO_FILENAME);
            if (strtolower(pathinfo($target_file, PATHINFO_EXTENSION)) != 'pdf') {
                $response['message'] = 'Sorry, only PDF files are allowed.';
                break;
            }
            if (move_uploaded_file($_FILES['policy_document']['tmp_name'], $target_file)) {
                $db_path = '/hrms/uploads/policies/' . $file_name;
                query($mysqli, "INSERT INTO policy_documents (company_id, document_name, file_path) VALUES (?, ?, ?)", [$company_id, $document_name, $db_path]);
                $response = ['success' => true, 'message' => 'Document uploaded successfully!'];
            }
        }
        break;
    case 'delete_document': // ... (code remains the same)
        $id = (int) ($_POST['id'] ?? 0);
        $doc_result = query($mysqli, "SELECT file_path FROM policy_documents WHERE id = ? AND company_id = ?", [$id, $company_id]);
        if ($doc_result['success'] && !empty($doc_result['data'])) {
            $server_path = $_SERVER['DOCUMENT_ROOT'] . $doc_result['data'][0]['file_path'];
            if (file_exists($server_path))
                unlink($server_path);
            query($mysqli, "DELETE FROM policy_documents WHERE id = ?", [$id]);
            $response = ['success' => true, 'message' => 'Document deleted.'];
        }
        break;

    default:
        $response['message'] = 'Invalid action specified.';
        break;
}

echo json_encode($response);
exit();