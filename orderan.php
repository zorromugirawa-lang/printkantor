<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}
require_once 'config.php';

// Handle Status Update
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_status'])) {
    $id = $_POST['id'];
    $new_status = $_POST['new_status'];
    
    if ($new_status == 'Selesai') {
        $stmt = $pdo->prepare("UPDATE pesanan SET status = ?, staff_selesai_id = ? WHERE id = ?");
        $stmt->execute([$new_status, $_SESSION['user_id'], $id]);
    } else {
        $stmt = $pdo->prepare("UPDATE pesanan SET status = ?, staff_selesai_id = NULL WHERE id = ?");
        $stmt->execute([$new_status, $id]);
    }
    
    header("Location: orderan.php");
    exit;
}

// Fetch Transactions
$limit = 50;
$orders = $pdo->query("SELECT p.*, k1.nama_lengkap as kasir, k2.nama_lengkap as pelaksana 
                       FROM pesanan p 
                       LEFT JOIN karyawan k1 ON p.karyawan_id = k1.id 
                       LEFT JOIN karyawan k2 ON p.staff_selesai_id = k2.id 
                       ORDER BY p.tanggal DESC 
                       LIMIT $limit")->fetchAll();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <title>PrintKantor - Daftar Orderan</title>
    <?php include 'includes/head.php'; ?>
</head>
<body class="bg-gray-50 flex">
    <?php include 'includes/sidebar.php'; ?>

    <main class="flex-1 p-10 h-screen overflow-auto">
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-800">Riwayat Penjualan</h1>
            <p class="text-gray-500">Daftar transaksi 50 terakhir yang dilakukan di sistem.</p>
        </div>

        <div class="bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden">
            <table class="w-full text-left">
                <thead class="bg-gray-50 border-b border-gray-100">
                    <tr>
                        <th class="px-8 py-5 text-sm font-semibold text-gray-600">No Invoice</th>
                        <th class="px-8 py-5 text-sm font-semibold text-gray-600">Tanggal & Waktu</th>
                        <th class="px-8 py-5 text-sm font-semibold text-gray-600">Kasir</th>
                        <th class="px-8 py-5 text-sm font-semibold text-gray-600">Status</th>
                        <th class="px-8 py-5 text-sm font-semibold text-gray-600 text-right">Total Transaksi</th>
                        <th class="px-8 py-5 text-sm font-semibold text-gray-600 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    <?php if (empty($orders)): ?>
                        <tr><td colspan="6" class="px-8 py-10 text-center text-gray-400">Belum ada data transaksi.</td></tr>
                    <?php else: ?>
                        <?php foreach($orders as $o): ?>
                        <tr class="hover:bg-blue-50/30 transition">
                            <td class="px-8 py-5 font-bold text-blue-600">#<?php echo $o['no_inv']; ?></td>
                            <td class="px-8 py-5 text-gray-500 text-sm"><?php echo date('d/m/Y H:i', strtotime($o['tanggal'])); ?></td>
                            <td class="px-8 py-5">
                                <div class="text-sm font-medium text-gray-800"><?php echo htmlspecialchars($o['kasir']); ?></div>
                                <?php if ($o['pelaksana']): ?>
                                    <div class="text-[10px] text-emerald-600 font-bold uppercase mt-1">
                                        <i class="fas fa-check-double mr-1"></i> Finishing: <?php echo htmlspecialchars($o['pelaksana']); ?>
                                    </div>
                                <?php endif; ?>
                            </td>
                            <td class="px-8 py-5">
                                <span class="px-3 py-1 rounded-full text-xs font-bold <?php echo $o['status'] == 'Pending' ? 'bg-amber-100 text-amber-600' : 'bg-emerald-100 text-emerald-600'; ?>">
                                    <i class="fas <?php echo $o['status'] == 'Pending' ? 'fa-clock' : 'fa-check-circle'; ?> mr-1"></i>
                                    <?php echo $o['status']; ?>
                                </span>
                            </td>
                            <td class="px-8 py-5 text-right font-black text-gray-800">Rp <?php echo number_format($o['total'], 0, ',', '.'); ?></td>
                            <td class="px-8 py-5 text-right flex justify-end gap-2">
                                <form method="POST" class="inline">
                                    <input type="hidden" name="update_status" value="1">
                                    <input type="hidden" name="id" value="<?php echo $o['id']; ?>">
                                    <input type="hidden" name="new_status" value="<?php echo $o['status'] == 'Pending' ? 'Selesai' : 'Pending'; ?>">
                                    <button type="submit" title="Tandai <?php echo $o['status'] == 'Pending' ? 'Selesai' : 'Pending'; ?>" 
                                            class="w-10 h-10 rounded-xl flex items-center justify-center transition <?php echo $o['status'] == 'Pending' ? 'bg-emerald-50 text-emerald-600 hover:bg-emerald-600 hover:text-white' : 'bg-amber-50 text-amber-600 hover:bg-amber-600 hover:text-white'; ?>">
                                        <i class="fas <?php echo $o['status'] == 'Pending' ? 'fa-check' : 'fa-undo'; ?>"></i>
                                    </button>
                                </form>
                                <a href="print_struk.php?id=<?php echo $o['id']; ?>" target="_blank" title="Cetak Struk"
                                   class="text-gray-400 hover:text-blue-600 transition bg-white border border-gray-200 w-10 h-10 rounded-xl inline-flex items-center justify-center">
                                    <i class="fas fa-print"></i>
                                </a>
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
