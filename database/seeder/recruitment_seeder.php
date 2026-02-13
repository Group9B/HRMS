<?php
// Force error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Database Configuration
$host = 'localhost';
$db = 'original_template';
$user = 'root';
$pass = '';
$dsn = "mysql:host=$host;dbname=$db;charset=utf8mb4";
$options = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES => false,
];

try {
    echo "Connecting to $db...\n";
    $pdo = new PDO($dsn, $user, $pass, $options);

    // Fetch First 3 Companies
    $stmt = $pdo->query("SELECT id, name FROM companies ORDER BY id ASC LIMIT 3");
    $companies = $stmt->fetchAll();

    foreach ($companies as $company) {
        $cid = $company['id'];
        $cname = $company['name'];
        echo "\nSeeding Recruitment for Company: $cname (ID: $cid)...\n";

        // 1. Fetch Departments for Job Assignments
        $stmt = $pdo->prepare("SELECT id, name FROM departments WHERE company_id = ?");
        $stmt->execute([$cid]);
        $depts = $stmt->fetchAll();

        if (empty($depts)) {
            echo "  No departments found. Skipping.\n";
            continue;
        }

        // 2. Create Jobs
        $job_titles = [
            'Software Engineer' => ['Full-time', 'Develop and maintain web applications.', 'Remote', 2],
            'HR Manager' => ['Full-time', 'Oversee recruitment and employee relations.', 'On-site', 1],
            'Marketing Intern' => ['Internship', 'Assist in social media campaigns.', 'Hybrid', 3],
            'Data Analyst' => ['Contract', 'Analyze sales data and generate reports.', 'Remote', 1],
            'Project Manager' => ['Full-time', 'Lead cross-functional teams.', 'On-site', 1]
        ];

        $job_ids = [];

        foreach ($job_titles as $title => $details) {
            $dept = $depts[array_rand($depts)];

            $stmt = $pdo->prepare("SELECT id FROM jobs WHERE company_id = ? AND title = ?");
            $stmt->execute([$cid, $title]);
            $jid = $stmt->fetchColumn();

            if (!$jid) {
                $ins = $pdo->prepare("
                    INSERT INTO jobs (company_id, department_id, title, employment_type, description, location, openings, status, posted_at) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, 'open', NOW())
                ");
                $ins->execute([$cid, $dept['id'], $title, $details[0], $details[1], $details[2], $details[3]]);
                $jid = $pdo->lastInsertId();
            }
            $job_ids[] = $jid;
        }
        echo "  Created/Found " . count($job_ids) . " jobs.\n";

        // 3. Create Candidates
        $first_names = ['Aarav', 'Vivaan', 'Aditya', 'Vihaan', 'Arjun', 'Sai', 'Reyansh', 'Ayaan', 'Krishna', 'Ishaan'];
        $last_names = ['Sharma', 'Verma', 'Gupta', 'Malhotra', 'Bhatia', 'Saxena', 'Mehta', 'Joshi', 'Singh', 'Patel'];

        $candidate_ids = [];

        for ($i = 0; $i < 15; $i++) {
            $fn = $first_names[array_rand($first_names)];
            $ln = $last_names[array_rand($last_names)];
            $email = strtolower($fn . '.' . $ln . rand(100, 999) . '@example.com');
            $phone = '98' . rand(10000000, 99999999);

            $stmt = $pdo->prepare("SELECT id FROM candidates WHERE email = ?");
            $stmt->execute([$email]);
            $can_id = $stmt->fetchColumn();

            if (!$can_id) {
                $ins = $pdo->prepare("
                    INSERT INTO candidates (first_name, last_name, email, phone, status, created_at) 
                    VALUES (?, ?, ?, ?, 'applied', NOW())
                ");
                $ins->execute([$fn, $ln, $email, $phone]);
                $can_id = $pdo->lastInsertId();
            }
            $candidate_ids[] = $can_id;
        }
        echo "  Created/Found " . count($candidate_ids) . " candidates.\n";

        // 4. Create Job Applications & Interviews
        // Fetch HR/Manager for interviewer
        $stmt = $pdo->prepare("
            SELECT u.id as user_id 
            FROM users u 
            WHERE u.company_id = ? AND u.role_id IN (2, 3, 4) 
            LIMIT 1
        ");
        $stmt->execute([$cid]);
        $interviewer_id = $stmt->fetchColumn(); // Default to admin/HR

        $total_apps = 0;
        $total_interviews = 0;

        foreach ($candidate_ids as $can_id) {
            $jid = $job_ids[array_rand($job_ids)];

            // Random Status
            $statuses = ['pending', 'shortlisted', 'interviewed', 'offered', 'hired', 'rejected'];
            $status = $statuses[array_rand($statuses)];

            // Check existing application
            $stmt = $pdo->prepare("SELECT id FROM job_applications WHERE candidate_id = ? AND job_id = ?");
            $stmt->execute([$can_id, $jid]);
            if ($stmt->fetch())
                continue;

            $ins = $pdo->prepare("
                INSERT INTO job_applications (candidate_id, job_id, status, applied_at) 
                VALUES (?, ?, ?, NOW())
            ");
            $ins->execute([$can_id, $jid, $status]);
            $total_apps++;

            // If interviewed or beyond, create interview record
            if (in_array($status, ['interviewed', 'offered', 'hired', 'rejected']) && $interviewer_id) {
                $date = date('Y-m-d H:i:s', strtotime("-" . rand(1, 10) . " days"));

                $int_res = 'pending';
                if ($status == 'rejected')
                    $int_res = 'rejected';
                if ($status == 'hired' || $status == 'offered')
                    $int_res = 'selected';

                $ins = $pdo->prepare("
                    INSERT INTO interviews (candidate_id, job_id, interviewer_id, interview_date, mode, feedback, result, created_at) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?)
                ");
                $ins->execute([
                    $can_id,
                    $jid,
                    $interviewer_id,
                    $date,
                    'online',
                    'Good communication skills.',
                    $int_res,
                    $date
                ]);
                $total_interviews++;
            }
        }
        echo "  Created $total_apps applications and $total_interviews interviews.\n";
    }

    echo "\nSUCCESS: Recruitment seeding complete.\n";

} catch (Exception $e) {
    if (isset($pdo) && $pdo->inTransaction())
        $pdo->rollBack();
    echo "ERROR: " . $e->getMessage() . "\n";
}
?>