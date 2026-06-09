<?php
// Fix path include: Memastikan file terkoneksi dengan database secara aman
include '../koneksi.php';
if (session_status() === PHP_SESSION_NONE) { session_start(); }

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 1. Ambil dan bersihkan input data pelanggan sesuai struktur tabel asli
    $customer_name  = mysqli_real_escape_string($koneksi, $_POST['customer_name']);
    $customer_email = mysqli_real_escape_string($koneksi, $_POST['customer_email']);
    $rent_date      = mysqli_real_escape_string($koneksi, $_POST['rent_date']);
    $return_date    = mysqli_real_escape_string($koneksi, $_POST['return_date']);
    
    // Penyesuaian nama variabel ke nama kolom database asli lu ( customer_phone & customer_address )
    $customer_phone   = mysqli_real_escape_string($koneksi, $_POST['customer_phone']);
    $jenis_jaminan    = mysqli_real_escape_string($koneksi, $_POST['jenis_jaminan']);
    $nomor_identitas  = mysqli_real_escape_string($koneksi, $_POST['nomor_identitas']);
    $metode_ambil     = mysqli_real_escape_string($koneksi, $_POST['metode_ambil']);
    $customer_address = ($metode_ambil === 'Diantar Kurir') ? mysqli_real_escape_string($koneksi, $_POST['customer_address']) : '-';
    $metode_bayar     = mysqli_real_escape_string($koneksi, $_POST['metode_bayar']);
    $catatan_tambahan = mysqli_real_escape_string($koneksi, $_POST['catatan_tambahan'] ?? '');
    $deposit          = 100000; 
    
    // Prosedur Upload Berkas File Dokumen Jaminan
    $target_dir = "../uploads/";
    if (!is_dir($target_dir)) {
        mkdir($target_dir, 0777, true);
    }

    $filename_id = "";
    if (!empty($_FILES['foto_identitas']['name'])) {
        $ext_id = pathinfo($_FILES['foto_identitas']['name'], PATHINFO_EXTENSION);
        $filename_id = "ID_" . time() . "_" . rand(100,999) . "." . $ext_id;
        move_uploaded_file($_FILES['foto_identitas']['tmp_name'], $target_dir . $filename_id);
    }

    $filename_selfie = "";
    if (!empty($_FILES['foto_selfie']['name'])) {
        $ext_selfie = pathinfo($_FILES['foto_selfie']['name'], PATHINFO_EXTENSION);
        $filename_selfie = "SLF_" . time() . "_" . rand(100,999) . "." . $ext_selfie;
        move_uploaded_file($_FILES['foto_selfie']['tmp_name'], $target_dir . $filename_selfie);
    }

    // Generate Kode Invoice Unik otomatis
    $invoice_code   = "HTC-" . strtoupper(substr(md5(time()), 0, 6));
    
    // 2. Hitung durasi hari sewa
    $tgl_mulai = new DateTime($rent_date);
    $tgl_akhir = new DateTime($return_date);
    $durasi_hari = $tgl_akhir->diff($tgl_mulai)->days;
    
    if ($durasi_hari <= 0) { 
        $durasi_hari = 1; 
    }
    
    // 3. Hitung total bayar dari array product form post
    $total_tarif_dasar = 0;
    if (isset($_POST['product_id'])) {
        foreach ($_POST['product_id'] as $key => $p_id) {
            $qty = (int)$_POST['quantity'][$key];
            $price = (int)$_POST['price_per_day'][$key];
            $total_tarif_dasar += ($price * $qty);
        }
    }
    
    // Akumulasi total akhir (Tarif Alat * Hari) + Deposit
    $total_bayar_akhir = ($total_tarif_dasar * $durasi_hari) + $deposit;

    // 4. INSERT UTAMA: Nama kolom disesuaikan 100% dengan urutan asli di HeidiSQL lu
    $query_transaksi = "INSERT INTO transaksi (
        invoice_code, customer_name, customer_phone, customer_email, customer_address, 
        jenis_jaminan, nomor_identitas, foto_identitas, foto_selfie, rent_date, 
        return_date, total_bayar, status, metode_ambil, metode_bayar, 
        total_amount, catatan_tambahan, deposit
    ) VALUES (
        '$invoice_code', '$customer_name', '$customer_phone', '$customer_email', '$customer_address', 
        '$jenis_jaminan', '$nomor_identitas', '$filename_id', '$filename_selfie', '$rent_date', 
        '$return_date', '$total_bayar_akhir', 'active', '$metode_ambil', '$metode_bayar', 
        '$total_bayar_akhir', '$catatan_tambahan', '$deposit'
    )";
    
    if (mysqli_query($koneksi, $query_transaksi)) {
        $rental_id = mysqli_insert_id($koneksi); 

        // 5. INSERT DETAIL: Simpan pecahan item ke tabel 'detail_transaksi'
        if (isset($_POST['product_id'])) {
            foreach ($_POST['product_id'] as $key => $p_id) {
                $p_id  = mysqli_real_escape_string($koneksi, $_POST['product_id'][$key]);
                $qty   = (int)$_POST['quantity'][$key];
                $price = (int)$_POST['price_per_day'][$key];
                $subtotal_item = $price * $qty * $durasi_hari;

                $query_detail = "INSERT INTO detail_transaksi (rental_id, product_id, price_per_day, quantity, days, subtotal, qty) 
                                 VALUES ('$rental_id', '$p_id', '$price', '$qty', '$durasi_hari', '$subtotal_item', '$qty')";
                
                mysqli_query($koneksi, $query_detail);
            }
        }
        
        // 6. Bersihkan session keranjang
        if (isset($_SESSION['keranjang'])) { unset($_SESSION['keranjang']); }
        if (isset($_SESSION['rent_date'])) { unset($_SESSION['rent_date']); }
        if (isset($_SESSION['return_date'])) { unset($_SESSION['return_date']); }
        if (isset($_SESSION['lama_sewa'])) { unset($_SESSION['lama_sewa']); }
        
        // Redirect langsung ke struk nota rincian invoice baru
        header("Location: invoice.php?id=" . $rental_id);
        exit();
        
    } else {
        echo "<div style='color:red; font-family:sans-serif; padding:20px; border:1px solid red; background:#fff5f5; border-radius:8px;'>";
        echo "<h3>Gagal Menyimpan Booking!</h3>";
        echo "<strong>Pesan Error MySQL:</strong> " . mysqli_error($koneksi);
        echo "</div>";
    }
} else {
    header("Location: keranjang.php");
    exit();
}
?>