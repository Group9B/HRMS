<?php
// Force error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

$logFile = __DIR__ . '/admin_seeder_log.txt';
file_put_contents($logFile, "Admin Seeder Started at " . date('Y-m-d H:i:s') . "\n");

function logger($msg)
{
    global $logFile;
    echo $msg . "\n";
    file_put_contents($logFile, $msg . "\n", FILE_APPEND);
}

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
    logger("Connecting to $db...");
    $pdo = new PDO($dsn, $user, $pass, $options);
    logger("Connected successfully.");

    // Fetch all companies with Address
    $stmt = $pdo->query("SELECT id, name, email, phone, address FROM companies");
    $companies = $stmt->fetchAll();

    logger("Found " . count($companies) . " companies. Processing...");

    $pdo->beginTransaction();
    $created_count = 0;

    foreach ($companies as $company) {
        $company_id = $company['id'];
        $company_name = $company['name'];
        $company_email = $company['email']; // Is Indian info@...
        $phone = $company['phone'];         // Is Indian +91...
        $address = $company['address'];     // Is Indian Address

        // 1. Generate Username
        $clean_name = strtolower(preg_replace('/[^a-zA-Z0-9\s]/', '', $company_name));
        $username = str_replace(' ', '_', $clean_name) . '_admin';

        $check = $pdo->prepare("SELECT id FROM users WHERE username = ?");
        $check->execute([$username]);
        if ($check->fetch()) {
            logger("User $username already exists.");
            // We continue to ensure Employee record links if missing? 
            // Ideally we shouldn't create duplicate employees if user exists.
            // We'll skip for now to avoid complexity.
            continue;
        }

        // 2. Default Department: Administration
        $stmt_dept = $pdo->prepare("SELECT id FROM departments WHERE company_id = ? AND name = 'Administration'");
        $stmt_dept->execute([$company_id]);
        $dept_id = $stmt_dept->fetchColumn();

        if (!$dept_id) {
            $pdo->prepare("INSERT INTO departments (company_id, name, description) VALUES (?, 'Administration', 'Company Administration')")->execute([$company_id]);
            $dept_id = $pdo->lastInsertId();
        }

        // 3. Default Designation: CEO / Owner
        $stmt_des = $pdo->prepare("SELECT id FROM designations WHERE department_id = ? AND name LIKE 'CEO%'");
        $stmt_des->execute([$dept_id]);
        $des_id = $stmt_des->fetchColumn();

        if (!$des_id) {
            $pdo->prepare("INSERT INTO designations (department_id, name, description) VALUES (?, 'CEO / Owner', 'Company Owner')")->execute([$dept_id]);
            $des_id = $pdo->lastInsertId();
        }

        // 4. Default Shift: General Shift
        $stmt_shift = $pdo->prepare("SELECT id FROM shifts WHERE company_id = ? AND name = 'General Shift'");
        $stmt_shift->execute([$company_id]);
        $shift_id = $stmt_shift->fetchColumn();

        if (!$shift_id) {
            $pdo->prepare("INSERT INTO shifts (company_id, name, start_time, end_time, description) VALUES (?, 'General Shift', '09:00:00', '18:00:00', 'Standard Day Shift')")->execute([$company_id]);
            $shift_id = $pdo->lastInsertId();
        }

        // 5. Create User
        $password_hash = password_hash('Staff12@', PASSWORD_DEFAULT);
        $role_id = 2; // Company Owner

        // Ensure email unique
        $check_email = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $check_email->execute([$company_email]);
        if ($check_email->fetch()) {
            $email_to_use = $username . '@example.com';
        } else {
            $email_to_use = $company_email;
        }

        $stmt_user = $pdo->prepare("INSERT INTO users (company_id, role_id, username, email, password, status) VALUES (?, ?, ?, ?, ?, 'active')");
        $stmt_user->execute([$company_id, $role_id, $username, $email_to_use, $password_hash]);
        $user_id = $pdo->lastInsertId();

        // 6. Create Employee with FULL Indian Details
        $emp_code = "ADM-" . str_pad($company_id, 3, '0', STR_PAD_LEFT);

        $parts = explode(' ', $company_name);
        $first_name = $parts[0];
        $last_name = isset($parts[1]) ? $parts[1] : 'Admin';

        // Generate realistic Indian DOB (Owners usually 30-50 years old)
        $year = rand(1975, 1995);
        $month = rand(1, 12);
        $day = rand(1, 28);
        $dob = "$year-$month-$day";

        $gender = (rand(0, 1) == 0) ? 'male' : 'female';

        $stmt_emp = $pdo->prepare("INSERT INTO employees (user_id, employee_code, first_name, last_name, contact, address, department_id, designation_id, shift_id, date_of_joining, dob, gender, status, salary) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, CURDATE(), ?, ?, 'active', 0)");

        try {
            $stmt_emp->execute([$user_id, $emp_code, $first_name, $last_name, $phone, $address, $dept_id, $des_id, $shift_id, $dob, $gender]);
            $created_count++;
        } catch (PDOException $e) {
            logger("Failed to create employee for $username: " . $e->getMessage());
        }
    }

    $pdo->commit();
    logger("SUCCESS: Created $created_count admins for " . count($companies) . " companies.");

} catch (Exception $e) {
    if (isset($pdo) && $pdo->inTransaction())
        $pdo->rollBack();
    logger("ERROR: " . $e->getMessage());
}
?>