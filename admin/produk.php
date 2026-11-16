<?php
/**
 * Panel Admin - Daftar & Pencarian Data Produk
 */
$judul_halaman = 'Data Produk';
$menu_aktif    = 'produk';
require __DIR__ . '/includes/header.php';

$cari        = input('cari');
$id_kategori = input_int('kategori');

$where  = ['1 = 1'];
$params = [];

if ($cari !== '') {
    $where[]  = '(p.nama_produk LIKE ? OR p.deskripsi LIKE ?)';
    $params[] = '%' . $cari . '%';
    $params[] = '%' . $cari . '%';
}
if ($id_kategori > 0) {
    $where[]  = 'p.id_kategori = ?';
    $params[] = $id_kategori;
}
$klausa = 'WHERE ' . implode(' AND ', $where);

$per_halaman = 10;
$halaman     = max(1, input_int('halaman', 1));
$total       = (int) db_value("SELECT COUNT(*) FROM produk p $klausa", $params);
$total_hal   = max(1, (int) ceil($total / $per_halaman));
$halaman     = min($halaman, $total_hal);
$offset      = ($halaman - 1) * $per_halaman;

$produk = db_all(
    "SELECT p.*, k.nama_kategori
       FROM produk p JOIN kategori k ON k.id_kategori = p.id_kategori
       $klausa
      ORDER BY p.id_produk DESC
      LIMIT $per_halaman OFFSET $offset",
    $params
);

$daftar_kategori = db_all('SELECT id_kategori, nama_kategori FROM kategori ORDER BY nama_kategori');
?>

<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
    <div>
        <h5 class="fw-bold mb-0">Data Produk</h5>
        <small class="text-muted"><?= number_format($total) ?> produk terdaftar</small>
    </div>
    <a href="<?= BASE_URL ?>/admin/produk_form.php" class="btn btn-primary">
        <i class="bi bi-plus-lg me-1"></i>Tambah Produk
    </a>
</div>

<div class="card border-0 shadow-sm mb-3">
    <div class="card-body">
        <form method="get" class="row g-2 align-items-end">
            <div class="col-md-5">
                <label class="form-label small mb-1">Cari produk</label>
                <input type="text" name="cari" class="form-control" placeholder="Nama atau deskripsi produk"
                       value="<?= e($cari) ?>">
            </div>
            <div class="col-md-4">
                <label class="form-label small mb-1">Kategori</label>
                <select name="kategori" class="form-select">
                    <option value="0">Semua Kategori</option>
                    <?php foreach ($daftar_kategori as $k): ?>
                    <option value="<?= (int)$k['id_kategori'] ?>"
                        <?= $id_kategori === (int)$k['id_kategori'] ? 'selected' : '' ?>>
                        <?= e($k['nama_kategori']) ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3 d-flex gap-2">
                <button class="btn btn-primary flex-grow-1"><i class="bi bi-search me-1"></i>Cari</button>
                <a href="<?= BASE_URL ?>/admin/produk.php" class="btn btn-outline-secondary">Reset</a>
            </div>
        </form>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="table-responsive">
        <table class="table table-rapat align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th style="width:60px">#</th>
                    <th style="width:80px">Gambar</th>
                    <th>Nama Produk</th>
                    <th>Kategori</th>
                    <th class="text-end">Harga</th>
                    <th class="text-center">Stok</th>
                    <th class="text-center">Status</th>
                    <th class="text-end" style="width:150px">Aksi</th>
                </tr>
            </thead>
            <tbody>
            <?php if (!$produk): ?>
                <tr><td colspan="8" class="text-center text-muted py-4">Tidak ada produk yang cocok.</td></tr>
            <?php else: $no = $offset + 1; foreach ($produk as $p): ?>
                <tr>
                    <td><?= $no++ ?></td>
                    <td><img src="<?= gambar_produk($p['gambar']) ?>" alt=""
                             style="width:56px;height:56px;object-fit:cover;border-radius:.5rem;background:#f1f3f5"></td>
                    <td>
                        <div class="fw-semibold"><?= e($p['nama_produk']) ?></div>
                        <div class="small text-muted text-truncate" style="max-width:320px">
                            <?= e(mb_strimwidth((string)$p['deskripsi'], 0, 80, '...')) ?>
                        </div>
                    </td>
                    <td><span class="badge text-bg-light text-primary"><?= e($p['nama_kategori']) ?></span></td>
                    <td class="text-end text-nowrap"><?= rupiah($p['harga']) ?></td>
                    <td class="text-center">
                        <span class="badge text-bg-<?= (int)$p['stok'] <= 5 ? 'danger' : ((int)$p['stok'] <= 15 ? 'warning' : 'success') ?>">
                            <?= (int)$p['stok'] ?>
                        </span>
                    </td>
                    <td class="text-center">
                        <span class="badge text-bg-<?= $p['status'] === 'aktif' ? 'success' : 'secondary' ?>">
                            <?= e(ucfirst($p['status'])) ?>
                        </span>
                    </td>
                    <td class="text-end">
                        <a href="<?= BASE_URL ?>/admin/produk_form.php?id=<?= (int)$p['id_produk'] ?>"
                           class="btn btn-sm btn-outline-primary" title="Edit">
                            <i class="bi bi-pencil-square"></i>
                        </a>
                        <form action="<?= BASE_URL ?>/admin/produk_hapus.php" method="post" class="d-inline"
                              onsubmit="return confirm('Yakin ingin menghapus produk ini? Data tidak dapat dikembalikan.')">
                            <input type="hidden" name="id_produk" value="<?= (int)$p['id_produk'] ?>">
                            <button class="btn btn-sm btn-outline-danger" title="Hapus">
                                <i class="bi bi-trash"></i>
                            </button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; endif; ?>
            </tbody>
        </table>
    </div>

    <?php if ($total_hal > 1): ?>
    <div class="card-footer bg-white">
        <nav>
            <ul class="pagination pagination-sm justify-content-center mb-0">
                <?php for ($i = 1; $i <= $total_hal; $i++):
                    $link = BASE_URL . '/admin/produk.php?' .
                            http_build_query(array_filter(['cari' => $cari, 'kategori' => $id_kategori, 'halaman' => $i],
                                fn($v) => $v !== '' && $v !== 0));
                ?>
                <li class="page-item <?= $i === $halaman ? 'active' : '' ?>">
                    <a class="page-link" href="<?= e($link) ?>"><?= $i ?></a>
                </li>
                <?php endfor; ?>
            </ul>
        </nav>
    </div>
    <?php endif; ?>
</div>

<?php require __DIR__ . '/includes/footer.php'; ?>
