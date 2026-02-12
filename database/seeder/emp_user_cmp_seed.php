<?php
$host = getenv('DB_HOST') ?: '127.0.0.1';
$db   = getenv('DB_NAME') ?: 'original_template';
$user = getenv('DB_USER') ?: 'root';
$pass = getenv('DB_PASS') ?: '';
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);

    // Realistic Indian Data
    $first_names = ['Arjun', 'Aditi', 'Suresh', 'Priya', 'Rohan', 'Anjali', 'Vikram', 'Deepika', 'Karan', 'Sneha', 'Amit', 'Meera', 'Rahul', 'Ishani', 'Sanjay', 'Kavita', 'Yash', 'Pooja', 'Abhishek', 'Riya', 'Manoj', 'Shweta', 'Rajesh', 'Nehal', 'Vijay', 'Tanvi', 'Varun', 'Aishwarya', 'Sunil', 'Bhavna'];
    $last_names = ['Sharma', 'Verma', 'Patel', 'Mehta', 'Iyer', 'Nair', 'Gupta', 'Singh', 'Desai', 'Joshi', 'Kulkarni', 'Reddy', 'Chauhan', 'Pandey', 'Malhotra', 'Agarwal', 'Shah', 'Trivedi', 'Saxena', 'Bose'];
    $cities = ['Ahmedabad, Gujarat', 'Mumbai, Maharashtra', 'Bangalore, Karnataka', 'Pune, Maharashtra', 'Delhi, NCR', 'Hyderabad, Telangana', 'Chennai, Tamil Nadu', 'Surat, Gujarat'];
    $companies = ['Tata Consultancy Services', 'Reliance Industries', 'Infosys Limited', 'HDFC Bank', 'Wipro Tech'];

    $pdo->beginTransaction();

    // 1. Insert Companies
    $company_ids = [];
    $stmt_comp = $pdo->prepare("INSERT INTO companies (name, address, email, phone, subscription_status) VALUES (?, ?, ?, ?, 'active')");
    foreach ($companies as $i => $name) {
        $stmt_comp->execute([
            $name, 
            "Corporate Hub, " . $cities[$i % count($cities)], 
            "hr@" . strtolower(str_replace(' ', '', $name)) . ".in", 
            "+91 22 4000 000" . ($i + 1)
        ]);
        $company_ids[] = $pdo->lastInsertId();
    }

    // 2. Insert Users & Employees
    $stmt_user = $pdo->prepare("INSERT INTO users (company_id, role_id, username, email, password, status) VALUES (?, ?, ?, ?, ?, 'active')");
    $stmt_emp = $pdo->prepare("INSERT INTO employees (user_id, employee_code, first_name, last_name, gender, address, status, salary, date_of_joining) VALUES (?, ?, ?, ?, ?, ?, 'active', ?, ?)");

    $password = password_hash('StaffSync@2026', PASSWORD_DEFAULT);

    for ($i = 0; $i < 55; $i++) {
        $fname = $first_names[array_rand($first_names)];
        $lname = $last_names[array_rand($last_names)];
        $c_id = $company_ids[$i % count($company_ids)];
        
        $username = strtolower($fname) . "." . strtolower($lname) . ($i + 100);
        $email = "$username@example.in";
        
        // No Admin (Role 1). Managers (Role 2) for every 10th entry, others are Employees (Role 3)
        $role_id = ($i % 10 == 0) ? 2 : 3;

        // Insert User
        $stmt_user->execute([$c_id, $role_id, $username, $email, $password]);
        $user_id = $pdo->lastInsertId();

        // Insert Employee
        $emp_code = "IND-" . (202600 + $i);
        $salary = rand(45000, 185000);
        $gender = ($i % 2 == 0) ? 'male' : 'female';
        $city = $cities[array_rand($cities)];
        $doj = date('Y-m-d', strtotime("-" . rand(30, 730) . " days"));

        $stmt_emp->execute([$user_id, $emp_code, $fname, $lname, $gender, $city, $salary, $doj]);
    }

    $pdo->commit();
    echo "Successfully inserted 5 companies and 55 user/employee records into $db.\n";

} catch (PDOException $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    die("Database Error: " . $e->getMessage());
}