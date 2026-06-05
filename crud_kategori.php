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
            // Menampilkan data kategori terurut alfabetis
            $sql = "SELECT id, nama_kategori FROM kategori ORDER BY nama_kategori ASC";
            $result = mysqli_query($conn, $sql);
            $data_kategori = array();
            
            while ($row = mysqli_fetch_assoc($result)) {
                array_push($data_kategori, $row);
            }
            echo json_encode($data_kategori);
            break;

        case "insert":
            $nama = mysqli_real_escape_string($conn, $_POST['nama_kategori']);
            
            // Cek duplikasi nama kategori
            $cek = mysqli_query($conn, "SELECT * FROM kategori WHERE LOWER(nama_kategori) = LOWER('$nama')");
            if (mysqli_num_rows($cek) > 0) {
                $respon['kode'] = "111"; // Kategori sudah ada
            } else {
                $sql = "INSERT INTO kategori (nama_kategori) VALUES ('$nama')";
                if (mysqli_query($conn, $sql)) {
                    $respon['kode'] = "000"; // Sukses
                } else {
                    $respon['kode'] = "222"; // Gagal query
                }
            }
            echo json_encode($respon);
            break;

        case "update":
            $id = $_POST['id'];
            $nama = mysqli_real_escape_string($conn, $_POST['nama_kategori']);
            
            // Cek duplikasi nama dengan id yang berbeda
            $cek = mysqli_query($conn, "SELECT * FROM kategori WHERE LOWER(nama_kategori) = LOWER('$nama') AND id != '$id'");
            if (mysqli_num_rows($cek) > 0) {
                $respon['kode'] = "111"; // Nama sudah terpakai
            } else {
                $sql = "UPDATE kategori SET nama_kategori = '$nama' WHERE id = '$id'";
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
            $sql = "DELETE FROM kategori WHERE id = '$id'";
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