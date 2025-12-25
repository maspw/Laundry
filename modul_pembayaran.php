<?php
// --- FILE: modul_pembayaran.php ---
// Logic: Transaksi Pembayaran (Status Auto Lunas)

$halaman = isset($_GET['hal']) ? $_GET['hal'] : 'data';

// --- 1. LOGIC HAPUS ---
if (isset($_GET['aksi']) && $_GET['aksi'] == 'hapus') {
    $id = $_GET['id'];
    $q = "DELETE FROM pembayaran WHERE id_pembayaran='$id'";
    if (mysqli_query($koneksi, $q)) {
        echo "<script>alert('✅ Data pembayaran dihapus!'); window.location='?table=pembayaran';</script>";
    } else {
        echo "<script>alert('❌ Gagal hapus! Data masih terikat.'); window.location='?table=pembayaran';</script>";
    }
}

// --- 2. LOGIC SIMPAN (BARU) ---
if (isset($_POST['simpan_bayar'])) {
    $id_bayar = $_POST['id_pembayaran'];
    $tgl      = $_POST['tgl_bayar'];
    $jml      = $_POST['jml_bayar'];
    $status   = 'Lunas'; // SET OTOMATIS LUNAS
    $order    = $_POST['id_order'];
    $metode   = $_POST['id_metode'];
    $karyawan = $_POST['id_karyawan'];

    // Cek double bayar
    $cek = mysqli_query($koneksi, "SELECT id_pembayaran FROM pembayaran WHERE id_order='$order'");
    if (mysqli_num_rows($cek) > 0) {
        echo "<script>alert('❌ Order ini sudah dibayar sebelumnya!'); window.location='?table=pembayaran';</script>";
    } else {
        $q = "INSERT INTO pembayaran VALUES ('$id_bayar', '$tgl', '$jml', '$status', '$order', '$metode', '$karyawan')";
        if (mysqli_query($koneksi, $q)) {
            echo "<script>alert('✅ Pembayaran berhasil! Status: LUNAS 💸'); window.location='?table=pembayaran';</script>";
        } else {
            echo "<script>alert('❌ Gagal simpan: " . mysqli_error($koneksi) . "');</script>";
        }
    }
}

// --- 3. LOGIC UPDATE ---
if (isset($_POST['update_bayar'])) {
    $id_bayar = $_POST['id_pembayaran'];
    $tgl      = $_POST['tgl_bayar'];
    $jml      = $_POST['jml_bayar'];
    $status   = 'Lunas'; // Tetep paksa Lunas pas edit
    $metode   = $_POST['id_metode'];
    $karyawan = $_POST['id_karyawan'];

    $q = "UPDATE pembayaran SET tgl_bayar='$tgl', jml_bayar='$jml', status='$status', id_metode='$metode', id_karyawan='$karyawan' WHERE id_pembayaran='$id_bayar'";
    if (mysqli_query($koneksi, $q)) {
        echo "<script>alert('✅ Data berhasil diupdate! ✏️'); window.location='?table=pembayaran';</script>";
    } else {
        echo "<script>alert('❌ Gagal update: " . mysqli_error($koneksi) . "');</script>";
    }
}

// --- VIEW FORM (TAMBAH / EDIT) ---
if ($halaman == 'tambah' || $halaman == 'edit'):
    $is_edit = ($halaman == 'edit');
    
    // Init Val
    $id_val = ""; $tgl_val = date('Y-m-d'); $jml_val = ""; $ord_val = ""; $met_val = ""; $kry_val = "";

    if ($is_edit && isset($_GET['id'])) {
        $id = $_GET['id'];
        $q = mysqli_query($koneksi, "SELECT * FROM pembayaran WHERE id_pembayaran='$id'");
        $data = mysqli_fetch_assoc($q);
        if($data) {
            $id_val = $data['id_pembayaran']; $tgl_val = $data['tgl_bayar'];
            $jml_val = $data['jml_bayar']; $ord_val = $data['id_order'];
            $met_val = $data['id_metode']; $kry_val = $data['id_karyawan'];
        }
    } elseif (!$is_edit) {
        $id_val = buatIdOtomatis($koneksi, "pembayaran", "id_pembayaran", "PB");
    }
?>
    <div class="max-w-xl mx-auto bg-white p-8 rounded-xl shadow-lg border border-gray-100 mt-4 relative overflow-hidden">
        <div class="absolute top-0 left-0 w-full h-1 bg-gradient-to-r from-blue-500 to-indigo-600"></div>
        <div class="flex justify-between items-center mb-6 border-b pb-4">
            <h2 class="text-xl font-bold text-gray-800">
                <?= $is_edit ? '<span class="text-yellow-600">✏️ Edit Pembayaran</span>' : '<span class="text-blue-600">💸 Input Pembayaran</span>' ?>
            </h2>
            <a href="?table=pembayaran" class="text-gray-400 hover:text-red-500 transition"><i class="fas fa-times text-xl"></i></a>
        </div>

        <form method="POST" action="" class="space-y-4">
            <div>
                <label class="block text-sm font-bold text-gray-700 mb-1">ID Pembayaran</label>
                <input type="text" name="id_pembayaran" value="<?= $id_val ?>" readonly class="w-full px-4 py-2 border rounded-lg bg-gray-100 text-gray-500 font-mono font-bold cursor-not-allowed">
            </div>

            <div>
                <label class="block text-sm font-bold text-gray-700 mb-1">Tanggal Bayar</label>
                <input type="date" name="tgl_bayar" value="<?= $tgl_val ?>" required class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:outline-none">
            </div>

            <div>
                <label class="block text-sm font-bold text-gray-700 mb-1">Order Tagihan</label>
                <?php if ($is_edit): ?>
                    <input type="text" name="id_order" value="<?= $ord_val ?>" readonly class="w-full px-4 py-2 border rounded-lg bg-gray-100 text-gray-500">
                    <p class="text-xs text-gray-400 mt-1">*ID Order tidak bisa diubah saat edit</p>
                <?php else: ?>
                    <select name="id_order" required class="w-full px-4 py-2 border rounded-lg bg-white focus:ring-2 focus:ring-blue-500" onchange="isiNominal(this)">
                        <option value="" data-total="0">-- Pilih Order Belum Bayar --</option>
                        <?php
                        // Tampilkan Order (Proses/Selesai) YANG BELUM ADA DI TABEL PEMBAYARAN
                        $sql = mysqli_query($koneksi, "
                            SELECT * FROM orders 
                            WHERE status_order IN ('Proses', 'Selesai') 
                            AND id_order NOT IN (SELECT id_order FROM pembayaran)
                            ORDER BY id_order DESC
                        ");
                        while($r=mysqli_fetch_array($sql)){ 
                            $statusLabel = $r['status_order'] == 'Selesai' ? '(Selesai ✅)' : '(Proses ⏳)';
                            echo "<option value='$r[id_order]' data-total='{$r['total_order']}'>$r[id_order] - Rp " . number_format($r['total_order'],0,',','.') . " $statusLabel</option>"; 
                        }
                        ?>
                    </select>
                    <p class="text-[10px] text-blue-500 mt-1">*Hanya menampilkan order yang belum dibayar</p>
                <?php endif; ?>
            </div>

            <div>
                <label class="block text-sm font-bold text-gray-700 mb-1">Nominal (Rp)</label>
                <input type="number" id="nominalBayar" name="jml_bayar" value="<?= $jml_val ?>" required class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500">
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-1">Metode</label>
                    <select name="id_metode" required class="w-full px-4 py-2 border rounded-lg bg-white focus:ring-2 focus:ring-blue-500">
                        <?php $sql = mysqli_query($koneksi, "SELECT * FROM metode_pembayaran"); while($r=mysqli_fetch_array($sql)){ $sel=($r['id_metode']==$met_val)?'selected':''; echo "<option value='$r[id_metode]' $sel>$r[nama_metode]</option>"; } ?>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-1">Kasir</label>
                    <select name="id_karyawan" required class="w-full px-4 py-2 border rounded-lg bg-white focus:ring-2 focus:ring-blue-500">
                        <?php $sql = mysqli_query($koneksi, "SELECT * FROM karyawan WHERE jabatan IN ('Kasir', 'Admin')"); while($r=mysqli_fetch_array($sql)){ $sel=($r['id_karyawan']==$kry_val)?'selected':''; echo "<option value='$r[id_karyawan]' $sel>$r[nama_karyawan]</option>"; } ?>
                    </select>
                </div>
            </div>

            <!-- DROPDOWN STATUS DIHAPUS SESUAI REQUEST (AUTO LUNAS DI BACKEND) -->

            <div class="pt-4 flex gap-3">
                <button type="submit" name="<?= $is_edit ? 'update_bayar' : 'simpan_bayar' ?>" class="flex-1 bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-lg shadow transition">
                    <?= $is_edit ? 'Simpan Perubahan' : 'Bayar & LUNAS' ?>
                </button>
            </div>
        </form>
    </div>

    <script>
    function isiNominal(selectObject) {
        var total = selectObject.options[selectObject.selectedIndex].getAttribute('data-total');
        document.getElementById('nominalBayar').value = total;
    }
    </script>

<?php else: ?>
    <!-- VIEW TABEL UTAMA -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="p-6 border-b border-gray-100 flex justify-between items-center bg-gray-50">
            <div>
                <h3 class="text-lg font-bold text-blue-600">Data Transaksi Pembayaran</h3>
                <p class="text-xs text-gray-500">Catatan pemasukan kasir.</p>
            </div>
            <a href="?table=pembayaran&hal=tambah" class="bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold py-2 px-4 rounded-lg shadow flex items-center gap-2 transition">
                <i class="fas fa-plus"></i> Input Baru
            </a>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-white text-gray-600 text-xs uppercase tracking-wider border-b border-gray-100">
                        <th class="px-6 py-4 font-semibold">ID</th>
                        <th class="px-6 py-4 font-semibold">Tanggal</th>
                        <th class="px-6 py-4 font-semibold">Order</th>
                        <th class="px-6 py-4 font-semibold text-right">Nominal</th>
                        <th class="px-6 py-4 font-semibold text-center">Metode</th>
                        <th class="px-6 py-4 font-semibold">Kasir</th>
                        <th class="px-6 py-4 font-semibold text-center">Status</th>
                        <th class="px-6 py-4 font-semibold text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    <?php
                    $q = "SELECT p.*, mp.nama_metode, k.nama_karyawan FROM pembayaran p JOIN metode_pembayaran mp ON p.id_metode = mp.id_metode JOIN karyawan k ON p.id_karyawan = k.id_karyawan ORDER BY p.id_pembayaran DESC";
                    $sql = mysqli_query($koneksi, $q);
                    
                    if(mysqli_num_rows($sql) > 0) {
                        while($row = mysqli_fetch_assoc($sql)) {
                            // Status pasti Lunas (karena inputan baru auto lunas), tapi tetep kita kasih logic warna jaga2 data lama
                            $sttClass = ($row['status'] == 'Lunas') ? 'bg-green-100 text-green-700 border border-green-200' : 'bg-red-100 text-red-700 border border-red-200';
                            $nmMetode = $row['nama_metode']; 
                    ?>
                    <tr class="hover:bg-blue-50 transition duration-150">
                        <td class="px-6 py-4 font-bold text-gray-700"><?= $row['id_pembayaran'] ?></td>
                        <td class="px-6 py-4 text-sm text-gray-600"><?= date('d/m/Y', strtotime($row['tgl_bayar'])) ?></td>
                        <td class="px-6 py-4 font-mono text-sm"><?= $row['id_order'] ?></td>
                        <td class="px-6 py-4 text-right font-bold text-gray-800">Rp <?= number_format($row['jml_bayar'],0,',','.') ?></td>
                        <td class="px-6 py-4 text-center"><span class="bg-purple-50 text-purple-600 py-1 px-2 rounded text-xs font-semibold border border-purple-100"><?= $nmMetode ?></span></td>
                        <td class="px-6 py-4 text-sm"><?= $row['nama_karyawan'] ?></td>
                        <td class="px-6 py-4 text-center"><span class="px-2 py-1 rounded text-xs font-bold <?= $sttClass ?>"><?= $row['status'] ?></span></td>
                        <td class="px-6 py-4 text-center">
                            <div class="flex justify-center gap-2">
                                <a href="?table=pembayaran&hal=edit&id=<?= $row['id_pembayaran'] ?>" class="text-yellow-500 hover:text-yellow-600 transition" title="Edit"><i class="fas fa-pen-to-square"></i></a>
                                <a href="?table=pembayaran&aksi=hapus&id=<?= $row['id_pembayaran'] ?>" onclick="return confirm('Hapus permanen?')" class="text-red-400 hover:text-red-600 transition" title="Hapus"><i class="fas fa-trash-can"></i></a>
                            </div>
                        </td>
                    </tr>
                    <?php 
                        }
                    } else {
                        echo "<tr><td colspan='8' class='p-8 text-center text-gray-400 italic'>Belum ada data transaksi. Yuk tambah dulu!</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>
<?php endif; ?>