<?php
session_start();
$_SESSION['user_id'] = 8;
$_SESSION['company_id'] = 1;
$_SESSION['role_id'] = 2;
$_SESSION['logged_in'] = true;

// Make a request to the API
$query_string = http_build_query(['action' => 'get_attendance_data', 'month' => '2025-12']);
$url = 'http://localhost/HRMS/api/api_attendance.php?' . $query_string;

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_COOKIE, session_name() . '=' . session_id());
$response = curl_exec($ch);
curl_close($ch);

$data = json_decode($response, true);

echo "API Response for December 2025:\n";
echo str_repeat('=', 100) . "\n\n";

if ($data['success']) {
    echo "✓ API call successful\n\n";

    echo "Employee Leaves Array:\n";
    if (empty($data['employee_leaves'])) {
        echo "  ✗ EMPTY - Still not populated!\n";
    } else {
        echo "  ✓ Populated with data:\n";
        foreach ($data['employee_leaves'] as $emp_id => $dates) {
            printf("    Employee ID %d: %s\n", $emp_id, implode(', ', array_keys($dates)));
        }
    }

    echo "\n" . str_repeat('=', 100) . "\n";
    echo "Employees with attendance data:\n";
    foreach ($data['employees'] as $emp) {
        $leave_count = count(array_filter($emp['attendance'], fn($att) => $att['status'] === 'leave'));
        printf("  %s: %d leave records\n", $emp['name'], $leave_count);
    }

    echo "\n" . str_repeat('=', 100) . "\n";
    echo "Summary:\n";
    printf("  Total Present: %d\n", $data['summary']['total_present']);
    printf("  Total Absent: %d\n", $data['summary']['total_absent']);
    printf("  Total Leave: %d\n", $data['summary']['total_leave']);
    printf("  Total Half-day: %d\n", $data['summary']['total_half_day']);
    printf("  Total Holiday: %d\n", $data['summary']['total_holiday']);
} else {
    echo "✗ API call failed\n";
    echo json_encode($data, JSON_PRETTY_PRINT);
}

echo "\n";
?>