<?php
// --- FILE: modul_karyawan.php ---
// Logic: CRUD Master Data Karyawan (Auto ID)

// 1. LOGIC HAPUS
if (isset($_GET['aksi']) && $_GET['aksi'] == 'hapus') {
    $id = $_GET['id'];
    $hapus = mysqli_query($koneksi, "DELETE FROM karyawan WHERE id_karyawan='$id'");

    if ($hapus) {
        echo "<script>alert('✅ Data karyawan berhasil dihapus!'); window.location='?table=karyawan';</script>";
    } else {
        echo "<script>alert('❌ Gagal hapus! Mungkin data masih nyangkut di transaksi.'); window.location='?table=karyawan';</script>";
    }
}

// 2. LOGIC SIMPAN (INSERT/UPDATE)
if (isset($_POST['simpan_karyawan'])) {
    // FIX: Ambil ID dari POST (sekarang udah auto di form)
    $id   = htmlspecialchars($_POST['id_karyawan']);
    $nama = htmlspecialchars($_POST['nama_karyawan']);
    $almt = htmlspecialchars($_POST['alamat']);
    $telp = htmlspecialchars($_POST['no_telp']);
    $jbt  = htmlspecialchars($_POST['jabatan']);
    $is_edit_mode = $_POST['is_edit_mode'];

    if ($is_edit_mode == '1') {
        $sql = "UPDATE karyawan SET nama_karyawan='$nama', alamat='$almt', no_telp='$telp', jabatan='$jbt' WHERE id_karyawan='$id'";
        $msg = "Data karyawan berhasil diupdate! ✏️";
    } else {
        // Cek ID Duplicate (Jaga-jaga)
        $cek = mysqli_query($koneksi, "SELECT * FROM karyawan WHERE id_karyawan='$id'");
        if (mysqli_num_rows($cek) > 0) {
            echo "<script>alert('❌ ID Karyawan sudah ada!');</script>";
            $sql = "";
        } else {
            $sql = "INSERT INTO karyawan VALUES('$id', '$nama', '$almt', '$telp', '$jbt')";
            $msg = "Karyawan baru berhasil direkrut! 🤝";
        }
    }

    if ($sql != "") {
        if (mysqli_query($koneksi, $sql)) {
            echo "<script>alert('$msg'); window.location='?table=karyawan';</script>";
        } else {
            echo "<script>alert('Waduh gagal: " . mysqli_error($koneksi) . "');</script>";
        }
    }
}

// 3. PREPARE VIEW DATA & AUTO ID
$halaman = isset($_GET['hal']) ? $_GET['hal'] : 'data';
$id_kry = ""; $nm_kry = ""; $almt_kry = ""; $telp_kry = ""; $jbt_kry = ""; $is_edit = false;

if ($halaman == 'edit' && isset($_GET['id'])) {
    $id_target = $_GET['id'];
    $q_edit = mysqli_query($koneksi, "SELECT * FROM karyawan WHERE id_karyawan='$id_target'");
    if ($d = mysqli_fetch_assoc($q_edit)) {
        $id_kry = $d['id_karyawan']; $nm_kry = $d['nama_karyawan']; $almt_kry = $d['alamat'];
        $telp_kry = $d['no_telp']; $jbt_kry = $d['jabatan']; $is_edit = true;
    }
} elseif ($halaman == 'tambah') {
    // GENERATE ID OTOMATIS DI SINI (Prefix 'KRY')
    // Pastikan fungsi buatIdOtomatis() ada di koneksi.php
    $id_kry = buatIdOtomatis($koneksi, "karyawan", "id_karyawan", "KRY");
}

// --- VIEW FORM ---
if ($halaman == 'tambah' || $halaman == 'edit'): 
?>
    <div class="max-w-lg mx-auto bg-white p-8 rounded-xl shadow-lg border border-gray-100 mt-4 relative overflow-hidden">
        <div class="absolute top-0 left-0 w-full h-1 bg-gradient-to-r from-teal-400 to-blue-500"></div>
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-2xl font-bold text-gray-800">
                <?php echo $is_edit ? '<span class="text-yellow-600">✏️ Edit Karyawan</span>' : '<span class="text-teal-600">👤 Tambah Karyawan</span>'; ?>
            </h2>
            <a href="?table=karyawan" class="text-gray-400 hover:text-red-500 transition"><i class="fas fa-times text-xl"></i></a>
        </div>

        <form method="POST" action="" class="space-y-4">
            <input type="hidden" name="is_edit_mode" value="<?= $is_edit ? '1' : '0' ?>">
            
            <div>
                <label class="block text-gray-700 text-sm font-bold mb-1">ID Karyawan </label>
                <!-- Input Readonly & Auto Filled -->
                <input type="text" name="id_karyawan" value="<?= $id_kry ?>" 
                    class="w-full px-4 py-2 border rounded-lg bg-gray-100 text-gray-500 font-mono font-bold cursor-not-allowed focus:outline-none" 
                    readonly>
            </div>

            <div>
                <label class="block text-gray-700 text-sm font-bold mb-1">Nama Lengkap</label>
                <input type="text" name="nama_karyawan" value="<?= $nm_kry ?>" class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500" placeholder="Nama Karyawan" required>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-gray-700 text-sm font-bold mb-1">No. Telp</label>
                    <input type="text" name="no_telp" value="<?= $telp_kry ?>" class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500" placeholder="08xxx">
                </div>
                <div>
                    <label class="block text-gray-700 text-sm font-bold mb-1">Jabatan</label>
                    <select name="jabatan" class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 bg-white">
                        <option value="Kasir" <?= ($jbt_kry == 'Kasir') ? 'selected' : '' ?>>Kasir</option>
                        <option value="Admin" <?= ($jbt_kry == 'Admin') ? 'selected' : '' ?>>Admin</option>
                        <option value="Operator" <?= ($jbt_kry == 'Operator') ? 'selected' : '' ?>>Operator</option>
                        <option value="Kurir" <?= ($jbt_kry == 'Kurir') ? 'selected' : '' ?>>Kurir</option>
                    </select>
                </div>
            </div>

            <div>
                <label class="block text-gray-700 text-sm font-bold mb-1">Alamat</label>
                <textarea name="alamat" rows="2" class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500"><?= $almt_kry ?></textarea>
            </div>

            <div class="flex gap-3 pt-2">
                <button type="submit" name="simpan_karyawan" class="w-full bg-teal-500 hover:bg-teal-600 text-white font-bold py-2 px-4 rounded-lg transition duration-200 shadow-md">
                    <?= $is_edit ? 'Simpan Perubahan' : 'Rekrut Karyawan' ?>
                </button>
            </div>
        </form>
    </div>

<?php else: ?>
    <!-- VIEW TABEL -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="p-6 border-b border-gray-100 flex justify-between items-center bg-gray-50">
            <div>
                <h3 class="text-lg font-bold text-teal-600">Tim Karyawan</h3>
                <p class="text-xs text-gray-500">Manajemen data pegawai laundry.</p>
            </div>
            <a href="?table=karyawan&hal=tambah" class="bg-teal-500 hover:bg-teal-600 text-white text-sm font-semibold py-2 px-4 rounded-lg transition duration-200 shadow-md flex items-center gap-2">
                <i class="fas fa-user-plus"></i> Tambah Baru
            </a>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-white text-gray-600 text-xs uppercase tracking-wider border-b border-gray-100">
                        <th class="p-5 font-bold">ID</th>
                        <th class="p-5 font-bold">Nama Karyawan</th>
                        <th class="p-5 font-bold">Kontak & Alamat</th>
                        <th class="p-5 font-bold">Jabatan</th>
                        <th class="p-5 font-bold text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    <?php
                    $data = mysqli_query($koneksi, 'SELECT * FROM karyawan ORDER BY id_karyawan ASC');
                    if(mysqli_num_rows($data) > 0) {
                        while ($d = mysqli_fetch_array($data)) {
                    ?>
                    <tr class="hover:bg-teal-50 transition duration-150 group">
                        <td class="p-5 font-medium text-gray-900"><?= $d['id_karyawan'] ?></td>
                        <td class="p-5 font-semibold text-gray-700"><?= $d['nama_karyawan'] ?></td>
                        <td class="p-5 text-gray-600 text-sm">
                            <div class="flex flex-col">
                                <span class="text-xs text-teal-500 mb-1"><i class="fas fa-phone"></i> <?= $d['no_telp'] ?></span>
                                <span><?= $d['alamat'] ?></span>
                            </div>
                        </td>
                        <td class="p-5">
                            <span class="px-3 py-1 rounded-full text-xs font-bold bg-indigo-100 text-indigo-600"><?= $d['jabatan'] ?></span>
                        </td>
                        <td class="p-5 text-center">
                            <div class="flex justify-center gap-3">
                                <a href="?table=karyawan&hal=edit&id=<?= $d['id_karyawan'] ?>" class="text-blue-500 hover:text-blue-700 transition transform hover:scale-110"><i class="fas fa-pen-to-square"></i></a>
                                <a href="?table=karyawan&aksi=hapus&id=<?= $d['id_karyawan'] ?>" onclick="return confirm('Yakin mau pecat <?= $d['nama_karyawan'] ?>?')" class="text-red-400 hover:text-red-600 transition transform hover:scale-110"><i class="fas fa-trash-can"></i></a>
                            </div>
                        </td>
                    </tr>
                    <?php }} else { echo "<tr><td colspan='5' class='p-10 text-center text-gray-400 italic'>Belum ada karyawan.</td></tr>"; } ?>
                </tbody>
            </table>
        </div>
    </div>
<?php endif; ?>