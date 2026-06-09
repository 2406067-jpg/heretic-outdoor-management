<?php
// =========================================================================
// SYSTEM REVENUE & CORE DISPATCHER
// =========================================================================
if (session_status() === PHP_SESSION_NONE) { 
    session_start(); 
}
include '../koneksi.php';

// Ambil data tanggal global dari session (jika ada input dari modal index)
$default_rent = $_SESSION['rent_date'] ?? date('Y-m-d');
$default_return = $_SESSION['return_date'] ?? date('Y-m-d', strtotime('+1 days'));
$default_lama = $_SESSION['lama_sewa'] ?? 1;
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Keranjang Belanja Profesional - Heretic Rental</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: 'Plus Jakarta Sans', sans-serif;
        }
        body {
            background-color: #0f172a; /* Dark Navy core matching index.php */
            color: #f8fafc;
            min-height: 100vh;
            padding: 40px 20px;
        }
        .container {
            max-width: 1300px;
            margin: 0 auto;
        }
        .header-title {
            margin-bottom: 30px;
            border-bottom: 2px solid #1e293b;
            padding-bottom: 15px;
        }
        .header-title h1 {
            font-size: 28px;
            font-weight: 700;
            color: #ffffff;
        }
        .header-title p {
            color: #94a3b8;
            font-size: 14px;
            margin-top: 5px;
        }
        /* Layout Grid System */
        .main-layout {
            display: grid;
            grid-template-columns: 1fr 420px;
            gap: 30px;
            align-items: start;
        }
        @media (max-width: 992px) {
            .main-layout {
                grid-template-columns: 1fr;
            }
        }
        /* Left Column: Table Style */
        .card-table-container {
            background-color: #1e293b;
            border: 1px solid #334155;
            border-radius: 12px;
            padding: 24px;
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.3);
            overflow-x: auto;
        }
        .rental-table {
            width: 100%;
            border-collapse: collapse;
            text-align: left;
            font-size: 14px;
        }
        .rental-table th {
            background-color: #0f172a;
            color: #38bdf8;
            padding: 14px;
            font-weight: 600;
            text-transform: uppercase;
            font-size: 12px;
            letter-spacing: 0.5px;
            border-bottom: 2px solid #334155;
        }
        .rental-table td {
            padding: 16px 14px;
            border-bottom: 1px solid #334155;
            color: #e2e8f0;
            vertical-align: middle;
        }
        .rental-table tr:hover {
            background-color: rgba(51, 65, 85, 0.5);
        }
        .btn-delete {
            background-color: #ef4444;
            color: white;
            border: none;
            padding: 6px 12px;
            border-radius: 6px;
            cursor: pointer;
            text-decoration: none;
            font-size: 12px;
            font-weight: 600;
            transition: 0.2s;
            display: inline-block;
        }
        .btn-delete:hover {
            background-color: #dc2626;
            box-shadow: 0 0 10px rgba(239, 68, 68, 0.4);
        }
        .empty-alert {
            text-align: center;
            padding: 40px;
            color: #94a3b8;
            font-size: 16px;
        }
        /* Right Column: Checkout Form Style */
        .checkout-card {
            background-color: #1e293b;
            border: 1px solid #334155;
            border-radius: 12px;
            padding: 24px;
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.3);
        }
        .checkout-card-header {
            background-color: #0f172a;
            padding: 12px 16px;
            border-radius: 8px;
            margin-bottom: 24px;
            border-left: 4px solid #10b981;
        }
        .checkout-card-header h5 {
            margin: 0;
            font-size: 16px;
            font-weight: 600;
            color: #ffffff;
        }
        .form-section-title {
            margin: 20px 0 12px 0;
            font-weight: 600;
            color: #38bdf8;
            font-size: 13px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            border-bottom: 1px solid #334155;
            padding-bottom: 6px;
        }
        .form-group {
            margin-bottom: 14px;
        }
        .form-group label {
            display: block;
            font-size: 12px;
            font-weight: 500;
            margin-bottom: 6px;
            color: #94a3b8;
        }
        .form-control {
            width: 100%;
            padding: 8px 12px;
            font-size: 13px;
            background-color: #0f172a;
            border: 1px solid #334155;
            border-radius: 6px;
            color: #ffffff;
            transition: 0.2s;
        }
        .form-control:focus {
            outline: none;
            border-color: #10b981;
            box-shadow: 0 0 5px rgba(16, 185, 129, 0.3);
        }
        .duration-box {
            margin-top: 12px;
            padding: 12px;
            background: #0f172a;
            border: 1px dashed #334155;
            border-radius: 6px;
            text-align: center;
        }
        .duration-box span {
            font-size: 11px;
            color: #94a3b8;
        }
        .duration-box strong {
            display: block;
            font-size: 15px;
            color: #10b981;
            margin-top: 2px;
        }
        .radio-group {
            display: flex;
            gap: 15px;
            margin-bottom: 10px;
            font-size: 13px;
        }
        .radio-group label {
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 6px;
            color: #e2e8f0;
        }
        /* Financial Summary Box */
        .summary-box {
            margin-top: 20px;
            margin-bottom: 20px;
            padding: 16px;
            background: #0f172a;
            border: 1px solid #334155;
            border-radius: 8px;
            font-size: 13px;
        }
        .summary-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 8px;
        }
        .summary-row span:first-child {
            color: #94a3b8;
        }
        .summary-row span:last-child {
            font-weight: 600;
            color: #e2e8f0;
        }
        .summary-total {
            display: flex;
            justify-content: space-between;
            font-weight: 700;
            font-size: 16px;
            color: #10b981;
            border-top: 1px solid #334155;
            margin-top: 10px;
            padding-top: 10px;
        }
        /* System Operational Buttons */
        .btn-submit {
            width: 100%;
            background-color: #10b981;
            color: #ffffff;
            border: none;
            padding: 12px;
            border-radius: 6px;
            font-weight: 600;
            cursor: pointer;
            font-size: 14px;
            transition: 0.2s;
        }
        .btn-submit:hover {
            background-color: #059669;
            box-shadow: 0 0 15px rgba(16, 185, 129, 0.4);
        }
        .btn-draft {
            width: 100%;
            background: transparent;
            color: #94a3b8;
            border: 1px solid #334155;
            padding: 8px;
            border-radius: 6px;
            font-weight: 500;
            cursor: pointer;
            font-size: 12px;
            margin-top: 8px;
            transition: 0.2s;
        }
        .btn-draft:hover {
            color: #ffffff;
            border-color: #94a3b8;
        }
    </style>
</head>
<body>

<div class="container">
    <div class="header-title">
        <h1><i class="fa-solid fa-cart-shopping text-success me-2"></i>Keranjang Sewa Kamu</h1>
        <p>Periksa kembali daftar unit alat camp, kamera, atau workspace yang ingin kamu sewa sebelum mengajukan kontrak booking resmi.</p>
    </div>

    <div class="main-layout">
        
        <div class="card-table-container">
            <table class="rental-table">
                <thead>
                    <tr>
                        <th>Nama Alat / Workspace</th>
                        <th>Harga / Hari</th>
                        <th>Jumlah Unit</th>
                        <th>Subtotal Basis</th>
                        <th style="text-align: center;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $total_seluruh_alat = 0;
                    if (!empty($_SESSION['keranjang'])) {
                        foreach ($_SESSION['keranjang'] as $id_alat => $value) {
                            // Query proteksi penarikan data master dari MySQL
                            $query = mysqli_query($koneksi, "SELECT * FROM alat WHERE id_alat = '$id_alat'");
                            $data  = mysqli_fetch_assoc($query);

                            if ($data) {
                                // Menghindari crash runtime tipe data string*array
                                $qty = is_array($value) ? ($value['qty'] ?? 1) : (int)$value;
                                $harga = (int)$data['harga_perhari'];
                                $subtotal_item = $harga * $qty;
                                $total_seluruh_alat += $subtotal_item;
                                ?>
                                <tr>
                                    <td style="font-weight: 600; color: #ffffff;"><?= htmlspecialchars($data['nama_alat']); ?></td>
                                    <td>Rp <?= number_format($harga, 0, ',', '.'); ?></td>
                                    <td style="font-weight: 600; color: #38bdf8;"><?= $qty; ?> Unit</td>
                                    <td style="font-weight: 600; color: #10b981;">Rp <?= number_format($subtotal_item, 0, ',', '.'); ?></td>
                                    <td style="text-align: center;">
                                        <a href="hapus_keranjang.php?id=<?= $id_alat; ?>" class="btn-delete"><i class="fa-solid fa-trash me-1"></i>Hapus</a>
                                    </td>
                                </tr>
                                <?php
                            }
                        }
                    } else {
                        echo "<tr><td colspan='5' class='empty-alert'><i class='fa-solid fa-folder-open d-block mb-2 fs-3'></i>Keranjang belanja sewa Anda masih kosong murni.</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>

        <div class="checkout-card">
            <div class="checkout-card-header">
                <h5>Ringkasan & Form Checkout</h5>
            </div>

            <form action="proses_checkout.php" method="POST" enctype="multipart/form-data">
                
                <div class="form-section-title">Data Penyewa</div>
                <div class="form-group">
                    <label>Nama Lengkap</label>
                    <input type="text" name="customer_name" class="form-control" required placeholder="Nama sesuai identitas resmi">
                </div>
                <div class="form-group">
                    <label>Email Aktif</label>
                    <input type="email" name="customer_email" class="form-control" required placeholder="nama@domain.com">
                </div>
                <div class="form-group">
                    <label>No. HP / WhatsApp</label>
                    <input type="tel" name="customer_phone" class="form-control" required placeholder="Contoh: 081234567890">
                </div>

                <div class="form-section-title">Detail Penyewaan</div>
                <div class="form-group">
                    <label>Tanggal Mulai Sewa</label>
                    <input type="date" id="tgl_mulai" name="rent_date" class="form-control" value="<?= $default_rent; ?>" required onchange="hitungSistemFinansial()">
                </div>
                <div class="form-group">
                    <label>Tanggal Pengembalian</label>
                    <input type="date" id="tgl_selesai" name="return_date" class="form-control" value="<?= $default_return; ?>" required onchange="hitungSistemFinansial()">
                </div>
                <div class="duration-box">
                    <span>Durasi Kontrak Operasional:</span>
                    <strong id="display_durasi"><?= $default_lama; ?> Hari Sewa</strong>
                </div>

                <div class="form-section-title">Verifikasi & Jaminan Hukum</div>
                <div class="form-group">
                    <label>Jenis Jaminan Utama</label>
                    <select name="jenis_jaminan" class="form-control" style="background-color: #0f172a;">
                        <option value="KTP">KTP (Kartu Tanda Penduduk)</option>
                        <option value="SIM">SIM (Surat Izin Mengemudi)</option>
                        <option value="KTM">KTM (Kartu Tanda Mahasiswa)</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Nomor Identitas Resmi</label>
                    <input type="text" name="nomor_identitas" class="form-control" required placeholder="NIK KTP / Nomor SIM">
                </div>
                <div class="form-group">
                    <label>Upload Foto Identitas Asli</label>
                    <input type="file" name="foto_identitas" class="form-control" accept="image/*" required>
                </div>
                <div class="form-group">
                    <label>Upload Foto Selfie Bersama Identitas</label>
                    <input type="file" name="foto_selfie" class="form-control" accept="image/*" required>
                </div>

                <div class="form-section-title">Metode Pengambilan</div>
                <div class="radio-group">
                    <label>
                        <input type="radio" name="metode_ambil" id="ambil_sendiri" value="Ambil Sendiri" checked onchange="toggleAlamatSistem(false)"> Ambil di Store
                    </label>
                    <label>
                        <input type="radio" name="metode_ambil" id="diantar_kurir" value="Diantar Kurir" onchange="toggleAlamatSistem(true)"> Diantar Kurir
                    </label>
                </div>
                <div id="kolom_alamat" class="form-group" style="display: none;">
                    <label>Alamat Pengiriman Lengkap</label>
                    <textarea name="customer_address" class="form-control" rows="2" placeholder="Tulis nama jalan, RT/RW, nomor rumah, & kecamatan"></textarea>
                </div>

                <div class="form-section-title">Sistem Pembayaran</div>
                <div class="form-group">
                    <select name="metode_bayar" class="form-control" style="background-color: #0f172a;">
                        <option value="Transfer Bank">Transfer Bank Manual</option>
                        <option value="QRIS">QRIS / E-Wallet Instant</option>
                        <option value="Bayar di Tempat">COD (Bayar Tunai di Kasir)</option>
                    </select>
                </div>

                <div class="form-section-title">Catatan Tambahan</div>
                <div class="form-group">
                    <textarea name="catatan_tambahan" class="form-control" rows="2" placeholder="Contoh: warna tas carrier / request lensa kamera..."></textarea>
                </div>

                <div class="summary-box">
                    <div class="summary-row">
                        <span>Total Alat Terpilih:</span>
                        <span>Rp <span id="display_subtotal_alat"><?= number_format($total_seluruh_alat, 0, ',', '.'); ?></span></span>
                    </div>
                    <div class="summary-row">
                        <span>Deposit Jaminan Wajib:</span>
                        <span>Rp 100.000</span>
                    </div>
                    <div class="summary-row" style="color: #f87171;">
                        <span>Denda Terlambat / Hari:</span>
                        <span>Rp 20.000</span>
                    </div>
                    <div class="summary-total">
                        <span>Total Bayar:</span>
                        <span id="grand_total_display">Rp 0</span>
                    </div>
                </div>

                <button type="submit" class="btn-submit"><i class="fa-solid fa-paper-plane me-2"></i>Ajukan Booking Sewa</button>
                <button type="button" class="btn-draft">Simpan Ke Draft Rencana</button>
            </form>
        </div>

    </div>
</div>

<script>
function toggleAlamatSistem(show) {
    const kolomAlamat = document.getElementById('kolom_alamat');
    kolomAlamat.style.display = show ? 'block' : 'none';
    const textarea = kolomAlamat.querySelector('textarea');
    if(show) {
        textarea.setAttribute('required', 'required');
    } else {
        textarea.removeAttribute('required');
    }
}

function hitungSistemFinansial() {
    const tglMulaiValue = document.getElementById('tgl_mulai').value;
    const tglSelesaiValue = document.getElementById('tgl_selesai').value;
    const displayDurasi = document.getElementById('display_durasi');
    const displaySubtotalAlat = document.getElementById('display_subtotal_alat');
    const grandTotalDisplay = document.getElementById('grand_total_display');
    
    // Tarik nilai murni akumulasi alat PHP dari server side 
    const totalBasisAlatPHP = <?= $total_seluruh_alat; ?>;
    const depositWajib = 100000;

    if (tglMulaiValue && tglSelesaiValue) {
        const date1 = new Date(tglMulaiValue);
        const date2 = new Date(tglSelesaiValue);
        
        // Kalkulasi selisih hari absolut
        const diffTime = date2 - date1;
        let diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
        
        if (diffDays <= 0) {
            diffDays = 1; // Proteksi minimal sewa 1 hari operasional
        }

        // Rumus Multiplier Durasi Hari
        const akumulasiAlatFinal = totalBasisAlatPHP * diffDays;
        const totalFinalBayar = akumulasiAlatFinal + depositWajib;

        // Render data ke elemen web user
        displayDurasi.innerHTML = diffDays + " Hari Sewa";
        displaySubtotalAlat.innerHTML = akumulasiAlatFinal.toLocaleString('id-ID');
        grandTotalDisplay.innerHTML = "Rp " + totalFinalBayar.toLocaleString('id-ID');
    } else {
        // Fallback render jika tanggal belum dipilih lengkap
        const totalAwal = totalBasisAlatPHP + depositWajib;
        grandTotalDisplay.innerHTML = "Rp " + totalAwal.toLocaleString('id-ID');
    }
}

// Jalankan fungsi kalkulator finansial secara otomatis saat halaman pertama kali dimuat
document.addEventListener("DOMContentLoaded", function() {
    hitungSistemFinansial();
});
</script>

</body>
</html>