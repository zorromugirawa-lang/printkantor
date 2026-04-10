<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}
require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['csv_file'])) {
    $file = $_FILES['csv_file']['tmp_name'];
    
    if (empty($file)) {
        header("Location: produk.php?error=File tidak boleh kosong");
        exit;
    }

    $handle = fopen($file, "r");
    $success = 0;
    $failed = 0;
    $row = 0;

    // Start Transaction
    $pdo->beginTransaction();

    try {
        while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
            $row++;
            if ($row == 1) continue; // Skip the header

            // CSV Columns: barcode, nama, kategori, harga_beli, harga_jual, stok, satuan
            $barcode    = trim($data[0]);
            $nama       = trim($data[1]);
            $kategori   = trim($data[2]); // Produk or Jasa
            $harga_beli = (float) $data[3];
            $harga_jual = (float) $data[4];
            $stok       = (int) $data[5];
            $satuan     = trim($data[6]);

            if (empty($nama) || empty($kategori) || empty($harga_jual)) {
                $failed++;
                continue;
            }

            // Check if barcode already exists
            if (!empty($barcode)) {
                $check = $pdo->prepare("SELECT id FROM produk WHERE barcode = ?");
                $check->execute([$barcode]);
                if ($check->fetch()) {
                    $failed++;
                    continue; // Skip existing barcodes
                }
            }

            $stmt = $pdo->prepare("INSERT INTO produk (barcode, nama, kategori, harga_beli, harga_jual, stok, satuan) VALUES (?, ?, ?, ?, ?, ?, ?)");
            if ($stmt->execute([$barcode, $nama, $kategori, $harga_beli, $harga_jual, $stok, $satuan])) {
                $success++;
            } else {
                $failed++;
            }
        }
        
        $pdo->commit();
        fclose($handle);
        
        header("Location: produk.php?message=Impor Selesai! $success Berhasil, $failed Lewati/Gagal.");
        exit;

    } catch (Exception $e) {
        $pdo->rollBack();
        header("Location: produk.php?error=Terjadi kesalahan saat memproses file: " . urlencode($e->getMessage()));
        exit;
    }
} else {
    header("Location: produk.php");
    exit;
}
