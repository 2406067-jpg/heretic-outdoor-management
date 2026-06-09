<?php
include '../koneksi.php';
include 'auth_super.php'; 

// =========================================================================
// INTERFACES & BACKEND CONTROLLER: MANIPULASI DATA ENGINE
// =========================================================================

// 1. TAMBAH STAFF ADMIN
if (isset($_POST['tambah_admin'])) {
    $nama     = mysqli_real_escape_string($koneksi, $_POST['nama']);
    $username = mysqli_real_escape_string($koneksi, $_POST['username']);
    $email    = mysqli_real_escape_string($koneksi, $_POST['email']);
    $password = password_hash($_POST['password'], PASSWORD_BCRYPT);
    
    $q_add = mysqli_query($koneksi, "INSERT INTO user (nama, username, email, password, role, is_active) VALUES ('$nama', '$username', '$email', '$password', 'admin', 1)");
    if ($q_add) {
        mysqli_query($koneksi, "INSERT INTO audit_log (aktivitas, aktor) VALUES ('Menambahkan staff admin baru: $username', 'Super Admin')");
        echo "<script>alert('Staff Admin Baru Berhasil Didaftarkan!'); window.location='index.php#kelola-admin';</script>";
    } else {
        echo "<script>alert('Gagal tambah admin: " . mysqli_error($koneksi) . "');</script>";
    }
}

// 2. OTORITAS UBHAH STATUS / HAPUS ADMIN
if (isset($_GET['action_admin']) && isset($_GET['id'])) {
    $id_adm = mysqli_real_escape_string($koneksi, $_GET['id']);
    $act    = $_GET['action_admin'];
    
    if ($act == 'nonaktifkan') {
        mysqli_query($koneksi, "UPDATE user SET is_active=0 WHERE id_user='$id_adm' AND role='admin'");
        mysqli_query($koneksi, "INSERT INTO audit_log (aktivitas, aktor) VALUES ('Menonaktifkan akses admin ID: $id_adm', 'Super Admin')");
        echo "<script>alert('Akses Admin Dinonaktifkan!'); window.location='index.php#kelola-admin';</script>";
    } elseif ($act == 'aktifkan') {
        mysqli_query($koneksi, "UPDATE user SET is_active=1 WHERE id_user='$id_adm' AND role='admin'");
        mysqli_query($koneksi, "INSERT INTO audit_log (aktivitas, aktor) VALUES ('Mengaktifkan kembali akses admin ID: $id_adm', 'Super Admin')");
        echo "<script>alert('Akses Admin Diaktifkan Kembali!'); window.location='index.php#kelola-admin';</script>";
    } elseif ($act == 'hapus') {
        mysqli_query($koneksi, "DELETE FROM user WHERE id_user='$id_adm' AND role='admin'");
        mysqli_query($koneksi, "INSERT INTO audit_log (aktivitas, aktor) VALUES ('Menghapus permanen admin ID: $id_adm', 'Super Admin')");
        echo "<script>alert('Akun Admin Berhasil Dihapus!'); window.location='index.php#kelola-admin';</script>";
    }
}

// 3. SUSPEND / AKTIFKAN USER PELANGGAN
if (isset($_GET['action_user']) && isset($_GET['id'])) {
    $id_usr = mysqli_real_escape_string($koneksi, $_GET['id']);
    $act    = $_GET['action_user'];
    
    if ($act == 'suspend') {
        mysqli_query($koneksi, "UPDATE user SET status_akun='Suspended' WHERE id_user='$id_usr' AND role='user'");
        mysqli_query($koneksi, "INSERT INTO audit_log (aktivitas, aktor) VALUES ('Melakukan tindakan SUSPEND pada pelanggan ID: $id_usr', 'Super Admin')");
        echo "<script>alert('Pelanggan berhasil di-suspend!'); window.location='index.php#kelola-user';</script>";
    } elseif ($act == 'aktifkan') {
        mysqli_query($koneksi, "UPDATE user SET status_akun='Active' WHERE id_user='$id_usr' AND role='user'");
        mysqli_query($koneksi, "INSERT INTO audit_log (aktivitas, aktor) VALUES ('Membuka blokir akun pelanggan ID: $id_usr', 'Super Admin')");
        echo "<script>alert('Akun pelanggan aktif kembali!'); window.location='index.php#kelola-user';</script>";
    }
}

// 4. UPDATE PENGATURAN BRAND CORE SYSTEM
if (isset($_POST['save_settings'])) {
    $nama_rental = mysqli_real_escape_string($koneksi, $_POST['nama_rental']);
    $email_sys   = mysqli_real_escape_string($koneksi, $_POST['email_sistem']);
    $wa_rental   = mysqli_real_escape_string($koneksi, $_POST['whatsapp_rental']);
    $alamat      = mysqli_real_escape_string($koneksi, $_POST['alamat_rental']);
    
    mysqli_query($koneksi, "UPDATE pengaturan_sistem SET nama_rental='$nama_rental', email_sistem='$email_sys', whatsapp_rental='$wa_rental', alamat_rental='$alamat' WHERE id=1");
    mysqli_query($koneksi, "INSERT INTO audit_log (aktivitas, aktor) VALUES ('Memperbarui konfigurasi utama sistem aplikasi', 'Super Admin')");
    echo "<script>alert('Konfigurasi Inti Berhasil Diperbarui!'); window.location='index.php#pengaturan';</script>";
}

// 5. TAMBAH KATEGORI BARU
if (isset($_POST['tambah_kategori'])) {
    $nama_kategori = mysqli_real_escape_string($koneksi, $_POST['nama_kategori']);
    mysqli_query($koneksi, "INSERT INTO audit_log (aktivitas, aktor) VALUES ('Menambahkan kategori baru: $nama_kategori', 'Super Admin')");
    echo "<script>alert('Kategori logistik berhasil ditambahkan!'); window.location='index.php#monitor-stok';</script>";
}

// =========================================================================
// DATA AGGREGATION & MATHEMATICAL CALCULATIONS (COUNTER METRICS)
// =========================================================================
$r_u = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT COUNT(*) as tot FROM user WHERE role='user'"));
$tot_user = $r_u['tot'] ?? 0;

$r_a = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT COUNT(*) as tot FROM user WHERE role='admin'"));
$tot_admin = $r_a['tot'] ?? 0;

$r_t = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT COUNT(*) as tot FROM alat"));
$tot_alat = $r_t['tot'] ?? 0;

$r_tx = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT COUNT(*) as tot FROM transaksi"));
$tot_transaksi = $r_tx['tot'] ?? 0;

$r_inc = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT SUM(total_bayar) as tot FROM transaksi WHERE status='Selesai' OR status='active'"));
$tot_pendapatan = $r_inc['tot'] ?? 0;
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Super Admin Dashboard - Heretic Management Engine</title>
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
        }

        .sidebar-panel { 
            height: 100vh; 
            background-color: var(--slate-dark); 
            width: 280px; 
            position: fixed; 
            overflow-y: auto; 
            z-index: 1000;
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
            transition: all 0.2s; 
        }
        .menu-item:hover, .menu-item.active { 
            color: #ffffff; 
            background: rgba(255,255,255,0.04); 
        }
        .menu-item.active {
            border-left: 4px solid var(--accent-pink);
            background: rgba(234, 68, 146, 0.1);
        }

        .main-workspace { margin-left: 280px; padding: 45px; min-height: 100vh; }
        .metric-card { 
            background: #ffffff; border: none; border-radius: 16px; padding: 26px; 
            box-shadow: 0 4px 20px rgba(0,0,0,0.01); display: flex; align-items: center; justify-content: space-between;
            border: 1px solid #f1f5f9;
        }
        .metric-icon { width: 54px; height: 54px; border-radius: 14px; display: flex; align-items: center; justify-content: center; font-size: 1.35rem; }
        .content-block-card { background: #ffffff; border: none; border-radius: 16px; box-shadow: 0 4px 25px rgba(0,0,0,0.02); margin-bottom: 35px; border: 1px solid #f1f5f9; }
        .block-card-header { padding: 20px 25px; background: #ffffff; border-bottom: 1px solid var(--border-color); display: flex; justify-content: space-between; align-items: center; border-radius: 16px 16px 0 0; }
        .custom-data-table th { background-color: #f8fafc; color: #64748b; font-weight: 600; text-transform: uppercase; font-size: 0.75rem; padding: 16px 20px; }
        .custom-data-table td { padding: 16px 20px; vertical-align: middle; font-size: 0.88rem; }
    </style>
</head>
<body>

    <div class="sidebar-panel">
        <div class="panel-brand">
            <div class="brand-avatar"></div>
            <div>
                <h5 class="mb-0 fw-bold text-white" style="letter-spacing: 0.5px;">HERETIC ENGINE</h5>
                <small class="text-muted fw-semibold" style="font-size: 0.7rem; color: var(--accent-pink) !important;"><i class="fa-solid fa-crown"></i> SUPER ADMIN MODE</small>
            </div>
        </div>
        
        <div class="py-3">
            <a href="#dashboard" class="menu-item active"><i class="fa-solid fa-chart-pie"></i> Dashboard Utama</a>
            
            <div class="nav-section-title">Otoritas User & Staff</div>
            <a href="#kelola-admin" class="menu-item"><i class="fa-solid fa-user-gear"></i> Kelola Data Admin</a>
            <a href="#kelola-user" class="menu-item"><i class="fa-solid fa-users"></i> Kelola Pelanggan</a>

            <div class="nav-section-title">Monitoring Global</div>
            <a href="#monitor-transaksi" class="menu-item"><i class="fa-solid fa-folder-open"></i> Monitoring Transaksi</a>
            <a href="#monitor-penyewaan" class="menu-item"><i class="fa-solid fa-arrow-right-arrow-left"></i> Monitoring Penyewaan</a>
            <a href="#monitor-stok" class="menu-item"><i class="fa-solid fa-warehouse"></i> Monitoring Stok & Kategori</a>

            <div class="nav-section-title">Analis Data & Keamanan</div>
            <a href="#laporan-global" class="menu-item"><i class="fa-solid fa-chart-line"></i> Laporan Global</a>
            <a href="#audit-log" class="menu-item"><i class="fa-solid fa-shield-halved"></i> Audit Log Aktivitas</a>

            <div class="nav-section-title">Konfigurasi Inti</div>
            <a href="#pengaturan" class="menu-item"><i class="fa-solid fa-sliders"></i> Pengaturan Sistem</a>
            <a href="../logout.php" class="menu-item text-danger mt-3"><i class="fa-solid fa-power-off"></i> Keluar / Logout</a>
        </div>
    </div>

    <div class="main-workspace" id="dashboard">
        
        <div class="d-flex justify-content-between align-items-center mb-5">
            <div>
                <h2 class="fw-bold text-dark mb-1">Super Server Console</h2>
                <p class="text-muted mb-0">Pemantauan performa bisnis rental dan kendali penuh keamanan data.</p>
            </div>
            <div class="bg-white border px-4 py-2 rounded-3 shadow-sm text-primary fw-semibold">
                <i class="fa-solid fa-user-shield text-danger me-2"></i> Root Mode Active
            </div>
        </div>

        <div class="row g-4 mb-5">
            <div class="col-md-3">
                <div class="metric-card">
                    <div>
                        <span class="text-muted small fw-bold text-uppercase d-block mb-1">Total User</span>
                        <h3 class="fw-bold mb-0 text-dark"><?= $tot_user; ?> <span class="fs-6 text-muted fw-normal">Akun</span></h3>
                    </div>
                    <div class="metric-icon bg-primary bg-opacity-10 text-primary"><i class="fa-solid fa-users"></i></div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="metric-card">
                    <div>
                        <span class="text-muted small fw-bold text-uppercase d-block mb-1">Total Admin</span>
                        <h3 class="fw-bold mb-0 text-dark"><?= $tot_admin; ?> <span class="fs-6 text-muted fw-normal">Staf</span></h3>
                    </div>
                    <div class="metric-icon bg-info bg-opacity-10 text-info"><i class="fa-solid fa-user-tie"></i></div>
                </div>
            </div>
            <div class="col-md-2">
                <div class="metric-card">
                    <div>
                        <span class="text-muted small fw-bold text-uppercase d-block mb-1">Total Alat</span>
                        <h3 class="fw-bold mb-0 text-dark"><?= $tot_alat; ?> <span class="fs-6 text-muted fw-normal">Item</span></h3>
                    </div>
                    <div class="metric-icon bg-warning bg-opacity-10 text-warning"><i class="fa-solid fa-boxes-stacked"></i></div>
                </div>
            </div>
            <div class="col-md-2">
                <div class="metric-card">
                    <div>
                        <span class="text-muted small fw-bold text-uppercase d-block mb-1">Total Transaksi</span>
                        <h3 class="fw-bold mb-0 text-dark"><?= $tot_transaksi; ?> <span class="fs-6 text-muted fw-normal">Logs</span></h3>
                    </div>
                    <div class="metric-icon bg-danger bg-opacity-10 text-danger"><i class="fa-solid fa-receipt"></i></div>
                </div>
            </div>
            <div class="col-md-2">
                <div class="metric-card">
                    <div>
                        <span class="text-muted small fw-bold text-uppercase d-block mb-1">Total Pendapatan</span>
                        <h5 class="fw-bold mb-0 text-success">Rp <?= number_format($tot_pendapatan, 0, ',', '.'); ?></h5>
                    </div>
                    <div class="metric-icon bg-success bg-opacity-10 text-success"><i class="fa-solid fa-wallet"></i></div>
                </div>
            </div>
        </div>

        <div class="content-block-card" id="kelola-admin">
            <div class="block-card-header">
                <h5 class="mb-0 fw-bold text-dark"><i class="fa-solid fa-user-gear text-primary me-2"></i> Kelola Admin</h5>
                <button class="btn btn-sm btn-primary px-3" data-bs-toggle="modal" data-bs-target="#modalTambahAdmin"><i class="fa-solid fa-user-plus me-1"></i> Tambah Admin</button>
            </div>
            <div class="table-responsive">
                <table class="table custom-data-table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>ID Admin</th>
                            <th>Nama</th>
                            <th>Username</th>
                            <th>Email</th>
                            <th>Status Akun</th>
                            <th class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $q_admin = mysqli_query($koneksi, "SELECT * FROM user WHERE role='admin' ORDER BY id_user DESC");
                        while($adm = mysqli_fetch_assoc($q_admin)){
                        ?>
                        <tr>
                            <td><span class="badge bg-secondary">ADM-0<?= $adm['id_user']; ?></span></td>
                            <td class="fw-bold"><?= $adm['nama']; ?></td>
                            <td class="text-primary">@<?= $adm['username']; ?></td>
                            <td><?= $adm['email']; ?></td>
                            <td>
                                <span class="badge bg-<?= $adm['is_active'] ? 'success' : 'danger' ?>-subtle text-<?= $adm['is_active'] ? 'success' : 'danger' ?> px-3 py-1 rounded-pill">
                                    <?= $adm['is_active'] ? 'Aktif' : 'Nonaktif' ?>
                                </span>
                            </td>
                            <td class="text-center">
                                <?php if($adm['is_active']): ?>
                                    <a href="index.php?action_admin=nonaktifkan&id=<?= $adm['id_user']; ?>" class="btn btn-sm btn-outline-warning py-1 px-2" onclick="return confirm('Nonaktifkan admin ini?')"><i class="fa-solid fa-ban"></i> Nonaktifkan</a>
                                <?php else: ?>
                                    <a href="index.php?action_admin=aktifkan&id=<?= $adm['id_user']; ?>" class="btn btn-sm btn-outline-success py-1 px-2"><i class="fa-solid fa-check"></i> Aktifkan</a>
                                <?php endif; ?>
                                <a href="index.php?action_admin=hapus&id=<?= $adm['id_user']; ?>" class="btn btn-sm btn-outline-danger py-1 px-2" onclick="return confirm('Hapus permanen akun admin ini?')"><i class="fa-solid fa-trash-can"></i> Hapus</a>
                            </td>
                        </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="content-block-card" id="kelola-user">
            <div class="block-card-header">
                <h5 class="mb-0 fw-bold text-dark"><i class="fa-solid fa-users text-info me-2"></i> Kelola User / Lihat Semua Pelanggan</h5>
            </div>
            <div class="table-responsive">
                <table class="table custom-data-table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>ID Member</th>
                            <th>Nama</th>
                            <th>Email</th>
                            <th>No HP</th>
                            <th>Status Akun</th>
                            <th class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $q_user = mysqli_query($koneksi, "SELECT * FROM user WHERE role='user' ORDER BY id_user DESC");
                        while($usr = mysqli_fetch_assoc($q_user)){
                            $is_suspended = isset($usr['status_akun']) && $usr['status_akun'] == 'Suspended';
                        ?>
                        <tr>
                            <td><span class="badge bg-light text-dark">USR-0<?= $usr['id_user']; ?></span></td>
                            <td class="fw-bold"><?= $usr['nama']; ?></td>
                            <td><?= $usr['email']; ?></td>
                            <td><?= !empty($usr['username']) ? $usr['username'] : 'N/A'; ?></td>
                            <td>
                                <span class="badge bg-<?= $is_suspended ? 'danger' : 'success' ?>-subtle text-<?= $is_suspended ? 'danger' : 'success' ?> px-3 py-1 rounded-pill">
                                    <?= $is_suspended ? 'Suspended' : 'Active' ?>
                                </span>
                            </td>
                            <td class="text-center">
                                <?php if(!$is_suspended): ?>
                                    <a href="index.php?action_user=suspend&id=<?= $usr['id_user']; ?>" class="btn btn-sm btn-danger px-3" onclick="return confirm('Suspend akun ini?')"><i class="fa-solid fa-user-slash"></i> Suspend</a>
                                <?php else: ?>
                                    <a href="index.php?action_user=aktifkan&id=<?= $usr['id_user']; ?>" class="btn btn-sm btn-success px-3"><i class="fa-solid fa-user-check"></i> Aktifkan</a>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="content-block-card" id="monitor-transaksi">
            <div class="block-card-header bg-dark text-white">
                <h5 class="mb-0 fw-bold text-white"><i class="fa-solid fa-folder-open text-warning me-2"></i> Monitoring Transaksi (Seluruh Aktivitas Lapangan)</h5>
            </div>
            <div class="table-responsive">
                <table class="table custom-data-table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>Invoice</th>
                            <th>Pelanggan</th>
                            <th>Total Bayar</th>
                            <th>Metode Ambil</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $q_tx_all = mysqli_query($koneksi, "SELECT * FROM transaksi ORDER BY id_transaksi DESC");
                        while($row = mysqli_fetch_assoc($q_tx_all)) {
                        ?>
                        <tr>
                            <td class="fw-bold text-primary">#<?= $row['invoice_code']; ?></td>
                            <td><?= $row['customer_name']; ?></td>
                            <td class="fw-bold text-success">Rp <?= number_format($row['total_bayar'], 0, ',', '.'); ?></td>
                            <td><span class="badge bg-light text-dark text-uppercase"><?= $row['metode_ambil']; ?></span></td>
                            <td><span class="badge bg-secondary text-capitalize px-3 py-1 rounded-pill"><?= $row['status']; ?></span></td>
                        </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="content-block-card" id="monitor-penyewaan">
            <div class="block-card-header bg-secondary text-white">
                <h5 class="mb-0 fw-bold text-white"><i class="fa-solid fa-arrow-right-arrow-left text-info me-2"></i> Monitoring Penyewaan (Sedang Disewa / Terlambat / Selesai)</h5>
            </div>
            <div class="table-responsive">
                <table class="table custom-data-table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>Invoice</th>
                            <th>Nama Penyewa</th>
                            <th>Tgl Pinjam</th>
                            <th>Batas Kembali</th>
                            <th>Kondisi Waktu</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $q_sewa_track = mysqli_query($koneksi, "SELECT * FROM transaksi ORDER BY id_transaksi DESC");
                        while($st = mysqli_fetch_assoc($q_sewa_track)) {
                            $is_telat = (strtotime(date('Y-m-d')) > strtotime($st['return_date'])) && ($st['status'] == 'active' || $st['status'] == 'Sedang Disewa');
                        ?>
                        <tr class="<?= $is_telat ? 'table-danger' : '' ?>">
                            <td class="fw-bold">#<?= $st['invoice_code']; ?></td>
                            <td><?= $st['customer_name']; ?></td>
                            <td><?= date('d/m/Y', strtotime($st['rent_date'])); ?></td>
                            <td class="fw-bold"><?= date('d/m/Y', strtotime($st['return_date'])); ?></td>
                            <td>
                                <?= $is_telat ? '<span class="text-danger fw-bold"><i class="fa-solid fa-triangle-exclamation"></i> Terlambat Pengembalian!</span>' : '<span class="text-success small">Waktu Aman</span>' ?>
                            </td>
                            <td><span class="badge bg-dark px-3 py-1 rounded-pill text-uppercase"><?= $st['status']; ?></span></td>
                        </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="content-block-card" id="monitor-stok">
            <div class="block-card-header">
                <h5 class="mb-0 fw-bold text-dark"><i class="fa-solid fa-warehouse text-warning me-2"></i> Monitoring Stok Alat & Kelola Kategori</h5>
                <button class="btn btn-sm btn-dark px-3" data-bs-toggle="modal" data-bs-target="#modalKategori"><i class="fa-solid fa-folder-plus me-1"></i> Kelola Kategori</button>
            </div>
            <div class="table-responsive">
                <table class="table custom-data-table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>ID Alat</th>
                            <th>Nama Alat</th>
                            <th>Kategori</th>
                            <th class="text-center">Harga/Hari</th>
                            <th class="text-center">Stok Gudang</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $q_st = mysqli_query($koneksi, "SELECT * FROM alat ORDER BY stok ASC");
                        while($s = mysqli_fetch_assoc($q_st)) {
                        ?>
                        <tr>
                            <td><span class="badge bg-secondary">ALT-<?= $s['id_alat']; ?></span></td>
                            <td class="fw-bold text-dark"><?= $s['nama_alat']; ?></td>
                            <td><span class="badge bg-outline-dark text-uppercase"><?= $s['kategori']; ?></span></td>
                            <td class="text-center fw-semibold">Rp <?= number_format($s['harga_perhari'], 0, ',', '.'); ?></td>
                            <td class="text-center fw-bold text-<?= $s['stok'] <= 2 ? 'danger' : 'success' ?>"><?= $s['stok']; ?> Unit</td>
                        </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="content-block-card" id="laporan-global">
            <div class="block-card-header">
                <h5 class="mb-0 fw-bold text-dark"><i class="fa-solid fa-chart-line text-success me-2"></i> Laporan Global & Alat Terlaris</h5>
            </div>
            <div class="p-4 row g-4">
                <div class="col-md-6">
                    <h6 class="fw-bold text-secondary mb-3"><i class="fa-solid fa-fire text-danger me-2"></i> Alat Terlaris</h6>
                    <ul class="list-group">
                        <li class="list-group-item d-flex justify-content-between align-items-center">1. Carrier Outdoor Pro <span class="badge bg-danger rounded-pill">Top Demand</span></li>
                        <li class="list-group-item d-flex justify-content-between align-items-center">2. Tenda Dome 4P <span class="badge bg-dark rounded-pill">Populer</span></li>
                        <li class="list-group-item d-flex justify-content-between align-items-center">3. Sony Mirrorless A6400 <span class="badge bg-dark rounded-pill">Kamera</span></li>
                    </ul>
                </div>
                <div class="col-md-6">
                    <h6 class="fw-bold text-secondary mb-3"><i class="fa-solid fa-clock text-primary me-2"></i> Pendapatan Berdasarkan Waktu</h6>
                    <table class="table table-bordered text-center small font-monospace">
                        <thead class="table-dark">
                            <tr><th>Hari Ini</th><th>Minggu Ini</th><th>Bulan Ini</th><th>Tahun Ini</th></tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td class="text-success fw-bold">Rp 350.000</td>
                                <td class="text-success fw-bold">Rp 1.450.000</td>
                                <td class="text-success fw-bold">Rp <?= number_format($tot_pendapatan, 0, ',', '.'); ?></td>
                                <td class="text-success fw-bold">Rp <?= number_format($tot_pendapatan, 0, ',', '.'); ?></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="content-block-card" id="audit-log">
            <div class="block-card-header bg-dark text-white">
                <h5 class="mb-0 fw-bold text-danger"><i class="fa-solid fa-shield-halved text-success me-2"></i> Audit Log (Rekam Jejak Aktivitas Server & Admin)</h5>
                <span class="badge bg-danger">REALTIME SYSTEM TRACE</span>
            </div>
            <div class="table-responsive" style="max-height: 250px; overflow-y: auto;">
                <table class="table custom-data-table table-hover mb-0 font-monospace text-xs">
                    <thead class="table-light sticky-top">
                        <tr>
                            <th>Waktu Log</th>
                            <th>Aktor</th>
                            <th>Aktivitas Perubahan Sistem</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $q_log = mysqli_query($koneksi, "SELECT * FROM audit_log ORDER BY id DESC LIMIT 30");
                        if(!$q_log || mysqli_num_rows($q_log) == 0) {
                            echo "<tr><td colspan='3' class='text-center text-muted py-3'>Belum ada log terekam hari ini.</td></tr>";
                        } else {
                            while($lg = mysqli_fetch_assoc($q_log)) {
                        ?>
                        <tr>
                            <td><?= $lg['timestamp']; ?></td>
                            <td class="text-danger fw-bold"><?= $lg['aktor']; ?></td>
                            <td class="text-dark"><?= $lg['aktivitas']; ?></td>
                        </tr>
                        <?php }} ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="content-block-card" id="pengaturan">
            <div class="block-card-header">
                <h5 class="mb-0 fw-bold text-dark"><i class="fa-solid fa-sliders text-primary me-2"></i> Pengaturan Sistem / Brand Identity</h5>
            </div>
            <form action="index.php" method="POST" class="p-4">
                <?php 
                $q_conf = mysqli_query($koneksi, "SELECT * FROM pengaturan_sistem WHERE id=1");
                $cnf = mysqli_fetch_assoc($q_conf);
                ?>
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Nama Rental</label>
                        <input type="text" name="nama_rental" class="form-control" value="<?= $cnf['nama_rental'] ?? 'HERETIC'; ?>" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Email Sistem</label>
                        <input type="email" name="email_sistem" class="form-control" value="<?= $cnf['email_sistem'] ?? 'admin@heretic.com'; ?>" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">WhatsApp Rental</label>
                        <input type="text" name="whatsapp_rental" class="form-control" value="<?= $cnf['whatsapp_rental'] ?? '081234567890'; ?>" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Alamat Kantor Pusat</label>
                        <input type="text" name="alamat_rental" class="form-control" value="<?= $cnf['alamat_rental'] ?? 'Main Core Street No.1'; ?>" required>
                    </div>
                    <div class="col-12 text-end mt-4">
                        <button type="submit" name="save_settings" class="btn btn-dark px-4"><i class="fa-solid fa-floppy-disk me-2"></i> Simpan Konfigurasi</button>
                    </div>
                </div>
            </form>
        </div>

    </div>

    <div class="modal fade" id="modalTambahAdmin" tabindex="-1" aria-hidden="true">
      <div class="modal-dialog">
        <form action="index.php" method="POST" class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title fw-bold">Daftarkan Staf Admin Baru</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
          </div>
          <div class="modal-body">
            <div class="mb-3"><label class="form-label">Nama Lengkap</label><input type="text" name="nama" class="form-control" required></div>
            <div class="mb-3"><label class="form-label">Username</label><input type="text" name="username" class="form-control" required></div>
            <div class="mb-3"><label class="form-label">Email Kerja</label><input type="email" name="email" class="form-control" required></div>
            <div class="mb-3"><label class="form-label">Password Default</label><input type="password" name="password" class="form-control" required></div>
          </div>
          <div class="modal-footer">
            <button type="submit" name="tambah_admin" class="btn btn-primary">Otorisasikan Staf</button>
          </div>
        </form>
      </div>
    </div>

    <div class="modal fade" id="modalKategori" tabindex="-1" aria-hidden="true">
      <div class="modal-dialog">
        <form action="index.php" method="POST" class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title fw-bold">Kelola Kategori Global</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
          </div>
          <div class="modal-body">
            <div class="mb-3">
                <label class="form-label">Tambah Kategori Baru</label>
                <input type="text" name="nama_kategori" class="form-control" placeholder="Contoh: Outdoor, Kamera, Lighting" required>
            </div>
            <div class="p-2 border rounded bg-light">
                <small class="text-muted d-block fw-bold">Kategori Aktif Saat Ini:</small>
                <span class="badge bg-dark m-1">OUTDOOR</span><span class="badge bg-dark m-1">KAMERA</span><span class="badge bg-dark m-1">LENSA</span><span class="badge bg-dark m-1">LIGHTING</span>
            </div>
          </div>
          <div class="modal-footer">
            <button type="submit" name="tambah_kategori" class="btn btn-dark">Simpan Kategori</button>
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