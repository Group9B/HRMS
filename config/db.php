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

// Error logging configuration
$log_file = __DIR__ . '/../error.log';
$detailed_log_file = __DIR__ . '/../error_detailed.log';
$enable_logging = getenv('APP_DEBUG') === 'true';

try {
    $mysqli = new mysqli($host, $user, $pass, $db);

    // Check connection
    if ($mysqli->connect_error) {
        throw new Exception("Connection failed");
    }

    // Set charset
    $mysqli->set_charset($charset);

} catch (Exception $e) {
    // Log the error with sanitized message if logging is enabled
    if ($enable_logging) {
        $error_id = uniqid('db_error_', true);
        $error_message = date('[Y-m-d H:i:s] ') . "Database Error ID: " . $error_id . PHP_EOL;
        
        // Create log file if it doesn't exist and log the error
        if (is_writable(dirname($log_file))) {
            if (!file_exists($log_file)) {
                touch($log_file);
                chmod($log_file, 0640);
            }
            if (is_writable($log_file) || !file_exists($log_file)) {
                error_log($error_message, 3, $log_file);
            }
        }
        
        // Log full error to separate file only accessible to admin
        if (is_writable(dirname($detailed_log_file))) {
            if (!file_exists($detailed_log_file)) {
                touch($detailed_log_file);
                chmod($detailed_log_file, 0640);
            }
            if (is_writable($detailed_log_file) || !file_exists($detailed_log_file)) {
                error_log($error_message . "Details: " . $e->getMessage() . PHP_EOL, 3, $detailed_log_file);
            }
        }
    }
    
    http_response_code(500);
    echo "Database connection failed. Please contact the administrator.";
    exit;
}
if (PHP_SESSION_NONE === session_status()) {
    session_start();
}
?>