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

    // 1. Fetch First 3 Companies
    $stmt = $pdo->query("SELECT id, name FROM companies ORDER BY id ASC LIMIT 3");
    $companies = $stmt->fetchAll();

    if (count($companies) == 0) {
        die("No companies found.\n");
    }

    // Team Names map to Departments
    $team_defs = [
        'Human Resources' => ['Recruitment Squad', 'Employee Relations', 'HR Ops'],
        'Information Technology' => ['Core Tech', 'DevOps Team', 'Frontend Ninjas', 'Backend Crew', 'Support Hawks'],
        'Sales & Marketing' => ['Sales Alphas', 'Marketing Gurus', 'Lead Gen Team', 'Brand Warriors'],
        'Finance' => ['Audit Team', 'Payroll Masters', 'Budget Control'],
        'Operations' => ['Logistics Unit', 'Quality Control', 'Supply Chain']
    ];

    $pdo->beginTransaction();

    foreach ($companies as $company) {
        $cid = $company['id'];
        $cname = $company['name'];
        echo "\nProcessing Company: $cname (ID: $cid)...\n";

        // Find Admin/Creator (Role 2)
        $stmt = $pdo->prepare("SELECT id FROM users WHERE company_id = ? AND role_id = 2 LIMIT 1");
        $stmt->execute([$cid]);
        $creator_id = $stmt->fetchColumn();
        if (!$creator_id) {
            // Fallback: any user
            $stmt = $pdo->prepare("SELECT id FROM users WHERE company_id = ? LIMIT 1");
            $stmt->execute([$cid]);
            $creator_id = $stmt->fetchColumn();
        }

        // Get Departments
        $stmt = $pdo->prepare("SELECT id, name FROM departments WHERE company_id = ?");
        $stmt->execute([$cid]);
        $depts = $stmt->fetchAll(PDO::FETCH_KEY_PAIR); // id => name

        // We want 5-7 teams.
        $teams_created = 0;
        $target_teams = rand(5, 7);

        $dept_ids = array_keys($depts);

        while ($teams_created < $target_teams) {
            // Pick a dept
            if (empty($dept_ids))
                break; // No depts?

            $did = $dept_ids[array_rand($dept_ids)];
            $dname = $depts[$did];

            // Generate Name
            $base_name = "Team";
            if (isset($team_defs[$dname])) {
                $candidates = $team_defs[$dname];
                $base_name = $candidates[array_rand($candidates)];
            } else {
                $base_name = "$dname Squad";
            }

            // Unique name check?
            $final_name = $base_name . " " . rand(1, 99);

            // Create Team
            $created_at = date('Y-m-d H:i:s');
            // Assuming 'teams' table: company_id, name, created_by...
            // Let's check schema: columns might be (company_id, name, description, created_by...)
            // In original_template.sql: CREATE TABLE teams (id, company_id, name, description, created_by...)

            // Important: We need to link Team to Dept? No, schema doesn't seem to link Team directly to Dept.
            // But user said "according to department".
            // So we assign EMPLOYEES from that Dept to this Team.

            $stmt_t = $pdo->prepare("INSERT INTO teams (company_id, name, description, created_by, created_at) VALUES (?, ?, ?, ?, ?)");
            $stmt_t->execute([$cid, $final_name, "Team for $dname tasks", $creator_id, $created_at]);
            $tid = $pdo->lastInsertId();

            echo "  Created Team: $final_name (Dept: $dname)\n";
            $teams_created++;

            // Assign Employees
            // Fetch employees in this Dept
            $stmt_e = $pdo->prepare("SELECT id FROM employees WHERE department_id = ? AND status='active'");
            $stmt_e->execute([$did]);
            $emps = $stmt_e->fetchAll(PDO::FETCH_COLUMN);

            if ($emps) {
                // Assign 3-8 random employees
                $num_to_assign = rand(3, 8);
                if ($num_to_assign > count($emps))
                    $num_to_assign = count($emps);

                $picked_emps = array_rand(array_flip($emps), $num_to_assign);
                if (!is_array($picked_emps))
                    $picked_emps = [$picked_emps];

                foreach ($picked_emps as $eid) {
                    // Check if already in team?
                    // team_members: team_id, employee_id
                    // UNIQUE KEY unique_team_employee (team_id, employee_id)
                    // But employee can be in multiple teams? Logic allows it.

                    $stmt_m = $pdo->prepare("INSERT IGNORE INTO team_members (team_id, employee_id, assigned_by, assigned_at) VALUES (?, ?, ?, ?)");
                    $stmt_m->execute([$tid, $eid, $creator_id, $created_at]);
                }
                echo "    Assigned " . count($picked_emps) . " members.\n";
            }
        }
    }

    $pdo->commit();
    echo "SUCCESS: Teams seeded.\n";

} catch (Exception $e) {
    if (isset($pdo) && $pdo->inTransaction())
        $pdo->rollBack();
    echo "ERROR: " . $e->getMessage() . "\n";
}
?>