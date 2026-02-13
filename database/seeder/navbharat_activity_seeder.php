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
    echo "Seeding Activity for Company ID: $cid (Navbharat Construct)...\n";

    // 1. Fetch Employees
    $stmt = $pdo->prepare("
        SELECT e.id, e.first_name, e.last_name 
        FROM employees e 
        JOIN departments d ON e.department_id = d.id 
        WHERE d.company_id = ? AND e.status = 'active'
    ");
    $stmt->execute([$cid]);
    $employees = $stmt->fetchAll();
    echo "Found " . count($employees) . " active employees.\n";

    if (count($employees) == 0)
        die("No employees found. Seed employees first.\n");

    // 2. Fetch Teams
    $stmt = $pdo->prepare("SELECT id, name FROM teams WHERE company_id = ?");
    $stmt->execute([$cid]);
    $teams = $stmt->fetchAll();
    echo "Found " . count($teams) . " teams.\n";

    // ==========================================
    // SEED ATTENDANCE (Last 30 Days)
    // ==========================================
    echo "\nSeeding Attendance...\n";
    $pdo->beginTransaction();

    $start_date = new DateTime();
    $start_date->modify('-30 days');
    $end_date = new DateTime();

    $dates = new DatePeriod($start_date, new DateInterval('P1D'), $end_date);
    $att_count = 0;

    foreach ($dates as $date) {
        $curr_date = $date->format('Y-m-d');
        $is_weekend = (date('N', strtotime($curr_date)) >= 7); // Sunday only

        foreach ($employees as $emp) {
            // 10% random absent if weekday, 100% absent (holiday) if sunday
            // Actually, let's keep it simple: Sundays are holidays/off.

            if ($is_weekend) {
                // Determine if we should log it as 'holiday' or just skip. 
                // Let's log 'holiday' for Sunday.
                $status = 'holiday'; // using 'holiday' distinct from enum? Schema says: 'present','absent','leave','half-day','holiday'
                $check_in = null;
                $check_out = null;
                $remarks = "Weekly Off";
            } else {
                $rand = rand(1, 100);
                if ($rand <= 85) {
                    $status = 'present';
                    // 9:00 AM +/- 30 mins
                    $hour = 9;
                    $min = rand(0, 59);
                    $check_in = sprintf("%02d:%02d:00", $hour, $min);

                    // 6:00 PM +/- 30 mins
                    $out_hour = 18;
                    $out_min = rand(0, 59);
                    $check_out = sprintf("%02d:%02d:00", $out_hour, $out_min);
                    $remarks = "Regular";
                } elseif ($rand <= 90) {
                    $status = 'absent';
                    $check_in = null;
                    $check_out = null;
                    $remarks = "Uninformed";
                } elseif ($rand <= 95) {
                    $status = 'leave';
                    $check_in = null;
                    $check_out = null;
                    $remarks = "Sick Leave";
                } else {
                    $status = 'half-day';
                    $check_in = "09:30:00";
                    $check_out = "13:30:00";
                    $remarks = "Personal Work";
                }
            }

            // Check duplicate
            $chk = $pdo->prepare("SELECT id FROM attendance WHERE employee_id = ? AND date = ?");
            $chk->execute([$emp['id'], $curr_date]);
            if (!$chk->fetch()) {
                $ins = $pdo->prepare("INSERT INTO attendance (employee_id, date, check_in, check_out, status, remarks) VALUES (?, ?, ?, ?, ?, ?)");
                $ins->execute([$emp['id'], $curr_date, $check_in, $check_out, $status, $remarks]);
                $att_count++;
            }
        }
    }
    $pdo->commit();
    echo "  Inserted $att_count attendance records.\n";

    // ==========================================
    // SEED TASKS
    // ==========================================
    echo "\nSeeding Tasks...\n";
    $pdo->beginTransaction();
    $tasks_to_seed = 50;

    // Status enum: 'pending','in_progress','completed','cancelled'
    $statuses = ['pending', 'in_progress', 'completed', 'cancelled'];
    $task_titles = ['Generate Report', 'Client Meeting', 'Update Database', 'Fix Bugs', 'Server Maintenance', 'Audit Preparation', 'Documentation', 'Team Sync', 'Inventory Check', 'Deployment'];

    $task_count = 0;
    for ($i = 0; $i < $tasks_to_seed; $i++) {
        $emp = $employees[array_rand($employees)];
        $team_id = (count($teams) > 0 && rand(0, 1)) ? $teams[array_rand($teams)]['id'] : null;

        $title = $task_titles[array_rand($task_titles)] . " " . rand(100, 999);
        $status = $statuses[array_rand($statuses)];
        $due_date = date('Y-m-d', strtotime("+" . rand(1, 14) . " days"));
        $assigned_by = 1; // Assuming SuperAdmin or finding a manager... let's null or 1.

        $ins = $pdo->prepare("INSERT INTO tasks (employee_id, team_id, title, description, assigned_by, due_date, status) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $ins->execute([$emp['id'], $team_id, $title, "Please complete this task regarding $title.", $assigned_by, $due_date, $status]);
        $task_count++;
    }
    $pdo->commit();
    echo "  Inserted $task_count tasks.\n";

    // ==========================================
    // SEED TEAM PERFORMANCE
    // ==========================================
    if (count($teams) > 0) {
        echo "\nSeeding Team Performance...\n";
        $pdo->beginTransaction();
        $perf_count = 0;

        // Seed for last 2 months
        $periods = [date('Y-m', strtotime('-1 month')), date('Y-m')];

        foreach ($teams as $team) {
            foreach ($periods as $period) {
                // Check if exists
                $chk = $pdo->prepare("SELECT id FROM team_performance WHERE team_id = ? AND period = ?");
                $chk->execute([$team['id'], $period]);

                if (!$chk->fetch()) {
                    $score = rand(70, 98);
                    $collab = rand(7, 10);
                    $achieve = "Delivered all milestones for $period.";
                    $chal = "Minor resource constraints.";
                    $remarks = "Good progress.";

                    $ins = $pdo->prepare("INSERT INTO team_performance (team_id, period, score, collaboration_score, achievements, challenges, remarks, evaluated_by, approved_by) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
                    $ins->execute([$team['id'], $period, $score, $collab, $achieve, $chal, $remarks, 1, 1]);
                    $perf_count++;
                }
            }
        }
        $pdo->commit();
        echo "  Inserted $perf_count team performance records.\n";
    }

    echo "\nSUCCESS: Navbharat seeding complete.\n";

} catch (Exception $e) {
    if (isset($pdo) && $pdo->inTransaction())
        $pdo->rollBack();
    echo "ERROR: " . $e->getMessage() . "\n";
}
?>