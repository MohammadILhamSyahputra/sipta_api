<?php
// Set waktu Indonesia Barat
date_default_timezone_set("Asia/Jakarta");

$DB_NAME = "sipta_mobile";
$DB_USER = "root";
$DB_PASS = ""; 
$DB_SERVER_LOC = "localhost";

$conn = mysqli_connect($DB_SERVER_LOC, $DB_USER, $DB_PASS, $DB_NAME);
header("Content-type: application/json; charset=UTF-8");

$respon = array();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    
    // 1. Hitung Jumlah Barang
    $q_barang = mysqli_query($conn, "SELECT COUNT(*) as total FROM barang");
    $r_barang = mysqli_fetch_assoc($q_barang);
    $respon['count_barang'] = intval($r_barang['total']);

    // 2. Hitung Jumlah Kategori
    $q_kategori = mysqli_query($conn, "SELECT COUNT(*) as total FROM kategori");
    $r_kategori = mysqli_fetch_assoc($q_kategori);
    $respon['count_kategori'] = intval($r_kategori['total']);

    // 3. Hitung Jumlah Sales
    $q_sales = mysqli_query($conn, "SELECT COUNT(*) as total FROM sales");
    $r_sales = mysqli_fetch_assoc($q_sales);
    $respon['count_sales'] = intval($r_sales['total']);

    // 4. Hitung Total Unit Stok (Gunakan COALESCE agar jika null tetap keluar angka 0)
    $q_stok = mysqli_query($conn, "SELECT COALESCE(SUM(stok), 0) as total FROM barang");
    $r_stok = mysqli_fetch_assoc($q_stok);
    $respon['total_stok'] = intval($r_stok['total']);

    // 5. Ambil Array Data untuk Kebutuhan Grafik PieChart
    $respon['grafik_stok'] = array();
    $q_grafik = mysqli_query($conn, "SELECT nama, stok FROM barang ORDER BY nama ASC");
    while ($row = mysqli_fetch_assoc($q_grafik)) {
        // Konversi stok ke tipe float/int agar terbaca valid di PieEntry Android
        $row['stok'] = floatval($row['stok']);
        array_push($respon['grafik_stok'], $row);
    }

    echo json_encode($respon);
}

mysqli_close($conn);
?>