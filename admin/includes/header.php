<?php
/**
 * Layout header Panel Admin
 * Variabel opsional: $judul_halaman, $menu_aktif
 */
require_once __DIR__ . '/../../config/koneksi.php';
require_once __DIR__ . '/../../includes/fungsi.php';
require_admin();

$judul_halaman = $judul_halaman ?? 'Dashboard';
$menu_aktif    = $menu_aktif ?? 'dashboard';

$menu = [
    'dashboard' => ['Dashboard',        'speedometer2', 'admin/index.php'],
    'produk'    => ['Data Produk',      'box-seam',     'admin/produk.php'],
    'kategori'  => ['Kategori',         'tags',         'admin/kategori.php'],
    'pesanan'   => ['Pesanan Masuk',    'receipt',      'admin/pesanan.php'],
];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($judul_halaman) ?> &middot; Admin <?= e(APP_NAME) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@500;600;700&family=Jost:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link href="<?= BASE_URL ?>/assets/css/style.css" rel="stylesheet">
</head>
<body class="bg-body-tertiary">
<div class="container-fluid px-0">

    <!-- Navbar menu admin (atas) -->
    <nav class="navbar navbar-expand-lg navbar-dark navbar-luxe navbar-admin shadow-sm px-3 sticky-top">
        <a href="<?= BASE_URL ?>/admin/index.php"
           class="navbar-brand d-flex align-items-center gap-2 fw-bold mb-0">
            <i class="bi bi-bag-heart-fill"></i><span><?= e(APP_NAME) ?></span>
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse"
                data-bs-target="#menuAdmin" aria-controls="menuAdmin" aria-expanded="false">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="menuAdmin">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                <?php foreach ($menu as $key => [$label, $ikon, $url]): ?>
                <li class="nav-item">
                    <a class="nav-link d-flex align-items-center gap-2 <?= $menu_aktif === $key ? 'active' : '' ?>"
                       href="<?= BASE_URL ?>/<?= $url ?>">
                        <i class="bi bi-<?= $ikon ?>"></i><?= e($label) ?>
                    </a>
                </li>
                <?php endforeach; ?>
            </ul>
            <div class="navbar-nav">
                <div class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                        <i class="bi bi-person-circle me-1"></i><?= e(user_login()['nama']) ?>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end shadow">
                        <li><h6 class="dropdown-header"><?= e(user_login()['email']) ?></h6></li>
                        <li><a class="dropdown-item" href="<?= BASE_URL ?>/index.php" target="_blank">
                            <i class="bi bi-shop me-2"></i>Lihat Toko</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item text-danger" href="<?= BASE_URL ?>/logout.php">
                            <i class="bi bi-box-arrow-right me-2"></i>Keluar</a></li>
                    </ul>
                </div>
            </div>
        </div>
    </nav>

    <div class="p-3 p-lg-4">
        <h4 class="fw-bold mb-3"><?= e($judul_halaman) ?></h4>
        <?php tampil_flash(); ?>
