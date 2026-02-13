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
    echo "Connected.\n";

    // 1. Fetch First 3 Companies
    $stmt = $pdo->query("SELECT * FROM companies ORDER BY id ASC LIMIT 3");
    $companies = $stmt->fetchAll();

    if (count($companies) == 0) {
        die("No companies found.\n");
    }

    $pdo->beginTransaction();

    foreach ($companies as $company) {
        $cid = $company['id'];
        $cname = $company['name'];
        echo "\nProcessing Company: $cname (ID: $cid)...\n";

        // 1. Pick an existing Department and Designation (Prefer 'Manager' in name)
        $stmt = $pdo->prepare("
            SELECT d.id as desig_id, d.name as desig_name, dept.id as dept_id, dept.name as dept_name 
            FROM designations d 
            JOIN departments dept ON d.department_id = dept.id 
            WHERE dept.company_id = ?
        ");
        $stmt->execute([$cid]);
        $all_desigs = $stmt->fetchAll();

        $target_desig = null;

        // Try to find one with 'Manager'
        foreach ($all_desigs as $d) {
            if (stripos($d['desig_name'], 'Manager') !== false) {
                $target_desig = $d;
                break;
            }
        }

        // Fallback to random
        if (!$target_desig && count($all_desigs) > 0) {
            $target_desig = $all_desigs[array_rand($all_desigs)];
        }

        // Fallback if no designations exist at all (edge case)
        if (!$target_desig) {
            // Create default if absolutely nothing exists
            echo "  No designations found. Creating default.\n";
            // ... existing fallback or skip ...
            // Let's just create Administration/General Manager ONLY if needed
            $stmt = $pdo->prepare("SELECT id FROM departments WHERE company_id = ? AND name = 'Administration'");
            $stmt->execute([$cid]);
            $dept_id = $stmt->fetchColumn();
            if (!$dept_id) {
                $stmt = $pdo->prepare("INSERT INTO departments (company_id, name) VALUES (?, 'Administration')");
                $stmt->execute([$cid]);
                $dept_id = $pdo->lastInsertId();
            }
            $stmt = $pdo->prepare("SELECT id FROM designations WHERE department_id = ? AND name = 'General Manager'");
            $stmt->execute([$dept_id]);
            $desig_id = $stmt->fetchColumn();
            if (!$desig_id) {
                $stmt = $pdo->prepare("INSERT INTO designations (department_id, name) VALUES (?, 'General Manager')");
                $stmt->execute([$dept_id]);
                $desig_id = $pdo->lastInsertId();
            }
            $target_desig = ['desig_id' => $desig_id, 'dept_id' => $dept_id];
        }

        $desig_id = $target_desig['desig_id'];
        $dept_id = $target_desig['dept_id'];

        echo "  Assigning to Dept: " . ($target_desig['dept_name'] ?? 'Admin') . " | Desig: " . ($target_desig['desig_name'] ?? 'Gen Mgr') . "\n";

        // 2. Get any shift (General)
        $stmt = $pdo->prepare("SELECT id FROM shifts WHERE company_id = ? LIMIT 1");
        $stmt->execute([$cid]);
        $shift_id = $stmt->fetchColumn();

        if (!$shift_id) {
            // Create default shift if none
            $stmt = $pdo->prepare("INSERT INTO shifts (company_id, name, start_time, end_time) VALUES (?, 'General Shift', '09:00:00', '18:00:00')");
            $stmt->execute([$cid]);
            $shift_id = $pdo->lastInsertId();
        }

        // 4. Create User: [company_slug]_manager
        $slug = strtolower(preg_replace('/[^a-z0-9]/', '', $cname));
        $username = $slug . '_manager';
        $email = $username . '@' . $slug . '.com';

        // Check if exists
        $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ?");
        $stmt->execute([$username]);
        if ($stmt->fetch()) {
            echo "  User $username already exists. Skipping.\n";
            continue;
        }

        $password = password_hash('Staff12@', PASSWORD_DEFAULT);
        $role_id = 3; // Manager Role

        $stmt = $pdo->prepare("INSERT INTO users (company_id, role_id, username, email, password, status) VALUES (?, ?, ?, ?, ?, 'active')");
        $stmt->execute([$cid, $role_id, $username, $email, $password]);
        $uid = $pdo->lastInsertId();
        echo "  Created User: $username (Pass: Staff12@)\n";

        // 5. Create Employee Record
        $emp_code = "MGR-" . $cid . "-" . $uid;
        $fname = "General";
        $lname = "Manager";
        $phone = "9" . rand(100000000, 999999999);
        $address = $company['address'] ?? "Head Office";
        $dob = "1980-01-01";
        $doj = date('Y-m-d');

        $stmt = $pdo->prepare("INSERT INTO employees (user_id, employee_code, first_name, last_name, contact, address, department_id, designation_id, shift_id, date_of_joining, dob, gender, status, salary) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'male', 'active', 150000)");
        $stmt->execute([$uid, $emp_code, $fname, $lname, $phone, $address, $dept_id, $desig_id, $shift_id, $doj, $dob]);

        echo "  Created Employee Profile for $username.\n";
    }

    $pdo->commit();
    echo "SUCCESS: Managers seeded.\n";

} catch (Exception $e) {
    if (isset($pdo) && $pdo->inTransaction())
        $pdo->rollBack();
    echo "ERROR: " . $e->getMessage() . "\n";
}
?>