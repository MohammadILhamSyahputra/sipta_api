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
        case "show_lap_penjualan":
            $tgl_mulai = mysqli_real_escape_string($conn, $_POST['tgl_mulai']);
            $tgl_akhir = mysqli_real_escape_string($conn, $_POST['tgl_akhir']);

            // Query mengambil data penjualan berdasarkan rentang tanggal dari database sipta_mobile
            $sql = "SELECT b.nama, dt.qty, dt.harga_satuan, t.tanggal, b.harga_beli 
                    FROM detail_transaksi dt
                    JOIN barang b ON dt.barang_id = b.id
                    JOIN transaksi t ON dt.transaksi_id = t.id
                    WHERE DATE(t.tanggal) BETWEEN '$tgl_mulai' AND '$tgl_akhir'
                    ORDER BY t.tanggal DESC";

            $result = mysqli_query($conn, $sql);
            $data = array();
            
            while ($row = mysqli_fetch_assoc($result)) {
                $data[] = array(
                    'nama' => $row['nama'],
                    'qty' => (int)$row['qty'],
                    'harga_satuan' => (int)$row['harga_satuan'],
                    'tanggal' => $row['tanggal'],
                    'harga_beli' => (int)$row['harga_beli']
                );
            }
            echo json_encode($data);
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