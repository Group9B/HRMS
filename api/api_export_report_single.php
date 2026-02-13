<?php
/**
 * Single Chart Excel Export API
 * Generates a single-sheet .xlsx file for a specific report/chart.
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
$type = $_GET['type'] ?? '';

if (!$type) {
    http_response_code(400);
    echo 'Report type is required.';
    exit();
}

$data = [];
$filename_prefix = 'Report';
$headers = [];
$rows = [];

// ── Fetch Data Based on Type ──

switch ($type) {
    case 'dept':
        $filename_prefix = 'Department_Distribution';
        $headers = ['Department', 'Employee Count'];
        $result = query($mysqli, "
            SELECT d.name, COUNT(e.id) as count 
            FROM departments d 
            LEFT JOIN employees e ON d.id = e.department_id AND e.status = 'active'
            WHERE d.company_id = ? 
            GROUP BY d.id, d.name
            ORDER BY count DESC
        ", [$company_id]);
        if ($result['success']) {
            foreach ($result['data'] as $d)
                $rows[] = [$d['name'], $d['count']];
        }
        break;

    case 'desig':
        $filename_prefix = 'Designation_Distribution';
        $headers = ['Designation', 'Employee Count'];
        $result = query($mysqli, "
            SELECT ds.name, COUNT(e.id) as count 
            FROM designations ds 
            INNER JOIN departments d ON ds.department_id = d.id
            LEFT JOIN employees e ON ds.id = e.designation_id AND e.status = 'active'
            WHERE d.company_id = ? 
            GROUP BY ds.id, ds.name
            HAVING count > 0
            ORDER BY count DESC
        ", [$company_id]);
        if ($result['success']) {
            foreach ($result['data'] as $d)
                $rows[] = [$d['name'], $d['count']];
        }
        break;

    case 'attendance':
        $filename_prefix = 'Attendance_Trends';
        $headers = ['Date', 'Present', 'Total', 'Presence %'];
        $result = query($mysqli, "
            SELECT date, 
                   COUNT(CASE WHEN status = 'present' THEN 1 END) as present_count,
                   COUNT(id) as total_attendance
            FROM attendance 
            WHERE employee_id IN (SELECT id FROM employees WHERE company_id = ?)
              AND date >= DATE_SUB(CURDATE(), INTERVAL 15 DAY)
            GROUP BY date 
            ORDER BY date ASC
        ", [$company_id]);
        if ($result['success']) {
            foreach ($result['data'] as $a) {
                $perc = $a['total_attendance'] > 0 ? round(($a['present_count'] / $a['total_attendance']) * 100, 1) : 0;
                $rows[] = [$a['date'], $a['present_count'], $a['total_attendance'], $perc . '%'];
            }
        }
        break;

    case 'leave':
        $filename_prefix = 'Leave_Distribution';
        $headers = ['Leave Status', 'Count'];
        $result = query($mysqli, "
            SELECT status, COUNT(id) as count 
            FROM leaves 
            WHERE employee_id IN (SELECT id FROM employees WHERE company_id = ?)
            GROUP BY status
        ", [$company_id]);
        if ($result['success']) {
            foreach ($result['data'] as $l)
                $rows[] = [ucfirst($l['status']), $l['count']];
        }
        break;

    case 'payroll':
        $filename_prefix = 'Payroll_Trends';
        $headers = ['Period', 'Total Payout', 'Payslips Generated'];
        $result = query($mysqli, "
            SELECT period, SUM(net_salary) as total_payout, COUNT(id) as payslip_count
            FROM payslips 
            WHERE company_id = ? AND status != 'cancelled'
              AND period >= DATE_FORMAT(DATE_SUB(CURDATE(), INTERVAL 6 MONTH), '%Y-%m')
            GROUP BY period 
            ORDER BY period ASC
        ", [$company_id]);
        if ($result['success']) {
            foreach ($result['data'] as $p) {
                $rows[] = [
                    date('M Y', strtotime($p['period'] . '-01')),
                    number_format((float) $p['total_payout'], 2),
                    $p['payslip_count']
                ];
            }
        }
        break;

    case 'recruitment':
        $filename_prefix = 'Recruitment_Funnel';
        $headers = ['Application Status', 'Count'];
        $result = query($mysqli, "
            SELECT status, COUNT(id) as count 
            FROM job_applications 
            WHERE job_id IN (SELECT id FROM jobs WHERE company_id = ?)
            GROUP BY status
        ", [$company_id]);
        if ($result['success']) {
            foreach ($result['data'] as $r)
                $rows[] = [ucfirst($r['status']), $r['count']];
        }
        break;

    default:
        http_response_code(400);
        echo 'Invalid report type.';
        exit();
}

// ── Build Single-Sheet XLSX ──

$filename = $filename_prefix . '_' . date('Y-m-d_His') . '.xlsx';
$tmpFile = tempnam(sys_get_temp_dir(), 'xlsx');

$zip = new ZipArchive();
if ($zip->open($tmpFile, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
    http_response_code(500);
    echo 'Failed to create export file.';
    exit();
}

// [Content_Types].xml
$contentTypes = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">
    <Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>
    <Default Extension="xml" ContentType="application/xml"/>
    <Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/>
    <Override PartName="/xl/worksheets/sheet1.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/>
    <Override PartName="/xl/styles.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.styles+xml"/>
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
<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">
    <Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet1.xml"/>
    <Relationship Id="rId2" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/styles" Target="styles.xml"/>
    <Relationship Id="rId3" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/sharedStrings" Target="sharedStrings.xml"/>
</Relationships>';
$zip->addFromString('xl/_rels/workbook.xml.rels', $wbRels);

// xl/workbook.xml
$workbook = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">
    <sheets>
        <sheet name="Report Data" sheetId="1" r:id="rId1"/>
    </sheets>
</workbook>';
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

// Build Sheet Data
foreach ($headers as $h)
    getSSIndex($h, $sharedStrings, $ssIndex, $ssMap);
foreach ($rows as $row) {
    foreach ($row as $val)
        getSSIndex((string) $val, $sharedStrings, $ssIndex, $ssMap);
}

$xml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>';
$xml .= '<worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">';
$xml .= '<cols>';
$xml .= '<col min="1" max="1" width="25" customWidth="1"/>'; // Col A width
$xml .= '<col min="2" max="5" width="18" customWidth="1"/>'; // Other cols width
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
$zip->addFromString('xl/worksheets/sheet1.xml', $xml);

// Shared Strings XML
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
