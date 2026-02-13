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
    echo "Seeding Payroll for Company ID: $cid (Navbharat Construct)...\n";

    // 1. Fetch Employees
    $stmt = $pdo->prepare("
        SELECT e.id, e.first_name, e.last_name, e.salary, e.user_id 
        FROM employees e 
        JOIN departments d ON e.department_id = d.id 
        WHERE d.company_id = ? AND e.status = 'active'
    ");
    $stmt->execute([$cid]);
    $employees = $stmt->fetchAll();

    if (count($employees) == 0)
        die("No employees found.\n");

    // Default Template ID
    $stmt = $pdo->query("SELECT id FROM payslip_templates WHERE company_id IS NULL OR company_id = $cid ORDER BY id ASC LIMIT 1");
    $template_id = $stmt->fetchColumn() ?: 1;

    // 2. Generate periods for last 1 month
    $periods = [];
    $periods[] = date('Y-m', strtotime('first day of last month'));

    echo "Generating payslips for periods: " . implode(', ', $periods) . "\n";

    $updated_salary_count = 0;
    $payslip_count = 0;

    $pdo->beginTransaction();

    foreach ($employees as $emp) {
        $eid = $emp['id'];
        $salary = $emp['salary'];

        // Update Salary if missing
        if ($salary <= 0) {
            $salary = rand(30000, 120000);
            $upd = $pdo->prepare("UPDATE employees SET salary = ? WHERE id = ?");
            $upd->execute([$salary, $eid]);
            $updated_salary_count++;
        }

        foreach ($periods as $period) {
            // Skip if payslip already exists
            $chk = $pdo->prepare("SELECT id FROM payslips WHERE employee_id = ? AND period = ?");
            $chk->execute([$eid, $period]);
            if ($chk->fetch())
                continue;

            // Calculate Components
            $basic = round($salary * 0.50, 2);
            $hra = round($salary * 0.20, 2);
            $special = round($salary * 0.30, 2);
            $diff = $salary - ($basic + $hra + $special);
            $special += $diff;

            $gross = $salary;
            $pf = round($basic * 0.12, 2);
            $tax = round($gross * 0.05, 2);
            $net = $gross - $pf - $tax;

            $earnings = [
                ['name' => 'Basic Salary', 'amount' => $basic],
                ['name' => 'House Rent Allowance', 'amount' => $hra],
                ['name' => 'Special Allowance', 'amount' => $special]
            ];
            $deductions = [
                ['name' => 'Provident Fund', 'amount' => $pf],
                ['name' => 'Income Tax', 'amount' => $tax]
            ];

            $ins = $pdo->prepare("
                INSERT INTO payslips 
                (company_id, employee_id, period, currency, earnings_json, deductions_json, gross_salary, net_salary, template_id, status, generated_by, generated_at) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
            ");
            $ins->execute([
                $cid,
                $eid,
                $period,
                'INR',
                json_encode($earnings),
                json_encode($deductions),
                $gross,
                $net,
                $template_id,
                'generated',
                1
            ]);
            $payslip_count++;
        }
    }

    $pdo->commit();
    echo "SUCCESS:\n";
    echo "  Updated Salary for $updated_salary_count employees.\n";
    echo "  Generated $payslip_count payslips across " . count($periods) . " months.\n";

} catch (Exception $e) {
    if (isset($pdo) && $pdo->inTransaction())
        $pdo->rollBack();
    echo "ERROR: " . $e->getMessage() . "\n";
}
?>