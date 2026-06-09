<?php
include '../koneksi.php';
include '../auth.php';
cek_akses('super_admin');

if(isset($_POST['proses_sewa'])) {
    $nota = "TRX-" . date('YmdHis');
    $nama_penyewa = $_POST['nama_penyewa'];
    $tgl_sewa = $_POST['tgl_sewa'];
    $tgl_kembali = $_POST['tgl_kembali'];
    
    // Hitung Hari
    $hari = (strtotime($tgl_kembali) - strtotime($tgl_sewa)) / (60 * 60 * 24);
    if($hari <= 0) $hari = 1;

    $total_bayar_akhir = 0;
    $items = $_POST['items']; // Array id_alat dan jumlah

    // 1. Simpan data induk transaksi dulu
    mysqli_query($koneksi, "INSERT INTO transaksi VALUES ('', '$nota', '$nama_penyewa', '$tgl_sewa', '$tgl_kembali', 0, 'disewa')");
    $id_transaksi = mysqli_insert_id($koneksi);

    // 2. Loop list barang yang disewa
    foreach($items as $id_alat => $qty) {
        if($qty > 0) {
            // Ambil harga asli alat
            $q_harga = mysqli_query($koneksi, "SELECT harga_perhari, stok FROM alat WHERE id_alat='$id_alat'");
            $d_harga = mysqli_fetch_assoc($q_harga);
            
            $subtotal = $d_harga['harga_perhari'] * $qty * $hari;
            $total_bayar_akhir += $subtotal;

            // Masukkan ke detail item transaksi
            mysqli_query($koneksi, "INSERT INTO detail_transaksi VALUES ('', '$id_transaksi', '$id_alat', '$qty')");
            
            // POTONG STOK DI GUDANG SECARA OTOMATIS
            mysqli_query($koneksi, "UPDATE alat SET stok = stok - $qty WHERE id_alat='$id_alat'");
        }
    }

    // 3. Update total bayar akumulasi ke induk transaksi
    mysqli_query($koneksi, "UPDATE transaksi SET total_bayar='$total_bayar_akhir' WHERE id_transaksi='$id_transaksi'");
    echo "<script>alert('Transaksi Berhasil! Nota: $nota'); window.location='index.php';</script>";
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sewa Baru - Heretic</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        :root { --bg-main: #151521; --bg-card: #1e1e2d; --text-main: #a2a2b3; --text-light: #ffffff; --accent-purple: #8950fc; --border-color: rgba(255, 255, 255, 0.05); }
        body { background-color: var(--bg-main); color: var(--text-main); font-family: 'Inter', sans-serif; }
        .sidebar { height: 100vh; background-color: var(--bg-card); width: 260px; position: fixed; border-right: 1px solid var(--border-color); }
        .nav-link-custom { color: var(--text-main); padding: 12px 25px; display: flex; align-items: center; gap: 15px; text-decoration: none; font-size: 0.9rem; border-left: 3px solid transparent; }
        .nav-link-custom:hover, .nav-link-custom.active { color: var(--text-light); background: rgba(137, 80, 252, 0.1); border-left: 3px solid var(--accent-purple); }
        .main-content { margin-left: 260px; padding: 30px; }
        .card-custom { background-color: var(--bg-card); border: none; border-radius: 12px; padding: 25px; }
        .form-control { background-color: #151521; border: 1px solid var(--border-color); color: #fff; }
        .form-control:focus { background-color: #151521; color:#fff; border-color: var(--accent-purple); box-shadow: none; }
    </style>
</head>
<body>

    <div class="sidebar">
        <div class="p-4 border-bottom border-dark">
            <h5 class="mb-0 fw-bold text-white">HERETIC</h5>
            <small class="text-primary">SUPER ADMIN</small>
        </div>
        <div class="py-3">
            <a href="index.php" class="nav-link-custom"><i class="fa-solid fa-chart-pie"></i> Analytics</a>
            <a href="master_alat.php" class="nav-link-custom"><i class="fa-solid fa-boxes-stacked"></i> Master Stok</a>
            <a href="transaksi.php" class="nav-link-custom active"><i class="fa-solid fa-cart-flatbed text-primary"></i> Sewa Baru</a>
            <a href="pengembalian.php" class="nav-link-custom"><i class="fa-solid fa-rotate-left"></i> Pengembalian</a>
            <a href="laporan.php" class="nav-link-custom"><i class="fa-solid fa-file-invoice-dollar"></i> Laporan</a>
        </div>
    </div>

    <div class="main-content">
        <h3 class="fw-bold text-white mb-4">Input Sewa Multi-Item</h3>
        <form action="" method="POST" class="card-custom">
            <div class="row">
                <div class="col-md-4 mb-3">
                    <label class="form-label text-white-50">Nama Pelanggan / Penyewa</label>
                    <input type="text" name="nama_penyewa" class="form-control" required placeholder="Input nama lengkap">
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label text-white-50">Tanggal Pengambilan</label>
                    <input type="date" name="tgl_sewa" class="form-control" required value="<?= date('Y-m-d'); ?>">
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label text-white-50">Tanggal Pengembalian</label>
                    <input type="date" name="tgl_kembali" class="form-control" required value="<?= date('Y-m-d', strtotime('+1 day')); ?>">
                </div>
            </div>

            <h5 class="text-white fw-bold mt-4 mb-3 border-bottom border-secondary pb-2"><i class="fa-solid fa-list me-2 text-warning"></i>PILIH UNIT ALAT</h5>
            <div class="row">
                <?php
                $q_all_alat = mysqli_query($koneksi, "SELECT * FROM alat WHERE stok > 0");
                while($al = mysqli_fetch_assoc($q_all_alat)):
                ?>
                <div class="col-md-6 mb-3">
                    <div class="p-3 rounded" style="background-color: #151521; border: 1px solid var(--border-color);">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="text-white mb-1 fw-bold"><?= $al['nama_alat']; ?></h6>
                                <small class="text-muted">Tarif: Rp <?= number_format($al['harga_perhari'],0,',','.'); ?>/Hari | Sisa Stok: <span class="text-success fw-bold"><?= $al['stok']; ?></span></small>
                            </div>
                            <div style="width: 80px;">
                                <input type="number" name="items[<?= $al['id_alat']; ?>]" class="form-control text-center py-1" value="0" min="0" max="<?= $al['stok']; ?>">
                            </div>
                        </div>
                    </div>
                </div>
                <?php endwhile; ?>
            </div>

            <div class="text-end mt-4">
                <button type="reset" class="btn btn-secondary me-2">Reset Form</button>
                <button type="submit" name="proses_sewa" class="btn btn-primary px-4">Simpan Transaksi Sewa</button>
            </div>
        </form>
    </div>
</body>
</html>