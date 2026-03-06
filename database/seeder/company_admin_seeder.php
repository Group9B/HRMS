<?php
// Force error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/_common/seeder_identity.php';

try {
    seeder_log("Company Admin Seeder started.");

    // Fetch all companies with Address
    $stmt = $pdo->query("SELECT id, name, email, phone, address FROM companies");
    $companies = $stmt->fetchAll();

    seeder_log("Found " . count($companies) . " companies. Processing...");

    $pdo->beginTransaction();
    $created_count = 0;

    foreach ($companies as $company) {
        $company_id = $company['id'];
        $company_name = $company['name'];
        $phone = $company['phone'];
        $address = $company['address'];

        // Skip if company already has a Company Owner
        if (company_has_role($pdo, $company_id, ROLE_COMPANY_OWNER)) {
            seeder_log("Company $company_name (#$company_id) already has an owner. Skipping.");
            continue;
        }

        // Build owner identity data first so email can include name + DOB
        $parts = preg_split('/\s+/', trim($company_name));
        $first_name = $parts[0] ?? 'Owner';
        $last_name = $parts[1] ?? 'Admin';

        // Generate realistic Indian DOB (Owners usually 30-50 years old)
        $year = rand(1975, 1995);
        $month = rand(1, 12);
        $day = rand(1, 28);
        $dob = sprintf('%04d-%02d-%02d', $year, $month, $day);

        // Generate unique credentials
        $creds = generate_credentials($pdo, $company_name, 'owner', $first_name, $last_name, $dob);
        $username = $creds['username'];
        $email_to_use = $creds['email'];

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
        $role_id = ROLE_COMPANY_OWNER;

        $stmt_user = $pdo->prepare("INSERT INTO users (company_id, role_id, username, email, password, status) VALUES (?, ?, ?, ?, ?, 'active')");
        $stmt_user->execute([$company_id, $role_id, $username, $email_to_use, $password_hash]);
        $user_id = $pdo->lastInsertId();

        // 6. Create Employee with FULL Indian Details
        $emp_code = "ADM-" . str_pad($company_id, 3, '0', STR_PAD_LEFT);

        $gender = (rand(0, 1) == 0) ? 'male' : 'female';

        $stmt_emp = $pdo->prepare("INSERT INTO employees (user_id, employee_code, first_name, last_name, contact, address, department_id, designation_id, shift_id, date_of_joining, dob, gender, status, salary) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, CURDATE(), ?, ?, 'active', 0)");

        try {
            $stmt_emp->execute([$user_id, $emp_code, $first_name, $last_name, $phone, $address, $dept_id, $des_id, $shift_id, $dob, $gender]);
            $created_count++;
        } catch (PDOException $e) {
            seeder_log("Failed to create employee for $username: " . $e->getMessage());
        }
    }

    $pdo->commit();
    seeder_log("SUCCESS: Created $created_count admins for " . count($companies) . " companies.");

} catch (Exception $e) {
    if (isset($pdo) && $pdo->inTransaction())
        $pdo->rollBack();
    seeder_log("ERROR: " . $e->getMessage());
}
?>