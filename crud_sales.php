<?php
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
            // Menampilkan seluruh data sales terurut nama ASC
            $sql = "SELECT id, nama_sales, no_telp, alamat FROM sales ORDER BY nama_sales ASC";
            $result = mysqli_query($conn, $sql);
            $data_sales = array();
            
            while ($row = mysqli_fetch_assoc($result)) {
                array_push($data_sales, $row);
            }
            echo json_encode($data_sales);
            break;

        case "insert":
            $nama = mysqli_real_escape_string($conn, $_POST['nama_sales']);
            $no_telp = mysqli_real_escape_string($conn, $_POST['no_telp']);
            $alamat = mysqli_real_escape_string($conn, $_POST['alamat']);
            
            // Validasi duplikasi nama sales (Case-Insensitive)
            $cek = mysqli_query($conn, "SELECT * FROM sales WHERE LOWER(nama_sales) = LOWER('$nama')");
            if (mysqli_num_rows($cek) > 0) {
                $respon['kode'] = "111"; // Nama sales duplikat
            } else {
                $sql = "INSERT INTO sales (nama_sales, no_telp, alamat) VALUES ('$nama', '$no_telp', '$alamat')";
                if (mysqli_query($conn, $sql)) {
                    $respon['kode'] = "000"; // Berhasil
                } else {
                    $respon['kode'] = "222"; 
                }
            }
            echo json_encode($respon);
            break;

        case "update":
            $id = $_POST['id'];
            $nama = mysqli_real_escape_string($conn, $_POST['nama_sales']);
            $no_telp = mysqli_real_escape_string($conn, $_POST['no_telp']);
            $alamat = mysqli_real_escape_string($conn, $_POST['alamat']);
            
            // Cek duplikasi nama pada ID yang berbeda
            $cek = mysqli_query($conn, "SELECT * FROM sales WHERE LOWER(nama_sales) = LOWER('$nama') AND id != '$id'");
            if (mysqli_num_rows($cek) > 0) {
                $respon['kode'] = "111"; // Nama sales sudah terpakai
            } else {
                $sql = "UPDATE sales SET nama_sales = '$nama', no_telp = '$no_telp', alamat = '$alamat' WHERE id = '$id'";
                if (mysqli_query($conn, $sql)) {
                    $respon['kode'] = "000";
                } else {
                    $respon['kode'] = "222";
                }
            }
            echo json_encode($respon);
            break;

        case "delete":
            $id = $_POST['id'];
            $sql = "DELETE FROM sales WHERE id = '$id'";
            if (mysqli_query($conn, $sql)) {
                $respon['kode'] = "000";
            } else {
                $respon['kode'] = "222";
            }
            echo json_encode($respon);
            break;
    }
}
mysqli_close($conn);
?>