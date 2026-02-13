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

    // Fetch first 3 companies
    $stmt = $pdo->query("SELECT id, name FROM companies ORDER BY id ASC LIMIT 3");
    $companies = $stmt->fetchAll();

    $total_converted = 0;

    $pdo->beginTransaction();

    foreach ($companies as $company) {
        $cid = $company['id'];
        $cname = $company['name'];
        echo "\n--- $cname (ID: $cid) ---\n";

        // Find ALL HR users (role_id = 4) in this company
        $stmt = $pdo->prepare("
            SELECT u.id as user_id, u.username, u.email, e.id as emp_id, e.first_name, e.last_name
            FROM users u
            LEFT JOIN employees e ON e.user_id = u.id
            WHERE u.company_id = ? AND u.role_id = 4
            ORDER BY u.id ASC
        ");
        $stmt->execute([$cid]);
        $hrs = $stmt->fetchAll();

        echo "  Found " . count($hrs) . " HR user(s).\n";

        if (count($hrs) <= 1) {
            echo "  Only 1 or 0 HRs — skipping.\n";
            continue;
        }

        // Keep the first HR, convert the rest
        $kept = array_shift($hrs);
        echo "  Keeping HR: {$kept['email']} (User ID: {$kept['user_id']})\n";

        // Fetch a non-HR designation for this company to assign
        $stmt = $pdo->prepare("
            SELECT d.id as desig_id, d.name as desig_name, dept.id as dept_id, dept.name as dept_name
            FROM designations d
            JOIN departments dept ON d.department_id = dept.id
            WHERE dept.company_id = ?
              AND d.name NOT LIKE '%HR%'
              AND d.name NOT LIKE '%Human Resource%'
            LIMIT 10
        ");
        $stmt->execute([$cid]);
        $available_desigs = $stmt->fetchAll();

        if (empty($available_desigs)) {
            echo "  WARNING: No non-HR designations found. Skipping conversion for $cname.\n";
            continue;
        }

        foreach ($hrs as $hr) {
            // Pick a random non-HR designation
            $pick = $available_desigs[array_rand($available_desigs)];

            // A. Update user role to Employee (5)
            $upd = $pdo->prepare("UPDATE users SET role_id = 5 WHERE id = ?");
            $upd->execute([$hr['user_id']]);

            // B. Update employee record (department + designation) if exists
            if ($hr['emp_id']) {
                $upd2 = $pdo->prepare("UPDATE employees SET department_id = ?, designation_id = ? WHERE id = ?");
                $upd2->execute([$pick['dept_id'], $pick['desig_id'], $hr['emp_id']]);
            }

            echo "  Converted: {$hr['email']} -> Employee | Dept: {$pick['dept_name']} | Desig: {$pick['desig_name']}\n";
            $total_converted++;
        }
    }

    $pdo->commit();
    echo "\nSUCCESS: Converted $total_converted extra HR(s) to Employee role.\n";

} catch (Exception $e) {
    if (isset($pdo) && $pdo->inTransaction())
        $pdo->rollBack();
    echo "ERROR: " . $e->getMessage() . "\n";
}
?>