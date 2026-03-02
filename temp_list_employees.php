<?php
$m = new mysqli('localhost', 'root', '', 'hrms_db');
if ($m->connect_error) {
    die("DB Error: " . $m->connect_error);
}

echo "=== Active Employees ===\n";
$r = $m->query("SELECT id, first_name, last_name, employee_code FROM employees WHERE status='active' LIMIT 15");
while ($row = $r->fetch_assoc()) {
    echo $row['id'] . " - " . $row['first_name'] . " " . $row['last_name'] . " (" . $row['employee_code'] . ")\n";
}

echo "\n=== Existing RFID Credentials ===\n";
$r2 = $m->query("SELECT ec.*, e.first_name, e.last_name FROM employee_credentials ec JOIN employees e ON ec.employee_id = e.id WHERE ec.type='rfid'");
if ($r2 && $r2->num_rows > 0) {
    while ($row = $r2->fetch_assoc()) {
        echo $row['employee_id'] . " - " . $row['first_name'] . " " . $row['last_name'] . " → RFID: " . $row['identifier_value'] . "\n";
    }
} else {
    echo "No RFID credentials registered yet.\n";
}
$m->close();
