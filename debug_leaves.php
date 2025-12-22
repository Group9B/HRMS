<?php
require_once 'config/db.php';
require_once 'includes/functions.php';

echo "Checking approved leaves in database...\n";
echo str_repeat('=', 100) . "\n\n";

$company_id = 1;
$month = '2025-12';
$start_date = $month . '-01';
$end_date = date('Y-m-t', strtotime($start_date));

echo "Date range: $start_date to $end_date\n\n";

// Check leaves in database
$leaves = $mysqli->query("
    SELECT l.id, l.employee_id, e.first_name, e.last_name, l.start_date, l.end_date, l.status
    FROM leaves l
    JOIN employees e ON l.employee_id = e.id
    JOIN users u ON e.user_id = u.id
    WHERE u.company_id = $company_id
    ORDER BY l.start_date DESC
");

$leaves_data = $leaves->fetch_all(MYSQLI_ASSOC);

echo "All leaves for company $company_id:\n";
if (empty($leaves_data)) {
    echo "  (No leaves found)\n";
} else {
    foreach ($leaves_data as $leave) {
        printf(
            "  Leave ID: %d | Employee: %s %s | Dates: %s to %s | Status: %s\n",
            $leave['id'],
            $leave['first_name'],
            $leave['last_name'],
            $leave['start_date'],
            $leave['end_date'],
            $leave['status']
        );
    }
}

echo "\n" . str_repeat('=', 100) . "\n";
echo "Checking approved leaves for December 2025:\n";
echo str_repeat('=', 100) . "\n\n";

$approved_leaves = $mysqli->query("
    SELECT l.id, l.employee_id, e.first_name, e.last_name, l.start_date, l.end_date, l.status
    FROM leaves l
    JOIN employees e ON l.employee_id = e.id
    JOIN users u ON e.user_id = u.id
    WHERE l.status = 'approved' AND u.company_id = $company_id AND l.start_date <= '$end_date' AND l.end_date >= '$start_date'
    ORDER BY l.start_date
");

$approved = $approved_leaves->fetch_all(MYSQLI_ASSOC);

if (empty($approved)) {
    echo "✗ No approved leaves found for December 2025\n";
} else {
    printf("✓ Found %d approved leave(s):\n\n", count($approved));
    foreach ($approved as $leave) {
        printf(
            "  Leave ID: %d | Employee: %s %s (ID: %d) | Dates: %s to %s\n",
            $leave['id'],
            $leave['first_name'],
            $leave['last_name'],
            $leave['employee_id'],
            $leave['start_date'],
            $leave['end_date']
        );
    }
}

echo "\n" . str_repeat('=', 100) . "\n";
echo "Checking attendance records with 'leave' status for December:\n";
echo str_repeat('=', 100) . "\n\n";

$attendance = $mysqli->query("
    SELECT a.id, a.employee_id, e.first_name, e.last_name, a.date, a.status
    FROM attendance a
    JOIN employees e ON a.employee_id = e.id
    JOIN users u ON e.user_id = u.id
    WHERE u.company_id = $company_id AND a.status = 'leave' AND a.date BETWEEN '$start_date' AND '$end_date'
    ORDER BY a.date, a.employee_id
");

$att_data = $attendance->fetch_all(MYSQLI_ASSOC);

if (empty($att_data)) {
    echo "✗ No leave attendance records found\n";
} else {
    printf("✓ Found %d leave attendance records:\n\n", count($att_data));
    foreach ($att_data as $rec) {
        printf(
            "  Attendance ID: %d | Employee: %s %s | Date: %s\n",
            $rec['id'],
            $rec['first_name'],
            $rec['last_name'],
            $rec['date']
        );
    }
}

echo "\n" . str_repeat('=', 100) . "\n";
?>