<?php
// --- FILE: modul_pengeluaran.php ---
// Logic: Transaksi Pengeluaran Operasional

$halaman = isset($_GET['hal']) ? $_GET['hal'] : 'data';

// --- 1. LOGIC HAPUS ---
if (isset($_GET['aksi']) && $_GET['aksi'] == 'hapus') {
    $id = $_GET['id'];
    $q = "DELETE FROM pengeluaran_operasional WHERE id_pengeluaran='$id'";
    if (mysqli_query($koneksi, $q)) {
        echo "<script>alert('✅ Data pengeluaran dihapus!'); window.location='?table=pengeluaran_operasional';</script>";
    } else {
        echo "<script>alert('❌ Gagal hapus: " . mysqli_error($koneksi) . "'); window.location='?table=pengeluaran_operasional';</script>";
    }
}

// --- 2. LOGIC SIMPAN ---
if (isset($_POST['simpan_pengeluaran'])) {
    $id   = $_POST['id_pengeluaran'];
    $tgl  = $_POST['tgl_pengeluaran'];
    $nama = $_POST['nama_pengeluaran'];
    $jml  = $_POST['jml_pengeluaran'];
    $kar  = $_POST['id_karyawan'];

    $q = "INSERT INTO pengeluaran_operasional VALUES ('$id', '$tgl', '$nama', '$jml', '$kar')";
    
    if (mysqli_query($koneksi, $q)) {
        echo "<script>alert('✅ Pengeluaran dicatat!'); window.location='?table=pengeluaran_operasional';</script>";
    } else {
        echo "<script>alert('❌ Gagal simpan: " . mysqli_error($koneksi) . "');</script>";
    }
}

// --- VIEW FORM TAMBAH ---
if ($halaman == 'tambah'):
    // Generate ID Otomatis (Prefix PO)
    $id_auto = buatIdOtomatis($koneksi, "pengeluaran_operasional", "id_pengeluaran", "PO");
?>
    <div class="max-w-xl mx-auto bg-white p-8 rounded-xl shadow-lg border border-gray-100 mt-4 relative overflow-hidden">
        <div class="absolute top-0 left-0 w-full h-1 bg-gradient-to-r from-red-400 to-orange-500"></div>
        <div class="flex justify-between items-center mb-6 border-b pb-4">
            <h2 class="text-xl font-bold text-gray-800">
                <span class="text-red-600">💸 Catat Pengeluaran</span>
            </h2>
            <a href="?table=pengeluaran_operasional" class="text-gray-400 hover:text-red-500 transition"><i class="fas fa-times text-xl"></i></a>
        </div>

        <form method="POST" action="" class="space-y-4">
            <div>
                <label class="block text-sm font-bold text-gray-700 mb-1">ID Transaksi</label>
                <input type="text" name="id_pengeluaran" value="<?= $id_auto ?>" readonly class="w-full px-4 py-2 border rounded-lg bg-gray-100 text-gray-500 font-mono font-bold cursor-not-allowed">
            </div>

            <div>
                <label class="block text-sm font-bold text-gray-700 mb-1">Tanggal</label>
                <input type="date" name="tgl_pengeluaran" value="<?= date('Y-m-d') ?>" required class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-red-500">
            </div>

            <div>
                <label class="block text-sm font-bold text-gray-700 mb-1">Nama Pengeluaran</label>
                <input type="text" name="nama_pengeluaran" placeholder="Cth: Bayar Listrik Bulan Ini" required class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-red-500">
            </div>

            <div>
                <label class="block text-sm font-bold text-gray-700 mb-1">Jumlah (Rp)</label>
                <input type="number" name="jml_pengeluaran" placeholder="0" required class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-red-500">
            </div>

            <div>
                <label class="block text-sm font-bold text-gray-700 mb-1">Penanggung Jawab (PIC)</label>
                <select name="id_karyawan" required class="w-full px-4 py-2 border rounded-lg bg-white focus:ring-2 focus:ring-red-500">
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
                <button type="submit" name="simpan_pengeluaran" class="flex-1 bg-red-600 hover:bg-red-700 text-white font-bold py-2 px-4 rounded-lg shadow transition">
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
                <h3 class="text-lg font-bold text-gray-800">Daftar Pengeluaran Operasional</h3>
                <p class="text-xs text-gray-500">Biaya operasional sehari-hari.</p>
            </div>
            <a href="?table=pengeluaran_operasional&hal=tambah" class="bg-red-600 hover:bg-red-700 text-white text-sm font-semibold py-2 px-4 rounded-lg shadow flex items-center gap-2 transition">
                <i class="fas fa-plus"></i> Catat Baru
            </a>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-white text-gray-600 text-xs uppercase tracking-wider border-b border-gray-100">
                        <th class="px-6 py-4 font-semibold text-center">No</th>
                        <th class="px-6 py-4 font-semibold">ID</th>
                        <th class="px-6 py-4 font-semibold">Tanggal</th>
                        <th class="px-6 py-4 font-semibold">Keterangan</th>
                        <th class="px-6 py-4 font-semibold text-right">Jumlah</th>
                        <th class="px-6 py-4 font-semibold">PIC</th>
                        <th class="px-6 py-4 font-semibold text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    <?php
                    $no = 1;
                    $q = "SELECT po.*, k.nama_karyawan 
                          FROM pengeluaran_operasional po
                          LEFT JOIN karyawan k ON po.id_karyawan = k.id_karyawan
                          ORDER BY po.tgl_pengeluaran DESC";
                    $sql = mysqli_query($koneksi, $q);
                    
                    if(mysqli_num_rows($sql) > 0) {
                        while($row = mysqli_fetch_assoc($sql)) {
                    ?>
                    <tr class="hover:bg-red-50 transition duration-150">
                        <td class="px-6 py-4 text-center text-gray-500 text-sm"><?= $no++ ?></td>
                        <td class="px-6 py-4 font-mono font-bold text-gray-700 text-sm"><?= $row['id_pengeluaran'] ?></td>
                        <td class="px-6 py-4 text-sm"><?= date('d/m/Y', strtotime($row['tgl_pengeluaran'])) ?></td>
                        <td class="px-6 py-4 font-medium text-gray-800"><?= $row['nama_pengeluaran'] ?></td>
                        <td class="px-6 py-4 text-right font-bold text-red-600">Rp <?= number_format($row['jml_pengeluaran']) ?></td>
                        <td class="px-6 py-4 text-sm text-gray-600"><?= $row['nama_karyawan'] ?></td>
                        <td class="px-6 py-4 text-center">
                            <a href="?table=pengeluaran_operasional&aksi=hapus&id=<?= $row['id_pengeluaran'] ?>" onclick="return confirm('Hapus data pengeluaran ini?')" class="text-red-400 hover:text-red-600 transition" title="Hapus"><i class="fas fa-trash-can"></i></a>
                        </td>
                    </tr>
                    <?php 
                        }
                    } else {
                        echo "<tr><td colspan='7' class='p-8 text-center text-gray-400 italic'>Belum ada data pengeluaran.</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>
<?php endif; ?>