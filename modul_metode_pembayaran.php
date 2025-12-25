<?php
// --- FILE: modul_metode_pembayaran.php ---
// Logic: CRUD Master Data Metode Pembayaran

$halaman = isset($_GET['hal']) ? $_GET['hal'] : 'data';

// --- 1. LOGIC HAPUS ---
if (isset($_GET['aksi']) && $_GET['aksi'] == 'hapus') {
    $id = $_GET['id'];
    $q = "DELETE FROM metode_pembayaran WHERE id_metode='$id'";
    if (mysqli_query($koneksi, $q)) {
        echo "<script>alert('✅ Metode pembayaran dihapus!'); window.location='?table=metode_pembayaran';</script>";
    } else {
        echo "<script>alert('❌ Gagal hapus! Data mungkin sedang dipakai di transaksi.'); window.location='?table=metode_pembayaran';</script>";
    }
}

// --- 2. LOGIC SIMPAN (BARU / EDIT) ---
if (isset($_POST['simpan_metode'])) {
    $id   = htmlspecialchars($_POST['id_metode']);
    $nama = htmlspecialchars($_POST['nama_metode']);
    $norek = htmlspecialchars($_POST['no_rek']);
    $an    = htmlspecialchars($_POST['atas_nama']);
    $is_edit = $_POST['is_edit_mode'];

    if ($is_edit == '1') {
        $q = "UPDATE metode_pembayaran SET nama_metode='$nama', no_rek='$norek', atas_nama='$an' WHERE id_metode='$id'";
        $msg = "Data berhasil diupdate! ✏️";
    } else {
        // Cek Duplicate ID
        $cek = mysqli_query($koneksi, "SELECT * FROM metode_pembayaran WHERE id_metode='$id'");
        if (mysqli_num_rows($cek) > 0) {
            echo "<script>alert('❌ ID Metode sudah ada!');</script>";
            $q = ""; // Cancel insert
        } else {
            $q = "INSERT INTO metode_pembayaran VALUES ('$id', '$nama', '$norek', '$an')";
            $msg = "Metode baru ditambahkan! 💳";
        }
    }
    
    if ($q != "" && mysqli_query($koneksi, $q)) {
        echo "<script>alert('✅ $msg'); window.location='?table=metode_pembayaran';</script>";
    } elseif ($q != "") {
        echo "<script>alert('❌ Gagal simpan: " . mysqli_error($koneksi) . "');</script>";
    }
}

// --- VIEW FORM (TAMBAH / EDIT) ---
if ($halaman == 'tambah' || $halaman == 'edit'):
    $is_edit = ($halaman == 'edit');
    $id_val = ""; $nama_val = ""; $norek_val = ""; $an_val = "";

    if ($is_edit && isset($_GET['id'])) {
        $id = $_GET['id'];
        $q = mysqli_query($koneksi, "SELECT * FROM metode_pembayaran WHERE id_metode='$id'");
        $data = mysqli_fetch_assoc($q);
        if ($data) {
            $id_val = $data['id_metode'];
            $nama_val = $data['nama_metode'];
            $norek_val = $data['no_rek'];
            $an_val = $data['atas_nama'];
        }
    } elseif (!$is_edit) {
        $id_val = buatIdOtomatis($koneksi, "metode_pembayaran", "id_metode", "MET");
    }
?>
    <div class="max-w-xl mx-auto bg-white p-8 rounded-xl shadow-lg border border-gray-100 mt-4 relative overflow-hidden">
        <div class="absolute top-0 left-0 w-full h-1 bg-gradient-to-r from-blue-400 to-cyan-500"></div>
        <div class="flex justify-between items-center mb-6 border-b pb-4">
            <h2 class="text-xl font-bold text-gray-800">
                <?= $is_edit ? '<span class="text-yellow-600">✏️ Edit Metode</span>' : '<span class="text-blue-600">💳 Tambah Metode</span>' ?>
            </h2>
            <a href="?table=metode_pembayaran" class="text-gray-400 hover:text-red-500 transition"><i class="fas fa-times text-xl"></i></a>
        </div>

        <form method="POST" action="" class="space-y-4">
            <input type="hidden" name="is_edit_mode" value="<?= $is_edit ? '1' : '0' ?>">

            <div>
                <label class="block text-sm font-bold text-gray-700 mb-1">ID Metode</label>
                <input type="text" name="id_metode" value="<?= $id_val ?>" readonly class="w-full px-4 py-2 border rounded-lg bg-gray-100 text-gray-500 font-mono font-bold cursor-not-allowed">
            </div>

            <div>
                <label class="block text-sm font-bold text-gray-700 mb-1">Nama Metode</label>
                <input type="text" name="nama_metode" value="<?= $nama_val ?>" placeholder="Cth: Transfer BCA, QRIS, Tunai" required class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500">
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-1">No. Rekening / ID</label>
                    <input type="text" name="no_rek" value="<?= $norek_val ?>" placeholder="-" required class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500">
                    <p class="text-xs text-gray-400 mt-1">*Isi '-' jika Tunai</p>
                </div>
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-1">Atas Nama</label>
                    <input type="text" name="atas_nama" value="<?= $an_val ?>" placeholder="-" required class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500">
                </div>
            </div>

            <div class="pt-4 flex gap-3">
                <button type="submit" name="simpan_metode" class="flex-1 bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-lg shadow transition">
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
                <h3 class="text-lg font-bold text-blue-600">Metode Pembayaran</h3>
                <p class="text-xs text-gray-500">Daftar opsi pembayaran yang tersedia.</p>
            </div>
            <a href="?table=metode_pembayaran&hal=tambah" class="bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold py-2 px-4 rounded-lg shadow flex items-center gap-2 transition">
                <i class="fas fa-plus"></i> Tambah Baru
            </a>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-white text-gray-600 text-xs uppercase tracking-wider border-b border-gray-100">
                        <th class="px-6 py-4 font-semibold text-center">ID</th>
                        <th class="px-6 py-4 font-semibold">Nama Metode</th>
                        <th class="px-6 py-4 font-semibold">No. Rekening</th>
                        <th class="px-6 py-4 font-semibold">Atas Nama</th>
                        <th class="px-6 py-4 font-semibold text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    <?php
                    $q = "SELECT * FROM metode_pembayaran ORDER BY id_metode ASC";
                    $sql = mysqli_query($koneksi, $q);
                    
                    if(mysqli_num_rows($sql) > 0) {
                        while($row = mysqli_fetch_assoc($sql)) {
                    ?>
                    <tr class="hover:bg-blue-50 transition duration-150">
                        <td class="px-6 py-4 text-center font-mono font-bold text-gray-700 text-sm"><?= $row['id_metode'] ?></td>
                        <td class="px-6 py-4 font-medium text-gray-800">
                            <i class="fas fa-wallet text-gray-400 mr-2"></i> <?= $row['nama_metode'] ?>
                        </td>
                        <td class="px-6 py-4 text-sm font-mono text-gray-600"><?= $row['no_rek'] ?></td>
                        <td class="px-6 py-4 text-sm text-gray-600"><?= $row['atas_nama'] ?></td>
                        <td class="px-6 py-4 text-center">
                            <div class="flex justify-center gap-2">
                                <a href="?table=metode_pembayaran&hal=edit&id=<?= $row['id_metode'] ?>" class="text-yellow-500 hover:text-yellow-600 transition" title="Edit"><i class="fas fa-pen-to-square"></i></a>
                                <a href="?table=metode_pembayaran&aksi=hapus&id=<?= $row['id_metode'] ?>" onclick="return confirm('Hapus metode ini?')" class="text-red-400 hover:text-red-600 transition" title="Hapus"><i class="fas fa-trash-can"></i></a>
                            </div>
                        </td>
                    </tr>
                    <?php 
                        }
                    } else {
                        echo "<tr><td colspan='5' class='p-8 text-center text-gray-400 italic'>Belum ada metode pembayaran.</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>
<?php endif; ?>