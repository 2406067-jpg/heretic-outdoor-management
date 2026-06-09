<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

function cek_akses($role_wajib) {
    if (!isset($_SESSION['username'])) {
        header("Location: ../login.php");
        exit();
    }
    
    if ($_SESSION['role'] !== $role_wajib) {
        echo "<script>alert('Anda tidak memiliki akses ke halaman ini!'); window.location='../login.php';</script>";
        exit();
    }
}
?>