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
            $query = isset($_POST['query']) ? mysqli_real_escape_string($conn, $_POST['query']) : "";
            
            // Ditambahkan b.foto di dalam query SELECT
            $sql = "SELECT b.id, b.kode_barang, b.nama, b.stok, b.harga_beli, b.harga_jual, b.foto,
                           k.nama_kategori, s.nama_sales, b.kategori_id, b.sales_id
                    FROM barang b
                    JOIN kategori k ON b.kategori_id = k.id
                    JOIN sales s ON b.sales_id = s.id
                    WHERE b.nama LIKE '%$query%' OR b.kode_barang LIKE '%$query%'
                    ORDER BY b.nama ASC";
                    
            $result = mysqli_query($conn, $sql);
            $data_barang = array();
            while ($row = mysqli_fetch_assoc($result)) {
                array_push($data_barang, $row);
            }
            echo json_encode($data_barang);
            break;

        case "get_spinner_data":
            $sql_kat = "SELECT id, nama_kategori FROM kategori ORDER BY nama_kategori ASC";
            $res_kat = mysqli_query($conn, $sql_kat);
            $kategori = array();
            while($r = mysqli_fetch_assoc($res_kat)){ $kategori[] = $r; }

            $sql_sal = "SELECT id, nama_sales FROM sales ORDER BY nama_sales ASC";
            $res_sal = mysqli_query($conn, $sql_sal);
            $sales = array();
            while($r = mysqli_fetch_assoc($res_sal)){ $sales[] = $r; }

            echo json_encode(array("kategori" => $kategori, "sales" => $sales));
            break;

        case "insert":
            $kode = mysqli_real_escape_string($conn, $_POST['kode_barang']);
            $nama = mysqli_real_escape_string($conn, $_POST['nama']);
            $stok = $_POST['stok'];
            $harga_beli = $_POST['harga_beli'];
            $harga_jual = $_POST['harga_jual'];
            $kategori_id = $_POST['id_kategori'];
            $sales_id = $_POST['id_sales'];
            
            // Proses upload teks gambar Base64 jika dikirim dari Android
            $nama_file_foto = null;
            if (!empty($_POST['foto_base64'])) {
                $foto_base64 = $_POST['foto_base64'];
                $nama_file_foto = "IMG_" . $kode . "_" . date('Ymd_His') . ".jpg";
                $path_tujuan = "images/" . $nama_file_foto;
                
                // Decode teks Base64 kembali menjadi file gambar riil di folder images
                file_put_contents($path_tujuan, base64_decode($foto_base64));
            }

            $cek_kode = mysqli_query($conn, "SELECT id FROM barang WHERE LOWER(kode_barang) = LOWER('$kode')");
            $cek_nama = mysqli_query($conn, "SELECT id FROM barang WHERE LOWER(nama) = LOWER('$nama')");

            if (mysqli_num_rows($cek_kode) > 0) {
                $respon['kode'] = "111"; 
            } else if (mysqli_num_rows($cek_nama) > 0) {
                $respon['kode'] = "222"; 
            } else {
                $sql = "INSERT INTO barang (kode_barang, nama, stok, harga_beli, harga_jual, kategori_id, sales_id, foto) 
                        VALUES ('$kode', '$nama', '$stok', '$harga_beli', '$harga_jual', '$kategori_id', '$sales_id', '$nama_file_foto')";
                if (mysqli_query($conn, $sql)) {
                    $respon['kode'] = "000"; 
                } else {
                    $respon['kode'] = "333"; 
                }
            }
            echo json_encode($respon);
            break;

        case "update":
            $id = $_POST['id'];
            $kode = mysqli_real_escape_string($conn, $_POST['kode_barang']);
            $nama = mysqli_real_escape_string($conn, $_POST['nama']);
            $stok = $_POST['stok'];
            $harga_beli = $_POST['harga_beli'];
            $harga_jual = $_POST['harga_jual'];
            $kategori_id = $_POST['id_kategori'];
            $sales_id = $_POST['id_sales'];

            // Cek apakah ada upload foto baru saat proses edit data
            if (!empty($_POST['foto_base64'])) {
                $foto_base64 = $_POST['foto_base64'];
                $nama_file_foto = "IMG_" . $kode . "_" . date('Ymd_His') . ".jpg";
                $path_tujuan = "images/" . $nama_file_foto;
                
                // Hapus foto lama yang ada di server agar penyimpanan Laragon tidak penuh
                $res_lama = mysqli_query($conn, "SELECT foto FROM barang WHERE id = '$id'");
                $row_lama = mysqli_fetch_assoc($res_lama);
                if (!empty($row_lama['foto']) && file_exists("images/" . $row_lama['foto'])) {
                    unlink("images/" . $row_lama['foto']);
                }

                file_put_contents($path_tujuan, base64_decode($foto_base64));
                
                $sql = "UPDATE barang SET nama = '$nama', stok = '$stok', harga_beli = '$harga_beli', 
                               harga_jual = '$harga_jual', kategori_id = '$kategori_id', sales_id = '$sales_id', foto = '$nama_file_foto' 
                        WHERE id = '$id'";
            } else {
                // Jika tidak edit foto, kolom foto diabaikan
                $sql = "UPDATE barang SET nama = '$nama', stok = '$stok', harga_beli = '$harga_beli', 
                               harga_jual = '$harga_jual', kategori_id = '$kategori_id', sales_id = '$sales_id' 
                        WHERE id = '$id'";
            }

            if (mysqli_query($conn, $sql)) {
                $respon['kode'] = "000";
            } else {
                $respon['kode'] = "333";
            }
            echo json_encode($respon);
            break;

        case "delete":
            $id = $_POST['id'];
            
            // Hapus file gambar fisik dari folder images sebelum data baris MySQL dihapus
            $res_lama = mysqli_query($conn, "SELECT foto FROM barang WHERE id = '$id'");
            $row_lama = mysqli_fetch_assoc($res_lama);
            if (!empty($row_lama['foto']) && file_exists("images/" . $row_lama['foto'])) {
                unlink("images/" . $row_lama['foto']);
            }

            $sql = "DELETE FROM barang WHERE id = '$id'";
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