<?php
require_once 'config/db.php';
require_once 'includes/functions.php';

echo "Checking Employee 1 details:\n";
$r = query($mysqli, 'SELECT e.id, e.first_name, e.last_name, e.department_id, d.name as dept_name, d.company_id FROM employees e LEFT JOIN departments d ON e.department_id = d.id WHERE e.id = 1');
foreach ($r['data'] as $emp) {
    echo "ID: {$emp['id']}\n";
    echo "Name: {$emp['first_name']} {$emp['last_name']}\n";
    echo "Department ID: {$emp['department_id']}\n";
    echo "Department: {$emp['dept_name']}\n";
    echo "Company ID: {$emp['company_id']}\n";
}
?>