<?php
// --- FILE: dashboard.php ---
// Main Controller: Dashboard System (COMPLETE with Pickups)

require_once 'koneksi.php';

// Konfigurasi Menu Sidebar
$menu_config = [
    // Master Data
    'pelanggan' => ['icon' => 'fa-users', 'label' => 'Pelanggan'],
    'karyawan' => ['icon' => 'fa-user-tie', 'label' => 'Karyawan'],
    'layanan' => ['icon' => 'fa-shirt', 'label' => 'Layanan'],
    'akun' => ['icon' => 'fa-book', 'label' => 'Akun COA'],
    'perlengkapan' => ['icon' => 'fa-box-open', 'label' => 'Perlengkapan'],
    'metode_pembayaran' => ['icon' => 'fa-wallet', 'label' => 'Metode Bayar'],
    
    // Transaksi
    'orders' => ['icon' => 'fa-cart-shopping', 'label' => 'Order Laundry'],
    'pembayaran' => ['icon' => 'fa-cash-register', 'label' => 'Pembayaran'],
    'pengambilan_laundry' => ['icon' => 'fa-truck-ramp-box', 'label' => 'Pengambilan'], // NEW MODULE
    'pembelian_bahan_baku' => ['icon' => 'fa-boxes-packing', 'label' => 'Pembelian BB'],
    'pengeluaran_operasional' => ['icon' => 'fa-money-bill-transfer', 'label' => 'Pengeluaran Ops'],
    'penggunaan_bahan_baku' => ['icon' => 'fa-recycle', 'label' => 'Penggunaan BB'],
    
    // Laporan
    'laporan_sales' => ['icon' => 'fa-chart-line', 'label' => 'Laporan Sales'],
    'laporan_pembelian' => ['icon' => 'fa-clipboard-list', 'label' => 'Laporan Belanja'],
    'laporan_pengeluaran' => ['icon' => 'fa-file-invoice-dollar', 'label' => 'Laporan Biaya'],
    'laporan_pengambilan' => ['icon' => 'fa-chart-pie', 'label' => 'Laporan Pickup'], // NEW REPORT
    'laporan_pembayaran' => ['icon' => 'fa-wallet', 'label' => 'Laporan Metode Bayar'],
    'jurnal_umum' => ['icon' => 'fa-book-journal-whills', 'label' => 'Jurnal Umum'],
    
    'default' => ['icon' => 'fa-table', 'label' => 'Data Tabel']
];

$group_master = ['pelanggan', 'karyawan', 'layanan', 'akun', 'perlengkapan', 'metode_pembayaran'];
$group_transaksi = ['orders', 'pembayaran', 'pengambilan_laundry', 'pembelian_bahan_baku', 'pengeluaran_operasional', 'penggunaan_bahan_baku'];

$all_tables = [
    'akun', 'karyawan', 'pelanggan', 'layanan', 'perlengkapan', 
    'metode_pembayaran', 'orders', 'pembayaran', 'pengambilan_laundry', 
    'pembelian_bahan_baku', 'penggunaan_bahan_baku', 'pengeluaran_operasional', 
    'laporan_sales', 'laporan_pembelian', 'laporan_pengeluaran', 'laporan_pengambilan',
    'laporan_pembayaran', 'jurnal_umum', 'jurnal_pembayaran', 'jurnal_pembelian', 'jurnal_pengeluaran', 'jurnal_penggunaan'
];

$group_laporan = array_diff($all_tables, array_merge($group_master, $group_transaksi, ['jurnal_pembayaran', 'jurnal_pembelian', 'jurnal_pengeluaran', 'jurnal_penggunaan']));

$current_table = isset($_GET['table']) ? $_GET['table'] : 'dashboard';
if ($current_table !== 'dashboard' && !in_array($current_table, $all_tables)) {
    $current_table = 'dashboard';
}

function countRows($conn, $table) {
    if (strpos($table, 'laporan') !== false) return 0;
    $sql = "SELECT COUNT(*) as total FROM `$table`"; 
    $result = $conn->query($sql);
    return ($result) ? $result->fetch_assoc()['total'] : 0;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SIA Laundry Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap');
        body { font-family: 'Inter', sans-serif; }
        ::-webkit-scrollbar { width: 8px; height: 8px; }
        ::-webkit-scrollbar-track { background: #f1f1f1; }
        ::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 4px; }
        ::-webkit-scrollbar-thumb:hover { background: #94a3b8; }
        details > summary { list-style: none; cursor: pointer; }
        details > summary::-webkit-details-marker { display: none; }
        details[open] summary .chevron { transform: rotate(180deg); }
        .chevron { transition: transform 0.2s; }
        .custom-scrollbar::-webkit-scrollbar { width: 4px; }
    </style>
</head>
<body class="bg-gray-50 text-slate-800 font-sans h-screen flex overflow-hidden">

    <?php include 'sidebar.php'; ?>

    <main class="flex-1 flex flex-col h-full overflow-hidden bg-gray-50">
        <header class="bg-white border-b border-gray-200 px-8 py-4 flex justify-between items-center shadow-sm flex-shrink-0">
            <div>
                <h2 class="text-2xl font-bold text-gray-800 capitalize flex items-center gap-3">
                    <?php 
                        $curr_icon = $menu_config[$current_table]['icon'] ?? 'fa-table';
                        if ($current_table != 'dashboard') echo "<i class='fa-solid $curr_icon text-blue-500'></i>";
                        echo $menu_config[$current_table]['label'] ?? str_replace('_', ' ', ucfirst($current_table)); 
                    ?>
                </h2>
                <p class="text-sm text-gray-500 mt-1 ml-1">
                    <?php echo $current_table == 'dashboard' ? 'Visualisasi Data Laundry' : 'Modul Aktif'; ?>
                </p>
            </div>
        </header>

        <div class="flex-1 overflow-y-auto p-6 md:p-8">
            <?php 
            // ROUTING MODUL
            if ($current_table == 'layanan') { include 'modul_layanan.php'; }
            elseif ($current_table == 'orders') { include 'modul_orders.php'; }
            elseif ($current_table == 'pembelian_bahan_baku') { include 'modul_pembelian.php'; }
            elseif ($current_table == 'karyawan') { include 'modul_karyawan.php'; }
            elseif ($current_table == 'pembayaran') { include 'modul_pembayaran.php'; }
            elseif ($current_table == 'pelanggan') { include 'modul_pelanggan.php'; }
            elseif ($current_table == 'pengeluaran_operasional') { include 'modul_pengeluaran.php'; }
            elseif ($current_table == 'pengambilan_laundry') { include 'modul_pengambilan.php'; } 
            elseif ($current_table == 'perlengkapan') { include 'modul_perlengkapan.php'; }
            elseif ($current_table == 'metode_pembayaran') { include 'modul_metode_pembayaran.php'; }
            elseif ($current_table == 'akun') { include 'modul_akun.php'; }
            elseif ($current_table == 'penggunaan_bahan_baku') { include 'modul_penggunaan.php'; }
            elseif (strpos($current_table, 'jurnal_') === 0) { 
                include 'modul_jurnal.php'; 
            }
            
            // ROUTING LAPORAN
            elseif ($current_table == 'laporan_sales') { include 'modul_laporan.php'; }
            elseif ($current_table == 'laporan_pembelian') { include 'modul_laporan_pembelian.php'; }
            elseif ($current_table == 'laporan_pengeluaran') { include 'modul_laporan_pengeluaran.php'; }
            elseif ($current_table == 'laporan_pengambilan') { include 'modul_laporan_pengambilan.php'; } 
            elseif ($current_table == 'laporan_pembayaran') { include 'modul_laporan_pembayaran.php'; }
        
            
            // DASHBOARD
            elseif ($current_table == 'dashboard') {
                $q_omset = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT SUM(total_order) as total FROM orders WHERE status_order='Selesai'"));
                $total_omset = $q_omset['total'] ?? 0;
                $total_order = countRows($koneksi, 'orders');
                $total_plg = countRows($koneksi, 'pelanggan');

                $q_status = mysqli_query($koneksi, "SELECT status_order, COUNT(*) as jumlah FROM orders GROUP BY status_order");
                $labels_status = []; $data_status = [];
                while($row = mysqli_fetch_assoc($q_status)) { $labels_status[] = $row['status_order']; $data_status[] = $row['jumlah']; }

                $q_layanan = mysqli_query($koneksi, "SELECT l.nama_layanan, COUNT(o.id_order) as jumlah FROM orders o JOIN layanan l ON o.id_layanan = l.id_layanan GROUP BY o.id_layanan ORDER BY jumlah DESC LIMIT 5");
                $labels_lyn = []; $data_lyn = [];
                while($row = mysqli_fetch_assoc($q_layanan)) { $labels_lyn[] = $row['nama_layanan']; $data_lyn[] = $row['jumlah']; }
            ?>
                <!-- Dashboard Visual Cards & Charts -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                    <div class="bg-gradient-to-r from-blue-600 to-blue-500 rounded-2xl p-6 text-white shadow-lg shadow-blue-200">
                        <div class="flex justify-between items-start">
                            <div><p class="text-blue-100 text-sm font-medium">Total Omset</p><h3 class="text-3xl font-bold mt-1">Rp <?= number_format($total_omset, 0, ',', '.') ?></h3></div>
                            <div class="p-3 bg-white/20 rounded-lg"><i class="fa-solid fa-money-bill-wave text-xl"></i></div>
                        </div>
                    </div>
                    <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100">
                        <div class="flex justify-between items-start">
                            <div><p class="text-gray-500 text-sm font-medium">Total Pesanan</p><h3 class="text-3xl font-bold text-gray-800 mt-1"><?= $total_order ?></h3></div>
                            <div class="p-3 bg-indigo-50 text-indigo-600 rounded-lg"><i class="fa-solid fa-basket-shopping text-xl"></i></div>
                        </div>
                    </div>
                    <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100">
                        <div class="flex justify-between items-start">
                            <div><p class="text-gray-500 text-sm font-medium">Pelanggan</p><h3 class="text-3xl font-bold text-gray-800 mt-1"><?= $total_plg ?></h3></div>
                            <div class="p-3 bg-green-50 text-green-600 rounded-lg"><i class="fa-solid fa-users text-xl"></i></div>
                        </div>
                    </div>
                </div>
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
                    <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100"><h3 class="text-lg font-bold text-gray-800 mb-4">🏆 Layanan Terlaris</h3><canvas id="chartLayanan" style="max-height: 300px;"></canvas></div>
                    <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100"><h3 class="text-lg font-bold text-gray-800 mb-4">📊 Status Pesanan</h3><div class="w-full h-64 flex justify-center"><canvas id="chartStatus" style="max-height: 250px;"></canvas></div></div>
                </div>
                <script>
                    const ctxLayanan = document.getElementById('chartLayanan');
                    new Chart(ctxLayanan, {type: 'bar', data: {labels: <?= json_encode($labels_lyn) ?>, datasets: [{label: 'Order', data: <?= json_encode($data_lyn) ?>, backgroundColor: ['#3b82f6', '#10b981', '#f59e0b', '#ef4444', '#8b5cf6'], borderRadius: 8}]}, options: {responsive: true, scales: {y: {beginAtZero: true}}, plugins: {legend: {display: false}}}});
                    const ctxStatus = document.getElementById('chartStatus');
                    new Chart(ctxStatus, {type: 'doughnut', data: {labels: <?= json_encode($labels_status) ?>, datasets: [{data: <?= json_encode($data_status) ?>, backgroundColor: ['#10b981', '#f59e0b', '#ef4444', '#94a3b8'], borderWidth: 0}]}, options: {responsive: true, maintainAspectRatio: false, plugins: {legend: {position: 'bottom'}}}});
                </script>
            <?php 
            }
            // GENERIC TABLE
            else {
                $sql_data = "SELECT * FROM `$current_table`"; $result_data = $koneksi->query($sql_data);
            ?>
                <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden flex flex-col">
                    <?php if ($result_data && $result_data->num_rows > 0): ?>
                        <div class="overflow-x-auto"><table class="w-full text-left border-collapse"><thead><tr class="bg-gray-100 border-b border-gray-200"><?php $fields = $result_data->fetch_fields(); foreach ($fields as $field): ?><th class="px-6 py-4 text-xs font-semibold text-gray-600 uppercase tracking-wider whitespace-nowrap"><?php echo $field->name; ?></th><?php endforeach; ?></tr></thead><tbody class="divide-y divide-gray-200"><?php while($row = $result_data->fetch_assoc()): ?><tr class="hover:bg-blue-50/50 transition-colors"><?php foreach ($row as $key => $cell): ?><td class="px-6 py-4 text-sm text-gray-700 whitespace-nowrap"><?php if (preg_match('/(harga|total|nominal|bayar|beli|jurnal)/i', $key) && is_numeric($cell)) { echo "Rp " . number_format($cell, 0, ',', '.'); } else { echo htmlspecialchars($cell); } ?></td><?php endforeach; ?></tr><?php endwhile; ?></tbody></table></div>
                    <?php else: ?><div class="p-12 text-center text-gray-500">Belum ada data di tabel ini.</div><?php endif; ?>
                </div>
            <?php } ?>
        </div>
    </main>
</body>
</html>