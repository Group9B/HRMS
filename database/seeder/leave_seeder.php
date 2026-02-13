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

    // Target Company: Navbharat Construct (ID 1)
    $cid = 1;
    echo "Seeding Leaves for Company ID: $cid (Navbharat Construct)...\n";

    // 1. Ensure Leave Policies
    $policies = [
        ['type' => 'Sick Leave', 'days' => 10, 'accruable' => 0],
        ['type' => 'Casual Leave', 'days' => 12, 'accruable' => 0],
        ['type' => 'Earned Leave', 'days' => 15, 'accruable' => 1],
        ['type' => 'Maternity Leave', 'days' => 90, 'accruable' => 0],
    ];

    $policy_map = []; // type => id

    foreach ($policies as $p) {
        $stmt = $pdo->prepare("SELECT id FROM leave_policies WHERE company_id = ? AND leave_type = ?");
        $stmt->execute([$cid, $p['type']]);
        $lid = $stmt->fetchColumn();

        if (!$lid) {
            $stmt = $pdo->prepare("INSERT INTO leave_policies (company_id, leave_type, days_per_year, is_accruable) VALUES (?, ?, ?, ?)");
            $stmt->execute([$cid, $p['type'], $p['days'], $p['accruable']]);
            $lid = $pdo->lastInsertId();
            echo "  Created Policy: {$p['type']}\n";
        }
        $policy_map[$p['type']] = ['id' => $lid, 'days' => $p['days']];
    }

    // 2. Fetch Employees
    $stmt = $pdo->prepare("
        SELECT e.id, e.first_name, e.last_name 
        FROM employees e 
        JOIN departments d ON e.department_id = d.id 
        WHERE d.company_id = ? AND e.status = 'active'
    ");
    $stmt->execute([$cid]);
    $employees = $stmt->fetchAll();

    if (count($employees) == 0)
        die("No employees found.\n");

    // 3. Initialize Leave Balances
    echo "\nInitializing Balances...\n";
    $year = date('Y');

    foreach ($employees as $emp) {
        foreach ($policy_map as $type => $data) {
            $lid = $data['id'];
            $days = $data['days'];

            $chk = $pdo->prepare("SELECT id FROM leave_balances WHERE employee_id = ? AND leave_policy_id = ? AND year = ?");
            $chk->execute([$emp['id'], $lid, $year]);

            if (!$chk->fetch()) {
                $ins = $pdo->prepare("INSERT INTO leave_balances (employee_id, leave_policy_id, year, accrued_days, used_days) VALUES (?, ?, ?, ?, 0)");
                $ins->execute([$emp['id'], $lid, $year, $days]);
            }
        }
    }

    // 4. Create Leave Requests
    echo "\nCreating Leave Requests...\n";
    $pdo->beginTransaction();

    $req_count = 0;
    $target_req = 60;

    $statuses = ['pending', 'approved', 'rejected', 'cancelled'];
    $reasons = ['Not feeling well', 'Family function', 'Personal work', 'Vacation', 'Urgent medical issue'];

    for ($i = 0; $i < $target_req; $i++) {
        $emp = $employees[array_rand($employees)];

        // Random Policy
        $p_keys = array_keys($policy_map);
        $type = $p_keys[array_rand($p_keys)];
        // Skip maternity if male (simple check: if name contains regex? No, data has gender col? Yes)
        // Schema lookup for emp gender would be better but let's just allow for now or random success.

        $start = date('Y-m-d', strtotime("-" . rand(0, 60) . " days"));
        $duration = rand(1, 4);
        $end = date('Y-m-d', strtotime("$start + " . ($duration - 1) . " days"));

        $reason = $reasons[array_rand($reasons)];
        $status = $statuses[array_rand($statuses)];
        $approved_by = ($status == 'approved' || $status == 'rejected') ? 1 : null; // Owner ID 1 (Navbharat Owner)

        $ins = $pdo->prepare("INSERT INTO leaves (employee_id, leave_type, start_date, end_date, reason, status, approved_by) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $ins->execute([$emp['id'], $type, $start, $end, $reason, $status, $approved_by]);

        // Update Balance if Approved
        if ($status == 'approved') {
            $lid = $policy_map[$type]['id'];
            $upd = $pdo->prepare("UPDATE leave_balances SET used_days = used_days + ? WHERE employee_id = ? AND leave_policy_id = ? AND year = ?");
            $upd->execute([$duration, $emp['id'], $lid, $year]);
        }
        $req_count++;
    }

    $pdo->commit();
    echo "SUCCESS: Created $req_count leave requests & updated balances.\n";

} catch (Exception $e) {
    if (isset($pdo) && $pdo->inTransaction())
        $pdo->rollBack();
    echo "ERROR: " . $e->getMessage() . "\n";
}
?>