<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}
require_once 'config.php';

$message = '';
$error_msg = '';

// Handle Delete Staff with Password Verification
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['delete_staff'])) {
    $target_id  = (int) $_POST['target_id'];
    $admin_pass = $_POST['admin_password'];

    // Fetch current admin's password from DB to verify
    $stmt = $pdo->prepare("SELECT password FROM karyawan WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $admin = $stmt->fetch();

    if (!$admin || !password_verify($admin_pass, $admin['password'])) {
        $error_msg = "Password admin salah! Staff tidak dihapus.";
    } else {
        // Prevent deleting yourself
        if ($target_id == $_SESSION['user_id']) {
            $error_msg = "Anda tidak bisa menghapus akun Anda sendiri!";
        } else {
            $del = $pdo->prepare("DELETE FROM karyawan WHERE id = ?");
            if ($del->execute([$target_id])) {
                $message = "Staff berhasil dihapus dari sistem.";
            } else {
                $error_msg = "Gagal menghapus staff. Mungkin masih ada data terkait.";
            }
        }
    }
}

// Fetch all employees basic data
$employees = $pdo->query("SELECT * FROM karyawan ORDER BY nama_lengkap ASC")->fetchAll();

// Fetch monthly performance for each employee
$performance_query = "
    SELECT 
        k.id,
        k.nama_lengkap, 
        k.kode_karyawan, 
        DATE_FORMAT(p.tanggal, '%M %Y') as bulan_label,
        DATE_FORMAT(p.tanggal, '%Y-%m') as bulan_key,
        COUNT(p.id) as total_transaksi,
        SUM(p.total) as total_omset
    FROM karyawan k
    LEFT JOIN pesanan p ON k.id = p.karyawan_id
    WHERE p.id IS NOT NULL
    GROUP BY k.id, bulan_key
    ORDER BY bulan_key DESC, total_transaksi DESC
";
$performance = $pdo->query($performance_query)->fetchAll();

// Group performance data by employee
$employee_stats = [];
foreach ($performance as $p) {
    if (!isset($employee_stats[$p['id']])) {
        $employee_stats[$p['id']] = [];
    }
    $employee_stats[$p['id']][] = $p;
}

// Top Staff this month
$top_staff = count($performance) > 0 ? $performance[0] : null;
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <title>PrintKantor - Manajemen Staff</title>
    <?php include 'includes/head.php'; ?>
</head>
<body class="bg-gray-50 flex">
    <?php include 'includes/sidebar.php'; ?>

    <main class="flex-1 p-10 h-screen overflow-auto">
        <div class="mb-10 flex justify-between items-start">
            <div>
                <h1 class="text-3xl font-bold text-gray-800">Manajemen & Performa Staff</h1>
                <p class="text-gray-500">Pantau produktivitas dan jumlah pelayanan kasir setiap karyawan.</p>
            </div>
        </div>

        <?php if ($message): ?>
        <div class="bg-emerald-50 text-emerald-700 px-6 py-4 rounded-2xl mb-8 border border-emerald-100 flex items-center justify-between">
            <span><i class="fas fa-check-circle mr-2"></i> <?php echo htmlspecialchars($message); ?></span>
            <button onclick="this.parentElement.remove()" class="text-emerald-400">&times;</button>
        </div>
        <?php endif; ?>
        <?php if ($error_msg): ?>
        <div class="bg-red-50 text-red-700 px-6 py-4 rounded-2xl mb-8 border border-red-100 flex items-center justify-between">
            <span><i class="fas fa-exclamation-circle mr-2"></i> <?php echo htmlspecialchars($error_msg); ?></span>
            <button onclick="this.parentElement.remove()" class="text-red-400">&times;</button>
        </div>
        <?php endif; ?>

        <!-- Summary Cards -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-10">
            <div class="bg-white p-6 rounded-3xl shadow-sm border border-gray-100 flex items-center gap-4">
                <div class="w-14 h-14 bg-blue-50 text-blue-600 rounded-2xl flex items-center justify-center text-2xl">
                    <i class="fas fa-user-tie"></i>
                </div>
                <div>
                    <p class="text-gray-400 text-sm font-medium">Total Staff</p>
                    <p class="text-2xl font-black text-gray-800"><?php echo count($employees); ?></p>
                </div>
            </div>
            <div class="bg-gradient-to-br from-emerald-500 to-teal-600 p-6 rounded-3xl shadow-lg shadow-emerald-100 text-white flex items-center gap-4">
                <div class="w-14 h-14 bg-white/20 rounded-2xl flex items-center justify-center text-2xl">
                    <i class="fas fa-trophy"></i>
                </div>
                <div>
                    <p class="text-emerald-50 text-sm font-medium">Staff Teraktif Bulan Ini</p>
                    <p class="text-xl font-bold"><?php echo $top_staff ? htmlspecialchars($top_staff['nama_lengkap']) : '-'; ?></p>
                </div>
            </div>
        </div>

        <!-- Employee List & Performance -->
        <div class="space-y-8">
            <?php foreach($employees as $emp): ?>
            <div class="bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="p-6 border-b border-gray-50 flex justify-between items-center bg-gray-50/50">
                    <div class="flex items-center gap-4">
                        <div class="w-12 h-12 bg-blue-600 text-white rounded-xl flex items-center justify-center font-bold text-xl uppercase">
                            <?php echo substr($emp['nama_lengkap'], 0, 1); ?>
                        </div>
                        <div>
                            <h3 class="font-bold text-gray-800 text-lg"><?php echo htmlspecialchars($emp['nama_lengkap']); ?></h3>
                            <p class="text-xs text-blue-600 font-mono"><?php echo $emp['kode_karyawan']; ?> • <?php echo ucfirst($emp['role']); ?></p>
                        </div>
                    </div>
                    <!-- Delete Button - only show if not the logged-in admin -->
                    <?php if ($emp['id'] != $_SESSION['user_id']): ?>
                    <button onclick="showDeleteModal(<?php echo $emp['id']; ?>, '<?php echo htmlspecialchars($emp['nama_lengkap'], ENT_QUOTES); ?>')"
                            class="flex items-center gap-2 px-4 py-2 rounded-xl text-red-500 border border-red-100 hover:bg-red-50 transition text-sm font-medium">
                        <i class="fas fa-trash-alt"></i>
                        <span>Hapus Staff</span>
                    </button>
                    <?php else: ?>
                    <span class="text-xs text-gray-400 italic px-3 py-2 border border-gray-100 rounded-xl">Akun Anda</span>
                    <?php endif; ?>
                </div>
                
                <div class="p-0">
                    <table class="w-full text-left">
                        <thead class="bg-white border-b border-gray-100">
                            <tr>
                                <th class="px-8 py-4 text-xs font-bold text-gray-400 uppercase tracking-wider">Periode Bulan</th>
                                <th class="px-8 py-4 text-xs font-bold text-gray-400 uppercase tracking-wider">Jumlah Pelayanan Kasir</th>
                                <th class="px-8 py-4 text-xs font-bold text-gray-400 uppercase tracking-wider text-right">Total Nilai Transaksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50">
                            <?php 
                            $stats = isset($employee_stats[$emp['id']]) ? $employee_stats[$emp['id']] : [];
                            if (empty($stats)): 
                            ?>
                                <tr>
                                    <td colspan="3" class="px-8 py-6 text-gray-400 text-sm italic">Belum ada aktivitas pelayanan kasir tercatat.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach($stats as $s): ?>
                                <tr class="hover:bg-blue-50/30 transition">
                                    <td class="px-8 py-4 font-semibold text-gray-700"><?php echo $s['bulan_label']; ?></td>
                                    <td class="px-8 py-4">
                                        <span class="bg-blue-100 text-blue-700 px-3 py-1 rounded-full text-xs font-black">
                                            <?php echo $s['total_transaksi']; ?> Transaksi
                                        </span>
                                    </td>
                                    <td class="px-8 py-4 text-right font-bold text-emerald-600">
                                        Rp <?php echo number_format($s['total_omset'], 0, ',', '.'); ?>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </main>

    <!-- Delete Confirmation Modal with Password -->
    <div id="deleteModal" class="hidden fixed inset-0 bg-black/60 backdrop-blur-sm z-50 flex items-center justify-center p-4">
        <div class="bg-white rounded-3xl w-full max-w-md p-8 shadow-2xl">
            <div class="text-center mb-6">
                <div class="w-16 h-16 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-user-times text-red-500 text-2xl"></i>
                </div>
                <h2 class="text-2xl font-bold text-gray-800">Hapus Staff</h2>
                <p class="text-gray-500 mt-2">Anda akan menghapus <strong id="modal_staff_name" class="text-red-600"></strong> dari sistem.</p>
            </div>

            <form method="POST" class="space-y-5">
                <input type="hidden" name="delete_staff" value="1">
                <input type="hidden" name="target_id" id="modal_target_id">
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                        <i class="fas fa-lock mr-1 text-gray-400"></i>Masukkan Password Admin Anda untuk Konfirmasi
                    </label>
                    <input type="password" name="admin_password" required
                           class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:outline-none focus:border-red-400 text-base"
                           placeholder="Password Anda saat ini">
                </div>
                <div class="flex gap-3 pt-2">
                    <button type="button" onclick="document.getElementById('deleteModal').classList.add('hidden')"
                            class="flex-1 py-3 rounded-2xl border border-gray-200 text-gray-600 font-semibold hover:bg-gray-50 transition">
                        Batal
                    </button>
                    <button type="submit" class="flex-1 py-3 rounded-2xl bg-red-600 text-white font-bold hover:bg-red-700 transition">
                        <i class="fas fa-trash-alt mr-2"></i>Ya, Hapus
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function showDeleteModal(id, name) {
            document.getElementById('modal_target_id').value = id;
            document.getElementById('modal_staff_name').textContent = name;
            document.getElementById('deleteModal').classList.remove('hidden');
        }
    </script>
</body>
</html>
