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
        case "show":
            // Mengambil riwayat kunjungan digabung (JOIN) dengan nama sales
            $sql = "SELECT r.id, s.nama_sales, r.status, r.tanggal_kunjungan 
                    FROM riwayat_sales r
                    JOIN sales s ON r.sales_id = s.id
                    ORDER BY r.id DESC";
            $result = mysqli_query($conn, $sql);
            $data = array();
            while ($row = mysqli_fetch_assoc($result)) {
                $data[] = $row;
            }
            echo json_encode($data);
            break;

        case "get_sales_spinner":
            // Mengambil daftar sales untuk pengisi dialog input/edit
            $sql = "SELECT id, nama_sales FROM sales ORDER BY nama_sales ASC";
            $result = mysqli_query($conn, $sql);
            $sales = array();
            while ($row = mysqli_fetch_assoc($result)) {
                $sales[] = $row;
            }
            echo json_encode($sales);
            break;

        case "get_one":
            $id = $_POST['id'];
            $sql = "SELECT sales_id, status, tanggal_kunjungan FROM riwayat_sales WHERE id = '$id'";
            $result = mysqli_query($conn, $sql);
            echo json_encode(mysqli_fetch_assoc($result));
            break;

        case "insert":
            $sales_id = $_POST['sales_id'];
            $status = mysqli_real_escape_string($conn, $_POST['status']);
            $tanggal = mysqli_real_escape_string($conn, $_POST['tanggal_kunjungan']);
            $now = date('Y-m-d H:i:s');

            // PERBAIKAN: Jika tanggal kosong, set menjadi keyword NULL agar cocok dengan tipe data timestamp
            $tanggal_sql = (empty($tanggal) || $tanggal == "null") ? "NULL" : "'$tanggal'";

            // Query disesuaikan dengan kolom baru yang sudah ditambahkan
            $sql = "INSERT INTO riwayat_sales (sales_id, tanggal_kunjungan, status, created_at) 
                    VALUES ('$sales_id', $tanggal_sql, '$status', '$now')";
            
            if (mysqli_query($conn, $sql)) {
                $respon['kode'] = "000";
            } else {
                $respon['kode'] = "333";
            }
            echo json_encode($respon);
            break;

        case "update":
            $id = $_POST['id'];
            $sales_id = $_POST['sales_id'];
            $status = mysqli_real_escape_string($conn, $_POST['status']);
            $tanggal = mysqli_real_escape_string($conn, $_POST['tanggal_kunjungan']);

            // PERBAIKAN: Penanganan nilai NULL saat proses update data
            $tanggal_sql = (empty($tanggal) || $tanggal == "null") ? "NULL" : "'$tanggal'";

            $sql = "UPDATE riwayat_sales 
                    SET sales_id = '$sales_id', status = '$status', tanggal_kunjungan = $tanggal_sql 
                    WHERE id = '$id'";
            
            if (mysqli_query($conn, $sql)) {
                $respon['kode'] = "000";
            } else {
                $respon['kode'] = "333";
            }
            echo json_encode($respon);
            break;

        case "delete":
            $id = $_POST['id'];
            $sql = "DELETE FROM riwayat_sales WHERE id = '$id'";
            if (mysqli_query($conn, $sql)) {
                $respon['kode'] = "000";
            } else {
                $respon['kode'] = "333";
            }
            echo json_encode($respon);
            break;
    }
}
mysqli_close($conn);
?>