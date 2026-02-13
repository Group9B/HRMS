<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Database Configuration
$host = 'localhost';
$db = 'original_template';
$user = 'root';
$pass = ''; // Default XAMPP password
$dsn = "mysql:host=$host;dbname=$db;charset=utf8mb4";
$options = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES => false,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);

    // Fetch First 3 Companies
    $stmt = $pdo->query("SELECT id, name FROM companies ORDER BY id ASC LIMIT 3");
    $companies = $stmt->fetchAll();

    header('Content-Type: text/plain');

    foreach ($companies as $company) {
        $cid = $company['id'];
        $cname = $company['name'];
        echo "COMPANY: $cname (ID: $cid)\n";
        echo str_repeat('-', 40) . "\n";

        // Define roles we want to see (Role ID => Role Name)
        $target_roles = [
            2 => 'Company Owner',
            3 => 'Manager',
            4 => 'HR',
            5 => 'Employee'
        ];

        foreach ($target_roles as $rid => $rname) {
            $stmt = $pdo->prepare("SELECT email FROM users WHERE company_id = ? AND role_id = ? LIMIT 1");
            $stmt->execute([$cid, $rid]);
            $email = $stmt->fetchColumn();

            echo str_pad($rname, 15) . ": " . ($email ?: '[Not Found]') . "\n";
        }
        echo "\n";
    }

} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage();
}
?>