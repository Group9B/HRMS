<?php
/**
 * Recruitment Excel Export API
 * Exports all job applications as .xlsx using native PHP ZipArchive + XML.
 */
require_once '../config/db.php';
require_once '../includes/functions.php';

if (!isLoggedIn() || !in_array($_SESSION['role_id'], [2, 3])) {
    http_response_code(403);
    echo 'Unauthorized access.';
    exit();
}
$company_id = $_SESSION['company_id'];

$sql = "SELECT ja.id as application_id, c.first_name, c.last_name, c.email, c.phone,
               j.title as job_title, d.name as department_name, ja.status, ja.applied_at
        FROM job_applications ja
        JOIN candidates c ON ja.candidate_id = c.id
        JOIN jobs j ON ja.job_id = j.id
        LEFT JOIN departments d ON j.department_id = d.id
        WHERE j.company_id = ?
        ORDER BY ja.applied_at DESC";
$result = query($mysqli, $sql, [$company_id]);
if (!$result['success']) {
    http_response_code(500);
    echo 'Failed to fetch recruitment data.';
    exit();
}

$apps = $result['data'];
$headers = ['ID', 'Candidate Name', 'Email', 'Phone', 'Job Title', 'Department', 'Status', 'Applied At'];
$colLetters = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H'];
$colWidths = [8, 25, 28, 16, 25, 20, 14, 20];
$filename = 'Recruitment_Export_' . date('Y-m-d_His') . '.xlsx';
$tmpFile = tempnam(sys_get_temp_dir(), 'xlsx');
$zip = new ZipArchive();
if ($zip->open($tmpFile, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
    http_response_code(500);
    echo 'Failed to create export file.';
    exit();
}

$zip->addFromString('[Content_Types].xml', '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types"><Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/><Default Extension="xml" ContentType="application/xml"/><Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/><Override PartName="/xl/worksheets/sheet1.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/><Override PartName="/xl/styles.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.styles+xml"/><Override PartName="/xl/sharedStrings.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sharedStrings+xml"/></Types>');
$zip->addFromString('_rels/.rels', '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships"><Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/></Relationships>');
$zip->addFromString('xl/_rels/workbook.xml.rels', '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships"><Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet1.xml"/><Relationship Id="rId2" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/styles" Target="styles.xml"/><Relationship Id="rId3" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/sharedStrings" Target="sharedStrings.xml"/></Relationships>');
$zip->addFromString('xl/workbook.xml', '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships"><sheets><sheet name="Applications" sheetId="1" r:id="rId1"/></sheets></workbook>');
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
foreach ($apps as $a) {
    idx($a['application_id'], $ss, $si, $sm);
    idx(trim(($a['first_name'] ?? '') . ' ' . ($a['last_name'] ?? '')), $ss, $si, $sm);
    idx($a['email'] ?? '', $ss, $si, $sm);
    idx($a['phone'] ?? '', $ss, $si, $sm);
    idx($a['job_title'] ?? '', $ss, $si, $sm);
    idx($a['department_name'] ?? 'N/A', $ss, $si, $sm);
    idx(ucfirst($a['status'] ?? ''), $ss, $si, $sm);
    idx($a['applied_at'] ?? '', $ss, $si, $sm);
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
foreach ($apps as $a) {
    $sh .= '<row r="' . $r . '">';
    $vals = [$a['application_id'], trim(($a['first_name'] ?? '') . ' ' . ($a['last_name'] ?? '')), $a['email'] ?? '', $a['phone'] ?? '', $a['job_title'] ?? '', $a['department_name'] ?? 'N/A', ucfirst($a['status'] ?? ''), $a['applied_at'] ?? ''];
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
