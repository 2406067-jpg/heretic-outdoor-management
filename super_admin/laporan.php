<?php
include '../koneksi.php';

// Proteksi halaman super_admin langsung secara inline agar anti-gagal
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'super_admin') {
    header("Location: ../login.php");
    exit();
}

// 1. Hitung total omset bersih keseluruhan dari seluruh transaksi
$q_omset = mysqli_query($koneksi, "SELECT SUM(total_bayar) as total FROM transaksi");
$d_omset = mysqli_fetch_assoc($q_omset);
$total_omset = $d_omset['total'] ?? 0;

// 2. Hitung jumlah total unit transaksi sukses (Berdasarkan jumlah baris transaksi)
$q_volume = mysqli_query($koneksi, "SELECT COUNT(*) as total FROM transaksi");
$d_volume = mysqli_fetch_assoc($q_volume);
$total_volume = $d_volume['total'] ?? 0;
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Finansial - Heretic Super Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { 
            background-color: #0b0d14; 
            color: #94a3b8; 
            font-family: 'Plus Jakarta Sans', sans-serif; 
            margin: 0; 
        }
        .sidebar { 
            height: 100vh; 
            background-color: #111422; 
            width: 260px; 
            position: fixed; 
            border-right: 1px solid #1e2235; 
        }
        .brand-logo { 
            padding: 30px 25px; 
        }
        .nav-link-custom { 
            color: #64748b; 
            padding: 14px 25px; 
            display: flex; 
            align-items: center; 
            gap: 15px; 
            text-decoration: none; 
            font-size: 0.95rem; 
            font-weight: 500; 
            transition: all 0.3s; 
        }
        .nav-link-custom:hover { 
            color: #ffffff; 
            background: #1a1f33; 
        }
        .nav-link-custom.active { 
            border-left: 4px solid #3d5afe; 
            color: #3d5afe; 
            background: rgba(61, 90, 254, 0.08); 
            font-weight: 600;
        }
        .main-content { 
            margin-left: 260px; 
            padding: 40px; 
        }
        
        /* Stats Dashboard Box */
        .card-stat { 
            background: #111422; 
            border: 1px solid #1e2235; 
            border-radius: 16px; 
            padding: 30px 25px; 
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.2);
        }
        
        /* Area List Transaksi Premium Dark */
        .report-container {
            background: #111422;
            border: 1px solid #1e2235;
            border-radius: 16px;
            padding: 30px;
            box-shadow: 0 4px 25px rgba(0, 0, 0, 0.25);
        }
        .report-header-title {
            color: #ffffff;
            font-size: 1.15rem;
            font-weight: 700;
            margin-bottom: 25px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        /* Desain Baris Transaksi Berwarna Gelap Elegan */
        .trx-row {
            background: #161b30;
            border: 1px solid #222947;
            border-radius: 12px;
            padding: 20px 25px;
            margin-bottom: 15px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            transition: transform 0.2s, border-color 0.2s;
        }
        .trx-row:hover {
            transform: translateY(-2px);
            border-color: #3d5afe;
        }
        .trx-customer-info {
            display: flex;
            align-items: center;
            gap: 15px;
        }
        .trx-icon-avatar {
            width: 45px;
            height: 45px;
            background: rgba(61, 90, 254, 0.15);
            color: #3d5afe;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.2rem;
        }
        .trx-name {
            color: #ffffff;
            font-weight: 600;
            margin-bottom: 2px;
            font-size: 1rem;
        }
        .trx-nota {
            font-size: 0.8rem;
            color: #64748b;
            font-family: monospace;
            background: #0b0d14;
            padding: 2px 8px;
            border-radius: 4px;
        }
        .trx-dates {
            display: flex;
            align-items: center;
            gap: 20px;
            color: #94a3b8;
            font-size: 0.9rem;
        }
        .date-box {
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .date-box i {
            color: #475569;
        }
        .trx-amount {
            color: #00cf96;
            font-weight: 700;
            font-size: 1.2rem;
            text-shadow: 0 0 10px rgba(0, 207, 150, 0.1);
        }

        @media (max-width: 992px) {
            .trx-row { flex-direction: column; align-items: start; gap: 15px; }
            .trx-amount { align-self: end; }
        }

        @media print {
            .sidebar, .btn-print { display: none !important; }
            .main-content { margin-left: 0 !important; padding: 0 !important; }
            body { background-color: #fff; color: #000; }
            .card-stat, .report-container { background: #fff; border: 1px solid #ddd; box-shadow: none; }
            .trx-row { background: #fff; border: 1px solid #ddd; color: #000; }
            .trx-name, .trx-amount { color: #000 !important; }
        }
    </style>
</head>
<body>

    <div class="sidebar">
        <div class="brand-logo">
            <h4 class="mb-0 fw-bold text-white" style="letter-spacing: 1px;">HERETIC</h4>
            <small class="text-primary fw-bold" style="font-size: 0.75rem; letter-spacing: 0.5px;">SUPER ADMIN</small>
        </div>
        <div class="py-2">
            <a href="index.php" class="nav-link-custom"><i class="fa-solid fa-chart-pie"></i> Analytics</a>
            <a href="master_alat.php" class="nav-link-custom"><i class="fa-solid fa-boxes-stacked"></i> Master Stok</a>
            <a href="transaksi.php" class="nav-link-custom"><i class="fa-solid fa-cart-flatbed"></i> Sewa Baru</a>
            <a href="pengembalian.php" class="nav-link-custom"><i class="fa-solid fa-rotate-left"></i> Pengembalian</a>
            <a href="laporan.php" class="nav-link-custom active"><i class="fa-solid fa-file-invoice-dollar"></i> Laporan</a>
        </div>
    </div>

    <div class="main-content">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2 class="fw-bold text-white mb-1">Laporan Finansial</h2>
                <p class="text-muted mb-0">Rekapitulasi omset bersih hasil transaksi selesai</p>
            </div>
            <button onclick="window.print()" class="btn btn-print text-white fw-semibold px-4 py-2" style="background-color: #161b30; border: 1px solid #222947; border-radius: 10px; transition: 0.3s;">
                <i class="fa-solid fa-print me-2"></i> Cetak PDF / Kertas
            </button>
        </div>

        <div class="row g-4 mb-4">
            <div class="col-md-6">
                <div class="card-stat" style="border-left: 4px solid #00cf96;">
                    <span class="text-muted small fw-bold text-uppercase d-block mb-2" style="letter-spacing: 0.5px;">Omset Bersih</span>
                    <h1 class="fw-bold mb-0" style="color: #00cf96;">Rp <?= number_format($total_omset, 0, ',', '.'); ?></h1>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card-stat" style="border-left: 4px solid #3d5afe;">
                    <span class="text-muted small fw-bold text-uppercase d-block mb-2" style="letter-spacing: 0.5px;">Volume Transaksi Sukses</span>
                    <h1 class="fw-bold text-white mb-0"><?= $total_volume; ?> <span class="fs-3 fw-normal" style="color:#64748b;">Invoice</span></h1>
                </div>
            </div>
        </div>

        <div class="report-container">
            <div class="report-header-title">
                <i class="fa-solid fa-clock-history text-primary"></i> Histori Transaksi Masuk
            </div>

            <?php
            // Memperbaiki pemanggilan: Mengambil data langsung dari tabel transaksi tanpa melakukan JOIN bermasalah ke tabel user
            $query = mysqli_query($koneksi, "SELECT * FROM transaksi ORDER BY id_transaksi DESC");
            if(mysqli_num_rows($query) > 0):
                while($row = mysqli_fetch_assoc($query)):
                    // Menyesuaikan pemanggilan nama dengan kolom nama_penyewa dari database
                    $nama_member = $row['nama_penyewa'] ?? 'Penyewa';
            ?>
            <div class="trx-row">
                <div class="trx-customer-info">
                    <div class="trx-icon-avatar">
                        <i class="fa-solid fa-user"></i>
                    </div>
                    <div>
                        <div class="trx-name"><?= htmlspecialchars($nama_member); ?></div>
                        <span class="trx-nota"><?= $row['nota_transaksi']; ?></span>
                    </div>
                </div>
                
                <div class="trx-dates">
                    <div class="date-box">
                        <i class="fa-regular fa-calendar-check"></i>
                        <span><?= date('d M Y', strtotime($row['tgl_sewa'])); ?></span>
                    </div>
                    <i class="fa-solid fa-arrow-right text-muted" style="font-size: 0.8rem;"></i>
                    <div class="date-box">
                        <i class="fa-regular fa-calendar-xmark"></i>
                        <span><?= date('d M Y', strtotime($row['tgl_kembali'])); ?></span>
                    </div>
                </div>

                <div class="trx-amount">
                    Rp <?= number_format($row['total_bayar'], 0, ',', '.'); ?>
                </div>
            </div>
            <?php 
                endwhile; 
            else: 
            ?>
            <div class="text-center py-5 text-muted">
                <i class="fa-solid fa-folder-open d-block fs-1 mb-3" style="color: #222947;"></i>
                Belum ada data transaksi masuk.
            </div>
            <?php endif; ?>
        </div>
    </div>

</body>
</html>