<?php
// --- FILE: modul_laporan_pengeluaran.php ---
// Logic: Laporan Biaya Operasional (Converted & Fixed)

// 1. FILTER WAKTU
$bulan_pilih = isset($_GET['bulan']) ? $_GET['bulan'] : date('m');
$tahun_pilih = isset($_GET['tahun']) ? $_GET['tahun'] : date('Y');

$nama_bulan = [
    '01'=>'Januari', '02'=>'Februari', '03'=>'Maret', '04'=>'April', 
    '05'=>'Mei', '06'=>'Juni', '07'=>'Juli', '08'=>'Agustus', 
    '09'=>'September', '10'=>'Oktober', '11'=>'November', '12'=>'Desember'
];

// 2. QUERY UTAMA: Rekap per Kategori/Nama Pengeluaran
// Mengelompokkan berdasarkan nama pengeluaran (misal: "Listrik", "Air", "Gaji")
$q_rekap = "SELECT nama_pengeluaran, 
                   COUNT(*) as frekuensi,
                   SUM(jml_pengeluaran) as total_biaya 
            FROM pengeluaran_operasional
            WHERE MONTH(tgl_pengeluaran) = '$bulan_pilih' AND YEAR(tgl_pengeluaran) = '$tahun_pilih'
            GROUP BY nama_pengeluaran
            ORDER BY total_biaya DESC";

$r_rekap = mysqli_query($koneksi, $q_rekap);

// Cek Error Query
if (!$r_rekap) {
    die("❌ Error Query: " . mysqli_error($koneksi));
}

// 3. SIAPKAN DATA CHART & SUMMARY
$chart_label = [];
$chart_data  = [];
$grand_total = 0;
$top_pos     = '-'; // Pos pengeluaran terbesar

while($d = mysqli_fetch_assoc($r_rekap)) {
    $chart_label[] = $d['nama_pengeluaran'];
    $chart_data[]  = $d['total_biaya'];
    $grand_total  += $d['total_biaya'];
    
    if ($top_pos == '-') $top_pos = $d['nama_pengeluaran'];
}
?>

<!-- UI MODULE START -->

<!-- HEADER & FILTER -->
<div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-6 gap-4">
    <div>
        <h2 class="text-2xl font-bold text-gray-800">📊 Laporan Biaya Operasional</h2>
        <p class="text-sm text-gray-500">Periode: <?= $nama_bulan[$bulan_pilih] ?> <?= $tahun_pilih ?></p>
    </div>
    
    <form action="" method="GET" class="bg-white p-2 rounded-lg shadow-sm flex items-center gap-2 border border-gray-200">
        <!-- Penting: Input Hidden buat mertahanin halaman -->
        <input type="hidden" name="table" value="laporan_pengeluaran">
        
        <select name="bulan" class="p-2 border rounded text-sm bg-gray-50 outline-none focus:ring-2 focus:ring-red-500">
            <?php foreach($nama_bulan as $k => $v): ?>
                <option value="<?= $k ?>" <?= ($k == $bulan_pilih) ? 'selected' : '' ?>><?= $v ?></option>
            <?php endforeach; ?>
        </select>
        
        <select name="tahun" class="p-2 border rounded text-sm bg-gray-50 outline-none focus:ring-2 focus:ring-red-500">
            <?php for($i=2024; $i<=date('Y'); $i++): ?>
                <option value="<?= $i ?>" <?= ($i == $tahun_pilih) ? 'selected' : '' ?>><?= $i ?></option>
            <?php endfor; ?>
        </select>
        
        <button type="submit" class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded text-sm font-bold transition">Filter</button>
    </form>
</div>

<!-- SUMMARY CARDS -->
<div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
    <!-- Card 1: Total Biaya -->
    <div class="bg-gradient-to-r from-red-500 to-orange-500 p-6 rounded-xl shadow-lg text-white transform hover:scale-[1.02] transition duration-300">
        <div class="flex justify-between items-start">
            <div>
                <p class="text-red-100 text-xs font-bold uppercase tracking-wider mb-1">Total Biaya Operasional</p>
                <h2 class="text-3xl font-bold">Rp <?= number_format($grand_total, 0, ',', '.') ?></h2>
            </div>
            <div class="p-3 bg-white/20 rounded-full"><i class="fa-solid fa-sack-dollar text-2xl"></i></div>
        </div>
    </div>
    
    <!-- Card 2: Top Pos -->
    <div class="bg-white p-6 rounded-xl shadow border border-gray-100 flex items-center justify-between">
        <div>
            <p class="text-gray-400 text-xs font-bold uppercase tracking-wider mb-1">Pos Pengeluaran Terbesar</p>
            <h2 class="text-xl font-bold text-gray-800"><?= $top_pos ?></h2>
            <p class="text-xs text-gray-400 mt-1">Menguras anggaran bulan ini</p>
        </div>
        <div class="p-3 bg-red-50 rounded-full text-red-500"><i class="fa-solid fa-money-bill-trend-up text-xl"></i></div>
    </div>
</div>

<!-- CHART & TABLE -->
<div class="grid grid-cols-1 lg:grid-cols-3 gap-8 mb-8">
    
    <!-- Chart (Pie Chart) -->
    <div class="lg:col-span-1 bg-white p-6 rounded-xl shadow border border-gray-100">
        <h3 class="font-bold text-gray-700 mb-4 text-center border-b pb-2">Komposisi Biaya</h3>
        <div class="h-64 flex justify-center items-center">
            <?php if ($grand_total > 0): ?>
                <canvas id="opsChart"></canvas>
            <?php else: ?>
                <p class="text-gray-400 text-sm italic">Belum ada data pengeluaran.</p>
            <?php endif; ?>
        </div>
    </div>

    <!-- Table Rincian -->
    <div class="lg:col-span-2 bg-white p-6 rounded-xl shadow border border-gray-100">
        <h3 class="font-bold text-gray-700 mb-4 border-b pb-2">Rincian Pos Biaya</h3>
        <div class="overflow-x-auto custom-scrollbar">
            <table class="w-full text-sm text-left">
                <thead class="bg-gray-50 text-gray-500">
                    <tr>
                        <th class="py-3 px-4 rounded-l">Keterangan</th>
                        <th class="py-3 px-4 text-center">Frekuensi</th>
                        <th class="py-3 px-4 text-right rounded-r">Total Biaya</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    <?php 
                    if(mysqli_num_rows($r_rekap) > 0): 
                        mysqli_data_seek($r_rekap, 0); // Reset pointer loop
                        while($r=mysqli_fetch_assoc($r_rekap)): 
                    ?>
                    <tr class="hover:bg-red-50 transition">
                        <td class="py-3 px-4 font-medium text-gray-700"><?= $r['nama_pengeluaran'] ?></td>
                        <td class="py-3 px-4 text-center text-gray-500"><?= $r['frekuensi'] ?>x</td>
                        <td class="py-3 px-4 text-right font-mono text-gray-800 font-semibold">Rp <?= number_format($r['total_biaya'], 0, ',', '.') ?></td>
                    </tr>
                    <?php 
                        endwhile; 
                    else: 
                    ?>
                    <tr>
                        <td colspan="3" class="text-center py-8 text-gray-400 italic">
                            <i class="fa-solid fa-box-open text-2xl mb-2 block"></i>
                            Tidak ada pengeluaran bulan ini.
                        </td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- SCRIPT CHART.JS -->
<script>
    // Cek apakah elemen canvas ada (biar ga error JS)
    var ctxOpsElement = document.getElementById('opsChart');
    
    if (ctxOpsElement) {
        var ctxOps = ctxOpsElement.getContext('2d');
        new Chart(ctxOps, {
            type: 'pie', // Pie chart cocok buat liat porsi pengeluaran
            data: {
                labels: <?= json_encode($chart_label) ?>,
                datasets: [{
                    data: <?= json_encode($chart_data) ?>,
                    backgroundColor: [
                        '#ef4444', // Red 500
                        '#f97316', // Orange 500
                        '#f59e0b', // Amber 500
                        '#84cc16', // Lime 500
                        '#10b981', // Emerald 500
                        '#3b82f6', // Blue 500
                        '#6366f1', // Indigo 500
                        '#8b5cf6'  // Violet 500
                    ],
                    borderWidth: 2,
                    borderColor: '#ffffff'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { position:'bottom', labels: { boxWidth: 12, padding: 15 } },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                let label = context.label || '';
                                if (label) { label += ': '; }
                                let value = context.parsed;
                                // Hitung persentase
                                let total = context.dataset.data.reduce((a, b) => a + b, 0);
                                let percentage = Math.round((value / total) * 100) + '%';
                                return label + 'Rp ' + new Intl.NumberFormat('id-ID').format(value) + ' (' + percentage + ')';
                            }
                        }
                    }
                }
            }
        });
    }
</script>