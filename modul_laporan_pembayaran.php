<?php
// --- FILE: modul_laporan_pembayaran.php ---
// Logic: Laporan Analisis Metode Pembayaran (Standalone Version)

// --- QUERY DATA ---
// Mengelompokkan pembayaran berdasarkan metode
$query = "SELECT m.nama_metode, 
                 COUNT(p.id_metode) as total_pakai, 
                 SUM(p.jml_bayar) as total_nominal,
                 AVG(p.jml_bayar) as rata_rata
          FROM pembayaran p
          JOIN metode_pembayaran m ON p.id_metode = m.id_metode
          GROUP BY p.id_metode
          ORDER BY total_pakai DESC";

$result = mysqli_query($koneksi, $query);

// Siapkan Array untuk Chart & Summary
$chart_label = [];
$chart_data  = [];
$grand_total_trx = 0;
$grand_total_rp  = 0;
$top_method      = '-';

$table_data = []; // Simpan data buat tabel biar ga perlu loop query lagi

while ($row = mysqli_fetch_assoc($result)) {
    $chart_label[] = $row['nama_metode'];
    $chart_data[]  = $row['total_pakai'];
    
    $grand_total_trx += $row['total_pakai'];
    $grand_total_rp  += $row['total_nominal'];
    
    if ($top_method == '-') $top_method = $row['nama_metode'];
    
    $table_data[] = $row;
}

// Hitung Rata-rata Global
$avg_global = ($grand_total_trx > 0) ? ($grand_total_rp / $grand_total_trx) : 0;
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Metode Pembayaran</title>
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>@import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap'); body{font-family:'Inter',sans-serif;}</style>
</head>
<body class="bg-gray-100 p-8 text-slate-800">

<div class="max-w-7xl mx-auto">
    <!-- HEADER -->
    <div class="flex flex-col md:flex-row justify-between items-center mb-8">
        <div>
            <h1 class="text-3xl font-bold text-gray-800">💳 Analisis Pembayaran</h1>
            <p class="text-sm text-gray-500">Insight preferensi pembayaran pelanggan.</p>
        </div>
        <div>
            <a href="dashboard.php" class="text-blue-600 font-medium hover:underline flex items-center gap-2">
                <i class="fa-solid fa-arrow-left"></i> Kembali ke Dashboard
            </a>
        </div>
    </div>

    <!-- SUMMARY CARDS -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <!-- Card 1: Total Uang -->
        <div class="bg-white p-6 rounded-xl shadow border-l-4 border-emerald-500">
            <div class="flex justify-between items-start">
                <div>
                    <p class="text-gray-400 text-xs font-bold uppercase mb-1">Total Pemasukan</p>
                    <h3 class="text-2xl font-bold text-gray-800">Rp <?= number_format($grand_total_rp, 0, ',', '.') ?></h3>
                </div>
                <div class="p-2 bg-emerald-50 rounded text-emerald-600"><i class="fa-solid fa-wallet"></i></div>
            </div>
        </div>

        <!-- Card 2: Total Transaksi -->
        <div class="bg-white p-6 rounded-xl shadow border-l-4 border-blue-500">
            <div class="flex justify-between items-start">
                <div>
                    <p class="text-gray-400 text-xs font-bold uppercase mb-1">Volume Transaksi</p>
                    <h3 class="text-2xl font-bold text-gray-800"><?= number_format($grand_total_trx) ?> <span class="text-sm font-normal text-gray-500">Trx</span></h3>
                </div>
                <div class="p-2 bg-blue-50 rounded text-blue-600"><i class="fa-solid fa-receipt"></i></div>
            </div>
        </div>

        <!-- Card 3: Metode Favorit -->
        <div class="bg-white p-6 rounded-xl shadow border-l-4 border-purple-500">
            <div class="flex justify-between items-start">
                <div>
                    <p class="text-gray-400 text-xs font-bold uppercase mb-1">Metode Favorit</p>
                    <h3 class="text-2xl font-bold text-gray-800"><?= $top_method ?></h3>
                </div>
                <div class="p-2 bg-purple-50 rounded text-purple-600"><i class="fa-solid fa-star"></i></div>
            </div>
        </div>

        <!-- Card 4: Rata-rata -->
        <div class="bg-white p-6 rounded-xl shadow border-l-4 border-orange-500">
            <div class="flex justify-between items-start">
                <div>
                    <p class="text-gray-400 text-xs font-bold uppercase mb-1">Rata-rata Nilai</p>
                    <h3 class="text-2xl font-bold text-gray-800">Rp <?= number_format($avg_global, 0, ',', '.') ?></h3>
                </div>
                <div class="p-2 bg-orange-50 rounded text-orange-600"><i class="fa-solid fa-calculator"></i></div>
            </div>
        </div>
    </div>

    <!-- CONTENT: CHART & TABLE -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        
        <!-- CHART SECTION -->
        <div class="lg:col-span-1 bg-white p-6 rounded-xl shadow border border-gray-100">
            <h3 class="font-bold text-gray-700 mb-6 text-center border-b pb-2">Proporsi Penggunaan</h3>
            <div class="relative h-64 flex justify-center items-center">
                <?php if ($grand_total_trx > 0): ?>
                    <canvas id="paymentChart"></canvas>
                <?php else: ?>
                    <p class="text-gray-400 italic text-sm">Belum ada data transaksi.</p>
                <?php endif; ?>
            </div>
            <div class="mt-6 text-center">
                <p class="text-xs text-gray-500">Data berdasarkan frekuensi penggunaan metode pembayaran.</p>
            </div>
        </div>

        <!-- TABLE SECTION -->
        <div class="lg:col-span-2 bg-white p-6 rounded-xl shadow border border-gray-100">
            <h3 class="font-bold text-gray-700 mb-4 border-b pb-2">Rincian Performa Metode</h3>
            <div class="overflow-x-auto">
                <table class="w-full text-sm text-left">
                    <thead class="bg-gray-50 text-gray-500">
                        <tr>
                            <th class="py-3 px-4 rounded-l">Metode</th>
                            <th class="py-3 px-4 text-center">Frekuensi</th>
                            <th class="py-3 px-4 text-right">Total Masuk</th>
                            <th class="py-3 px-4 text-right rounded-r">Rata-rata/Trx</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <?php foreach ($table_data as $row): ?>
                        <tr class="hover:bg-gray-50 transition">
                            <td class="py-3 px-4 font-medium text-gray-700 flex items-center gap-2">
                                <?php 
                                    // Ikon simpel berdasarkan nama
                                    $icon = 'fa-credit-card';
                                    if(stripos($row['nama_metode'], 'Tunai') !== false) $icon = 'fa-money-bill';
                                    if(stripos($row['nama_metode'], 'QRIS') !== false) $icon = 'fa-qrcode';
                                    if(stripos($row['nama_metode'], 'Transfer') !== false) $icon = 'fa-building-columns';
                                ?>
                                <i class="fa-solid <?= $icon ?> text-gray-400 w-5"></i>
                                <?= $row['nama_metode'] ?>
                            </td>
                            <td class="py-3 px-4 text-center">
                                <span class="bg-blue-100 text-blue-600 px-2 py-1 rounded text-xs font-bold"><?= $row['total_pakai'] ?></span>
                            </td>
                            <td class="py-3 px-4 text-right font-mono font-semibold text-gray-700">
                                Rp <?= number_format($row['total_nominal'], 0, ',', '.') ?>
                            </td>
                            <td class="py-3 px-4 text-right font-mono text-gray-500 text-xs">
                                Rp <?= number_format($row['rata_rata'], 0, ',', '.') ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        
                        <?php if (empty($table_data)): ?>
                            <tr><td colspan="4" class="text-center py-8 text-gray-400 italic">Belum ada data pembayaran.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- SCRIPT CHART.JS -->
<script>
    var ctxPayment = document.getElementById('paymentChart');
    if (ctxPayment) {
        new Chart(ctxPayment, {
            type: 'doughnut',
            data: {
                labels: <?= json_encode($chart_label) ?>,
                datasets: [{
                    data: <?= json_encode($chart_data) ?>,
                    backgroundColor: [
                        '#3b82f6', // Blue
                        '#10b981', // Emerald
                        '#f59e0b', // Amber
                        '#8b5cf6', // Violet
                        '#ec4899', // Pink
                        '#64748b'  // Slate
                    ],
                    borderWidth: 0,
                    hoverOffset: 4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '70%',
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: { usePointStyle: true, boxWidth: 8 }
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                let label = context.label || '';
                                let value = context.parsed;
                                let total = context.dataset.data.reduce((a, b) => a + b, 0);
                                let percentage = Math.round((value / total) * 100) + '%';
                                return label + ': ' + value + ' (' + percentage + ')';
                            }
                        }
                    }
                }
            }
        });
    }
</script>

</body>
</html>