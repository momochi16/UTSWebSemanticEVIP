<?php
/**
 * Header halaman publik
 * Variabel opsional: $judul_halaman
 */
if (!defined('BASE_URL')) {
    require_once __DIR__ . '/../config/koneksi.php';
}
require_once __DIR__ . '/fungsi.php';

$judul_halaman = $judul_halaman ?? 'Beranda';
$jml_keranjang = jumlah_item_keranjang();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($judul_halaman) ?> &middot; <?= e(APP_NAME) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@500;600;700&family=Jost:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link href="<?= BASE_URL ?>/assets/css/style.css" rel="stylesheet">
</head>
<body class="d-flex flex-column min-vh-100 bg-body-tertiary">

<nav class="navbar navbar-expand-lg navbar-dark navbar-luxe sticky-top shadow-sm">
    <div class="container">
        <a class="navbar-brand fw-bold d-flex align-items-center gap-2" href="<?= BASE_URL ?>/index.php">
            <i class="bi bi-bag-heart-fill fs-4"></i>
            <span><?= e(APP_NAME) ?></span>
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navUtama">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navUtama">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                <li class="nav-item">
                    <a class="nav-link <?= $judul_halaman === 'Beranda' ? 'active' : '' ?>"
                       href="<?= BASE_URL ?>/index.php"><i class="bi bi-shop me-1"></i>Produk</a>
                </li>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown">
                        <i class="bi bi-grid me-1"></i>Kategori</a>
                    <ul class="dropdown-menu shadow">
                        <?php
                        $nav_kategori = db_all('SELECT id_kategori, nama_kategori FROM kategori ORDER BY nama_kategori');
                        foreach ($nav_kategori as $k):
                        ?>
                        <li><a class="dropdown-item"
                               href="<?= BASE_URL ?>/index.php?kategori=<?= (int)$k['id_kategori'] ?>">
                            <i class="bi bi-tag me-2 text-primary"></i><?= e($k['nama_kategori']) ?></a></li>
                        <?php endforeach; ?>
                    </ul>
                </li>
                <?php if (is_admin()): ?>
                <li class="nav-item">
                    <a class="nav-link" href="<?= BASE_URL ?>/admin/index.php">
                        <i class="bi bi-speedometer2 me-1"></i>Panel Admin</a>
                </li>
                <?php endif; ?>
            </ul>

            <form class="d-flex me-lg-3 mb-2 mb-lg-0" role="search" action="<?= BASE_URL ?>/index.php" method="get">
                <div class="input-group input-group-sm">
                    <input class="form-control" type="search" name="cari" placeholder="Cari produk..."
                           value="<?= e($_GET['cari'] ?? '') ?>">
                    <button class="btn btn-gold" type="submit"><i class="bi bi-search"></i></button>
                </div>
            </form>

            <ul class="navbar-nav align-items-lg-center">
                <li class="nav-item me-lg-2">
                    <a class="nav-link position-relative" href="<?= BASE_URL ?>/keranjang.php">
                        <i class="bi bi-cart3 fs-5"></i>
                        <span class="text-white-50 small d-lg-none">Keranjang</span>
                        <?php if ($jml_keranjang > 0): ?>
                        <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill text-bg-warning">
                            <?= $jml_keranjang ?>
                        </span>
                        <?php endif; ?>
                    </a>
                </li>

                <?php if (is_login()): $u = user_login(); ?>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown">
                        <i class="bi bi-person-circle me-1"></i><?= e($u['nama']) ?>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end shadow">
                        <li><h6 class="dropdown-header"><?= e($u['email']) ?></h6></li>
                        <li><a class="dropdown-item" href="<?= BASE_URL ?>/pesanan_saya.php">
                            <i class="bi bi-receipt me-2"></i>Pesanan Saya</a></li>
                        <?php if (is_admin()): ?>
                        <li><a class="dropdown-item" href="<?= BASE_URL ?>/admin/index.php">
                            <i class="bi bi-speedometer2 me-2"></i>Panel Admin</a></li>
                        <?php endif; ?>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item text-danger" href="<?= BASE_URL ?>/logout.php">
                            <i class="bi bi-box-arrow-right me-2"></i>Keluar</a></li>
                    </ul>
                </li>
                <?php else: ?>
                <li class="nav-item"><a class="nav-link" href="<?= BASE_URL ?>/login.php">
                    <i class="bi bi-box-arrow-in-right me-1"></i>Masuk</a></li>
                <li class="nav-item ms-lg-2">
                    <a class="btn btn-gold btn-sm fw-semibold" href="<?= BASE_URL ?>/register.php">Daftar</a>
                </li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>

<main class="flex-grow-1 py-4">
    <div class="container">
        <?php tampil_flash(); ?>
