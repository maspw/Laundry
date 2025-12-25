<?php
// --- FILE: modul_pelanggan.php ---
// Logic: CRUD Master Data Pelanggan (Auto ID)

// 1. LOGIC HAPUS
if (isset($_GET['aksi']) && $_GET['aksi'] == 'hapus') {
    $id = $_GET['id'];
    $hapus = mysqli_query($koneksi, "DELETE FROM pelanggan WHERE id_pelanggan='$id'");

    if ($hapus) {
        echo "<script>alert('✅ Data pelanggan berhasil dihapus!'); window.location='?table=pelanggan';</script>";
    } else {
        echo "<script>alert('❌ Gagal hapus! Data ini lagi dipake di transaksi order.'); window.location='?table=pelanggan';</script>";
    }
}

// 2. LOGIC SIMPAN (INSERT/UPDATE)
if (isset($_POST['simpan_pelanggan'])) {
    $id   = htmlspecialchars($_POST['id_pelanggan']);
    $nama = htmlspecialchars($_POST['nama']);
    $almt = htmlspecialchars($_POST['alamat']);
    $telp = htmlspecialchars($_POST['no_telp']);
    $is_edit_mode = $_POST['is_edit_mode'];

    if ($is_edit_mode == '1') {
        $sql = "UPDATE pelanggan SET nama='$nama', alamat='$almt', no_telp='$telp' WHERE id_pelanggan='$id'";
        $msg = "Data pelanggan berhasil diupdate! ✏️";
    } else {
        // Cek ID Duplicate
        $cek = mysqli_query($koneksi, "SELECT * FROM pelanggan WHERE id_pelanggan='$id'");
        if (mysqli_num_rows($cek) > 0) {
            echo "<script>alert('❌ ID Pelanggan sudah ada!');</script>";
            $sql = "";
        } else {
            $sql = "INSERT INTO pelanggan VALUES('$id', '$nama', '$almt', '$telp')";
            $msg = "Pelanggan baru berhasil join! 🤝";
        }
    }

    if ($sql != "") {
        if (mysqli_query($koneksi, $sql)) {
            echo "<script>alert('$msg'); window.location='?table=pelanggan';</script>";
        } else {
            echo "<script>alert('Error Database: " . mysqli_error($koneksi) . "');</script>";
        }
    }
}

// 3. PREPARE VIEW DATA & AUTO ID
$halaman = isset($_GET['hal']) ? $_GET['hal'] : 'data';
$id_plg = ""; $nm_plg = ""; $almt_plg = ""; $telp_plg = ""; $is_edit = false;

if ($halaman == 'edit' && isset($_GET['id'])) {
    $id_target = $_GET['id'];
    $q_edit = mysqli_query($koneksi, "SELECT * FROM pelanggan WHERE id_pelanggan='$id_target'");
    if ($d = mysqli_fetch_assoc($q_edit)) {
        $id_plg = $d['id_pelanggan']; $nm_plg = $d['nama']; $almt_plg = $d['alamat'];
        $telp_plg = $d['no_telp']; $is_edit = true;
    }
} elseif ($halaman == 'tambah') {
    // GENERATE ID OTOMATIS (Prefix 'PLG')
    $id_plg = buatIdOtomatis($koneksi, "pelanggan", "id_pelanggan", "PLG");
}

// --- VIEW FORM ---
if ($halaman == 'tambah' || $halaman == 'edit'): 
?>
    <div class="max-w-lg mx-auto bg-white p-8 rounded-xl shadow-lg border border-gray-100 mt-4 relative overflow-hidden">
        <div class="absolute top-0 left-0 w-full h-1 bg-gradient-to-r from-purple-400 to-pink-500"></div>
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-2xl font-bold text-gray-800">
                <?php echo $is_edit ? '<span class="text-yellow-600">✏️ Edit Pelanggan</span>' : '<span class="text-purple-600">👤 Tambah Pelanggan</span>'; ?>
            </h2>
            <a href="?table=pelanggan" class="text-gray-400 hover:text-red-500 transition"><i class="fas fa-times text-xl"></i></a>
        </div>

        <form method="POST" action="" class="space-y-4">
            <input type="hidden" name="is_edit_mode" value="<?= $is_edit ? '1' : '0' ?>">
            
            <div>
                <label class="block text-gray-700 text-sm font-bold mb-1">ID Pelanggan (Auto)</label>
                <input type="text" name="id_pelanggan" value="<?= $id_plg ?>" 
                    class="w-full px-4 py-2 border rounded-lg bg-gray-100 text-gray-500 font-mono font-bold cursor-not-allowed focus:outline-none" 
                    readonly>
            </div>

            <div>
                <label class="block text-gray-700 text-sm font-bold mb-1">Nama Lengkap</label>
                <input type="text" name="nama" value="<?= $nm_plg ?>" class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500" placeholder="Nama Pelanggan" required>
            </div>

            <div>
                <label class="block text-gray-700 text-sm font-bold mb-1">No. Telepon</label>
                <input type="text" name="no_telp" value="<?= $telp_plg ?>" class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500" placeholder="08xxx">
            </div>

            <div>
                <label class="block text-gray-700 text-sm font-bold mb-1">Alamat</label>
                <textarea name="alamat" rows="3" class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500" placeholder="Alamat lengkap..."><?= $almt_plg ?></textarea>
            </div>

            <div class="flex gap-3 pt-2">
                <button type="submit" name="simpan_pelanggan" class="w-full bg-purple-600 hover:bg-purple-700 text-white font-bold py-2 px-4 rounded-lg transition duration-200 shadow-md">
                    <?= $is_edit ? 'Simpan Perubahan' : 'Simpan Data' ?>
                </button>
            </div>
        </form>
    </div>

<?php else: ?>
    <!-- VIEW TABEL -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="p-6 border-b border-gray-100 flex justify-between items-center bg-gray-50">
            <div>
                <h3 class="text-lg font-bold text-purple-600">Database Pelanggan</h3>
                <p class="text-xs text-gray-500">Kelola data pelanggan setia laundry.</p>
            </div>
            <a href="?table=pelanggan&hal=tambah" class="bg-purple-600 hover:bg-purple-700 text-white text-sm font-semibold py-2 px-4 rounded-lg transition duration-200 shadow-md flex items-center gap-2">
                <i class="fas fa-user-plus"></i> Tambah Baru
            </a>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-white text-gray-600 text-xs uppercase tracking-wider border-b border-gray-100">
                        <th class="p-5 font-bold">ID</th>
                        <th class="p-5 font-bold">Nama Pelanggan</th>
                        <th class="p-5 font-bold">Kontak</th>
                        <th class="p-5 font-bold">Alamat</th>
                        <th class="p-5 font-bold text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    <?php
                    $data = mysqli_query($koneksi, 'SELECT * FROM pelanggan ORDER BY id_pelanggan ASC');
                    if(mysqli_num_rows($data) > 0) {
                        while ($d = mysqli_fetch_array($data)) {
                    ?>
                    <tr class="hover:bg-purple-50 transition duration-150 group">
                        <td class="p-5 font-medium text-gray-900"><?= $d['id_pelanggan'] ?></td>
                        <td class="p-5 font-semibold text-gray-700"><?= $d['nama'] ?></td>
                        <td class="p-5 text-gray-600 text-sm">
                            <span class="text-purple-600 font-medium"><i class="fas fa-phone"></i> <?= $d['no_telp'] ?></span>
                        </td>
                        <td class="p-5 text-gray-600 text-sm truncate max-w-xs"><?= $d['alamat'] ?></td>
                        <td class="p-5 text-center">
                            <div class="flex justify-center gap-3">
                                <a href="?table=pelanggan&hal=edit&id=<?= $d['id_pelanggan'] ?>" class="text-blue-500 hover:text-blue-700 transition transform hover:scale-110"><i class="fas fa-pen-to-square"></i></a>
                                <a href="?table=pelanggan&aksi=hapus&id=<?= $d['id_pelanggan'] ?>" onclick="return confirm('Yakin mau hapus data pelanggan <?= $d['nama'] ?>?')" class="text-red-400 hover:text-red-600 transition transform hover:scale-110"><i class="fas fa-trash-can"></i></a>
                            </div>
                        </td>
                    </tr>
                    <?php }} else { echo "<tr><td colspan='5' class='p-10 text-center text-gray-400 italic'>Belum ada pelanggan.</td></tr>"; } ?>
                </tbody>
            </table>
        </div>
    </div>
<?php endif; ?>