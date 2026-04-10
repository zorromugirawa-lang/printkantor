<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    die("Akses ditolak.");
}
require_once 'config.php';

if (!isset($_GET['id'])) {
    die("ID Transaksi tidak ditemukan.");
}

$id = $_GET['id'];

// Fetch Order Header
$stmt = $pdo->prepare("SELECT p.*, k1.nama_lengkap as kasir, k2.nama_lengkap as pelaksana 
                       FROM pesanan p 
                       LEFT JOIN karyawan k1 ON p.karyawan_id = k1.id 
                       LEFT JOIN karyawan k2 ON p.staff_selesai_id = k2.id 
                       WHERE p.id = ?");
$stmt->execute([$id]);
$order = $stmt->fetch();

if (!$order) {
    die("Transaksi tidak ditemukan.");
}

// Fetch Order Details
$stmt = $pdo->prepare("SELECT dp.*, pr.nama FROM detail_pesanan dp JOIN produk pr ON dp.produk_id = pr.id WHERE dp.pesanan_id = ?");
$stmt->execute([$id]);
$details = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Struk - <?php echo $order['no_inv']; ?></title>
    <style>
        @page { size: 58mm 200mm; margin: 0; }
        body { font-family: 'Courier New', Courier, monospace; width: 50mm; margin: 4mm auto; font-size: 10pt; background: white; }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .line { border-top: 1px dashed #000; margin: 4mm 0; }
        .header { margin-bottom: 4mm; }
        .item { margin-bottom: 2mm; }
        .total-row { display: flex; justify-content: space-between; margin-bottom: 1mm; }
        .btn-print { background: #1e40af; color: white; border: none; padding: 10px; width: 100%; border-radius: 5px; cursor: pointer; margin-bottom: 10px; }
        @media print { .btn-print { display: none; } }
    </style>
</head>
<body>
    <button class="btn-print" onclick="window.print()">CETAK STRUK</button>

    <div class="text-center header">
        <h2 style="margin:0">PrintKantor</h2>
        <p style="margin:2px 0; font-size: 8pt;">Jl. Jend. Sudirman No. 12<br>Pemalang - Jawa Tengah</p>
    </div>

    <div style="font-size: 8pt;">
        <div>Inv: <?php echo $order['no_inv']; ?></div>
        <div>Tgl: <?php echo $order['tanggal']; ?></div>
        <div>Staff: <?php echo $order['kasir']; ?></div>
        <?php if ($order['pelaksana']): ?>
        <div>Hasil: <?php echo $order['pelaksana']; ?></div>
        <?php endif; ?>
    </div>

    <div class="line"></div>

    <?php foreach($details as $d): ?>
    <div class="item">
        <div><?php echo $d['nama']; ?></div>
        <div style="display:flex; justify-content:space-between; font-size: 9pt;">
            <span><?php echo $d['qty']; ?> x <?php echo number_format($d['harga_satuan'], 0, ',', '.'); ?></span>
            <span><?php echo number_format($d['subtotal'], 0, ',', '.'); ?></span>
        </div>
    </div>
    <?php endforeach; ?>

    <div class="line"></div>

    <div class="total-row">
        <span>TOTAL:</span>
        <span style="font-weight:bold">Rp <?php echo number_format($order['total'], 0, ',', '.'); ?></span>
    </div>
    <div class="total-row">
        <span>BAYAR:</span>
        <span>Rp <?php echo number_format($order['bayar'], 0, ',', '.'); ?></span>
    </div>
    <div class="total-row">
        <span>KEMBALI:</span>
        <span>Rp <?php echo number_format($order['kembalian'], 0, ',', '.'); ?></span>
    </div>

    <div class="line"></div>

    <div class="text-center" style="font-size: 8pt; margin-top: 10px;">
        Terima Kasih Atas Kunjungan Anda<br>
        Barang yang sudah dibeli tidak dapat ditukar/dikembalikan.
    </div>
</body>
</html>
