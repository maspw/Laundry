<?php
// --- FILE: modul_pembelian.php ---
// Logic: Transaksi Pembelian Bahan Baku

// Pastikan koneksi sudah ada (via dashboard.php)
// include 'koneksi.php'; 

$halaman = isset($_GET['hal']) ? $_GET['hal'] : 'data';

// --- 1. LOGIC HAPUS ---
if (isset($_GET['aksi']) && $_GET['aksi'] == 'hapus') {
    $id = $_GET['id'];
    $q = "DELETE FROM pembelian_bahan_baku WHERE id_pembelian='$id'";
    if (mysqli_query($koneksi, $q)) {
        echo "<script>alert('✅ Data pembelian dihapus!'); window.location='?table=pembelian_bahan_baku';</script>";
    } else {
        echo "<script>alert('❌ Gagal hapus: " . mysqli_error($koneksi) . "'); window.location='?table=pembelian_bahan_baku';</script>";
    }
}

// --- 2. LOGIC SIMPAN ---
if (isset($_POST['simpan_pembelian'])) {
    $id_pembelian = $_POST['id_pembelian'];
    $tgl          = $_POST['tgl_pembelian'];
    $nama         = $_POST['nama_pembelian'];
    $jml          = $_POST['jml_pembelian'];
    $id_karyawan  = $_POST['id_karyawan'];
    $id_perlengkapan = $_POST['id_perlengkapan'];

    $q = "INSERT INTO pembelian_bahan_baku (id_pembelian, tgl_pembelian, nama_pembelian, jml_pembelian, id_karyawan, id_perlengkapan) 
          VALUES ('$id_pembelian', '$tgl', '$nama', '$jml', '$id_karyawan', '$id_perlengkapan')";
    
    if (mysqli_query($koneksi, $q)) {
        echo "<script>alert('✅ Pembelian berhasil disimpan!'); window.location='?table=pembelian_bahan_baku';</script>";
    } else {
        echo "<script>alert('❌ Gagal simpan: " . mysqli_error($koneksi) . "');</script>";
    }
}

// --- VIEW FORM TAMBAH ---
if ($halaman == 'tambah'):
    // Generate ID Otomatis (Prefix PBL sesuai kode lama lo)
    $id_auto = buatIdOtomatis($koneksi, "pembelian_bahan_baku", "id_pembelian", "PBL");
?>
    <div class="max-w-xl mx-auto bg-white p-8 rounded-xl shadow-lg border border-gray-100 mt-4 relative overflow-hidden">
        <div class="absolute top-0 left-0 w-full h-1 bg-gradient-to-r from-green-400 to-teal-500"></div>
        <div class="flex justify-between items-center mb-6 border-b pb-4">
            <h2 class="text-xl font-bold text-gray-800">
                <span class="text-green-600">📦 Pembelian Bahan Baku</span>
            </h2>
            <a href="?table=pembelian_bahan_baku" class="text-gray-400 hover:text-red-500 transition"><i class="fas fa-times text-xl"></i></a>
        </div>

        <form method="POST" action="" class="space-y-4">
            <div>
                <label class="block text-sm font-bold text-gray-700 mb-1">ID Pembelian</label>
                <input type="text" name="id_pembelian" value="<?= $id_auto ?>" readonly class="w-full px-4 py-2 border rounded-lg bg-gray-100 text-gray-500 font-mono font-bold cursor-not-allowed">
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-1">Tanggal</label>
                    <input type="date" name="tgl_pembelian" value="<?= date('Y-m-d') ?>" required class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-green-500">
                </div>
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-1">Jumlah</label>
                    <input type="number" name="jml_pembelian" placeholder="0" required class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-green-500">
                </div>
            </div>

            <div>
                <label class="block text-sm font-bold text-gray-700 mb-1">Nama Pembelian</label>
                <input type="text" name="nama_pembelian" placeholder="Cth: Restock Deterjen" required class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-green-500">
            </div>

            <div>
                <label class="block text-sm font-bold text-gray-700 mb-1">Perlengkapan</label>
                <select name="id_perlengkapan" required class="w-full px-4 py-2 border rounded-lg bg-white focus:ring-2 focus:ring-green-500">
                    <option value="">-- Pilih Barang --</option>
                    <?php 
                    $q_brg = mysqli_query($koneksi, "SELECT * FROM perlengkapan"); 
                    while($r=mysqli_fetch_assoc($q_brg)){ 
                        echo "<option value='{$r['id_perlengkapan']}'>{$r['nama_perlengkapan']} (Stok: {$r['jumlah']})</option>"; 
                    } 
                    ?>
                </select>
            </div>

            <div>
                <label class="block text-sm font-bold text-gray-700 mb-1">Karyawan (PIC)</label>
                <select name="id_karyawan" required class="w-full px-4 py-2 border rounded-lg bg-white focus:ring-2 focus:ring-green-500">
                    <option value="">-- Pilih Karyawan --</option>
                    <?php 
                    $q_kry = mysqli_query($koneksi, "SELECT * FROM karyawan"); 
                    while($r=mysqli_fetch_assoc($q_kry)){ 
                        echo "<option value='{$r['id_karyawan']}'>{$r['nama_karyawan']}</option>"; 
                    } 
                    ?>
                </select>
            </div>

            <div class="pt-4 flex gap-3">
                <button type="submit" name="simpan_pembelian" class="flex-1 bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-4 rounded-lg shadow transition">
                    Simpan Data
                </button>
            </div>
        </form>
    </div>

<?php else: ?>
    <!-- VIEW TABEL DATA -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="p-6 border-b border-gray-100 flex justify-between items-center bg-gray-50">
            <div>
                <h3 class="text-lg font-bold text-gray-800">Daftar Pembelian Bahan Baku</h3>
                <p class="text-xs text-gray-500">Riwayat belanja operasional laundry.</p>
            </div>
            <a href="?table=pembelian_bahan_baku&hal=tambah" class="bg-green-600 hover:bg-green-700 text-white text-sm font-semibold py-2 px-4 rounded-lg shadow flex items-center gap-2 transition">
                <i class="fas fa-plus"></i> Input Baru
            </a>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-white text-gray-600 text-xs uppercase tracking-wider border-b border-gray-100">
                        <th class="px-6 py-4 font-semibold text-center">No</th>
                        <th class="px-6 py-4 font-semibold">ID</th>
                        <th class="px-6 py-4 font-semibold">Tanggal</th>
                        <th class="px-6 py-4 font-semibold">Item</th>
                        <th class="px-6 py-4 font-semibold text-right">Qty</th>
                        <th class="px-6 py-4 font-semibold">PIC</th>
                        <th class="px-6 py-4 font-semibold text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    <?php
                    $no = 1;
                    // Join tabel biar nama barang & karyawan muncul
                    $q = "SELECT pb.*, k.nama_karyawan, p.nama_perlengkapan 
                          FROM pembelian_bahan_baku pb
                          LEFT JOIN karyawan k ON pb.id_karyawan = k.id_karyawan
                          LEFT JOIN perlengkapan p ON pb.id_perlengkapan = p.id_perlengkapan
                          ORDER BY pb.tgl_pembelian DESC";
                    $sql = mysqli_query($koneksi, $q);
                    
                    if(mysqli_num_rows($sql) > 0) {
                        while($row = mysqli_fetch_assoc($sql)) {
                    ?>
                    <tr class="hover:bg-green-50 transition duration-150">
                        <td class="px-6 py-4 text-center text-gray-500 text-sm"><?= $no++ ?></td>
                        <td class="px-6 py-4 font-mono font-bold text-gray-700 text-sm"><?= $row['id_pembelian'] ?></td>
                        <td class="px-6 py-4 text-sm"><?= date('d/m/Y', strtotime($row['tgl_pembelian'])) ?></td>
                        <td class="px-6 py-4">
                            <div class="text-sm font-bold text-gray-800"><?= $row['nama_perlengkapan'] ?></div>
                            <div class="text-xs text-gray-500"><?= $row['nama_pembelian'] ?></div>
                        </td>
                        <td class="px-6 py-4 text-right font-bold text-green-600"><?= number_format($row['jml_pembelian']) ?></td>
                        <td class="px-6 py-4 text-sm text-gray-600"><?= $row['nama_karyawan'] ?></td>
                        <td class="px-6 py-4 text-center">
                            <a href="?table=pembelian_bahan_baku&aksi=hapus&id=<?= $row['id_pembelian'] ?>" onclick="return confirm('Hapus data pembelian ini?')" class="text-red-400 hover:text-red-600 transition" title="Hapus"><i class="fas fa-trash-can"></i></a>
                        </td>
                    </tr>
                    <?php 
                        }
                    } else {
                        echo "<tr><td colspan='7' class='p-8 text-center text-gray-400 italic'>Belum ada data pembelian.</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>
<?php endif; ?>