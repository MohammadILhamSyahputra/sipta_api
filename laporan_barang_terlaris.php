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

    switch ($mode) {
        case "show_barang_terlaris":
            $tgl_mulai = mysqli_real_escape_string($conn, $_POST['tgl_mulai']);
            $tgl_akhir = mysqli_real_escape_string($conn, $_POST['tgl_akhir']);

            // Query mengambil top 10 produk dengan akumulasi Qty terbanyak
            $sql = "SELECT b.kode_barang, b.nama, b.harga_beli, SUM(dt.qty) AS total_qty
                    FROM detail_transaksi dt
                    JOIN transaksi t ON dt.transaksi_id = t.id
                    JOIN barang b ON dt.barang_id = b.id
                    WHERE DATE(t.tanggal) BETWEEN '$tgl_mulai' AND '$tgl_akhir'
                    GROUP BY b.id, b.kode_barang, b.nama, b.harga_beli
                    ORDER BY total_qty DESC
                    LIMIT 10";

            $result = mysqli_query($conn, $sql);
            $barang = array();

            while ($row = mysqli_fetch_assoc($result)) {
                $barang[] = array(
                    'kode_barang' => $row['kode_barang'],
                    'nama' => $row['nama'],
                    'harga_beli' => (int)$row['harga_beli'],
                    'total_qty' => (int)$row['total_qty']
                );
            }
            echo json_encode($barang);
            break;

        default:
            $respon['kode'] = "444";
            $respon['pesan'] = "Mode tidak dikenali";
            echo json_encode($respon);
            break;
    }
}
mysqli_close($conn);
?>