<?php
// --- FILE: modul_laporan_pengambilan.php ---
// Logic: Analitik Data Pengambilan

// Stats Card
$total_ambil = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT COUNT(*) as total FROM pengambilan_laundry"))['total'];
$total_sudah = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT COUNT(*) as total FROM pengambilan_laundry WHERE status_pengambilan='Sudah Diambil'"))['total'];

// Data Grafik Tren (7 Hari Terakhir)
$q_grafik = mysqli_query($koneksi, "SELECT tgl_ambil, COUNT(*) as jumlah FROM pengambilan_laundry GROUP BY tgl_ambil ORDER BY tgl_ambil DESC LIMIT 7");
$labels = [];
$data_val = [];
while($r = mysqli_fetch_assoc($q_grafik)) {
    // Balik urutan array biar tanggal lama di kiri (karena query DESC)
    array_unshift($labels, date('d M', strtotime($r['tgl_ambil'])));
    array_unshift($data_val, $r['jumlah']);
}
?>

<div class="flex flex-col md:flex-row justify-between items-center mb-8">
    <div>
        <h1 class="text-3xl font-bold text-indigo-600">📊 Laporan Pengambilan</h1>
        <p class="text-gray-500 text-sm">Analisis barang keluar dari outlet.</p>
    </div>
</div>

<div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
    <div class="bg-white p-6 rounded-xl shadow border-l-4 border-indigo-500 flex justify-between items-center">
        <div>
            <p class="text-sm text-gray-500 font-bold uppercase">Total Transaksi</p>
            <h3 class="text-3xl font-bold"><?= $total_ambil ?> <span class="text-sm font-normal">Tiket</span></h3>
        </div>
        <i class="fa-solid fa-receipt text-4xl text-indigo-200"></i>
    </div>
    <div class="bg-white p-6 rounded-xl shadow border-l-4 border-green-500 flex justify-between items-center">
        <div>
            <p class="text-sm text-gray-500 font-bold uppercase">Sudah Diambil</p>
            <h3 class="text-3xl font-bold"><?= $total_sudah ?> <span class="text-sm font-normal">Selesai</span></h3>
        </div>
        <i class="fa-solid fa-check-double text-4xl text-green-200"></i>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
    <!-- Chart -->
    <div class="lg:col-span-2 bg-white p-6 rounded-xl shadow border border-gray-100">
        <h2 class="text-lg font-bold mb-4 text-gray-700">Tren Pengambilan (7 Hari Terakhir)</h2>
        <div class="h-64"><canvas id="chartAmbil"></canvas></div>
    </div>

    <!-- Recent List -->
    <div class="bg-white p-6 rounded-xl shadow border border-gray-100">
        <h2 class="text-lg font-bold mb-4 text-gray-700">Aktivitas Terbaru</h2>
        <div class="overflow-y-auto max-h-64">
            <table class="w-full text-sm">
                <?php
                $recent = mysqli_query($koneksi, "SELECT * FROM pengambilan_laundry ORDER BY id_pengambilan DESC LIMIT 5");
                while($row = mysqli_fetch_assoc($recent)):
                ?>
                <tr class="border-b">
                    <td class="py-2 text-gray-500 text-xs"><?= date('d/m', strtotime($row['tgl_ambil'])) ?></td>
                    <td class="py-2 font-medium"><?= $row['nama_pengambil'] ?></td>
                    <td class="py-2 text-right"><span class="bg-gray-100 text-xs px-2 rounded"><?= $row['status_pengambilan']=='Sudah Diambil'?'✅':'⏳' ?></span></td>
                </tr>
                <?php endwhile; ?>
            </table>
        </div>
    </div>
</div>

<script>
    const ctx = document.getElementById('chartAmbil').getContext('2d');
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: <?= json_encode($labels) ?>,
            datasets: [{
                label: 'Jumlah Pengambilan',
                data: <?= json_encode($data_val) ?>,
                borderColor: '#4f46e5',
                backgroundColor: 'rgba(79, 70, 229, 0.1)',
                tension: 0.4,
                fill: true
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: { y: { beginAtZero: true, ticks: { stepSize: 1 } } }
        }
    });
</script>