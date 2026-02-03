<?php
/**
 * Overtime Calculation Helper
 * Calculates overtime hours and pay based on attendance vs shift times
 */

/**
 * Calculate overtime for an employee for a given period
 * 
 * @param mysqli $mysqli Database connection
 * @param int $employee_id Employee ID
 * @param string $period Period in YYYY-MM format
 * @return array ['overtime_hours' => float, 'overtime_amount' => float, 'details' => array]
 */
function calculateOvertimeForPeriod($mysqli, $employee_id, $period)
{
    $result = [
        'overtime_hours' => 0,
        'overtime_amount' => 0,
        'hourly_rate' => 0,
        'details' => [],
        'error' => null
    ];

    // Get employee with shift and salary
    $empQuery = query($mysqli, "
        SELECT e.id, e.salary, e.shift_id, 
               s.start_time, s.end_time, s.name as shift_name
        FROM employees e
        LEFT JOIN shifts s ON e.shift_id = s.id
        WHERE e.id = ?
    ", [$employee_id]);

    if (!$empQuery['success'] || empty($empQuery['data'])) {
        $result['error'] = 'Employee not found';
        return $result;
    }

    $emp = $empQuery['data'][0];

    // Validate required data
    if (empty($emp['salary']) || $emp['salary'] <= 0) {
        $result['error'] = 'Employee salary not set';
        return $result;
    }

    if (empty($emp['start_time']) || empty($emp['end_time'])) {
        $result['error'] = 'Employee shift not configured';
        return $result;
    }

    $salary = (float) $emp['salary'];
    $shiftStart = $emp['start_time'];
    $shiftEnd = $emp['end_time'];

    // Calculate shift hours
    $shiftHours = calculateTimeDifferenceInHours($shiftStart, $shiftEnd);
    if ($shiftHours <= 0) {
        $result['error'] = 'Invalid shift configuration';
        return $result;
    }

    // Get period dates
    $startDate = $period . '-01';
    $endDate = date('Y-m-t', strtotime($startDate));

    // Fetch attendance records with both check-in and check-out
    $attendanceQuery = query($mysqli, "
        SELECT date, check_in, check_out, status
        FROM attendance
        WHERE employee_id = ? 
          AND date >= ? 
          AND date <= ?
          AND check_in IS NOT NULL 
          AND check_out IS NOT NULL
          AND status IN ('present', 'half-day')
        ORDER BY date ASC
    ", [$employee_id, $startDate, $endDate]);

    if (!$attendanceQuery['success']) {
        $result['error'] = 'Failed to fetch attendance';
        return $result;
    }

    $totalOvertimeHours = 0;
    $details = [];

    foreach ($attendanceQuery['data'] as $record) {
        $workedHours = calculateTimeDifferenceInHours($record['check_in'], $record['check_out']);

        if ($workedHours > $shiftHours) {
            $overtimeHours = $workedHours - $shiftHours;
            $totalOvertimeHours += $overtimeHours;

            $details[] = [
                'date' => $record['date'],
                'check_in' => $record['check_in'],
                'check_out' => $record['check_out'],
                'worked_hours' => round($workedHours, 2),
                'overtime_hours' => round($overtimeHours, 2)
            ];
        }
    }

    // Calculate overtime pay
    // Assuming 22 working days per month
    $workingDaysPerMonth = 22;
    $dailyRate = $salary / $workingDaysPerMonth;
    $hourlyRate = $dailyRate / $shiftHours;
    $overtimeRate = $hourlyRate * 1.5; // 1.5x for overtime
    $overtimeAmount = $totalOvertimeHours * $overtimeRate;

    $result['overtime_hours'] = round($totalOvertimeHours, 2);
    $result['overtime_amount'] = round($overtimeAmount, 2);
    $result['hourly_rate'] = round($hourlyRate, 2);
    $result['overtime_rate'] = round($overtimeRate, 2);
    $result['shift_hours'] = round($shiftHours, 2);
    $result['details'] = $details;

    return $result;
}

/**
 * Calculate time difference in hours between two time strings
 * Handles overnight shifts (when end < start)
 * 
 * @param string $startTime Start time (HH:MM:SS)
 * @param string $endTime End time (HH:MM:SS)
 * @return float Hours difference
 */
function calculateTimeDifferenceInHours($startTime, $endTime)
{
    $start = strtotime($startTime);
    $end = strtotime($endTime);

    // Handle overnight shifts
    if ($end < $start) {
        $end += 86400; // Add 24 hours
    }

    $diffSeconds = $end - $start;
    return $diffSeconds / 3600; // Convert to hours
}
