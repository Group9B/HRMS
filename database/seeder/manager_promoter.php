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

    // 1. Find employees with 'Manager' in designation but User Role is NOT 3 (Manager) nor 2 (Owner)
    // We assume Role 3 is 'Manager'
    $sql = "
        SELECT 
            u.id as user_id, 
            u.username, 
            u.role_id, 
            e.first_name, 
            e.last_name, 
            d.name as desig_name,
            c.name as company_name
        FROM employees e
        JOIN users u ON e.user_id = u.id
        JOIN designations d ON e.designation_id = d.id
        JOIN companies c ON u.company_id = c.id
        WHERE 
            d.name LIKE '%Manager%' 
            AND u.role_id != 3 
            AND u.role_id != 2
    ";

    $stmt = $pdo->query($sql);
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (count($results) == 0) {
        echo "No role discrepancies found. All 'Managers' have correct role.\n";
        exit;
    }

    echo "Found " . count($results) . " employees to promote to Manager Role (ID 3):\n";

    $pdo->beginTransaction();

    $count = 0;
    foreach ($results as $r) {
        echo " Promoting [{$r['company_name']}] {$r['username']} ({$r['desig_name']}) from Role {$r['role_id']} -> 3\n";

        $upd = $pdo->prepare("UPDATE users SET role_id = 3 WHERE id = ?");
        $upd->execute([$r['user_id']]);
        $count++;
    }

    $pdo->commit();
    echo "SUCCESS: Promoted $count users to Manager Role.\n";

} catch (Exception $e) {
    if (isset($pdo) && $pdo->inTransaction())
        $pdo->rollBack();
    echo "ERROR: " . $e->getMessage() . "\n";
}
?>