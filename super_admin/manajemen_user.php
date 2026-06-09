<?php
include '../koneksi.php';
include '../auth.php';
cek_akses('super_admin');

// PROSES TAMBAH USER / AKUN
if (isset($_POST['tambah_user'])) {
    $nama = $_POST['nama_user'] ?? '';
    $telepon = $_POST['telepon'] ?? '';
    $username = $_POST['username'] ?? '';
    $password = md5($_POST['password'] ?? ''); 
    $role = $_POST['role'] ?? 'user';

    // DIKUNCI MATI: Menggunakan kolom 'nama' dan 'no_telp' sesuai database kamu
    $query_insert = "INSERT INTO users (nama, no_telp, username, password, role) VALUES (?, ?, ?, ?, ?)";
    $stmt = mysqli_prepare($koneksi, $query_insert);
    
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "sssss", $nama, $telepon, $username, $password, $role);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
    }

    header("Location: manajemen_user.php");
    exit();
}

// PROSES EDIT USER / AKUN
if (isset($_POST['edit_user'])) {
    $id_user = $_POST['id_user'];
    $nama = $_POST['nama_user'] ?? '';
    $telepon = $_POST['telepon'] ?? '';
    $username = $_POST['username'] ?? '';
    $role = $_POST['role'] ?? 'user';

    if (!empty($_POST['password'])) {
        $password = md5($_POST['password']);
        $query_update = "UPDATE users SET nama=?, no_telp=?, username=?, password=?, role=? WHERE id_user=?";
        $stmt = mysqli_prepare($koneksi, $query_update);
        if ($stmt) {
            mysqli_stmt_bind_param($stmt, "sssssi", $nama, $telepon, $username, $password, $role, $id_user);
        }
    } else {
        $query_update = "UPDATE users SET nama=?, no_telp=?, username=?, role=? WHERE id_user=?";
        $stmt = mysqli_prepare($koneksi, $query_update);
        if ($stmt) {
            mysqli_stmt_bind_param($stmt, "ssssi", $nama, $telepon, $username, $role, $id_user);
        }
    }
    
    if ($stmt) {
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
    }

    header("Location: manajemen_user.php");
    exit();
}

// PROSES HAPUS USER / AKUN
if (isset($_GET['hapus'])) {
    $id_user = $_GET['hapus'];

    $stmt = mysqli_prepare($koneksi, "DELETE FROM users WHERE id_user=?");
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "i", $id_user);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
    }

    header("Location: manajemen_user.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola User - Heretic</title>
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
        body { background-color: var(--bg-main) !important; color: var(--text-main) !important; font-family: 'Inter', sans-serif; margin: 0; }
        
        .sidebar { height: 100vh; background-color: var(--bg-card); width: 260px; position: fixed; border-right: 1px solid var(--border-color); z-index: 99; }
        .brand-logo { padding: 25px; display: flex; align-items: center; gap: 12px; border-bottom: 1px solid var(--border-color); }
        .logo-icon { width: 35px; height: 35px; background: linear-gradient(135deg, #ffc107, #ff9800); border-radius: 8px; }
        .nav-link-custom { color: var(--text-main); padding: 12px 25px; display: flex; align-items: center; gap: 15px; text-decoration: none; font-size: 0.9rem; transition: all 0.3s; border-left: 3px solid transparent; }
        .nav-link-custom:hover, .nav-link-custom.active { color: var(--text-light); background: rgba(137, 80, 252, 0.08); border-left: 3px solid var(--accent-purple); }
        
        .main-content { margin-left: 260px; padding: 40px; }
        
        .card-custom { background-color: var(--bg-card) !important; border: 1px solid var(--border-color) !important; border-radius: 14px; box-shadow: 0px 10px 30px rgba(0,0,0,0.2); overflow: hidden; }
        .table-responsive { margin: 0; background: transparent !important; }
        
        .table-custom { width: 100%; margin-bottom: 0; vertical-align: middle; border-collapse: collapse; background: transparent !important; }
        .table-custom th { background-color: rgba(255, 255, 255, 0.02) !important; color: #ffffff !important; font-weight: 600; font-size: 0.85rem; text-transform: uppercase; letter-spacing: 0.5px; padding: 18px 24px; border-bottom: 1px solid var(--border-color) !important; }
        .table-custom td { padding: 18px 24px; border-bottom: 1px solid var(--border-color) !important; color: #e2e2e9 !important; background-color: transparent !important; }
        .table-custom tr { background-color: transparent !important; }
        .table-custom tr:hover td { background-color: rgba(255, 255, 255, 0.02) !important; }

        .badge-custom { padding: 6px 12px; font-weight: 600; font-size: 0.75rem; border-radius: 6px; text-transform: uppercase; display: inline-block; }
        .badge-admin { background-color: rgba(137, 80, 252, 0.15) !important; color: #a278ff !important; border: 1px solid rgba(137, 80, 252, 0.3) !important; }
        .badge-user { background-color: rgba(0, 207, 150, 0.15) !important; color: #00cf96 !important; border: 1px solid rgba(0, 207, 150, 0.3) !important; }
        
        .btn-action { width: 36px; height: 36px; padding: 0; display: inline-flex; align-items: center; justify-content: center; border-radius: 8px; transition: all 0.2s; border: 1px solid var(--border-color); color: #a2a2b3; background: rgba(255,255,255,0.02); text-decoration: none; }
        .btn-action:hover { color: #fff; background: var(--accent-purple); border-color: var(--accent-purple); }
        .btn-action.btn-delete:hover { background: #dc3545; border-color: #dc3545; }
        
        .btn-add { background: var(--accent-purple); border: none; color: white; padding: 12px 24px; border-radius: 8px; font-weight: 500; font-size: 0.9rem; transition: opacity 0.2s; }
        .btn-add:hover { opacity: 0.9; color: white; }

        .modal-content { background-color: #141424; color: #fff; border: 1px solid var(--border-color); border-radius: 16px; box-shadow: 0 20px 50px rgba(0,0,0,0.5); }
        .form-control, .form-select { background-color: #0f0f1a !important; border: 1px solid var(--border-color) !important; color: #fff !important; padding: 12px 16px; border-radius: 8px; }
        .form-control:focus, .form-select:focus { border-color: var(--accent-purple) !important; box-shadow: none !important; }
        .modal-header { border-bottom: 1px solid var(--border-color); padding: 22px 28px; }
        .modal-body { padding: 28px; }
        .modal-footer { border-top: 1px solid var(--border-color); padding: 20px 28px; }
    </style>
</head>
<body>

    <div class="sidebar">
        <div class="brand-logo">
            <div class="logo-icon"></div>
            <div>
                <h5 class="mb-0 fw-bold text-white" style="letter-spacing: 1px;">HERETIC</h5>
                <small style="font-size: 0.7rem; color: var(--accent-purple); font-weight: 700;">SUPER ADMIN</small>
            </div>
        </div>
        <div class="py-3">
            <p class="px-4 text-uppercase mb-2 mt-2" style="font-size: 0.75rem; letter-spacing: 1px; color: rgba(255,255,255,0.2);">Dashboards</p>
            <a href="index.php" class="nav-link-custom"><i class="fa-solid fa-chart-pie"></i> Dashboard</a>
            <a href="master_alat.php" class="nav-link-custom"><i class="fa-solid fa-boxes-stacked"></i> Master Alat (Stok)</a>
            <a href="transaksi.php" class="nav-link-custom"><i class="fa-solid fa-cart-flatbed"></i> Transaksi Baru</a>
            <a href="pengembalian.php" class="nav-link-custom"><i class="fa-solid fa-rotate-left"></i> Pengembalian Barang</a>
            <p class="px-4 text-uppercase mb-2 mt-4" style="font-size: 0.75rem; letter-spacing: 1px; color: rgba(255,255,255,0.2);">Management</p>
            <a href="manajemen_user.php" class="nav-link-custom active"><i class="fa-solid fa-users"></i> Kelola User</a>
            <a href="laporan.php" class="nav-link-custom"><i class="fa-solid fa-file-invoice-dollar"></i> Laporan Keuangan</a>
            <a href="../logout.php" class="nav-link-custom text-danger mt-4"><i class="fa-solid fa-power-off"></i> Keluar / Logout</a>
        </div>
    </div>

    <div class="main-content">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h3 class="fw-bold text-white mb-1">Manajemen Pengguna</h3>
                <small class="text-white-50">Kelola Hak Akses Super Admin, Admin Lapangan, dan Akun Pelanggan</small>
            </div>
            <button class="btn btn-add" data-bs-toggle="modal" data-bs-target="#modalTambah"><i class="fa-solid fa-user-plus me-2"></i>Tambah User</button>
        </div>

        <div class="card-custom">
            <div class="table-responsive">
                <table class="table-custom">
                    <thead>
                        <tr>
                            <th>Nama Lengkap</th>
                            <th>No. Telepon</th>
                            <th>Username</th>
                            <th>Akses / Role</th>
                            <th class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $query = mysqli_query($koneksi, "SELECT * FROM users ORDER BY id_user DESC");
                        $modals_edit = [];
                        
                        if ($query && mysqli_num_rows($query) > 0):
                            while($row = mysqli_fetch_assoc($query)):
                                // Membaca value array dari database (Kunci Mati)
                                $val_nama = $row['nama'] ?? '';
                                $val_telp = $row['no_telp'] ?? '';
                                $val_user = $row['username'] ?? '';
                                $val_role = $row['role'] ?? 'user';
                                
                                ob_start();
                        ?>
                        <div class="modal fade" id="modalEdit<?= $row['id_user']; ?>" tabindex="-1" aria-hidden="true">
                            <div class="modal-dialog modal-dialog-centered">
                                <form action="" method="POST" class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title fw-bold text-white">Edit Akses Pengguna</h5>
                                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                    </div>
                                    <div class="modal-body">
                                        <input type="hidden" name="id_user" value="<?= $row['id_user']; ?>">
                                        <div class="mb-3">
                                            <label class="form-label text-white-50">Nama Lengkap</label>
                                            <input type="text" name="nama_user" class="form-control" value="<?= htmlspecialchars($val_nama); ?>" required>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label text-white-50">No. Telepon</label>
                                            <input type="text" name="telepon" class="form-control" value="<?= htmlspecialchars($val_telp); ?>" required>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label text-white-50">Username</label>
                                            <input type="text" name="username" class="form-control" value="<?= htmlspecialchars($val_user); ?>" required>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label text-white-50">Password Baru <small class="text-warning">(Kosongkan jika tidak diganti)</small></label>
                                            <input type="password" name="password" class="form-control" placeholder="••••••••">
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label text-white-50">Akses Karyawan / Role</label>
                                            <select name="role" class="form-select" required>
                                                <option value="super_admin" <?= $val_role == 'super_admin' ? 'selected' : ''; ?>>Super Admin</option>
                                                <option value="admin" <?= $val_role == 'admin' ? 'selected' : ''; ?>>Admin Lapangan</option>
                                                <option value="user" <?= $val_role == 'user' ? 'selected' : ''; ?>>User / Pelanggan</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                                        <button type="submit" name="edit_user" class="btn btn-primary" style="background: var(--accent-purple); border:none;">Simpan Perubahan</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                        <?php 
                        $modals_edit[] = ob_get_clean(); 
                        
                        $is_admin = ($val_role == 'super_admin' || $val_role == 'admin');
                        $role_badge = $is_admin ? 'badge-admin' : 'badge-user';
                        $role_text = ($val_role == 'super_admin') ? 'Super Admin' : (($val_role == 'admin') ? 'Admin Lapangan' : 'User / Pelanggan');
                        ?>
                        <tr>
                            <td style="color: #ffffff !important; font-weight: 500;"><?= htmlspecialchars($val_nama); ?></td>
                            <td style="color: #a2a2b3 !important;"><?= htmlspecialchars($val_telp); ?></td>
                            <td class="text-info fw-semibold"><?= htmlspecialchars($val_user); ?></td>
                            <td><span class="badge-custom <?= $role_badge; ?>"><?= $role_text; ?></span></td>
                            <td class="text-center">
                                <button type="button" class="btn btn-action me-1" data-bs-toggle="modal" data-bs-target="#modalEdit<?= $row['id_user']; ?>" title="Edit User"><i class="fa-solid fa-user-pen"></i></button>
                                <a href="manajemen_user.php?hapus=<?= $row['id_user']; ?>" onclick="return confirm('Hapus pengguna ini?')" class="btn btn-action btn-delete" title="Hapus User"><i class="fa-solid fa-trash"></i></a>
                            </td>
                        </tr>
                        <?php endwhile; else: ?>
                        <tr>
                            <td colspan="5" class="text-center py-5 text-muted" style="color: #6c757d !important;">
                                <i class="fa-solid fa-users-slash d-block fs-2 mb-3" style="opacity:0.4;"></i>
                                Belum ada data pengguna yang terdaftar di database.
                            </td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <?= implode("\n", $modals_edit); ?>

    <div class="modal fade" id="modalTambah" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <form action="" method="POST" class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold text-white">Tambah Akun Pengguna</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label text-white-50">Nama Lengkap</label>
                        <input type="text" name="nama_user" class="form-control" placeholder="Contoh: Admin Lapangan Heretic" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label text-white-50">No. Telepon</label>
                        <input type="text" name="telepon" class="form-control" placeholder="08123xxxx" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label text-white-50">Username</label>
                        <input type="text" name="username" class="form-control" placeholder="username_heretic" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label text-white-50">Password</label>
                        <input type="password" name="password" class="form-control" placeholder="••••••••" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label text-white-50">Akses Karyawan / Role</label>
                        <select name="role" class="form-select" required>
                            <option value="user">User / Pelanggan</option>
                            <option value="admin">Admin Lapangan</option>
                            <option value="super_admin">Super Admin</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" name="tambah_user" class="btn btn-primary" style="background: var(--accent-purple); border:none;">Buat Akun</button>
                </div>
            </form>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>