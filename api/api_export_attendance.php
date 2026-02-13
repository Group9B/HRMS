<?php
/**
 * Attendance Excel Export API
 * Exports attendance data for a given month as .xlsx using native PHP ZipArchive + XML.
 */

require_once '../config/db.php';
require_once '../includes/functions.php';

if (!isLoggedIn() || !in_array($_SESSION['role_id'], [2, 3])) {
    http_response_code(403);
    echo 'Unauthorized access.';
    exit();
}

$company_id = $_SESSION['company_id'];
$month = $_GET['month'] ?? date('Y-m');
$start_date = $month . '-01';
$end_date = date('Y-m-t', strtotime($start_date));
$days_in_month = (int) date('t', strtotime($start_date));

// Fetch attendance data
$sql = "SELECT e.id as employee_id, e.first_name, e.last_name, d.name as department_name,
               a.date, a.status, a.check_in, a.check_out
        FROM employees e
        JOIN users u ON e.user_id = u.id
        LEFT JOIN departments d ON e.department_id = d.id
        LEFT JOIN attendance a ON e.id = a.employee_id AND a.date BETWEEN ? AND ?
        WHERE u.company_id = ?
        ORDER BY d.name, e.first_name, a.date";
$result = query($mysqli, $sql, [$start_date, $end_date, $company_id]);

if (!$result['success']) {
    http_response_code(500);
    echo 'Failed to fetch attendance data.';
    exit();
}

// Group data by employee
$employees = [];
foreach ($result['data'] as $row) {
    $eid = $row['employee_id'];
    if (!isset($employees[$eid])) {
        $employees[$eid] = [
            'name' => $row['first_name'] . ' ' . $row['last_name'],
            'department' => $row['department_name'] ?? 'Unassigned',
            'attendance' => []
        ];
    }
    if ($row['date']) {
        $employees[$eid]['attendance'][$row['date']] = [
            'status' => ucfirst($row['status'] ?? ''),
            'check_in' => $row['check_in'] ?? '',
            'check_out' => $row['check_out'] ?? ''
        ];
    }
}

// Build headers: Employee, Department, then one col per day
$headers = ['Employee', 'Department'];
for ($d = 1; $d <= $days_in_month; $d++) {
    $headers[] = $d;
}
$headers[] = 'Present';
$headers[] = 'Absent';
$headers[] = 'Leave';

$colCount = count($headers);
$colLetters = [];
for ($i = 0; $i < $colCount; $i++) {
    if ($i < 26) {
        $colLetters[] = chr(65 + $i);
    } else {
        $colLetters[] = chr(64 + intdiv($i, 26)) . chr(65 + ($i % 26));
    }
}

$filename = 'Attendance_' . $month . '_' . date('His') . '.xlsx';

// ─── Build XLSX ───
$tmpFile = tempnam(sys_get_temp_dir(), 'xlsx');
$zip = new ZipArchive();
if ($zip->open($tmpFile, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
    http_response_code(500);
    echo 'Failed to create export file.';
    exit();
}

// [Content_Types].xml
$zip->addFromString('[Content_Types].xml', '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">
    <Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>
    <Default Extension="xml" ContentType="application/xml"/>
    <Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/>
    <Override PartName="/xl/worksheets/sheet1.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/>
    <Override PartName="/xl/styles.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.styles+xml"/>
    <Override PartName="/xl/sharedStrings.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sharedStrings+xml"/>
</Types>');

$zip->addFromString('_rels/.rels', '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">
    <Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/>
</Relationships>');

$zip->addFromString('xl/_rels/workbook.xml.rels', '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">
    <Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet1.xml"/>
    <Relationship Id="rId2" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/styles" Target="styles.xml"/>
    <Relationship Id="rId3" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/sharedStrings" Target="sharedStrings.xml"/>
</Relationships>');

$monthLabel = date('F Y', strtotime($start_date));
$zip->addFromString('xl/workbook.xml', '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">
    <sheets><sheet name="Attendance ' . htmlspecialchars($monthLabel) . '" sheetId="1" r:id="rId1"/></sheets>
</workbook>');

$zip->addFromString('xl/styles.xml', '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<styleSheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">
    <fonts count="2">
        <font><sz val="11"/><name val="Calibri"/></font>
        <font><b/><sz val="11"/><color rgb="FFFFFFFF"/><name val="Calibri"/></font>
    </fonts>
    <fills count="3">
        <fill><patternFill patternType="none"/></fill>
        <fill><patternFill patternType="gray125"/></fill>
        <fill><patternFill patternType="solid"><fgColor rgb="FF4472C4"/></patternFill></fill>
    </fills>
    <borders count="2">
        <border><left/><right/><top/><bottom/><diagonal/></border>
        <border>
            <left style="thin"><color auto="1"/></left>
            <right style="thin"><color auto="1"/></right>
            <top style="thin"><color auto="1"/></top>
            <bottom style="thin"><color auto="1"/></bottom>
            <diagonal/>
        </border>
    </borders>
    <cellStyleXfs count="1"><xf numFmtId="0" fontId="0" fillId="0" borderId="0"/></cellStyleXfs>
    <cellXfs count="3">
        <xf numFmtId="0" fontId="0" fillId="0" borderId="0" xfId="0"/>
        <xf numFmtId="0" fontId="1" fillId="2" borderId="1" xfId="0" applyFont="1" applyFill="1" applyBorder="1" applyAlignment="1"><alignment horizontal="center" vertical="center"/></xf>
        <xf numFmtId="0" fontId="0" fillId="0" borderId="1" xfId="0" applyBorder="1"/>
    </cellXfs>
</styleSheet>');

// Shared strings
$sharedStrings = [];
$ssIndex = 0;
$ssMap = [];

function getSSIndex($value, &$sharedStrings, &$ssIndex, &$ssMap) {
    $val = (string) $value;
    if (!isset($ssMap[$val])) {
        $ssMap[$val] = $ssIndex;
        $sharedStrings[] = $val;
        $ssIndex++;
    }
    return $ssMap[$val];
}

// Register headers
foreach ($headers as $h) {
    getSSIndex($h, $sharedStrings, $ssIndex, $ssMap);
}

// Register data
foreach ($employees as $emp) {
    getSSIndex($emp['name'], $sharedStrings, $ssIndex, $ssMap);
    getSSIndex($emp['department'], $sharedStrings, $ssIndex, $ssMap);
    for ($d = 1; $d <= $days_in_month; $d++) {
        $dateStr = $month . '-' . str_pad($d, 2, '0', STR_PAD_LEFT);
        $status = $emp['attendance'][$dateStr]['status'] ?? '-';
        $shortStatus = match (strtolower($status)) {
            'present' => 'P',
            'absent' => 'A',
            'leave' => 'L',
            'holiday' => 'H',
            'half-day' => 'HD',
            default => '-'
        };
        getSSIndex($shortStatus, $sharedStrings, $ssIndex, $ssMap);
    }
}

// Numbers for counts
foreach ($employees as $emp) {
    $counts = ['present' => 0, 'absent' => 0, 'leave' => 0];
    foreach ($emp['attendance'] as $att) {
        $key = strtolower($att['status']);
        if (isset($counts[$key])) $counts[$key]++;
        if ($key === 'half-day') $counts['present'] += 0.5;
    }
    getSSIndex((string)$counts['present'], $sharedStrings, $ssIndex, $ssMap);
    getSSIndex((string)$counts['absent'], $sharedStrings, $ssIndex, $ssMap);
    getSSIndex((string)$counts['leave'], $sharedStrings, $ssIndex, $ssMap);
}

// sharedStrings.xml
$ssXml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>';
$ssXml .= '<sst xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" count="' . count($sharedStrings) . '" uniqueCount="' . count($sharedStrings) . '">';
foreach ($sharedStrings as $s) {
    $ssXml .= '<si><t>' . htmlspecialchars($s, ENT_XML1, 'UTF-8') . '</t></si>';
}
$ssXml .= '</sst>';
$zip->addFromString('xl/sharedStrings.xml', $ssXml);

// Sheet
$sheetXml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>';
$sheetXml .= '<worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">';
$sheetXml .= '<cols>';
$sheetXml .= '<col min="1" max="1" width="25" customWidth="1"/>';
$sheetXml .= '<col min="2" max="2" width="20" customWidth="1"/>';
for ($i = 3; $i <= $colCount; $i++) {
    $sheetXml .= '<col min="' . $i . '" max="' . $i . '" width="5" customWidth="1"/>';
}
$sheetXml .= '</cols>';
$sheetXml .= '<sheetData>';

// Header row
$sheetXml .= '<row r="1">';
foreach ($headers as $i => $h) {
    $idx = getSSIndex($h, $sharedStrings, $ssIndex, $ssMap);
    $sheetXml .= '<c r="' . $colLetters[$i] . '1" t="s" s="1"><v>' . $idx . '</v></c>';
}
$sheetXml .= '</row>';

// Data rows
$rowNum = 2;
foreach ($employees as $emp) {
    $sheetXml .= '<row r="' . $rowNum . '">';
    
    // Name
    $idx = getSSIndex($emp['name'], $sharedStrings, $ssIndex, $ssMap);
    $sheetXml .= '<c r="' . $colLetters[0] . $rowNum . '" t="s" s="2"><v>' . $idx . '</v></c>';
    
    // Department
    $idx = getSSIndex($emp['department'], $sharedStrings, $ssIndex, $ssMap);
    $sheetXml .= '<c r="' . $colLetters[1] . $rowNum . '" t="s" s="2"><v>' . $idx . '</v></c>';
    
    // Days
    $counts = ['present' => 0, 'absent' => 0, 'leave' => 0];
    for ($d = 1; $d <= $days_in_month; $d++) {
        $dateStr = $month . '-' . str_pad($d, 2, '0', STR_PAD_LEFT);
        $status = $emp['attendance'][$dateStr]['status'] ?? '-';
        $shortStatus = match (strtolower($status)) {
            'present' => 'P',
            'absent' => 'A',
            'leave' => 'L',
            'holiday' => 'H',
            'half-day' => 'HD',
            default => '-'
        };
        
        $key = strtolower($status);
        if (isset($counts[$key])) $counts[$key]++;
        if ($key === 'half-day') $counts['present'] += 0.5;
        
        $colIdx = $d + 1; // 0=Name, 1=Dept, 2+=days
        $idx = getSSIndex($shortStatus, $sharedStrings, $ssIndex, $ssMap);
        $sheetXml .= '<c r="' . $colLetters[$colIdx] . $rowNum . '" t="s" s="2"><v>' . $idx . '</v></c>';
    }
    
    // Summary counts
    $summaryStartCol = $days_in_month + 2;
    $idx = getSSIndex((string)$counts['present'], $sharedStrings, $ssIndex, $ssMap);
    $sheetXml .= '<c r="' . $colLetters[$summaryStartCol] . $rowNum . '" t="s" s="2"><v>' . $idx . '</v></c>';
    $idx = getSSIndex((string)$counts['absent'], $sharedStrings, $ssIndex, $ssMap);
    $sheetXml .= '<c r="' . $colLetters[$summaryStartCol + 1] . $rowNum . '" t="s" s="2"><v>' . $idx . '</v></c>';
    $idx = getSSIndex((string)$counts['leave'], $sharedStrings, $ssIndex, $ssMap);
    $sheetXml .= '<c r="' . $colLetters[$summaryStartCol + 2] . $rowNum . '" t="s" s="2"><v>' . $idx . '</v></c>';
    
    $sheetXml .= '</row>';
    $rowNum++;
}

$sheetXml .= '</sheetData></worksheet>';
$zip->addFromString('xl/worksheets/sheet1.xml', $sheetXml);

$zip->close();

header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment; filename="' . $filename . '"');
header('Content-Length: ' . filesize($tmpFile));
header('Cache-Control: max-age=0');

readfile($tmpFile);
unlink($tmpFile);
exit();
