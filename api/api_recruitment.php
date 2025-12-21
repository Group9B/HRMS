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

function validateDepartmentExists($departmentId, $companyId, $mysqli)
{
    if ($departmentId) {
        $result = query($mysqli, "SELECT id FROM departments WHERE id = ? AND company_id = ?", [$departmentId, $companyId]);
        if (!$result['success'] || empty($result['data'])) {
            return 'Invalid department selected.';
        }
    }
    return null;
}

function hasJobApplicants($jobId, $mysqli)
{
    $result = query($mysqli, "SELECT COUNT(*) as count FROM job_applications WHERE job_id = ?", [$jobId]);
    return !empty($result['data']) && $result['data'][0]['count'] > 0;
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

        // Check if editing a job with applicants - only allow status change to 'closed'
        if ($id !== 0) {
            if (hasJobApplicants($id, $mysqli)) {
                // Get current job to check if only status changed to closed
                $currentJob = query($mysqli, "SELECT title, department_id, description, employment_type, location, openings, status FROM jobs WHERE id = ? AND company_id = ?", [$id, $company_id]);

                if (!empty($currentJob['data'])) {
                    $current = $currentJob['data'][0];
                    // Check if any field other than status changed
                    if (
                        $title !== $current['title'] ||
                        $department_id != $current['department_id'] ||
                        $description !== $current['description'] ||
                        $employment_type !== $current['employment_type'] ||
                        $location !== $current['location'] ||
                        $openings != $current['openings']
                    ) {
                        $response['message'] = 'Cannot edit job posting when applicants have applied. You can only close the job opening.';
                        break;
                    }
                    // If only status is changing, allow it
                    if ($status === 'closed' || $status === $current['status']) {
                        // Allow status change to closed
                    } else {
                        $response['message'] = 'Cannot edit job posting when applicants have applied. You can only close the job opening.';
                        break;
                    }
                }
            }
        }

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

        $deptError = validateDepartmentExists($department_id, $company_id, $mysqli);
        if ($deptError) {
            $response['message'] = $deptError;
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
        if (hasJobApplicants($id, $mysqli)) {
            $response['message'] = 'Cannot delete job posting with applicants. Close the job opening instead.';
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
        $is_hired = (int) ($_POST['is_hired'] ?? 0);

        if (!$application_id) {
            $response['message'] = 'Invalid application ID.';
            break;
        }

        $statusError = validateApplicationStatus($status);
        if ($statusError) {
            $response['message'] = $statusError;
            break;
        }

        $sql = "UPDATE job_applications SET status = ?, updated_at = NOW() WHERE id = ?";
        $result = query($mysqli, $sql, [$status, $application_id]);
        if ($result['success']) {
            // If status is hired, move candidate to employees table
            if ($is_hired && $status === 'hired') {
                // Get candidate and job details
                $candidateQuery = query($mysqli, "
                    SELECT c.id, c.first_name, c.last_name, c.email, c.phone, c.dob, c.gender,
                           ja.job_id, j.department_id
                    FROM job_applications ja
                    JOIN candidates c ON ja.candidate_id = c.id
                    JOIN jobs j ON ja.job_id = j.id
                    WHERE ja.id = ?
                ", [$application_id]);

                if ($candidateQuery['success'] && !empty($candidateQuery['data'])) {
                    $candidate = $candidateQuery['data'][0];

                    // Create a user account for the candidate first
                    $temporary_password = generateRandomPassword(12);
                    $hashed_password = password_hash($temporary_password, PASSWORD_DEFAULT);

                    // Get employee role ID (usually role_id = 4 for Employee)
                    $roleResult = query($mysqli, "SELECT id FROM roles WHERE name = 'Employee' LIMIT 1");
                    $role_id = (!empty($roleResult['data'])) ? $roleResult['data'][0]['id'] : 4;

                    // Generate username from email
                    $username = strtolower(str_replace('@mail.com', '', $candidate['email']));
                    $username = preg_replace('/[^a-z0-9_]/', '_', $username);

                    $createUserQuery = query($mysqli, "
                        INSERT INTO users (username, email, password, role_id, company_id, status)
                        VALUES (?, ?, ?, ?, ?, 'active')
                    ", [
                        $username,
                        $candidate['email'],
                        $hashed_password,
                        $role_id,
                        $company_id
                    ]);
                    if (!$createUserQuery['success']) {
                        $response['message'] = 'Application status updated but failed to create user account: ' . ($createUserQuery['error'] ?? 'Unknown error');
                        echo json_encode($response);
                        exit();
                    }

                    $user_id = $createUserQuery['insert_id'];

                    // Generate employee code
                    $emp_code = generateEmployeeCode($mysqli, $company_id, date('Y-m-d'));

                    // Insert into employees table with the new user_id
                    $insertEmployeeQuery = query($mysqli, "
                        INSERT INTO employees 
                        (user_id, employee_code, first_name, last_name, dob, gender, contact, 
                         department_id, date_of_joining, status)
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                    ", [
                        $user_id,
                        $emp_code,
                        $candidate['first_name'],
                        $candidate['last_name'],
                        !empty($candidate['dob']) ? $candidate['dob'] : null,
                        !empty($candidate['gender']) ? $candidate['gender'] : null,
                        $candidate['phone'],
                        $candidate['department_id'],
                        date('Y-m-d'),
                        'active'
                    ]);

                    if (!$insertEmployeeQuery['success']) {
                        $response['message'] = 'Application status updated but failed to add employee record: ' . ($insertEmployeeQuery['error'] ?? 'Unknown error');
                        echo json_encode($response);
                        exit();
                    }

                    // Check if job should be auto-closed (all openings filled)
                    $jobId = $candidate['job_id'];
                    $jobDetailsQuery = query($mysqli, "SELECT openings FROM jobs WHERE id = ?", [$jobId]);
                    if ($jobDetailsQuery['success'] && !empty($jobDetailsQuery['data'])) {
                        $requiredOpenings = (int) $jobDetailsQuery['data'][0]['openings'];

                        // Count hired applicants for this job
                        $hiredCountQuery = query($mysqli, "
                            SELECT COUNT(*) as hired_count 
                            FROM job_applications 
                            WHERE job_id = ? AND status = 'hired'
                        ", [$jobId]);

                        if ($hiredCountQuery['success'] && !empty($hiredCountQuery['data'])) {
                            $hiredCount = (int) $hiredCountQuery['data'][0]['hired_count'];

                            // If all openings are filled, close the job
                            if ($hiredCount >= $requiredOpenings) {
                                query($mysqli, "UPDATE jobs SET status = 'closed' WHERE id = ?", [$jobId]);
                            }
                        }
                    }
                } else {
                    $response['message'] = 'Application status updated but could not fetch candidate details.';
                    echo json_encode($response);
                    exit();
                }
            }
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

    // --- Dashboard Stats ---
    case 'get_dashboard_stats':
        // Total jobs
        $totalJobsResult = query($mysqli, "SELECT COUNT(*) as count FROM jobs WHERE company_id = ?", [$company_id]);
        $totalJobs = $totalJobsResult['data'][0]['count'] ?? 0;

        // Total applications
        $totalAppResult = query($mysqli, "SELECT COUNT(*) as count FROM job_applications ja JOIN jobs j ON ja.job_id = j.id WHERE j.company_id = ?", [$company_id]);
        $totalApplications = $totalAppResult['data'][0]['count'] ?? 0;

        // Hired this month
        $hiredMonthResult = query($mysqli, "SELECT COUNT(*) as count FROM job_applications ja JOIN jobs j ON ja.job_id = j.id WHERE j.company_id = ? AND ja.status = 'hired' AND MONTH(ja.updated_at) = MONTH(NOW()) AND YEAR(ja.updated_at) = YEAR(NOW())", [$company_id]);
        $hiredThisMonth = $hiredMonthResult['data'][0]['count'] ?? 0;

        // Open positions (sum of openings for open jobs)
        $openPosResult = query($mysqli, "SELECT COALESCE(SUM(openings), 0) as count FROM jobs WHERE company_id = ? AND status = 'open'", [$company_id]);
        $openPositions = (int) ($openPosResult['data'][0]['count'] ?? 0);

        // Application status breakdown - count by status
        $statusCounts = ['pending' => 0, 'shortlisted' => 0, 'interviewed' => 0, 'offered' => 0, 'hired' => 0, 'rejected' => 0];
        $statusBreakdownResult = query($mysqli, "SELECT ja.status, COUNT(*) as count FROM job_applications ja JOIN jobs j ON ja.job_id = j.id WHERE j.company_id = ? GROUP BY ja.status", [$company_id]);
        if ($statusBreakdownResult['success'] && !empty($statusBreakdownResult['data'])) {
            foreach ($statusBreakdownResult['data'] as $item) {
                $status = strtolower($item['status']);
                if (isset($statusCounts[$status])) {
                    $statusCounts[$status] = (int) $item['count'];
                }
            }
        }

        // Job status breakdown
        $jobCounts = ['open' => 0, 'closed' => 0];
        $jobStatusResult = query($mysqli, "SELECT status, COUNT(*) as count FROM jobs WHERE company_id = ? GROUP BY status", [$company_id]);
        if ($jobStatusResult['success'] && !empty($jobStatusResult['data'])) {
            foreach ($jobStatusResult['data'] as $item) {
                $status = strtolower($item['status']);
                if (isset($jobCounts[$status])) {
                    $jobCounts[$status] = (int) $item['count'];
                }
            }
        }

        $response = [
            'success' => true,
            'data' => array_merge([
                'total_jobs' => $totalJobs,
                'total_applications' => $totalApplications,
                'hired_this_month' => $hiredThisMonth,
                'open_positions' => $openPositions
            ], $statusCounts, $jobCounts)
        ];
        break;

    case 'get_shortlisted_candidates':
        $sql = "SELECT ja.id as application_id, c.first_name, c.last_name, c.email, c.phone, j.title as job_title, ja.applied_at FROM job_applications ja 
                JOIN candidates c ON ja.candidate_id = c.id 
                JOIN jobs j ON ja.job_id = j.id 
                WHERE j.company_id = ? AND ja.status = 'shortlisted' 
                ORDER BY ja.applied_at DESC";
        $result = query($mysqli, $sql, [$company_id]);
        $response = ['success' => true, 'data' => $result['data'] ?? []];
        break;

    case 'get_scheduled_interviews':
        $sql = "SELECT i.id as interview_id, c.first_name, c.last_name, c.email, j.title as job_title, i.interview_date, i.mode, u.first_name as interviewer_first_name, u.last_name as interviewer_last_name 
                FROM interviews i 
                JOIN candidates c ON i.candidate_id = c.id 
                JOIN jobs j ON i.job_id = j.id 
                JOIN users u ON i.interviewer_id = u.id 
                WHERE j.company_id = ? 
                ORDER BY i.interview_date DESC";
        $result = query($mysqli, $sql, [$company_id]);
        $response = ['success' => true, 'data' => $result['data'] ?? []];
        break;

    case 'complete_interview':
        $interview_id = (int) ($_POST['interview_id'] ?? 0);
        if (!$interview_id) {
            $response['message'] = 'Invalid interview ID.';
            break;
        }

        // Verify interview belongs to company
        $interviewCheck = query($mysqli, "SELECT i.id FROM interviews i JOIN jobs j ON i.job_id = j.id WHERE i.id = ? AND j.company_id = ?", [$interview_id, $company_id]);
        if (empty($interviewCheck['data'])) {
            $response['message'] = 'Interview not found.';
            break;
        }

        // Update interview status (mark as completed)
        $result = query($mysqli, "UPDATE interviews SET status = 'completed' WHERE id = ?", [$interview_id]);
        if ($result['success']) {
            $response = ['success' => true, 'message' => 'Interview marked as completed.'];
        }
        break;

    case 'cancel_interview':
        $interview_id = (int) ($_POST['interview_id'] ?? 0);
        if (!$interview_id) {
            $response['message'] = 'Invalid interview ID.';
            break;
        }

        // Verify interview belongs to company
        $interviewCheck = query($mysqli, "SELECT i.id FROM interviews i JOIN jobs j ON i.job_id = j.id WHERE i.id = ? AND j.company_id = ?", [$interview_id, $company_id]);
        if (empty($interviewCheck['data'])) {
            $response['message'] = 'Interview not found.';
            break;
        }

        // Delete the interview
        $result = query($mysqli, "DELETE FROM interviews WHERE id = ?", [$interview_id]);
        if ($result['success']) {
            // Reset the application status back to shortlisted
            query($mysqli, "UPDATE job_applications SET status = 'shortlisted' WHERE id = (SELECT id FROM job_applications WHERE id IN (SELECT MAX(id) FROM job_applications WHERE candidate_id IN (SELECT candidate_id FROM interviews WHERE id = ?)))", [$interview_id]);
            $response = ['success' => true, 'message' => 'Interview cancelled.'];
        }
        break;

    default:
        $response['message'] = 'Invalid action specified.';
        break;
}

echo json_encode($response);
exit();
?>