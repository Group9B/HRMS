<?php
// Force error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/_common/seeder_identity.php';

try {
    seeder_log("Detailed Employees Seeder started.");

    // Roles are defined in _common/seeder_runtime.php constants
    // ROLE_HR = 3, ROLE_EMPLOYEE = 4, ROLE_MANAGER = 6

    // 2. Fetch First 3 Companies
    $stmt = $pdo->query("SELECT * FROM companies ORDER BY id ASC LIMIT 3");
    $companies = $stmt->fetchAll();

    if (count($companies) == 0) {
        die("No companies found to seed.\n");
    }

    // Data Arrays (Indian Context)
    $first_names = ['Aarav', 'Vihaan', 'Aditya', 'Sai', 'Arjun', 'Rohan', 'Ishaan', 'Rahul', 'Amit', 'Suresh', 'Ramesh', 'Priya', 'Ananya', 'Diya', 'Sana', 'Kavita', 'Meera', 'Neha', 'Pooja', 'Sneha', 'Vikram', 'Sanjay', 'Manish', 'Deepak', 'Anil', 'Sunil', 'Rajesh', 'Vivek', 'Ajay', 'Vijay', 'Karan', 'Simran', 'Riya', 'Nisha', 'Isha', 'Swati', 'Preeti', 'Jyoti', 'Varun', 'Tarun', 'Nitin', 'Alok', 'Ashish', 'Gaurav', 'Harish', 'Jatin', 'Kapil', 'Lalit', 'Manoj'];
    $last_names = ['Sharma', 'Verma', 'Gupta', 'Patel', 'Singh', 'Kumar', 'Reddy', 'Nair', 'Iyer', 'Mishra', 'Jha', 'Yadav', 'Das', 'Roy', 'Chopra', 'Malhotra', 'Kapoor', 'Jain', 'Agarwal', 'Saxena', 'Bhatia', 'Mehta', 'Shah', 'Desai', 'Joshi', 'Kulkarni', 'Rao', 'More', 'Patil', 'Pawar', 'Shinde', 'Gaikwad', 'Tiwari', 'Pandey', 'Dubey', 'Tripathi', 'Chaudhary', 'Thakur', 'Khan', 'Pathan', 'Shaikh', 'Ansari', 'Siddiqui'];

    $depts_def = [
        'Human Resources' => ['HR Manager', 'HR Executive', 'Recruiter'],
        'Information Technology' => ['IT Manager', 'Senior Developer', 'Junior Developer', 'System Admin', 'QA Engineer'],
        'Sales & Marketing' => ['Sales Manager', 'Marketing Executive', 'Sales Representative'],
        'Finance' => ['Finance Manager', 'Accountant', 'Payroll Specialist'],
        'Operations' => ['Operations Manager', 'Supervisor', 'Operations Executive']
    ];

    $shifts_def = [
        ['name' => 'General Shift', 'start' => '09:00:00', 'end' => '18:00:00'],
        ['name' => 'Morning Shift', 'start' => '06:00:00', 'end' => '14:00:00'],
        ['name' => 'Night Shift', 'start' => '22:00:00', 'end' => '06:00:00']
    ];

    foreach ($companies as $company) {
        $cid = $company['id'];
        $cname = $company['name'];
        echo "\nProcessing Company: $cname (ID: $cid)...\n";

        // A. Create Departments & Designations
        $dept_ids = []; // name => id
        $desig_ids = []; // dept_name => [desig_name => id]

        foreach ($depts_def as $dname => $desigs) {
            // Check/Create Dept
            $stmt = $pdo->prepare("SELECT id FROM departments WHERE company_id = ? AND name = ?");
            $stmt->execute([$cid, $dname]);
            $did = $stmt->fetchColumn();
            if (!$did) {
                $stmt = $pdo->prepare("INSERT INTO departments (company_id, name) VALUES (?, ?)");
                $stmt->execute([$cid, $dname]);
                $did = $pdo->lastInsertId();
            }
            $dept_ids[$dname] = $did;

            // Check/Create Designations
            foreach ($desigs as $desig_name) {
                $stmt = $pdo->prepare("SELECT id FROM designations WHERE department_id = ? AND name = ?");
                $stmt->execute([$did, $desig_name]);
                $des_id = $stmt->fetchColumn();
                if (!$des_id) {
                    $stmt = $pdo->prepare("INSERT INTO designations (department_id, name) VALUES (?, ?)");
                    $stmt->execute([$did, $desig_name]);
                    $des_id = $pdo->lastInsertId();
                }
                $desig_ids[$dname][$desig_name] = $des_id;
            }
        }

        // B. Create Shifts
        $shift_ids = [];
        foreach ($shifts_def as $s) {
            $stmt = $pdo->prepare("SELECT id FROM shifts WHERE company_id = ? AND name = ?");
            $stmt->execute([$cid, $s['name']]);
            $sid = $stmt->fetchColumn();
            if (!$sid) {
                $stmt = $pdo->prepare("INSERT INTO shifts (company_id, name, start_time, end_time) VALUES (?, ?, ?, ?)");
                $stmt->execute([$cid, $s['name'], $s['start'], $s['end']]);
                $sid = $pdo->lastInsertId();
            }
            $shift_ids[] = $sid;
        }

        // C. Seed Employees
        $count = 0;
        $target = 55; // > 50

        // 1. Increase HR headcount with 3 fixed HR users
        create_employee($pdo, $cid, 'Human Resources', 'HR Manager', ROLE_HR, $dept_ids, $desig_ids, $shift_ids, $first_names, $last_names, $company, 'hr');
        $count++;
        create_employee($pdo, $cid, 'Human Resources', 'HR Executive', ROLE_HR, $dept_ids, $desig_ids, $shift_ids, $first_names, $last_names, $company, 'hr');
        $count++;
        create_employee($pdo, $cid, 'Human Resources', 'Recruiter', ROLE_HR, $dept_ids, $desig_ids, $shift_ids, $first_names, $last_names, $company, 'hr');
        $count++;

        // 2. Operations Manager (Fixed)
        create_employee($pdo, $cid, 'Operations', 'Operations Manager', ROLE_MANAGER, $dept_ids, $desig_ids, $shift_ids, $first_names, $last_names, $company, 'manager');
        $count++;

        // 3. Loop rest
        while ($count < $target) {
            // Pick rand dept
            $d_keys = array_keys($depts_def);
            $rand_dept = $d_keys[array_rand($d_keys)];

            // Pick rand desig in that dept
            $des_keys = $depts_def[$rand_dept];
            // Filter out Managers for regular staff if desired, but user said "different roles".
            // Let's just pick random designation. 
            // If designation contains 'Manager', assign Role 3? Else Role 5.
            $rand_desig = $des_keys[array_rand($des_keys)];

            $role = ROLE_EMPLOYEE; // Employee default
            if ($rand_dept === 'Human Resources') {
                $role = ROLE_HR;
            }
            if (strpos($rand_desig, 'Manager') !== false) {
                $role = ROLE_MANAGER;
            }

            create_employee($pdo, $cid, $rand_dept, $rand_desig, $role, $dept_ids, $desig_ids, $shift_ids, $first_names, $last_names, $company);
            $count++;
            if ($count % 10 == 0)
                echo "  Created $count employees...\n";
        }
        echo "  Done. Total $count employees.\n";
    }

} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}

function create_employee($pdo, $cid, $dept_name, $desig_name, $role_id, $dept_ids, $desig_ids, $shift_ids, $fnames, $lnames, $company, $user_prefix = null)
{
    $fname = $fnames[array_rand($fnames)];
    $lname = $lnames[array_rand($lnames)];
    $dob = sprintf('%04d-%02d-%02d', rand(1980, 2000), rand(1, 12), rand(1, 28));

    $roleTag = 'emp';
    if ($role_id === ROLE_HR) {
        $roleTag = 'hr';
    }
    if ($role_id === ROLE_MANAGER) {
        $roleTag = 'mgr';
    }
    if ($user_prefix === 'hr') {
        $roleTag = 'hr';
    }
    if ($user_prefix === 'manager' || $user_prefix === 'mgr') {
        $roleTag = 'mgr';
    }

    $creds = generate_credentials($pdo, $company['name'], $roleTag, $fname, $lname, $dob);
    $username = $creds['username'];
    $email = $creds['email'];

    $password = password_hash('Staff12@', PASSWORD_DEFAULT);
    $status = 'active';

    // Insert User
    $stmt = $pdo->prepare("INSERT INTO users (company_id, role_id, username, email, password, status) VALUES (?, ?, ?, ?, ?, ?)");
    try {
        $stmt->execute([$cid, $role_id, $username, $email, $password, $status]);
        $uid = $pdo->lastInsertId();
    } catch (PDOException $e) {
        // If collision happens between generate/check and insert, skip this row.
        return;
    }

    // Insert Employee
    $dept_id = $dept_ids[$dept_name];
    $desig_id = $desig_ids[$dept_name][$desig_name];
    $shift_id = $shift_ids[array_rand($shift_ids)];

    // Employee Code: EMP-{CID}-{UID}
    $emp_code = "EMP-" . $cid . "-" . $uid;
    $phone = "9" . rand(100000000, 999999999);
    $doj = sprintf('%04d-%02d-%02d', rand(2022, 2025), rand(1, 12), rand(1, 28));
    $salary = rand(30000, 150000);
    $gender = (rand(0, 1) == 0) ? 'male' : 'female';
    $address = $company['address'] ?? "India";

    $stmt = $pdo->prepare("INSERT INTO employees (user_id, employee_code, first_name, last_name, contact, address, department_id, designation_id, shift_id, date_of_joining, dob, gender, status, salary) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute([$uid, $emp_code, $fname, $lname, $phone, $address, $dept_id, $desig_id, $shift_id, $doj, $dob, $gender, $status, $salary]);
}
?>