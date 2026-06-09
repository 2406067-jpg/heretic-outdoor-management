<?php
include '../koneksi.php';
include '../auth.php';
cek_akses('super_admin');

// PROSES SIMPAN / TAMBAH ALAT
if (isset($_POST['tambah_alat'])) {
    $nama = $_POST['nama_alat'];
    $kategori = $_POST['kategori'];
    $harga = $_POST['harga_perhari'];
    $stok = $_POST['stok'];
    
    $stmt = mysqli_prepare($koneksi, "INSERT INTO alat (nama_alat, kategori, harga_perhari, stok) VALUES (?, ?, ?, ?)");
    mysqli_stmt_bind_param($stmt, "ssii", $nama, $kategori, $harga, $stok);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    
    header("Location: master_alat.php");
    exit();
}

// PROSES EDIT ALAT
if (isset($_POST['edit_alat'])) {
    $id = $_POST['id_alat'];
    $nama = $_POST['nama_alat'];
    $kategori = $_POST['kategori'];
    $harga = $_POST['harga_perhari'];
    $stok = $_POST['stok'];
    
    $stmt = mysqli_prepare($koneksi, "UPDATE alat SET nama_alat=?, kategori=?, harga_perhari=?, stok=? WHERE id_alat=?");
    mysqli_stmt_bind_param($stmt, "ssiii", $nama, $kategori, $harga, $stok, $id);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    
    header("Location: master_alat.php");
    exit();
}

// PROSES HAPUS ALAT
if (isset($_GET['hapus'])) {
    $id = $_GET['hapus'];
    
    $stmt = mysqli_prepare($koneksi, "DELETE FROM alat WHERE id_alat=?");
    mysqli_stmt_bind_param($stmt, "i", $id);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    
    header("Location: master_alat.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Master Stok - Heretic</title>
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
        
        .sidebar { height: 100vh; background-color: var(--bg-card); width: 260px; position: fixed; border-right: 1px solid var(--border-color); z-index: 99; }
        .brand-logo { padding: 25px; display: flex; align-items: center; gap: 12px; border-bottom: 1px solid var(--border-color); }
        .logo-icon { width: 35px; height: 35px; background: linear-gradient(135deg, #ffc107, #ff9800); border-radius: 8px; }
        .nav-link-custom { color: var(--text-main); padding: 12px 25px; display: flex; align-items: center; gap: 15px; text-decoration: none; font-size: 0.9rem; transition: all 0.3s; border-left: 3px solid transparent; }
        .nav-link-custom:hover, .nav-link-custom.active { color: var(--text-light); background: rgba(137, 80, 252, 0.08); border-left: 3px solid var(--accent-purple); }
        
        .main-content { margin-left: 260px; padding: 40px; }
        
        .card-custom { background-color: var(--bg-card); border: 1px solid var(--border-color); border-radius: 14px; box-shadow: 0px 10px 30px rgba(0,0,0,0.2); overflow: hidden; }
        .table-responsive { margin: 0; }
        
        .table-custom { width: 100%; margin-bottom: 0; color: var(--text-main); vertical-align: middle; border-collapse: collapse; }
        .table-custom th { background-color: rgba(255, 255, 255, 0.02) !important; color: #fff !important; font-weight: 600; font-size: 0.85rem; text-transform: uppercase; letter-spacing: 0.5px; padding: 18px 24px; border-bottom: 1px solid var(--border-color); }
        .table-custom td { padding: 18px 24px; border-bottom: 1px solid var(--border-color); color: var(--text-main); background-color: transparent !important; }
        .table-custom tr:last-child td { border-bottom: none; }
        .table-custom tr:hover td { background-color: rgba(255, 255, 255, 0.01) !important; }

        .badge-custom { padding: 6px 12px; font-weight: 500; font-size: 0.75rem; border-radius: 6px; border: 1px solid transparent; }
        .badge-tenda { background-color: rgba(255, 193, 7, 0.1); color: #ffc107; border-color: rgba(255, 193, 7, 0.2); }
        .badge-tas { background-color: rgba(40, 167, 69, 0.1); color: #28a745; border-color: rgba(40, 167, 69, 0.2); }
        .badge-kamera { background-color: rgba(23, 162, 184, 0.1); color: #17a2b8; border-color: rgba(23, 162, 184, 0.2); }
        .badge-aksesoris { background-color: rgba(108, 117, 125, 0.1); color: #bcbcbc; border-color: rgba(108, 117, 125, 0.2); }
        
        .btn-action { width: 34px; height: 34px; padding: 0; display: inline-flex; align-items: center; justify-content: center; border-radius: 8px; transition: all 0.2s; }
        .btn-add { background: var(--accent-purple); border: none; color: white; padding: 10px 20px; border-radius: 8px; font-weight: 500; font-size: 0.9rem; transition: opacity 0.2s; }
        .btn-add:hover { opacity: 0.9; color: white; }

        .modal-content { background-color: #141424; color: #fff; border: 1px solid var(--border-color); border-radius: 16px; }
        .form-control, .form-select { background-color: #0f0f1a; border: 1px solid var(--border-color); color: #fff; padding: 10px 14px; border-radius: 8px; }
        .form-control:focus, .form-select:focus { background-color: #0f0f1a; color:#fff; border-color: var(--accent-purple); box-shadow: none; }
        .modal-header { border-bottom: 1px solid var(--border-color); padding: 20px 26px; }
        .modal-body { padding: 26px; }
        .modal-footer { border-top: 1px solid var(--border-color); padding: 18px 26px; }
    </style>
</head>
<body>

    <div class="sidebar">
        <div class="brand-logo">
            <div class="logo-icon"></div>
            <div>
                <h5 class="mb-0 fw-bold text-white" style="letter-spacing: 1px;">HERETIC</h5>
                <small style="font-size: 0.7rem; color: var(--accent-purple);">SUPER ADMIN</small>
            </div>
        </div>
        <div class="py-3">
            <p class="px-4 text-uppercase mb-2 mt-2" style="font-size: 0.75rem; letter-spacing: 1px; color: rgba(255,255,255,0.2);">Dashboards</p>
            <a href="index.php" class="nav-link-custom"><i class="fa-solid fa-chart-pie"></i> Dashboard</a>
            <a href="master_alat.php" class="nav-link-custom active"><i class="fa-solid fa-boxes-stacked"></i> Master Alat (Stok)</a>
            <a href="transaksi.php" class="nav-link-custom"><i class="fa-solid fa-cart-flatbed"></i> Transaksi Baru</a>
            <a href="pengembalian.php" class="nav-link-custom"><i class="fa-solid fa-rotate-left"></i> Pengembalian Barang</a>
            <p class="px-4 text-uppercase mb-2 mt-4" style="font-size: 0.75rem; letter-spacing: 1px; color: rgba(255,255,255,0.2);">Management</p>
            <a href="manajemen_user.php" class="nav-link-custom"><i class="fa-solid fa-users"></i> Kelola User</a>
            <a href="laporan.php" class="nav-link-custom"><i class="fa-solid fa-file-invoice-dollar"></i> Laporan Keuangan</a>
            <a href="../logout.php" class="nav-link-custom text-danger mt-4"><i class="fa-solid fa-power-off"></i> Keluar / Logout</a>
        </div>
    </div>

    <div class="main-content">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h3 class="fw-bold text-white mb-1">Master Stok Alat</h3>
                <small class="text-white-50">Kelola data logistik & tarif rental PT. Heretic Rental Utama</small>
            </div>
            <button class="btn btn-add" data-bs-toggle="modal" data-bs-target="#modalTambah"><i class="fa-solid fa-plus me-2"></i>Tambah Alat</button>
        </div>

        <div class="card-custom">
            <div class="table-responsive">
                <table class="table-custom">
                    <thead>
                        <tr>
                            <th>Kode</th>
                            <th>Nama Alat</th>
                            <th>Kategori</th>
                            <th>Tarif / Hari</th>
                            <th>Sisa Stok</th>
                            <th class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $query = mysqli_query($koneksi, "SELECT * FROM alat ORDER BY id_alat DESC");
                        $modals = []; 
                        while($row = mysqli_fetch_assoc($query)):
                            $kode_otomatis = 'ALT-' . str_pad($row['id_alat'], 3, '0', STR_PAD_LEFT);
                            ob_start();
                        ?>
                        <div class="modal fade" id="modalEdit<?= $row['id_alat']; ?>" tabindex="-1" aria-hidden="true">
                            <div class="modal-dialog modal-dialog-centered">
                                <form action="" method="POST" class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title fw-bold text-white">Edit Data Alat</h5>
                                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                    </div>
                                    <div class="modal-body">
                                        <input type="hidden" name="id_alat" value="<?= $row['id_alat']; ?>">
                                        <div class="mb-3">
                                            <label class="form-label text-white-50">Kode Alat (Otomatis)</label>
                                            <input type="text" class="form-control" value="<?= $kode_otomatis; ?>" disabled>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label text-white-50">Nama Alat</label>
                                            <input type="text" name="nama_alat" class="form-control" value="<?= htmlspecialchars($row['nama_alat']); ?>" required>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label text-white-50">Kategori</label>
                                            <select name="kategori" class="form-select" required>
                                                <option value="Tenda" <?= $row['kategori'] == 'Tenda' ? 'selected' : ''; ?>>Tenda</option>
                                                <option value="Tas" <?= $row['kategori'] == 'Tas' ? 'selected' : ''; ?>>Tas Carrier</option>
                                                <option value="Kamera" <?= $row['kategori'] == 'Kamera' ? 'selected' : ''; ?>>Kamera & Lensa</option>
                                                <option value="Aksesoris" <?= $row['kategori'] == 'Aksesoris' ? 'selected' : ''; ?>>Aksesoris Pendukung</option>
                                            </select>
                                        </div>
                                        <div class="row">
                                            <div class="col-6 mb-3">
                                                <label class="form-label text-white-50">Tarif / Hari (Rp)</label>
                                                <input type="number" name="harga_perhari" class="form-control" value="<?= $row['harga_perhari']; ?>" required>
                                            </div>
                                            <div class="col-6 mb-3">
                                                <label class="form-label text-white-50">Jumlah Stok</label>
                                                <input type="number" name="stok" class="form-control" value="<?= $row['stok']; ?>" required>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                                        <button type="submit" name="edit_alat" class="btn btn-primary" style="background: var(--accent-purple); border:none;">Simpan Perubahan</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                        <?php 
                        $modals[] = ob_get_clean(); 
                        
                        $badge_class = 'badge-aksesoris';
                        if(strtolower($row['kategori']) == 'tenda') $badge_class = 'badge-tenda';
                        if(strtolower($row['kategori']) == 'kamera') $badge_class = 'badge-kamera';
                        if(strtolower($row['kategori']) == 'tas') $badge_class = 'badge-tas';
                        ?>
                        <tr>
                            <td class="text-warning fw-bold" style="letter-spacing: 0.5px;"><?= $kode_otomatis; ?></td>
                            <td class="text-white fw-medium"><?= htmlspecialchars($row['nama_alat']); ?></td>
                            <td><span class="badge-custom <?= $badge_class; ?>"><?= htmlspecialchars($row['kategori']); ?></span></td>
                            <td class="text-white">Rp <?= number_format($row['harga_perhari'], 0, ',', '.'); ?></td>
                            <td class="text-success fw-bold"><?= $row['stok']; ?> Unit</td>
                            <td class="text-center">
                                <button class="btn btn-action btn-outline-info me-1" data-bs-toggle="modal" data-bs-target="#modalEdit<?= $row['id_alat']; ?>"><i class="fa-solid fa-pen-to-square"></i></button>
                                <a href="master_alat.php?hapus=<?= $row['id_alat']; ?>" onclick="return confirm('Yakin hapus alat ini?')" class="btn btn-action btn-outline-danger"><i class="fa-solid fa-trash"></i></a>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <?php echo implode("\n", $modals); ?>

    <div class="modal fade" id="modalTambah" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <form action="" method="POST" class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold text-white">Tambah Alat Baru</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label text-white-50">Nama Alat</label>
                        <input type="text" name="nama_alat" class="form-control" placeholder="Nama brand & seri alat" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label text-white-50">Kategori</label>
                        <select name="kategori" class="form-select" required>
                            <option value="Tenda">Tenda</option>
                            <option value="Tas">Tas Carrier</option>
                            <option value="Kamera">Kamera & Lensa</option>
                            <option value="Aksesoris">Aksesoris Pendukung</option>
                        </select>
                    </div>
                    <div class="row">
                        <div class="col-6 mb-3">
                            <label class="form-label text-white-50">Tarif / Hari (Rp)</label>
                            <input type="number" name="harga_perhari" class="form-control" placeholder="50000" required>
                        </div>
                        <div class="col-6 mb-3">
                            <label class="form-label text-white-50">Jumlah Stok</label>
                            <input type="number" name="stok" class="form-control" placeholder="10" required>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" name="tambah_alat" class="btn btn-primary" style="background: var(--accent-purple); border:none;">Tambah</button>
                </div>
            </form>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>