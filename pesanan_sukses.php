<?php
/**
 * Halaman Konfirmasi Pesanan Berhasil Dibuat
 */
require_once __DIR__ . '/config/koneksi.php';
require_once __DIR__ . '/includes/fungsi.php';

require_login();
$judul_halaman = 'Pesanan Berhasil';

$id_user = (int) user_login()['id_user'];
$kode    = input('kode');

$pesanan = db_row('SELECT * FROM pesanan WHERE kode_pesanan = ? AND id_user = ?', [$kode, $id_user]);

if (!$pesanan) {
    set_flash('danger', 'Data pesanan tidak ditemukan.');
    redirect('pesanan_saya.php');
}

$rincian = db_all('SELECT * FROM detail_pesanan WHERE id_pesanan = ?', [(int)$pesanan['id_pesanan']]);

require_once __DIR__ . '/includes/header.php';
?>

<div class="text-center mb-4">
    <i class="bi bi-check-circle-fill text-success" style="font-size:4rem"></i>
    <h3 class="fw-bold mt-2 mb-1">Pesanan Berhasil Dibuat!</h3>
    <p class="text-muted mb-0">
        Terima kasih, pesanan Anda sudah tersimpan di sistem kami dan akan segera diproses.
    </p>
</div>

<div class="row justify-content-center">
    <div class="col-lg-9">
        <div class="alert alert-success d-flex align-items-center gap-2">
            <i class="bi bi-upc-scan fs-4"></i>
            <div>
                Kode pesanan Anda: <strong><?= e($pesanan['kode_pesanan']) ?></strong><br>
                <span class="small">Simpan kode ini untuk melacak status pesanan Anda.</span>
            </div>
        </div>

        <?php require __DIR__ . '/includes/kartu_pesanan.php'; ?>

        <div class="d-flex flex-wrap gap-2 justify-content-center mt-4">
            <a href="<?= BASE_URL ?>/pesanan_saya.php" class="btn btn-primary">
                <i class="bi bi-receipt me-1"></i>Lihat Pesanan Saya
            </a>
            <a href="<?= BASE_URL ?>/index.php" class="btn btn-outline-secondary">
                <i class="bi bi-shop me-1"></i>Lanjut Belanja
            </a>
            <button onclick="window.print()" class="btn btn-outline-secondary no-print">
                <i class="bi bi-printer me-1"></i>Cetak
            </button>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
