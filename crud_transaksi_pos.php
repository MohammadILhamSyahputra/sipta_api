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
        case "get_barang":
            // Mengambil data barang langsung dari database pusat
            $sql = "SELECT id, kode_barang, nama, stok, harga_jual FROM barang";
            $result = mysqli_query($conn, $sql);
            $data = array();
            while ($row = mysqli_fetch_assoc($result)) {
                $data[] = $row;
            }
            echo json_encode($data);
            break;

        case "scan_single_barang":
            // Menggunakan $conn (sesuai filemu) dan fungsi asli mysqli_real_escape_string
            $kode = mysqli_real_escape_string($conn, $_POST['kode_barang']);
            
            $sql = "SELECT id, kode_barang, nama, stok, harga_jual FROM barang WHERE kode_barang = '$kode' LIMIT 1";
            $result = mysqli_query($conn, $sql);
            
            if (mysqli_num_rows($result) > 0) {
                $row = mysqli_fetch_assoc($result);
                
                $output = array(
                    "id" => (int)$row['id'],
                    "kode_barang" => $row['kode_barang'],
                    "nama" => $row['nama'],
                    "stok" => (int)$row['stok'],
                    "harga_jual" => (int)$row['harga_jual']
                );
                echo json_encode($output);
            } else {
                $outputGagal = array(
                    "status" => "gagal",
                    "pesan" => "Barang dengan kode '$kode' tidak terdaftar!"
                );
                echo json_encode($outputGagal);
            }
            break;

        case "proses_checkout":
            $total_harga = $_POST['total_harga'];
            $total_bayar = $_POST['total_bayar'];
            $kembalian   = $_POST['kembalian'];
            $tanggal     = date('Y-m-d H:i:s');
            
            // Decode string array JSON dari Android menjadi Array PHP
            $items_json  = $_POST['items_json'];
            $items       = json_decode($items_json, true);

            if (empty($items)) {
                $respon['kode'] = "444";
                $respon['pesan'] = "Keranjang kosong!";
                echo json_encode($respon);
                break;
            }

            // Mulai database transaction untuk menjaga konsistensi data
            mysqli_begin_transaction($conn);

            try {
                // 1. Insert ke tabel transaksi (sesuai image_6dc31f.png)
                $sqlTransaksi = "INSERT INTO transaksi (total_harga, total_bayar, kembalian, tanggal) 
                                 VALUES ('$total_harga', '$total_bayar', '$kembalian', '$tanggal')";
                
                if (!mysqli_query($conn, $sqlTransaksi)) {
                    throw new Exception("Gagal simpan header transaksi");
                }
                
                // Ambil ID transaksi yang baru saja digenerate
                $transaksi_id = mysqli_insert_id($conn);

                // 2. Loop insert ke detail_transaksi & Update potong stok barang
                foreach ($items as $item) {
                    $barang_id    = $item['barang_id'];
                    $qty          = $item['qty'];
                    $harga_satuan = $item['harga_satuan'];
                    $subtotal     = $item['subtotal'];

                    // Cek stok terbaru di database dulu untuk menghindari over-selling
                    $resCek = mysqli_query($conn, "SELECT stok FROM barang WHERE id = '$barang_id'");
                    $rowBarang = mysqli_fetch_assoc($resCek);
                    if ($rowBarang['stok'] < $qty) {
                        throw new Exception("Stok salah satu barang tidak mencukupi di server!");
                    }

                    // Insert ke detail_transaksi (sesuai image_6dc2c1.png)
                    $sqlDetail = "INSERT INTO detail_transaksi (transaksi_id, barang_id, qty, harga_satuan, subtotal) 
                                  VALUES ('$transaksi_id', '$barang_id', '$qty', '$harga_satuan', '$subtotal')";
                    
                    if (!mysqli_query($conn, $sqlDetail)) {
                        throw new Exception("Gagal simpan item detail");
                    }

                    // Update potong stok di tabel barang
                    $sqlUpdateStok = "UPDATE barang SET stok = stok - $qty WHERE id = '$barang_id'";
                    if (!mysqli_query($conn, $sqlUpdateStok)) {
                        throw new Exception("Gagal memotong stok barang");
                    }
                }

                // Jika semua berhasil tanpa error, commit ke database
                mysqli_commit($conn);
                $respon['kode'] = "000";
                $respon['pesan'] = "Transaksi Berhasil!";

            } catch (Exception $e) {
                // Jika ada satu saja yang gagal, batalkan semua proses
                mysqli_rollback($conn);
                $respon['kode'] = "333";
                $respon['pesan'] = $e->getMessage();
            }

            echo json_encode($respon);
            break;
    }
}
mysqli_close($conn);
?>