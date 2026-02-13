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
    $pdo = new PDO($dsn, $user, $pass, $options);

    // 1. Fetch First 3 Companies
    $stmt = $pdo->query("SELECT id, name FROM companies ORDER BY id ASC LIMIT 3");
    $companies = $stmt->fetchAll();

    echo "=== DEMO ACCOUNTS (Password: Staff12@) ===\n\n";

    foreach ($companies as $company) {
        $cid = $company['id'];
        $cname = $company['name'];
        echo "COMPANY: $cname (ID: $cid)\n";
        echo str_repeat('-', 40) . "\n";

        // Define roles we want to see (Role ID => Role Name)
        // Adjust IDs based on your DB: 2=Owner, 3=Manager, 4=HR, 5=Employee
        // Also checks for 'Auditor' if we had any

        $target_roles = [
            2 => 'Company Owner',
            3 => 'Manager',
            4 => 'HR',
            5 => 'Employee'
        ];

        foreach ($target_roles as $rid => $rname) {
            // Fetch one user for this role in this company
            $stmt = $pdo->prepare("
                SELECT u.username, u.email 
                FROM users u 
                WHERE u.company_id = ? AND u.role_id = ? 
                LIMIT 1
            ");
            $stmt->execute([$cid, $rid]);
            $user = $stmt->fetch();

            if ($user) {
                echo str_pad($rname, 15) . ": " . $user['email'] . "\n";
            } else {
                echo str_pad($rname, 15) . ": [Not Found]\n";
            }
        }
        echo "\n";
    }

} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}
?>