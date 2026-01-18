<?php
header('Content-Type: application/json');
require_once '../config/db.php';
require_once '../includes/functions.php';

$response = ['success' => false, 'message' => 'An unknown error occurred.'];
$action = $_REQUEST['action'] ?? '';

switch ($action) {
    case 'apply_for_job':
        // --- Form Data ---
        $job_id = (int) ($_POST['job_id'] ?? 0);
        $first_name = $_POST['first_name'] ?? '';
        $last_name = $_POST['last_name'] ?? '';
        $email = $_POST['email'] ?? '';
        $phone = $_POST['phone'] ?? '';
        $dob = !empty($_POST['dob']) ? $_POST['dob'] : null;
        $gender = $_POST['gender'] ?? null;

        // --- Validation ---
        if (empty($job_id) || empty($first_name) || empty($last_name) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $response['message'] = 'Please fill in all required fields correctly.';
            break;
        }
        if (!isset($_FILES['resume']) || $_FILES['resume']['error'] != 0) {
            $response['message'] = 'A resume/CV file is required.';
            break;
        }

        // --- Check if already applied ---
        $check_sql = "SELECT ja.id FROM job_applications ja JOIN candidates c ON ja.candidate_id = c.id WHERE ja.job_id = ? AND c.email = ?";
        $already_applied = query($mysqli, $check_sql, [$job_id, $email]);
        if (!empty($already_applied['data'])) {
            $response['message'] = 'You have already applied for this position with this email address.';
            break;
        }

        // --- File Upload ---
        $upload_dir = '../../uploads/resumes/';
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }
        $file_name = uniqid() . '-' . basename($_FILES['resume']['name']);
        $target_file = $upload_dir . $file_name;
        $file_size = $_FILES['resume']['size'];
        $mime_type = $_FILES['resume']['type'];

        // Allow only specific file types (e.g., PDF, DOC, DOCX)
        $allowed_types = ['application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'];
        if (!in_array($mime_type, $allowed_types)) {
            $response['message'] = 'Invalid file type. Only PDF, DOC, and DOCX are allowed.';
            break;
        }

        if (move_uploaded_file($_FILES['resume']['tmp_name'], $target_file)) {
            $db_path = '/hrms/uploads/resumes/' . $file_name;

            // Get job and company details for email
            $job_details = query($mysqli, "SELECT j.title, c.name as company_name FROM jobs j JOIN companies c ON j.company_id = c.id WHERE j.id = ?", [$job_id]);
            $job_title = $job_details['data'][0]['title'] ?? 'the position';
            $company_name = $job_details['data'][0]['company_name'] ?? 'the company';

            // --- Database Inserts ---
            // 1. Create Candidate
            $candidate_sql = "INSERT INTO candidates (first_name, last_name, email, phone, dob, gender) VALUES (?, ?, ?, ?, ?, ?)";
            $candidate_result = query($mysqli, $candidate_sql, [$first_name, $last_name, $email, $phone, $dob, $gender]);
            $candidate_id = $candidate_result['insert_id'] ?? 0;

            if ($candidate_id > 0) {
                // 2. Create Document Record
                $doc_sql = "INSERT INTO documents (doc_name, doc_type, file_path, doc_size, mime_type, related_type, related_id) VALUES (?, ?, ?, ?, ?, ?, ?)";
                query($mysqli, $doc_sql, ["Resume/CV", "resume", $db_path, $file_size, $mime_type, 'candidate', $candidate_id]);

                // 3. Create Job Application
                $app_sql = "INSERT INTO job_applications (candidate_id, job_id) VALUES (?, ?)";
                query($mysqli, $app_sql, [$candidate_id, $job_id]);

                // 4. Send confirmation email to candidate
                try {
                    require_once '../includes/mail/MailService.php';
                    $mailService = new MailService();
                    $emailBody = "
                        <h2>Application Received!</h2>
                        <p>Dear {$first_name},</p>
                        <p>Thank you for applying for the position of <strong>{$job_title}</strong> at <strong>{$company_name}</strong>.</p>
                        <p>We have received your application and our recruitment team will review it shortly.</p>
                        <p>You can check your application status anytime by visiting our portal and entering your email address.</p>
                        <br>
                        <p>Best regards,<br>HR Team at {$company_name}</p>
                    ";
                    $mailService->send($email, $first_name, "Application Received - {$job_title}", $emailBody);
                } catch (Exception $e) {
                    // Email failure should not affect application success
                    error_log("Failed to send application confirmation email: " . $e->getMessage());
                }

                $response = ['success' => true, 'message' => 'Your application has been submitted successfully! You can check your status using your email address.'];
            } else {
                $response['message'] = 'Failed to create candidate record.';
            }
        } else {
            $response['message'] = 'Sorry, there was an error uploading your file.';
        }
        break;

    case 'check_status':
        $email = $_GET['email'] ?? '';
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $response['message'] = 'Please enter a valid email address.';
            break;
        }

        $sql = "
            SELECT 
                j.title as job_title, 
                c.name as company_name, 
                ja.applied_at, 
                ja.status,
                i.interview_date,
                i.mode,
                CONCAT(emp.first_name, ' ', emp.last_name) as interviewer_name
            FROM job_applications ja
            JOIN candidates can ON ja.candidate_id = can.id
            JOIN jobs j ON ja.job_id = j.id
            JOIN companies c ON j.company_id = c.id
            LEFT JOIN interviews i ON ja.candidate_id = i.candidate_id AND ja.job_id = i.job_id
            LEFT JOIN users u ON i.interviewer_id = u.id
            LEFT JOIN employees emp ON u.id = emp.user_id
            WHERE can.email = ?
            ORDER BY ja.applied_at DESC
        ";
        $result = query($mysqli, $sql, [$email]);
        $response = ['success' => true, 'data' => $result['data'] ?? []];
        break;

    default:
        $response['message'] = 'Invalid action specified.';
        break;
}

echo json_encode($response);
exit();
?>