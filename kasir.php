<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}
require_once 'config.php';

$message = '';
$error = '';

// Handle Checkout
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['checkout'])) {
    $cart = json_decode($_POST['cart_data'], true);
    $bayar = $_POST['bayar'];
    $total = $_POST['total'];
    $kembalian = $bayar - $total;
    $active_tab = isset($_POST['active_tab']) ? $_POST['active_tab'] : 'Pelanggan 1';
    
    if (empty($cart)) {
        $error = "Keranjang masih kosong!";
    } elseif ($bayar < $total) {
        $error = "Pembayaran kurang!";
    } else {
        try {
            $pdo->beginTransaction();
            
            // Generate Invoice Number
            $no_inv = "INV-" . date('Ymd') . "-" . str_pad(rand(1, 999), 3, '0', STR_PAD_LEFT);
            
            // Insert Pesanan
            $stmt = $pdo->prepare("INSERT INTO pesanan (no_inv, total, bayar, kembalian, status, karyawan_id) VALUES (?, ?, ?, ?, 'Selesai', ?)");
            $stmt->execute([$no_inv, $total, $bayar, $kembalian, $_SESSION['user_id']]);
            $pesanan_id = $pdo->lastInsertId();
            
            foreach ($cart as $item) {
                // Insert Detail Pesanan
                $stmt = $pdo->prepare("INSERT INTO detail_pesanan (pesanan_id, produk_id, qty, harga_satuan, subtotal) VALUES (?, ?, ?, ?, ?)");
                $stmt->execute([$pesanan_id, $item['id'], $item['qty'], $item['price'], $item['qty'] * $item['price']]);
                
                // Update Stock if it's a 'Produk'
                $prod = $pdo->prepare("SELECT kategori FROM produk WHERE id = ?");
                $prod->execute([$item['id']]);
                $kategori = $prod->fetchColumn();
                
                if ($kategori == 'Produk') {
                    $upd = $pdo->prepare("UPDATE produk SET stok = stok - ? WHERE id = ?");
                    $upd->execute([$item['qty'], $item['id']]);
                }
            }
            
            $pdo->commit();
            $message = "Transaksi Berhasil! No Invoice: $no_inv";
            // Script to open receipt in new tab and clear specific cart
            echo "<script>
                let saved = JSON.parse(localStorage.getItem('kasir_carts')) || {'Pelanggan 1':[], 'Pelanggan 2':[]};
                saved['$active_tab'] = [];
                localStorage.setItem('kasir_carts', JSON.stringify(saved));
                window.open('print_struk.php?id=$pesanan_id', '_blank');
            </script>";
        } catch (Exception $e) {
            $pdo->rollBack();
            $error = "Terjadi kesalahan: " . $e->getMessage();
        }
    }
}

// Fetch all products for selection
$all_produk = $pdo->query("SELECT * FROM produk ORDER BY nama ASC")->fetchAll();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <title>PrintKantor - Kasir</title>
    <?php include 'includes/head.php'; ?>
</head>
<body class="bg-gray-100 flex h-screen overflow-hidden">
    <?php include 'includes/sidebar.php'; ?>

    <main class="flex-1 flex flex-col">
        <!-- Header -->
        <header class="bg-white border-b border-gray-200 px-8 py-4 flex justify-between items-center">
            <h1 class="text-2xl font-bold text-gray-800">Kasir / POS</h1>
            <div class="flex items-center gap-4">
                <div class="text-right">
                    <p class="text-sm font-semibold text-gray-700"><?php echo date('d F Y'); ?></p>
                    <p class="text-xs text-gray-500" id="live-clock">00:00:00</p>
                </div>
                <div class="w-10 h-10 bg-blue-100 text-blue-600 rounded-full flex items-center justify-center">
                    <i class="fas fa-cash-register"></i>
                </div>
            </div>
        </header>

        <div class="flex-1 flex overflow-hidden">
            <!-- Left Side: Product Selection -->
            <div class="w-2/3 p-6 overflow-y-auto custom-scrollbar">
                <div class="grid grid-cols-2 gap-4 mb-6">
                    <div class="relative flex-1">
                        <i class="fas fa-barcode absolute left-4 top-1/2 -translate-y-1/2 text-gray-400"></i>
                        <input type="text" id="barcode-scan" placeholder="Scan Barcode (Mode Fokus)..." 
                               class="w-full pl-12 pr-4 py-4 bg-white border-2 border-blue-100 rounded-2xl focus:outline-none focus:border-blue-500 shadow-sm transition font-mono font-bold text-blue-600">
                    </div>
                    <div class="relative flex-1">
                        <i class="fas fa-search absolute left-4 top-1/2 -translate-y-1/2 text-gray-400"></i>
                        <input type="text" id="product-search" placeholder="Cari nama produk/jasa..." 
                               class="w-full pl-12 pr-4 py-4 bg-white border border-gray-200 rounded-2xl focus:outline-none focus:border-blue-500 shadow-sm transition">
                    </div>
                </div>

                <div class="grid grid-cols-2 lg:grid-cols-3 gap-4" id="product-grid">
                    <?php foreach($all_produk as $p): ?>
                    <button onclick='addToCart(<?php echo json_encode($p); ?>)'
                            data-nama="<?php echo strtolower($p['nama']); ?>"
                            data-barcode="<?php echo strtolower($p['barcode']); ?>"
                            class="product-card bg-white p-5 rounded-3xl border border-transparent hover:border-blue-500 hover:shadow-xl hover:shadow-blue-100 transition text-left group">
                        <div class="flex justify-between items-start mb-4">
                            <span class="px-2 py-1 rounded-lg text-[10px] font-bold uppercase <?php echo $p['kategori'] == 'Produk' ? 'bg-blue-50 text-blue-600' : 'bg-purple-50 text-purple-600'; ?>">
                                <?php echo $p['kategori']; ?>
                            </span>
                            <?php if ($p['kategori'] == 'Produk'): ?>
                                <span class="text-xs <?php echo $p['stok'] <= 5 ? 'text-red-500 font-bold' : 'text-gray-400'; ?>">
                                    Stok: <?php echo $p['stok']; ?>
                                </span>
                            <?php endif; ?>
                        </div>
                        <h3 class="font-bold text-gray-800 group-hover:text-blue-600 transition line-clamp-2"><?php echo $p['nama']; ?></h3>
                        <p class="text-blue-600 font-bold text-lg mt-2">Rp <?php echo number_format($p['harga_jual'], 0, ',', '.'); ?></p>
                        <div class="mt-4 flex items-center gap-2 text-[10px] text-gray-400">
                            <i class="fas fa-tag"></i> <span><?php echo $p['satuan']; ?></span>
                        </div>
                    </button>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Right Side: Cart -->
            <div class="w-1/3 bg-white border-l border-gray-200 flex flex-col">
                <div class="p-4 border-b border-gray-100 flex justify-between items-center bg-white">
                    <h2 class="text-lg font-bold text-gray-800 flex items-center gap-2">
                        <i class="fas fa-shopping-basket text-blue-600"></i> Keranjang
                    </h2>
                    <div class="flex bg-gray-100 p-1 rounded-xl">
                        <button id="tab-1" class="px-3 py-1.5 text-sm font-bold rounded-lg shadow-sm bg-white text-blue-600 transition" onclick="switchTab('Pelanggan 1')" type="button">Pelanggan 1</button>
                        <button id="tab-2" class="px-3 py-1.5 text-sm font-bold rounded-lg text-gray-500 hover:text-gray-700 transition" onclick="switchTab('Pelanggan 2')" type="button">Pelanggan 2</button>
                    </div>
                </div>

                <!-- Cart Items -->
                <div class="flex-1 overflow-y-auto p-6 custom-scrollbar space-y-4" id="cart-items">
                    <!-- Dynamic Cart items here -->
                    <div class="text-center py-20 text-gray-400" id="empty-cart-msg">
                        <i class="fas fa-shopping-cart text-5xl mb-4 opacity-20"></i>
                        <p>Keranjang Kosong</p>
                    </div>
                </div>

                <!-- Summary -->
                <div class="p-6 bg-gray-50 border-t border-gray-200">
                    <?php if ($error): ?>
                        <div class="bg-red-50 text-red-600 p-3 rounded-xl mb-4 text-xs">
                            <i class="fas fa-exclamation-circle mr-1"></i> <?php echo $error; ?>
                        </div>
                    <?php endif; ?>
                    <?php if ($message): ?>
                        <div class="bg-emerald-50 text-emerald-600 p-3 rounded-xl mb-4 text-xs">
                            <i class="fas fa-check-circle mr-1"></i> <?php echo $message; ?>
                        </div>
                    <?php endif; ?>

                    <div class="space-y-2 mb-6">
                        <div class="flex justify-between text-gray-500">
                            <span>Subtotal</span>
                            <span id="subtotal">Rp 0</span>
                        </div>
                        <div class="flex justify-between text-2xl font-black text-gray-800 pt-2 border-t border-gray-200">
                            <span>TOTAL</span>
                            <span id="grand-total">Rp 0</span>
                        </div>
                    </div>

                    <form action="" method="POST" id="checkout-form">
                        <input type="hidden" name="checkout" value="1">
                        <input type="hidden" name="active_tab" id="active_tab" value="Pelanggan 1">
                        <input type="hidden" name="cart_data" id="cart_data">
                        <input type="hidden" name="total" id="hidden_total">
                        
                        <div class="mb-4">
                            <label class="block text-xs font-bold text-gray-400 mb-2 uppercase">Nominal Bayar</label>
                            <input type="number" name="bayar" id="input-bayar" required placeholder="0"
                                   class="w-full px-4 py-4 bg-white border-2 border-blue-100 rounded-2xl text-2xl font-bold text-blue-600 focus:outline-none focus:border-blue-500 transition">
                        </div>

                        <div class="flex justify-between items-center mb-6 bg-blue-50 p-4 rounded-2xl">
                            <span class="text-blue-500 font-bold">Kembalian</span>
                            <span id="kembalian" class="text-xl font-black text-blue-700">Rp 0</span>
                        </div>

                        <button type="submit" class="w-full bg-[#1e40af] text-white py-5 rounded-3xl font-black text-xl shadow-xl shadow-blue-200 hover:bg-blue-800 transition active:transform active:scale-95 disabled:opacity-50 disabled:cursor-not-allowed" id="btn-checkout" disabled>
                            KONFIRMASI BAYAR
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </main>

    <script>
        let carts = JSON.parse(localStorage.getItem('kasir_carts')) || {
            'Pelanggan 1': [],
            'Pelanggan 2': []
        };
        let activeCart = localStorage.getItem('kasir_active_cart') || 'Pelanggan 1';

        function saveCarts() {
            localStorage.setItem('kasir_carts', JSON.stringify(carts));
            localStorage.setItem('kasir_active_cart', activeCart);
        }

        function switchTab(tabName) {
            activeCart = tabName;
            document.getElementById('active_tab').value = tabName;
            saveCarts();
            
            const tab1 = document.getElementById('tab-1');
            const tab2 = document.getElementById('tab-2');
            
            if (tabName === 'Pelanggan 1') {
                tab1.className = 'px-3 py-1.5 text-sm font-bold rounded-lg shadow-sm bg-white text-blue-600 transition';
                tab2.className = 'px-3 py-1.5 text-sm font-bold rounded-lg text-gray-500 hover:text-gray-700 transition';
            } else {
                tab2.className = 'px-3 py-1.5 text-sm font-bold rounded-lg shadow-sm bg-white text-blue-600 transition';
                tab1.className = 'px-3 py-1.5 text-sm font-bold rounded-lg text-gray-500 hover:text-gray-700 transition';
            }
            
            renderCart();
            document.getElementById('barcode-scan').focus();
        }

        document.addEventListener('DOMContentLoaded', () => {
            switchTab(activeCart);
        });

        function addToCart(product) {
            const cart = carts[activeCart];
            const existing = cart.find(item => item.id === product.id);
            if (existing) {
                existing.qty++;
            } else {
                cart.push({
                    id: product.id,
                    name: product.nama,
                    price: parseFloat(product.harga_jual),
                    qty: 1,
                    satuan: product.satuan
                });
            }
            saveCarts();
            renderCart();
        }

        function removeFromCart(id) {
            carts[activeCart] = carts[activeCart].filter(item => item.id !== id);
            saveCarts();
            renderCart();
        }

        function updateQty(id, change) {
            const item = carts[activeCart].find(item => item.id === id);
            if (item) {
                item.qty += change;
                if (item.qty <= 0) {
                    removeFromCart(id);
                    return;
                }
            }
            saveCarts();
            renderCart();
        }

        function renderCart() {
            const cart = carts[activeCart];
            const container = document.getElementById('cart-items');
            const emptyMsg = document.getElementById('empty-cart-msg');
            
            if (cart.length === 0) {
                container.innerHTML = '';
                container.appendChild(emptyMsg);
                emptyMsg.classList.remove('hidden');
                updateTotals(0);
                return;
            }

            emptyMsg.classList.add('hidden');
            container.innerHTML = cart.map(item => `
                <div class="flex items-center gap-4 bg-gray-50 p-4 rounded-2xl group">
                    <div class="flex-1">
                        <h4 class="font-bold text-gray-800 text-sm">${item.name}</h4>
                        <p class="text-xs text-blue-600 font-semibold">Rp ${item.price.toLocaleString('id-ID')}</p>
                    </div>
                    <div class="flex items-center bg-white rounded-xl shadow-sm border border-gray-100">
                        <button onclick="updateQty(${item.id}, -1)" class="w-8 h-8 flex items-center justify-center text-gray-400 hover:text-red-500">-</button>
                        <span class="w-8 text-center font-bold text-xs">${item.qty}</span>
                        <button onclick="updateQty(${item.id}, 1)" class="w-8 h-8 flex items-center justify-center text-gray-400 hover:text-blue-500">+</button>
                    </div>
                    <button onclick="removeFromCart(${item.id})" class="text-gray-300 hover:text-red-500 transition">
                        <i class="fas fa-trash-alt"></i>
                    </button>
                </div>
            `).join('');

            const total = cart.reduce((sum, item) => sum + (item.price * item.qty), 0);
            updateTotals(total);
        }

        function updateTotals(total) {
            const cart = carts[activeCart];
            document.getElementById('subtotal').innerText = 'Rp ' + total.toLocaleString('id-ID');
            document.getElementById('grand-total').innerText = 'Rp ' + total.toLocaleString('id-ID');
            document.getElementById('cart_data').value = JSON.stringify(cart);
            document.getElementById('hidden_total').value = total;
            
            const btn = document.getElementById('btn-checkout');
            btn.disabled = cart.length === 0;
            
            calculateKembalian();
        }

        function calculateKembalian() {
            const total = parseFloat(document.getElementById('hidden_total').value) || 0;
            const bayar = parseFloat(document.getElementById('input-bayar').value) || 0;
            const kembalian = bayar - total;
            
            const kembalianEl = document.getElementById('kembalian');
            kembalianEl.innerText = 'Rp ' + (kembalian < 0 ? 0 : kembalian).toLocaleString('id-ID');
            
            const btn = document.getElementById('btn-checkout');
            if (bayar < total && total > 0) {
                btn.classList.add('opacity-50');
                btn.disabled = true;
            } else if (total > 0) {
                btn.classList.remove('opacity-50');
                btn.disabled = false;
            }
        }

        document.getElementById('input-bayar').addEventListener('input', calculateKembalian);

        // Live Search
        document.getElementById('product-search').addEventListener('input', function(e) {
            const q = e.target.value.toLowerCase();
            document.querySelectorAll('.product-card').forEach(card => {
                const name = card.getAttribute('data-nama');
                card.classList.toggle('hidden', !name.includes(q));
            });
        });

        // Barcode Scanning
        const barcodeInput = document.getElementById('barcode-scan');
        const allProductsData = <?php echo json_encode($all_produk); ?>;

        barcodeInput.focus();

        barcodeInput.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                const code = this.value.trim();
                if (code) {
                    const product = allProductsData.find(p => p.barcode === code);
                    if (product) {
                        addToCart(product);
                        this.value = '';
                    } else {
                        // Optional: alert or indicator for not found
                        this.select();
                        console.log("Barcode tidak ditemukan: " + code);
                    }
                }
            }
        });

        // Refocus barcode input when any modal/window clicked (to ensure scanning is always ready)
        document.addEventListener('click', function(e) {
            if (e.target.tagName !== 'INPUT' && e.target.tagName !== 'SELECT' && e.target.tagName !== 'TEXTAREA') {
                barcodeInput.focus();
            }
        });

        // Clock
        setInterval(() => {
            const now = new Date();
            document.getElementById('live-clock').innerText = now.toLocaleTimeString('id-ID');
        }, 1000);
    </script>
</body>
</html>
