<?php
$host = 'localhost';
$db   = 'printkantor';
$user = 'root';
$pass = '';   // kosongkan jika pakai XAMPP default

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Koneksi gagal: " . $e->getMessage());
}
?>