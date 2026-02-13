<?php
/**
 * Assets Excel Export API
 * Exports all assets as .xlsx using native PHP ZipArchive + XML.
 */
require_once '../config/db.php';
require_once '../includes/functions.php';

if (!isLoggedIn() || !in_array($_SESSION['role_id'], [2, 3])) {
    http_response_code(403);
    echo 'Unauthorized access.';
    exit();
}
$company_id = $_SESSION['company_id'];

$sql = "SELECT a.*, ac.name as category_name, ac.type as category_type,
               CONCAT(e.first_name, ' ', e.last_name) as assigned_to_name
        FROM assets a
        LEFT JOIN asset_categories ac ON a.category_id = ac.id
        LEFT JOIN asset_assignments aa ON a.id = aa.asset_id AND aa.status = 'Active'
        LEFT JOIN employees e ON aa.employee_id = e.id
        WHERE a.company_id = ?
        ORDER BY a.created_at DESC";
$result = query($mysqli, $sql, [$company_id]);
if (!$result['success']) {
    http_response_code(500);
    echo 'Failed to fetch asset data.';
    exit();
}

$assets = $result['data'];
$headers = ['Asset Name', 'Category', 'Type', 'Asset Tag', 'Serial Number', 'Status', 'Condition', 'Assigned To', 'Purchase Date', 'Purchase Cost', 'Warranty Expiry'];
$colLetters = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K'];
$colWidths = [25, 18, 14, 16, 20, 14, 14, 22, 14, 14, 16];
$filename = 'Assets_Export_' . date('Y-m-d_His') . '.xlsx';
$tmpFile = tempnam(sys_get_temp_dir(), 'xlsx');
$zip = new ZipArchive();
if ($zip->open($tmpFile, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
    http_response_code(500);
    echo 'Failed to create export file.';
    exit();
}

// Boilerplate OOXML
$zip->addFromString('[Content_Types].xml', '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types"><Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/><Default Extension="xml" ContentType="application/xml"/><Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/><Override PartName="/xl/worksheets/sheet1.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/><Override PartName="/xl/styles.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.styles+xml"/><Override PartName="/xl/sharedStrings.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sharedStrings+xml"/></Types>');
$zip->addFromString('_rels/.rels', '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships"><Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/></Relationships>');
$zip->addFromString('xl/_rels/workbook.xml.rels', '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships"><Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet1.xml"/><Relationship Id="rId2" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/styles" Target="styles.xml"/><Relationship Id="rId3" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/sharedStrings" Target="sharedStrings.xml"/></Relationships>');
$zip->addFromString('xl/workbook.xml', '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships"><sheets><sheet name="Assets" sheetId="1" r:id="rId1"/></sheets></workbook>');
$zip->addFromString('xl/styles.xml', '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><styleSheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main"><fonts count="2"><font><sz val="11"/><name val="Calibri"/></font><font><b/><sz val="11"/><color rgb="FFFFFFFF"/><name val="Calibri"/></font></fonts><fills count="3"><fill><patternFill patternType="none"/></fill><fill><patternFill patternType="gray125"/></fill><fill><patternFill patternType="solid"><fgColor rgb="FF4472C4"/></patternFill></fill></fills><borders count="2"><border><left/><right/><top/><bottom/><diagonal/></border><border><left style="thin"><color auto="1"/></left><right style="thin"><color auto="1"/></right><top style="thin"><color auto="1"/></top><bottom style="thin"><color auto="1"/></bottom><diagonal/></border></borders><cellStyleXfs count="1"><xf numFmtId="0" fontId="0" fillId="0" borderId="0"/></cellStyleXfs><cellXfs count="3"><xf numFmtId="0" fontId="0" fillId="0" borderId="0" xfId="0"/><xf numFmtId="0" fontId="1" fillId="2" borderId="1" xfId="0" applyFont="1" applyFill="1" applyBorder="1" applyAlignment="1"><alignment horizontal="center" vertical="center"/></xf><xf numFmtId="0" fontId="0" fillId="0" borderId="1" xfId="0" applyBorder="1"/></cellXfs></styleSheet>');

$ss = [];
$si = 0;
$sm = [];
function idx($v, &$ss, &$si, &$sm)
{
    $v = (string) $v;
    if (!isset($sm[$v])) {
        $sm[$v] = $si;
        $ss[] = $v;
        $si++;
    }
    return $sm[$v];
}

foreach ($headers as $h)
    idx($h, $ss, $si, $sm);
foreach ($assets as $a) {
    idx($a['asset_name'] ?? '', $ss, $si, $sm);
    idx($a['category_name'] ?? 'N/A', $ss, $si, $sm);
    idx($a['category_type'] ?? 'N/A', $ss, $si, $sm);
    idx($a['asset_tag'] ?? 'N/A', $ss, $si, $sm);
    idx($a['serial_number'] ?? 'N/A', $ss, $si, $sm);
    idx($a['status'] ?? '', $ss, $si, $sm);
    idx($a['condition_status'] ?? '', $ss, $si, $sm);
    idx($a['assigned_to_name'] ?? 'Unassigned', $ss, $si, $sm);
    idx($a['purchase_date'] ?? 'N/A', $ss, $si, $sm);
    idx($a['purchase_cost'] ? number_format((float) $a['purchase_cost'], 2) : 'N/A', $ss, $si, $sm);
    idx($a['warranty_expiry'] ?? 'N/A', $ss, $si, $sm);
}

$sx = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><sst xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" count="' . count($ss) . '" uniqueCount="' . count($ss) . '">';
foreach ($ss as $s)
    $sx .= '<si><t>' . htmlspecialchars($s, ENT_XML1, 'UTF-8') . '</t></si>';
$sx .= '</sst>';
$zip->addFromString('xl/sharedStrings.xml', $sx);

$sh = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main"><cols>';
foreach ($colWidths as $i => $w) {
    $c = $i + 1;
    $sh .= '<col min="' . $c . '" max="' . $c . '" width="' . $w . '" customWidth="1"/>';
}
$sh .= '</cols><sheetData><row r="1">';
foreach ($headers as $i => $h) {
    $sh .= '<c r="' . $colLetters[$i] . '1" t="s" s="1"><v>' . idx($h, $ss, $si, $sm) . '</v></c>';
}
$sh .= '</row>';

$r = 2;
foreach ($assets as $a) {
    $sh .= '<row r="' . $r . '">';
    $vals = [$a['asset_name'] ?? '', $a['category_name'] ?? 'N/A', $a['category_type'] ?? 'N/A', $a['asset_tag'] ?? 'N/A', $a['serial_number'] ?? 'N/A', $a['status'] ?? '', $a['condition_status'] ?? '', $a['assigned_to_name'] ?? 'Unassigned', $a['purchase_date'] ?? 'N/A', $a['purchase_cost'] ? number_format((float) $a['purchase_cost'], 2) : 'N/A', $a['warranty_expiry'] ?? 'N/A'];
    foreach ($vals as $i => $v) {
        $sh .= '<c r="' . $colLetters[$i] . $r . '" t="s" s="2"><v>' . idx($v, $ss, $si, $sm) . '</v></c>';
    }
    $sh .= '</row>';
    $r++;
}
$sh .= '</sheetData></worksheet>';
$zip->addFromString('xl/worksheets/sheet1.xml', $sh);
$zip->close();

header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment; filename="' . $filename . '"');
header('Content-Length: ' . filesize($tmpFile));
header('Cache-Control: max-age=0');
readfile($tmpFile);
unlink($tmpFile);
exit();
