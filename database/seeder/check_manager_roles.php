<?php
// Force error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

$host = 'localhost';
$db = 'original_template';
$user = 'root';
$pass = '';
$dsn = "mysql:host=$host;dbname=$db;charset=utf8mb4";
$pdo = new PDO($dsn, $user, $pass);

// Find employees with 'Manager' in designation but User Role is NOT 3 (Manager) nor 2 (Owner)
$sql = "
    SELECT 
        u.id as user_id, 
        u.username, 
        u.role_id, 
        e.first_name, 
        e.last_name, 
        d.name as desig_name
    FROM employees e
    JOIN users u ON e.user_id = u.id
    JOIN designations d ON e.designation_id = d.id
    WHERE 
        d.name LIKE '%Manager%' 
        AND u.role_id != 3 
        AND u.role_id != 2
";

$stmt = $pdo->query($sql);
$results = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo "Found " . count($results) . " employees with 'Manager' title but not Manager Role:\n";
foreach ($results as $r) {
    echo "User: {$r['username']} (Role {$r['role_id']}) - Title: {$r['desig_name']}\n";
}
?>