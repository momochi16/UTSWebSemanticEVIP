<?php
/**
 * Panel Admin - Detail Pesanan & Perubahan Status
 */
require_once __DIR__ . '/../config/koneksi.php';
require_once __DIR__ . '/../includes/fungsi.php';
require_admin();

$judul_halaman = 'Detail Pesanan';
$menu_aktif    = 'pesanan';

$id_pesanan = input_int('id');
$pesanan    = db_row(
    'SELECT p.*, u.nama AS nama_pembeli, u.email
       FROM pesanan p JOIN users u ON u.id_user = p.id_user
      WHERE p.id_pesanan = ?',
    [$id_pesanan]
);

if (!$pesanan) {
    set_flash('danger', 'Pesanan tidak ditemukan.');
    redirect('admin/pesanan.php');
}

// ------------------------- Ubah status pesanan -------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $status_baru = input('status');
    if (!in_array($status_baru, ['menunggu', 'diproses', 'dikirim', 'selesai', 'dibatalkan'], true)) {
        set_flash('danger', 'Status tidak valid.');
    } else {
        db_exec('UPDATE pesanan SET status = ? WHERE id_pesanan = ?', [$status_baru, $id_pesanan]);
        set_flash('success', 'Status pesanan ' . $pesanan['kode_pesanan'] . ' berhasil diubah menjadi '
            . ucfirst($status_baru) . '.');
    }
    redirect('admin/pesanan_detail.php?id=' . $id_pesanan);
}

$rincian = db_all('SELECT * FROM detail_pesanan WHERE id_pesanan = ?', [$id_pesanan]);

require __DIR__ . '/includes/header.php';
?>

<nav aria-label="breadcrumb">
    <ol class="breadcrumb small">
        <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/admin/pesanan.php" class="text-decoration-none">Pesanan Masuk</a></li>
        <li class="breadcrumb-item active"><?= e($pesanan['kode_pesanan']) ?></li>
    </ol>
</nav>

<div class="row g-4">
    <div class="col-lg-8">
        <?php
        $subtotal = array_sum(array_map(fn($r) => (float)$r['subtotal'], $rincian));
        require __DIR__ . '/../includes/kartu_pesanan.php';
        ?>
    </div>

    <div class="col-lg-4">
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white fw-semibold">
                <i class="bi bi-person-badge me-1 text-primary"></i>Informasi Pembeli
            </div>
            <ul class="list-group list-group-flush">
                <li class="list-group-item d-flex justify-content-between">
                    <span class="text-muted small">Nama Akun</span>
                    <span class="small fw-semibold"><?= e($pesanan['nama_pembeli']) ?></span>
                </li>
                <li class="list-group-item d-flex justify-content-between">
                    <span class="text-muted small">Email</span>
                    <span class="small"><?= e($pesanan['email']) ?></span>
                </li>
                <li class="list-group-item d-flex justify-content-between">
                    <span class="text-muted small">Tanggal Pesan</span>
                    <span class="small"><?= date('d/m/Y H:i', strtotime($pesanan['created_at'])) ?></span>
                </li>
            </ul>
        </div>

        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white fw-semibold">
                <i class="bi bi-arrow-repeat me-1 text-primary"></i>Ubah Status Pesanan
            </div>
            <div class="card-body">
                <p class="small text-muted">
                    Status saat ini: <?= label_status($pesanan['status']) ?>
                </p>
                <form method="post">
                    <div class="mb-3">
                        <select name="status" class="form-select">
                            <?php foreach (['menunggu' => 'Menunggu', 'diproses' => 'Diproses', 'dikirim' => 'Dikirim',
                                            'selesai' => 'Selesai', 'dibatalkan' => 'Dibatalkan'] as $v => $l): ?>
                            <option value="<?= $v ?>" <?= $pesanan['status'] === $v ? 'selected' : '' ?>>
                                <?= e($l) ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <button class="btn btn-primary w-100"><i class="bi bi-check2 me-1"></i>Simpan Status</button>
                </form>

                <hr class="my-3">
                <button onclick="window.print()" class="btn btn-outline-secondary w-100 no-print">
                    <i class="bi bi-printer me-1"></i>Cetak Invoice
                </button>
            </div>
        </div>
    </div>
</div>

<?php require __DIR__ . '/includes/footer.php'; ?>
