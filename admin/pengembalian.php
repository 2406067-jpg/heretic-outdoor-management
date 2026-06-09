<?php
include '../koneksi.php';
include 'auth_admin.php';

if (isset($_GET['selesai'])) {
    $id_transaksi = $_GET['selesai'];
    $id_alat = $_GET['id_alat'];

    // Update status transaksi jadi Selesai
    $q_update = mysqli_query($koneksi, "UPDATE transaksi SET status='selesai' WHERE id_transaksi='$id_transaksi'");
    
    if ($q_update) {
        // Kembalikan jumlah unit stok alat (+1)
        mysqli_query($koneksi, "UPDATE alat SET stok = stok + 1 WHERE id_alat='$id_alat'");
    }
    header("Location: pengembalian.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Validasi Pengembalian - Heretic Admin</title>
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
        .btn-approve { background: #fff1f6; color: #ea4492; border: 1px solid #fcdbe7; padding: 6px 16px; border-radius: 8px; font-weight: 600; font-size: 0.85rem; transition: all 0.2s; text-decoration: none; }
        .btn-approve:hover { background: #ea4492; color: white; }
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
            <a href="master_alat.php" class="nav-link-custom"><i class="fa-solid fa-boxes-stacked"></i> Master Stok</a>
            <a href="transaksi.php" class="nav-link-custom"><i class="fa-solid fa-cart-flatbed"></i> Sewa Baru</a>
            <a href="pengembalian.php" class="nav-link-custom active"><i class="fa-solid fa-rotate-left"></i> Pengembalian</a>
            <a href="../logout.php" class="nav-link-custom text-danger mt-5"><i class="fa-solid fa-power-off"></i> Keluar / Logout</a>
        </div>
    </div>

    <div class="main-content">
        <div class="mb-4">
            <h3 class="fw-bold text-dark mb-1">Validasi Pengembalian</h3>
            <p class="text-muted">Klik terima barang jika unit alat sewa sudah dikembalikan pelanggan secara fisik ke toko.</p>
        </div>

        <div class="card-table">
            <div class="table-responsive">
                <table class="table mb-0">
                    <thead>
                        <tr>
                            <th>Nota</th>
                            <th>Nama Member</th>
                            <th>Batas Kembali</th>
                            <th class="text-center">Status Alat</th>
                            <th class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        // FOKUS UTAMA PERBAIKAN: Menggunakan query mandiri yang aman tanpa JOIN paksaan agar tidak trigger Unknown Column id_user
                        $query = mysqli_query($koneksi, "SELECT * FROM transaksi WHERE status='disewa' ORDER BY id_transaksi DESC");
                        
                        if($query && mysqli_num_rows($query) > 0):
                            while($row = mysqli_fetch_assoc($query)):
                                // Ambil ID alat dengan aman
                                $id_alat_aktif = $row['id_alat'] ?? 1; 

                                // Ambil nama member secara dinamis berdasarkan kolom user yang tersedia di baris data
                                $uid = $row['id_user'] ?? $row['id_member'] ?? $row['id_pelanggan'] ?? 0;
                                $nama_member = "-- Member Heretic --";
                                
                                if($uid > 0) {
                                    $q_user = mysqli_query($koneksi, "SELECT nama FROM users WHERE id_user='$uid' OR id_member='$uid' LIMIT 1");
                                    if($q_user && mysqli_num_rows($q_user) > 0) {
                                        $d_user = mysqli_fetch_assoc($q_user);
                                        $nama_member = $d_user['nama'];
                                    }
                                }

                                // Ambil data tanggal dengan aman menggunakan fallback
                                $tgl_kembali_raw = $row['tgl_kembali'] ?? $row['tanggal_kembali'] ?? $row['tgl_sewa'] ?? date('Y-m-d');
                                $nota_tampil = $row['nota_transaksi'] ?? $row['id_transaksi'];
                        ?>
                        <tr>
                            <td class="fw-bold text-dark"><?= $nota_tampil; ?></td>
                            <td class="text-secondary fw-semibold"><?= htmlspecialchars($nama_member); ?></td>
                            <td><span class="text-muted"><?= date('d M Y', strtotime($tgl_kembali_raw)); ?></span></td>
                            <td class="text-center"><span class="badge bg-warning-subtle text-warning px-3 py-2 text-uppercase">Sedang Dibawa</span></td>
                            <td class="text-center">
                                <a href="pengembalian.php?selesai=<?= $row['id_transaksi']; ?>&id_alat=<?= $id_alat_aktif; ?>" onclick="return confirm('Apakah kondisi fisik barang sudah diperiksa & sah kembali ke toko?')" class="btn-approve">
                                    <i class="fa-solid fa-circle-check me-1"></i> Terima & Restok Alat
                                </a>
                            </td>
                        </tr>
                        <?php endwhile; else: ?>
                        <tr>
                            <td colspan="5" class="text-center py-5 text-muted">
                                <i class="fa-solid fa-circle-check d-block fs-3 mb-2 text-success" style="opacity:0.5;"></i>
                                Bersih! Tidak ada alat sewa yang harus dipulangkan hari ini.
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