<?php
session_start();
if (isset($_SESSION['user_id'])) {
    header("Location: dashboard.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <title>PrintKantor - Login</title>
    <?php include 'includes/head.php'; ?>
</head>
<body class="bg-gradient-to-br from-blue-900 to-indigo-900 min-h-screen flex items-center justify-center">
    <div class="bg-white rounded-3xl shadow-2xl w-full max-w-md overflow-hidden">
        <div class="bg-[#1e40af] text-white p-8 text-center">
            <div class="mx-auto w-20 h-20 bg-white text-[#1e40af] rounded-3xl flex items-center justify-center text-5xl mb-4">🖨️</div>
            <h1 class="text-3xl font-bold">PrintKantor</h1>
            <p class="text-blue-200">Alat Kantor & Printing Service</p>
        </div>

        <div class="flex">
            <button onclick="switchTab(0)" id="tab0" 
                    class="flex-1 py-5 font-semibold border-b-4 border-[#1e40af] text-[#1e40af]">Masuk</button>
            <button onclick="switchTab(1)" id="tab1" 
                    class="flex-1 py-5 font-semibold text-gray-500">Buat Akun Karyawan</button>
        </div>

        <!-- Form Login -->
        <div id="login-form" class="p-8">
            <form action="login.php" method="POST">
                <div class="space-y-6">
                    <div>
                        <label class="text-sm font-medium text-gray-600 mb-2 block">Username</label>
                        <input type="text" name="username" value="admin" required 
                               class="w-full px-6 py-4 border border-gray-300 rounded-2xl focus:outline-none focus:border-blue-600 text-lg">
                    </div>
                    <div>
                        <label class="text-sm font-medium text-gray-600 mb-2 block">Password</label>
                        <input type="password" name="password" value="admin" required 
                               class="w-full px-6 py-4 border border-gray-300 rounded-2xl focus:outline-none focus:border-blue-600 text-lg">
                    </div>
                    <button type="submit" class="w-full bg-[#1e40af] text-white py-4 rounded-3xl font-semibold text-xl hover:bg-blue-800 transition">
                        MASUK
                    </button>
                </div>
            </form>
        </div>

        <!-- Form Register -->
        <div id="register-form" class="p-8 hidden">
            <form action="register.php" method="POST">
                <div class="space-y-6">
                    <div>
                        <label class="text-sm font-medium text-gray-600 mb-2 block">Nama Lengkap</label>
                        <input type="text" name="nama_lengkap" required class="w-full px-6 py-4 border border-gray-300 rounded-2xl">
                    </div>
                    <div>
                        <label class="text-sm font-medium text-gray-600 mb-2 block">Kode Karyawan</label>
                        <input type="text" name="kode_karyawan" placeholder="KRY001" required 
                               class="w-full px-6 py-4 border border-gray-300 rounded-2xl">
                    </div>
                    <div>
                        <label class="text-sm font-medium text-gray-600 mb-2 block">Username</label>
                        <input type="text" name="username" required class="w-full px-6 py-4 border border-gray-300 rounded-2xl">
                    </div>
                    <div>
                        <label class="text-sm font-medium text-gray-600 mb-2 block">Password</label>
                        <input type="password" name="password" required class="w-full px-6 py-4 border border-gray-300 rounded-2xl">
                    </div>
                    <button type="submit" class="w-full bg-emerald-600 text-white py-4 rounded-3xl font-semibold text-xl hover:bg-emerald-700 transition">
                        BUAT AKUN BARU
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function switchTab(n) {
            document.getElementById('login-form').classList.toggle('hidden', n !== 0);
            document.getElementById('register-form').classList.toggle('hidden', n !== 1);
            document.getElementById('tab0').classList.toggle('border-b-4', n === 0);
            document.getElementById('tab0').classList.toggle('text-[#1e40af]', n === 0);
            document.getElementById('tab1').classList.toggle('border-b-4', n === 1);
            document.getElementById('tab1').classList.toggle('text-[#1e40af]', n === 1);
        }
    </script>
</body>
</html>