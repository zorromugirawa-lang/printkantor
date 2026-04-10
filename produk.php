<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}
require_once 'config.php';

$message = '';

// Read feedback from URL (after CSV import redirect)
if (isset($_GET['message'])) {
    $message = htmlspecialchars($_GET['message']);
}
$error_msg = '';
if (isset($_GET['error'])) {
    $error_msg = htmlspecialchars($_GET['error']);
}

// Handle CRUD Actions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['add'])) {
        $nama = $_POST['nama'];
        $barcode = $_POST['barcode'];
        $kategori = $_POST['kategori'];
        $harga_beli = $_POST['harga_beli'] ?: 0;
        $harga_jual = $_POST['harga_jual'];
        $stok = $_POST['stok'] ?: 0;
        $satuan = $_POST['satuan'];

        $stmt = $pdo->prepare("INSERT INTO produk (nama, barcode, kategori, harga_beli, harga_jual, stok, satuan) VALUES (?, ?, ?, ?, ?, ?, ?)");
        if ($stmt->execute([$nama, $barcode, $kategori, $harga_beli, $harga_jual, $stok, $satuan])) {
            $message = "Produk berhasil ditambahkan!";
        }
    } elseif (isset($_POST['edit'])) {
        $id = $_POST['id'];
        $nama = $_POST['nama'];
        $barcode = $_POST['barcode'];
        $kategori = $_POST['kategori'];
        $harga_beli = $_POST['harga_beli'] ?: 0;
        $harga_jual = $_POST['harga_jual'];
        $stok = $_POST['stok'] ?: 0;
        $satuan = $_POST['satuan'];

        $stmt = $pdo->prepare("UPDATE produk SET nama=?, barcode=?, kategori=?, harga_beli=?, harga_jual=?, stok=?, satuan=? WHERE id=?");
        if ($stmt->execute([$nama, $barcode, $kategori, $harga_beli, $harga_jual, $stok, $satuan, $id])) {
            $message = "Produk berhasil diperbarui!";
        }
    } elseif (isset($_POST['delete'])) {
        $id = $_POST['id'];
        $stmt = $pdo->prepare("DELETE FROM produk WHERE id=?");
        if ($stmt->execute([$id])) {
            $message = "Produk berhasil dihapus!";
        }
    }
}

// Fetch Products
$produk_list = $pdo->query("SELECT * FROM produk ORDER BY id DESC")->fetchAll();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <title>PrintKantor - Produk & Jasa</title>
    <?php include 'includes/head.php'; ?>
</head>
<body class="bg-gray-50 flex">
    <?php include 'includes/sidebar.php'; ?>

    <main class="flex-1 p-10 h-screen overflow-auto custom-scrollbar">
        <div class="flex justify-between items-center mb-8">
            <div>
                <h1 class="text-3xl font-bold text-gray-800">Manajemen Produk & Jasa</h1>
                <p class="text-gray-500">Kelola katalog stok barang dan layanan pencetakan.</p>
            </div>
            <div class="flex items-center gap-3">
                <a href="template_produk.csv" download class="bg-white border border-gray-200 text-gray-600 px-5 py-3 rounded-2xl flex items-center gap-2 hover:bg-gray-50 transition text-sm font-medium">
                    <i class="fas fa-file-csv text-emerald-600"></i>
                    <span>Download Template</span>
                </a>
                <button onclick="openModal('importModal')" class="bg-emerald-600 text-white px-5 py-3 rounded-2xl flex items-center gap-2 hover:bg-emerald-700 transition shadow-lg shadow-emerald-200 text-sm font-medium">
                    <i class="fas fa-file-upload"></i>
                    <span>Import CSV</span>
                </button>
                <button onclick="openModal('addModal')" class="bg-blue-600 text-white px-6 py-3 rounded-2xl flex items-center gap-2 hover:bg-blue-700 transition shadow-lg shadow-blue-200">
                    <i class="fas fa-plus"></i>
                    <span>Tambah Baru</span>
                </button>
            </div>
        </div>

        <?php if ($message): ?>
            <div class="bg-emerald-50 text-emerald-700 px-6 py-4 rounded-2xl mb-8 border border-emerald-100 flex items-center justify-between">
                <span><i class="fas fa-check-circle mr-2"></i> <?php echo $message; ?></span>
                <button onclick="this.parentElement.remove()" class="text-emerald-400">&times;</button>
            </div>
        <?php endif; ?>
        <?php if ($error_msg): ?>
            <div class="bg-red-50 text-red-700 px-6 py-4 rounded-2xl mb-8 border border-red-100 flex items-center justify-between">
                <span><i class="fas fa-exclamation-circle mr-2"></i> <?php echo $error_msg; ?></span>
                <button onclick="this.parentElement.remove()" class="text-red-400">&times;</button>
            </div>
        <?php endif; ?>

        <!-- Statistics Row -->
        <div class="grid grid-cols-4 gap-6 mb-10">
            <div class="bg-white p-6 rounded-3xl border border-gray-100 shadow-sm">
                <h4 class="text-gray-400 text-sm">Total Produk</h4>
                <p class="text-2xl font-bold mt-2"><?php echo count(array_filter($produk_list, fn($p) => $p['kategori'] == 'Produk')); ?></p>
            </div>
            <div class="bg-white p-6 rounded-3xl border border-gray-100 shadow-sm">
                <h4 class="text-gray-400 text-sm">Total Jasa</h4>
                <p class="text-2xl font-bold mt-2"><?php echo count(array_filter($produk_list, fn($p) => $p['kategori'] == 'Jasa')); ?></p>
            </div>
            <div class="bg-white p-6 rounded-3xl border border-gray-100 shadow-sm">
                <h4 class="text-gray-400 text-sm">Stok Habis</h4>
                <p class="text-2xl font-bold mt-2 text-red-500"><?php echo count(array_filter($produk_list, fn($p) => $p['kategori'] == 'Produk' && $p['stok'] <= 0)); ?></p>
            </div>
            <div class="bg-white p-6 rounded-3xl border border-gray-100 shadow-sm">
                <h4 class="text-gray-400 text-sm">Aset Persediaan</h4>
                <p class="text-2xl font-bold mt-2 text-emerald-600">Rp <?php 
                    $total_aset = 0;
                    foreach($produk_list as $p) $total_aset += ($p['harga_beli'] * $p['stok']);
                    echo number_format($total_aset, 0, ',', '.'); 
                ?></p>
            </div>
        </div>

        <!-- Table Card -->
        <div class="bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden">
            <table class="w-full text-left">
                <thead class="bg-gray-50 border-b border-gray-100">
                    <tr>
                        <th class="px-8 py-5 text-sm font-semibold text-gray-600">Barcode</th>
                        <th class="px-8 py-5 text-sm font-semibold text-gray-600">Nama Produk/Jasa</th>
                        <th class="px-8 py-5 text-sm font-semibold text-gray-600">Kategori</th>
                        <th class="px-8 py-5 text-sm font-semibold text-gray-600">Harga Jual</th>
                        <th class="px-8 py-5 text-sm font-semibold text-gray-600">Stok</th>
                        <th class="px-8 py-5 text-sm font-semibold text-gray-600 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    <?php if (empty($produk_list)): ?>
                        <tr><td colspan="5" class="px-8 py-10 text-center text-gray-400">Belum ada data produk/jasa.</td></tr>
                    <?php else: ?>
                        <?php foreach($produk_list as $p): ?>
                        <tr class="hover:bg-blue-50/30 transition">
                            <td class="px-8 py-5">
                                <span class="bg-gray-100 px-3 py-1 rounded-lg text-[10px] font-mono font-bold text-gray-500">
                                    <?php echo $p['barcode'] ?: '-'; ?>
                                </span>
                            </td>
                            <td class="px-8 py-5">
                                <div class="font-bold text-gray-800"><?php echo htmlspecialchars($p['nama']); ?></div>
                                <div class="text-xs text-gray-400"><?php echo htmlspecialchars($p['satuan']); ?></div>
                            </td>
                            <td class="px-8 py-5">
                                <span class="px-3 py-1 rounded-full text-xs font-semibold <?php echo $p['kategori'] == 'Produk' ? 'bg-blue-100 text-blue-600' : 'bg-purple-100 text-purple-600'; ?>">
                                    <?php echo $p['kategori']; ?>
                                </span>
                            </td>
                            <td class="px-8 py-5 font-semibold text-gray-700">Rp <?php echo number_format($p['harga_jual'], 0, ',', '.'); ?></td>
                            <td class="px-8 py-5">
                                <?php if ($p['kategori'] == 'Produk'): ?>
                                    <span class="<?php echo $p['stok'] <= 5 ? 'text-red-500 font-bold' : 'text-gray-600'; ?>">
                                        <?php echo $p['stok']; ?>
                                    </span>
                                <?php else: ?>
                                    <span class="text-gray-300">-</span>
                                <?php endif; ?>
                            </td>
                            <td class="px-8 py-5 text-right space-x-2">
                                <button onclick="editProduct(<?php echo htmlspecialchars(json_encode($p)); ?>)" class="text-blue-500 hover:text-blue-700 bg-blue-50 w-10 h-10 rounded-xl inline-flex items-center justify-center transition">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button onclick="confirmDelete(<?php echo $p['id']; ?>)" class="text-red-500 hover:text-red-700 bg-red-50 w-10 h-10 rounded-xl inline-flex items-center justify-center transition">
                                    <i class="fas fa-trash-alt"></i>
                                </button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </main>

    <!-- Modal Tambah -->
    <div id="addModal" class="hidden fixed inset-0 bg-black/50 backdrop-blur-sm z-50 flex items-center justify-center p-4">
        <div class="bg-white rounded-3xl w-full max-w-xl p-8 shadow-2xl">
            <div class="flex justify-between items-center mb-6">
                <h2 class="text-2xl font-bold text-gray-800">Tambah Produk/Jasa</h2>
                <button onclick="closeModal('addModal')" class="text-gray-400 hover:text-gray-600"><i class="fas fa-times text-xl"></i></button>
            </div>
            <form action="" method="POST" class="space-y-4">
                <input type="hidden" name="add" value="1">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Nama Produk / Jasa</label>
                    <input type="text" name="nama" required class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:outline-none focus:border-blue-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Barcode (Opsional)</label>
                    <input type="text" name="barcode" class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:outline-none focus:border-blue-500" placeholder="Scan barcode di sini...">
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Kategori</label>
                        <select name="kategori" class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:outline-none focus:border-blue-500">
                            <option value="Produk">Produk Barang</option>
                            <option value="Jasa">Layanan Jasa</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Satuan</label>
                        <input type="text" name="satuan" placeholder="Pcs/Lbr/Rim" class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:outline-none focus:border-blue-500">
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Harga Beli</label>
                        <input type="number" name="harga_beli" value="0" class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:outline-none focus:border-blue-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Harga Jual</label>
                        <input type="number" name="harga_jual" required class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:outline-none focus:border-blue-500">
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Stok Awal</label>
                    <input type="number" name="stok" value="0" class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:outline-none focus:border-blue-500">
                </div>
                <button type="submit" class="w-full bg-blue-600 text-white py-4 rounded-2xl font-bold hover:bg-blue-700 transition mt-4">SIMPAN DATA</button>
            </form>
        </div>
    </div>

    <!-- Modal Edit -->
    <div id="editModal" class="hidden fixed inset-0 bg-black/50 backdrop-blur-sm z-50 flex items-center justify-center p-4">
        <div class="bg-white rounded-3xl w-full max-w-xl p-8 shadow-2xl">
            <div class="flex justify-between items-center mb-6">
                <h2 class="text-2xl font-bold text-gray-800">Edit Produk/Jasa</h2>
                <button onclick="closeModal('editModal')" class="text-gray-400 hover:text-gray-600"><i class="fas fa-times text-xl"></i></button>
            </div>
            <form action="" method="POST" class="space-y-4">
                <input type="hidden" name="edit" value="1">
                <input type="hidden" name="id" id="edit_id">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Nama Produk / Jasa</label>
                    <input type="text" name="nama" id="edit_nama" required class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:outline-none focus:border-blue-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Barcode</label>
                    <input type="text" name="barcode" id="edit_barcode" class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:outline-none focus:border-blue-500">
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Kategori</label>
                        <select name="kategori" id="edit_kategori" class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:outline-none focus:border-blue-500">
                            <option value="Produk">Produk Barang</option>
                            <option value="Jasa">Layanan Jasa</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Satuan</label>
                        <input type="text" name="satuan" id="edit_satuan" class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:outline-none focus:border-blue-500">
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Harga Beli</label>
                        <input type="number" name="harga_beli" id="edit_harga_beli" class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:outline-none focus:border-blue-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Harga Jual</label>
                        <input type="number" name="harga_jual" id="edit_harga_jual" required class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:outline-none focus:border-blue-500">
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Stok</label>
                    <input type="number" name="stok" id="edit_stok" class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:outline-none focus:border-blue-500">
                </div>
                <button type="submit" class="w-full bg-blue-600 text-white py-4 rounded-2xl font-bold hover:bg-blue-700 transition mt-4">UPDATE DATA</button>
            </form>
        </div>
    </div>

    <!-- Form Hapus Hidden -->
    <form id="deleteForm" method="POST" class="hidden">
        <input type="hidden" name="delete" value="1">
        <input type="hidden" name="id" id="delete_id">
    </form>

    <!-- Modal Import CSV -->
    <div id="importModal" class="hidden fixed inset-0 bg-black/50 backdrop-blur-sm z-50 flex items-center justify-center p-4">
        <div class="bg-white rounded-3xl w-full max-w-lg p-8 shadow-2xl">
            <div class="flex justify-between items-center mb-6">
                <h2 class="text-2xl font-bold text-gray-800">Import Produk via CSV</h2>
                <button onclick="closeModal('importModal')" class="text-gray-400 hover:text-gray-600"><i class="fas fa-times text-xl"></i></button>
            </div>

            <div class="bg-blue-50 border border-blue-100 rounded-2xl p-5 mb-6 text-sm text-blue-800 space-y-1">
                <p class="font-bold text-base mb-2"><i class="fas fa-info-circle mr-2"></i>Format Kolom CSV:</p>
                <code class="block bg-white rounded-lg px-4 py-3 text-xs text-gray-700 border border-blue-100 font-mono">
                    barcode, nama, kategori, harga_beli, harga_jual, stok, satuan
                </code>
                <p class="text-blue-600 mt-2">Kategori hanya boleh: <strong>Produk</strong> atau <strong>Jasa</strong>.</p>
                <p class="text-blue-600">Produk dengan barcode yang sudah ada akan dilewati (tidak duplikat).</p>
            </div>

            <form action="import_produk.php" method="POST" enctype="multipart/form-data" class="space-y-5">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Pilih File CSV</label>
                    <div class="border-2 border-dashed border-gray-200 rounded-2xl p-8 text-center hover:border-blue-400 transition cursor-pointer" onclick="document.getElementById('csv_file_input').click()">
                        <i class="fas fa-cloud-upload-alt text-3xl text-gray-300 mb-3"></i>
                        <p class="text-gray-500 text-sm" id="file_label">Klik untuk memilih file .csv</p>
                        <input type="file" name="csv_file" id="csv_file_input" accept=".csv" class="hidden" onchange="document.getElementById('file_label').textContent = this.files[0].name" required>
                    </div>
                </div>
                <button type="submit" class="w-full bg-emerald-600 text-white py-4 rounded-2xl font-bold hover:bg-emerald-700 transition">
                    <i class="fas fa-upload mr-2"></i>MULAI IMPORT
                </button>
            </form>
        </div>
    </div>

    <script>
        function openModal(id) {
            document.getElementById(id).classList.remove('hidden');
        }
        function closeModal(id) {
            document.getElementById(id).classList.add('hidden');
        }
        function editProduct(data) {
            document.getElementById('edit_id').value = data.id;
            document.getElementById('edit_nama').value = data.nama;
            document.getElementById('edit_barcode').value = data.barcode || '';
            document.getElementById('edit_kategori').value = data.kategori;
            document.getElementById('edit_harga_beli').value = data.harga_beli;
            document.getElementById('edit_harga_jual').value = data.harga_jual;
            document.getElementById('edit_stok').value = data.stok;
            document.getElementById('edit_satuan').value = data.satuan;
            openModal('editModal');
        }
        function confirmDelete(id) {
            if (confirm('Apakah Anda yakin ingin menghapus produk ini?')) {
                document.getElementById('delete_id').value = id;
                document.getElementById('deleteForm').submit();
            }
        }
    </script>
</body>
</html>
