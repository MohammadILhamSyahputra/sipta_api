<?php
date_default_timezone_set("Asia/Jakarta");

$DB_NAME = "sipta_mobile";
$DB_USER = "root";
$DB_PASS = ""; 
$DB_SERVER_LOC = "localhost";

$conn = mysqli_connect($DB_SERVER_LOC, $DB_USER, $DB_PASS, $DB_NAME);
header("Content-type: application/json; charset=UTF-8");

$respon = array();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $mode = $_POST['mode'];
    $today = date('Y-m-d');

    switch ($mode) {
        case "show_today":
            // Ambil transaksi yang tanggalnya COCOK dengan hari ini saja (LIKE 'yyyy-MM-dd%')
            $sql = "SELECT id, total_harga, total_bayar, kembalian, tanggal 
                    FROM transaksi 
                    WHERE tanggal LIKE '$today%' 
                    ORDER BY id DESC";
            
            $result = mysqli_query($conn, $sql);
            $data = array();
            while ($row = mysqli_fetch_assoc($result)) {
                $data[] = $row;
            }
            echo json_encode($data);
            break;

        case "show_detail":
            $transaksi_id = $_POST['transaksi_id'];
            
            // Ambil list item belanja (JOIN ke tabel barang sesuai struktur image_6dc2c1.png)
            $sqlDetail = "SELECT d.qty, d.harga_satuan, d.subtotal, b.nama 
                          FROM detail_transaksi d 
                          JOIN barang b ON d.barang_id = b.id 
                          WHERE d.transaksi_id = '$transaksi_id'";
            
            $resultDetail = mysqli_query($conn, $sqlDetail);
            $dataDetail = array();
            while ($rowD = mysqli_fetch_assoc($resultDetail)) {
                $dataDetail[] = $rowD;
            }
            echo json_encode($dataDetail);
            break;
    }
}
mysqli_close($conn);
?>