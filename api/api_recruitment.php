<?php
header('Content-Type: application/json');
require_once '../config/db.php';
require_once '../includes/functions.php';

$response = ['success' => false, 'message' => 'An unknown error occurred.'];

// Security Check: Company Admin or HR Manager
if (!isLoggedIn() || !in_array($_SESSION['role_id'], [2, 3])) {
    $response['message'] = 'Unauthorized access.';
    echo json_encode($response);
    exit();
}

$company_id = $_SESSION['company_id'];
$user_id = $_SESSION['user_id'];
$action = $_REQUEST['action'] ?? '';

switch ($action) {
    // --- Job Postings ---
    case 'get_jobs':
        $sql = "SELECT j.*, d.name as department_name, (SELECT COUNT(*) FROM job_applications ja WHERE ja.job_id = j.id) as application_count FROM jobs j LEFT JOIN departments d ON j.department_id = d.id WHERE j.company_id = ? ORDER BY j.posted_at DESC";
        $result = query($mysqli, $sql, [$company_id]);
        $response = ['success' => true, 'data' => $result['data'] ?? []];
        break;

    case 'add_edit_job':
        $id = (int) ($_POST['id'] ?? 0);
        $title = $_POST['title'] ?? '';
        $department_id = $_POST['department_id'] ? (int) $_POST['department_id'] : null;
        $description = $_POST['description'] ?? '';
        $employment_type = $_POST['employment_type'] ?? 'full-time'; // Added employment_type
        $location = $_POST['location'] ?? '';
        $openings = (int) ($_POST['openings'] ?? 1);
        $status = $_POST['status'] ?? 'open';

        if (empty($title) || $openings < 1) {
            $response['message'] = 'Job title and at least one opening are required.';
            break;
        }

        if ($id === 0) { // Add
            $sql = "INSERT INTO jobs (company_id, title, department_id, description, employment_type, location, openings, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
            $params = [$company_id, $title, $department_id, $description, $employment_type, $location, $openings, $status];
        } else { // Edit
            $sql = "UPDATE jobs SET title = ?, department_id = ?, description = ?, employment_type = ?, location = ?, openings = ?, status = ? WHERE id = ? AND company_id = ?";
            $params = [$title, $department_id, $description, $employment_type, $location, $openings, $status, $id, $company_id];
        }
        $result = query($mysqli, $sql, $params);
        if ($result['success']) {
            $response = ['success' => true, 'message' => 'Job posting saved successfully!'];
        }
        break;

    // --- Job Applications ---
    case 'get_applications':
        $job_id = (int) ($_GET['job_id'] ?? 0);
        $sql = "SELECT ja.id, ja.status, c.first_name, c.last_name, c.email, c.phone, ja.applied_at FROM job_applications ja JOIN candidates c ON ja.candidate_id = c.id WHERE ja.job_id = ? ORDER BY ja.applied_at DESC";
        $result = query($mysqli, $sql, [$job_id]);
        $response = ['success' => true, 'data' => $result['data'] ?? []];
        break;

    case 'update_application_status':
        $application_id = (int) ($_POST['id'] ?? 0);
        $status = $_POST['status'] ?? '';
        $sql = "UPDATE job_applications SET status = ? WHERE id = ?";
        $result = query($mysqli, $sql, [$status, $application_id]);
        if ($result['success']) {
            $response = ['success' => true, 'message' => 'Application status updated.'];
        }
        break;

    // --- Interviews ---
    case 'schedule_interview':
        $application_id = (int) ($_POST['application_id'] ?? 0);
        $interviewer_id = (int) ($_POST['interviewer_id'] ?? 0);
        $interview_date = $_POST['interview_date'] ?? '';
        $mode = $_POST['mode'] ?? 'offline';

        if (empty($application_id) || empty($interviewer_id) || empty($interview_date)) {
            $response['message'] = 'All fields are required to schedule an interview.';
            break;
        }

        $app_details_result = query($mysqli, "SELECT candidate_id, job_id FROM job_applications WHERE id = ?", [$application_id]);
        if (!$app_details_result['success'] || empty($app_details_result['data'])) {
            $response['message'] = 'Invalid application ID.';
            break;
        }
        $app_details = $app_details_result['data'][0];

        $sql = "INSERT INTO interviews (candidate_id, job_id, interviewer_id, interview_date, mode) VALUES (?, ?, ?, ?, ?)";
        $params = [$app_details['candidate_id'], $app_details['job_id'], $interviewer_id, $interview_date, $mode];
        $result = query($mysqli, $sql, $params);

        if ($result['success']) {
            query($mysqli, "UPDATE job_applications SET status = 'interviewed' WHERE id = ?", [$application_id]);
            $response = ['success' => true, 'message' => 'Interview scheduled successfully!'];
        }
        break;

    default:
        $response['message'] = 'Invalid action specified.';
        break;
}

echo json_encode($response);
exit();
?>