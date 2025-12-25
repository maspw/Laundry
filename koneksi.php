<?php
// --- FILE: koneksi.php ---
// Config Database & Helper Functions

$host = 'localhost';
$user = 'root';
$pass = '';
$db_name = 'db_laundry';
$port = 3307; // Sesuaikan port XAMPP lo

$conn = mysqli_connect($host, $user, $pass, $db_name, $port);

if (!$conn) {
    die("❌ Koneksi gagal bestie: " . mysqli_connect_error());
}

$koneksi = $conn; 

// --- HELPER: ID OTOMATIS (SMART VERSION) ---
function buatIdOtomatis($conn, $table, $idColumn, $prefix) {
    // 1. Hitung panjang prefix (misal "ORD" = 3)
    // Di SQL Substring mulai dari indeks 1, jadi kita mulai ambil dari (panjang+1)
    $startSub = strlen($prefix) + 1;
    
    // 2. Ambil angka terbesar, ABAIKAN nol di depan dengan CAST AS UNSIGNED
    // Filter WHERE LIKE '$prefix%' biar ga salah ambil ID dari format lain kalau ada
    $query = "SELECT MAX(CAST(SUBSTRING($idColumn, $startSub) AS UNSIGNED)) as maxId 
              FROM $table 
              WHERE $idColumn LIKE '$prefix%'";
    
    $hasil = mysqli_query($conn, $query);
    $data = mysqli_fetch_array($hasil);
    $maxAngka = $data['maxId'];

    // 3. Logic Penambahan
    if ($maxAngka) {
        $noUrut = $maxAngka + 1;
    } else {
        $noUrut = 1;
    }
    
    // 4. Return Format Tanpa Nol (Sesuai Request)
    // Hasil: ORD14, ORD15, ORD100
    return $prefix . $noUrut;
}
?>