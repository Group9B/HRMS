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

// --- Validation Functions ---
function validateJobTitle($title)
{
    if (!$title || trim($title) === '')
        return 'Job title is required.';
    if (strlen($title) < 3)
        return 'Job title must be at least 3 characters long.';
    if (strlen($title) > 100)
        return 'Job title must not exceed 100 characters.';
    if (!preg_match('/^[a-zA-Z0-9\s\-&.,()]+$/', $title))
        return 'Job title contains invalid characters.';
    return null;
}

function validateDescription($description, $maxLength = 2000)
{
    if ($description && strlen($description) > $maxLength)
        return "Description must not exceed $maxLength characters.";
    return null;
}

function validateLocation($location)
{
    if ($location && strlen($location) > 100)
        return 'Location must not exceed 100 characters.';
    return null;
}

function validateOpenings($openings)
{
    if (!is_numeric($openings) || (int) $openings < 1)
        return 'Number of openings must be at least 1.';
    if ((int) $openings > 999)
        return 'Number of openings cannot exceed 999.';
    return null;
}

function validateEmploymentType($type)
{
    $validTypes = ['full-time', 'part-time', 'internship', 'contract'];
    if (!in_array($type, $validTypes))
        return 'Invalid employment type selected.';
    return null;
}

function validateJobStatus($status)
{
    $validStatuses = ['open', 'closed'];
    if (!in_array($status, $validStatuses))
        return 'Invalid job status.';
    return null;
}

function validateInterviewDate($date)
{
    if (!$date)
        return 'Interview date and time is required.';
    $timestamp = strtotime($date);
    if (!$timestamp)
        return 'Invalid date format.';
    if ($timestamp < time())
        return 'Interview date cannot be in the past.';
    return null;
}

function validateApplicationStatus($status)
{
    $validStatuses = ['pending', 'shortlisted', 'interviewed', 'offered', 'hired', 'rejected'];
    if (!in_array($status, $validStatuses))
        return 'Invalid application status.';
    return null;
}

// --- End Validation Functions ---

switch ($action) {
    // --- Job Postings ---
    case 'get_jobs':
        $sql = "SELECT j.*, d.name as department_name, (SELECT COUNT(*) FROM job_applications ja WHERE ja.job_id = j.id) as application_count FROM jobs j LEFT JOIN departments d ON j.department_id = d.id WHERE j.company_id = ? ORDER BY j.posted_at DESC";
        $result = query($mysqli, $sql, [$company_id]);
        $response = ['success' => true, 'data' => $result['data'] ?? []];
        break;

    case 'add_edit_job':
        $id = (int) ($_POST['id'] ?? 0);
        $title = trim($_POST['title'] ?? '');
        $department_id = $_POST['department_id'] ? (int) $_POST['department_id'] : null;
        $description = trim($_POST['description'] ?? '');
        $employment_type = $_POST['employment_type'] ?? 'full-time';
        $location = trim($_POST['location'] ?? '');
        $openings = $_POST['openings'] ?? 1;
        $status = $_POST['status'] ?? 'open';

        // Validate all inputs
        $titleError = validateJobTitle($title);
        if ($titleError) {
            $response['message'] = $titleError;
            break;
        }

        $descError = validateDescription($description);
        if ($descError) {
            $response['message'] = $descError;
            break;
        }

        $locError = validateLocation($location);
        if ($locError) {
            $response['message'] = $locError;
            break;
        }

        $openingsError = validateOpenings($openings);
        if ($openingsError) {
            $response['message'] = $openingsError;
            break;
        }

        $typeError = validateEmploymentType($employment_type);
        if ($typeError) {
            $response['message'] = $typeError;
            break;
        }

        $statusError = validateJobStatus($status);
        if ($statusError) {
            $response['message'] = $statusError;
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

    case 'delete_job':
        $id = (int) ($_POST['id'] ?? 0);
        if (!$id) {
            $response['message'] = 'Invalid job ID.';
            break;
        }
        $sql = "DELETE FROM jobs WHERE id = ? AND company_id = ?";
        $result = query($mysqli, $sql, [$id, $company_id]);
        if ($result['success']) {
            $response = ['success' => true, 'message' => 'Job posting deleted successfully!'];
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
        $status = trim($_POST['status'] ?? '');

        if (!$application_id) {
            $response['message'] = 'Invalid application ID.';
            break;
        }

        $statusError = validateApplicationStatus($status);
        if ($statusError) {
            $response['message'] = $statusError;
            break;
        }

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
        $interview_date = trim($_POST['interview_date'] ?? '');
        $mode = trim($_POST['mode'] ?? 'offline');

        if (!$application_id) {
            $response['message'] = 'Invalid application ID.';
            break;
        }

        if (!$interviewer_id) {
            $response['message'] = 'Interviewer is required.';
            break;
        }

        $dateError = validateInterviewDate($interview_date);
        if ($dateError) {
            $response['message'] = $dateError;
            break;
        }

        $validModes = ['offline', 'online'];
        if (!in_array($mode, $validModes)) {
            $response['message'] = 'Invalid interview mode.';
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