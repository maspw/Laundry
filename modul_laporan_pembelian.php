<?php
// --- FILE: modul_laporan_pembelian.php ---
// Logic: Laporan Pengeluaran Bahan Baku (FIX ERROR KOLOM)

$bulan_pilih = isset($_GET['bulan']) ? $_GET['bulan'] : date('m');
$tahun_pilih = isset($_GET['tahun']) ? $_GET['tahun'] : date('Y');

$nama_bulan = [
    '01'=>'Januari', '02'=>'Februari', '03'=>'Maret', '04'=>'April', 
    '05'=>'Mei', '06'=>'Juni', '07'=>'Juli', '08'=>'Agustus', 
    '09'=>'September', '10'=>'Oktober', '11'=>'November', '12'=>'Desember'
];

// Query Utama: Rekap per barang
// FIX: Ganti 'p.harga' jadi 'p.harga_satuan' sesuai struktur database asli lo
$q_rekap = "SELECT p.nama_perlengkapan, 
                   SUM(pb.jml_pembelian) as total_qty,
                   SUM(pb.jml_pembelian * p.harga_satuan) as total_biaya 
            FROM pembelian_bahan_baku pb
            JOIN perlengkapan p ON pb.id_perlengkapan = p.id_perlengkapan
            WHERE MONTH(pb.tgl_pembelian) = '$bulan_pilih' AND YEAR(pb.tgl_pembelian) = '$tahun_pilih'
            GROUP BY pb.id_perlengkapan
            ORDER BY total_qty DESC";

$r_rekap = mysqli_query($koneksi, $q_rekap);

// Cek error query biar ketahuan kalau ada salah nama kolom lagi
if (!$r_rekap) {
    die("❌ Error Query: " . mysqli_error($koneksi));
}

// Data buat Grafik & Summary
$chart_label = [];
$chart_data  = [];
$grand_total = 0;
$top_item    = '-';

while($d = mysqli_fetch_assoc($r_rekap)) {
    $chart_label[] = $d['nama_perlengkapan'];
    $chart_data[]  = $d['total_qty'];
    $grand_total  += $d['total_biaya'];
    
    if($top_item == '-') $top_item = $d['nama_perlengkapan']; // Item pertama = top item
}
?>

<!-- HEADER & FILTER -->
<div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-6 gap-4">
    <div>
        <h2 class="text-2xl font-bold text-gray-800">📊 Laporan Belanja BB</h2>
        <p class="text-sm text-gray-500">Periode: <?= $nama_bulan[$bulan_pilih] ?> <?= $tahun_pilih ?></p>
    </div>
    
    <form action="" method="GET" class="bg-white p-2 rounded-lg shadow-sm flex items-center gap-2 border border-gray-200">
        <input type="hidden" name="table" value="laporan_pembelian">
        <select name="bulan" class="p-2 border rounded text-sm bg-gray-50 outline-none focus:ring-2 focus:ring-green-500">
            <?php foreach($nama_bulan as $k => $v): ?>
                <option value="<?= $k ?>" <?= ($k == $bulan_pilih) ? 'selected' : '' ?>><?= $v ?></option>
            <?php endforeach; ?>
        </select>
        <select name="tahun" class="p-2 border rounded text-sm bg-gray-50 outline-none focus:ring-2 focus:ring-green-500">
            <?php for($i=2024; $i<=date('Y'); $i++): ?>
                <option value="<?= $i ?>" <?= ($i == $tahun_pilih) ? 'selected' : '' ?>><?= $i ?></option>
            <?php endfor; ?>
        </select>
        <button type="submit" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded text-sm font-bold transition">Filter</button>
    </form>
</div>

<!-- SUMMARY CARDS -->
<div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
    <div class="bg-gradient-to-r from-green-500 to-teal-500 p-6 rounded-xl shadow-lg text-white">
        <p class="text-green-100 text-xs font-bold uppercase mb-1">Total Pengeluaran</p>
        <h2 class="text-3xl font-bold">Rp <?= number_format($grand_total, 0, ',', '.') ?></h2>
    </div>
    <div class="bg-white p-6 rounded-xl shadow border border-gray-100">
        <p class="text-gray-400 text-xs font-bold uppercase mb-1">Item Terbanyak Dibeli</p>
        <h2 class="text-xl font-bold text-gray-800"><?= $top_item ?></h2>
    </div>
</div>

<!-- CHART & TABLE -->
<div class="grid grid-cols-1 lg:grid-cols-3 gap-8 mb-8">
    <!-- Chart -->
    <div class="lg:col-span-1 bg-white p-6 rounded-xl shadow border border-gray-100">
        <h3 class="font-bold text-gray-700 mb-4 text-center">Komposisi Belanja (Qty)</h3>
        <div class="h-64 flex justify-center"><canvas id="beliChart"></canvas></div>
    </div>

    <!-- Table -->
    <div class="lg:col-span-2 bg-white p-6 rounded-xl shadow border border-gray-100">
        <h3 class="font-bold text-gray-700 mb-4">Rincian Pembelian</h3>
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left">
                <thead class="bg-gray-50 text-gray-500 border-b">
                    <tr><th class="py-3 px-4">Nama Barang</th><th class="py-3 px-4 text-right">Qty Beli</th><th class="py-3 px-4 text-right">Total Biaya</th></tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    <?php if(mysqli_num_rows($r_rekap) > 0): mysqli_data_seek($r_rekap, 0); while($r=mysqli_fetch_assoc($r_rekap)): ?>
                    <tr class="hover:bg-green-50 transition">
                        <td class="py-3 px-4 font-medium text-gray-700"><?= $r['nama_perlengkapan'] ?></td>
                        <td class="py-3 px-4 text-right"><?= number_format($r['total_qty']) ?></td>
                        <td class="py-3 px-4 text-right font-mono">Rp <?= number_format($r['total_biaya'], 0, ',', '.') ?></td>
                    </tr>
                    <?php endwhile; else: ?>
                    <tr><td colspan="3" class="text-center py-4 text-gray-400 italic">Tidak ada pembelian bulan ini.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
    var ctxBeli = document.getElementById('beliChart').getContext('2d');
    new Chart(ctxBeli, {
        type: 'doughnut',
        data: {
            labels: <?= json_encode($chart_label) ?>,
            datasets: [{
                data: <?= json_encode($chart_data) ?>,
                backgroundColor: ['#10b981', '#3b82f6', '#f59e0b', '#ef4444', '#8b5cf6'],
                borderWidth: 0
            }]
        },
        options: { responsive: true, maintainAspectRatio: false, plugins: { legend: {position:'bottom'} } }
    });
</script>