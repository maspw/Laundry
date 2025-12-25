<?php
// --- FILE: modul_laporan.php ---
// Logic: Laporan Penjualan & Analisis Grafik

// 1. FILTER WAKTU
$bulan_pilih = isset($_GET['bulan']) ? $_GET['bulan'] : date('m');
$tahun_pilih = isset($_GET['tahun']) ? $_GET['tahun'] : date('Y');

$nama_bulan = [
    '01'=>'Januari', '02'=>'Februari', '03'=>'Maret', '04'=>'April', 
    '05'=>'Mei', '06'=>'Juni', '07'=>'Juli', '08'=>'Agustus', 
    '09'=>'September', '10'=>'Oktober', '11'=>'November', '12'=>'Desember'
];

// 2. QUERY: Top Layanan
$q_top = "SELECT l.nama_layanan, COUNT(o.id_order) as jumlah_transaksi, SUM(o.total_order) as total_omzet
          FROM orders o JOIN layanan l ON o.id_layanan = l.id_layanan
          WHERE MONTH(o.tgl_order) = '$bulan_pilih' AND YEAR(o.tgl_order) = '$tahun_pilih'
          GROUP BY o.id_layanan ORDER BY total_omzet DESC";
$r_top = mysqli_query($koneksi, $q_top);

// 3. QUERY: Grafik Harian
$q_daily = "SELECT DATE(tgl_order) as tanggal, SUM(total_order) as omzet
            FROM orders
            WHERE MONTH(tgl_order) = '$bulan_pilih' AND YEAR(tgl_order) = '$tahun_pilih'
            GROUP BY tanggal ORDER BY tanggal ASC";
$r_daily = mysqli_query($koneksi, $q_daily);

$chart_label = [];
$chart_data  = [];
$total_bulan_ini = 0;

while($d = mysqli_fetch_assoc($r_daily)) {
    $chart_label[] = date('d M', strtotime($d['tanggal']));
    $chart_data[]  = $d['omzet'];
    $total_bulan_ini += $d['omzet'];
}
?>

<!-- HEADER & FILTER -->
<div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-6 gap-4">
    <div>
        <h2 class="text-2xl font-bold text-gray-800">📈 Laporan Penjualan</h2>
        <p class="text-sm text-gray-500">Analisis performa laundry periode <?= $nama_bulan[$bulan_pilih] ?> <?= $tahun_pilih ?></p>
    </div>
    
    <form action="" method="GET" class="bg-white p-2 rounded-lg shadow-sm flex items-center gap-2 border border-gray-200">
        <input type="hidden" name="table" value="laporan_sales"> <!-- Penting biar tetep di halaman ini -->
        <select name="bulan" class="p-2 border rounded text-sm bg-gray-50 outline-none focus:ring-2 focus:ring-blue-500">
            <?php foreach($nama_bulan as $k => $v): ?>
                <option value="<?= $k ?>" <?= ($k == $bulan_pilih) ? 'selected' : '' ?>><?= $v ?></option>
            <?php endforeach; ?>
        </select>
        <select name="tahun" class="p-2 border rounded text-sm bg-gray-50 outline-none focus:ring-2 focus:ring-blue-500">
            <?php for($i=2024; $i<=date('Y'); $i++): ?>
                <option value="<?= $i ?>" <?= ($i == $tahun_pilih) ? 'selected' : '' ?>><?= $i ?></option>
            <?php endfor; ?>
        </select>
        <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded text-sm font-bold transition">
            <i class="fa-solid fa-filter"></i> Filter
        </button>
    </form>
</div>

<!-- SUMMARY CARDS -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
    <!-- Card 1: Total Omzet -->
    <div class="bg-gradient-to-br from-blue-600 to-indigo-600 p-6 rounded-xl shadow-lg text-white relative overflow-hidden group">
        <div class="absolute right-0 top-0 opacity-10 transform translate-x-2 -translate-y-2 group-hover:scale-110 transition">
            <i class="fa-solid fa-chart-line text-8xl"></i>
        </div>
        <p class="text-blue-100 text-xs font-bold uppercase tracking-wider mb-1">Total Omzet</p>
        <h2 class="text-3xl font-bold">Rp <?= number_format($total_bulan_ini, 0, ',', '.') ?></h2>
        <p class="text-xs text-blue-200 mt-2">Periode terpilih</p>
    </div>
    
    <!-- Card 2: Layanan Terlaris -->
    <?php 
    mysqli_data_seek($r_top, 0); 
    $juara = mysqli_fetch_assoc($r_top);
    ?>
    <div class="bg-white p-6 rounded-xl shadow border border-gray-100 relative overflow-hidden">
        <div class="flex justify-between items-start">
            <div>
                <p class="text-gray-400 text-xs font-bold uppercase tracking-wider mb-1">Layanan Terlaris 🏆</p>
                <h2 class="text-xl font-bold text-gray-800 truncate pr-2">
                    <?= $juara ? $juara['nama_layanan'] : '-' ?>
                </h2>
                <p class="text-sm text-green-600 mt-1 font-semibold bg-green-50 inline-block px-2 py-0.5 rounded">
                    <?= $juara ? $juara['jumlah_transaksi'].' Transaksi' : 'No Data' ?>
                </p>
            </div>
            <div class="bg-yellow-100 text-yellow-600 p-3 rounded-lg"><i class="fa-solid fa-crown text-xl"></i></div>
        </div>
    </div>
</div>

<!-- CONTENT: CHART & TABLE -->
<div class="grid grid-cols-1 lg:grid-cols-3 gap-8 mb-8">
    
    <!-- GRAFIK -->
    <div class="lg:col-span-2 bg-white p-6 rounded-xl shadow border border-gray-100">
        <h3 class="font-bold text-gray-700 mb-6 flex items-center gap-2 border-b pb-2">
            <i class="fa-solid fa-chart-area text-blue-500"></i> Tren Pendapatan Harian
        </h3>
        <div class="relative w-full h-72">
            <canvas id="salesReportChart"></canvas>
        </div>
    </div>

    <!-- TABEL DETAIL -->
    <div class="lg:col-span-1 bg-white p-6 rounded-xl shadow border border-gray-100 flex flex-col">
        <h3 class="font-bold text-gray-700 mb-4 flex items-center gap-2 border-b pb-2">
            <i class="fa-solid fa-list-ol text-purple-500"></i> Detail Layanan
        </h3>
        <div class="overflow-y-auto flex-1 pr-1 custom-scrollbar" style="max-height: 300px;">
            <table class="w-full text-sm text-left">
                <thead class="bg-gray-50 text-gray-500 sticky top-0">
                    <tr>
                        <th class="py-2 px-2 rounded-l">Layanan</th>
                        <th class="py-2 px-2 text-center">Trx</th>
                        <th class="py-2 px-2 text-right rounded-r">Omzet</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    <?php 
                    if(mysqli_num_rows($r_top) > 0) {
                        mysqli_data_seek($r_top, 0);
                        while($row = mysqli_fetch_assoc($r_top)): 
                    ?>
                        <tr class="hover:bg-gray-50 transition">
                            <td class="py-3 px-2 font-medium text-gray-700 truncate max-w-[100px]" title="<?= $row['nama_layanan'] ?>">
                                <?= $row['nama_layanan'] ?>
                            </td>
                            <td class="py-3 px-2 text-center">
                                <span class="bg-blue-100 text-blue-600 text-[10px] px-2 py-0.5 rounded-full font-bold">
                                    <?= $row['jumlah_transaksi'] ?>
                                </span>
                            </td>
                            <td class="py-3 px-2 text-right font-mono text-gray-600 text-xs">
                                <?= number_format($row['total_omzet'], 0, ',', '.') ?>
                            </td>
                        </tr>
                    <?php 
                        endwhile;
                    } else {
                        echo "<tr><td colspan='3' class='text-center py-4 text-gray-400 italic text-xs'>Belum ada data.</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- SCRIPT CHART KHUSUS HALAMAN INI -->
<script>
    // Cek biar gak double instance chart kalo di-load ulang
    if (window.mySalesReportChart instanceof Chart) {
        window.mySalesReportChart.destroy();
    }

    var ctxReport = document.getElementById('salesReportChart').getContext('2d');
    
    // Bikin gradient warna biar estetik
    var gradient = ctxReport.createLinearGradient(0, 0, 0, 400);
    gradient.addColorStop(0, 'rgba(37, 99, 235, 0.2)'); // Blue atas
    gradient.addColorStop(1, 'rgba(37, 99, 235, 0)');   // Transparan bawah

    window.mySalesReportChart = new Chart(ctxReport, {
        type: 'line',
        data: {
            labels: <?= json_encode($chart_label) ?>,
            datasets: [{
                label: 'Omzet Harian',
                data: <?= json_encode($chart_data) ?>,
                borderColor: '#2563EB',
                backgroundColor: gradient,
                borderWidth: 2,
                tension: 0.4, // Melengkung
                fill: true,
                pointBackgroundColor: '#ffffff',
                pointBorderColor: '#2563EB',
                pointBorderWidth: 2,
                pointRadius: 4,
                pointHoverRadius: 6
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            interaction: { intersect: false, mode: 'index' },
            plugins: {
                legend: { display: false },
                tooltip: {
                    backgroundColor: 'rgba(0,0,0,0.8)',
                    padding: 10,
                    callbacks: {
                        label: function(context) {
                            return ' Rp ' + new Intl.NumberFormat('id-ID').format(context.parsed.y);
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    grid: { borderDash: [4, 4], color: '#f3f4f6' },
                    ticks: {
                        callback: function(value) { return 'Rp ' + (value / 1000) + 'k'; },
                        font: { size: 10 }
                    }
                },
                x: {
                    grid: { display: false },
                    ticks: { font: { size: 10 } }
                }
            }
        }
    });
</script>