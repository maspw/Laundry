<?php
// --- FILE: modul_laporan_pembayaran.php ---
// Logic: Laporan Analisis Metode Pembayaran (With Filter Period)

// 1. TANGKAP INPUT FILTER (Default: Bulan Ini)
$bulan_pilih = isset($_GET['bulan']) ? $_GET['bulan'] : date('m');
$tahun_pilih = isset($_GET['tahun']) ? $_GET['tahun'] : date('Y');

// Array Nama Bulan buat Dropdown & Judul
$nama_bulan = [
    '01'=>'Januari', '02'=>'Februari', '03'=>'Maret', '04'=>'April', 
    '05'=>'Mei', '06'=>'Juni', '07'=>'Juli', '08'=>'Agustus', 
    '09'=>'September', '10'=>'Oktober', '11'=>'November', '12'=>'Desember'
];

// --- QUERY DATA (DENGAN FILTER) ---
// Mengelompokkan pembayaran berdasarkan metode + Filter Waktu
$query = "SELECT m.nama_metode, 
                 COUNT(p.id_metode) as total_pakai, 
                 SUM(p.jml_bayar) as total_nominal,
                 AVG(p.jml_bayar) as rata_rata
          FROM pembayaran p
          JOIN metode_pembayaran m ON p.id_metode = m.id_metode
          WHERE MONTH(p.tgl_bayar) = '$bulan_pilih' AND YEAR(p.tgl_bayar) = '$tahun_pilih'
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

if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $chart_label[] = $row['nama_metode'];
        $chart_data[]  = $row['total_pakai'];
        
        $grand_total_trx += $row['total_pakai'];
        $grand_total_rp  += $row['total_nominal'];
        
        if ($top_method == '-') $top_method = $row['nama_metode'];
        
        $table_data[] = $row;
    }
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
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>@import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap'); body{font-family:'Inter',sans-serif;}</style>
</head>
<body class="bg-gray-100 p-8 text-slate-800">

<div class="max-w-7xl mx-auto">
    <div class="flex flex-col md:flex-row justify-between items-center mb-8 gap-4">
        <div>
            <h1 class="text-3xl font-bold text-gray-800">💳 Analisis Pembayaran</h1>
            <p class="text-sm text-gray-500">
                Periode: <span class="font-bold text-blue-600"><?= $nama_bulan[$bulan_pilih] ?> <?= $tahun_pilih ?></span>
            </p>
        </div>

        <div class="flex items-center gap-3">
            <form action="" method="GET" class="flex items-center gap-2 bg-white p-2 rounded-lg shadow-sm border border-gray-200">
                <input type="hidden" name="table" value="laporan_pembayaran">
                
                <select name="bulan" class="p-2 border rounded text-sm bg-gray-50 outline-none focus:ring-2 focus:ring-blue-500 cursor-pointer">
                    <?php foreach($nama_bulan as $k => $v): ?>
                        <option value="<?= $k ?>" <?= ($k == $bulan_pilih) ? 'selected' : '' ?>><?= $v ?></option>
                    <?php endforeach; ?>
                </select>
                
                <select name="tahun" class="p-2 border rounded text-sm bg-gray-50 outline-none focus:ring-2 focus:ring-blue-500 cursor-pointer">
                    <?php for($i=2024; $i<=date('Y'); $i++): ?>
                        <option value="<?= $i ?>" <?= ($i == $tahun_pilih) ? 'selected' : '' ?>><?= $i ?></option>
                    <?php endfor; ?>
                </select>
                
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded text-sm font-bold transition">
                    <i class="fa-solid fa-filter"></i>
                </button>
            </form>

            </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <div class="bg-white p-6 rounded-xl shadow border-l-4 border-emerald-500">
            <div class="flex justify-between items-start">
                <div>
                    <p class="text-gray-400 text-xs font-bold uppercase mb-1">Total Pemasukan</p>
                    <h3 class="text-2xl font-bold text-gray-800">Rp <?= number_format($grand_total_rp, 0, ',', '.') ?></h3>
                </div>
                <div class="p-2 bg-emerald-50 rounded text-emerald-600"><i class="fa-solid fa-wallet"></i></div>
            </div>
        </div>

        <div class="bg-white p-6 rounded-xl shadow border-l-4 border-blue-500">
            <div class="flex justify-between items-start">
                <div>
                    <p class="text-gray-400 text-xs