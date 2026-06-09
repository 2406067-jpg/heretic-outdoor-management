<?php
include '../koneksi.php';
if (session_status() === PHP_SESSION_NONE) { session_start(); }

// Proses Validasi Setuju atau Tolak dari Admin
if (isset($_GET['aksi']) && isset($_GET['id'])) {
    $id_transaksi = mysqli_real_escape_string($koneksi, $_GET['id']);
    $aksi = $_GET['aksi'];
    
    if ($aksi == 'setuju') {
        // Update status transaksi utama menjadi 'active' sesuai enum database
        mysqli_query($koneksi, "UPDATE transaksi SET status='active' WHERE id_transaksi='$id_transaksi'");
        
        // Ambil semua item yang ada di dalam transaksi ini untuk memotong stok fisik alat
        $get_detail = mysqli_query($koneksi, "SELECT product_id, quantity FROM detail_transaksi WHERE rental_id='$id_transaksi'");
        while ($detail = mysqli_fetch_assoc($get_detail)) {
            $p_id = $detail['product_id'];
            $qty  = $detail['quantity'];
            // Potong stok di tabel alat berdasarkan id_alat yang cocok dengan product_id
            mysqli_query($koneksi, "UPDATE alat SET stok = stok - $qty WHERE id_alat='$p_id'");
        }
    } else if ($aksi == 'tolak') {
        // Jika ditolak, kita hapus atau ubah status sesuai kebutuhan sistemmu
        mysqli_query($koneksi, "UPDATE transaksi SET status='completed' WHERE id_transaksi='$id_transaksi'");
    }
    
    header("Location: booking.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Booking Masuk - Heretic Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background-color: #f8f9fa; color: #495057; font-family: 'Plus Jakarta Sans', sans-serif; }
        .sidebar { height: 100vh; background-color: #ffffff; width: 260px; position: fixed; border-right: 1px solid #efeef4; }
        .brand-logo { padding: 30px 25px; display: flex; align-items: center; gap: 12px; }
        .logo-icon { width: 32px; height: 32px; background: linear-gradient(135deg, #ea4492, #9c27b0); border-radius: 8px; }
        .nav-link-custom { color: #8a8a9d; padding: 14px 25px; display: flex; align-items: center; gap: 15px; text-decoration: none; font-size: 0.95rem; font-weight: 500; border-left: 4px solid transparent; }
        .nav-link-custom:hover, .nav-link-custom.active { color: #ea4492; background: #fff5f8; border-left: 4px solid #ea4492; }
        .main-content { margin-left: 260px; padding: 40px; }
        .card-table { background: #ffffff; border: none; border-radius: 16px; padding: 25px; box-shadow: 0 10px 30px rgba(0,0,0,0.01); }
        .table thead th { background-color: #faf9fc; color: #7d7d91; font-weight: 600; font-size: 0.85rem; text-transform: uppercase; padding: 15px 20px; border: none; }
        .table tbody td { padding: 18px 20px; border-bottom: 1px solid #f1eff5; vertical-align: middle; }
    </style>
</head>
<body>

    <div class="sidebar">
        <div class="brand-logo">
            <div class="logo-icon"></div>
            <div>
                <h5 class="mb-0 fw-bold text-dark">HERETIC</h5>
                <small class="text-muted fw-semibold" style="font-size: 0.75rem;">ADMIN LAPANGAN</small>
            </div>
        </div>
        <div class="py-2">
            <a href="index.php" class="nav-link-custom"><i class="fa-solid fa-chart-pie"></i> Dashboard</a>
            <a href="booking.php" class="nav-link-custom active"><i class="fa-solid fa-bell"></i> Booking Masuk</a>
            <a href="master_alat.php" class="nav-link-custom"><i class="fa-solid fa-boxes-stacked"></i> Master Stok</a>
            <a href="transaksi.php" class="nav-link-custom"><i class="fa-solid fa-cart-flatbed"></i> Sewa Baru</a>
            <a href="pengembalian.php" class="nav-link-custom"><i class="fa-solid fa-rotate-left"></i> Pengembalian</a>
            <a href="../logout.php" class="nav-link-custom text-danger mt-5"><i class="fa-solid fa-power-off"></i> Keluar / Logout</a>
        </div>
    </div>

    <div class="main-content">
        <div class="mb-4">
            <h3 class="fw-bold text-dark mb-1">Persetujuan Booking Pelanggan</h3>
            <p class="text-muted">Periksa ketersediaan fisik alat sebelum memberikan izin sewa kepada member.</p>
        </div>

        <div class="card-table">
            <div class="table-responsive">
                <table class="table mb-0">
                    <thead>
                        <tr>
                            <th>Nota / Invoice</th>
                            <th>Pelanggan</th>
                            <th>Mulai</th>
                            <th>Kembali</th>
                            <th class="text-center">Aksi Pilihan</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        // FIX QUERY BARIS 94+: Join transaksi ke detail_transaksi lalu ke tabel alat
                        // Karena di enum status databasemu default-nya 'active', kita tampilkan data yang berstatus 'active'
                        $query = mysqli_query($koneksi, "SELECT t.*, d.quantity, a.nama_alat 
                                                         FROM transaksi t 
                                                         JOIN detail_transaksi d ON t.id_transaksi = d.rental_id 
                                                         JOIN alat a ON d.product_id = a.id_alat 
                                                         WHERE t.status='active' 
                                                         ORDER BY t.id_transaksi DESC");

                        if($query && mysqli_num_rows($query) > 0):
                            while($row = mysqli_fetch_assoc($query)):
                        ?>
                        <tr>
                            <td class="fw-bold text-dark"><?= htmlspecialchars($row['invoice_code']); ?></td>
                            <td class="text-secondary fw-semibold">
                                <?= htmlspecialchars($row['customer_name']); ?><br>
                                <small class="text-muted" style="font-size:0.8rem; font-weight:400;">
                                    Item: <?= htmlspecialchars($row['nama_alat']); ?> (<?= $row['quantity']; ?>x)
                                </small>
                            </td>
                            <td><span class="text-muted"><?= date('d/m/Y', strtotime($row['rent_date'])); ?></span></td>
                            <td><span class="text-muted"><?= date('d/m/Y', strtotime($row['return_date'])); ?></span></td>
                            <td class="text-center">
                                <a href="booking.php?aksi=setuju&id=<?= $row['id_transaksi']; ?>" class="btn btn-sm btn-success me-1">
                                    <i class="fa-solid fa-check"></i> Setujui
                                </a>
                                <a href="booking.php?aksi=tolak&id=<?= $row['id_transaksi']; ?>" onclick="return confirm('Tolak permohonan booking ini?')" class="btn btn-sm btn-outline-danger">
                                    <i class="fa-solid fa-xmark"></i> Tolak
                                </a>
                            </td>
                        </tr>
                        <?php endwhile; else: ?>
                        <tr>
                            <td colspan="5" class="text-center py-5 text-muted">
                                <i class="fa-solid fa-circle-check d-block fs-3 mb-2 text-muted" style="opacity:0.4;"></i>
                                Aman! Belum ada data booking aktif dari pelanggan.
                            </td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</body>
</html>