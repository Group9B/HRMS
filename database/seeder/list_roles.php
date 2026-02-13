<?php
$host = 'localhost';
$db = 'original_template';
$user = 'root';
$pass = '';
$dsn = "mysql:host=$host;dbname=$db;charset=utf8mb4";
$pdo = new PDO($dsn, $user, $pass);

$stmt = $pdo->query("SELECT * FROM roles");
$roles = $stmt->fetchAll(PDO::FETCH_ASSOC);

$file = 'C:\xampp\htdocs\HRMS\database\seeder\roles_list.txt';
$content = "Roles:\n";
foreach ($roles as $role) {
    $content .= $role['id'] . ": " . $role['name'] . "\n";
}
file_put_contents($file, $content);
?>