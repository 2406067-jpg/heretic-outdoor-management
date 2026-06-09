<?php
session_start();
session_destroy(); // Menghapus total semua isi keranjang yang rusak
echo "<script>alert('Session berhasil dibersihkan!'); window.location='index.php';</script>";
?>