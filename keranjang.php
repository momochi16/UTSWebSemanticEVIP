<?php
/**
 * Halaman Keranjang Belanja
 */
require_once __DIR__ . '/config/koneksi.php';
require_once __DIR__ . '/includes/fungsi.php';

$judul_halaman = 'Keranjang Belanja';
require_login();

$id_user = (int) user_login()['id_user'];

$item = db_all(
    'SELECT kr.id_keranjang, kr.jumlah, p.id_produk, p.nama_produk, p.harga, p.stok,
            p.gambar, k.nama_kategori
       FROM keranjang kr
       JOIN produk p ON p.id_produk = kr.id_produk
       JOIN kategori k ON k.id_kategori = p.id_kategori
      WHERE kr.id_user = ?
      ORDER BY kr.created_at DESC',
    [$id_user]
);

$subtotal = 0.0;
$jml_item = 0;
foreach ($item as $i) {
    $subtotal += (float)$i['harga'] * (int)$i['jumlah'];
    $jml_item += (int)$i['jumlah'];
}

// Biaya pengiriman sederhana: gratis jika subtotal >= Rp 500.000
$ongkir = ($subtotal > 0 && $subtotal < 500000) ? 20000.0 : 0.0;
$total  = $subtotal + $ongkir;

require_once __DIR__ . '/includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="fw-bold mb-0"><i class="bi bi-cart3 me-2 text-primary"></i>Keranjang Belanja</h4>
    <span class="text-muted small"><?= (int)$jml_item ?> item</span>
</div>

<?php if (!$item): ?>
<div class="card border-0 shadow-sm">
    <div class="card-body text-center py-5">
        <i class="bi bi-cart-x fs-1 text-muted"></i>
        <h5 class="mt-3">Keranjang Anda masih kosong</h5>
        <p class="text-muted">Yuk pilih produk favorit Anda terlebih dahulu.</p>
        <a href="<?= BASE_URL ?>/index.php" class="btn btn-primary">
            <i class="bi bi-shop me-1"></i>Mulai Belanja
        </a>
    </div>
</div>
<?php else: ?>

<div class="row g-4">
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm">
            <div class="table-responsive">
                <table class="table table-rapat align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th style="width:45%">Produk</th>
                            <th class="text-end">Harga</th>
                            <th class="text-center" style="width:150px">Jumlah</th>
                            <th class="text-end">Subtotal</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($item as $i): ?>
                        <tr>
                            <td>
                                <div class="d-flex align-items-center gap-3">
                                    <img src="<?= gambar_produk($i['gambar']) ?>" class="thumb-keranjang"
                                         alt="<?= e($i['nama_produk']) ?>">
                                    <div>
                                        <a class="fw-semibold text-decoration-none text-dark"
                                           href="<?= BASE_URL ?>/produk_detail.php?id=<?= (int)$i['id_produk'] ?>">
                                            <?= e($i['nama_produk']) ?></a>
                                        <div class="small text-muted"><?= e($i['nama_kategori']) ?></div>
                                        <div class="small text-muted">Stok tersedia: <?= (int)$i['stok'] ?></div>
                                    </div>
                                </div>
                            </td>
                            <td class="text-end text-nowrap"><?= rupiah($i['harga']) ?></td>
                            <td>
                                <form action="<?= BASE_URL ?>/keranjang_aksi.php" method="post"
                                      class="d-flex gap-1 justify-content-center">
                                    <input type="hidden" name="aksi" value="ubah">
                                    <input type="hidden" name="id_keranjang" value="<?= (int)$i['id_keranjang'] ?>">
                                    <input type="number" name="jumlah" class="form-control form-control-sm text-center"
                                           value="<?= (int)$i['jumlah'] ?>" min="1"
                                           max="<?= max(1, (int)$i['stok']) ?>" style="width:70px">
                                    <button class="btn btn-sm btn-outline-primary" title="Perbarui">
                                        <i class="bi bi-arrow-repeat"></i>
                                    </button>
                                </form>
                            </td>
                            <td class="text-end fw-semibold text-nowrap">
                                <?= rupiah((float)$i['harga'] * (int)$i['jumlah']) ?>
                            </td>
                            <td class="text-end">
                                <form action="<?= BASE_URL ?>/keranjang_aksi.php" method="post"
                                      onsubmit="return confirm('Hapus produk ini dari keranjang?')">
                                    <input type="hidden" name="aksi" value="hapus">
                                    <input type="hidden" name="id_keranjang" value="<?= (int)$i['id_keranjang'] ?>">
                                    <button class="btn btn-sm btn-outline-danger" title="Hapus">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <div class="card-footer bg-white d-flex justify-content-between">
                <a href="<?= BASE_URL ?>/index.php" class="btn btn-outline-secondary btn-sm">
                    <i class="bi bi-arrow-left me-1"></i>Lanjut Belanja
                </a>
                <form action="<?= BASE_URL ?>/keranjang_aksi.php" method="post"
                      onsubmit="return confirm('Kosongkan seluruh keranjang?')">
                    <input type="hidden" name="aksi" value="kosongkan">
                    <button class="btn btn-outline-danger btn-sm">
                        <i class="bi bi-trash3 me-1"></i>Kosongkan Keranjang
                    </button>
                </form>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white fw-semibold">Ringkasan Belanja</div>
            <div class="card-body">
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted">Subtotal (<?= (int)$jml_item ?> item)</span>
                    <span class="fw-semibold"><?= rupiah($subtotal) ?></span>
                </div>
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted">Ongkos Kirim</span>
                    <span class="fw-semibold"><?= $ongkir > 0 ? rupiah($ongkir) : 'GRATIS' ?></span>
                </div>
                <?php if ($ongkir > 0): ?>
                <div class="alert alert-info py-2 small">
                    <i class="bi bi-truck me-1"></i>Tambah <?= rupiah(500000 - $subtotal) ?> lagi untuk gratis ongkir.
                </div>
                <?php endif; ?>
                <hr>
                <div class="d-flex justify-content-between mb-3">
                    <span class="fw-semibold">Total Bayar</span>
                    <span class="fs-5 fw-bold text-primary"><?= rupiah($total) ?></span>
                </div>
                <a href="<?= BASE_URL ?>/checkout.php" class="btn btn-primary w-100">
                    <i class="bi bi-credit-card me-1"></i>Lanjut ke Checkout
                </a>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
