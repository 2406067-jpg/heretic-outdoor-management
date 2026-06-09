<?php
if (session_status() === PHP_SESSION_NONE) { 
    session_start(); 
}
include '../koneksi.php'; 

// 1. Cek keamanan akses POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo "<script>alert('Akses ilegal!'); window.location='index.php';</script>";
    exit();
}

// 2. Tangkap data sesuai dengan properti 'name' di form index.php Anda
$id_alat   = mysqli_real_escape_string($koneksi, $_POST['id_alat'] ?? '');
$rent_date = mysqli_real_escape_string($koneksi, $_POST['rent_date'] ?? '');
$lama_sewa = mysqli_real_escape_string($koneksi, $_POST['lama_sewa'] ?? '1');
$qty       = mysqli_real_escape_string($koneksi, $_POST['qty'] ?? '1'); 

// 3. Validasi: Jangan biarkan data penting kosong
if (empty($id_alat) || empty($rent_date)) {
    echo "<script>alert('Gagal! Data sewa tidak lengkap.'); window.history.back();</script>";
    exit();
}

// 4. Hitung tanggal kembali otomatis berdasarkan lama sewa (hari)
$return_date = date('Y-m-d', strtotime($rent_date . ' + ' . $lama_sewa . ' days'));

// 5. Simpan langsung ke dalam struktur session keranjang belanja Anda
$_SESSION['keranjang'][$id_alat] = [
    'qty'         => $qty,
    'rent_date'   => $rent_date,
    'return_date' => $return_date,
    'catatan'     => 'Sewa langsung dari katalog'
];

// 6. Redirect ke halaman keranjang belanja
echo "<script>
        alert('Item berhasil ditambahkan ke rencana sewa!'); 
        window.location='keranjang.php'; 
      </script>";
exit();
?>