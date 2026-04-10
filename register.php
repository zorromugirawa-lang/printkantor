<?php
require 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama       = trim($_POST['nama_lengkap']);
    $kode       = strtoupper(trim($_POST['kode_karyawan']));
    $username   = trim($_POST['username']);
    $password   = $_POST['password'];

    if (strlen($password) < 6) {
        echo "<script>alert('Password minimal 6 karakter!'); history.back();</script>";
        exit;
    }

    $hashed = password_hash($password, PASSWORD_DEFAULT);

    try {
        $stmt = $pdo->prepare("INSERT INTO karyawan (kode_karyawan, nama_lengkap, username, password) VALUES (?, ?, ?, ?)");
        $stmt->execute([$kode, $nama, $username, $hashed]);
        
        echo "<script>alert('Akun berhasil dibuat! Silakan login.'); window.location='index.php';</script>";
    } catch (PDOException $e) {
        echo "<script>alert('Kode Karyawan atau Username sudah digunakan!'); history.back();</script>";
    }
}
?>