<?php
// --- FILE: modul_orders.php ---
// Logic: Transaksi Order Laundry (Pure Transaction & Auto ID)

$pesan = "";

// --- 1. LOGIC TAMBAH ORDER BARU ---
if (isset($_POST['tambah_order'])) {
    $id_order     = $_POST['id_order']; 
    $tgl_order    = $_POST['tgl_order'];
    $id_pelanggan = $_POST['id_pelanggan'];
    $id_layanan   = $_POST['id_layanan'];
    $qty          = isset($_POST['qty']) ? (float)$_POST['qty'] : 1;
    $status       = 'Proses'; 

    // AUTO ASSIGN KARYAWAN (SEMENTARA BELUM ADA LOGIN)
    // Ambil karyawan pertama yang ada di database biar ga error
    // Nanti ganti ini pake: $id_karyawan = $_SESSION['user_id'];
    $q_kry_auto = mysqli_query($koneksi, "SELECT id_karyawan FROM karyawan LIMIT 1");
    $d_kry_auto = mysqli_fetch_assoc($q_kry_auto);
    $id_karyawan = $d_kry_auto ? $d_kry_auto['id_karyawan'] : 'KRY001'; // Fallback kalau kosong

    // Validasi Tanggal
    if ($tgl_order < date('Y-m-d')) {
        echo "<script>alert('❌ Tanggal tidak boleh mundur!'); window.location='?table=orders';</script>";
        exit;
    }

    $q_lyn = mysqli_query($koneksi, "SELECT harga FROM layanan WHERE id_layanan = '$id_layanan'");
    if (mysqli_num_rows($q_lyn) > 0) {
        $d_lyn = mysqli_fetch_assoc($q_lyn);
        $total = $d_lyn['harga'] * $qty;

        $q_ord = "INSERT INTO orders (id_order, tgl_order, total_order, status_order, id_pelanggan, id_karyawan, id_layanan) 
                  VALUES ('$id_order', '$tgl_order', '$total', '$status', '$id_pelanggan', '$id_karyawan', '$id_layanan')";
        
        if (mysqli_query($koneksi, $q_ord)) {
            $pesan = "✅ Order $id_order berhasil masuk! Total Rp ".number_format($total);
        } else {
            $pesan = "❌ Error Database: " . mysqli_error($koneksi); 
        }
    } else {
        $pesan = "❌ Error: Layanan tidak ditemukan!";
    }
}

// --- 2. LOGIC UPDATE STATUS & HAPUS ---
if (isset($_GET['aksi']) && isset($_GET['id'])) {
    $id = $_GET['id'];
    $act = $_GET['aksi'];

    if ($act == 'selesai') {
        // Cek Pembayaran Lunas
        $cek_bayar = mysqli_query($koneksi, "SELECT status FROM pembayaran WHERE id_order='$id'");
        $data_bayar = mysqli_fetch_assoc($cek_bayar);

        if ($data_bayar && $data_bayar['status'] == 'Lunas') {
            mysqli_query($koneksi, "UPDATE orders SET status_order='Selesai' WHERE id_order='$id'");
            echo "<script>alert('✅ Sip! Order $id sudah selesai.'); window.location='?table=orders';</script>";
        } else {
            echo "<script>alert('⛔ Eits, belum bisa selesai! Order ini BELUM LUNAS. Harap selesaikan pembayaran dulu.'); window.location='?table=orders';</script>";
        }

    } elseif ($act == 'batal') {
        mysqli_query($koneksi, "UPDATE orders SET status_order='Batal' WHERE id_order='$id'");
        echo "<script>window.location='?table=orders';</script>";
    }
}

if (isset($_GET['hapus'])) {
    $id_hps = $_GET['hapus'];
    $q_del = "DELETE FROM orders WHERE id_order='$id_hps'";
    if (mysqli_query($koneksi, $q_del)) {
        echo "<script>alert('🗑️ Order dihapus!'); window.location='?table=orders';</script>";
    } else {
        echo "<script>alert('❌ Gagal hapus: " . mysqli_error($koneksi) . "'); window.location='?table=orders';</script>";
    }
}

// --- GENERATE ID OTOMATIS ---
$nextOrderId = buatIdOtomatis($koneksi, "orders", "id_order", "ORD");
?>

<!-- VIEW START -->
<?php if ($pesan != ''): ?>
    <div class="bg-blue-100 border-l-4 border-blue-500 text-blue-700 p-4 mb-6 rounded shadow-sm flex items-center justify-between">
        <div><span class="font-bold">Info:</span> <?= $pesan ?></div>
        <button onclick="this.parentElement.style.display='none'" class="text-blue-500 hover:text-blue-700"><i class="fa-solid fa-times"></i></button>
    </div>
<?php endif; ?>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
    <!-- KOLOM KIRI: FORM ORDER (Sudah Bersih dari Form Pelanggan & Karyawan) -->
    <div class="lg:col-span-1 space-y-6">
        
        <!-- Form Order -->
        <div class="bg-white p-6 rounded-xl shadow-lg border border-gray-200">
            <h2 class="text-xl font-bold mb-4 text-gray-700 flex items-center">
                <span class="bg-blue-100 text-blue-600 p-2 rounded-lg mr-2"><i class="fa-solid fa-cart-plus"></i></span> Input Order
            </h2>
            <form action="" method="POST" class="space-y-4">
                <div>
                    <label class="block text-xs font-bold text-gray-500 uppercase mb-1">ID Order</label>
                    <input type="text" name="id_order" value="<?= $nextOrderId ?>" readonly class="w-full px-3 py-2 border rounded-lg bg-gray-100 text-gray-500 font-mono font-bold cursor-not-allowed focus:outline-none">
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Tanggal</label>
                    <input type="date" name="tgl_order" value="<?= date('Y-m-d') ?>" min="<?= date('Y-m-d') ?>" required class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-400">
                </div>
                <div>
                    <div class="flex justify-between items-center mb-1">
                        <label class="block text-xs font-bold text-gray-500 uppercase">Pelanggan</label>
                        <a href="?table=pelanggan&hal=tambah" class="text-[10px] text-blue-500 hover:underline">+ Baru?</a>
                    </div>
                    <select name="id_pelanggan" required class="w-full px-3 py-2 border rounded-lg bg-white focus:outline-none focus:ring-2 focus:ring-blue-400">
                        <option value="">-- Pilih Pelanggan --</option>
                        <?php $q=mysqli_query($koneksi, "SELECT * FROM pelanggan ORDER BY id_pelanggan DESC"); while($r=mysqli_fetch_assoc($q)){ echo "<option value='".$r['id_pelanggan']."'>".$r['nama']." (".$r['id_pelanggan'].")</option>"; } ?>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Layanan</label>
                    <select name="id_layanan" required class="w-full px-3 py-2 border rounded-lg bg-white focus:outline-none focus:ring-2 focus:ring-blue-400">
                        <option value="">-- Pilih Layanan --</option>
                        <?php $q=mysqli_query($koneksi, "SELECT * FROM layanan"); while($r=mysqli_fetch_assoc($q)){ echo "<option value='".$r['id_layanan']."'>".$r['nama_layanan']." - Rp ".number_format($r['harga'])."</option>"; } ?>
                    </select>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Qty / Berat</label>
                        <input type="number" name="qty" value="1" min="1" step="0.1" required class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-400">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Satuan</label>
                        <select name="satuan" class="w-full px-3 py-2 border rounded-lg bg-white focus:outline-none focus:ring-2 focus:ring-blue-400">
                            <option value="kg">Kg</option><option value="pcs">Pcs</option><option value="meter">Meter</option>
                        </select>
                    </div>
                </div>
                
                <!-- INPUT KARYAWAN DIHAPUS (Auto Assign di Backend) -->

                <button type="submit" name="tambah_order" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-lg transition duration-300 shadow-md">Simpan Order 🚀</button>
            </form>
        </div>
    </div>

    <!-- KOLOM KANAN: TABEL -->
    <div class="lg:col-span-2">
        <div class="bg-white rounded-xl shadow-lg border border-gray-200 overflow-hidden">
            <div class="p-6 border-b border-gray-100 flex justify-between items-center">
                <h2 class="text-xl font-bold text-gray-700">📜 Daftar Order Aktif</h2>
                <div class="text-xs text-gray-400">Sorted by Date Desc</div>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm text-left">
                    <thead class="text-xs text-gray-700 uppercase bg-gray-50 border-b border-gray-100">
                        <tr>
                            <th class="px-6 py-3 font-semibold">ID</th><th class="px-6 py-3 font-semibold">Tanggal</th><th class="px-6 py-3 font-semibold">Pelanggan</th><th class="px-6 py-3 font-semibold">Layanan</th><th class="px-6 py-3 font-semibold text-center">Status</th><th class="px-6 py-3 font-semibold text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <?php
                        $q_view = "SELECT o.*, p.nama AS nm_plg, l.nama_layanan, l.satuan, l.harga, 
                                          (SELECT status FROM pembayaran WHERE id_order = o.id_order LIMIT 1) as status_bayar 
                                   FROM orders o 
                                   JOIN pelanggan p ON o.id_pelanggan = p.id_pelanggan 
                                   JOIN layanan l ON o.id_layanan = l.id_layanan 
                                   ORDER BY o.tgl_order DESC, o.id_order DESC";
                        
                        $res = mysqli_query($koneksi, $q_view);
                        if (mysqli_num_rows($res) > 0) {
                            while($row = mysqli_fetch_assoc($res)) {
                                $est_qty = ($row['harga'] > 0) ? round($row['total_order'] / $row['harga'], 1) : 0;
                                $sttClass = match($row['status_order']) { 'Selesai'=>'bg-green-100 text-green-800 border-green-200', 'Proses'=>'bg-yellow-100 text-yellow-800 border-yellow-200', 'Batal'=>'bg-red-100 text-red-800 border-red-200', default=>'bg-gray-100 text-gray-800' };
                                
                                $bayarBadge = ($row['status_bayar'] == 'Lunas') 
                                    ? '<span class="text-[10px] bg-green-500 text-white px-1 rounded ml-1">LUNAS</span>' 
                                    : '<span class="text-[10px] bg-red-500 text-white px-1 rounded ml-1">BELUM</span>';
                        ?>
                        <tr class="bg-white hover:bg-blue-50 transition duration-150">
                            <td class="px-6 py-4 font-bold text-gray-700 font-mono"><?= $row['id_order'] ?></td>
                            <td class="px-6 py-4 text-gray-500 text-xs"><?= date('d/m/Y', strtotime($row['tgl_order'])) ?></td>
                            <td class="px-6 py-4 font-semibold text-gray-800"><?= $row['nm_plg'] ?></td>
                            <td class="px-6 py-4"><div class="font-medium text-gray-700"><?= $row['nama_layanan'] ?></div><div class="text-xs text-gray-500 mt-1">Rp <?= number_format($row['total_order'], 0, ',', '.') ?> <?= $bayarBadge ?></div></td>
                            <td class="px-6 py-4 text-center"><span class="<?= $sttClass ?> px-2.5 py-1 rounded-full text-xs font-bold border block w-fit mx-auto"><?= $row['status_order'] ?></span></td>
                            <td class="px-6 py-4 text-center">
                                <div class="flex justify-center items-center gap-2">
                                    <?php if($row['status_order'] == 'Proses'): ?>
                                        <a href="?table=orders&aksi=selesai&id=<?= $row['id_order'] ?>" class="text-green-500 hover:text-green-700 p-1" title="Set Selesai"><i class="fa-solid fa-check-circle fa-lg"></i></a>
                                        <a href="?table=orders&aksi=batal&id=<?= $row['id_order'] ?>" class="text-yellow-500 hover:text-yellow-700 p-1" onclick="return confirm('Batalkan?')" title="Batal"><i class="fa-solid fa-ban fa-lg"></i></a>
                                    <?php endif; ?>
                                    <a href="?table=orders&hapus=<?= $row['id_order'] ?>" onclick="return confirm('Hapus permanen?')" class="text-red-400 hover:text-red-600 p-1" title="Hapus"><i class="fa-solid fa-trash-can fa-lg"></i></a>
                                </div>
                            </td>
                        </tr>
                        <?php }} else { echo "<tr><td colspan='6' class='text-center py-8 text-gray-400 italic'>Belum ada orderan masuk nih...</td></tr>"; } ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>