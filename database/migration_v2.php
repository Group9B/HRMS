<?php
require_once __DIR__ . '/../config/db.php';

$mysqli->query("SET FOREIGN_KEY_CHECKS=0");

$check = $mysqli->query("SHOW COLUMNS FROM interviews LIKE 'status'");
if ($check->num_rows == 0) {
    echo "Adding status column...\n";
    $sql = "ALTER TABLE interviews ADD COLUMN status ENUM('scheduled', 'completed') DEFAULT 'scheduled' AFTER interview_date";
    if ($mysqli->query($sql)) {
        echo "SUCCESS: Added status column.";
    } else {
        echo "ERROR: " . $mysqli->error;
    }
} else {
    echo "EXISTS: Column status already exists.";
}

$mysqli->query("SET FOREIGN_KEY_CHECKS=1");
?>