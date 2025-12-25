<?php
// --- FILE: modul_pengambilan.php ---
// Logic: Transaksi Pengambilan Laundry (Delivery/Pickup)

// Pastikan koneksi sudah di-include dari dashboard
// include 'koneksi.php';

$halaman = isset($_GET['hal']) ? $_GET['hal'] : 'data';

// --- 1. LOGIC HAPUS ---
if (isset($_GET['aksi']) && $_GET['aksi'] == 'hapus') {
    $id = $_GET['id'];
    $q = "DELETE FROM pengambilan_laundry WHERE id_pengambilan='$id'";
    if (mysqli_query($koneksi, $q)) {
        echo "<script>alert('✅ Data pengambilan dihapus!'); window.location='?table=pengambilan_laundry';</script>";
    } else {
        echo "<script>alert('❌ Gagal hapus: " . mysqli_error($koneksi) . "'); window.location='?table=pengambilan_laundry';</script>";
    }
}

// --- 2. LOGIC SIMPAN (BARU) ---
if (isset($_POST['simpan_pengambilan'])) {
    $id   = $_POST['id_pengambilan'];
    $tgl  = $_POST['tgl_ambil'];
    $nama = $_POST['nama_pengambil'];
    $stat = $_POST['status_pengambilan'];
    $bayar= $_POST['id_pembayaran'];

    // Cek apakah pembayaran ini sudah pernah diambil sebelumnya (Opsional)
    // $cek = mysqli_query($koneksi, "SELECT id_pengambilan FROM pengambilan_laundry WHERE id_pembayaran='$bayar'");
    // if(mysqli_num_rows($cek) > 0) { ... }

    $q = "INSERT INTO pengambilan_laundry (id_pengambilan, tgl_ambil, nama_pengambil, status_pengambilan, id_pembayaran) 
          VALUES ('$id', '$tgl', '$nama', '$stat', '$bayar')";
    
    if (mysqli_query($koneksi, $q)) {
        echo "<script>alert('✅ Data pengambilan disimpan!'); window.location='?table=pengambilan_laundry';</script>";
    } else {
        echo "<script>alert('❌ Gagal simpan: " . mysqli_error($koneksi) . "');</script>";
    }
}

// --- 3. LOGIC UPDATE ---
if (isset($_POST['update_pengambilan'])) {
    $id   = $_POST['id_pengambilan'];
    $tgl  = $_POST['tgl_ambil'];
    $nama = $_POST['nama_pengambil'];
    $stat = $_POST['status_pengambilan'];
    $bayar= $_POST['id_pembayaran'];

    $q = "UPDATE pengambilan_laundry SET tgl_ambil='$tgl', nama_pengambil='$nama', status_pengambilan='$stat', id_pembayaran='$bayar' WHERE id_pengambilan='$id'";
    
    if (mysqli_query($koneksi, $q)) {
        echo "<script>alert('✅ Data berhasil diupdate!'); window.location='?table=pengambilan_laundry';</script>";
    } else {
        echo "<script>alert('❌ Gagal update: " . mysqli_error($koneksi) . "');</script>";
    }
}

// --- VIEW FORM (TAMBAH / EDIT) ---
if ($halaman == 'tambah' || $halaman == 'edit'):
    $is_edit = ($halaman == 'edit');
    $id_val = ""; $tgl_val = date('Y-m-d'); $nama_val = ""; $stat_val = ""; $bayar_val = "";

    if ($is_edit && isset($_GET['id'])) {
        $id = $_GET['id'];
        $q = mysqli_query($koneksi, "SELECT * FROM pengambilan_laundry WHERE id_pengambilan='$id'");
        $data = mysqli_fetch_assoc($q);
        if ($data) {
            $id_val = $data['id_pengambilan'];
            $tgl_val = $data['tgl_ambil'];
            $nama_val = $data['nama_pengambil'];
            $stat_val = $data['status_pengambilan'];
            $bayar_val = $data['id_pembayaran'];
        }
    } elseif (!$is_edit) {
        $id_val = buatIdOtomatis($koneksi, "pengambilan_laundry", "id_pengambilan", "PGL");
    }
?>
    <div class="max-w-xl mx-auto bg-white p-8 rounded-xl shadow-lg border border-gray-100 mt-4 relative overflow-hidden">
        <div class="absolute top-0 left-0 w-full h-1 bg-gradient-to-r from-purple-500 to-indigo-500"></div>
        <div class="flex justify-between items-center mb-6 border-b pb-4">
            <h2 class="text-xl font-bold text-gray-800">
                <?= $is_edit ? '<span class="text-yellow-600">✏️ Edit Pengambilan</span>' : '<span class="text-purple-600">📦 Input Pengambilan</span>' ?>
            </h2>
            <a href="?table=pengambilan_laundry" class="text-gray-400 hover:text-red-500 transition"><i class="fas fa-times text-xl"></i></a>
        </div>

        <form method="POST" action="" class="space-y-4">
            <div>
                <label class="block text-sm font-bold text-gray-700 mb-1">ID Pengambilan</label>
                <input type="text" name="id_pengambilan" value="<?= $id_val ?>" readonly class="w-full px-4 py-2 border rounded-lg bg-gray-100 text-gray-500 font-mono font-bold cursor-not-allowed">
            </div>

            <div>
                <label class="block text-sm font-bold text-gray-700 mb-1">Tanggal Ambil</label>
                <input type="date" name="tgl_ambil" value="<?= $tgl_val ?>" required class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-purple-500">
            </div>

            <div>
                <label class="block text-sm font-bold text-gray-700 mb-1">Nama Pengambil</label>
                <input type="text" name="nama_pengambil" value="<?= $nama_val ?>" placeholder="Nama kostumer/kurir" required class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-purple-500">
            </div>

            <div>
                <label class="block text-sm font-bold text-gray-700 mb-1">ID Pembayaran (Lunas)</label>
                <select name="id_pembayaran" required class="w-full px-4 py-2 border rounded-lg bg-white focus:ring-2 focus:ring-purple-500">
                    <option value="">-- Pilih ID Pembayaran --</option>
                    <?php 
                    // Ambil data pembayaran yang SUDAH LUNAS untuk ditampilkan
                    $q_bayar = mysqli_query($koneksi, "SELECT id_pembayaran, jml_bayar FROM pembayaran WHERE status='Lunas'"); 
                    while($r=mysqli_fetch_assoc($q_bayar)){ 
                        $selected = ($r['id_pembayaran'] == $bayar_val) ? 'selected' : '';
                        echo "<option value='{$r['id_pembayaran']}' $selected>{$r['id_pembayaran']} - Rp ".number_format($r['jml_bayar'])."</option>"; 
                    } 
                    ?>
                </select>
            </div>

            <div>
                <label class="block text-sm font-bold text-gray-700 mb-1">Status</label>
                <select name="status_pengambilan" required class="w-full px-4 py-2 border rounded-lg bg-white focus:ring-2 focus:ring-purple-500">
                    <option value="Sudah Diambil" <?= ($stat_val == 'Sudah Diambil') ? 'selected' : '' ?>>Sudah Diambil</option>
                    <option value="Belum Diambil" <?= ($stat_val == 'Belum Diambil') ? 'selected' : '' ?>>Belum Diambil</option>
                </select>
            </div>

            <div class="pt-4 flex gap-3">
                <button type="submit" name="<?= $is_edit ? 'update_pengambilan' : 'simpan_pengambilan' ?>" class="flex-1 bg-purple-600 hover:bg-purple-700 text-white font-bold py-2 px-4 rounded-lg shadow transition">
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
                <h3 class="text-lg font-bold text-purple-600">Data Pengambilan Laundry</h3>
                <p class="text-xs text-gray-500">Log pengambilan cucian oleh pelanggan.</p>
            </div>
            <a href="?table=pengambilan_laundry&hal=tambah" class="bg-purple-600 hover:bg-purple-700 text-white text-sm font-semibold py-2 px-4 rounded-lg shadow flex items-center gap-2 transition">
                <i class="fas fa-plus"></i> Input Baru
            </a>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-white text-gray-600 text-xs uppercase tracking-wider border-b border-gray-100">
                        <th class="px-6 py-4 font-semibold text-center">ID</th>
                        <th class="px-6 py-4 font-semibold">Tanggal</th>
                        <th class="px-6 py-4 font-semibold">Nama Pengambil</th>
                        <th class="px-6 py-4 font-semibold">Status</th>
                        <th class="px-6 py-4 font-semibold">Info Tagihan</th>
                        <th class="px-6 py-4 font-semibold text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    <?php
                    // Query JOIN 3 Tabel: Pengambilan -> Pembayaran -> Order
                    $q = "SELECT pgl.*, p.jml_bayar, o.id_order 
                          FROM pengambilan_laundry pgl
                          LEFT JOIN pembayaran p ON pgl.id_pembayaran = p.id_pembayaran
                          LEFT JOIN orders o ON p.id_order = o.id_order
                          ORDER BY pgl.id_pengambilan DESC";
                    $sql = mysqli_query($koneksi, $q);
                    
                    if(mysqli_num_rows($sql) > 0) {
                        while($row = mysqli_fetch_assoc($sql)) {
                            $badgeColor = ($row['status_pengambilan'] == 'Sudah Diambil') ? 'bg-green-100 text-green-700' : 'bg-yellow-100 text-yellow-700';
                    ?>
                    <tr class="hover:bg-purple-50 transition duration-150">
                        <td class="px-6 py-4 text-center font-mono font-bold text-gray-700 text-sm"><?= $row['id_pengambilan'] ?></td>
                        <td class="px-6 py-4 text-sm"><?= date('d/m/Y', strtotime($row['tgl_ambil'])) ?></td>
                        <td class="px-6 py-4 font-medium text-gray-800"><?= $row['nama_pengambil'] ?></td>
                        <td class="px-6 py-4 text-center">
                            <span class="<?= $badgeColor ?> px-2 py-1 rounded text-xs font-bold"><?= $row['status_pengambilan'] ?></span>
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-600">
                            <div class="text-xs">Inv: <?= $row['id_pembayaran'] ?></div>
                            <div class="font-bold">Rp <?= number_format($row['jml_bayar']) ?></div>
                        </td>
                        <td class="px-6 py-4 text-center">
                            <div class="flex justify-center gap-2">
                                <a href="?table=pengambilan_laundry&hal=edit&id=<?= $row['id_pengambilan'] ?>" class="text-yellow-500 hover:text-yellow-600 transition" title="Edit"><i class="fas fa-pen-to-square"></i></a>
                                <a href="?table=pengambilan_laundry&aksi=hapus&id=<?= $row['id_pengambilan'] ?>" onclick="return confirm('Hapus data pengambilan ini?')" class="text-red-400 hover:text-red-600 transition" title="Hapus"><i class="fas fa-trash-can"></i></a>
                            </div>
                        </td>
                    </tr>
                    <?php 
                        }
                    } else {
                        echo "<tr><td colspan='6' class='p-8 text-center text-gray-400 italic'>Belum ada data pengambilan.</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>
<?php endif; ?>