<?php
include '../koneksi.php';
include 'auth_admin.php';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Master Stok - Heretic Admin</title>
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
        .table tbody td { padding: 18px 20px; border-bottom: 1px solid #f1eff5; vertical-align: middle; color: #495057; }
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
            <a href="master_alat.php" class="nav-link-custom active"><i class="fa-solid fa-boxes-stacked"></i> Master Stok</a>
            <a href="transaksi.php" class="nav-link-custom"><i class="fa-solid fa-cart-flatbed"></i> Sewa Baru</a>
            <a href="pengembalian.php" class="nav-link-custom"><i class="fa-solid fa-rotate-left"></i> Pengembalian</a>
            <a href="../logout.php" class="nav-link-custom text-danger mt-5"><i class="fa-solid fa-power-off"></i> Keluar / Logout</a>
        </div>
    </div>

    <div class="main-content">
        <div class="mb-4">
            <h3 class="fw-bold text-dark mb-1">Daftar Stok Alat</h3>
            <p class="text-muted">Informasi ketersediaan unit barang rental secara real-time.</p>
        </div>

        <div class="card-table">
            <div class="table-responsive">
                <table class="table mb-0">
                    <thead>
                        <tr>
                            <th>Nama Alat</th>
                            <th>Kategori</th>
                            <th>Harga Sewa / Hari</th>
                            <th class="text-center">Sisa Stok</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $query = mysqli_query($koneksi, "SELECT * FROM alat ORDER BY id_alat DESC");
                        if(mysqli_num_rows($query) > 0):
                            while($row = mysqli_fetch_assoc($query)):
                        ?>
                        <tr>
                            <td class="fw-bold text-dark"><?= htmlspecialchars($row['nama_alat']); ?></td>
                            <td><span class="badge bg-light text-secondary px-3 py-2 text-capitalize"><?= htmlspecialchars($row['kategori'] ?? 'Alat'); ?></span></td>
                            
                            <td class="fw-semibold text-secondary">Rp <?= number_format($row['harga'] ?? 0, 0, ',', '.'); ?></td>
                            
                            <td class="text-center">
                                <span class="badge <?= ($row['stok'] > 0) ? 'bg-success-subtle text-success' : 'bg-danger-subtle text-danger'; ?> px-3 py-2">
                                    <?= $row['stok']; ?> Unit Ready
                                </span>
                            </td>
                        </tr>
                        <?php endwhile; else: ?>
                        <tr>
                            <td colspan="4" class="text-center py-4 text-muted">Belum ada data barang sewa.</td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</body>
</html>