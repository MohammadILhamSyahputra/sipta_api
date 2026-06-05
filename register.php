<?php
$DB_NAME = "sipta_mobile";
$DB_USER = "root";
$DB_PASS = ""; // Default Laragon kosong
$DB_SERVER_LOC = "localhost";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $conn = mysqli_connect($DB_SERVER_LOC, $DB_USER, $DB_PASS, $DB_NAME);

    // Menerima data dari Android Volley
    $nama = $_POST['nama'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    $userType = $_POST['user_type'];

    $respon = array();

    // 1. Cek apakah email sudah terdaftar
    $cekSql = "SELECT * FROM users WHERE email = '$email'";
    $cekResult = mysqli_query($conn, $cekSql);

    if (mysqli_num_rows($cekResult) > 0) {
        // Email sudah ada
        $respon['kode'] = "111"; // Kode gagal / email duplikat
        echo json_encode($respon);
    } else {
        // 2. Jika aman, lakukan INSERT
        $sql = "INSERT INTO users (nama, email, password, user_type) VALUES ('$nama', '$email', '$password', '$userType')";
        $result = mysqli_query($conn, $sql);

        if ($result) {
            $respon['kode'] = "000"; // Kode sukses sesuai standar modul
            echo json_encode($respon);
        } else {
            $respon['kode'] = "222"; // Kode gagal query
            echo json_encode($respon);
        }
    }
    mysqli_close($conn);
}
?>