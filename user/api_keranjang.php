<?php
include '../koneksi.php';
if (session_status() === PHP_SESSION_NONE) { session_start(); }

if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = array();
}

$action = isset($_GET['action']) ? $_GET['action'] : '';

if ($action == 'add' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_alat = mysqli_real_escape_string($koneksi, $_POST['id_alat']);
    
    // Ambil data dari database untuk cek stok real-time
    $query = mysqli_query($koneksi, "SELECT * FROM alat WHERE id_alat = '$id_alat' LIMIT 1");
    $data_alat = mysqli_fetch_assoc($query);

    if ($data_alat) {
        // Jika item sudah ada di keranjang, tambah 1. Jika belum ada, mulai dari angka 1
        if (isset($_SESSION['cart'][$id_alat])) {
            if ($_SESSION['cart'][$id_alat]['qty'] < $data_alat['stok']) {
                $_SESSION['cart'][$id_alat]['qty'] += 1;
            } else {
                echo "<script>alert('Gagal! Jumlah sewa melebihi batas stok tersedia.'); window.location='index.php';</script>";
                exit();
            }
        } else {
            $_SESSION['cart'][$id_alat] = array(
                'id_alat' => $data_alat['id_alat'],
                'qty' => 1
            );
        }
        header("Location: keranjang.php");
        exit();
    }
}

// Fitur Hapus Item Tunggal di Keranjang
if ($action == 'delete' && isset($_GET['id'])) {
    $id_hapus = $_GET['id'];
    unset($_SESSION['cart'][$id_hapus]);
    header("Location: keranjang.php");
    exit();
}
?>