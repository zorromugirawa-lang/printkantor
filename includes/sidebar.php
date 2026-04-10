<?php
$current_page = basename($_SERVER['PHP_SELF']);
?>
<div class="w-72 bg-[#1e40af] text-white flex flex-col h-screen sticky top-0">
    <div class="px-8 py-10 border-b border-white/20">
        <div class="flex items-center gap-3">
            <div class="w-12 h-12 bg-white rounded-2xl flex items-center justify-center text-4xl text-[#1e40af]">🖨️</div>
            <div>
                <h1 class="text-3xl font-bold">PrintKantor</h1>
                <p class="text-sm text-blue-200 -mt-1">Pemalang</p>
            </div>
        </div>
    </div>

    <div class="flex-1 p-6 space-y-2 overflow-y-auto">
        <a href="dashboard.php" class="flex items-center gap-3 px-5 py-4 rounded-2xl transition hover:bg-white/10 <?php echo $current_page == 'dashboard.php' ? 'bg-white/10 font-bold' : ''; ?>">
            <i class="fas fa-home text-xl w-8"></i>
            <span class="text-base font-medium">Dashboard</span>
        </a>
        
        <a href="produk.php" class="flex items-center gap-3 px-5 py-4 rounded-2xl transition hover:bg-white/10 <?php echo $current_page == 'produk.php' ? 'bg-white/10 font-bold' : ''; ?>">
            <i class="fas fa-box text-xl w-8"></i>
            <span class="text-base font-medium">Produk & Jasa</span>
        </a>

        <a href="stok.php" class="flex items-center gap-3 px-5 py-4 rounded-2xl transition hover:bg-white/10 <?php echo $current_page == 'stok.php' ? 'bg-white/10 font-bold' : ''; ?>">
            <i class="fas fa-layer-group text-xl w-8"></i>
            <span class="text-base font-medium">Stok Barang</span>
        </a>

        <a href="kasir.php" class="flex items-center gap-3 px-5 py-4 rounded-2xl transition hover:bg-white/10 <?php echo $current_page == 'kasir.php' ? 'bg-white/10 font-bold' : ''; ?>">
            <i class="fas fa-cash-register text-xl w-8"></i>
            <span class="text-base font-medium">Kasir / POS</span>
        </a>

        <a href="orderan.php" class="flex items-center gap-3 px-5 py-4 rounded-2xl transition hover:bg-white/10 <?php echo $current_page == 'orderan.php' ? 'bg-white/10 font-bold' : ''; ?>">
            <i class="fas fa-list-ul text-xl w-8"></i>
            <span class="text-base font-medium">Daftar Orderan</span>
        </a>

        <a href="laporan.php" class="flex items-center gap-3 px-5 py-4 rounded-2xl transition hover:bg-white/10 <?php echo $current_page == 'laporan.php' ? 'bg-white/10 font-bold' : ''; ?>">
            <i class="fas fa-chart-line text-xl w-8"></i>
            <span class="text-base font-medium">Laporan & Riwayat</span>
        </a>

        <a href="karyawan.php" class="flex items-center gap-3 px-5 py-4 rounded-2xl transition hover:bg-white/10 <?php echo $current_page == 'karyawan.php' ? 'bg-white/10 font-bold' : ''; ?>">
            <i class="fas fa-users text-xl w-8"></i>
            <span class="text-base font-medium">Manajemen Staff</span>
        </a>
    </div>

    <!-- User Info & Logout -->
    <div class="p-6 border-t border-white/20">
        <div class="flex flex-col gap-4">
            <div>
                <div class="font-semibold text-base"><?php echo htmlspecialchars($_SESSION['nama']); ?></div>
                <div class="text-sm text-blue-200"><?php echo htmlspecialchars($_SESSION['kode']); ?> • <?php echo ucfirst($_SESSION['role']); ?></div>
            </div>
            <a href="logout.php" onclick="return confirm('Yakin ingin keluar dari sistem?');"
               class="text-red-300 hover:text-red-400 flex items-center gap-2 text-base font-medium">
                <i class="fas fa-sign-out-alt"></i>
                <span>Keluar Sistem</span>
            </a>
        </div>
    </div>
</div>
