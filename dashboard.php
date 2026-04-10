<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}
require_once 'config.php';

// Fetch Statistics
$total_produk = $pdo->query("SELECT COUNT(*) FROM produk")->fetchColumn();
$order_hari_ini = $pdo->query("SELECT COUNT(*) FROM pesanan WHERE DATE(tanggal) = CURDATE()")->fetchColumn();
$pending_order = $pdo->query("SELECT COUNT(*) FROM pesanan WHERE status = 'Pending'")->fetchColumn();
$pendapatan_hari_ini = $pdo->query("SELECT SUM(total) FROM pesanan WHERE DATE(tanggal) = CURDATE()")->fetchColumn() ?: 0;
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <title>PrintKantor - Dashboard</title>
    <?php include 'includes/head.php'; ?>
</head>
<body class="bg-gray-50 uppercase-text-none">
    <div class="flex">
        <!-- Sidebar -->
        <?php include 'includes/sidebar.php'; ?>

        <!-- Main Content -->
        <div class="flex-1 p-10 h-screen overflow-auto">
            <h1 class="text-4xl font-extrabold text-gray-800">Selamat Datang, <?php echo htmlspecialchars($_SESSION['nama']); ?> 👋</h1>
            <p class="text-lg text-gray-600 mt-2">Sistem Manajemen Penjualan Alat Kantor & Printing</p>

            <div class="mt-12 grid grid-cols-1 md:grid-cols-4 gap-6">
                <div class="bg-white rounded-3xl p-6 shadow-sm border border-gray-100 flex items-center justify-between">
                    <div>
                        <h3 class="text-gray-500 text-sm font-semibold uppercase tracking-wider">Total Produk</h3>
                        <p class="text-4xl font-black text-[#1e40af] mt-2"><?php echo $total_produk; ?></p>
                    </div>
                    <div class="text-blue-100 text-5xl"><i class="fas fa-boxes"></i></div>
                </div>
                <div class="bg-white rounded-3xl p-6 shadow-sm border border-gray-100 flex items-center justify-between">
                    <div>
                        <h3 class="text-gray-500 text-sm font-semibold uppercase tracking-wider">Order Hari Ini</h3>
                        <p class="text-4xl font-black text-emerald-600 mt-2"><?php echo $order_hari_ini; ?></p>
                    </div>
                    <div class="text-emerald-100 text-5xl"><i class="fas fa-shopping-cart"></i></div>
                </div>
                <div class="bg-white rounded-3xl p-6 shadow-sm border border-gray-100 flex items-center justify-between ring-2 ring-amber-500 ring-offset-2">
                    <div>
                        <h3 class="text-amber-600 text-sm font-semibold uppercase tracking-wider">Pending Order</h3>
                        <p class="text-4xl font-black text-amber-600 mt-2"><?php echo $pending_order; ?></p>
                    </div>
                    <div class="text-amber-100 text-5xl"><i class="fas fa-clock"></i></div>
                </div>
                <div class="bg-white rounded-3xl p-6 shadow-sm border border-gray-100 flex items-center justify-between">
                    <div>
                        <h3 class="text-gray-500 text-sm font-semibold uppercase tracking-wider">Omset Hari Ini</h3>
                        <p class="text-2xl font-black text-gray-800 mt-2">Rp <?php echo number_format($pendapatan_hari_ini, 0, ',', '.'); ?></p>
                    </div>
                    <div class="text-gray-100 text-5xl"><i class="fas fa-wallet"></i></div>
                </div>
            </div>

            <div class="mt-10 bg-white rounded-3xl p-8 shadow-sm border border-gray-100">
                <h2 class="text-xl font-semibold mb-6 flex items-center gap-2">
                    <i class="fas fa-info-circle text-blue-600"></i> Status Sistem
                </h2>
                <div class="flex items-center gap-4 bg-blue-50 p-4 rounded-2xl">
                    <div class="w-12 h-12 bg-blue-600 text-white rounded-full flex items-center justify-center text-xl">
                        <i class="fas fa-user-shield"></i>
                    </div>
                    <div>
                        <p class="text-gray-700">Anda masuk sebagai <strong><?php echo ucfirst($_SESSION['role']); ?></strong></p>
                        <p class="text-sm text-gray-500">Akses penuh ke modul Stok, Kasir, dan Laporan.</p>
                    </div>
                </div>
                <div class="mt-8 grid grid-cols-1 md:grid-cols-2 gap-4">
                    <a href="kasir.php" class="p-6 bg-gradient-to-r from-blue-600 to-blue-700 text-white rounded-2xl flex items-center justify-between group hover:shadow-lg transition">
                        <div>
                            <h4 class="font-bold text-lg">Mulai Kasir</h4>
                            <p class="text-blue-100 text-sm">Buka antarmuka penjualan</p>
                        </div>
                        <i class="fas fa-arrow-right transform group-hover:translate-x-2 transition"></i>
                    </a>
                    <a href="orderan.php" class="p-6 bg-amber-500 text-white rounded-2xl flex items-center justify-between group hover:shadow-lg transition">
                        <div>
                            <h4 class="font-bold text-lg">Cek Pesanan (<?php echo $pending_order; ?>)</h4>
                            <p class="text-amber-100 text-sm">Lihat pekerjaan pending</p>
                        </div>
                        <i class="fas fa-tasks transform group-hover:translate-x-2 transition"></i>
                    </a>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
l>