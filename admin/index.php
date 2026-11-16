<?php
/**
 * Dashboard Panel Admin
 */
$judul_halaman = 'Dashboard';
$menu_aktif    = 'dashboard';
require __DIR__ . '/includes/header.php';

$total_produk   = (int) db_value('SELECT COUNT(*) FROM produk');
$total_kategori = (int) db_value('SELECT COUNT(*) FROM kategori');
$total_pesanan  = (int) db_value('SELECT COUNT(*) FROM pesanan');
$total_pembeli  = (int) db_value('SELECT COUNT(DISTINCT id_user) FROM pesanan');
$pendapatan     = (float) db_value('SELECT COALESCE(SUM(total_bayar),0) FROM pesanan WHERE status <> "dibatalkan"');
$pesanan_baru   = (int) db_value('SELECT COUNT(*) FROM pesanan WHERE status = "menunggu"');

$statistik = [
    ['Total Produk',    $total_produk,   'box-seam',   'primary', 'admin/produk.php'],
    ['Kategori',        $total_kategori, 'tags',       'success', 'admin/kategori.php'],
    ['Total Pesanan',   $total_pesanan,  'receipt',    'info',    'admin/pesanan.php'],
    ['Total Pembeli',   $total_pembeli,  'people',     'warning', 'admin/pesanan.php'],
];

$pesanan_terbaru = db_all(
    'SELECT p.*, u.nama AS nama_pembeli
       FROM pesanan p JOIN users u ON u.id_user = p.id_user
      ORDER BY p.created_at DESC LIMIT 5'
);

$stok_rendah = db_all(
    'SELECT p.id_produk, p.nama_produk, p.stok, k.nama_kategori
       FROM produk p JOIN kategori k ON k.id_kategori = p.id_kategori
      WHERE p.stok <= 15
      ORDER BY p.stok ASC LIMIT 5'
);
?>

<?php if ($pesanan_baru > 0): ?>
<div class="alert alert-info d-flex align-items-center gap-2">
    <i class="bi bi-bell-fill fs-5"></i>
    <div>Ada <strong><?= $pesanan_baru ?></strong> pesanan baru yang menunggu diproses.
        <a href="<?= BASE_URL ?>/admin/pesanan.php?status=menunggu" class="alert-link">Lihat sekarang</a>.
    </div>
</div>
<?php endif; ?>

<div class="row row-cols-1 row-cols-sm-2 row-cols-xl-4 g-3 mb-4">
    <?php foreach ($statistik as [$label, $nilai, $ikon, $warna, $url]): ?>
    <div class="col">
        <a href="<?= BASE_URL ?>/<?= $url ?>" class="text-decoration-none">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="stat-icon text-bg-<?= $warna ?>"><i class="bi bi-<?= $ikon ?>"></i></div>
                    <div>
                        <div class="fs-4 fw-bold text-dark"><?= number_format($nilai, 0, ',', '.') ?></div>
                        <div class="small text-muted"><?= e($label) ?></div>
                    </div>
                </div>
            </div>
        </a>
    </div>
    <?php endforeach; ?>
</div>

<div class="card border-0 shadow-sm mb-4 bg-luxe-gradient">
    <div class="card-body text-white d-flex flex-wrap justify-content-between align-items-center gap-3">
        <div>
            <div class="small text-white-50">Total Pendapatan (pesanan tidak dibatalkan)</div>
            <div class="display-6 fw-bold"><?= rupiah($pendapatan) ?></div>
        </div>
        <i class="bi bi-cash-coin opacity-50" style="font-size:3rem"></i>
    </div>
</div>

<div class="row g-4">
    <div class="col-lg-7">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <span class="fw-semibold"><i class="bi bi-clock-history me-1 text-primary"></i>Pesanan Terbaru</span>
                <a href="<?= BASE_URL ?>/admin/pesanan.php" class="btn btn-sm btn-outline-primary">Semua</a>
            </div>
            <div class="table-responsive">
                <table class="table table-rapat align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Kode</th><th>Pembeli</th><th class="text-end">Total</th><th class="text-center">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php if (!$pesanan_terbaru): ?>
                        <tr><td colspan="4" class="text-center text-muted py-4">Belum ada pesanan.</td></tr>
                    <?php else: foreach ($pesanan_terbaru as $p): ?>
                        <tr>
                            <td>
                                <a href="<?= BASE_URL ?>/admin/pesanan_detail.php?id=<?= (int)$p['id_pesanan'] ?>"
                                   class="text-decoration-none fw-semibold"><?= e($p['kode_pesanan']) ?></a>
                                <div class="small text-muted"><?= date('d/m/Y H:i', strtotime($p['created_at'])) ?></div>
                            </td>
                            <td class="small"><?= e($p['nama_pembeli']) ?></td>
                            <td class="text-end text-nowrap fw-semibold"><?= rupiah($p['total_bayar']) ?></td>
                            <td class="text-center"><?= label_status($p['status']) ?></td>
                        </tr>
                    <?php endforeach; endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="col-lg-5">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white fw-semibold">
                <i class="bi bi-exclamation-triangle me-1 text-warning"></i>Stok Menipis (&le; 15)
            </div>
            <ul class="list-group list-group-flush">
                <?php if (!$stok_rendah): ?>
                <li class="list-group-item text-center text-muted py-4">Semua stok produk aman.</li>
                <?php else: foreach ($stok_rendah as $s): ?>
                <li class="list-group-item d-flex justify-content-between align-items-center">
                    <div>
                        <div class="fw-semibold small"><?= e($s['nama_produk']) ?></div>
                        <div class="small text-muted"><?= e($s['nama_kategori']) ?></div>
                    </div>
                    <span class="badge text-bg-<?= (int)$s['stok'] <= 5 ? 'danger' : 'warning' ?>">
                        <?= (int)$s['stok'] ?> unit
                    </span>
                </li>
                <?php endforeach; endif; ?>
            </ul>
        </div>
    </div>
</div>

<?php require __DIR__ . '/includes/footer.php'; ?>
