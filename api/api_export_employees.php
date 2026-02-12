<?php
/**
 * Employee Excel Export API
 * Generates a proper .xlsx file using PHP's ZipArchive and XML (OOXML format).
 * Zero external dependencies required.
 */

require_once '../config/db.php';
require_once '../includes/functions.php';

// Security Check: Must be a logged-in Company Admin or HR
if (!isLoggedIn() || !in_array($_SESSION['role_id'], [2, 3])) {
    http_response_code(403);
    echo 'Unauthorized access.';
    exit();
}

$company_id = $_SESSION['company_id'];

// Fetch employee data
$sql = "
    SELECT e.employee_code, e.first_name, e.last_name, 
           d.name as department_name, des.name as designation_name, 
           s.name as shift_name, e.date_of_joining, e.status
    FROM employees e
    LEFT JOIN departments d ON e.department_id = d.id
    LEFT JOIN designations des ON e.designation_id = des.id
    LEFT JOIN shifts s ON e.shift_id = s.id
    WHERE d.company_id = ?
    ORDER BY e.first_name ASC
";
$result = query($mysqli, $sql, [$company_id]);

if (!$result['success']) {
    http_response_code(500);
    echo 'Failed to fetch employee data.';
    exit();
}

$employees = $result['data'];

// Column headers
$headers = ['Employee Code', 'First Name', 'Last Name', 'Department', 'Designation', 'Shift', 'Date of Joining', 'Status'];

// Generate filename
$filename = 'Employees_Export_' . date('Y-m-d_His') . '.xlsx';

// ─── Build XLSX using ZipArchive (OOXML) ───
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
$rels = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">
    <Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/>
</Relationships>';
$zip->addFromString('_rels/.rels', $rels);

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
        <sheet name="Employees" sheetId="1" r:id="rId1"/>
    </sheets>
</workbook>';
$zip->addFromString('xl/workbook.xml', $workbook);

// xl/styles.xml - with header styling
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

// Build shared strings (all text values)
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

// Collect all strings (headers + data)
foreach ($headers as $h) {
    getSSIndex($h, $sharedStrings, $ssIndex, $ssMap);
}
foreach ($employees as $emp) {
    getSSIndex($emp['employee_code'] ?? 'N/A', $sharedStrings, $ssIndex, $ssMap);
    getSSIndex($emp['first_name'] ?? '', $sharedStrings, $ssIndex, $ssMap);
    getSSIndex($emp['last_name'] ?? '', $sharedStrings, $ssIndex, $ssMap);
    getSSIndex($emp['department_name'] ?? 'N/A', $sharedStrings, $ssIndex, $ssMap);
    getSSIndex($emp['designation_name'] ?? 'N/A', $sharedStrings, $ssIndex, $ssMap);
    getSSIndex($emp['shift_name'] ?? 'N/A', $sharedStrings, $ssIndex, $ssMap);
    getSSIndex($emp['date_of_joining'] ?? 'N/A', $sharedStrings, $ssIndex, $ssMap);
    getSSIndex(ucfirst($emp['status'] ?? 'N/A'), $sharedStrings, $ssIndex, $ssMap);
}

// xl/sharedStrings.xml
$ssXml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>';
$ssXml .= '<sst xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" count="' . count($sharedStrings) . '" uniqueCount="' . count($sharedStrings) . '">';
foreach ($sharedStrings as $s) {
    $ssXml .= '<si><t>' . htmlspecialchars($s, ENT_XML1, 'UTF-8') . '</t></si>';
}
$ssXml .= '</sst>';
$zip->addFromString('xl/sharedStrings.xml', $ssXml);

// xl/worksheets/sheet1.xml
$colLetters = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H'];

$sheetXml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>';
$sheetXml .= '<worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">';

// Column widths
$sheetXml .= '<cols>';
$colWidths = [18, 15, 15, 20, 20, 15, 16, 12];
foreach ($colWidths as $i => $w) {
    $col = $i + 1;
    $sheetXml .= '<col min="' . $col . '" max="' . $col . '" width="' . $w . '" customWidth="1"/>';
}
$sheetXml .= '</cols>';

$sheetXml .= '<sheetData>';

// Header row (style index 1 = bold white on blue)
$sheetXml .= '<row r="1">';
foreach ($headers as $i => $h) {
    $idx = getSSIndex($h, $sharedStrings, $ssIndex, $ssMap);
    $sheetXml .= '<c r="' . $colLetters[$i] . '1" t="s" s="1"><v>' . $idx . '</v></c>';
}
$sheetXml .= '</row>';

// Data rows (style index 2 = normal with thin border)
$rowNum = 2;
foreach ($employees as $emp) {
    $sheetXml .= '<row r="' . $rowNum . '">';
    $values = [
        $emp['employee_code'] ?? 'N/A',
        $emp['first_name'] ?? '',
        $emp['last_name'] ?? '',
        $emp['department_name'] ?? 'N/A',
        $emp['designation_name'] ?? 'N/A',
        $emp['shift_name'] ?? 'N/A',
        $emp['date_of_joining'] ?? 'N/A',
        ucfirst($emp['status'] ?? 'N/A')
    ];
    foreach ($values as $i => $val) {
        $idx = getSSIndex($val, $sharedStrings, $ssIndex, $ssMap);
        $sheetXml .= '<c r="' . $colLetters[$i] . $rowNum . '" t="s" s="2"><v>' . $idx . '</v></c>';
    }
    $sheetXml .= '</row>';
    $rowNum++;
}

$sheetXml .= '</sheetData>';
$sheetXml .= '</worksheet>';
$zip->addFromString('xl/worksheets/sheet1.xml', $sheetXml);

$zip->close();

// Output the file
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment; filename="' . $filename . '"');
header('Content-Length: ' . filesize($tmpFile));
header('Cache-Control: max-age=0');

readfile($tmpFile);
unlink($tmpFile);
exit();
