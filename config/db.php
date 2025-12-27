<?php
// config/db.php
// Database connection using MySQLi OOP

// Load environment variables from .env file
$env_file = __DIR__ . '/../.env';
if (file_exists($env_file)) {
    $lines = file($env_file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        // Skip comments
        if (strpos(trim($line), '#') === 0) {
            continue;
        }
        // Parse KEY=VALUE format
        if (strpos($line, '=') !== false) {
            list($key, $value) = explode('=', $line, 2);
            $key = trim($key);
            $value = trim($value, " \t\n\r\0\x0B\"'");
            if (!empty($key)) {
                putenv("{$key}={$value}");
            }
        }
    }
}

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