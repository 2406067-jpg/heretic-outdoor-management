<?php
include '../koneksi.php';
// pastikan session admin aktif, jika belum ada auth_admin.php, lu bisa bypass atau sesuaikan line ini
include 'auth_admin.php'; 

// =========================================================================
// 1. PROSES AKSI MANIPULASI DATA & TRANSACTION (DARI TOMBOL-TOMBOL ADMIN)
// =========================================================================
if (isset($_GET['action']) && isset($_GET['id'])) {
    $id_target = mysqli_real_escape_string($koneksi, $_GET['id']);
    $action    = $_GET['action'];

    // --- AKSI VERIFIKASI SEWA ---
    if ($action == 'setujui_booking') {
        // Mengubah status dari 'Menunggu Verifikasi' menjadi tahapan berikutnya 'Menunggu Pembayaran'
        mysqli_query($koneksi, "UPDATE transaksi SET status='Menunggu Pembayaran' WHERE id_transaksi='$id_target'");
        echo "<script>alert('Booking ID #$id_target Berhasil Disetujui! Status beralih ke Menunggu Pembayaran.'); window.location='index.php';</script>";
    } 
    elseif ($action == 'tolak_booking') {
        mysqli_query($koneksi, "UPDATE transaksi SET status='Ditolak' WHERE id_transaksi='$id_target'");
        echo "<script>alert('Booking ID #$id_target telah Ditolak.'); window.location='index.php';</script>";
    } 
    // --- AKSI VERIFIKASI PEMBAYARAN FINANSIAL ---
    elseif ($action == 'terima_bayar') {
        mysqli_query($koneksi, "UPDATE transaksi SET status='Disetujui' WHERE id_transaksi='$id_target'");
        echo "<script>alert('Dana Masuk Sah! Status diubah menjadi Disetujui. Siap diambil user.'); window.location='index.php';</script>";
    } 
    // --- AKSI DISTRIBUSI LOGISTIK BARANG ---
    elseif ($action == 'mulai_sewa') {
        mysqli_query($koneksi, "UPDATE transaksi SET status='Sedang Disewa' WHERE id_transaksi='$id_target'");
        echo "<script>alert('Unit alat resmi diserahkan ke pelanggan! Status: Sedang Disewa.'); window.location='index.php';</script>";
    } 
    // --- AKSI PENGEMBALIAN BARANG + RETURN GUDANG + CALCULATE DENDA AUTOMATIC ---
    elseif ($action == 'proses_kembali') {
        // 1. Ambil data tanggal kembali untuk hitung keterlambatan denda
        $q_cek = mysqli_query($koneksi, "SELECT return_date FROM transaksi WHERE id_transaksi='$id_target'");
        $d_cek = mysqli_fetch_assoc($q_cek);
        
        $tgl_kembali  = strtotime($d_cek['return_date']);
        $tgl_sekarang = strtotime(date('Y-m-d'));
        $total_denda  = 0;
        
        if ($tgl_sekarang > $tgl_kembali) {
            $selisih_detik = $tgl_sekarang - $tgl_kembali;
            $selisih_hari  = round($selisih_detik / (60 * 60 * 24));
            $total_denda   = $selisih_hari * 20000; // Formula denda real-time Rp 20.000 / hari
        }

        // 2. Kembalikan stok armada alat secara otomatis berdasar detail_transaksi
        $q_loop_item = mysqli_query($koneksi, "SELECT product_id, qty FROM detail_transaksi WHERE rental_id='$id_target'");
        while ($item = mysqli_fetch_assoc($q_loop_item)) {
            $id_alat = $item['product_id'];
            $qty_ret = $item['qty'];
            mysqli_query($koneksi, "UPDATE alat SET stok = stok + $qty_ret WHERE id_alat='$id_alat'");
        }

        // 3. Update status final transaksi sewa
        mysqli_query($koneksi, "UPDATE transaksi SET status='Selesai', deposit='$total_denda' WHERE id_transaksi='$id_target'");
        
        if ($total_denda > 0) {
            echo "<script>alert('Pengembalian Terdeteksi Terlambat! Unit kembali ke gudang, denda Rp " . number_format($total_denda, 0, ',', '.') . " ditambahkan ke sistem.'); window.location='index.php';</script>";
        } else {
            echo "<script>alert('Unit Alat Kembali Tepat Waktu. Transaksi Selesai & Ditutup.'); window.location='index.php';</script>";
        }
    }
}

// =========================================================================
// 2. PROSES OPERASI CRUD MASTER DATA ALAT (TAMBAH, EDIT, HAPUS)
// =========================================================================
if (isset($_POST['tambah_alat'])) {
    $nama_alat     = mysqli_real_escape_string($koneksi, $_POST['nama_alat']);
    $kategori      = mysqli_real_escape_string($koneksi, $_POST['kategori']);
    $harga_perhari = mysqli_real_escape_string($koneksi, $_POST['harga_perhari']);
    $stok          = mysqli_real_escape_string($koneksi, $_POST['stok']);
    $deskripsi     = mysqli_real_escape_string($koneksi, $_POST['deskripsi']);
    
    // Upload handler gambar armada
    $gambar = $_FILES['gambar']['name'];
    $tmp    = $_FILES['gambar']['tmp_name'];
    $path   = "../uploads/alat/" . $gambar;
    
    if (move_uploaded_file($tmp, $path)) {
        mysqli_query($koneksi, "INSERT INTO alat (nama_alat, kategori, harga_perhari, stok, gambar, deskripsi) VALUES ('$nama_alat', '$kategori', '$harga_perhari', '$stok', '$gambar', '$deskripsi')");
        echo "<script>alert('Armada Alat Baru Berhasil Disimpan!'); window.location='index.php#data-alat';</script>";
    }
}

if (isset($_POST['edit_alat'])) {
    $id_alat       = mysqli_real_escape_string($koneksi, $_POST['id_alat']);
    $nama_alat     = mysqli_real_escape_string($koneksi, $_POST['nama_alat']);
    $kategori      = mysqli_real_escape_string($koneksi, $_POST['kategori']);
    $harga_perhari = mysqli_real_escape_string($koneksi, $_POST['harga_perhari']);
    $stok          = mysqli_real_escape_string($koneksi, $_POST['stok']);
    $deskripsi     = mysqli_real_escape_string($koneksi, $_POST['deskripsi']);
    
    if ($_FILES['gambar']['name'] != "") {
        $gambar = $_FILES['gambar']['name'];
        $tmp    = $_FILES['gambar']['tmp_name'];
        move_uploaded_file($tmp, "../uploads/alat/" . $gambar);
        mysqli_query($koneksi, "UPDATE alat SET nama_alat='$nama_alat', kategori='$kategori', harga_perhari='$harga_perhari', stok='$stok', gambar='$gambar', deskripsi='$deskripsi' WHERE id_alat='$id_alat'");
    } else {
        mysqli_query($koneksi, "UPDATE alat SET nama_alat='$nama_alat', kategori='$kategori', harga_perhari='$harga_perhari', stok='$stok', deskripsi='$deskripsi' WHERE id_alat='$id_alat'");
    }
    echo "<script>alert('Data Alat Berhasil Diperbarui!'); window.location='index.php#data-alat';</script>";
}

if (isset($_GET['delete_alat'])) {
    $id_del = mysqli_real_escape_string($koneksi, $_GET['delete_alat']);
    mysqli_query($koneksi, "DELETE FROM alat WHERE id_alat='$id_del'");
    echo "<script>alert('Data Alat Berhasil Dihapus dari Database!'); window.location='index.php#data-alat';</script>";
}

// =========================================================================
// 3. AGREGASI DATA STATISTIK UNTUK DASHBOARD UTAMA
// =========================================================================
// Total Alat Keseluruhan
$r_tot_alat  = mysqli_query($koneksi, "SELECT COUNT(*) as total FROM alat");
$d_tot_alat  = mysqli_fetch_assoc($r_tot_alat);
$stat_alat   = $d_tot_alat['total'] ?? 0;

// Alat Keluar / Sedang Disewa
$r_out_alat  = mysqli_query($koneksi, "SELECT SUM(dt.qty) as total FROM detail_transaksi dt JOIN transaksi t ON dt.rental_id=t.id_transaksi WHERE t.status='Sedang Disewa'");
$d_out_alat  = mysqli_fetch_assoc($r_out_alat);
$stat_sewa   = $d_out_alat['total'] ?? 0;

// Akumulasi Finansial Pendapatan Bulan Berjalan
$curr_month  = date('m');
$curr_year   = date('Y');
$r_income    = mysqli_query($koneksi, "SELECT SUM(total_bayar) as total FROM transaksi WHERE status='Selesai' AND MONTH(rent_date)='$curr_month' AND YEAR(rent_date)='$curr_year'");
$d_income    = mysqli_fetch_assoc($r_income);
$stat_income = $d_income['total'] ?? 0;

// Jumlah Akun Terdaftar (Pelanggan)
$r_cust      = mysqli_query($koneksi, "SELECT COUNT(*) as total FROM user WHERE role='user'");
$d_cust      = mysqli_fetch_assoc($r_cust);
$stat_cust   = $d_cust['total'] ?? 0;
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistem Kontrol Server - Heretic Premium Rental</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <style>
        :root {
            --slate-dark: #0f172a;
            --slate-card: #1e293b;
            --accent-pink: #ea4492;
            --accent-purple: #9c27b0;
            --bg-light: #f8fafc;
            --border-color: #e2e8f0;
        }
        
        body { 
            background-color: var(--bg-light); 
            color: #334155; 
            font-family: 'Plus Jakarta Sans', sans-serif; 
            margin: 0;
            scroll-behavior: smooth;
        }

        /* Sidebar Interface Styler */
        .sidebar-panel { 
            height: 100vh; 
            background-color: var(--slate-dark); 
            width: 280px; 
            position: fixed; 
            overflow-y: auto; 
            z-index: 1000;
            border-right: 1px solid rgba(255,255,255,0.05);
        }
        .panel-brand { 
            padding: 30px 25px; 
            display: flex; 
            align-items: center; 
            gap: 12px; 
            border-bottom: 1px solid var(--slate-card); 
        }
        .brand-avatar { 
            width: 36px; 
            height: 36px; 
            background: linear-gradient(135deg, var(--accent-pink), var(--accent-purple)); 
            border-radius: 10px; 
        }
        .nav-section-title { 
            font-size: 0.72rem; 
            text-transform: uppercase; 
            letter-spacing: 1.2px; 
            color: #475569; 
            padding: 22px 25px 8px 25px; 
            font-weight: 700; 
        }
        .menu-item { 
            color: #94a3b8; 
            padding: 12px 25px; 
            display: flex; 
            align-items: center; 
            gap: 12px; 
            text-decoration: none; 
            font-size: 0.88rem; 
            font-weight: 500; 
            transition: all 0.2s ease-in-out; 
        }
        .menu-item:hover { 
            color: #f8fafc; 
            background: rgba(255,255,255,0.04); 
        }
        .menu-item.active { 
            color: #ffffff; 
            background: linear-gradient(90deg, var(--accent-pink), var(--accent-purple)); 
            font-weight: 600; 
            border-radius: 8px;
            margin: 0 15px;
            padding: 12px 15px;
        }
        .menu-sub-item { 
            padding-left: 20px; 
        }

        /* Content Area Layout */
        .main-workspace { 
            margin-left: 280px; 
            padding: 45px; 
            min-height: 100vh; 
        }
        
        /* Metric Card Blocks */
        .metric-card { 
            background: #ffffff; 
            border: none; 
            border-radius: 16px; 
            padding: 26px; 
            box-shadow: 0 4px 20px rgba(0,0,0,0.02); 
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .metric-icon { 
            width: 54px; 
            height: 54px; 
            border-radius: 14px; 
            display: flex; 
            align-items: center; 
            justify-content: center; 
            font-size: 1.35rem; 
        }
        
        /* Data Presentation Elements */
        .content-block-card { 
            background: #ffffff; 
            border: none; 
            border-radius: 16px; 
            box-shadow: 0 4px 25px rgba(0,0,0,0.02); 
            margin-bottom: 35px;
            overflow: hidden;
            border: 1px solid #f1f5f9;
        }
        .block-card-header { 
            padding: 20px 25px; 
            background: #ffffff;
            border-bottom: 1px solid var(--border-color); 
            display: flex; 
            justify-content: space-between; 
            align-items: center; 
        }
        
        .custom-data-table th { 
            background-color: #f8fafc; 
            color: #64748b; 
            font-weight: 600; 
            text-transform: uppercase; 
            font-size: 0.75rem; 
            letter-spacing: 0.5px; 
            padding: 16px 20px; 
            border-bottom: 1px solid var(--border-color);
        }
        .custom-data-table td { 
            padding: 16px 20px; 
            vertical-align: middle; 
            font-size: 0.88rem; 
            color: #334155;
        }

        /* Status Label Badges */
        .badge-status { 
            padding: 6px 14px; 
            border-radius: 50px; 
            font-size: 0.75rem; 
            font-weight: 600; 
            display: inline-block; 
            text-transform: capitalize;
        }
        .badge-waiting { background-color: #fef3c7; color: #d97706; }
        .badge-pay { background-color: #e0f2fe; color: #0369a1; }
        .badge-approved { background-color: #e0e7ff; color: #4f46e5; }
        .badge-active { background-color: #dcfce7; color: #16a34a; }
        .badge-closed { background-color: #f1f5f9; color: #64748b; }
        
        .thumbnail-alat {
            width: 55px; height: 55px; object-fit: cover; border-radius: 8px; border: 1px solid var(--border-color);
        }
    </style>
</head>
<body>

    <div class="sidebar-panel">
        <div class="panel-brand">
            <div class="brand-avatar"></div>
            <div>
                <h5 class="mb-0 fw-bold text-white" style="letter-spacing: 0.5px;">HERETIC CORE</h5>
                <small class="text-muted fw-semibold" style="font-size: 0.7rem;">SINKRONISASI AKTIF</small>
            </div>
        </div>
        
        <div class="py-3">
            <a href="#dashboard" class="menu-item active"><i class="fa-solid fa-chart-line"></i> Dashboard</a>
            
            <div class="nav-section-title">Master Data Toko</div>
            <div class="menu-sub-item">
                <a href="#data-alat" class="menu-item"><i class="fa-solid fa-camera"></i> Kelola Data Alat</a>
                <a href="#pelanggan" class="menu-item"><i class="fa-solid fa-user-shield"></i> Kelola Pelanggan</a>
            </div>

            <div class="nav-section-title">Workflow Transaksi Live</div>
            <div class="menu-sub-item">
                <?php 
                $c_booking = mysqli_query($koneksi, "SELECT COUNT(*) as total FROM transaksi WHERE status='Menunggu Verifikasi'");
                $d_c_book  = mysqli_fetch_assoc($c_booking);
                $count_book = $d_c_book['total'] ?? 0;
                ?>
                <a href="#booking-masuk" class="menu-item d-flex justify-content-between align-items-center">
                    <span><i class="fa-solid fa-clock"></i> 1. Booking Masuk</span>
                    <?php if($count_book > 0): ?><span class="badge bg-warning text-dark font-monospace rounded-pill px-2"><?=$count_book?></span><?php endif; ?>
                </a>

                <?php 
                $c_bayar = mysqli_query($koneksi, "SELECT COUNT(*) as total FROM transaksi WHERE status='Dibayar'");
                $d_c_bayar = mysqli_fetch_assoc($c_bayar);
                $count_bayar = $d_c_bayar['total'] ?? 0;
                ?>
                <a href="#verifikasi-pembayaran" class="menu-item d-flex justify-content-between align-items-center">
                    <span><i class="fa-solid fa-receipt"></i> 2. Verifikasi Bayar</span>
                    <?php if($count_bayar > 0): ?><span class="badge bg-info text-white font-monospace rounded-pill px-2"><?=$count_bayar?></span><?php endif; ?>
                </a>

                <a href="#penyewaan-aktif" class="menu-item"><i class="fa-solid fa-person-running"></i> 3. Sewa Aktif Lapangan</a>
                <a href="#pengembalian-barang" class="menu-item"><i class="fa-solid fa-box-open"></i> 4. Pengembalian & Denda</a>
            </div>

            <div class="nav-section-title">Modul Pelaporan</div>
            <div class="menu-sub-item">
                <a href="#laporan-panel" class="menu-item"><i class="fa-solid fa-wallet"></i> Laporan Finansial</a>
                <a href="#laporan-panel" class="menu-item"><i class="fa-solid fa-fire"></i> Alat Terlaris</a>
            </div>

            <div class="nav-section-title">Sistem Otoritas</div>
            <a href="../logout.php" class="menu-item text-danger mt-4"><i class="fa-solid fa-power-off"></i> Terminasi / Logout</a>
        </div>
    </div>

    <div class="main-workspace" id="dashboard">
        
        <div class="d-flex justify-content-between align-items-center mb-5">
            <div>
                <h2 class="fw-bold text-dark mb-1">Pusat Kendali Operasional</h2>
                <p class="text-muted mb-0">Pemantauan otomatis data interaksi sewa *customer* secara langsung.</p>
            </div>
            <div class="bg-white border px-4 py-2 rounded-3 shadow-sm text-secondary fw-semibold">
                <i class="fa-solid fa-satellite-dish text-success me-2 animate-pulse"></i> Server Terkoneksi
            </div>
        </div>

        <div class="row g-4 mb-5">
            <div class="col-md-3">
                <div class="metric-card">
                    <div>
                        <span class="text-muted small fw-bold text-uppercase d-block mb-1">Total Koleksi Alat</span>
                        <h2 class="fw-bold mb-0 text-dark"><?= $stat_alat; ?> <span class="fs-6 text-muted fw-normal">Unit</span></h2>
                    </div>
                    <div class="metric-icon bg-primary bg-opacity-10 text-primary"><i class="fa-solid fa-boxes-stacked"></i></div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="metric-card">
                    <div>
                        <span class="text-muted small fw-bold text-uppercase d-block mb-1">Alat Sedang Disewa</span>
                        <h2 class="fw-bold mb-0 text-dark"><?= (int)$stat_sewa; ?> <span class="fs-6 text-muted fw-normal">Unit</span></h2>
                    </div>
                    <div class="metric-icon bg-warning bg-opacity-10 text-warning"><i class="fa-solid fa-handshake"></i></div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="metric-card">
                    <div>
                        <span class="text-muted small fw-bold text-uppercase d-block mb-1">Omset Terbuktikan (Bulan Ini)</span>
                        <h2 class="fw-bold mb-0 text-success" style="font-size: 1.4rem;">Rp <?= number_format($stat_income, 0, ',', '.'); ?></h2>
                    </div>
                    <div class="metric-icon bg-success bg-opacity-10 text-success"><i class="fa-solid fa-money-bill-trend-up"></i></div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="metric-card">
                    <div>
                        <span class="text-muted small fw-bold text-uppercase d-block mb-1">Pelanggan Terdaftar</span>
                        <h2 class="fw-bold mb-0 text-dark"><?= $stat_cust; ?> <span class="fs-6 text-muted fw-normal">User</span></h2>
                    </div>
                    <div class="metric-icon bg-info bg-opacity-10 text-info"><i class="fa-solid fa-users-gear"></i></div>
                </div>
            </div>
        </div>

        <hr class="my-5" style="border-top: 2px dashed #cbd5e1;">

        <div class="content-block-card" id="booking-masuk">
            <div class="block-card-header bg-dark text-white py-3">
                <h5 class="mb-0 fw-bold"><i class="fa-solid fa-circle-nodes text-warning me-2"></i> 1. Verifikasi Penyewaan (Booking Awal User)</h5>
                <span class="badge bg-secondary">Tahap Pertama</span>
            </div>
            <div class="table-responsive">
                <table class="table custom-data-table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>Invoice</th>
                            <th>Nama Customer</th>
                            <th>Rincian Alat Pilihan</th>
                            <th>Rencana Durasi</th>
                            <th>ID Card KTP</th>
                            <th>Status Sistem</th>
                            <th class="text-center">Aksi Konfirmasi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $q_book_list = mysqli_query($koneksi, "SELECT * FROM transaksi WHERE status='Menunggu Verifikasi' ORDER BY id_transaksi DESC");
                        if(mysqli_num_rows($q_book_list) == 0) {
                            echo "<tr><td colspan='7' class='text-center text-muted py-4'>Tidak ada pengajuan sewa baru saat ini.</td></tr>";
                        }
                        while($row = mysqli_fetch_assoc($q_book_list)) {
                        ?>
                        <tr>
                            <td class="fw-bold text-primary">#<?= $row['invoice_code']; ?></td>
                            <td class="fw-semibold"><?= $row['customer_name']; ?></td>
                            <td>
                                <?php 
                                $tx_id = $row['id_transaksi'];
                                $q_items = mysqli_query($koneksi, "SELECT a.nama_alat, dt.qty FROM detail_transaksi dt JOIN alat a ON dt.product_id=a.id_alat WHERE dt.rental_id='$tx_id'");
                                while($it = mysqli_fetch_assoc($q_items)) {
                                    echo "• " . $it['nama_alat'] . " (x" . $it['qty'] . ")<br>";
                                }
                                ?>
                            </td>
                            <td><?= date('d/m/Y', strtotime($row['rent_date'])); ?> s/d <?= date('d/m/Y', strtotime($row['return_date'])); ?></td>
                            <td>
                                <a href="../uploads/identitas/<?= $row['foto_identitas']; ?>" target="_blank" class="btn btn-xs btn-outline-secondary py-1 px-2 text-xs"><i class="fa-solid fa-id-card"></i> Lihat Berkas</a>
                            </td>
                            <td><span class="badge-status badge-waiting">Menunggu Verifikasi</span></td>
                            <td class="text-center">
                                <a href="index.php?action=setujui_booking&id=<?= $row['id_transaksi']; ?>" class="btn btn-sm btn-success px-3 me-1"><i class="fa-solid fa-circle-check"></i> Setujui</a>
                                <a href="index.php?action=tolak_booking&id=<?= $row['id_transaksi']; ?>" class="btn btn-sm btn-danger px-2" onclick="return confirm('Tolak pengajuan sewa ini?')"><i class="fa-solid fa-ban"></i> Tolak</a>
                            </td>
                        </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="content-block-card" id="verifikasi-pembayaran">
            <div class="block-card-header bg-secondary text-white py-3">
                <h5 class="mb-0 fw-bold"><i class="fa-solid fa-money-bill-wave text-success me-2"></i> 2. Verifikasi Finansial (Validasi Slip Setoran Bank)</h5>
                <span class="badge bg-light text-dark">Tahap Kedua</span>
            </div>
            <div class="table-responsive">
                <table class="table custom-data-table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>Invoice</th>
                            <th>Penyewa</th>
                            <th>Total Tagihan Kasir</th>
                            <th>Metode Bayar</th>
                            <th>Bukti File Gambar</th>
                            <th>Status Finansial</th>
                            <th class="text-center">Eksekusi Validasi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $q_pay_list = mysqli_query($koneksi, "SELECT * FROM transaksi WHERE status='Dibayar' ORDER BY id_transaksi DESC");
                        if(mysqli_num_rows($q_pay_list) == 0) {
                            echo "<tr><td colspan='7' class='text-center text-muted py-4'>Tidak ada kiriman bukti transfer yang mengantre.</td></tr>";
                        }
                        while($row = mysqli_fetch_assoc($q_pay_list)) {
                        ?>
                        <tr>
                            <td class="fw-bold">#<?= $row['invoice_code']; ?></td>
                            <td><?= $row['customer_name']; ?></td>
                            <td class="fw-bold text-success">Rp <?= number_format($row['total_bayar'], 0, ',', '.'); ?></td>
                            <td><span class="badge bg-dark"><?= $row['metode_bayar'] ? $row['metode_bayar'] : 'Transfer Bank'; ?></span></td>
                            <td>
                                <a href="../uploads/bukti/<?= $row['foto_selfie']; ?>" target="_blank" class="btn btn-sm btn-light border text-primary font-semibold"><i class="fa-solid fa-image"></i> Buka Bukti.jpg</a>
                            </td>
                            <td><span class="badge-status badge-pay">Dibayar (Proses Cek)</span></td>
                            <td class="text-center">
                                <a href="index.php?action=terima_bayar&id=<?= $row['id_transaksi']; ?>" class="btn btn-sm btn-success px-4"><i class="fa-solid fa-thumbs-up"></i> Terima Pembayaran Sah</a>
                            </td>
                        </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="content-block-card" id="penyewaan-aktif">
            <div class="block-card-header bg-primary text-white py-3">
                <h5 class="mb-0 fw-bold"><i class="fa-solid fa-boxes-packing me-2"></i> 3 & 4. Manajemen Distribusi Alat (Serah Terima Gudang & Cek Denda)</h5>
                <span class="badge bg-light text-primary fw-bold">Live Status</span>
            </div>
            <div class="table-responsive">
                <table class="table custom-data-table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>Invoice</th>
                            <th>Nama Pelanggan</th>
                            <th>Alat Dipinjam</th>
                            <th>Tenggat Pengembalian</th>
                            <th>Kalkulasi Keterlambatan</th>
                            <th>Status Lapangan</th>
                            <th class="text-center">Aksi Terminal Gudang</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $q_active_list = mysqli_query($koneksi, "SELECT * FROM transaksi WHERE status IN ('Disetujui', 'Sedang Disewa') ORDER BY status ASC, return_date ASC");
                        if(mysqli_num_rows($q_active_list) == 0) {
                            echo "<tr><td colspan='7' class='text-center text-muted py-4'>Tidak ada barang yang sedang keluar/disewa dilapangan.</td></tr>";
                        }
                        while($row = mysqli_fetch_assoc($q_active_list)) {
                            $tgl_kembali_obj  = strtotime($row['return_date']);
                            $tgl_sekarang_obj = strtotime(date('Y-m-d'));
                            $is_overdue       = ($tgl_sekarang_obj > $tgl_kembali_obj) && ($row['status'] == 'Sedang Disewa');
                        ?>
                        <tr class="<?= $is_overdue ? 'table-danger' : '' ?>">
                            <td class="fw-bold text-dark">#<?= $row['invoice_code']; ?></td>
                            <td><?= $row['customer_name']; ?></td>
                            <td>
                                <?php 
                                $tx_id = $row['id_transaksi'];
                                $q_items = mysqli_query($koneksi, "SELECT a.nama_alat, dt.qty FROM detail_transaksi dt JOIN alat a ON dt.product_id=a.id_alat WHERE dt.rental_id='$tx_id'");
                                while($it = mysqli_fetch_assoc($q_items)) {
                                    echo "- " . $it['nama_alat'] . " (" . $it['qty'] . "x)<br>";
                                }
                                ?>
                            </td>
                            <td class="fw-bold"><?= date('d M Y', $tgl_kembali_obj); ?></td>
                            <td>
                                <?php 
                                if($row['status'] == 'Disetujui') {
                                    echo "<span class='text-muted small'><i class='fa-solid fa-boxes-arrow-right'></i> Menunggu Diambil User</span>";
                                } else {
                                    if($is_overdue) {
                                        $hari_telat = round(($tgl_sekarang_obj - $tgl_kembali_obj) / (60 * 60 * 24));
                                        $denda_live = $hari_telat * 20000;
                                        echo "<span class='text-danger fw-bold'><i class='fa-solid fa-triangle-exclamation animate-bounce'></i> Telat $hari_telat Hari (Denda: Rp ".number_format($denda_live, 0, ',', '.').")</span>";
                                    } else {
                                        echo "<span class='text-success small'><i class='fa-solid fa-circle-check'></i> Durasi Aman</span>";
                                    }
                                }
                                ?>
                            </td>
                            <td>
                                <span class="badge-status <?= $row['status'] == 'Sedang Disewa' ? ($is_overdue ? 'badge-danger bg-danger text-white' : 'badge-active') : 'badge-approved' ?>">
                                    <?= $row['status'] == 'Disetujui' ? 'Siap Diambil' : $row['status']; ?>
                                </span>
                            </td>
                            <td class="text-center">
                                <?php if($row['status'] == 'Disetujui'): ?>
                                    <a href="index.php?action=mulai_sewa&id=<?= $row['id_transaksi']; ?>" class="btn btn-sm btn-primary px-3 fw-semibold"><i class="fa-solid fa-truck-ramp-box"></i> Lepas Barang</a>
                                <?php else: ?>
                                    <a href="index.php?action=proses_kembali&id=<?= $row['id_transaksi']; ?>" class="btn btn-sm btn-dark px-3" onclick="return confirm('Konfirmasi bahwa item penyewaan ini telah sukses dikembalikan ke rak gudang?')"><i class="fa-solid fa-boxes-packing"></i> [Barang Kembali]</a>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="content-block-card" id="data-alat">
            <div class="block-card-header bg-white py-3">
                <h5 class="mb-0 fw-bold text-dark"><i class="fa-solid fa-boxes-stacked text-primary me-2"></i> Pengelolaan Data Master Armada Alat</h5>
                <button class="btn btn-sm btn-pink text-white bg-dark px-3" data-bs-toggle="modal" data-bs-target="#modalTambahAlat"><i class="fa-solid fa-plus me-1"></i> Tambah Alat Baru</button>
            </div>
            <div class="table-responsive">
                <table class="table custom-data-table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>Preview Foto</th>
                            <th>Nama Komponen Alat</th>
                            <th>Kategori</th>
                            <th>Tarif Sewa / Hari</th>
                            <th class="text-center">Stok Gudang</th>
                            <th class="text-center">Opsi Tindakan</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $q_gudang = mysqli_query($koneksi, "SELECT * FROM alat ORDER BY id_alat DESC");
                        while($al = mysqli_fetch_assoc($q_gudang)) {
                        ?>
                        <tr>
                            <td>
                                <img src="../uploads/alat/<?= $al['gambar'] ? $al['gambar'] : 'default.jpg'; ?>" class="thumbnail-alat" alt="Foto Unit">
                            </td>
                            <td class="fw-bold text-dark"><?= $al['nama_alat']; ?></td>
                            <td><span class="badge bg-secondary"><?= $al['kategori']; ?></span></td>
                            <td class="fw-bold text-secondary">Rp <?= number_format($al['harga_perhari'], 0, ',', '.'); ?></td>
                            <td class="text-center fw-semibold <?= $al['stok'] == 0 ? 'text-danger' : 'text-success' ?>"><?= $al['stok']; ?> Unit Available</td>
                            <td class="text-center">
                                <button class="btn btn-sm btn-outline-secondary me-1" data-bs-toggle="modal" data-bs-target="#modalEditAlat<?= $al['id_alat']; ?>"><i class="fa-solid fa-pen-to-square"></i></button>
                                <a href="index.php?delete_alat=<?= $al['id_alat']; ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Hapus permanen alat ini?')"><i class="fa-solid fa-trash"></i></a>
                            </td>
                        </tr>

                        <div class="modal fade" id="modalEditAlat<?= $al['id_alat']; ?>" tabindex="-1" aria-hidden="true">
                          <div class="modal-dialog">
                            <form action="index.php" method="POST" enctype="multipart/form-data" class="modal-content">
                              <div class="modal-header">
                                <h5 class="modal-title fw-bold">Ubah Informasi Unit Alat</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                              </div>
                              <div class="modal-body">
                                <input type="hidden" name="id_alat" value="<?= $al['id_alat']; ?>">
                                <div class="mb-3">
                                    <label class="form-label font-semibold">Nama Alat</label>
                                    <input type="text" name="nama_alat" class="form-control" value="<?= $al['nama_alat']; ?>" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label font-semibold">Kategori</label>
                                    <select name="kategori" class="form-select">
                                        <option value="outdoor" <?= $al['kategori'] == 'outdoor' ? 'selected' : ''; ?>>Outdoor / Camping</option>
                                        <option value="kamera" <?= $al['kategori'] == 'kamera' ? 'selected' : ''; ?>>Kamera & DSLR</option>
                                        <option value="lensa" <?= $al['kategori'] == 'lensa' ? 'selected' : ''; ?>>Lensa Premium</option>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label font-semibold">Harga / Hari (Rp)</label>
                                    <input type="number" name="harga_perhari" class="form-control" value="<?= $al['harga_perhari']; ?>" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label font-semibold">Stok Ready</label>
                                    <input type="number" name="stok" class="form-control" value="<?= $al['stok']; ?>" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label font-semibold">Ganti File Gambar <small class="text-muted">(Kosongkan jika tidak diubah)</small></label>
                                    <input type="file" name="gambar" class="form-control">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label font-semibold">Deskripsi Singkat</label>
                                    <textarea name="deskripsi" class="form-control" rows="3"><?= $al['deskripsi']; ?></textarea>
                                </div>
                              </div>
                              <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                                <button type="submit" name="edit_alat" class="btn btn-primary">Simpan Perubahan</button>
                              </div>
                            </form>
                          </div>
                        </div>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="content-block-card" id="pelanggan">
            <div class="block-card-header bg-white py-3">
                <h5 class="mb-0 fw-bold text-dark"><i class="fa-solid fa-users text-info me-2"></i> Database Keanggotaan Pelanggan (User Nyambung Otomatis)</h5>
            </div>
            <div class="table-responsive">
                <table class="table custom-data-table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>ID User</th>
                            <th>Nama Pelanggan</th>
                            <th>Alamat Email</th>
                            <th>Nomor HP / WhatsApp</th>
                            <th>Otoritas Role</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $q_users_all = mysqli_query($koneksi, "SELECT * FROM user WHERE role='user' ORDER BY id DESC");
                        while($us = mysqli_fetch_assoc($q_users_all)) {
                        ?>
                        <tr>
                            <td><span class="badge bg-light text-dark">USR-0<?= $us['id']; ?></span></td>
                            <td class="fw-bold"><?= $us['nama']; ?></td>
                            <td><?= $us['email']; ?></td>
                            <td class="font-monospace text-primary"><?= $us['hp'] ? $us['hp'] : '0812XXXXXXXX'; ?></td>
                            <td><span class="badge bg-success-subtle text-success px-3 py-1 rounded-pill">Customer</span></td>
                        </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="content-block-card" id="laporan-panel">
            <div class="block-card-header bg-light py-3">
                <h5 class="mb-0 fw-bold text-dark"><i class="fa-solid fa-file-invoice-dollar text-success me-2"></i> Arsip Riwayat Transaksi Selesai & Laporan Pendapatan</h5>
            </div>
            <div class="table-responsive">
                <table class="table custom-data-table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>Invoice</th>
                            <th>Nama Member</th>
                            <th>Tanggal Pinjam</th>
                            <th>Tanggal Masuk Gudang</th>
                            <th>Akumulasi Denda</th>
                            <th>Total Dana Masuk</th>
                            <th>Status Dokumen</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $q_done_list = mysqli_query($koneksi, "SELECT * FROM transaksi WHERE status='Selesai' ORDER BY id_transaksi DESC");
                        if(mysqli_num_rows($q_done_list) == 0) {
                            echo "<tr><td colspan='7' class='text-center text-muted py-4'>Belum ada rekam riwayat transaksi bernilai 'Selesai'.</td></tr>";
                        }
                        while($row = mysqli_fetch_assoc($q_done_list)) {
                        ?>
                        <tr>
                            <td class="fw-bold">#<?= $row['invoice_code']; ?></td>
                            <td><?= $row['customer_name']; ?></td>
                            <td><?= date('d M Y', strtotime($row['rent_date'])); ?></td>
                            <td><?= date('d M Y', strtotime($row['return_date'])); ?></td>
                            <td class="text-danger fw-bold">Rp <?= number_format((int)$row['deposit'], 0, ',', '.'); ?></td>
                            <td class="fw-bold text-success">Rp <?= number_format($row['total_bayar'], 0, ',', '.'); ?></td>
                            <td><span class="badge-status badge-closed"><i class="fa-solid fa-lock"></i> Arrived & Closed</span></td>
                        </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
        </div>

    </div>

    <div class="modal fade" id="modalTambahAlat" tabindex="-1" aria-hidden="true">
      <div class="modal-dialog">
        <form action="index.php" method="POST" enctype="multipart/form-data" class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title fw-bold">Registrasi Armada Alat Baru</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <div class="mb-3">
                <label class="form-label font-semibold">Nama Komponen Alat</label>
                <input type="text" name="nama_alat" class="form-control" placeholder="Contoh: Kamera Sony A6400" required>
            </div>
            <div class="mb-3">
                <label class="form-label font-semibold">Kategori Alat</label>
                <select name="kategori" class="form-select">
                    <option value="outdoor">Outdoor / Camping</option>
                    <option value="kamera">Kamera & DSLR</option>
                    <option value="lensa">Lensa Premium</option>
                </select>
            </div>
            <div class="mb-3">
                <label class="form-label font-semibold">Harga Sewa / Hari (Rp)</label>
                <input type="number" name="harga_perhari" class="form-control" placeholder="80000" required>
            </div>
            <div class="mb-3">
                <label class="form-label font-semibold">Kuantitas Stok Awal</label>
                <input type="number" name="stok" class="form-control" placeholder="5" required>
            </div>
            <div class="mb-3">
                <label class="form-label font-semibold">Upload Foto Unit Alat</label>
                <input type="file" name="gambar" class="form-control" required>
            </div>
            <div class="mb-3">
                <label class="form-label font-semibold">Deskripsi / Kelengkapan</label>
                <textarea name="deskripsi" class="form-control" rows="3" placeholder="Tulis kelengkapan isi paket..."></textarea>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            <button type="submit" name="tambah_alat" class="btn btn-dark">Simpan ke Rak Gudang</button>
          </div>
        </form>
      </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        document.querySelectorAll('.menu-item').forEach(link => {
            link.addEventListener('click', function() {
                document.querySelectorAll('.menu-item').forEach(item => item.classList.remove('active'));
                this.classList.add('active');
            });
        });
    </script>
</body>
</html>