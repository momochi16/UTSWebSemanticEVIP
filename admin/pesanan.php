<?php
/**
 * Panel Admin - Daftar Pesanan Masuk
 */
$judul_halaman = 'Pesanan Masuk';
$menu_aktif    = 'pesanan';
require __DIR__ . '/includes/header.php';

$status = input('status');
$cari   = input('cari');

$where  = ['1 = 1'];
$params = [];

if (in_array($status, ['menunggu', 'diproses', 'dikirim', 'selesai', 'dibatalkan'], true)) {
    $where[]  = 'p.status = ?';
    $params[] = $status;
}
if ($cari !== '') {
    $where[]  = '(p.kode_pesanan LIKE ? OR p.nama_penerima LIKE ? OR u.nama LIKE ?)';
    $params[] = '%' . $cari . '%';
    $params[] = '%' . $cari . '%';
    $params[] = '%' . $cari . '%';
}
$klausa = 'WHERE ' . implode(' AND ', $where);

$per_halaman = 10;
$halaman     = max(1, input_int('halaman', 1));
$total       = (int) db_value("SELECT COUNT(*) FROM pesanan p JOIN users u ON u.id_user = p.id_user $klausa", $params);
$total_hal   = max(1, (int) ceil($total / $per_halaman));
$halaman     = min($halaman, $total_hal);
$offset      = ($halaman - 1) * $per_halaman;

$pesanan = db_all(
    "SELECT p.*, u.nama AS nama_pembeli,
            (SELECT COALESCE(SUM(jumlah),0) FROM detail_pesanan d WHERE d.id_pesanan = p.id_pesanan) AS total_item
       FROM pesanan p JOIN users u ON u.id_user = p.id_user
       $klausa
      ORDER BY p.created_at DESC
      LIMIT $per_halaman OFFSET $offset",
    $params
);

$tab = [
    ''           => 'Semua',
    'menunggu'   => 'Menunggu',
    'diproses'   => 'Diproses',
    'dikirim'    => 'Dikirim',
    'selesai'    => 'Selesai',
    'dibatalkan' => 'Dibatalkan',
];
?>

<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
    <div>
        <h5 class="fw-bold mb-0">Pesanan Masuk</h5>
        <small class="text-muted"><?= number_format($total) ?> pesanan ditemukan</small>
    </div>
</div>

<ul class="nav nav-pills mb-3 flex-nowrap overflow-auto">
    <?php foreach ($tab as $nilai => $label):
        $qs = array_filter(['status' => $nilai, 'cari' => $cari], fn($v) => $v !== '');
    ?>
    <li class="nav-item">
        <a class="nav-link text-nowrap <?= $status === $nilai ? 'active' : '' ?>"
           href="<?= BASE_URL ?>/admin/pesanan.php<?= $qs ? '?' . http_build_query($qs) : '' ?>">
            <?= e($label) ?>
        </a>
    </li>
    <?php endforeach; ?>
</ul>

<div class="card border-0 shadow-sm mb-3">
    <div class="card-body">
        <form method="get" class="row g-2 align-items-end">
            <?php if ($status !== ''): ?><input type="hidden" name="status" value="<?= e($status) ?>"><?php endif; ?>
            <div class="col-md-9">
                <label class="form-label small mb-1">Cari pesanan</label>
                <input type="text" name="cari" class="form-control"
                       placeholder="Kode pesanan / nama penerima / nama pembeli" value="<?= e($cari) ?>">
            </div>
            <div class="col-md-3 d-flex gap-2">
                <button class="btn btn-primary flex-grow-1"><i class="bi bi-search me-1"></i>Cari</button>
                <a href="<?= BASE_URL ?>/admin/pesanan.php" class="btn btn-outline-secondary">Reset</a>
            </div>
        </form>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="table-responsive">
        <table class="table table-rapat align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>Kode Pesanan</th>
                    <th>Pembeli</th>
                    <th>Tanggal</th>
                    <th class="text-center">Item</th>
                    <th class="text-end">Total</th>
                    <th class="text-center">Status</th>
                    <th class="text-end" style="width:110px">Aksi</th>
                </tr>
            </thead>
            <tbody>
            <?php if (!$pesanan): ?>
                <tr><td colspan="7" class="text-center text-muted py-4">Belum ada pesanan pada filter ini.</td></tr>
            <?php else: foreach ($pesanan as $p): ?>
                <tr>
                    <td class="fw-semibold"><?= e($p['kode_pesanan']) ?></td>
                    <td>
                        <div class="small"><?= e($p['nama_pembeli']) ?></div>
                        <div class="small text-muted"><?= e($p['nama_penerima']) ?> &middot; <?= e($p['no_hp']) ?></div>
                    </td>
                    <td class="text-nowrap small"><?= date('d/m/Y H:i', strtotime($p['created_at'])) ?></td>
                    <td class="text-center"><?= (int)$p['total_item'] ?></td>
                    <td class="text-end fw-semibold text-nowrap"><?= rupiah($p['total_bayar']) ?></td>
                    <td class="text-center"><?= label_status($p['status']) ?></td>
                    <td class="text-end">
                        <a href="<?= BASE_URL ?>/admin/pesanan_detail.php?id=<?= (int)$p['id_pesanan'] ?>"
                           class="btn btn-sm btn-outline-primary"><i class="bi bi-eye"></i></a>
                    </td>
                </tr>
            <?php endforeach; endif; ?>
            </tbody>
        </table>
    </div>

    <?php if ($total_hal > 1): ?>
    <div class="card-footer bg-white">
        <ul class="pagination pagination-sm justify-content-center mb-0">
            <?php for ($i = 1; $i <= $total_hal; $i++):
                $link = BASE_URL . '/admin/pesanan.php?' . http_build_query(
                    array_filter(['status' => $status, 'cari' => $cari, 'halaman' => $i], fn($v) => $v !== ''));
            ?>
            <li class="page-item <?= $i === $halaman ? 'active' : '' ?>">
                <a class="page-link" href="<?= e($link) ?>"><?= $i ?></a>
            </li>
            <?php endfor; ?>
        </ul>
    </div>
    <?php endif; ?>
</div>

<?php require __DIR__ . '/includes/footer.php'; ?>
