<?php
/**
 * Halaman Detail Produk
 */
require_once __DIR__ . '/config/koneksi.php';
require_once __DIR__ . '/includes/fungsi.php';

$id_produk = input_int('id');
$produk = db_row(
    'SELECT p.*, k.nama_kategori, u.nama AS nama_penjual
       FROM produk p
       JOIN kategori k ON k.id_kategori = p.id_kategori
       JOIN users u ON u.id_user = p.id_user
      WHERE p.id_produk = ?',
    [$id_produk]
);

if (!$produk) {
    set_flash('danger', 'Produk yang Anda cari tidak ditemukan.');
    redirect('index.php');
}

$judul_halaman = $produk['nama_produk'];

// Produk terkait (kategori sama, selain produk ini)
$terkait = db_all(
    'SELECT p.*, k.nama_kategori
       FROM produk p
       JOIN kategori k ON k.id_kategori = p.id_kategori
      WHERE p.id_kategori = ? AND p.id_produk <> ? AND p.status = "aktif"
      ORDER BY RAND() LIMIT 4',
    [(int)$produk['id_kategori'], $id_produk]
);

require_once __DIR__ . '/includes/header.php';
?>

<nav aria-label="breadcrumb">
    <ol class="breadcrumb small">
        <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/index.php" class="text-decoration-none">Beranda</a></li>
        <li class="breadcrumb-item">
            <a href="<?= BASE_URL ?>/index.php?kategori=<?= (int)$produk['id_kategori'] ?>"
               class="text-decoration-none"><?= e($produk['nama_kategori']) ?></a>
        </li>
        <li class="breadcrumb-item active" aria-current="page"><?= e($produk['nama_produk']) ?></li>
    </ol>
</nav>

<div class="card border-0 shadow-sm mb-4">
    <div class="card-body p-4">
        <div class="row g-4">
            <div class="col-lg-5">
                <img src="<?= gambar_produk($produk['gambar']) ?>" class="gambar-detail"
                     alt="<?= e($produk['nama_produk']) ?>">
            </div>
            <div class="col-lg-7">
                <span class="badge text-bg-light text-primary mb-2"><?= e($produk['nama_kategori']) ?></span>
                <h3 class="fw-bold mb-2"><?= e($produk['nama_produk']) ?></h3>

                <div class="d-flex align-items-center gap-3 mb-3 text-muted small">
                    <span><i class="bi bi-shop me-1"></i><?= e($produk['nama_penjual']) ?></span>
                    <span><i class="bi bi-star-fill text-warning me-1"></i>4.8</span>
                    <span><i class="bi bi-box me-1"></i>Stok <?= (int)$produk['stok'] ?></span>
                </div>

                <div class="harga-detail mb-3"><?= rupiah($produk['harga']) ?></div>

                <p class="text-muted"><?= nl2br(e($produk['deskripsi'])) ?></p>

                <hr>

                <?php if ($produk['stok'] > 0): ?>
                <form action="<?= BASE_URL ?>/keranjang_aksi.php" method="post" class="row g-2 align-items-end">
                    <input type="hidden" name="aksi" value="tambah">
                    <input type="hidden" name="id_produk" value="<?= (int)$produk['id_produk'] ?>">
                    <div class="col-auto">
                        <label class="form-label small mb-1">Jumlah</label>
                        <input type="number" name="jumlah" class="form-control" value="1"
                               min="1" max="<?= (int)$produk['stok'] ?>" style="width:110px">
                    </div>
                    <div class="col-auto">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-cart-plus me-1"></i>Tambah ke Keranjang
                        </button>
                    </div>
                    <div class="col-auto">
                        <a href="<?= BASE_URL ?>/keranjang.php" class="btn btn-outline-secondary">
                            <i class="bi bi-cart3 me-1"></i>Lihat Keranjang
                        </a>
                    </div>
                </form>
                <?php else: ?>
                <div class="alert alert-warning mb-0">
                    <i class="bi bi-exclamation-triangle me-1"></i>Stok produk ini sedang habis.
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php if ($terkait): ?>
<h5 class="fw-bold mb-3"><i class="bi bi-collection me-1 text-primary"></i>Produk Terkait</h5>
<div class="row row-cols-1 row-cols-sm-2 row-cols-lg-4 g-3">
    <?php foreach ($terkait as $t): ?>
    <div class="col">
        <div class="card kartu-produk h-100 shadow-sm">
            <a href="<?= BASE_URL ?>/produk_detail.php?id=<?= (int)$t['id_produk'] ?>">
                <img src="<?= gambar_produk($t['gambar']) ?>" class="gambar-produk" alt="<?= e($t['nama_produk']) ?>">
            </a>
            <div class="card-body">
                <h6 class="nama-produk mb-2">
                    <a class="text-decoration-none text-dark"
                       href="<?= BASE_URL ?>/produk_detail.php?id=<?= (int)$t['id_produk'] ?>">
                        <?= e($t['nama_produk']) ?></a>
                </h6>
                <div class="harga-produk"><?= rupiah($t['harga']) ?></div>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
</div>
<?php endif; ?>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
