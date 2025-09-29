<?php
require_once '../config/db.php';
require_once '../includes/functions.php';

// Security: Must be logged in to view any document
if (!isLoggedIn()) {
    http_response_code(403);
    die('Access Denied. Please log in.');
}

$doc_id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
// All users can only see documents from their own company
$company_id = $_SESSION['company_id'];

if ($doc_id <= 0) {
    http_response_code(400);
    die('Invalid document ID specified.');
}

// Fetch the document path from the database, ensuring it belongs to the user's company and is a policy document
$result = query($mysqli, "SELECT file_path FROM documents WHERE id = ? AND company_id = ? AND related_type = 'policy'", [$doc_id, $company_id]);

if (!$result['success'] || empty($result['data'])) {
    http_response_code(404);
    die('Document not found or you do not have permission to view it.');
}

$relative_path = $result['data'][0]['file_path'];
// Convert the web-accessible path to a server file system path
$file_path = str_replace("/hrms", realpath(__DIR__ . '/..'), $relative_path);

if (!file_exists($file_path)) {
    http_response_code(404);
    die('The requested file could not be found on the server.');
}

// Securely serve the file to the browser
header('Content-Type: application/pdf');
header('Content-Disposition: inline; filename="' . basename($relative_path) . '"');
header('Content-Length: ' . filesize($file_path));
readfile($file_path);
exit();

