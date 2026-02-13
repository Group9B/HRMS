<?php
/**
 * Payslips Excel Export API
 * Exports all payslips as .xlsx using native PHP ZipArchive + XML.
 */

session_start();
require_once '../config/db.php';
require_once '../includes/functions.php';

if (!isLoggedIn() || !in_array($_SESSION['role_id'], [1, 2, 3])) {
    http_response_code(403);
    echo 'Unauthorized access.';
    exit();
}

$company_id = $_SESSION['company_id'];

$sql = "SELECT p.*, e.first_name, e.last_name, e.employee_code
        FROM payslips p
        JOIN employees e ON p.employee_id = e.id
        WHERE p.company_id = ?
        ORDER BY p.generated_at DESC";
$result = query($mysqli, $sql, [$company_id]);

if (!$result['success']) {
    http_response_code(500);
    echo 'Failed to fetch payslip data.';
    exit();
}

$payslips = $result['data'];
$headers = ['Employee', 'Code', 'Period', 'Currency', 'Gross Salary', 'Net Salary', 'Status', 'Generated At'];
$colLetters = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H'];
$colWidths = [25, 14, 12, 10, 16, 16, 12, 20];

$filename = 'Payslips_Export_' . date('Y-m-d_His') . '.xlsx';
$tmpFile = tempnam(sys_get_temp_dir(), 'xlsx');
$zip = new ZipArchive();
if ($zip->open($tmpFile, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
    http_response_code(500);
    echo 'Failed to create export file.';
    exit();
}

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
$zip->addFromString('xl/workbook.xml', '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">
    <sheets><sheet name="Payslips" sheetId="1" r:id="rId1"/></sheets>
</workbook>');
$zip->addFromString('xl/styles.xml', '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<styleSheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">
    <fonts count="2"><font><sz val="11"/><name val="Calibri"/></font><font><b/><sz val="11"/><color rgb="FFFFFFFF"/><name val="Calibri"/></font></fonts>
    <fills count="3"><fill><patternFill patternType="none"/></fill><fill><patternFill patternType="gray125"/></fill><fill><patternFill patternType="solid"><fgColor rgb="FF4472C4"/></patternFill></fill></fills>
    <borders count="2"><border><left/><right/><top/><bottom/><diagonal/></border><border><left style="thin"><color auto="1"/></left><right style="thin"><color auto="1"/></right><top style="thin"><color auto="1"/></top><bottom style="thin"><color auto="1"/></bottom><diagonal/></border></borders>
    <cellStyleXfs count="1"><xf numFmtId="0" fontId="0" fillId="0" borderId="0"/></cellStyleXfs>
    <cellXfs count="3">
        <xf numFmtId="0" fontId="0" fillId="0" borderId="0" xfId="0"/>
        <xf numFmtId="0" fontId="1" fillId="2" borderId="1" xfId="0" applyFont="1" applyFill="1" applyBorder="1" applyAlignment="1"><alignment horizontal="center" vertical="center"/></xf>
        <xf numFmtId="0" fontId="0" fillId="0" borderId="1" xfId="0" applyBorder="1"/>
    </cellXfs>
</styleSheet>');

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

foreach ($headers as $h) {
    getSSIndex($h, $sharedStrings, $ssIndex, $ssMap);
}
foreach ($payslips as $p) {
    getSSIndex(trim(($p['first_name'] ?? '') . ' ' . ($p['last_name'] ?? '')), $sharedStrings, $ssIndex, $ssMap);
    getSSIndex($p['employee_code'] ?? '', $sharedStrings, $ssIndex, $ssMap);
    getSSIndex($p['period'] ?? '', $sharedStrings, $ssIndex, $ssMap);
    getSSIndex($p['currency'] ?? 'INR', $sharedStrings, $ssIndex, $ssMap);
    getSSIndex(number_format((float) ($p['gross_salary'] ?? 0), 2), $sharedStrings, $ssIndex, $ssMap);
    getSSIndex(number_format((float) ($p['net_salary'] ?? 0), 2), $sharedStrings, $ssIndex, $ssMap);
    getSSIndex(ucfirst($p['status'] ?? 'generated'), $sharedStrings, $ssIndex, $ssMap);
    getSSIndex($p['generated_at'] ?? '', $sharedStrings, $ssIndex, $ssMap);
}

$ssXml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>';
$ssXml .= '<sst xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" count="' . count($sharedStrings) . '" uniqueCount="' . count($sharedStrings) . '">';
foreach ($sharedStrings as $s) {
    $ssXml .= '<si><t>' . htmlspecialchars($s, ENT_XML1, 'UTF-8') . '</t></si>';
}
$ssXml .= '</sst>';
$zip->addFromString('xl/sharedStrings.xml', $ssXml);

$sheetXml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main"><cols>';
foreach ($colWidths as $i => $w) {
    $col = $i + 1;
    $sheetXml .= '<col min="' . $col . '" max="' . $col . '" width="' . $w . '" customWidth="1"/>';
}
$sheetXml .= '</cols><sheetData>';

$sheetXml .= '<row r="1">';
foreach ($headers as $i => $h) {
    $idx = getSSIndex($h, $sharedStrings, $ssIndex, $ssMap);
    $sheetXml .= '<c r="' . $colLetters[$i] . '1" t="s" s="1"><v>' . $idx . '</v></c>';
}
$sheetXml .= '</row>';

$rowNum = 2;
foreach ($payslips as $p) {
    $sheetXml .= '<row r="' . $rowNum . '">';
    $values = [
        trim(($p['first_name'] ?? '') . ' ' . ($p['last_name'] ?? '')),
        $p['employee_code'] ?? '',
        $p['period'] ?? '',
        $p['currency'] ?? 'INR',
        number_format((float) ($p['gross_salary'] ?? 0), 2),
        number_format((float) ($p['net_salary'] ?? 0), 2),
        ucfirst($p['status'] ?? 'generated'),
        $p['generated_at'] ?? ''
    ];
    foreach ($values as $i => $val) {
        $idx = getSSIndex($val, $sharedStrings, $ssIndex, $ssMap);
        $sheetXml .= '<c r="' . $colLetters[$i] . $rowNum . '" t="s" s="2"><v>' . $idx . '</v></c>';
    }
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
