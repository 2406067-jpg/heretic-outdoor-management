<?php
// Hubungkan ke koneksi database utama
include '../koneksi.php';
if (session_status() === PHP_SESSION_NONE) { session_start(); }

// Ambil ID Transaksi dari parameter URL (?id=...)
$id_transaksi = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Ambil data transaksi utama dari tabel transaksi berdasarkan ID
$query = mysqli_query($koneksi, "SELECT * FROM transaksi WHERE id_transaksi = '$id_transaksi' LIMIT 1");
$trx = mysqli_fetch_assoc($query);

// Fallback preventif jika data tidak ditemukan
if (!$trx) {
    echo "<script>alert('Data transaksi tidak ditemukan!'); window.location='keranjang.php';</script>";
    exit();
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice Pembayaran #<?= $trx['invoice_code']; ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { background-color: #0f172a; color: #f8fafc; font-family: 'Plus Jakarta Sans', sans-serif; padding: 40px 20px; margin: 0; }
        .invoice-box { max-width: 650px; margin: 0 auto; background-color: #1e293b; border: 1px solid #334155; border-radius: 16px; padding: 35px; box-shadow: 0 20px 25px -5px rgba(0,0,0,0.4); }
        .success-banner { background: linear-gradient(135deg, #10b981, #059669); color: white; padding: 18px; border-radius: 10px; text-align: center; margin-bottom: 30px; font-weight: 700; font-size: 16px; box-shadow: 0 4px 12px rgba(16, 185, 129, 0.2); }
        .invoice-header { display: flex; justify-content: space-between; align-items: center; border-bottom: 2px solid #334155; padding-bottom: 15px; margin-bottom: 25px; }
        .invoice-header h2 { margin: 0; font-size: 20px; font-weight: 700; color: #38bdf8; }
        .invoice-header .badge { background: #334155; color: #38bdf8; padding: 6px 12px; border-radius: 20px; font-size: 12px; font-weight: 600; text-transform: uppercase; }
        .row-meta { display: flex; justify-content: space-between; margin-bottom: 14px; font-size: 14px; border-bottom: 1px dashed #334155; padding-bottom: 10px; }
        .row-meta span:first-child { color: #94a3b8; font-weight: 500; }
        .row-meta span:last-child { color: #f1f5f9; font-weight: 600; text-align: right; }
        .total-pay-box { background: rgba(56, 189, 248, 0.05); border: 1px solid rgba(56, 189, 248, 0.2); padding: 20px; border-radius: 12px; display: flex; justify-content: space-between; align-items: center; margin-top: 30px; }
        .total-pay-box span { font-size: 14px; color: #94a3b8; font-weight: 600; }
        .total-pay-box h3 { margin: 0; font-size: 24px; font-weight: 700; color: #10b981; }
        .btn-back { display: flex; align-items: center; justify-content: center; background: #38bdf8; color: #0f172a; padding: 14px; border-radius: 8px; text-decoration: none; font-weight: 700; margin-top: 30px; transition: all 0.2s ease; font-size: 15px; box-shadow: 0 4px 14px rgba(56, 189, 248, 0.3); }
        .btn-back:hover { background: #0ea5e9; transform: translateY(-1px); box-shadow: 0 6px 20px rgba(14, 165, 233, 0.4); }
        .info-note { font-size: 12px; color: #94a3b8; text-align: center; margin-top: 20px; line-height: 1.6; }
    </style>
</head>
<body>

<div class="invoice-box">
    <div class="success-banner">
        <i class="fa-solid fa-circle-check me-2"></i> Pengajuan Kontrak Booking Berhasil Disimpan!
    </div>
    
    <div class="invoice-header">
        <h2><i class="fa-solid fa-file-invoice text-info me-2"></i>Rincian Nota Sewa</h2>
        <span class="badge"><?= htmlspecialchars($trx['status']); ?></span>
    </div>

    <div class="row-meta"><span>Kode Invoice</span><span><?= $trx['invoice_code']; ?></span></div>
    <div class="row-meta"><span>Nama Penyewa</span><span><?= htmlspecialchars($trx['customer_name']); ?></span></div>
    <div class="row-meta"><span>Nomor WhatsApp</span><span><?= htmlspecialchars($trx['customer_phone']); ?></span></div>
    <div class="row-meta"><span>Tanggal Mulai</span><span><?= date('d M Y', strtotime($trx['rent_date'])); ?></span></div>
    <div class="row-meta"><span>Tanggal Kembali</span><span><?= date('d M Y', strtotime($trx['return_date'])); ?></span></div>
    <div class="row-meta"><span>Metode Pengambilan</span><span><?= htmlspecialchars($trx['metode_ambil']); ?></span></div>
    <div class="row-meta"><span>Sistem Pembayaran</span><span><?= htmlspecialchars($trx['metode_bayar']); ?></span></div>
    <div class="row-meta"><span>Uang Jaminan (Deposit)</span><span>Rp <?= number_format($trx['deposit'], 0, ',', '.'); ?></span></div>
    
    <?php if(!empty($trx['catatan_tambahan'])): ?>
        <div class="row-meta"><span>Catatan Tambahan</span><span><?= htmlspecialchars($trx['catatan_tambahan']); ?></span></div>
    <?php endif; ?>

    <div class="total-pay-box">
        <span>TOTAL AKUMULASI BIAYA</span>
        <h3>Rp <?= number_format($trx['total_bayar'], 0, ',', '.'); ?></h3>
    </div>

    <a href="keranjang.php" class="btn-back"><i class="fa-solid fa-arrow-left me-2"></i>Kembali ke Katalog Alat</a>
    
    <div class="info-note">
        *Silakan lakukan pembayaran sesuai dengan metode pilihan Anda.<br>Admin akan memvalidasi foto identitas fisik Anda dalam waktu maksimal 1x24 jam.
    </div>
</div>

</body>
</html>