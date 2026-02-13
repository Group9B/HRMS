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

    // DEBUG: List all roles
    echo "--- Current Roles in DB ---\n";
    $all_roles = $pdo->query("SELECT * FROM roles")->fetchAll();
    foreach ($all_roles as $r) {
        echo "ID: " . $r['id'] . " | Name: " . $r['name'] . "\n";
    }
    echo "---------------------------\n";

    // 1. Find Auditor Role IDs
    // The user sees 'Auditor' in the UI. It might be ID 6, 7, etc.
    // We will look for ANY role with 'Auditor' in name.
    $stmt = $pdo->prepare("SELECT id, name FROM roles WHERE name LIKE ?");
    $stmt->execute(['%Auditor%']);
    $auditor_roles = $stmt->fetchAll();

    $role_ids_to_convert = [];
    foreach ($auditor_roles as $ar) {
        $role_ids_to_convert[] = $ar['id'];
        echo "Found Target Role: " . $ar['name'] . " (ID: " . $ar['id'] . ")\n";
    }

    // ALSO check for ID 6 and 7 explicitly if they exist and are not superadmin/companyadmin
    // just in case the name is slightly different but logic implies it.
    // Actually, safer to stick to name matching or ask user.
    // User said "there are still many records with role auditor".
    // If name matches 'Auditor', we convert.

    if (empty($role_ids_to_convert)) {
        die("No roles matching 'Auditor' found. Please check role names above.\n");
    }

    // 2. Find Users
    $inKey = str_repeat('?,', count($role_ids_to_convert) - 1) . '?';
    $stmt = $pdo->prepare("SELECT id, username, role_id FROM users WHERE role_id IN ($inKey)");
    $stmt->execute($role_ids_to_convert);
    $auditors = $stmt->fetchAll();

    echo "Found " . count($auditors) . " users to convert.\n";

    if (count($auditors) == 0) {
        echo "No users found with Auditor role(s).\n";
        // Debug: Show a few users and their roles
        echo "Sampling 5 users:\n";
        $sample = $pdo->query("SELECT u.username, r.name as role_name FROM users u JOIN roles r ON u.role_id = r.id LIMIT 5")->fetchAll();
        foreach ($sample as $s)
            echo $s['username'] . " -> " . $s['role_name'] . "\n";
        die();
    }

    // 3. Targets
    $targets = [3, 4, 5]; // Manager, HR, Employee
    // Verify existence
    $valid_targets = [];
    foreach ($targets as $tid) {
        $c = $pdo->query("SELECT id FROM roles WHERE id=$tid")->fetch();
        if ($c)
            $valid_targets[] = $tid;
    }
    if (empty($valid_targets))
        die("Target roles 3,4,5 not found.\n");

    $pdo->beginTransaction();

    foreach ($auditors as $user) {
        $uid = $user['id'];
        $uname = $user['username'];
        $current_rid = $user['role_id'];

        $new_role = $valid_targets[array_rand($valid_targets)];

        $upd = $pdo->prepare("UPDATE users SET role_id = ? WHERE id = ?");
        $upd->execute([$new_role, $uid]);

        echo "Converted $uname (Role $current_rid) -> Role $new_role\n";
    }

    $pdo->commit();
    echo "SUCCESS: Converted all found auditors.\n";

} catch (Exception $e) {
    if (isset($pdo) && $pdo->inTransaction())
        $pdo->rollBack();
    echo "ERROR: " . $e->getMessage() . "\n";
}
?>