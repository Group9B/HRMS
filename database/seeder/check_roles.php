<?php
// DB Config
$host = 'localhost';
$db = 'original_template';
$user = 'root';
$pass = '';
$dsn = "mysql:host=$host;dbname=$db;charset=utf8mb4";
$pdo = new PDO($dsn, $user, $pass);

$stmt = $pdo->query("SELECT * FROM roles");
$roles = $stmt->fetchAll(PDO::FETCH_ASSOC);

$file = __DIR__ . '/roles_dump.txt';
$content = "Roles in DB:\n";
foreach ($roles as $role) {
    $content .= "ID: " . $role['id'] . " - Name: " . $role['name'] . "\n";
}
file_put_contents($file, $content);
echo "Dumped to $file\n";
?>