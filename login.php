<?php
session_start();
include 'koneksi.php';

$error = '';
if (isset($_POST['login'])) {
    $username = mysqli_real_escape_string($koneksi, $_POST['username']);
    // Enkripsi MD5 ditambahkan di sini agar sinkron dengan manajemen_user.php
    $password = md5(mysqli_real_escape_string($koneksi, $_POST['password']));

    // -----------------------------------------------------------------
    // BUG BOUNCER / AKUN DARURAT SUPER ADMIN
    // Jika database kosong, kamu bisa masuk pakai akun di bawah ini:
    // -----------------------------------------------------------------
    if ($username === 'superadmin' && $_POST['password'] === 'admin123') {
        $_SESSION['id_user'] = 999;
        $_SESSION['username'] = 'superadmin';
        $_SESSION['nama_lengkap'] = 'Super Admin Heretic';
        $_SESSION['role'] = 'super_admin';

        header("Location: super_admin/index.php");
        exit();
    }
    // -----------------------------------------------------------------

    // Query pencarian ke database asli
    $query = mysqli_query($koneksi, "SELECT * FROM users WHERE username='$username' AND password='$password'");
    
    if ($query && mysqli_num_rows($query) === 1) {
        $data = mysqli_fetch_assoc($query);
        $_SESSION['id_user'] = $data['id_user'];
        $_SESSION['username'] = $data['username'];
        
        // Menangani jika di database kolomnya bernama 'nama' atau 'nama_lengkap'
        $_SESSION['nama_lengkap'] = $data['nama'] ?? $data['nama_user'] ?? $data['nama_lengkap'] ?? 'Admin';
        $_SESSION['role'] = $data['role'];

        if ($data['role'] == 'super_admin') {
            header("Location: super_admin/index.php");
        } elseif ($data['role'] == 'admin') {
            header("Location: admin/index.php");
        } else {
            header("Location: user/index.php");
        }
        exit();
    } else {
        $error = 'Username atau Password salah!';
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Login - PT. Heretic Rental</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background: #0f0f1a; color: #fff; display: flex; align-items: center; justify-content: center; height: 100vh; font-family: 'Inter', sans-serif; }
        .card { background: #141424; border: 1px solid rgba(255,255,255,0.05); border-radius: 16px; width: 400px; box-shadow: 0 20px 50px rgba(0,0,0,0.5) !important; }
        .form-control { background-color: #0f0f1a !important; border: 1px solid rgba(255,255,255,0.05) !important; color: #fff !important; padding: 12px; }
        .form-control:focus { border-color: #8950fc !important; box-shadow: none !important; }
        .btn-warning { background: #8950fc !important; border: none !important; color: #fff !important; padding: 12px; border-radius: 8px; font-weight: 600; }
        .btn-warning:hover { opacity: 0.9; }
    </style>
</head>
<body>
    <div class="card p-4 shadow">
        <h3 class="text-center mb-1 fw-bold text-white" style="letter-spacing: 1px;">HERETIC RENTAL</h3>
        <p class="text-center text-muted small mb-4">Silakan masuk ke akun panel Anda</p>
        
        <?php if($error): ?>
            <div class="alert alert-danger p-2 text-center small" style="background: rgba(220,53,69,0.1); border: 1px solid rgba(220,53,69,0.2); color: #ff6b7b;"><?= $error; ?></div>
        <?php endif; ?>
        
        <form method="POST">
            <div class="mb-3">
                <label class="form-label text-white-50 small">Username</label>
                <input type="text" name="username" class="form-control" placeholder="Masukkan username" required>
            </div>
            <div class="mb-4">
                <label class="form-label text-white-50 small">Password</label>
                <input type="password" name="password" class="form-control" placeholder="••••••••" required>
            </div>
            <button type="submit" name="login" class="btn btn-warning w-100">Masuk Aplikasi</button>
        </form>
    </div>
</body>
</html>