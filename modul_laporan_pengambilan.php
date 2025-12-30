<?php
// --- 1. IMPORT KONEKSI ---
require_once 'koneksi.php';

// --- 2. LOGIKA FILTER PERIODE ---
$bulan_pilih = isset($_GET['bulan']) ? $_GET['bulan'] : date('m');
$tahun_pilih = isset($_GET['tahun']) ? $_GET['tahun'] : date('Y');

// Deteksi parameter navigasi agar tidak balik ke dashboard
$hal_aktif = isset($_GET['table']) ? $_GET['table'] : 'laporan_pengambilan';

$nama_bulan = [
    '01'=>'Januari', '02'=>'Februari', '03'=>'Maret', '04'=>'April', 
    '05'=>'Mei', '06'=>'Juni', '07'=>'Juli', '08'=>'Agustus', 
    '09'=>'September', '10'=>'Oktober', '11'=>'November', '12'=>'Desember'
];

// --- 3. QUERY DATA REKAP PENGAMBILAN ---
// Menghitung total dan status pengambilan dalam periode terpilih
$query_stats = "SELECT 
                    COUNT(*) as total_ambil,
                    SUM(CASE WHEN status_pengambilan = 'Sudah Diambil' THEN 1 ELSE 0 END) as total_selesai
                FROM pengambilan_laundry 
                WHERE MONTH(tgl_ambil) = '$bulan_pilih' AND YEAR(tgl_ambil) = '$tahun_pilih'";
$res_stats = mysqli_fetch_assoc(mysqli_query($koneksi, $query_stats));

$grand_total_trx = $res_stats['total_ambil'];
$grand_total_selesai = $res_stats['total_selesai'];

// Data untuk Tabel Detail
$q_detail = "SELECT * FROM pengambilan_laundry 
             WHERE MONTH(tgl_ambil) = '$bulan_pilih' AND YEAR(tgl_ambil) = '$tahun_pilih'
             ORDER BY tgl_ambil DESC";
$r_detail = mysqli_query($koneksi, $q_detail);

// Data untuk Grafik Tren (Harian dalam bulan tersebut)
$q_grafik = "SELECT tgl_ambil, COUNT(*) as jumlah 
             FROM pengambilan_laundry 
             WHERE MONTH(tgl_ambil) = '$bulan_pilih' AND YEAR(tgl_ambil) = '$tahun_pilih'
             GROUP BY tgl_ambil 
             ORDER BY tgl_ambil ASC";
$r_grafik = mysqli_query($koneksi, $q_grafik);

$chart_label = [];
$chart_data  = [];
while($g = mysqli_fetch_assoc($r_grafik)) {
    $chart_label[] = date('d M', strtotime($g['tgl_ambil']));
    $chart_data[]  = $g['jumlah'];
}
?>

<!-- HEADER & FILTER -->
<div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-6 gap-4">
    <div>
        <h2 class="text-2xl font-bold text-slate-800 uppercase tracking-tight">📊 Laporan Pengambilan Barang</h2>
        <p class="text-sm text-slate-500 font-semibold">Periode: <span class="text-blue-600"><?= $nama_bulan[$bulan_pilih] ?> <?= $tahun_pilih ?></span></p>
    </div>
    
    <form action="" method="GET" class="bg-white p-2 rounded-xl shadow-sm flex items-center gap-2 border border-slate-200">
        <input type="hidden" name="table" value="<?= $hal_aktif ?>">
        
        <select name="bulan" class="p-2 border border-slate-200 rounded-lg text-sm bg-gray-50 outline-none focus:ring-2 focus:ring-blue-400 cursor-pointer text-slate-700">
            <?php foreach($nama_bulan as $k => $v): ?>
                <option value="<?= $k ?>" <?= ($k == $bulan_pilih) ? 'selected' : '' ?>><?= $v ?></option>
            <?php endforeach; ?>
        </select>
        <select name="tahun" class="p-2 border border-slate-200 rounded-lg text-sm bg-gray-50 outline-none focus:ring-2 focus:ring-blue-400 cursor-pointer text-slate-700">
            <?php for($i=2024; $i<=date('Y'); $i++): ?>
                <option value="<?= $i ?>" <?= ($i == $tahun_pilih) ? 'selected' : '' ?>><?= $i ?></option>
            <?php endfor; ?>
        </select>
        <button type="submit" class="bg-gradient-to-r from-slate-700 to-blue-800 hover:from-slate-800 hover:to-blue-900 text-white px-5 py-2 rounded-lg text-sm font-bold transition shadow-md">
            Filter
        </button>
    </form>
</div>

<!-- SUMMARY CARDS -->
<div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
    <!-- Card Total Transaksi -->
    <div class="bg-gradient-to-r from-slate-700 via-slate-800 to-blue-900 p-6 rounded-2xl shadow-xl text-white relative overflow-hidden">
        <div class="absolute -right-4 -top-4 opacity-20">
            <i class="fa fa-boxes-packing text-8xl"></i>
        </div>
        <p class="text-slate-300 text-xs font-bold uppercase mb-1 tracking-widest">Volume Pengambilan</p>
        <h2 class="text-3xl font-black"><?= number_format($grand_total_trx) ?> <span class="text-lg font-normal text-slate-400">Tiket</span></h2>
    </div>
    
    <!-- Card Status Selesai -->
    <div class="bg-white p-6 rounded-2xl shadow-lg border border-slate-100 flex items-center justify-between">
        <div>
            <p class="text-slate-400 text-xs font-bold uppercase mb-1 tracking-widest">Sudah Diserahkan</p>
            <h2 class="text-2xl font-black text-transparent bg-clip-text bg-gradient-to-r from-blue-700 to-slate-900">
                <?= number_format($grand_total_selesai) ?> Selesai
            </h2>
        </div>
        <div class="bg-blue-50 p-4 rounded-full text-blue-600">
            <i class="fa fa-check-double text-2xl"></i>
        </div>
    </div>
</div>

<!-- CHART & TABLE -->
<div class="grid grid-cols-1 lg:grid-cols-3 gap-8 mb-8">
    <!-- Table Section (7/12 equivalent) -->
    <div class="lg:col-span-2 bg-white p-6 rounded-2xl shadow-lg border border-slate-50">
        <h3 class="font-bold text-slate-700 mb-6 flex items-center gap-2">
            <i class="fa fa-list-ul text-blue-600"></i> Aktivitas Pengambilan
        </h3>
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left">
                <thead class="bg-slate-100 text-slate-600 uppercase text-[11px] tracking-wider font-extrabold">
                    <tr>
                        <th class="py-4 px-4 rounded-tl-xl">Tanggal</th>
                        <th class="py-4 px-4">Nama Pengambil</th>
                        <th class="py-4 px-4 text-center">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    <?php if(mysqli_num_rows($r_detail) > 0): while($r = mysqli_fetch_assoc($r_detail)): ?>
                    <tr class="hover:bg-blue-50/50 transition duration-150">
                        <td class="py-4 px-4 font-medium text-slate-500">
                            <?= date('d/m/Y', strtotime($r['tgl_ambil'])) ?>
                        </td>
                        <td class="py-4 px-4 font-bold text-slate-700">
                            <div class="flex items-center gap-3">
                                <span class="w-8 h-8 rounded-lg bg-slate-100 text-slate-500 flex items-center justify-center">
                                    <i class="fa fa-user text-xs"></i>
                                </span>
                                <?= $r['nama_pengambil'] ?>
                            </div>
                        </td>
                        <td class="py-4 px-4 text-center">
                            <?php if($r['status_pengambilan'] == 'Sudah Diambil'): ?>
                                <span class="bg-blue-100 text-blue-700 px-3 py-1 rounded-full text-[10px] font-black uppercase">
                                    Selesai
                                </span>
                            <?php else: ?>
                                <span class="bg-slate-100 text-slate-500 px-3 py-1 rounded-full text-[10px] font-black uppercase">
                                    Pending
                                </span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endwhile; else: ?>
                    <tr>
                        <td colspan="3" class="text-center py-10 text-slate-300 italic text-sm">Tidak ada data pengambilan.</td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Chart Section (5/12 equivalent) -->
    <div class="lg:col-span-1 bg-white p-6 rounded-2xl shadow-lg border border-slate-50">
        <h3 class="font-bold text-slate-700 mb-6 text-center flex items-center justify-center gap-2">
            <i class="fa fa-chart-line text-blue-500"></i> Tren Pengambilan
        </h3>
        <div class="h-64 flex justify-center">
            <?php if(!empty($chart_data)): ?>
                <canvas id="chartAmbil"></canvas>
            <?php else: ?>
                <div class="flex flex-col items-center justify-center text-slate-300 italic text-sm text-center">
                    <i class="fa fa-folder-open text-4xl mb-2"></i>
                    <span>Tren kosong periode ini.</span>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
    <?php if(!empty($chart_data)): ?>
    var ctxAmbil = document.getElementById('chartAmbil').getContext('2d');
    new Chart(ctxAmbil, {
        type: 'line',
        data: {
            labels: <?= json_encode($chart_label) ?>,
            datasets: [{
                label: 'Jumlah Pengambilan',
                data: <?= json_encode($chart_data) ?>,
                borderColor: '#1e40af',
                backgroundColor: 'rgba(30, 64, 175, 0.1)',
                borderWidth: 3,
                pointBackgroundColor: '#1e40af',
                pointRadius: 4,
                tension: 0.4,
                fill: true
            }]
        },
        options: { 
            responsive: true, 
            maintainAspectRatio: false, 
            plugins: { 
                legend: { display: false } 
            },
            scales: {
                y: { beginAtZero: true, ticks: { stepSize: 1 } },
                x: { grid: { display: false } }
            }
        }
    });
    <?php endif; ?>
</script>

<footer class="mt-8 text-center text-slate-400 text-[10px] font-bold uppercase tracking-[0.2em] pb-4">
    &copy; <?= date('Y') ?> Zyngga Laundry System | Professional Blue-Grey Theme 🏢
</footer>