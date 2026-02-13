<?php
/**
 * Admin Reports Excel Export API
 * Generates a multi-sheet .xlsx file with all admin report data.
 * Sheets: Company Directory, Company Usage, User Registration Activity, Employee Status, User Role Distribution.
 * Zero external dependencies required.
 */

require_once '../config/db.php';
require_once '../includes/functions.php';

// Security Check: Super Admin only
if (!isLoggedIn() || !isset($_SESSION['role_id']) || $_SESSION['role_id'] !== 1) {
    http_response_code(403);
    echo 'Unauthorized access.';
    exit();
}

// ── Fetch all report data ──

// 1. Company Directory
$directory_data = [];
$directory_result = query($mysqli, "SELECT name, email, phone, address, created_at FROM companies ORDER BY name ASC");
if ($directory_result['success'])
    $directory_data = $directory_result['data'];

// 2. Company Usage (Users per Company)
$usage_data = [];
$usage_result = query($mysqli, "
    SELECT c.name, COUNT(u.id) as user_count 
    FROM companies c 
    LEFT JOIN users u ON c.id = u.company_id 
    GROUP BY c.name 
    HAVING user_count > 0
    ORDER BY user_count DESC 
    LIMIT 20
");
if ($usage_result['success'])
    $usage_data = $usage_result['data'];

// 3. User Registration Activity (Last 12 months)
$activity_data = [];
$activity_result = query($mysqli, "
    SELECT 
        DATE_FORMAT(created_at, '%Y-%m') as join_month, 
        COUNT(id) as new_users 
    FROM users 
    WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL 12 MONTH) AND company_id IS NOT NULL
    GROUP BY join_month 
    ORDER BY join_month ASC
");
if ($activity_result['success'])
    $activity_data = $activity_result['data'];

// 4. Employee Status Distribution
$emp_status_data = [];
$emp_status_result = query($mysqli, "
    SELECT COALESCE(status, 'active') as status, COUNT(id) as count 
    FROM employees 
    GROUP BY status
");
if ($emp_status_result['success'])
    $emp_status_data = $emp_status_result['data'];

// 5. User Role Distribution
$role_data = [];
$role_result = query($mysqli, "
    SELECT r.name, COUNT(u.id) as count 
    FROM users u 
    LEFT JOIN roles r ON u.role_id = r.id 
    GROUP BY r.id, r.name
");
if ($role_result['success'])
    $role_data = $role_result['data'];

// ── Build XLSX with 5 sheets ──

$filename = 'Admin_Reports_' . date('Y-m-d_His') . '.xlsx';
$tmpFile = tempnam(sys_get_temp_dir(), 'xlsx');

$zip = new ZipArchive();
if ($zip->open($tmpFile, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
    http_response_code(500);
    echo 'Failed to create export file.';
    exit();
}

$sheetNames = [
    'Company Directory',
    'Company Usage',
    'Registration Activity',
    'Employee Status',
    'User Roles'
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
        <fill><patternFill patternType="solid"><fgColor rgb="FF4e73df"/></patternFill></fill>
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
    foreach ($headers as $h)
        getSSIndex($h, $sharedStrings, $ssIndex, $ssMap);
    foreach ($rows as $row) {
        foreach ($row as $val)
            getSSIndex((string) $val, $sharedStrings, $ssIndex, $ssMap);
    }

    $xml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>';
    $xml .= '<worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">';
    $xml .= '<cols>';
    foreach ($colWidths as $i => $w) {
        $col = $i + 1;
        $xml .= '<col min="' . $col . '" max="' . $col . '" width="' . $w . '" customWidth="1"/>';
    }
    $xml .= '</cols><sheetData>';

    // Header
    $xml .= '<row r="1">';
    foreach ($headers as $i => $h) {
        $idx = getSSIndex($h, $sharedStrings, $ssIndex, $ssMap);
        $xml .= '<c r="' . colLetter($i) . '1" t="s" s="1"><v>' . $idx . '</v></c>';
    }
    $xml .= '</row>';

    // Data
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

// ── Sheet 1: Company Directory ──
$rows1 = [];
foreach ($directory_data as $d) {
    $rows1[] = [
        $d['name'],
        $d['email'],
        $d['phone'],
        $d['address'],
        date('Y-m-d', strtotime($d['created_at']))
    ];
}
$sheet1 = buildSheet(['Company Name', 'Email', 'Phone', 'Address', 'Registered On'], $rows1, [30, 25, 15, 30, 15], $sharedStrings, $ssIndex, $ssMap);
$zip->addFromString('xl/worksheets/sheet1.xml', $sheet1);

// ── Sheet 2: Company Usage ──
$rows2 = [];
foreach ($usage_data as $u) {
    $rows2[] = [$u['name'], $u['user_count']];
}
$sheet2 = buildSheet(['Company', 'User Count'], $rows2, [30, 15], $sharedStrings, $ssIndex, $ssMap);
$zip->addFromString('xl/worksheets/sheet2.xml', $sheet2);

// ── Sheet 3: Registration Activity ──
$rows3 = [];
foreach ($activity_data as $a) {
    $rows3[] = [
        date('M Y', strtotime($a['join_month'] . '-01')),
        $a['new_users']
    ];
}
$sheet3 = buildSheet(['Month', 'New Registrations'], $rows3, [20, 18], $sharedStrings, $ssIndex, $ssMap);
$zip->addFromString('xl/worksheets/sheet3.xml', $sheet3);

// ── Sheet 4: Employee Status ──
$rows4 = [];
foreach ($emp_status_data as $status => $count) {
    $rows4[] = [ucfirst($status['status'] ?? $status), $status['count'] ?? $count];
}
$sheet4 = buildSheet(['Status', 'Count'], $rows4, [20, 15], $sharedStrings, $ssIndex, $ssMap);
$zip->addFromString('xl/worksheets/sheet4.xml', $sheet4);

// ── Sheet 5: User Roles ──
$rows5 = [];
foreach ($role_data as $r) {
    $rows5[] = [$r['name'], $r['count']];
}
$sheet5 = buildSheet(['Role', 'User Count'], $rows5, [20, 15], $sharedStrings, $ssIndex, $ssMap);
$zip->addFromString('xl/worksheets/sheet5.xml', $sheet5);

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
