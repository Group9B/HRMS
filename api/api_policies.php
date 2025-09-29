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

$action = $_REQUEST['action'] ?? '';
$company_id = $_SESSION['company_id'];

switch ($action) {
    // Leave Policy Actions
    case 'get_policies':
        $result = query($mysqli, "SELECT * FROM leave_policies WHERE company_id = ?", [$company_id]);
        $response = ['success' => true, 'data' => $result['data'] ?? []];
        break;

    case 'add_edit_policy':
        $id = (int) ($_POST['id'] ?? 0);
        $leave_type = trim($_POST['leave_type'] ?? '');
        $days = (int) ($_POST['days_per_year'] ?? 0);
        $is_accruable = isset($_POST['is_accruable']) ? 1 : 0;

        if (empty($leave_type) || $days <= 0) {
            $response['message'] = 'Leave type and a valid number of days are required.';
            break;
        }

        if ($id === 0) {
            $sql = "INSERT INTO leave_policies (company_id, leave_type, days_per_year, is_accruable) VALUES (?, ?, ?, ?)";
            $params = [$company_id, $leave_type, $days, $is_accruable];
        } else {
            $sql = "UPDATE leave_policies SET leave_type = ?, days_per_year = ?, is_accruable = ? WHERE id = ? AND company_id = ?";
            $params = [$leave_type, $days, $is_accruable, $id, $company_id];
        }
        $result = query($mysqli, $sql, $params);
        $response = ['success' => $result['success'], 'message' => $result['success'] ? 'Leave policy saved.' : 'Failed to save policy.'];
        break;

    case 'delete_policy':
        $id = (int) ($_POST['id'] ?? 0);
        $result = query($mysqli, "DELETE FROM leave_policies WHERE id = ? AND company_id = ?", [$id, $company_id]);
        $response = ['success' => $result['success'], 'message' => $result['success'] ? 'Policy deleted.' : 'Failed to delete policy.'];
        break;

    // Holiday Actions
    case 'get_holidays':
        $result = query($mysqli, "SELECT * FROM holidays WHERE company_id = ? ORDER BY holiday_date ASC", [$company_id]);
        $response = ['success' => true, 'data' => $result['data'] ?? []];
        break;

    case 'add_holiday':
        $name = trim($_POST['holiday_name'] ?? '');
        $date = $_POST['holiday_date'] ?? '';
        if (empty($name) || empty($date)) {
            $response['message'] = 'Holiday name and date are required.';
            break;
        }
        $result = query($mysqli, "INSERT INTO holidays (company_id, holiday_name, holiday_date) VALUES (?, ?, ?)", [$company_id, $name, $date]);
        $response = ['success' => $result['success'], 'message' => $result['success'] ? 'Holiday added.' : 'Failed to add holiday.'];
        break;

    case 'delete_holiday':
        $id = (int) ($_POST['id'] ?? 0);
        $result = query($mysqli, "DELETE FROM holidays WHERE id = ? AND company_id = ?", [$id, $company_id]);
        $response = ['success' => $result['success'], 'message' => $result['success'] ? 'Holiday deleted.' : 'Failed to delete holiday.'];
        break;

    // Global Holiday Import
    case 'get_unadded_global_holidays':
        $sql = "SELECT gh.* FROM global_holidays gh LEFT JOIN holidays h ON gh.holiday_date = h.holiday_date AND h.company_id = ? WHERE h.id IS NULL ORDER BY gh.holiday_date ASC";
        $result = query($mysqli, $sql, [$company_id]);
        $response = ['success' => true, 'data' => $result['data'] ?? []];
        break;

    case 'batch_add_holidays':
        $holiday_ids = $_POST['holiday_ids'] ?? [];
        if (empty($holiday_ids)) {
            $response['message'] = 'No holidays selected.';
            break;
        }
        $placeholders = implode(',', array_fill(0, count($holiday_ids), '?'));
        $global_holidays_result = query($mysqli, "SELECT holiday_name, holiday_date FROM global_holidays WHERE id IN ($placeholders)", $holiday_ids);

        if ($global_holidays_result['success'] && !empty($global_holidays_result['data'])) {
            $sql = "INSERT INTO holidays (company_id, holiday_name, holiday_date) VALUES (?, ?, ?)";
            $stmt = $mysqli->prepare($sql);
            $count = 0;
            foreach ($global_holidays_result['data'] as $holiday) {
                $stmt->bind_param("iss", $company_id, $holiday['holiday_name'], $holiday['holiday_date']);
                if ($stmt->execute()) {
                    $count++;
                }
            }
            $stmt->close();
            $response = ['success' => true, 'message' => "Successfully imported $count holidays."];
        } else {
            $response['message'] = 'Could not find selected global holidays to import.';
        }
        break;

    // Saturday Policy
    case 'get_holiday_settings':
        $result = query($mysqli, "SELECT saturday_policy FROM company_holiday_settings WHERE company_id = ?", [$company_id]);
        $policy = $result['success'] && !empty($result['data']) ? $result['data'][0]['saturday_policy'] : 'none';
        $response = ['success' => true, 'data' => ['saturday_policy' => $policy]];
        break;

    case 'save_holiday_settings':
        $saturday_policy = $_POST['saturday_policy'] ?? 'none';
        $allowed_policies = ['none', '1st_3rd', '2nd_4th', 'all'];
        if (!in_array($saturday_policy, $allowed_policies)) {
            $response['message'] = 'Invalid Saturday policy specified.';
            break;
        }
        $sql = "INSERT INTO company_holiday_settings (company_id, saturday_policy) VALUES (?, ?) ON DUPLICATE KEY UPDATE saturday_policy = VALUES(saturday_policy)";
        $result = query($mysqli, $sql, [$company_id, $saturday_policy]);
        $response = ['success' => $result['success'], 'message' => $result['success'] ? 'Settings saved.' : 'Failed to save settings.'];
        break;

    // Document Actions
    case 'get_documents':
        // UPDATED: Querying the new generic 'documents' table
        $sql = "SELECT id, doc_name as document_name, file_path, uploaded_at 
                FROM documents 
                WHERE related_id = ? AND related_type = 'policy'
                ORDER BY uploaded_at DESC";
        $result = query($mysqli, $sql, [$company_id]);
        // Frontend expects 'document_name', so we alias 'doc_name'
        $response = ['success' => true, 'data' => $result['data'] ?? []];
        break;

    case 'upload_document':
        $doc_name = trim($_POST['document_name'] ?? '');
        if (empty($doc_name) || !isset($_FILES['policy_document'])) {
            $response['message'] = 'Document name and file are required.';
            break;
        }

        $file = $_FILES['policy_document'];
        define('MAX_FILE_SIZE', 5 * 1024 * 1024); // 5 MB
        $allowed_mime_types = ['application/pdf'];
        $allowed_extensions = ['pdf'];

        if ($file['error'] !== UPLOAD_ERR_OK) {
            $response['message'] = 'File upload error. Code: ' . $file['error'];
            break;
        }
        if ($file['size'] > MAX_FILE_SIZE) {
            $response['message'] = 'File is too large. Maximum size is 5 MB.';
            break;
        }

        $file_extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime_type = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);

        if (!in_array($file_extension, $allowed_extensions) || !in_array($mime_type, $allowed_mime_types)) {
            $response['message'] = 'Invalid file type. Only PDF documents are allowed.';
            break;
        }

        $upload_dir = '../uploads/policies/';
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0755, true);
        }

        $safe_filename = preg_replace("/[^a-zA-Z0-9-_\.]/", "", basename($file['name']));
        $unique_filename = uniqid() . '-' . $safe_filename;
        $destination = $upload_dir . $unique_filename;

        if (move_uploaded_file($file['tmp_name'], $destination)) {
            $db_path = "/hrms/uploads/policies/" . $unique_filename;

            // UPDATED: Inserting into the new generic 'documents' table
            $sql = "INSERT INTO documents (related_id, related_type, doc_name, doc_type, file_path, doc_size, mime_type) VALUES (?, 'policy', ?, 'policy', ?, ?, ?)";
            $params = [$company_id, $doc_name, $db_path, $file['size'], $mime_type];
            $result = query($mysqli, $sql, $params);

            $response = ['success' => $result['success'], 'message' => $result['success'] ? 'Document uploaded.' : 'Failed to save document record.'];
        } else {
            $response['message'] = 'Failed to move uploaded file.';
        }
        break;

    case 'delete_document':
        $id = (int) ($_POST['id'] ?? 0);

        // UPDATED: Select from the 'documents' table to get the file path
        $doc_result = query($mysqli, "SELECT file_path FROM documents WHERE id = ? AND related_id = ? AND related_type = 'policy'", [$id, $company_id]);

        if ($doc_result['success'] && !empty($doc_result['data'])) {
            $relative_path = str_replace("/hrms", "../..", $doc_result['data'][0]['file_path']);
            if (file_exists($relative_path)) {
                @unlink($relative_path);
            }
        }

        // UPDATED: Delete from the 'documents' table
        $result = query($mysqli, "DELETE FROM documents WHERE id = ? AND related_id = ? AND related_type = 'policy'", [$id, $company_id]);
        $response = ['success' => $result['success'], 'message' => $result['success'] ? 'Document deleted.' : 'Failed to delete document.'];
        break;

    default:
        $response['message'] = 'Invalid action specified.';
        break;
}

echo json_encode($response);
exit();

