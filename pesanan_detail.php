<?php
/**
 * Halaman Detail Pesanan (sisi pembeli)
 */
require_once __DIR__ . '/config/koneksi.php';
require_once __DIR__ . '/includes/fungsi.php';

require_login();
$judul_halaman = 'Detail Pesanan';

$id_user    = (int) user_login()['id_user'];
$id_pesanan = input_int('id');

$pesanan = db_row('SELECT * FROM pesanan WHERE id_pesanan = ? AND id_user = ?', [$id_pesanan, $id_user]);

if (!$pesanan) {
    set_flash('danger', 'Pesanan tidak ditemukan.');
    redirect('pesanan_saya.php');
}

$rincian = db_all('SELECT * FROM detail_pesanan WHERE id_pesanan = ?', [$id_pesanan]);

require_once __DIR__ . '/includes/header.php';
?>

<nav aria-label="breadcrumb">
    <ol class="breadcrumb small">
        <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/pesanan_saya.php" class="text-decoration-none">Pesanan Saya</a></li>
        <li class="breadcrumb-item active"><?= e($pesanan['kode_pesanan']) ?></li>
    </ol>
</nav>

<div class="row justify-content-center">
    <div class="col-lg-9">
        <?php require __DIR__ . '/includes/kartu_pesanan.php'; ?>

        <div class="d-flex flex-wrap gap-2 justify-content-center mt-4">
            <a href="<?= BASE_URL ?>/pesanan_saya.php" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-1"></i>Kembali
            </a>
            <button onclick="window.print()" class="btn btn-outline-secondary no-print">
                <i class="bi bi-printer me-1"></i>Cetak Invoice
            </button>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
