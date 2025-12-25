<?php
// --- FILE: modul_jurnal.php ---
// Logic: Menangani 4 Jenis Jurnal & Jurnal Umum (Gabungan)

// 1. TENTUKAN JENIS JURNAL
$jenis_jurnal = $current_table ?? $_GET['jenis'] ?? 'jurnal_umum';

// Mapping Judul & Warna Header
$config_jurnal = [
    'jurnal_pembayaran'   => ['title' => 'Jurnal Pemasukan (Revenue)', 'color' => 'blue', 'icon' => 'fa-money-bill-trend-up'],
    'jurnal_pembelian'    => ['title' => 'Jurnal Pembelian BB', 'color' => 'teal', 'icon' => 'fa-cart-flatbed'],
    'jurnal_pengeluaran'  => ['title' => 'Jurnal Beban Operasional', 'color' => 'red', 'icon' => 'fa-file-invoice-dollar'],
    'jurnal_penggunaan'   => ['title' => 'Jurnal Pemakaian Stok', 'color' => 'orange', 'icon' => 'fa-box-open'],
    'jurnal_umum'         => ['title' => 'Jurnal Umum (All Transaction)', 'color' => 'indigo', 'icon' => 'fa-book-journal-whills']
];

$conf = $config_jurnal[$jenis_jurnal] ?? $config_jurnal['jurnal_umum'];

// 2. FILTER WAKTU (Optional)
// Default 'semua' agar user langsung lihat data jika ada
$bulan = $_GET['bulan'] ?? 'semua';
$tahun = $_GET['tahun'] ?? 'semua';

// Helper nama bulan
$nama_bulan = ['01'=>'Januari', '02'=>'Februari', '03'=>'Maret', '04'=>'April', '05'=>'Mei', '06'=>'Juni', '07'=>'Juli', '08'=>'Agustus', '09'=>'September', '10'=>'Oktober', '11'=>'November', '12'=>'Desember'];

// Function Builder Clause WHERE
function buildWhere($colDate, $m, $y) {
    global $koneksi; // Akses global koneksi untuk escape
    $clauses = [];
    if ($m !== 'semua') {
        $m = mysqli_real_escape_string($koneksi, $m);
        $clauses[] = "MONTH($colDate)='$m'";
    }
    if ($y !== 'semua') {
        $y = mysqli_real_escape_string($koneksi, $y);
        $clauses[] = "YEAR($colDate)='$y'";
    }
    
    if (count($clauses) > 0) {
        return "WHERE " . implode(' AND ', $clauses);
    }
    return ""; // Return string kosong kalau filter 'semua'
}

// 3. BUILD QUERY BERDASARKAN JENIS
$query = "";

// A. Jurnal Pembayaran
$where_bayar = buildWhere('tgl_bayar', $bulan, $tahun);
$q_bayar = "SELECT tgl_bayar as tgl, id_pembayaran as no_bukti, CONCAT('Pendapatan Jasa - Order ', id_order) as ket, '111-Kas' as akun_debet, '411-Pendapatan' as akun_kredit, jml_bayar as nominal, 'Masuk' as tipe FROM pembayaran $where_bayar";

// B. Jurnal Pembelian
$where_beli = buildWhere('pb.tgl_pembelian', $bulan, $tahun);
$q_beli = "SELECT pb.tgl_pembelian as tgl, pb.id_pembelian as no_bukti, CONCAT('Beli ', pb.nama_pembelian) as ket, '113-Perlengkapan' as akun_debet, '111-Kas' as akun_kredit, (pb.jml_pembelian * p.harga_satuan) as nominal, 'Keluar' as tipe 
           FROM pembelian_bahan_baku pb 
           JOIN perlengkapan p ON pb.id_perlengkapan = p.id_perlengkapan
           $where_beli";

// C. Jurnal Pengeluaran
$where_luar = buildWhere('tgl_pengeluaran', $bulan, $tahun);
$q_luar = "SELECT tgl_pengeluaran as tgl, id_pengeluaran as no_bukti, nama_pengeluaran as ket, '511-Beban Ops' as akun_debet, '111-Kas' as akun_kredit, jml_pengeluaran as nominal, 'Keluar' as tipe 
           FROM pengeluaran_operasional 
           $where_luar";

// D. Jurnal Penggunaan
$where_guna = buildWhere('pg.tgl_penggunaan', $bulan, $tahun);
$q_guna = "SELECT tgl_penggunaan as tgl, id_penggunaan as no_bukti, CONCAT('Pakai ', k.nama_perlengkapan) as ket, '512-Beban Perlengkapan' as akun_debet, '113-Perlengkapan' as akun_kredit, (pg.jml_penggunaan * k.harga_satuan) as nominal, 'Memorial' as tipe
           FROM penggunaan_bahan_baku pg
           JOIN perlengkapan k ON pg.id_perlengkapan = k.id_perlengkapan
           $where_guna";

// SWITCH LOGIC
switch ($jenis_jurnal) {
    case 'jurnal_pembayaran': $query = $q_bayar . " ORDER BY tgl DESC"; break;
    case 'jurnal_pembelian':  $query = $q_beli . " ORDER BY tgl DESC"; break;
    case 'jurnal_pengeluaran':$query = $q_luar . " ORDER BY tgl DESC"; break;
    case 'jurnal_penggunaan': $query = $q_guna . " ORDER BY tgl DESC"; break;
    default: 
        // JURNAL UMUM (GABUNGAN)
        $cek_tabel = mysqli_query($koneksi, "SHOW TABLES LIKE 'penggunaan_bahan_baku'");
        if(mysqli_num_rows($cek_tabel) > 0) {
            $query = "$q_bayar UNION ALL $q_beli UNION ALL $q_luar UNION ALL $q_guna ORDER BY tgl DESC"; 
        } else {
             $query = "$q_bayar UNION ALL $q_beli UNION ALL $q_luar ORDER BY tgl DESC"; 
        }
        break;
}

$result = mysqli_query($koneksi, $query);
if (!$result) { die("❌ Query Error: " . mysqli_error($koneksi)); }

$total_debet = 0; $total_kredit = 0;
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $conf['title'] ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>@import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap'); body{font-family:'Inter',sans-serif;}</style>
</head>
<body class="bg-gray-50 p-6 text-slate-800">

    <!-- NAVIGASI TAB JURNAL -->
    <div class="mb-6 flex flex-wrap gap-2">
        <a href="?table=jurnal_umum" class="<?= $jenis_jurnal=='jurnal_umum' ? 'bg-indigo-600 text-white shadow-lg' : 'bg-white text-gray-600 hover:bg-gray-100' ?> px-4 py-2 rounded-lg text-sm font-semibold transition"><i class="fa-solid fa-layer-group mr-1"></i> Gabungan</a>
        <a href="?table=jurnal_pembayaran" class="<?= $jenis_jurnal=='jurnal_pembayaran' ? 'bg-blue-600 text-white shadow-lg' : 'bg-white text-gray-600 hover:bg-gray-100' ?> px-4 py-2 rounded-lg text-sm font-semibold transition"><i class="fa-solid fa-money-bill mr-1"></i> Pemasukan</a>
        <a href="?table=jurnal_pembelian" class="<?= $jenis_jurnal=='jurnal_pembelian' ? 'bg-teal-600 text-white shadow-lg' : 'bg-white text-gray-600 hover:bg-gray-100' ?> px-4 py-2 rounded-lg text-sm font-semibold transition"><i class="fa-solid fa-cart-shopping mr-1"></i> Pembelian</a>
        <a href="?table=jurnal_pengeluaran" class="<?= $jenis_jurnal=='jurnal_pengeluaran' ? 'bg-red-600 text-white shadow-lg' : 'bg-white text-gray-600 hover:bg-gray-100' ?> px-4 py-2 rounded-lg text-sm font-semibold transition"><i class="fa-solid fa-fire mr-1"></i> Beban Ops</a>
        <a href="?table=jurnal_penggunaan" class="<?= $jenis_jurnal=='jurnal_penggunaan' ? 'bg-orange-600 text-white shadow-lg' : 'bg-white text-gray-600 hover:bg-gray-100' ?> px-4 py-2 rounded-lg text-sm font-semibold transition"><i class="fa-solid fa-box mr-1"></i> Stok</a>
    </div>

    <!-- HEADER & FILTER -->
    <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-200 mb-6">
        <div class="flex flex-col md:flex-row justify-between items-center gap-4">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 rounded-full bg-<?= $conf['color'] ?>-100 flex items-center justify-center text-<?= $conf['color'] ?>-600 text-xl">
                    <i class="fa-solid <?= $conf['icon'] ?>"></i>
                </div>
                <div>
                    <h1 class="text-2xl font-bold text-gray-800"><?= $conf['title'] ?></h1>
                    <p class="text-sm text-gray-500">
                        Periode: 
                        <?php 
                        if($bulan=='semua' && $tahun=='semua') echo "Semua Data";
                        elseif($bulan=='semua') echo "Semua Bulan " . $tahun;
                        elseif($tahun=='semua') echo $nama_bulan[$bulan] . " (Semua Tahun)";
                        else echo $nama_bulan[$bulan] . " " . $tahun;
                        ?>
                    </p>
                </div>
            </div>
            
            <form action="" method="GET" class="flex gap-2">
                <input type="hidden" name="table" value="<?= $jenis_jurnal ?>">
                <select name="bulan" class="border rounded-lg px-3 py-2 text-sm bg-gray-50 focus:ring-2 focus:ring-<?= $conf['color'] ?>-500 outline-none">
                    <option value="semua" <?= $bulan=='semua'?'selected':'' ?>>Semua Bulan</option>
                    <?php foreach($nama_bulan as $k=>$v): ?><option value="<?= $k ?>" <?= $k==$bulan?'selected':'' ?>><?= $v ?></option><?php endforeach; ?>
                </select>
                <select name="tahun" class="border rounded-lg px-3 py-2 text-sm bg-gray-50 focus:ring-2 focus:ring-<?= $conf['color'] ?>-500 outline-none">
                    <option value="semua" <?= $tahun=='semua'?'selected':'' ?>>Semua Tahun</option>
                    <?php for($i=2024;$i<=date('Y');$i++): ?><option value="<?= $i ?>" <?= $i==$tahun?'selected':'' ?>><?= $i ?></option><?php endfor; ?>
                </select>
                <button type="submit" class="bg-<?= $conf['color'] ?>-600 hover:bg-<?= $conf['color'] ?>-700 text-white px-4 py-2 rounded-lg text-sm font-bold transition">
                    Filter
                </button>
            </form>
        </div>
    </div>

    <!-- TABEL JURNAL -->
    <div class="bg-white rounded-xl shadow-lg border border-gray-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left">
                <thead class="bg-gray-100 text-gray-600 uppercase text-xs border-b border-gray-200">
                    <tr>
                        <th class="px-6 py-4">Tanggal</th>
                        <th class="px-6 py-4">Keterangan / Akun</th>
                        <th class="px-6 py-4">Ref</th>
                        <th class="px-6 py-4 text-right">Debet</th>
                        <th class="px-6 py-4 text-right">Kredit</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    <?php 
                    if($result && mysqli_num_rows($result) > 0):
                        while($row = mysqli_fetch_assoc($result)): 
                            $total_debet += $row['nominal'];
                            $total_kredit += $row['nominal'];
                    ?>
                    <!-- BARIS DEBET -->
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-2 w-32 align-top text-gray-500 font-mono"><?= date('d/m/Y', strtotime($row['tgl'])) ?></td>
                        <td class="px-6 py-2">
                            <div class="font-bold text-gray-800"><?= $row['akun_debet'] ?></div>
                            <div class="text-xs text-gray-500 italic"><?= $row['ket'] ?> (<?= $row['no_bukti'] ?>)</div>
                        </td>
                        <td class="px-6 py-2 text-center text-xs bg-gray-50"><?= $row['no_bukti'] ?></td>
                        <td class="px-6 py-2 text-right font-mono font-semibold text-gray-800">Rp <?= number_format($row['nominal'], 0, ',', '.') ?></td>
                        <td class="px-6 py-2 text-right font-mono text-gray-400">-</td>
                    </tr>
                    <!-- BARIS KREDIT -->
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-2 border-b"></td> <!-- Kosongin Tanggal -->
                        <td class="px-6 py-2 border-b pl-12"> <!-- Indentasi Kredit -->
                            <div class="text-gray-600"><?= $row['akun_kredit'] ?></div>
                        </td>
                        <td class="px-6 py-2 border-b text-center text-xs bg-gray-50"></td>
                        <td class="px-6 py-2 border-b text-right font-mono text-gray-400">-</td>
                        <td class="px-6 py-2 border-b text-right font-mono font-semibold text-gray-800">Rp <?= number_format($row['nominal'], 0, ',', '.') ?></td>
                    </tr>
                    <?php endwhile; else: ?>
                    <tr><td colspan="5" class="p-8 text-center text-gray-400 italic">Tidak ada transaksi pada periode ini.</td></tr>
                    <?php endif; ?>
                </tbody>
                <!-- FOOTER TOTAL -->
                <tfoot class="bg-<?= $conf['color'] ?>-50 font-bold text-gray-700">
                    <tr>
                        <td colspan="3" class="px-6 py-4 text-right uppercase text-xs tracking-wider">Total Balance</td>
                        <td class="px-6 py-4 text-right text-<?= $conf['color'] ?>-700">Rp <?= number_format($total_debet, 0, ',', '.') ?></td>
                        <td class="px-6 py-4 text-right text-<?= $conf['color'] ?>-700">Rp <?= number_format($total_kredit, 0, ',', '.') ?></td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>

</body>
</html>