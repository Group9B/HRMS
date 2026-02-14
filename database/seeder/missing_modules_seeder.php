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
    echo "Seeding Missing Modules for Company ID: $cid (Navbharat Construct)...\n";

    // ==========================================
    // 1. SEED POLICIES
    // ==========================================
    echo "\n1. Seeding Policies...\n";
    $policies = [
        ['Code of Conduct', 'Guidelines outlining the social norms, rules, and responsibilities of...'],
        ['IT Usage Policy', 'Rules regarding the use of company computers, internet, and data...'],
        ['Remote Work Policy', 'Eligibility and expectations for employees working from home...'],
        ['Travel & Expense Policy', 'Procedures for booking travel and claiming reimbursements...'],
        ['Health & Safety', 'Workplace safety protocols and emergency procedures...']
    ];

    $policy_count = 0;
    foreach ($policies as $p) {
        $name = $p[0];
        $content = $p[1];

        $stmt = $pdo->prepare("SELECT id FROM policies WHERE company_id = ? AND policy_name = ?");
        $stmt->execute([$cid, $name]);
        if (!$stmt->fetch()) {
            $ins = $pdo->prepare("INSERT INTO policies (company_id, policy_name, content, created_at) VALUES (?, ?, ?, NOW())");
            $ins->execute([$cid, $name, $content]);
            $policy_count++;
        }
    }
    echo "   Added $policy_count policies.\n";

    // Fetch Active Employees for linking
    $stmt = $pdo->prepare("
        SELECT e.id, e.user_id 
        FROM employees e 
        JOIN departments d ON e.department_id = d.id 
        WHERE d.company_id = ? AND e.status = 'active'
    ");
    $stmt->execute([$cid]);
    $employees = $stmt->fetchAll();

    if (empty($employees)) {
        die("No employees found. Seed employees first.\n");
    }

    // ==========================================
    // 2. SEED PERFORMANCE REVIEWS
    // ==========================================
    echo "\n2. Seeding Performance Reviews...\n";
    $pdo->beginTransaction();
    $perf_count = 0;
    $period = date('Y-m', strtotime('last month'));

    // Find a manager/admin evaluator (User ID 30 - Devesh Shah from logs, or generic)
    // Let's use the first employee's user_id as a fallback evaluator if mostly self-eval not applicable
    $evaluator_id = 30; // Hardcoded valid user from logs (Devesh Shah) or 1 (Admin)

    foreach ($employees as $emp) {
        // Check duplicate
        $chk = $pdo->prepare("SELECT id FROM performance WHERE employee_id = ? AND period = ?");
        $chk->execute([$emp['id'], $period]);
        if (!$chk->fetch()) {
            $score = rand(65, 98);
            $remarks = ($score > 90) ? "Outstanding performance." : (($score > 80) ? "Met expectations." : "Needs improvement.");

            $ins = $pdo->prepare("
                INSERT INTO performance (employee_id, evaluator_id, approved_by, period, score, remarks, created_at) 
                VALUES (?, ?, ?, ?, ?, ?, NOW())
            ");
            $ins->execute([$emp['id'], $evaluator_id, $evaluator_id, $period, $score, $remarks]);
            $perf_count++;
        }
    }
    $pdo->commit();
    echo "   Added $perf_count performance reviews for $period.\n";

    // ==========================================
    // 3. SEED SUPPORT TICKETS
    // ==========================================
    echo "\n3. Seeding Support Tickets...\n";
    $pdo->beginTransaction();
    $ticket_count = 0;
    $subjects = [
        'Laptop overheating' => 'high',
        'Need access to Jira' => 'medium',
        'Payroll discrepancy in last month' => 'high',
        'Office chair broken' => 'low',
        'VPN connection issues' => 'high',
        'Request for new monitor' => 'medium'
    ];
    $statuses = ['open', 'in_progress', 'closed'];

    for ($i = 0; $i < 10; $i++) {
        $emp = $employees[array_rand($employees)];
        $subj_keys = array_keys($subjects);
        $subject = $subj_keys[array_rand($subj_keys)];
        $priority = $subjects[$subject];
        $status = $statuses[array_rand($statuses)];

        $ins = $pdo->prepare("
            INSERT INTO support_tickets (user_id, subject, message, priority, status, created_at) 
            VALUES (?, ?, ?, ?, ?, NOW())
        ");
        $ins->execute([$emp['user_id'], $subject, "I am facing issues with $subject. Please help.", $priority, $status]);
        $ticket_count++;
    }
    $pdo->commit();
    echo "   Added $ticket_count support tickets.\n";

    // ==========================================
    // 4. SEED FEEDBACK
    // ==========================================
    echo "\n4. Seeding Feedback...\n";
    $pdo->beginTransaction();
    $feedback_count = 0;
    $msgs = [
        'Great work environment!' => 'appreciation',
        'Coffee machine is broken often.' => 'complaint',
        'Can we have more team outages?' => 'suggestion',
        'The new policy is very helpful.' => 'feedback'
    ];

    for ($i = 0; $i < 8; $i++) {
        $emp = $employees[array_rand($employees)];
        $msg_keys = array_keys($msgs);
        $msg = $msg_keys[array_rand($msg_keys)];
        $type = $msgs[$msg];

        // submitted_by is usually another employee or self? "submitted_by" might mean the author.
        // Schema: employee_id (target?), submitted_by (author?)
        // Let's assume generic feedback system: submitted_by = author. employee_id = target (optional? or 0 for company?)
        // If it's company feedback, maybe employee_id is ignored or represents 'about whom'.
        // Let's set employee_id to author for 'about me' or just a dummy linking.

        $ins = $pdo->prepare("
            INSERT INTO feedback (employee_id, submitted_by, message, type, status, created_at) 
            VALUES (?, ?, ?, ?, 'pending', NOW())
        ");
        // Linking feedback to the employee themselves or a manager? 
        // Let's link employee_id to themselves for now (or a random peer)
        $target_emp = $employees[array_rand($employees)];

        $ins->execute([$target_emp['id'], $emp['user_id'], $msg, $type]);
        $feedback_count++;
    }
    $pdo->commit();
    echo "   Added $feedback_count feedback entries.\n";

    // ==========================================
    // 5. SEED ASSETS (Enhancement)
    // ==========================================
    echo "\n5. Seeding Additional Assets...\n";
    $pdo->beginTransaction();
    $asset_count = 0;

    // Laptop Category ID = 1 (from previous dump check)
    $cats = [
        1 => ['Dell Latitude', 'Laptop', 45000],
        2 => ['Dell Optiplex', 'Desktop', 35000],
        3 => ['Samsung 24"', 'Monitor', 12000]
    ];

    for ($i = 0; $i < 5; $i++) {
        $cat_id = array_rand($cats);
        $details = $cats[$cat_id];
        $tag = "AST-NEW-" . rand(1000, 9999);

        $ins = $pdo->prepare("
            INSERT INTO assets (company_id, category_id, asset_name, asset_tag, serial_number, purchase_date, purchase_cost, status, condition_status, created_at) 
            VALUES (?, ?, ?, ?, ?, NOW(), ?, 'Available', 'New', NOW())
        ");
        $ins->execute([$cid, $cat_id, $details[0], $tag, 'SN' . rand(10000, 99999), $details[2]]);
        $asset_count++;
    }
    $pdo->commit();
    echo "   Added $asset_count new assets.\n";

    echo "\nSUCCESS: Missing modules seeding complete.\n";

} catch (Exception $e) {
    if (isset($pdo) && $pdo->inTransaction())
        $pdo->rollBack();
    echo "ERROR: " . $e->getMessage() . "\n";
}
?>