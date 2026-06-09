<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Cek apakah session role sudah ada dan nilainya adalah 'admin'
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    // Jika tidak sesuai, arahkan kembali ke halaman login utama di luar folder
    header("Location: ../login.php");
    exit();
}
?>