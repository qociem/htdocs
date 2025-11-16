<?php
// create_user.php - gunakan untuk membuat user di DB
require_once 'db.php'; // pastikan db.php berfungsi
$username = 'kosim';
$password = 'berbagi0'; // ganti
$hash = password_hash($password, PASSWORD_DEFAULT);

$stmt = $pdo->prepare("INSERT INTO users (username, password_hash) VALUES (?, ?)");
try {
    $stmt->execute([$username, $hash]);
    echo "User created: $username\n";
} catch (Exception $e) {
    echo "Gagal: " . $e->getMessage();
}
