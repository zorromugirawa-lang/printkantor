<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}
require_once 'config.php';

$message = '';

// Handle Stock Update
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_stock'])) {
    $id = $_POST['id'];
    $change = $_POST['change'];
    
    $stmt = $pdo->prepare("UPDATE produk SET stok = stok + ? WHERE id = ?");
    if ($stmt->execute([$change, $id])) {
        $message = "Stok berhasil diperbarui!";
    }
}

// Fetch only Products (not Services)
$produk_list = $pdo->query("SELECT * FROM produk WHERE kategori = 'Produk' ORDER BY stok ASC")->fetchAll();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <title>PrintKantor - Stok Barang</title>
    <?php include 'includes/head.php'; ?>
</head>
<body class="bg-gray-50 flex">
    <?php include 'includes/sidebar.php'; ?>

    <main class="flex-1 p-10 h-screen overflow-auto">
        <div class="flex justify-between items-center mb-8">
            <div>
                <h1 class="text-3xl font-bold text-gray-800">Manajemen Stok Barang</h1>
                <p class="text-gray-500">Monitoring ketersediaan barang fisik secara real-time.</p>
            </div>
        </div>

        <?php if ($message): ?>
            <div class="bg-blue-50 text-blue-700 px-6 py-4 rounded-2xl mb-8 border border-blue-100 flex items-center justify-between">
                <span><i class="fas fa-info-circle mr-2"></i> <?php echo $message; ?></span>
                <button onclick="this.parentElement.remove()" class="text-blue-400">&times;</button>
            </div>
        <?php endif; ?>

        <!-- Low Stock Alerts -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-10">
            <?php 
            $low_stock_items = array_filter($produk_list, fn($p) => $p['stok'] <= 5);
            if (!empty($low_stock_items)): 
            ?>
                <div class="col-span-full bg-red-50 border border-red-100 p-6 rounded-3xl flex items-center gap-4">
                    <div class="w-12 h-12 bg-red-500 text-white rounded-2xl flex items-center justify-center text-2xl">
                        <i class="fas fa-exclamation-triangle"></i>
                    </div>
                    <div>
                        <h4 class="font-bold text-red-800">Peringatan Stok Menipis!</h4>
                        <p class="text-red-600 text-sm">Ada <?php echo count($low_stock_items); ?> item yang memiliki stok kurang dari atau sama dengan 5.</p>
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <!-- Stock Table -->
        <div class="bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden">
            <table class="w-full text-left">
                <thead class="bg-gray-50 border-b border-gray-100">
                    <tr>
                        <th class="px-8 py-5 text-sm font-semibold text-gray-600">Barang</th>
                        <th class="px-8 py-5 text-sm font-semibold text-gray-600">Satuan</th>
                        <th class="px-8 py-5 text-sm font-semibold text-gray-600">Terakhir Update</th>
                        <th class="px-8 py-5 text-sm font-semibold text-gray-600">Stok Saat Ini</th>
                        <th class="px-8 py-5 text-sm font-semibold text-gray-600 text-right">Aksi Cepat</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    <?php if (empty($produk_list)): ?>
                        <tr><td colspan="5" class="px-8 py-10 text-center text-gray-400">Tidak ada data barang fisik. Pastikan kategori adalah 'Produk'.</td></tr>
                    <?php else: ?>
                        <?php foreach($produk_list as $p): ?>
                        <tr class="hover:bg-blue-50/30 transition">
                            <td class="px-8 py-5 font-bold text-gray-800"><?php echo htmlspecialchars($p['nama']); ?></td>
                            <td class="px-8 py-5 text-gray-500"><?php echo htmlspecialchars($p['satuan']); ?></td>
                            <td class="px-8 py-5 text-xs text-gray-400"><?php echo $p['created_at']; ?></td>
                            <td class="px-8 py-5">
                                <div class="flex items-center gap-3">
                                    <span class="text-2xl font-bold <?php echo $p['stok'] <= 5 ? 'text-red-500' : 'text-blue-600'; ?>">
                                        <?php echo $p['stok']; ?>
                                    </span>
                                    <?php if ($p['stok'] <= 5): ?>
                                        <span class="bg-red-100 text-red-600 text-[10px] px-2 py-0.5 rounded-full font-bold uppercase">Low</span>
                                    <?php endif; ?>
                                </div>
                            </td>
                            <td class="px-8 py-5 text-right">
                                <form action="" method="POST" class="inline-flex items-center gap-2">
                                    <input type="hidden" name="update_stock" value="1">
                                    <input type="hidden" name="id" value="<?php echo $p['id']; ?>">
                                    <button type="submit" name="change" value="-1" class="w-8 h-8 rounded-lg bg-gray-100 text-gray-600 hover:bg-gray-200 transition">-</button>
                                    <input type="number" name="custom_change" placeholder="+" class="w-16 px-2 py-1 border border-gray-200 rounded-lg text-center text-sm focus:outline-none focus:border-blue-500" onchange="this.form.change.value = this.value">
                                    <button type="submit" name="change" value="1" class="w-8 h-8 rounded-lg bg-blue-600 text-white hover:bg-blue-700 transition">+</button>
                                </form>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </main>
</body>
</html>
