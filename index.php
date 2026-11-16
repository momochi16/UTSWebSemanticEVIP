<?php
/**
 * Halaman Beranda - Daftar Produk
 */
require_once __DIR__ . '/config/koneksi.php';
require_once __DIR__ . '/includes/fungsi.php';

$judul_halaman = 'Beranda';

// ---------------- Pencarian, filter, dan pengurutan ----------------
$cari        = input('cari');
$id_kategori = input_int('kategori');
$urut        = input('urut', 'terbaru');

$where  = ["p.status = 'aktif'"];
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

$orderBy = [
    'terbaru'  => 'p.created_at DESC, p.id_produk DESC',
    'termurah' => 'p.harga ASC',
    'termahal' => 'p.harga DESC',
    'nama'     => 'p.nama_produk ASC',
][$urut] ?? 'p.created_at DESC';

// ---------------- Pagination ----------------
$per_halaman = 9;
$halaman     = max(1, input_int('halaman', 1));
$total       = (int) db_value("SELECT COUNT(*) FROM produk p $klausa", $params);
$total_hal   = max(1, (int) ceil($total / $per_halaman));
$halaman     = min($halaman, $total_hal);
$offset      = ($halaman - 1) * $per_halaman;

$produk = db_all(
    "SELECT p.*, k.nama_kategori
       FROM produk p
       JOIN kategori k ON k.id_kategori = p.id_kategori
       $klausa
      ORDER BY $orderBy
      LIMIT $per_halaman OFFSET $offset",
    $params
);

$daftar_kategori = db_all(
    'SELECT k.id_kategori, k.nama_kategori, COUNT(p.id_produk) AS jml
       FROM kategori k
       LEFT JOIN produk p ON p.id_kategori = k.id_kategori AND p.status = "aktif"
      GROUP BY k.id_kategori, k.nama_kategori
      ORDER BY k.nama_kategori'
);

$kategori_aktif = $id_kategori > 0
    ? db_row('SELECT nama_kategori FROM kategori WHERE id_kategori = ?', [$id_kategori])
    : null;

require_once __DIR__ . '/includes/header.php';
?>

<?php if ($cari === '' && $id_kategori === 0 && $halaman === 1): ?>
<section class="hero p-4 p-md-5 mb-4 shadow-sm">
    <div class="row align-items-center g-4">
        <div class="col-lg-7">
            <span class="hero-eyebrow mb-3">Luxury Heels Boutique</span>
            <h1 class="display-5 fw-bold mb-3">Kemewahan di setiap langkah</h1>
            <p class="hero-lead mb-4 opacity-75">
                Setiap langkah adalah pernyataan. Jelajahi koleksi heels pilihan dari maison
                ternama, dari stiletto yang memikat hingga slingback yang abadi, dan temukan
                pasangan yang melengkapi gaya Anda.
            </p>
            <a href="#daftar-produk" class="btn btn-gold fw-semibold">
                <i class="bi bi-arrow-down-circle me-1"></i>Mulai Belanja
            </a>
        </div>
        <div class="col-lg-5 text-center">
            <img src="<?= BASE_URL ?>/assets/img/heels.jpg"
                 class="hero-gambar" alt="Koleksi high heels mewah <?= e(APP_NAME) ?>">
        </div>
    </div>
</section>
<?php endif; ?>

<!-- Kategori (filter horizontal) -->
<div class="mb-4" id="kategori">
    <div class="d-flex align-items-center flex-wrap gap-2">
        <span class="text-muted small me-1"><i class="bi bi-funnel me-1 text-primary"></i>Kategori</span>
        <a href="<?= BASE_URL ?>/index.php"
           class="chip-kategori <?= $id_kategori === 0 ? 'active' : '' ?>">
            Semua Kategori
            <span class="chip-jumlah"><?= number_format($total) ?></span>
        </a>
        <?php foreach ($daftar_kategori as $k): ?>
        <a href="<?= BASE_URL ?>/index.php?kategori=<?= (int)$k['id_kategori'] ?>"
           class="chip-kategori <?= $id_kategori === (int)$k['id_kategori'] ? 'active' : '' ?>">
            <?= e($k['nama_kategori']) ?>
            <span class="chip-jumlah"><?= (int)$k['jml'] ?></span>
        </a>
        <?php endforeach; ?>
    </div>
</div>

<div class="row g-4" id="daftar-produk">
    <!-- Grid produk -->
    <div class="col-12">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
            <div>
                <h5 class="mb-0 fw-bold">
                    <?= $kategori_aktif ? e($kategori_aktif['nama_kategori']) : 'Semua Produk' ?>
                </h5>
                <small class="text-muted">
                    <?php if ($cari !== ''): ?>
                        Hasil pencarian "<strong><?= e($cari) ?></strong>" &middot;
                    <?php endif; ?>
                    <?= number_format($total) ?> produk ditemukan
                </small>
            </div>
            <form class="d-flex align-items-center gap-2" method="get" action="<?= BASE_URL ?>/index.php">
                <?php if ($cari !== ''): ?><input type="hidden" name="cari" value="<?= e($cari) ?>"><?php endif; ?>
                <?php if ($id_kategori > 0): ?><input type="hidden" name="kategori" value="<?= (int)$id_kategori ?>"><?php endif; ?>
                <label class="text-muted small text-nowrap">Urutkan:</label>
                <select name="urut" class="form-select form-select-sm" onchange="this.form.submit()">
                    <option value="terbaru"  <?= $urut === 'terbaru'  ? 'selected' : '' ?>>Terbaru</option>
                    <option value="termurah" <?= $urut === 'termurah' ? 'selected' : '' ?>>Harga Terendah</option>
                    <option value="termahal" <?= $urut === 'termahal' ? 'selected' : '' ?>>Harga Tertinggi</option>
                    <option value="nama"     <?= $urut === 'nama'     ? 'selected' : '' ?>>Nama A-Z</option>
                </select>
            </form>
        </div>

        <?php if (!$produk): ?>
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center py-5">
                <i class="bi bi-search fs-1 text-muted"></i>
                <h5 class="mt-3">Produk tidak ditemukan</h5>
                <p class="text-muted mb-3">Coba ubah kata kunci atau pilih kategori lain.</p>
                <a href="<?= BASE_URL ?>/index.php" class="btn btn-primary btn-sm">Lihat semua produk</a>
            </div>
        </div>
        <?php else: ?>
        <div class="row row-cols-1 row-cols-sm-2 row-cols-xl-3 g-3">
            <?php foreach ($produk as $p): ?>
            <div class="col">
                <div class="card kartu-produk h-100 shadow-sm">
                    <a href="<?= BASE_URL ?>/produk_detail.php?id=<?= (int)$p['id_produk'] ?>">
                        <img src="<?= gambar_produk($p['gambar']) ?>" class="gambar-produk"
                             alt="<?= e($p['nama_produk']) ?>">
                    </a>
                    <div class="card-body d-flex flex-column">
                        <span class="badge text-bg-light text-primary align-self-start mb-2">
                            <?= e($p['nama_kategori']) ?>
                        </span>
                        <h6 class="mb-2 nama-produk">
                            <a class="text-decoration-none text-dark"
                               href="<?= BASE_URL ?>/produk_detail.php?id=<?= (int)$p['id_produk'] ?>">
                                <?= e($p['nama_produk']) ?></a>
                        </h6>
                        <div class="harga-produk mb-2"><?= rupiah($p['harga']) ?></div>
                        <div class="small text-muted mb-3">
                            <i class="bi bi-box me-1"></i>Stok: <?= (int)$p['stok'] ?>
                        </div>
                        <div class="mt-auto d-grid gap-2">
                            <a href="<?= BASE_URL ?>/produk_detail.php?id=<?= (int)$p['id_produk'] ?>"
                               class="btn btn-outline-primary btn-sm">
                                <i class="bi bi-eye me-1"></i>Detail
                            </a>
                            <form action="<?= BASE_URL ?>/keranjang_aksi.php" method="post">
                                <input type="hidden" name="aksi" value="tambah">
                                <input type="hidden" name="id_produk" value="<?= (int)$p['id_produk'] ?>">
                                <input type="hidden" name="jumlah" value="1">
                                <button type="submit" class="btn btn-primary btn-sm w-100"
                                        <?= $p['stok'] < 1 ? 'disabled' : '' ?>>
                                    <i class="bi bi-cart-plus me-1"></i>
                                    <?= $p['stok'] < 1 ? 'Stok Habis' : 'Tambah ke Keranjang' ?>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>

        <?php if ($total_hal > 1): ?>
        <nav class="mt-4">
            <ul class="pagination justify-content-center">
                <?php
                $qs = ['cari' => $cari, 'kategori' => $id_kategori, 'urut' => $urut];
                $buat_link = function ($hal) use ($qs) {
                    $qs['halaman'] = $hal;
                    return BASE_URL . '/index.php?' . http_build_query(array_filter($qs, fn($v) => $v !== '' && $v !== 0));
                };
                ?>
                <li class="page-item <?= $halaman <= 1 ? 'disabled' : '' ?>">
                    <a class="page-link" href="<?= e($buat_link($halaman - 1)) ?>">&laquo;</a>
                </li>
                <?php for ($i = 1; $i <= $total_hal; $i++): ?>
                <li class="page-item <?= $i === $halaman ? 'active' : '' ?>">
                    <a class="page-link" href="<?= e($buat_link($i)) ?>"><?= $i ?></a>
                </li>
                <?php endfor; ?>
                <li class="page-item <?= $halaman >= $total_hal ? 'disabled' : '' ?>">
                    <a class="page-link" href="<?= e($buat_link($halaman + 1)) ?>">&raquo;</a>
                </li>
            </ul>
        </nav>
        <?php endif; ?>
        <?php endif; ?>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
