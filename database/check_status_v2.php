<?php
require_once __DIR__ . '/../config/db.php';
$check = $mysqli->query("SHOW COLUMNS FROM interviews LIKE 'status'");
echo ($check->num_rows > 0) ? "EXISTS" : "MISSING";
?>