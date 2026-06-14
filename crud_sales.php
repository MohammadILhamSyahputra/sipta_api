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
            // 🟢 PERBAIKAN 1: Ikut memanggil kolom latitude dan longitude dari tabel sales
            $sql = "SELECT id, nama_sales, no_telp, alamat, latitude, longitude FROM sales ORDER BY nama_sales ASC";
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
            
            // 🟢 PERBAIKAN 2: Tangkap kiriman data koordinat dari Volley Android
            $latitude = mysqli_real_escape_string($conn, $_POST['latitude']);
            $longitude = mysqli_real_escape_string($conn, $_POST['longitude']);
            
            // Validasi duplikasi nama sales (Case-Insensitive)
            $cek = mysqli_query($conn, "SELECT * FROM sales WHERE LOWER(nama_sales) = LOWER('$nama')");
            if (mysqli_num_rows($cek) > 0) {
                $respon['kode'] = "111"; // Nama sales duplikat
            } else {
                // 🟢 PERBAIKAN 3: Masukkan data latitude dan longitude ke dalam query SQL INSERT
                $sql = "INSERT INTO sales (nama_sales, no_telp, alamat, latitude, longitude) VALUES ('$nama', '$no_telp', '$alamat', '$latitude', '$longitude')";
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
            
            // 🟢 PERBAIKAN 4: Tangkap kiriman data koordinat baru untuk proses pembaruan data
            $latitude = mysqli_real_escape_string($conn, $_POST['latitude']);
            $longitude = mysqli_real_escape_string($conn, $_POST['longitude']);
            
            // Cek duplikasi nama pada ID yang berbeda
            $cek = mysqli_query($conn, "SELECT * FROM sales WHERE LOWER(nama_sales) = LOWER('$nama') AND id != '$id'");
            if (mysqli_num_rows($cek) > 0) {
                $respon['kode'] = "111"; // Nama sales sudah terpakai
            } else {
                // 🟢 PERBAIKAN 5: Perbarui kolom latitude dan longitude berdasarkan ID sales terpilih
                $sql = "UPDATE sales SET nama_sales = '$nama', no_telp = '$no_telp', alamat = '$alamat', latitude = '$latitude', longitude = '$longitude' WHERE id = '$id'";
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