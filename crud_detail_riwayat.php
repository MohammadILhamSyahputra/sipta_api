<?php
date_default_timezone_set("Asia/Jakarta");

$DB_NAME = "sipta_mobile";
$DB_USER = "root";
$DB_PASS = ""; 
$DB_SERVER_LOC = "localhost";

$conn = mysqli_connect($DB_SERVER_LOC, $DB_USER, $DB_PASS, $DB_NAME);

// Atur header agar selalu membalas dalam bentuk JSON
header("Content-type: application/json; charset=UTF-8");

$respon = array();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $mode = $_POST['mode'];

    switch ($mode) {
        case "get_info":
            $id_riwayat = $_POST['riwayat_sales_id'];
            $sql = "SELECT s.id as sales_id, s.nama_sales, r.status, r.tanggal_kunjungan, r.created_at
                    FROM riwayat_sales r
                    JOIN sales s ON r.sales_id = s.id
                    WHERE r.id = '$id_riwayat'";
            $result = mysqli_query($conn, $sql);
            echo json_encode(mysqli_fetch_assoc($result));
            break;

        case "show_items":
            $id_riwayat = $_POST['riwayat_sales_id'];
            $sql = "SELECT d.id, b.nama, d.qty_masuk, d.qty_return
                    FROM detail_riwayat_sales d
                    JOIN barang b ON d.barang_id = b.id
                    WHERE d.riwayat_sales_id = '$id_riwayat'
                    ORDER BY d.id DESC";
            $result = mysqli_query($conn, $sql);
            $data = array();
            while ($row = mysqli_fetch_assoc($result)) {
                $data[] = $row;
            }
            echo json_encode($data);
            break;

        case "get_barang_spinner":
            $sales_id = $_POST['sales_id'];
            
            // EVALUASI KELOMPOK: Cek tabel 'barang' di phpMyAdmin.
            // Jika kolom relasi sales bernama 'id_sales', ganti menjadi: WHERE id_sales = '$sales_id'
            $sql = "SELECT id, nama FROM barang WHERE sales_id = '$sales_id' ORDER BY nama ASC";
            
            $result = mysqli_query($conn, $sql);
            $barang = array();
            if ($result) {
                while ($row = mysqli_fetch_assoc($result)) {
                    $barang[] = $row;
                }
            }
            echo json_encode($barang);
            break;

        case "get_one_item":
            $id_detail = $_POST['id_detail'];
            $sql = "SELECT qty_masuk, qty_return FROM detail_riwayat_sales WHERE id = '$id_detail'";
            $result = mysqli_query($conn, $sql);
            echo json_encode(mysqli_fetch_assoc($result));
            break;

        case "insert_item":
            $id_riwayat = $_POST['riwayat_sales_id'];
            $barang_id = $_POST['barang_id'];
            $qty_masuk = empty($_POST['qty_masuk']) ? 0 : intval($_POST['qty_masuk']);
            $qty_return = empty($_POST['qty_return']) ? 0 : intval($_POST['qty_return']); // Menerima parameter dari Android

            // 1. Jalankan Query INSERT ke tabel detail_riwayat_sales
            $sql = "INSERT INTO detail_riwayat_sales (riwayat_sales_id, barang_id, qty_masuk, qty_return)
                    VALUES ('$id_riwayat', '$barang_id', '$qty_masuk', '$qty_return')";
            
            if (mysqli_query($conn, $sql)) {
                
                // 2. KUNCI OTOMATISASI: Update kalkulasi stok langsung ke tabel barang
                // Rumus: Stok Baru = Stok Lama + Qty Masuk - Qty Return
                $sql_update_stok = "UPDATE barang 
                                    SET stok = stok + $qty_masuk - $qty_return 
                                    WHERE id = '$barang_id'";
                
                mysqli_query($conn, $sql_update_stok);

                $respon['kode'] = "000";
                $respon['pesan'] = "Barang berhasil disimpan dan stok barang diperbarui";
            } else {
                $respon['kode'] = "333";
                $respon['pesan'] = "Eror MySQL: " . mysqli_error($conn); 
            }
            echo json_encode($respon);
            break;

        case "update_item":
            $id_detail = $_POST['id_detail'];
            $qty_masuk_baru = empty($_POST['qty_masuk']) ? 0 : intval($_POST['qty_masuk']);
            $qty_return_baru = empty($_POST['qty_return']) ? 0 : intval($_POST['qty_return']);

            // 1. Ambil data kuantitas lama terlebih dahulu untuk kalkulasi balik stok barang
            $q_lama = mysqli_query($conn, "SELECT barang_id, qty_masuk, qty_return FROM detail_riwayat_sales WHERE id = '$id_detail'");
            $d_lama = mysqli_fetch_assoc($q_lama);
            
            if ($d_lama) {
                $barang_id = $d_lama['barang_id'];
                $qty_masuk_lama = intval($d_lama['qty_masuk']);
                $qty_return_lama = intval($d_lama['qty_return']);

                // 2. Kembalikan stok barang ke kondisi semula sebelum diedit
                $sql_rollback = "UPDATE barang 
                                 SET stok = stok - $qty_masuk_lama + $qty_return_lama 
                                 WHERE id = '$barang_id'";
                mysqli_query($conn, $sql_rollback);

                // 3. Update data baru ke tabel detail_riwayat_sales
                $sql_update_detail = "UPDATE detail_riwayat_sales 
                                      SET qty_masuk = '$qty_masuk_baru', qty_return = '$qty_return_baru' 
                                      WHERE id = '$id_detail'";
                
                if (mysqli_query($conn, $sql_update_detail)) {
                    
                    // 4. Hitung dan masukkan manipulasi stok yang baru
                    $sql_stok_baru = "UPDATE barang 
                                      SET stok = stok + $qty_masuk_baru - $qty_return_baru 
                                      WHERE id = '$barang_id'";
                    mysqli_query($conn, $sql_stok_baru);

                    $respon['kode'] = "000";
                } else {
                    $respon['kode'] = "333";
                    $respon['pesan'] = "Eror Update: " . mysqli_error($conn);
                }
            } else {
                $respon['kode'] = "333";
                $respon['pesan'] = "Data riwayat lama tidak ditemukan";
            }
            echo json_encode($respon);
            break;
    }
}
mysqli_close($conn);
?>