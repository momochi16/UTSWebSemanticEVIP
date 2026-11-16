<?php
/**
 * Halaman Riwayat Pesanan Pembeli
 */
require_once __DIR__ . '/config/koneksi.php';
require_once __DIR__ . '/includes/fungsi.php';

require_login();
$judul_halaman = 'Pesanan Saya';

$id_user = (int) user_login()['id_user'];

$pesanan = db_all(
    'SELECT p.*, (SELECT COALESCE(SUM(jumlah),0) FROM detail_pesanan d
                   WHERE d.id_pesanan = p.id_pesanan) AS total_item
       FROM pesanan p
      WHERE p.id_user = ?
      ORDER BY p.created_at DESC',
    [$id_user]
);

require_once __DIR__ . '/includes/header.php';
?>

<h4 class="fw-bold mb-3"><i class="bi bi-receipt me-2 text-primary"></i>Pesanan Saya</h4>

<?php if (!$pesanan): ?>
<div class="card border-0 shadow-sm">
    <div class="card-body text-center py-5">
        <i class="bi bi-inbox fs-1 text-muted"></i>
        <h5 class="mt-3">Belum ada pesanan</h5>
        <p class="text-muted mb-3">Anda belum pernah membuat pesanan.</p>
        <a href="<?= BASE_URL ?>/index.php" class="btn btn-primary btn-sm">Mulai Belanja</a>
    </div>
</div>
<?php else: ?>
<div class="card border-0 shadow-sm">
    <div class="table-responsive">
        <table class="table table-rapat align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>Kode Pesanan</th>
                    <th>Tanggal</th>
                    <th class="text-center">Jumlah Item</th>
                    <th class="text-end">Total Bayar</th>
                    <th class="text-center">Status</th>
                    <th class="text-end">Aksi</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($pesanan as $p): ?>
                <tr>
                    <td class="fw-semibold"><?= e($p['kode_pesanan']) ?></td>
                    <td class="text-nowrap small"><?= date('d/m/Y H:i', strtotime($p['created_at'])) ?></td>
                    <td class="text-center"><?= (int)$p['total_item'] ?></td>
                    <td class="text-end fw-semibold text-nowrap"><?= rupiah($p['total_bayar']) ?></td>
                    <td class="text-center"><?= label_status($p['status']) ?></td>
                    <td class="text-end">
                        <a href="<?= BASE_URL ?>/pesanan_detail.php?id=<?= (int)$p['id_pesanan'] ?>"
                           class="btn btn-sm btn-outline-primary">
                            <i class="bi bi-eye me-1"></i>Detail
                        </a>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php endif; ?>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
