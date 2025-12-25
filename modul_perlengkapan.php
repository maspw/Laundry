<?php
// --- FILE: modul_perlengkapan.php ---
// Logic: CRUD Master Data Perlengkapan (Fix Undefined Array Key)

$halaman = isset($_GET['hal']) ? $_GET['hal'] : 'data';

// --- 1. LOGIC HAPUS ---
if (isset($_GET['aksi']) && $_GET['aksi'] == 'hapus') {
    $id = $_GET['id'];
    $q = "DELETE FROM perlengkapan WHERE id_perlengkapan='$id'";
    if (mysqli_query($koneksi, $q)) {
        echo "<script>alert('✅ Data perlengkapan dihapus!'); window.location='?table=perlengkapan';</script>";
    } else {
        echo "<script>alert('❌ Gagal hapus: " . mysqli_error($koneksi) . "'); window.location='?table=perlengkapan';</script>";
    }
}

// --- 2. LOGIC SIMPAN (BARU / EDIT) ---
if (isset($_POST['simpan_perlengkapan'])) {
    $id    = $_POST['id_perlengkapan'];
    $nama  = $_POST['nama_perlengkapan'];
    $jenis = $_POST['jenis_perlengkapan'];
    $harga = $_POST['harga_satuan'];
    
    // Fix: Pastikan key 'jumlah' ada, kalau ga ada set 0
    $jumlah = isset($_POST['jumlah']) ? $_POST['jumlah'] : 0; 
    
    $is_edit = $_POST['is_edit_mode'];

    if ($is_edit == '1') {
        $q = "UPDATE perlengkapan SET nama_perlengkapan='$nama', jenis_perlengkapan='$jenis', harga_satuan='$harga', jumlah='$jumlah' WHERE id_perlengkapan='$id'";
        $msg = "Data berhasil diupdate! ✏️";
    } else {
        $q = "INSERT INTO perlengkapan (id_perlengkapan, nama_perlengkapan, jenis_perlengkapan, harga_satuan, jumlah) VALUES ('$id', '$nama', '$jenis', '$harga', '$jumlah')";
        $msg = "Perlengkapan baru ditambahkan! 📦";
    }
    
    if (mysqli_query($koneksi, $q)) {
        echo "<script>alert('✅ $msg'); window.location='?table=perlengkapan';</script>";
    } else {
        echo "<script>alert('❌ Gagal simpan: " . mysqli_error($koneksi) . "');</script>";
    }
}

// --- VIEW FORM (TAMBAH / EDIT) ---
if ($halaman == 'tambah' || $halaman == 'edit'):
    $is_edit = ($halaman == 'edit');
    
    // Inisialisasi Variabel Awal (PENTING BIAR GA ERROR)
    $id_val = ""; $nama_val = ""; $jenis_val = ""; $harga_val = ""; $jml_val = "0";

    if ($is_edit && isset($_GET['id'])) {
        $id = $_GET['id'];
        $q = mysqli_query($koneksi, "SELECT * FROM perlengkapan WHERE id_perlengkapan='$id'");
        $data = mysqli_fetch_assoc($q);
        if ($data) {
            $id_val = $data['id_perlengkapan'];
            $nama_val = $data['nama_perlengkapan'];
            $jenis_val = $data['jenis_perlengkapan'];
            $harga_val = $data['harga_satuan'];
            
            // Fix: Cek apakah key 'jumlah' ada di database?
            // Kalau kolom 'jumlah' belum ada di DB lo, dia bakal error.
            // Pake ?? 0 buat jaga-jaga.
            $jml_val = $data['jumlah'] ?? 0; 
        }
    } elseif (!$is_edit) {
        $id_val = buatIdOtomatis($koneksi, "perlengkapan", "id_perlengkapan", "PRL");
    }
?>
    <div class="max-w-xl mx-auto bg-white p-8 rounded-xl shadow-lg border border-gray-100 mt-4 relative overflow-hidden">
        <div class="absolute top-0 left-0 w-full h-1 bg-gradient-to-r from-orange-400 to-amber-500"></div>
        <div class="flex justify-between items-center mb-6 border-b pb-4">
            <h2 class="text-xl font-bold text-gray-800">
                <?= $is_edit ? '<span class="text-yellow-600">✏️ Edit Perlengkapan</span>' : '<span class="text-orange-600">📦 Tambah Perlengkapan</span>' ?>
            </h2>
            <a href="?table=perlengkapan" class="text-gray-400 hover:text-red-500 transition"><i class="fas fa-times text-xl"></i></a>
        </div>

        <form method="POST" action="" class="space-y-4">
            <input type="hidden" name="is_edit_mode" value="<?= $is_edit ? '1' : '0' ?>">

            <div>
                <label class="block text-sm font-bold text-gray-700 mb-1">ID Perlengkapan</label>
                <input type="text" name="id_perlengkapan" value="<?= $id_val ?>" readonly class="w-full px-4 py-2 border rounded-lg bg-gray-100 text-gray-500 font-mono font-bold cursor-not-allowed">
            </div>

            <div>
                <label class="block text-sm font-bold text-gray-700 mb-1">Nama Barang</label>
                <input type="text" name="nama_perlengkapan" value="<?= $nama_val ?>" placeholder="Cth: Deterjen Cair, Plastik 5kg" required class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-orange-500">
            </div>

            <div>
                <label class="block text-sm font-bold text-gray-700 mb-1">Jenis</label>
                <select name="jenis_perlengkapan" class="w-full px-4 py-2 border rounded-lg bg-white focus:ring-2 focus:ring-orange-500">
                    <option value="Bahan Baku" <?= ($jenis_val == 'Bahan Baku') ? 'selected' : '' ?>>Bahan Baku</option>
                    <option value="Alat" <?= ($jenis_val == 'Alat') ? 'selected' : '' ?>>Alat</option>
                    <option value="ATK" <?= ($jenis_val == 'ATK') ? 'selected' : '' ?>>ATK</option>
                </select>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-1">Harga Satuan (Rp)</label>
                    <input type="number" name="harga_satuan" value="<?= $harga_val ?>" placeholder="0" required class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-orange-500">
                </div>
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-1">Jumlah Stok</label>
                    <!-- Tambahin pengecekan isset() di value -->
                    <input type="number" name="jumlah" value="<?= $jml_val ?>" placeholder="0" required class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-orange-500">
                </div>
            </div>

            <div class="pt-4 flex gap-3">
                <button type="submit" name="simpan_perlengkapan" class="flex-1 bg-orange-500 hover:bg-orange-600 text-white font-bold py-2 px-4 rounded-lg shadow transition">
                    <?= $is_edit ? 'Simpan Perubahan' : 'Simpan Data' ?>
                </button>
            </div>
        </form>
    </div>

<?php else: ?>
    <!-- VIEW TABEL DATA -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="p-6 border-b border-gray-100 flex justify-between items-center bg-gray-50">
            <div>
                <h3 class="text-lg font-bold text-orange-600">Daftar Perlengkapan</h3>
                <p class="text-xs text-gray-500">Inventory bahan baku dan alat laundry.</p>
            </div>
            <a href="?table=perlengkapan&hal=tambah" class="bg-orange-500 hover:bg-orange-600 text-white text-sm font-semibold py-2 px-4 rounded-lg shadow flex items-center gap-2 transition">
                <i class="fas fa-plus"></i> Tambah Baru
            </a>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-white text-gray-600 text-xs uppercase tracking-wider border-b border-gray-100">
                        <th class="px-6 py-4 font-semibold text-center">ID</th>
                        <th class="px-6 py-4 font-semibold">Nama Barang</th>
                        <th class="px-6 py-4 font-semibold">Jenis</th>
                        <th class="px-6 py-4 font-semibold text-right">Harga Satuan</th>
                        <th class="px-6 py-4 font-semibold text-right">Stok</th>
                        <th class="px-6 py-4 font-semibold text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    <?php
                    $q = "SELECT * FROM perlengkapan ORDER BY id_perlengkapan ASC";
                    $sql = mysqli_query($koneksi, $q);
                    
                    if(mysqli_num_rows($sql) > 0) {
                        while($row = mysqli_fetch_assoc($sql)) {
                            // Fix: Cek key 'jumlah' ada atau nggak
                            $stok = isset($row['jumlah']) ? $row['jumlah'] : 0;
                            $stokColor = ($stok < 5) ? 'text-red-600' : 'text-green-600';
                    ?>
                    <tr class="hover:bg-orange-50 transition duration-150">
                        <td class="px-6 py-4 text-center font-mono font-bold text-gray-700 text-sm"><?= $row['id_perlengkapan'] ?></td>
                        <td class="px-6 py-4 font-medium text-gray-800"><?= $row['nama_perlengkapan'] ?></td>
                        <td class="px-6 py-4 text-sm text-gray-600">
                            <span class="bg-gray-100 border border-gray-200 px-2 py-1 rounded text-xs"><?= $row['jenis_perlengkapan'] ?></span>
                        </td>
                        <td class="px-6 py-4 text-right font-mono text-gray-700">Rp <?= number_format($row['harga_satuan'], 0, ',', '.') ?></td>
                        <td class="px-6 py-4 text-right font-bold <?= $stokColor ?>">
                            <?= number_format($stok) ?>
                        </td>
                        <td class="px-6 py-4 text-center">
                            <div class="flex justify-center gap-2">
                                <a href="?table=perlengkapan&hal=edit&id=<?= $row['id_perlengkapan'] ?>" class="text-yellow-500 hover:text-yellow-600 transition" title="Edit"><i class="fas fa-pen-to-square"></i></a>
                                <a href="?table=perlengkapan&aksi=hapus&id=<?= $row['id_perlengkapan'] ?>" onclick="return confirm('Hapus data barang ini?')" class="text-red-400 hover:text-red-600 transition" title="Hapus"><i class="fas fa-trash-can"></i></a>
                            </div>
                        </td>
                    </tr>
                    <?php 
                        }
                    } else {
                        echo "<tr><td colspan='6' class='p-8 text-center text-gray-400 italic'>Belum ada data perlengkapan.</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>
<?php endif; ?>