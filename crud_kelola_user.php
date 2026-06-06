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
            // PERBAIKAN KUNCI: Gunakan 'AS' agar nama kolom database pas dengan JSON yang dicari Android
            $sql = "SELECT id, nama AS username, email, user_type AS level FROM users ORDER BY id DESC";
            $result = mysqli_query($conn, $sql);
            $data = array();
            if ($result) {
                while ($row = mysqli_fetch_assoc($result)) {
                    $data[] = $row;
                }
            }
            echo json_encode($data);
            break;

        case "insert":
            $username = mysqli_real_escape_string($conn, $_POST['username']);
            $email = mysqli_real_escape_string($conn, $_POST['email']);
            $password = $_POST['password']; 
            $level = mysqli_real_escape_string($conn, $_POST['level']);

            // Validasi duplikasi email terlebih dahulu
            $cek = mysqli_query($conn, "SELECT id FROM users WHERE email = '$email'");
            if (mysqli_num_rows($cek) > 0) {
                $respon['kode'] = "444"; 
                $respon['pesan'] = "Email sudah digunakan!";
            } else {
                // PERBAIKAN KUNCI: Sesuaikan nama kolom insert dengan database (nama, email, password, user_type)
                $sql = "INSERT INTO users (nama, email, password, user_type) VALUES ('$username', '$email', '$password', '$level')";
                if (mysqli_query($conn, $sql)) {
                    $respon['kode'] = "000";
                } else {
                    $respon['kode'] = "333";
                }
            }
            echo json_encode($respon);
            break;

        case "update_role":
            $id = $_POST['id'];
            $level = mysqli_real_escape_string($conn, $_POST['level']);

            // PERBAIKAN KUNCI: Ubah kolom SET menjadi user_type sesuai database
            $sql = "UPDATE users SET user_type = '$level' WHERE id = '$id'";
            if (mysqli_query($conn, $sql)) {
                $respon['kode'] = "000";
            } else {
                $respon['kode'] = "333";
            }
            echo json_encode($respon);
            break;

        case "delete":
            $id = $_POST['id'];
            $sql = "DELETE FROM users WHERE id = '$id'";
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