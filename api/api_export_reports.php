<?php
/**
 * Reports Excel Export API
 * Generates a multi-sheet .xlsx file with all company report data.
 * Sheets: Departments, Designations, Attendance Trends, Leave Distribution, Payroll Trends, Recruitment Funnel.
 * Zero external dependencies required.
 */

require_once '../config/db.php';
require_once '../includes/functions.php';

// Security Check
if (!isLoggedIn() || !in_array($_SESSION['role_id'], [2, 3])) {
    http_response_code(403);
    echo 'Unauthorized access.';
    exit();
}

$company_id = $_SESSION['company_id'];

// ── Fetch all report data ──

// 1. Department Distribution
$dept_data = [];
$dept_result = query($mysqli, "
    SELECT d.name, COUNT(e.id) as count 
    FROM departments d 
    LEFT JOIN employees e ON d.id = e.department_id AND e.status = 'active'
    WHERE d.company_id = ? 
    GROUP BY d.id, d.name
    ORDER BY count DESC
", [$company_id]);
if ($dept_result['success'])
    $dept_data = $dept_result['data'];

// 2. Designation Distribution
$desig_data = [];
$desig_result = query($mysqli, "
    SELECT ds.name, COUNT(e.id) as count 
    FROM designations ds 
    INNER JOIN departments d ON ds.department_id = d.id
    LEFT JOIN employees e ON ds.id = e.designation_id AND e.status = 'active'
    WHERE d.company_id = ? 
    GROUP BY ds.id, ds.name
    HAVING count > 0
    ORDER BY count DESC
", [$company_id]);
if ($desig_result['success'])
    $desig_data = $desig_result['data'];

// 3. Attendance Trends (Last 15 days)
$attendance_data = [];
$att_result = query($mysqli, "
    SELECT date, 
           COUNT(CASE WHEN status = 'present' THEN 1 END) as present_count,
           COUNT(id) as total_attendance
    FROM attendance 
    WHERE employee_id IN (SELECT id FROM employees WHERE company_id = ?)
      AND date >= DATE_SUB(CURDATE(), INTERVAL 15 DAY)
    GROUP BY date 
    ORDER BY date ASC
", [$company_id]);
if ($att_result['success'])
    $attendance_data = $att_result['data'];

// 4. Leave Distribution
$leave_data = [];
$leave_result = query($mysqli, "
    SELECT status, COUNT(id) as count 
    FROM leaves 
    WHERE employee_id IN (SELECT id FROM employees WHERE company_id = ?)
    GROUP BY status
", [$company_id]);
if ($leave_result['success'])
    $leave_data = $leave_result['data'];

// 5. Payroll Trends (Last 6 months)
$payroll_data = [];
$payroll_result = query($mysqli, "
    SELECT period, SUM(net_salary) as total_payout, COUNT(id) as payslip_count
    FROM payslips 
    WHERE company_id = ? AND status != 'cancelled'
      AND period >= DATE_FORMAT(DATE_SUB(CURDATE(), INTERVAL 6 MONTH), '%Y-%m')
    GROUP BY period 
    ORDER BY period ASC
", [$company_id]);
if ($payroll_result['success'])
    $payroll_data = $payroll_result['data'];

// 6. Recruitment Funnel
$recruitment_data = [];
$recruitment_result = query($mysqli, "
    SELECT status, COUNT(id) as count 
    FROM job_applications 
    WHERE job_id IN (SELECT id FROM jobs WHERE company_id = ?)
    GROUP BY status
", [$company_id]);
if ($recruitment_result['success'])
    $recruitment_data = $recruitment_result['data'];

// ── Build XLSX with 6 sheets ──

$filename = 'Company_Reports_' . date('Y-m-d_His') . '.xlsx';
$tmpFile = tempnam(sys_get_temp_dir(), 'xlsx');

$zip = new ZipArchive();
if ($zip->open($tmpFile, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
    http_response_code(500);
    echo 'Failed to create export file.';
    exit();
}

$sheetNames = [
    'Departments',
    'Designations',
    'Attendance Trends',
    'Leave Distribution',
    'Payroll Trends',
    'Recruitment Funnel'
];
$sheetCount = count($sheetNames);

// [Content_Types].xml
$contentTypes = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">
    <Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>
    <Default Extension="xml" ContentType="application/xml"/>
    <Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/>';
for ($i = 1; $i <= $sheetCount; $i++) {
    $contentTypes .= '<Override PartName="/xl/worksheets/sheet' . $i . '.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/>';
}
$contentTypes .= '<Override PartName="/xl/styles.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.styles+xml"/>
    <Override PartName="/xl/sharedStrings.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sharedStrings+xml"/>
</Types>';
$zip->addFromString('[Content_Types].xml', $contentTypes);

// _rels/.rels
$zip->addFromString('_rels/.rels', '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">
    <Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/>
</Relationships>');

// xl/_rels/workbook.xml.rels
$wbRels = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">';
for ($i = 1; $i <= $sheetCount; $i++) {
    $wbRels .= '<Relationship Id="rId' . $i . '" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet' . $i . '.xml"/>';
}
$wbRels .= '<Relationship Id="rId' . ($sheetCount + 1) . '" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/styles" Target="styles.xml"/>';
$wbRels .= '<Relationship Id="rId' . ($sheetCount + 2) . '" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/sharedStrings" Target="sharedStrings.xml"/>';
$wbRels .= '</Relationships>';
$zip->addFromString('xl/_rels/workbook.xml.rels', $wbRels);

// xl/workbook.xml
$workbook = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">
    <sheets>';
for ($i = 0; $i < $sheetCount; $i++) {
    $workbook .= '<sheet name="' . htmlspecialchars($sheetNames[$i], ENT_XML1, 'UTF-8') . '" sheetId="' . ($i + 1) . '" r:id="rId' . ($i + 1) . '"/>';
}
$workbook .= '</sheets></workbook>';
$zip->addFromString('xl/workbook.xml', $workbook);

// xl/styles.xml
$styles = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
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
    <cellStyleXfs count="1">
        <xf numFmtId="0" fontId="0" fillId="0" borderId="0"/>
    </cellStyleXfs>
    <cellXfs count="3">
        <xf numFmtId="0" fontId="0" fillId="0" borderId="0" xfId="0"/>
        <xf numFmtId="0" fontId="1" fillId="2" borderId="1" xfId="0" applyFont="1" applyFill="1" applyBorder="1" applyAlignment="1">
            <alignment horizontal="center" vertical="center"/>
        </xf>
        <xf numFmtId="0" fontId="0" fillId="0" borderId="1" xfId="0" applyBorder="1"/>
    </cellXfs>
</styleSheet>';
$zip->addFromString('xl/styles.xml', $styles);

// ── Shared Strings ──
$sharedStrings = [];
$ssIndex = 0;
$ssMap = [];

function getSSIndex($value, &$sharedStrings, &$ssIndex, &$ssMap)
{
    $val = (string) $value;
    if (!isset($ssMap[$val])) {
        $ssMap[$val] = $ssIndex;
        $sharedStrings[] = $val;
        $ssIndex++;
    }
    return $ssMap[$val];
}

function colLetter($index)
{
    $letter = '';
    while ($index >= 0) {
        $letter = chr(65 + ($index % 26)) . $letter;
        $index = intval($index / 26) - 1;
    }
    return $letter;
}

function buildSheet($headers, $rows, $colWidths, &$sharedStrings, &$ssIndex, &$ssMap)
{
    // Pre-register all strings
    foreach ($headers as $h)
        getSSIndex($h, $sharedStrings, $ssIndex, $ssMap);
    foreach ($rows as $row) {
        foreach ($row as $val)
            getSSIndex((string) $val, $sharedStrings, $ssIndex, $ssMap);
    }

    $xml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>';
    $xml .= '<worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">';

    // Column widths
    $xml .= '<cols>';
    foreach ($colWidths as $i => $w) {
        $col = $i + 1;
        $xml .= '<col min="' . $col . '" max="' . $col . '" width="' . $w . '" customWidth="1"/>';
    }
    $xml .= '</cols>';

    $xml .= '<sheetData>';

    // Header row
    $xml .= '<row r="1">';
    foreach ($headers as $i => $h) {
        $idx = getSSIndex($h, $sharedStrings, $ssIndex, $ssMap);
        $xml .= '<c r="' . colLetter($i) . '1" t="s" s="1"><v>' . $idx . '</v></c>';
    }
    $xml .= '</row>';

    // Data rows
    $rowNum = 2;
    foreach ($rows as $row) {
        $xml .= '<row r="' . $rowNum . '">';
        foreach ($row as $i => $val) {
            $idx = getSSIndex((string) $val, $sharedStrings, $ssIndex, $ssMap);
            $xml .= '<c r="' . colLetter($i) . $rowNum . '" t="s" s="2"><v>' . $idx . '</v></c>';
        }
        $xml .= '</row>';
        $rowNum++;
    }

    $xml .= '</sheetData></worksheet>';
    return $xml;
}

// ── Sheet 1: Departments ──
$rows1 = [];
foreach ($dept_data as $d) {
    $rows1[] = [$d['name'], $d['count']];
}
$sheet1 = buildSheet(['Department', 'Employee Count'], $rows1, [30, 18], $sharedStrings, $ssIndex, $ssMap);
$zip->addFromString('xl/worksheets/sheet1.xml', $sheet1);

// ── Sheet 2: Designations ──
$rows2 = [];
foreach ($desig_data as $d) {
    $rows2[] = [$d['name'], $d['count']];
}
$sheet2 = buildSheet(['Designation', 'Employee Count'], $rows2, [30, 18], $sharedStrings, $ssIndex, $ssMap);
$zip->addFromString('xl/worksheets/sheet2.xml', $sheet2);

// ── Sheet 3: Attendance Trends ──
$rows3 = [];
foreach ($attendance_data as $a) {
    $perc = $a['total_attendance'] > 0 ? round(($a['present_count'] / $a['total_attendance']) * 100, 1) : 0;
    $rows3[] = [$a['date'], $a['present_count'], $a['total_attendance'], $perc . '%'];
}
$sheet3 = buildSheet(['Date', 'Present', 'Total', 'Presence %'], $rows3, [16, 14, 14, 14], $sharedStrings, $ssIndex, $ssMap);
$zip->addFromString('xl/worksheets/sheet3.xml', $sheet3);

// ── Sheet 4: Leave Distribution ──
$rows4 = [];
foreach ($leave_data as $l) {
    $rows4[] = [ucfirst($l['status']), $l['count']];
}
$sheet4 = buildSheet(['Leave Status', 'Count'], $rows4, [22, 14], $sharedStrings, $ssIndex, $ssMap);
$zip->addFromString('xl/worksheets/sheet4.xml', $sheet4);

// ── Sheet 5: Payroll Trends ──
$rows5 = [];
foreach ($payroll_data as $p) {
    $rows5[] = [
        date('M Y', strtotime($p['period'] . '-01')),
        number_format((float) $p['total_payout'], 2),
        $p['payslip_count']
    ];
}
$sheet5 = buildSheet(['Period', 'Total Payout', 'Payslips Generated'], $rows5, [18, 20, 20], $sharedStrings, $ssIndex, $ssMap);
$zip->addFromString('xl/worksheets/sheet5.xml', $sheet5);

// ── Sheet 6: Recruitment Funnel ──
$rows6 = [];
foreach ($recruitment_data as $r) {
    $rows6[] = [ucfirst($r['status']), $r['count']];
}
$sheet6 = buildSheet(['Application Status', 'Count'], $rows6, [22, 14], $sharedStrings, $ssIndex, $ssMap);
$zip->addFromString('xl/worksheets/sheet6.xml', $sheet6);

// ── Shared Strings XML ──
$ssXml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>';
$ssXml .= '<sst xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" count="' . count($sharedStrings) . '" uniqueCount="' . count($sharedStrings) . '">';
foreach ($sharedStrings as $s) {
    $ssXml .= '<si><t>' . htmlspecialchars($s, ENT_XML1, 'UTF-8') . '</t></si>';
}
$ssXml .= '</sst>';
$zip->addFromString('xl/sharedStrings.xml', $ssXml);

$zip->close();

// Output
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment; filename="' . $filename . '"');
header('Content-Length: ' . filesize($tmpFile));
header('Cache-Control: max-age=0');

readfile($tmpFile);
unlink($tmpFile);
exit();
