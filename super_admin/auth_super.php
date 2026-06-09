<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
// Cek apakah yang login beneran super_admin
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'super_admin') {
    echo "<script>alert('Akses Ditolak! Anda bukan Super Admin.'); window.location='../login.php';</script>";
    exit();
}
?>