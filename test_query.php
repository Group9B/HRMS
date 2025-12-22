<?php
require_once 'config/db.php';
require_once 'includes/functions.php';

$company_id = 1;
$start_date = '2025-12-01';
$end_date = '2025-12-31';

echo "Testing the leaves query with correct JOIN:\n";
echo str_repeat('=', 100) . "\n\n";

$leaves_result = query($mysqli, "
    SELECT employee_id, start_date, end_date 
    FROM leaves l
    JOIN employees e ON l.employee_id = e.id
    JOIN users u ON e.user_id = u.id
    WHERE l.status = 'approved' AND u.company_id = ? AND l.start_date <= ? AND l.end_date >= ?
", [$company_id, $end_date, $start_date]);

echo "Query Result:\n";
if ($leaves_result['success']) {
    echo "✓ Query executed successfully\n";
    echo "Data returned: " . count($leaves_result['data']) . " records\n\n";

    $employee_leaves = [];
    if (!empty($leaves_result['data'])) {
        foreach ($leaves_result['data'] as $leave) {
            echo "Leave: Employee ID {$leave['employee_id']}, {$leave['start_date']} to {$leave['end_date']}\n";

            $current = new DateTime($leave['start_date']);
            $end = new DateTime($leave['end_date']);
            while ($current <= $end) {
                $employee_leaves[$leave['employee_id']][$current->format('Y-m-d')] = true;
                $current->modify('+1 day');
            }
        }

        echo "\n" . str_repeat('-', 100) . "\n";
        echo "Built employee_leaves array:\n";
        foreach ($employee_leaves as $emp_id => $dates) {
            echo "  Employee ID $emp_id:\n";
            echo "    Dates: " . implode(', ', array_keys($dates)) . "\n";
        }
    } else {
        echo "No leaves returned\n";
    }
} else {
    echo "✗ Query failed: " . $leaves_result['error'] . "\n";
}

echo "\n" . str_repeat('=', 100) . "\n";
?>