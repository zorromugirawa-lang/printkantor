<?php
session_start();
require 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    $stmt = $pdo->prepare("SELECT * FROM karyawan WHERE username = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id']   = $user['id'];
        $_SESSION['nama']      = $user['nama_lengkap'];
        $_SESSION['kode']      = $user['kode_karyawan'];
        $_SESSION['role']      = $user['role'];

        header("Location: dashboard.php");
        exit;
    } else {
        echo "<script>alert('Username atau Password salah!'); window.location='index.php';</script>";
    }
}
?>