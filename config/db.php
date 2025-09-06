<?php
// config/db.php
// Database connection using MySQLi OOP

$host = getenv('DB_HOST') ?: 'localhost';
$db = getenv('DB_NAME') ?: 'hrms_db';
$user = getenv('DB_USER') ?: 'root';
$pass = getenv('DB_PASS') ?: '';
$charset = 'utf8mb4';

define('APP_ROOT', __DIR__ . '/');
try {
    $mysqli = new mysqli($host, $user, $pass, $db);

    // Check connection
    if ($mysqli->connect_error) {
        throw new Exception("Connection failed: " . $mysqli->connect_error);
    }

    // Set charset
    $mysqli->set_charset($charset);

} catch (Exception $e) {
    http_response_code(500);
    echo "Database connection failed: " . htmlspecialchars($e->getMessage());
    exit;
}
if (PHP_SESSION_NONE === session_status()) {
    session_start();
}
?>