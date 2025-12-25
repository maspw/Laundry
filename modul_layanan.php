<?php
// --- FILE: modul_layanan.php ---
// Logic: CRUD Master Data Layanan

// 1. LOGIC DELETE
if (isset($_GET['aksi']) && $_GET['aksi'] == 'hapus') {
    $id = $_GET['id'];
    $hapus = mysqli_query($koneksi, "DELETE FROM layanan WHERE id_layanan='$id'");
    if ($hapus) {
        echo "<script>alert('✅ Layanan berhasil dihapus!'); window.location='?table=layanan';</script>";
    } else {
        echo "<script>alert('❌ Gagal hapus! Data mungkin sedang dipakai.'); window.location='?table=layanan';</script>";
    }
}

// 2. LOGIC SAVE (INSERT/UPDATE)
if (isset($_POST['simpan_layanan'])) {
    $id   = $_POST['id_layanan'];
    $nama = $_POST['nama_layanan'];
    $hrg  = $_POST['harga'];
    $stn  = $_POST['satuan'];
    $est  = $_POST['estimasi_waktu'];
    $is_edit_mode = $_POST['is_edit_mode'];

    if ($is_edit_mode == '1') {
        $sql = "UPDATE layanan SET nama_layanan='$nama', harga='$hrg', satuan='$stn', estimasi_waktu='$est' WHERE id_layanan='$id'";
        $msg = "Data layanan berhasil diupdate! ✏️";
    } else {
        // Cek duplikat ID
        $cek = mysqli_query($koneksi, "SELECT * FROM layanan WHERE id_layanan='$id'");
        if (mysqli_num_rows($cek) > 0) {
            echo "<script>alert('❌ ID Layanan sudah ada! Gunakan ID lain.');</script>";
            $sql = "";
        } else {
            $sql = "INSERT INTO layanan VALUES('$id', '$nama', '$hrg', '$stn', '$est')";
            $msg = "Layanan baru berhasil ditambah! ➕";
        }
    }

    if ($sql != "") {
        if (mysqli_query($koneksi, $sql)) {
            echo "<script>alert('$msg'); window.location='?table=layanan';</script>";
        } else {
            echo "<script>alert('Gagal simpan: " . mysqli_error($koneksi) . "');</script>";
        }
    }
}

// 3. PREPARE VARIABLE VIEW
$halaman = isset($_GET['hal']) ? $_GET['hal'] : 'data';
$id_lyn = ""; $nm_lyn = ""; $hrg_lyn = ""; $sat_lyn = ""; $est_lyn = ""; $is_edit = false;

if ($halaman == 'edit' && isset($_GET['id'])) {
    $id_target = $_GET['id'];
    $q_edit = mysqli_query($koneksi, "SELECT * FROM layanan WHERE id_layanan='$id_target'");
    if ($d = mysqli_fetch_assoc($q_edit)) {
        $id_lyn = $d['id_layanan']; $nm_lyn = $d['nama_layanan']; $hrg_lyn = $d['harga'];
        $sat_lyn = $d['satuan']; $est_lyn = $d['estimasi_waktu']; $is_edit = true;
    }
} elseif ($halaman == 'tambah') {
    // ID Otomatis (Opsional, kalau mau manual kosongin aja)
    // $id_lyn = buatIdOtomatis($koneksi, "layanan", "id_layanan", "LYN"); 
}

// --- VIEW START ---
if ($halaman == 'tambah' || $halaman == 'edit'): 
?>
    <!-- VIEW FORM -->
    <div class="max-w-lg mx-auto bg-white p-8 rounded-xl shadow-lg border border-gray-100 mt-4">
        <div class="flex justify-between items-center mb-6 border-b pb-4">
            <h2 class="text-xl font-bold text-gray-800">
                <?php echo $is_edit ? '<span class="text-yellow-500">✏️ Edit Layanan</span>' : '<span class="text-blue-500">➕ Tambah Layanan</span>'; ?>
            </h2>
            <a href="?table=layanan" class="text-gray-400 hover:text-gray-600 transition"><i class="fas fa-times text-xl"></i></a>
        </div>
        <form method="POST" action="">
            <input type="hidden" name="is_edit_mode" value="<?= $is_edit ? '1' : '0' ?>">
            
            <div class="mb-4">
                <label class="block text-gray-700 text-sm font-bold mb-2">ID Layanan</label>
                <input type="text" name="id_layanan" value="<?= $id_lyn ?>" class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 <?= $is_edit ? 'bg-gray-100 cursor-not-allowed' : 'bg-white' ?>" placeholder="Ex: L001" required <?= $is_edit ? 'readonly' : '' ?>>
            </div>

            <div class="mb-4">
                <label class="block text-gray-700 text-sm font-bold mb-2">Nama Layanan</label>
                <input type="text" name="nama_layanan" value="<?= $nm_lyn ?>" class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500" placeholder="Ex: Cuci Komplit" required>
            </div>

            <div class="grid grid-cols-2 gap-4 mb-4">
                <div>
                    <label class="block text-gray-700 text-sm font-bold mb-2">Harga (Rp)</label>
                    <input type="number" name="harga" value="<?= $hrg_lyn ?>" class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500" placeholder="0" required>
                </div>
                <div>
                    <label class="block text-gray-700 text-sm font-bold mb-2">Satuan</label>
                    <div class="relative">
                        <select name="satuan" class="w-full px-4 py-2 border rounded-lg appearance-none bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500">
                            <option value="Kg" <?= ($sat_lyn == 'Kg') ? 'selected' : '' ?>>Kg</option>
                            <option value="Pcs" <?= ($sat_lyn == 'Pcs') ? 'selected' : '' ?>>Pcs</option>
                            <option value="Meter" <?= ($sat_lyn == 'Meter') ? 'selected' : '' ?>>Meter</option>
                        </select>
                        <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-gray-700"><i class="fas fa-chevron-down text-xs"></i></div>
                    </div>
                </div>
            </div>

            <div class="mb-6">
                <label class="block text-gray-700 text-sm font-bold mb-2">Estimasi Waktu</label>
                <input type="text" name="estimasi_waktu" value="<?= $est_lyn ?>" class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500" placeholder="Ex: 2 Hari" required>
            </div>

            <div class="flex gap-3">
                <button type="submit" name="simpan_layanan" class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded-lg transition duration-200 shadow-lg shadow-indigo-200"><?= $is_edit ? 'Update Data' : 'Simpan Data' ?></button>
                <a href="?table=layanan" class="w-full bg-gray-200 hover:bg-gray-300 text-gray-700 font-bold py-2 px-4 rounded-lg text-center transition duration-200">Batal</a>
            </div>
        </form>
    </div>

<?php else: ?>
    <!-- VIEW TABEL -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="p-6 border-b border-gray-100 flex justify-between items-center bg-gray-50">
            <div><h3 class="text-lg font-bold text-indigo-600">Daftar Layanan</h3><p class="text-xs text-gray-500">Master data jenis layanan laundry.</p></div>
            <a href="?table=layanan&hal=tambah" class="bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold py-2 px-4 rounded-lg transition duration-200 shadow-md flex items-center gap-2"><i class="fas fa-plus"></i> Tambah Baru</a>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-white text-gray-600 text-xs uppercase tracking-wider border-b border-gray-100">
                        <th class="px-6 py-4 font-semibold">ID</th><th class="px-6 py-4 font-semibold">Nama Layanan</th><th class="px-6 py-4 font-semibold">Harga</th><th class="px-6 py-4 font-semibold">Satuan</th><th class="px-6 py-4 font-semibold">Estimasi</th><th class="px-6 py-4 font-semibold text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    <?php
                    $data = mysqli_query($koneksi, 'SELECT * FROM layanan ORDER BY id_layanan ASC');
                    if(mysqli_num_rows($data) > 0) {
                        while ($d = mysqli_fetch_array($data)) {
                    ?>
                    <tr class="hover:bg-indigo-50 transition duration-150">
                        <td class="px-6 py-4 font-medium text-gray-900"><?= $d['id_layanan'] ?></td>
                        <td class="px-6 py-4"><?= $d['nama_layanan'] ?></td>
                        <td class="px-6 py-4 text-green-600 font-bold">Rp <?= number_format($d['harga'], 0, ',', '.') ?></td>
                        <td class="px-6 py-4"><span class="bg-gray-100 text-gray-600 py-1 px-3 rounded-full text-xs font-bold"><?= $d['satuan'] ?></span></td>
                        <td class="px-6 py-4 text-gray-500"><?= $d['estimasi_waktu'] ?></td>
                        <td class="px-6 py-4 text-center">
                            <div class="flex justify-center gap-2">
                                <a href="?table=layanan&hal=edit&id=<?= $d['id_layanan'] ?>" class="text-blue-500 hover:text-blue-700 bg-blue-50 hover:bg-blue-100 p-2 rounded-lg transition"><i class="fas fa-edit"></i></a>
                                <a href="?table=layanan&aksi=hapus&id=<?= $d['id_layanan'] ?>" onclick="return confirm('Hapus layanan <?= $d['nama_layanan'] ?>?')" class="text-red-500 hover:text-red-700 bg-red-50 hover:bg-red-100 p-2 rounded-lg transition"><i class="fas fa-trash-alt"></i></a>
                            </div>
                        </td>
                    </tr>
                    <?php }} else { echo "<tr><td colspan='6' class='p-8 text-center text-gray-400'>Belum ada data layanan.</td></tr>"; } ?>
                </tbody>
            </table>
        </div>
    </div>
<?php endif; ?>