<?php
if (session_status() === PHP_SESSION_NONE) { 
    session_start(); 
}
include '../koneksi.php'; 

// Proteksi Request Method
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo "<script>alert('Akses ilegal!'); window.location='index.php';</script>";
    exit();
}

// Sanitasi Data Input Form Modal
$id_alat   = mysqli_real_escape_string($koneksi, $_POST['id_alat'] ?? '');
$rent_date = mysqli_real_escape_string($koneksi, $_POST['rent_date'] ?? '');
$lama_sewa = mysqli_real_escape_string($koneksi, $_POST['lama_sewa'] ?? '1');
$qty       = mysqli_real_escape_string($koneksi, $_POST['qty'] ?? '1'); 

// Validasi Kelengkapan Data
if (empty($id_alat) || empty($rent_date)) {
    echo "<script>alert('Gagal! Data sewa tidak lengkap.'); window.history.back();</script>";
    exit();
}

// Hitung Tanggal Pengembalian Otomatis
$return_date = date('Y-m-d', strtotime($rent_date . ' + ' . $lama_sewa . ' days'));

// -------------------------------------------------------------------------
// STANDARISASI SESSION: Amankan Angka Murni untuk Mencegah Error Perkalian
// -------------------------------------------------------------------------
$_SESSION['rent_date']   = $rent_date;
$_SESSION['return_date'] = $return_date;
$_SESSION['lama_sewa']   = (int)$lama_sewa;

// Simpan HANYA nilai quantity (integer murni) agar baris 76 keranjang.php tidak crash
$_SESSION['keranjang'][$id_alat] = (int)$qty; 

echo "<script>
        alert('Item berhasil ditambahkan ke keranjang!'); 
        window.location='keranjang.php'; 
      </script>";
exit();
?>