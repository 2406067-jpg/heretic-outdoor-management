<?php
include '../koneksi.php';
include '../auth.php';
cek_akses('super_admin');

if(isset($_GET['proses_kembali'])) {
    $id_trx = $_GET['proses_kembali'];

    // 1. Kembalikan jumlah stok barang ke master alat otomatis
    $q_detail = mysqli_query($koneksi, "SELECT id_alat, jumlah FROM detail_transaksi WHERE id_transaksi='$id_trx'");
    while($dt = mysqli_fetch_assoc($q_detail)) {
        $id_alat = $dt['id_alat'];
        $qty = $dt['jumlah'];
        mysqli_query($koneksi, "UPDATE alat_rental SET stok = stok + $qty WHERE id_alat='$id_alat'");
    }

    // 2. Ubah status transaksi jadi selesai
    mysqli_query($koneksi, "UPDATE transaksi SET status='selesai' WHERE id_transaksi='$id_trx'");
    echo "<script>alert('Sewa Selesai! Stok alat berhasil dikembalikan ke Gudang.'); window.location='pengembalian.php';</script>";
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modul Pengembalian Asset - Heretic</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        :root { 
            --bg-main: #0f0f1a; 
            --bg-card: #141424; 
            --text-main: #a2a2b3; 
            --text-light: #ffffff; 
            --accent-purple: #8950fc; 
            --border-color: rgba(255, 255, 255, 0.05); 
        }
        body { background-color: var(--bg-main); color: var(--text-main); font-family: 'Inter', sans-serif; }
        
        /* Sidebar Layout (Disamakan dengan image_a5bad1.png) */
        .sidebar { height: 100vh; background-color: var(--bg-card); width: 260px; position: fixed; border-right: 1px solid var(--border-color); z-index: 99; }
        .brand-logo { padding: 25px; display: flex; align-items: center; gap: 12px; border-bottom: 1px solid var(--border-color); }
        .logo-icon { width: 35px; height: 35px; background: linear-gradient(135deg, #ffc107, #ff9800); border-radius: 8px; }
        .nav-link-custom { color: var(--text-main); padding: 12px 25px; display: flex; align-items: center; gap: 15px; text-decoration: none; font-size: 0.9rem; transition: all 0.3s; border-left: 3px solid transparent; }
        .nav-link-custom:hover, .nav-link-custom.active { color: var(--text-light); background: rgba(137, 80, 252, 0.08); border-left: 3px solid var(--accent-purple); }
        
        /* Content Wrapper */
        .main-content { margin-left: 260px; padding: 40px; }
        
        /* Reset Table & Custom Premium Card */
        .card-custom { background-color: var(--bg-card); border: 1px solid var(--border-color); border-radius: 14px; box-shadow: 0px 10px 30px rgba(0,0,0,0.2); overflow: hidden; }
        .table-responsive { margin: 0; }
        
        .table-custom { width: 100%; margin-bottom: 0; color: var(--text-main); vertical-align: middle; border-collapse: collapse; }
        .table-custom th { background-color: rgba(255, 255, 255, 0.02) !important; color: #fff !important; font-weight: 600; font-size: 0.85rem; text-uppercase: true; letter-spacing: 0.5px; padding: 18px 24px; border-bottom: 1px solid var(--border-color); }
        .table-custom td { padding: 18px 24px; border-bottom: 1px solid var(--border-color); color: var(--text-main); background-color: transparent !important; }
        .table-custom tr:last-child td { border-bottom: none; }
        .table-custom tr:hover td { background-color: rgba(255, 255, 255, 0.01) !important; }

        /* Button & Badge styling */
        .btn-success-custom { background-color: #28a745; border: none; color: white; padding: 8px 16px; border-radius: 6px; font-weight: 500; font-size: 0.85rem; transition: opacity 0.2s; text-decoration: none; display: inline-block; }
        .btn-success-custom:hover { opacity: 0.9; color: white; }
    </style>
</head>
<body>

    <div class="sidebar">
        <div class="brand-logo">
            <div class="logo-icon"></div>
            <div>
                <h5 class="mb-0 fw-bold text-white" style="letter-spacing: 1px;">HERETIC RENTAL</h5>
                <small style="font-size: 0.7rem; color: var(--accent-purple); font-weight: 700;">SUPER ADMIN PANELS</small>
            </div>
        </div>
        <div class="py-3">
            <p class="px-4 text-uppercase mb-2 mt-2" style="font-size: 0.75rem; letter-spacing: 1px; color: rgba(255,255,255,0.2);">Dashboards</p>
            <a href="index.php" class="nav-link-custom"><i class="fa-solid fa-chart-pie"></i> Dashboard</a>
            <a href="master_alat.php" class="nav-link-custom"><i class="fa-solid fa-boxes-stacked"></i> Master Alat (Stok)</a>
            <a href="transaksi.php" class="nav-link-custom"><i class="fa-solid fa-cart-flatbed"></i> Transaksi Baru</a>
            <a href="pengembalian.php" class="nav-link-custom active"><i class="fa-solid fa-rotate-left"></i> Pengembalian Barang</a>
            <p class="px-4 text-uppercase mb-2 mt-4" style="font-size: 0.75rem; letter-spacing: 1px; color: rgba(255,255,255,0.2);">Management</p>
            <a href="manajemen_user.php" class="nav-link-custom"><i class="fa-solid fa-users"></i> Kelola User</a>
            <a href="laporan.php" class="nav-link-custom"><i class="fa-solid fa-file-invoice-dollar"></i> Laporan Keuangan</a>
            <a href="../logout.php" class="nav-link-custom text-danger mt-4"><i class="fa-solid fa-power-off"></i> Keluar / Logout</a>
        </div>
    </div>

    <div class="main-content">
        <div class="mb-4">
            <h3 class="fw-bold text-white mb-1">Modul Validasi Pengembalian Barang</h3>
            <small class="text-white-50">Selesaikan status sewa dan pulihkan sisa stok logistik masuk</small>
        </div>

        <div class="card-custom">
            <div class="table-responsive">
                <table class="table-custom">
                    <thead>
                        <tr>
                            <th>Nota</th>
                            <th>Nama Pelanggan</th>
                            <th>Tgl Pinjam</th>
                            <th>Batas Kembali</th>
                            <th>Total Tagihan</th>
                            <th class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $query = mysqli_query($koneksi, "SELECT * FROM transaksi WHERE status='disewa' ORDER BY id_transaksi DESC");
                        if(mysqli_num_rows($query) > 0):
                            while($row = mysqli_fetch_assoc($query)):
                        ?>
                        <tr>
                            <td class="text-warning fw-bold" style="letter-spacing: 0.5px;"><?= htmlspecialchars($row['nota_transaksi']); ?></td>
                            <td class="text-white fw-medium"><?= htmlspecialchars($row['nama_penyewa']); ?></td>
                            <td><?= date('d-m-Y', strtotime($row['tgl_sewa'])); ?></td>
                            <td class="text-warning fw-medium"><?= date('d-m-Y', strtotime($row['tgl_kembali'])); ?></td>
                            <td class="text-white fw-semibold">Rp <?= number_format($row['total_bayar'], 0, ',', '.'); ?></td>
                            <td class="text-center">
                                <a href="pengembalian.php?proses_kembali=<?= $row['id_transaksi']; ?>" onclick="return confirm('Apakah semua barang sewaan nota ini sudah kembali dengan utuh?')" class="btn btn-success-custom">
                                    <i class="fa-solid fa-square-check me-2"></i>Setujui Pengembalian
                                </a>
                            </td>
                        </tr>
                        <?php endwhile; else: ?>
                        <tr>
                            <td colspan="6" class="text-center py-5 text-muted">
                                <i class="fa-solid fa-circle-check d-block fs-2 mb-3 text-success" style="opacity: 0.6;"></i>
                                Hebat! Semua barang sewaan sudah kembali ke gudang (Tidak ada sewa aktif).
                            </td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>