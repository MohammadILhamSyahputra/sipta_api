<?php
$DB_NAME = "sipta_mobile";
$DB_USER = "root";
$DB_PASS = ""; // Default Laragon kosong
$DB_SERVER_LOC = "localhost";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $conn = mysqli_connect($DB_SERVER_LOC, $DB_USER, $DB_PASS, $DB_NAME);

    // Menerima parameter input dari Android Volley
    $email = $_POST['email'];
    $password = $_POST['password'];

    $respon = array();

    // Query mencari user berdasarkan email dan password
    $sql = "SELECT * FROM users WHERE email = '$email' AND password = '$password'";
    $result = mysqli_query($conn, $sql);

    if (mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);
        
        $respon['kode'] = "000"; // Login Sukses
        $respon['nama'] = $row['nama'];
        $respon['email'] = $row['email'];
        $respon['user_type'] = $row['user_type']; // Mengambil role dari MySQL
        
        echo json_encode($respon);
    } else {
        $respon['kode'] = "111"; // Email atau Password Salah
        echo json_encode($respon);
    }

    mysqli_close($conn);
}
?>