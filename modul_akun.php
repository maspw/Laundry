<?php
// --- FILE: modul_akun.php ---
// Logic: CRUD Master Data Akun (Chart of Accounts)

$halaman = isset($_GET['hal']) ? $_GET['hal'] : 'data';

// --- 1. LOGIC HAPUS ---
if (isset($_GET['aksi']) && $_GET['aksi'] == 'hapus') {
    $id = $_GET['id'];
    $q = "DELETE FROM akun WHERE id_akun='$id'";
    if (mysqli_query($koneksi, $q)) {
        echo "<script>alert('✅ Data akun dihapus!'); window.location='?table=akun';</script>";
    } else {
        echo "<script>alert('❌ Gagal hapus: " . mysqli_error($koneksi) . "'); window.location='?table=akun';</script>";
    }
}

// --- 2. LOGIC SIMPAN (BARU / EDIT) ---
if (isset($_POST['simpan_akun'])) {
    $id     = htmlspecialchars($_POST['id_akun']);
    $nama   = htmlspecialchars($_POST['nama_akun']);
    $header = htmlspecialchars($_POST['header_akun']);
    $is_edit = $_POST['is_edit_mode'];

    if ($is_edit == '1') {
        $q = "UPDATE akun SET nama_akun='$nama', header_akun='$header' WHERE id_akun='$id'";
        $msg = "Data akun berhasil diupdate! ✏️";
    } else {
        // Cek Duplicate
        $cek = mysqli_query($koneksi, "SELECT * FROM akun WHERE id_akun='$id'");
        if (mysqli_num_rows($cek) > 0) {
            echo "<script>alert('❌ ID Akun sudah ada!');</script>";
            $q = ""; 
        } else {
            $q = "INSERT INTO akun VALUES ('$id', '$header', '$nama')";
            $msg = "Akun baru berhasil ditambahkan! 📒";
        }
    }
    
    if ($q != "" && mysqli_query($koneksi, $q)) {
        echo "<script>alert('✅ $msg'); window.location='?table=akun';</script>";
    } elseif ($q != "") {
        echo "<script>alert('❌ Gagal simpan: " . mysqli_error($koneksi) . "');</script>";
    }
}

// --- VIEW FORM (TAMBAH / EDIT) ---
if ($halaman == 'tambah' || $halaman == 'edit'):
    $is_edit = ($halaman == 'edit');
    $id_val = ""; $nama_val = ""; $header_val = "";

    if ($is_edit && isset($_GET['id'])) {
        $id = $_GET['id'];
        $q = mysqli_query($koneksi, "SELECT * FROM akun WHERE id_akun='$id'");
        $data = mysqli_fetch_assoc($q);
        if ($data) {
            $id_val = $data['id_akun'];
            $nama_val = $data['nama_akun'];
            $header_val = $data['header_akun'];
        }
    } elseif (!$is_edit) {
        $id_val = buatIdOtomatis($koneksi, "akun", "id_akun", "AKN");
    }
?>
    <div class="max-w-xl mx-auto bg-white p-8 rounded-xl shadow-lg border border-gray-100 mt-4 relative overflow-hidden">
        <div class="absolute top-0 left-0 w-full h-1 bg-gradient-to-r from-cyan-400 to-blue-500"></div>
        <div class="flex justify-between items-center mb-6 border-b pb-4">
            <h2 class="text-xl font-bold text-gray-800">
                <?= $is_edit ? '<span class="text-yellow-600">✏️ Edit Akun</span>' : '<span class="text-cyan-600">📒 Tambah Akun</span>' ?>
            </h2>
            <a href="?table=akun" class="text-gray-400 hover:text-red-500 transition"><i class="fas fa-times text-xl"></i></a>
        </div>

        <form method="POST" action="" class="space-y-4">
            <input type="hidden" name="is_edit_mode" value="<?= $is_edit ? '1' : '0' ?>">

            <div>
                <label class="block text-sm font-bold text-gray-700 mb-1">ID Akun</label>
                <input type="text" name="id_akun" value="<?= $id_val ?>" readonly class="w-full px-4 py-2 border rounded-lg bg-gray-100 text-gray-500 font-mono font-bold cursor-not-allowed">
            </div>

            <div>
                <label class="block text-sm font-bold text-gray-700 mb-1">Nama Akun</label>
                <input type="text" name="nama_akun" value="<?= $nama_val ?>" placeholder="Cth: Kas Besar, Utang Usaha" required class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-cyan-500">
            </div>

            <div>
                <label class="block text-sm font-bold text-gray-700 mb-1">Header Akun (Kategori)</label>
                <div class="relative">
                    <select name="header_akun" class="w-full px-4 py-2 border rounded-lg bg-white focus:ring-2 focus:ring-cyan-500 appearance-none">
                        <option value="">-- Pilih Header --</option>
                        <?php 
                        $headers = ['1-Aset', '2-Kewajiban', '3-Ekuitas', '4-Pendapatan', '5-Beban'];
                        foreach($headers as $h):
                            $selected = ($header_val == $h) ? 'selected' : '';
                        ?>
                            <option value="<?= $h ?>" <?= $selected ?>><?= $h ?></option>
                        <?php endforeach; ?>
                    </select>
                    <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-gray-700">
                        <i class="fas fa-chevron-down text-xs"></i>
                    </div>
                </div>
            </div>

            <div class="pt-4 flex gap-3">
                <button type="submit" name="simpan_akun" class="flex-1 bg-cyan-600 hover:bg-cyan-700 text-white font-bold py-2 px-4 rounded-lg shadow transition">
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
                <h3 class="text-lg font-bold text-cyan-600">Daftar Akun (COA)</h3>
                <p class="text-xs text-gray-500">Chart of Accounts sistem akuntansi.</p>
            </div>
            <a href="?table=akun&hal=tambah" class="bg-cyan-600 hover:bg-cyan-700 text-white text-sm font-semibold py-2 px-4 rounded-lg shadow flex items-center gap-2 transition">
                <i class="fas fa-plus"></i> Tambah Baru
            </a>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-white text-gray-600 text-xs uppercase tracking-wider border-b border-gray-100">
                        <th class="px-6 py-4 font-semibold text-center">ID</th>
                        <th class="px-6 py-4 font-semibold">Nama Akun</th>
                        <th class="px-6 py-4 font-semibold">Header / Kategori</th>
                        <th class="px-6 py-4 font-semibold text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    <?php
                    $q = "SELECT * FROM akun ORDER BY header_akun ASC, id_akun ASC";
                    $sql = mysqli_query($koneksi, $q);
                    
                    if(mysqli_num_rows($sql) > 0) {
                        while($row = mysqli_fetch_assoc($sql)) {
                            // Warna badge beda-beda tiap header
                            $badgeColor = 'bg-gray-100 text-gray-600';
                            if(strpos($row['header_akun'], '1') === 0) $badgeColor = 'bg-green-100 text-green-700'; // Aset
                            if(strpos($row['header_akun'], '2') === 0) $badgeColor = 'bg-red-100 text-red-700'; // Kewajiban
                            if(strpos($row['header_akun'], '4') === 0) $badgeColor = 'bg-blue-100 text-blue-700'; // Pendapatan
                    ?>
                    <tr class="hover:bg-cyan-50 transition duration-150">
                        <td class="px-6 py-4 text-center font-mono font-bold text-gray-700 text-sm"><?= $row['id_akun'] ?></td>
                        <td class="px-6 py-4 font-medium text-gray-800"><?= $row['nama_akun'] ?></td>
                        <td class="px-6 py-4 text-sm">
                            <span class="<?= $badgeColor ?> px-2 py-1 rounded text-xs font-bold border border-gray-200">
                                <?= $row['header_akun'] ?>
                            </span>
                        </td>
                        <td class="px-6 py-4 text-center">
                            <div class="flex justify-center gap-2">
                                <a href="?table=akun&hal=edit&id=<?= $row['id_akun'] ?>" class="text-yellow-500 hover:text-yellow-600 transition" title="Edit"><i class="fas fa-pen-to-square"></i></a>
                                <a href="?table=akun&aksi=hapus&id=<?= $row['id_akun'] ?>" onclick="return confirm('Hapus akun <?= $row['nama_akun'] ?>?')" class="text-red-400 hover:text-red-600 transition" title="Hapus"><i class="fas fa-trash-can"></i></a>
                            </div>
                        </td>
                    </tr>
                    <?php 
                        }
                    } else {
                        echo "<tr><td colspan='4' class='p-8 text-center text-gray-400 italic'>Belum ada data akun.</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>
<?php endif; ?>