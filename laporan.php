<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}
require_once 'config.php';

// Summary Stats (Total All Time)
$revenue = $pdo->query("SELECT SUM(total) FROM pesanan")->fetchColumn() ?: 0;
$total_orders = $pdo->query("SELECT COUNT(*) FROM pesanan")->fetchColumn();
$unique_customers = $pdo->query("SELECT COUNT(DISTINCT no_inv) FROM pesanan")->fetchColumn();

// Profit Calculation (Joined with details and products to get buy prices)
$profit_data = $pdo->query("SELECT SUM(dp.qty * (dp.harga_satuan - pr.harga_beli)) as laba 
                             FROM detail_pesanan dp 
                             JOIN produk pr ON dp.produk_id = pr.id")->fetchColumn() ?: 0;

// Monthly Sales Data (Last 6 Months)
$monthly_sales = $pdo->query("SELECT DATE_FORMAT(tanggal, '%M') as bulan, SUM(total) as total 
                              FROM pesanan 
                              GROUP BY DATE_FORMAT(tanggal, '%Y-%m') 
                              ORDER BY tanggal DESC 
                              LIMIT 6")->fetchAll();

// Top Selling Items
$top_items = $pdo->query("SELECT pr.nama, SUM(dp.qty) as terjual, SUM(dp.subtotal) as omset
                           FROM detail_pesanan dp 
                           JOIN produk pr ON dp.produk_id = pr.id 
                           GROUP BY dp.produk_id 
                           ORDER BY terjual DESC 
                           LIMIT 5")->fetchAll();

// Top Staff of the month
$top_staff_month = $pdo->query("SELECT k.nama_lengkap, COUNT(p.id) as total 
                                FROM pesanan p 
                                JOIN karyawan k ON p.karyawan_id = k.id 
                                WHERE DATE_FORMAT(p.tanggal, '%Y-%m') = DATE_FORMAT(CURDATE(), '%Y-%m')
                                GROUP BY p.karyawan_id 
                                ORDER BY total DESC 
                                LIMIT 1")->fetch();
?>
<head>
    <title>PrintKantor - Laporan</title>
    <?php include 'includes/head.php'; ?>
</head>
<body class="bg-gray-50 flex">
    <?php include 'includes/sidebar.php'; ?>

    <main class="flex-1 p-10 h-screen overflow-auto">
        <div class="mb-10">
            <h1 class="text-3xl font-bold text-gray-800">Laporan & Analisis</h1>
            <p class="text-gray-500">Rekapitulasi performa penjualan dan pendapatan toko.</p>
        </div>

        <!-- Best Staff Insight -->
        <?php if ($top_staff_month): ?>
        <div class="bg-gradient-to-r from-blue-600 to-indigo-700 rounded-3xl p-8 mb-10 text-white flex items-center justify-between shadow-xl shadow-blue-100">
            <div class="flex items-center gap-6">
                <div class="w-20 h-20 bg-white/20 rounded-2xl flex items-center justify-center text-4xl">
                    <i class="fas fa-award"></i>
                </div>
                <div>
                    <h3 class="text-blue-100 text-sm font-bold uppercase tracking-widest">Staff Terbaik Bulan Ini</h3>
                    <p class="text-3xl font-black mt-1"><?php echo htmlspecialchars($top_staff_month['nama_lengkap']); ?></p>
                    <p class="text-blue-100 opacity-80 mt-1">Telah melayani sebanyak <strong><?php echo $top_staff_month['total']; ?> transaksi</strong> di kasir.</p>
                </div>
            </div>
            <a href="karyawan.php" class="bg-white text-blue-600 px-6 py-3 rounded-xl font-bold hover:bg-blue-50 transition">
                Lihat Semua Staff
            </a>
        </div>
        <?php endif; ?>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            <!-- Best Sellers -->
            <div class="md:col-span-1 bg-white p-8 rounded-3xl shadow-sm border border-gray-100">
                <h3 class="text-lg font-bold text-gray-800 mb-6">Paling Laris</h3>
                <div class="space-y-6">
                    <?php if (empty($top_items)): ?>
                        <p class="text-gray-400 text-sm text-center py-10">Belum ada data penjualan.</p>
                    <?php else: ?>
                        <?php foreach($top_items as $item): ?>
                        <div class="flex items-center justify-between">
                            <div>
                                <h4 class="font-bold text-gray-700 text-sm"><?php echo htmlspecialchars($item['nama']); ?></h4>
                                <p class="text-gray-400 text-xs"><?php echo $item['terjual']; ?> terjual</p>
                            </div>
                            <span class="font-black text-sm text-blue-600">Rp <?php echo number_format($item['omset'], 0, ',', '.'); ?></span>
                        </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Monthly Chart (Mockup representation with CSS bars) -->
            <div class="md:col-span-2 bg-white p-8 rounded-3xl shadow-sm border border-gray-100">
                <h3 class="text-lg font-bold text-gray-800 mb-6">Tren Penjualan (6 Bulan Terakhir)</h3>
                <div class="flex items-end justify-between h-48 gap-4 px-4">
                    <?php if (empty($monthly_sales)): ?>
                        <div class="w-full flex items-center justify-center text-gray-400 text-xs">Belum ada data bulanan.</div>
                    <?php else: ?>
                        <?php 
                        $max_sale = !empty($monthly_sales) ? max(array_column($monthly_sales, 'total')) : 1;
                        foreach(array_reverse($monthly_sales) as $sale): 
                            $height = ($sale['total'] / $max_sale) * 100;
                        ?>
                        <div class="flex-1 flex flex-col items-center gap-2 group">
                            <div class="w-full bg-blue-50 rounded-lg overflow-hidden relative" style="height: 100%;">
                                <div class="absolute bottom-0 left-0 right-0 bg-blue-600 rounded-t-lg transition-all group-hover:bg-blue-700" style="height: <?php echo $height; ?>%;"></div>
                            </div>
                            <span class="text-[10px] font-bold text-gray-400 uppercase"><?php echo substr($sale['bulan'], 0, 3); ?></span>
                        </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
                <p class="mt-10 text-xs text-gray-400 italic">* Laporan ini dihitung secara real-time berdasarkan data di database.</p>
            </div>
        </div>
    </main>
</body>
</html>
