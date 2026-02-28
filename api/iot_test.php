<?php
/**
 * IoT Attendance System - Test Script
 * 
 * Run this script in browser or CLI to:
 * 1. Check if required tables exist
 * 2. Create a test device token
 * 3. Create a test credential
 * 4. Show curl commands for testing
 * 
 * URL: http://localhost/hrms/api/iot_test.php
 */

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/functions.php';

header('Content-Type: text/html; charset=utf-8');

echo "<!DOCTYPE html><html><head><title>IoT Attendance Test</title>";
echo "<style>body{font-family:monospace;padding:20px;background:#1e1e1e;color:#d4d4d4;} 
      .success{color:#4ec9b0;} .error{color:#f14c4c;} .warning{color:#dcdcaa;}
      pre{background:#2d2d2d;padding:15px;border-radius:5px;overflow-x:auto;}
      h2{color:#569cd6;border-bottom:1px solid #3c3c3c;padding-bottom:10px;}
      .box{background:#252526;padding:15px;margin:10px 0;border-radius:5px;border-left:3px solid #569cd6;}</style>";
echo "</head><body>";
echo "<h1>🔧 IoT Attendance System - Test & Setup</h1>";

// ─────────────────────────────────────────────────────────────
// Check 1: Required Tables
// ─────────────────────────────────────────────────────────────
echo "<h2>1. Database Tables Check</h2>";

$tables = ['employee_credentials', 'iot_devices', 'attendance'];
foreach ($tables as $table) {
    $result = $mysqli->query("SHOW TABLES LIKE '$table'");
    if ($result->num_rows > 0) {
        echo "<div class='success'>✓ Table `$table` exists</div>";
    } else {
        echo "<div class='error'>✗ Table `$table` NOT FOUND - Run migration SQL first!</div>";
    }
}

// Check attendance columns
$colResult = $mysqli->query("SHOW COLUMNS FROM attendance LIKE 'device_id'");
if ($colResult->num_rows > 0) {
    echo "<div class='success'>✓ Column `attendance.device_id` exists</div>";
} else {
    echo "<div class='warning'>⚠ Column `attendance.device_id` missing - Run ALTER TABLE</div>";
}

$colResult = $mysqli->query("SHOW COLUMNS FROM attendance LIKE 'auth_method'");
if ($colResult->num_rows > 0) {
    echo "<div class='success'>✓ Column `attendance.auth_method` exists</div>";
} else {
    echo "<div class='warning'>⚠ Column `attendance.auth_method` missing - Run ALTER TABLE</div>";
}

// ─────────────────────────────────────────────────────────────
// Check 2: Test Device Setup
// ─────────────────────────────────────────────────────────────
echo "<h2>2. Test Device Setup</h2>";

// Check if test device exists
$testToken = 'TEST_TOKEN_' . md5('hrms_iot_test');
$deviceResult = query($mysqli, "SELECT * FROM iot_devices WHERE device_token = ?", [$testToken]);

if ($deviceResult['success'] && !empty($deviceResult['data'])) {
    $device = $deviceResult['data'][0];
    echo "<div class='success'>✓ Test device exists (ID: {$device['id']})</div>";
    echo "<div class='box'><strong>Device Token:</strong> <code>$testToken</code></div>";
} else {
    // Get first company for test device
    $companyResult = query($mysqli, "SELECT id, name FROM companies LIMIT 1");
    if ($companyResult['success'] && !empty($companyResult['data'])) {
        $company = $companyResult['data'][0];

        // Create test device
        $insertResult = query(
            $mysqli,
            "INSERT INTO iot_devices (company_id, device_name, device_token, location, status) 
             VALUES (?, 'Test Scanner', ?, 'Test Location', 'active')",
            [$company['id'], $testToken]
        );

        if ($insertResult['success']) {
            echo "<div class='success'>✓ Created test device for company: {$company['name']}</div>";
            echo "<div class='box'><strong>Device Token:</strong> <code>$testToken</code></div>";
        } else {
            echo "<div class='error'>✗ Failed to create test device</div>";
        }
    } else {
        echo "<div class='error'>✗ No companies found - create a company first</div>";
    }
}

// ─────────────────────────────────────────────────────────────
// Check 3: Test Credential Setup
// ─────────────────────────────────────────────────────────────
echo "<h2>3. Test Credential Setup</h2>";

// Check if test credential exists
$testRfid = 'TEST_RFID_12345678';
$credResult = query($mysqli, "SELECT * FROM employee_credentials WHERE identifier_value = ?", [$testRfid]);

if ($credResult['success'] && !empty($credResult['data'])) {
    $cred = $credResult['data'][0];
    echo "<div class='success'>✓ Test credential exists (Employee ID: {$cred['employee_id']})</div>";
    echo "<div class='box'><strong>Test RFID:</strong> <code>$testRfid</code></div>";
} else {
    // Get first active employee for test credential
    $empResult = query(
        $mysqli,
        "SELECT e.id, e.first_name, e.last_name, u.company_id 
         FROM employees e 
         JOIN users u ON e.user_id = u.id 
         WHERE e.status = 'active' 
         LIMIT 1"
    );

    if ($empResult['success'] && !empty($empResult['data'])) {
        $emp = $empResult['data'][0];

        $insertResult = query(
            $mysqli,
            "INSERT INTO employee_credentials (employee_id, type, identifier_value) VALUES (?, 'rfid', ?)",
            [$emp['id'], $testRfid]
        );

        if ($insertResult['success']) {
            echo "<div class='success'>✓ Created test credential for: {$emp['first_name']} {$emp['last_name']}</div>";
            echo "<div class='box'><strong>Test RFID:</strong> <code>$testRfid</code></div>";
        } else {
            echo "<div class='error'>✗ Failed to create test credential</div>";
        }
    } else {
        echo "<div class='warning'>⚠ No active employees found - create an employee first</div>";
    }
}

// ─────────────────────────────────────────────────────────────
// Test Commands
// ─────────────────────────────────────────────────────────────
echo "<h2>4. Test Commands (Copy & Run)</h2>";

$baseUrl = 'http://localhost/hrms/api';

echo "<h3>Heartbeat Test (GET):</h3>";
echo "<pre>curl -X GET \"$baseUrl/iot_heartbeat.php\" \\
  -H \"Authorization: Bearer $testToken\"</pre>";

echo "<h3>Device Status Test (GET):</h3>";
echo "<pre>curl -X GET \"$baseUrl/iot_device_status.php\" \\
  -H \"Authorization: Bearer $testToken\"</pre>";

echo "<h3>Mark Attendance Test (POST):</h3>";
echo "<pre>curl -X POST \"$baseUrl/iot_attendance.php\" \\
  -H \"Authorization: Bearer $testToken\" \\
  -H \"Content-Type: application/json\" \\
  -d '{\"auth_type\": \"rfid\", \"identifier_value\": \"$testRfid\"}'</pre>";

// ─────────────────────────────────────────────────────────────
// Quick Stats
// ─────────────────────────────────────────────────────────────
echo "<h2>5. Current Stats</h2>";

$stats = [
    'Total Devices' => "SELECT COUNT(*) as c FROM iot_devices",
    'Active Devices' => "SELECT COUNT(*) as c FROM iot_devices WHERE status = 'active'",
    'Registered Credentials' => "SELECT COUNT(*) as c FROM employee_credentials",
    'Today\'s IoT Attendance' => "SELECT COUNT(*) as c FROM attendance WHERE date = CURDATE() AND device_id IS NOT NULL"
];

echo "<div class='box'>";
foreach ($stats as $label => $sql) {
    $result = $mysqli->query($sql);
    $count = $result ? $result->fetch_assoc()['c'] : 'N/A';
    echo "$label: <strong>$count</strong><br>";
}
echo "</div>";

echo "</body></html>";
