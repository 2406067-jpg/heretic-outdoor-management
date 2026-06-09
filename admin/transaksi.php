<?php
include '../koneksi.php';
include 'auth_admin.php';

$success_msg = '';
$error_msg = '';

if (isset($_POST['proses_sewa'])) {
    $id_user = $_POST['id_user'];
    $tgl_sewa = $_POST['tgl_sewa'];
    $tgl_kembali = $_POST['tgl_kembali'];
    $arr_id_alat = $_POST['id_alat'];
    $arr_qty = $_POST['qty'];

    $durasi = (strtotime($tgl_kembali) - strtotime($tgl_sewa)) / (60 * 60 * 24);
    if ($durasi <= 0) $durasi = 1;

    $nota = "INV-" . strtoupper(substr(md5(time()), 0, 6));
    
    // Mulai hitung total bayar dari seluruh item di form
    $total_bayar_nota = 0;
    $valid_stok = true;

    // Validasi sisa stok dulu sebelum insert
    foreach ($arr_id_alat as $index => $id_alat) {
        $qty_diminta = (int)$arr_qty[$index];
        $res_stok = mysqli_query($koneksi, "SELECT stok, nama_alat FROM alat_rental WHERE id_alat='$id_alat'");
        $d_stok = mysqli_fetch_assoc($res_stok);
        if ($d_stok['stok'] < $qty_diminta) {
            $valid_stok = false;
            $error_msg = "Stok alat '" . $d_stok['nama_alat'] . "' tidak mencukupi (Sisa: " . $d_stok['stok'] . ").";
            break;
        }
    }

    if ($valid_stok) {
        // 1. Insert data induk ke tabel transaksi
        mysqli_query($koneksi, "INSERT INTO transaksi (nota_transaksi, id_user, tgl_sewa, tgl_kembali, total_bayar, status) VALUES ('$nota', '$id_user', '$tgl_sewa', '$tgl_kembali', 0, 'disewa')");
        $id_transaksi_baru = mysqli_insert_id($koneksi);

        // 2. Loop keranjang belanja untuk input ke detail_transaksi & hitung harga
        foreach ($arr_id_alat as $index => $id_alat) {
            $qty = (int)$arr_qty[$index];
            $res_harga = mysqli_query($koneksi, "SELECT harga_per_hari FROM alat_rental WHERE id_alat='$id_alat'");
            $d_harga = mysqli_fetch_assoc($res_harga);
            
            $subtotal_item = $d_harga['harga_per_hari'] * $durasi * $qty;
            $total_bayar_nota += $subtotal_item;

            // Insert ke tabel detail_transaksi kelompokmu
            mysqli_query($koneksi, "INSERT INTO detail_transaksi (id_transaksi, id_alat, qty, subtotal) VALUES ('$id_transaksi_baru', '$id_alat', '$qty', '$subtotal_item')");
            
            // Potong stok alat otomatis
            mysqli_query($koneksi, "UPDATE alat_rental SET stok = stok - $qty WHERE id_alat='$id_alat'");
        }

        // 3. Update nominal akhir total_bayar di tabel induk transaksi
        mysqli_query($koneksi, "UPDATE transaksi SET total_bayar='$total_bayar_nota' WHERE id_transaksi='$id_transaksi_baru'");
        $success_msg = "Nota $nota berhasil diproses! Total Tagihan: Rp " . number_format($total_bayar_nota, 0, ',', '.');
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Sewa Baru Multi-Item - Heretic</title>
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
        .card-custom { background: #ffffff; border: none; border-radius: 16px; padding: 30px; box-shadow: 0 10px 30px rgba(0,0,0,0.01); }
        .form-control, .form-select { border: 1px solid #efeef4 !important; padding: 12px; border-radius: 10px; background-color: #faf9fc; }
        .form-control:focus, .form-select:focus { border-color: #ea4492 !important; box-shadow: none !important; background-color: #fff; }
        .btn-add-item { background-color: #fff5f8; color: #ea4492; border: 1px dashed #ea4492; border-radius: 10px; padding: 10px; font-weight: 600; width: 100%; transition: all 0.2s; }
        .btn-add-item:hover { background-color: #ea4492; color: white; }
        .btn-submit { background: linear-gradient(135deg, #ea4492, #9c27b0); border: none; color: white; padding: 14px; border-radius: 12px; font-weight: 600; }
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
            <a href="transaksi.php" class="nav-link-custom active"><i class="fa-solid fa-cart-flatbed"></i> Sewa Baru</a>
            <a href="pengembalian.php" class="nav-link-custom"><i class="fa-solid fa-rotate-left"></i> Pengembalian</a>
            <a href="../logout.php" class="nav-link-custom text-danger mt-5"><i class="fa-solid fa-power-off"></i> Keluar / Logout</a>
        </div>
    </div>

    <div class="main-content">
        <div class="mb-4">
            <h3 class="fw-bold text-dark mb-1">Struk Sewa Baru (Multi-Alat)</h3>
            <p class="text-muted">Fitur kasir otomatis untuk menyewa banyak jenis alat sekaligus dalam satu invoice.</p>
        </div>

        <?php if($success_msg): ?>
            <div class="alert alert-success border-0 rounded-3 p-3 mb-4"><i class="fa-solid fa-circle-check me-2"></i><?= $success_msg; ?></div>
        <?php endif; ?>
        <?php if($error_msg): ?>
            <div class="alert alert-danger border-0 rounded-3 p-3 mb-4"><i class="fa-solid fa-triangle-exclamation"></i><?= $error_msg; ?></div>
        <?php endif; ?>

        <div class="card-custom">
            <form method="POST" action="">
                <div class="row g-3 mb-4">
                    <div class="col-md-4">
                        <label class="form-label small fw-bold text-secondary">Nama Penyewa</label>
                        <select name="id_user" class="form-select" required>
                            <option value="">-- Pilih Akun Member --</option>
                            <?php 
                            $users = mysqli_query($koneksi, "SELECT id_user, nama, nama_user FROM user WHERE role='user'");
                            while($u = mysqli_fetch_assoc($users)) {
                                $nama_tampil = $u['nama'] ?? $u['nama_user'];
                                echo "<option value='".$u['id_user']."'>".$nama_tampil."</option>";
                            }
                            ?>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small fw-bold text-secondary">Tanggal Sewa</label>
                        <input type="date" name="tgl_sewa" class="form-control" value="<?= date('Y-m-d'); ?>" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small fw-bold text-secondary">Tanggal Kembali</label>
                        <input type="date" name="tgl_kembali" class="form-control" required>
                    </div>
                </div>

                <hr style="border-color: #efeef4;">

                <h5 class="fw-bold text-dark mb-3">Daftar Alat yang Dipilih</h5>
                <div id="wrapper-alat">
                    <div class="row g-3 alignment-row mb-3">
                        <div class="col-md-8">
                            <select name="id_alat[]" class="form-select" required>
                                <option value="">-- Cari Nama Alat Rental --</option>
                                <?php 
                                $alats = mysqli_query($koneksi, "SELECT id_alat, nama_alat, stok, harga_per_hari FROM alat_rental WHERE stok > 0");
                                while($a = mysqli_fetch_assoc($alats)) {
                                    echo "<option value='".$a['id_alat']."'>".$a['nama_alat']." (Harga: Rp ".number_format($a['harga_per_hari'], 0, ',', '.')."/hari | Sisa Stok: ".$a['stok'].")</option>";
                                }
                                ?>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <input type="number" name="qty[]" class="form-control" min="1" value="1" placeholder="Qty" required>
                        </div>
                        <div class="col-md-1 d-flex align-items-center">
                            <button type="button" class="btn btn-link text-danger remove-row-btn" style="display:none;"><i class="fa-solid fa-trash-can fs-5"></i></button>
                        </div>
                    </div>
                </div>

                <div class="mb-4">
                    <button type="button" id="add-item-btn" class="btn btn-add-item"><i class="fa-solid fa-plus-circle me-2"></i>+ Tambah Alat Lain ke Nota Ini</button>
                </div>

                <button type="submit" name="proses_sewa" class="btn btn-submit w-100 shadow-sm">Simpan Transaksi & Cetak Struk</button>
            </form>
        </div>
    </div>

    <script>
        document.getElementById('add-item-btn').addEventListener('click', function() {
            var wrapper = document.getElementById('wrapper-alat');
            var firstRow = wrapper.querySelector('.row');
            var cloneRow = firstRow.cloneNode(true);
            
            // Bersihkan value clonningan
            cloneRow.querySelector('select').value = "";
            cloneRow.querySelector('input').value = "1";
            
            // Munculkan tombol hapus pada baris baru
            var deleteBtn = cloneRow.querySelector('.remove-row-btn');
            deleteBtn.style.display = "block";
            deleteBtn.addEventListener('click', function() {
                cloneRow.remove();
            });

            wrapper.appendChild(cloneRow);
        });
    </script>
</body>
</html>