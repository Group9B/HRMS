<?php
require_once __DIR__ . '/../config/db.php';

// disable foreign key checks to avoid issues during alteration if needed (though adding column usually safe)
$mysqli->query("SET FOREIGN_KEY_CHECKS=0");

// Check if column exists
$check = $mysqli->query("SHOW COLUMNS FROM interviews LIKE 'status'");
if ($check->num_rows == 0) {
    echo "Adding status column to interviews table...\n";
    $sql = "ALTER TABLE interviews ADD COLUMN status ENUM('scheduled', 'completed') DEFAULT 'scheduled' AFTER interview_date";
    if ($mysqli->query($sql)) {
        echo "Successfully added status column.\n";
    } else {
        echo "Error adding column: " . $mysqli->error . "\n";
    }
} else {
    echo "Column 'status' already exists.\n";
}

$mysqli->query("SET FOREIGN_KEY_CHECKS=1");
echo "Migration completed.\n";
?>