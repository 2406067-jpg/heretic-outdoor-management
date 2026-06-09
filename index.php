<?php
include 'koneksi.php';
session_start();
$cart_count = 0;
if (!empty($_SESSION['cart'])) {
    foreach ($_SESSION['cart'] as $item) {
        $cart_count += $item['qty'];
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Heretic Rental - Premium Gear</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        :root { --cyber-bg: #0b0d14; --cyber-card: #111422; --cyber-border: #1e2235; --cyber-green: #00cf96; --cyber-blue: #3d5afe; }
        body { background-color: var(--cyber-bg); color: #94a3b8; font-family: 'Plus Jakarta Sans', sans-serif; }
        
        /* HEADER STYLE */
        .navbar-custom { background-color: rgba(17, 20, 34, 0.95); backdrop-filter: blur(10px); border-bottom: 1px solid var(--cyber-border); py: 20px; }
        .badge-cart { background-color: var(--cyber-green); color: var(--cyber-bg); font-weight: bold; border-radius: 50%; }

        /* HERO BANNER STYLE */
        .hero-section { position: relative; min-height: 85vh; display: flex; align-items: center; overflow: hidden; padding-top: 80px; }
        .hero-glow-1 { position: absolute; top: 10%; left: 20%; width: 300px; height: 300px; background: rgba(0, 207, 150, 0.1); filter: blur(100px); border-radius: 50%; }
        .hero-glow-2 { position: absolute; bottom: 10%; right: 20%; width: 300px; height: 300px; background: rgba(61, 90, 254, 0.1); filter: blur(100px); border-radius: 50%; }
        .text-gradient { background: linear-gradient(to right, var(--cyber-green), var(--cyber-blue)); -webkit-background-clip: text; -webkit-text-fill-color: transparent; }
        
        /* CATALOG CARD STYLE */
        .cyber-card { background-color: var(--cyber-card); border: 1px solid var(--cyber-border); border-radius: 16px; transition: all 0.3s ease; overflow: hidden; }
        .cyber-card:hover { transform: translateY(-5px); border-color: var(--cyber-green); }
        .btn-cyber-primary { background-color: #3d5afe; border: none; color: white; padding: 10px 20px; border-radius: 8px; font-weight: 600; }
        .btn-cyber-primary:hover { background-color: #2b44d1; }
    </style>
</head>
<body>

    <nav class="navbar navbar-expand-lg navbar-dark navbar-custom fixed-top py-3">
        <div class="container">
            <a class="navbar-brand fw-bold text-white fs-4" href="index.php">HERETIC <span style="color: var(--cyber-green);">RENTAL</span></a>
            <div class="d-flex align-items-center gap-3">
                <a href="keranjang.php" class="btn btn-outline-light position-relative border-0 px-3" style="background: var(--cyber-card);">
                    <i class="fa-solid fa-shopping-cart text-info me-1"></i> Keranjang
                    <?php if($cart_count > 0): ?>
                        <span class="position-absolute top-0 start-100 translate-middle badge badge-cart text-dark px-2 bg-success rounded-circle" style="font-size: 11px;"><?= $cart_count; ?></span>
                    <?php endif; ?>
                </a>
            </div>
        </div>
    </nav>

    <section class="hero-section">
        <div class="hero-glow-1"></div>
        <div class="hero-glow-2"></div>
        <div class="container">
            <div class="row align-items-center g-5">
                <div class="col-lg-7 text-center text-lg-start">
                    <span class="badge px-3 py-2 mb-3" style="background: var(--cyber-card); color: var(--cyber-green); border: 1px solid rgba(0,207,150,0.3)">⚡ PREMIUM OUTDOOR & CAMERA GEAR</span>
                    <h1 class="text-white fw-extrabold display-4 mb-3">Sewa Alat <br><span class="text-gradient">Outdoor & Kamera</span><br>Berkualitas</h1>
                    <p class="fs-5 text-muted max-w-lg mb-4">Heretic Rental menyediakan berbagai peralatan camping gunung dan kamera profesional untuk petualangan serta kebutuhan kreator Anda. Mudah, aman, dan instan.</p>
                    <a href="#katalog" class="btn btn-cyber-primary btn-lg px-4 py-3 text-white">Jelajahi Katalog <i class="fa-solid fa-arrow-right ms-2"></i></a>
                </div>
                <div class="col-lg-5 d-none d-lg-block">
                    <div class="cyber-card p-5 text-center rotate-3" style="background: linear-gradient(135deg, rgba(0,207,150,0.1), rgba(61,90,254,0.1)); height: 350px; display:flex; align-items:center; justify-content:center;">
                        <div>
                            <i class="fa-solid fa-mountain-sun display-1 text-success mb-3"></i>
                            <i class="fa-solid fa-camera display-2 text-primary d-block"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <div class="container py-5" id="katalog">
        <h2 class="text-white fw-bold mb-4">Katalog Alat Tersedia</h2>
        <div class="row g-4">
            <?php
            $q = mysqli_query($koneksi, "SELECT * FROM alat");
            while ($row = mysqli_fetch_assoc($q)):
            ?>
            <div class="col-md-4">
                <div class="cyber-card h-100 p-4 d-flex flex-col justify-content-between">
                    <div>
                        <span class="badge text-uppercase mb-2 text-info" style="background: rgba(61,90,254,0.1);"><?= $row['kategori']; ?></span>
                        <h4 class="text-white fw-bold fs-5 mt-1"><?= htmlspecialchars($row['nama_alat']); ?></h4>
                        <p class="text-muted small my-3"><?= htmlspecialchars($row['deskripsi']); ?></p>
                    </div>
                    <div class="mt-4">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <span class="text-success fw-bold fs-5">Rp <?= number_format($row['harga_perhari'], 0, ',', '.'); ?><span class="text-muted small">/hari</span></span>
                            <span class="small text-muted">Stok: <b class="text-white"><?= $row['stok']; ?></b> unit</span>
                        </div>
                        <form action="api_keranjang.php?action=add" method="POST">
                            <input type="hidden" name="id_alat" value="<?= $row['id_alat']; ?>">
                            <?php if($row['stok'] > 0): ?>
                                <button type="submit" class="btn btn-cyber-primary w-100 py-2"><i class="fa-solid fa-cart-plus me-2"></i> Tambah Item</button>
                            <?php else: ?>
                                <button class="btn btn-secondary w-100 py-2" disabled>Habis Tersewa</button>
                            <?php endif; ?>
                        </form>
                    </div>
                </div>
            </div>
            <?php endwhile; ?>
        </div>
    </div>

</body>
</html>