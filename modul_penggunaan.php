<?php
// --- FILE: modul_penggunaan.php ---
// Logic: Transaksi Penggunaan Bahan Baku (Stok Out)

$halaman = isset($_GET['hal']) ? $_GET['hal'] : 'data';

// --- 1. LOGIC HAPUS ---
if (isset($_GET['aksi']) && $_GET['aksi'] == 'hapus') {
    $id = $_GET['id'];
    
    // Balikin stok sebelum dihapus
    // Note: Pastikan tabel 'perlengkapan' punya kolom 'jumlah' (stok) ya Pip, 
    // soalnya di SQL dump lu cuma ada harga, belum ada stok.
    $q_old = mysqli_query($koneksi, "SELECT id_perlengkapan, jml_penggunaan FROM penggunaan_bahan_baku WHERE id_penggunaan='$id'");
    $d_old = mysqli_fetch_assoc($q_old);
    
    if($d_old) {
        // Asumsi tabel perlengkapan punya kolom 'jumlah'
        mysqli_query($koneksi, "UPDATE perlengkapan SET jumlah = jumlah + {$d_old['jml_penggunaan']} WHERE id_perlengkapan='{$d_old['id_perlengkapan']}'");
    }

    $q = "DELETE FROM penggunaan_bahan_baku WHERE id_penggunaan='$id'";
    if (mysqli_query($koneksi, $q)) {
        echo "<script>alert('✅ Data penggunaan dihapus! Stok dikembalikan.'); window.location='?table=penggunaan_bahan_baku';</script>";
    } else {
        echo "<script>alert('❌ Gagal hapus: " . mysqli_error($koneksi) . "'); window.location='?table=penggunaan_bahan_baku';</script>";
    }
}

// --- 2. LOGIC SIMPAN (BARU) ---
if (isset($_POST['simpan_penggunaan'])) {
    $id    = $_POST['id_penggunaan'];
    $tgl   = $_POST['tgl_penggunaan'];
    $brg   = $_POST['id_perlengkapan'];
    $jml   = $_POST['jumlah']; // Input dari form
    $ket   = $_POST['keterangan']; // Input dari form, masuk ke 'nama_penggunaan'

    // Cek Stok Dulu Cukup Gak?
    // Pastikan tabel perlengkapan ada kolom 'jumlah'
    $cek_stok = mysqli_query($koneksi, "SELECT jumlah FROM perlengkapan WHERE id_perlengkapan='$brg'");
    $d_stok = mysqli_fetch_assoc($cek_stok);

    // Kalau kolom jumlah belum ada di DB, baris if ini bakal error/warning.
    // Pastikan alter table perlengkapan add jumlah int(11) default 0;
    if ($d_stok && $d_stok['jumlah'] < $jml) {
        echo "<script>alert('❌ Stok tidak cukup! Sisa stok: {$d_stok['jumlah']}');</script>";
    } else {
        // 1. Simpan Transaksi (Sesuaikan nama kolom DB: jml_penggunaan & nama_penggunaan)
        $q = "INSERT INTO penggunaan_bahan_baku (id_penggunaan, tgl_penggunaan, id_perlengkapan, jml_penggunaan, nama_penggunaan) 
              VALUES ('$id', '$tgl', '$brg', '$jml', '$ket')";
        
        if (mysqli_query($koneksi, $q)) {
            // 2. Kurangi Stok Master Barang
            mysqli_query($koneksi, "UPDATE perlengkapan SET jumlah = jumlah - $jml WHERE id_perlengkapan='$brg'");
            
            echo "<script>alert('✅ Penggunaan tercatat! Stok berkurang.'); window.location='?table=penggunaan_bahan_baku';</script>";
        } else {
            echo "<script>alert('❌ Gagal simpan: " . mysqli_error($koneksi) . "');</script>";
        }
    }
}

// --- VIEW FORM (TAMBAH) ---
if ($halaman == 'tambah'):
    $id_val = buatIdOtomatis($koneksi, "penggunaan_bahan_baku", "id_penggunaan", "PGN");
?>
    <div class="max-w-xl mx-auto bg-white p-8 rounded-xl shadow-lg border border-gray-100 mt-4 relative overflow-hidden">
        <div class="absolute top-0 left-0 w-full h-1 bg-gradient-to-r from-yellow-400 to-orange-500"></div>
        <div class="flex justify-between items-center mb-6 border-b pb-4">
            <h2 class="text-xl font-bold text-gray-800">
                <span class="text-yellow-600">📉 Catat Pemakaian Stok</span>
            </h2>
            <a href="?table=penggunaan_bahan_baku" class="text-gray-400 hover:text-red-500 transition"><i class="fas fa-times text-xl"></i></a>
        </div>

        <form method="POST" action="" class="space-y-4">
            <div>
                <label class="block text-sm font-bold text-gray-700 mb-1">ID Penggunaan</label>
                <input type="text" name="id_penggunaan" value="<?= $id_val ?>" readonly class="w-full px-4 py-2 border rounded-lg bg-gray-100 text-gray-500 font-mono font-bold cursor-not-allowed">
            </div>

            <div>
                <label class="block text-sm font-bold text-gray-700 mb-1">Tanggal Pakai</label>
                <input type="date" name="tgl_penggunaan" value="<?= date('Y-m-d') ?>" required class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-yellow-500">
            </div>

            <div>
                <label class="block text-sm font-bold text-gray-700 mb-1">Pilih Barang</label>
                <select name="id_perlengkapan" required class="w-full px-4 py-2 border rounded-lg bg-white focus:ring-2 focus:ring-yellow-500">
                    <option value="">-- Pilih Perlengkapan --</option>
                    <?php 
                    // Pastikan ada kolom 'jumlah' di perlengkapan
                    $q_brg = mysqli_query($koneksi, "SELECT * FROM perlengkapan"); 
                    while($r=mysqli_fetch_assoc($q_brg)){ 
                        // Handle kalau kolom jumlah null/gak ada biar gak error display
                        $stok = isset($r['jumlah']) ? $r['jumlah'] : 0;
                        if($stok > 0) {
                            echo "<option value='{$r['id_perlengkapan']}'>{$r['nama_perlengkapan']} (Sisa: {$stok})</option>"; 
                        }
                    } 
                    ?>
                </select>
            </div>

            <div>
                <label class="block text-sm font-bold text-gray-700 mb-1">Jumlah Pakai</label>
                <input type="number" name="jumlah" placeholder="0" min="1" required class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-yellow-500">
            </div>

            <div>
                <label class="block text-sm font-bold text-gray-700 mb-1">Keterangan / Keperluan</label>
                <textarea name="keterangan" placeholder="Contoh: Cuci Harian, Barang Rusak, dll" class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-yellow-500"></textarea>
            </div>

            <div class="pt-4 flex gap-3">
                <button type="submit" name="simpan_penggunaan" class="flex-1 bg-yellow-500 hover:bg-yellow-600 text-white font-bold py-2 px-4 rounded-lg shadow transition">
                    Simpan Data
                </button>
            </div>
        </form>
    </div>

<?php else: ?>
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="p-6 border-b border-gray-100 flex justify-between items-center bg-gray-50">
            <div>
                <h3 class="text-lg font-bold text-yellow-600">Riwayat Penggunaan Stok</h3>
                <p class="text-xs text-gray-500">Log barang keluar dari gudang.</p>
            </div>
            <a href="?table=penggunaan_bahan_baku&hal=tambah" class="bg-yellow-500 hover:bg-yellow-600 text-white text-sm font-semibold py-2 px-4 rounded-lg shadow flex items-center gap-2 transition">
                <i class="fas fa-plus"></i> Catat Pakai
            </a>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-white text-gray-600 text-xs uppercase tracking-wider border-b border-gray-100">
                        <th class="px-6 py-4 font-semibold text-center">ID</th>
                        <th class="px-6 py-4 font-semibold">Tanggal</th>
                        <th class="px-6 py-4 font-semibold">Nama Barang</th>
                        <th class="px-6 py-4 font-semibold text-right">Jml</th>
                        <th class="px-6 py-4 font-semibold">Keperluan</th>
                        <th class="px-6 py-4 font-semibold text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    <?php
                    $no = 1;
                    // Hapus JOIN ke karyawan karena tidak ada relasi
                    $q = "SELECT pg.*, p.nama_perlengkapan, p.jenis_perlengkapan 
                          FROM penggunaan_bahan_baku pg
                          LEFT JOIN perlengkapan p ON pg.id_perlengkapan = p.id_perlengkapan
                          ORDER BY pg.tgl_penggunaan DESC";
                    $sql = mysqli_query($koneksi, $q);
                    
                    if(mysqli_num_rows($sql) > 0) {
                        while($row = mysqli_fetch_assoc($sql)) {
                    ?>
                    <tr class="hover:bg-yellow-50 transition duration-150">
                        <td class="px-6 py-4 text-center text-gray-500 text-sm font-mono"><?= $row['id_penggunaan'] ?></td>
                        <td class="px-6 py-4 text-sm"><?= date('d/m/Y', strtotime($row['tgl_penggunaan'])) ?></td>
                        <td class="px-6 py-4">
                            <div class="font-bold text-gray-800"><?= $row['nama_perlengkapan'] ?></div>
                            <span class="text-[10px] bg-gray-100 px-1 rounded text-gray-500"><?= $row['jenis_perlengkapan'] ?></span>
                        </td>
                        <td class="px-6 py-4 text-right font-bold text-red-500">-<?= number_format($row['jml_penggunaan']) ?></td>
                        <td class="px-6 py-4 text-sm text-gray-600 italic"><?= $row['nama_penggunaan'] ?></td>
                        
                        <td class="px-6 py-4 text-center">
                            <a href="?table=penggunaan_bahan_baku&aksi=hapus&id=<?= $row['id_penggunaan'] ?>" onclick="return confirm('Hapus data ini? Stok akan dikembalikan.')" class="text-red-400 hover:text-red-600 transition" title="Hapus & Refund Stok"><i class="fas fa-trash-can"></i></a>
                        </td>
                    </tr>
                    <?php 
                        }
                    } else {
                        echo "<tr><td colspan='6' class='p-8 text-center text-gray-400 italic'>Belum ada data penggunaan.</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>
<?php endif; ?>