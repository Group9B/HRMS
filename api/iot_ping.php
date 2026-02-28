<?php
/**
 * Simple IoT Ping Test - No Authentication Required
 * Use this to verify Wokwi can reach your server through ngrok
 */

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');

// Log the request for debugging
$logData = [
    'time' => date('Y-m-d H:i:s'),
    'method' => $_SERVER['REQUEST_METHOD'],
    'headers' => getallheaders(),
    'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown'
];

// Write to log file
file_put_contents(__DIR__ . '/../logs/iot_debug.log', json_encode($logData) . "\n", FILE_APPEND);

// Return simple success
echo json_encode([
    'success' => true,
    'message' => 'Ping OK',
    'server_time' => date('H:i:s'),
    'your_ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown'
]);
