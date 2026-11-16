<?php
/**
 * Halaman Checkout - Membuat dan menyimpan transaksi pemesanan
 */
require_once __DIR__ . '/config/koneksi.php';
require_once __DIR__ . '/includes/fungsi.php';

require_login();
$judul_halaman = 'Checkout';

$id_user = (int) user_login()['id_user'];

// ---------------- Ambil keranjang ----------------
$item = db_all(
    'SELECT kr.id_keranjang, kr.jumlah, p.id_produk, p.nama_produk, p.harga, p.stok, p.gambar
       FROM keranjang kr
       JOIN produk p ON p.id_produk = kr.id_produk
      WHERE kr.id_user = ?',
    [$id_user]
);

if (!$item) {
    set_flash('warning', 'Keranjang belanja Anda kosong.');
    redirect('keranjang.php');
}

$subtotal = 0.0;
foreach ($item as $i) {
    $subtotal += (float)$i['harga'] * (int)$i['jumlah'];
}
$ongkir = ($subtotal > 0 && $subtotal < 500000) ? 20000.0 : 0.0;
$total  = $subtotal + $ongkir;

// ---------------- Data awal form ----------------
$profil = db_row('SELECT nama, no_hp, alamat FROM users WHERE id_user = ?', [$id_user]);
$form = [
    'nama_penerima'     => $profil['nama'] ?? user_login()['nama'],
    'no_hp'             => $profil['no_hp'] ?? '',
    'alamat_kirim'      => $profil['alamat'] ?? '',
    'catatan'           => '',
    'metode_pembayaran' => 'transfer_bank',
];
$error = '';

// ---------------- Proses simpan pesanan ----------------
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    foreach (array_keys($form) as $k) {
        $form[$k] = input($k, $form[$k]);
    }

    if ($form['nama_penerima'] === '' || $form['no_hp'] === '' || $form['alamat_kirim'] === '') {
        $error = 'Nama penerima, no. HP, dan alamat pengiriman wajib diisi.';
    } elseif (!in_array($form['metode_pembayaran'], ['transfer_bank', 'cod', 'e_wallet'], true)) {
        $error = 'Metode pembayaran tidak valid.';
    } else {
        // Validasi stok terakhir sebelum menyimpan
        foreach ($item as $i) {
            if ((int)$i['jumlah'] > (int)$i['stok']) {
                $error = 'Stok produk "' . $i['nama_produk'] . '" tidak mencukupi (tersisa '
                       . (int)$i['stok'] . '). Silakan perbarui keranjang Anda.';
                break;
            }
        }
    }

    if ($error === '') {
        mysqli_begin_transaction($koneksi);
        try {
            $kode_pesanan = buat_kode_pesanan();

            $id_pesanan = db_exec(
                'INSERT INTO pesanan
                    (kode_pesanan, id_user, nama_penerima, no_hp, alamat_kirim,
                     catatan, metode_pembayaran, total_bayar, status)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, "menunggu")',
                [$kode_pesanan, $id_user, $form['nama_penerima'], $form['no_hp'],
                 $form['alamat_kirim'], $form['catatan'], $form['metode_pembayaran'], $total]
            );

            foreach ($item as $i) {
                $jumlah   = (int)$i['jumlah'];
                $harga    = (float)$i['harga'];
                $sub_item = $harga * $jumlah;

                db_exec(
                    'INSERT INTO detail_pesanan
                        (id_pesanan, id_produk, nama_produk, harga_satuan, jumlah, subtotal)
                     VALUES (?, ?, ?, ?, ?, ?)',
                    [$id_pesanan, (int)$i['id_produk'], $i['nama_produk'], $harga, $jumlah, $sub_item]
                );

                // Kurangi stok produk
                db_exec('UPDATE produk SET stok = stok - ? WHERE id_produk = ?',
                    [$jumlah, (int)$i['id_produk']]);
            }

            // Kosongkan keranjang setelah pesanan tersimpan
            db_exec('DELETE FROM keranjang WHERE id_user = ?', [$id_user]);

            mysqli_commit($koneksi);

            $_SESSION['pesanan_baru'] = $kode_pesanan;
            set_flash('success', 'Pesanan berhasil dibuat dengan kode ' . $kode_pesanan . '.');
            redirect('pesanan_sukses.php?kode=' . urlencode($kode_pesanan));

        } catch (Throwable $ex) {
            mysqli_rollback($koneksi);
            $error = 'Gagal menyimpan pesanan: ' . $ex->getMessage();
        }
    }
}

require_once __DIR__ . '/includes/header.php';
?>

<h4 class="fw-bold mb-3"><i class="bi bi-credit-card me-2 text-primary"></i>Checkout Pesanan</h4>

<!-- Alur proses -->
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body d-flex flex-wrap gap-3 align-items-center">
        <span class="step-badge text-bg-success"><i class="bi bi-check"></i></span>
        <span class="fw-semibold small">Pilih Produk</span>
        <i class="bi bi-arrow-right text-muted"></i>
        <span class="step-badge text-bg-success"><i class="bi bi-check"></i></span>
        <span class="fw-semibold small">Keranjang</span>
        <i class="bi bi-arrow-right text-muted"></i>
        <span class="step-badge text-bg-primary">3</span>
        <span class="fw-semibold small">Data Pengiriman</span>
        <i class="bi bi-arrow-right text-muted"></i>
        <span class="step-badge text-bg-secondary">4</span>
        <span class="fw-semibold small text-muted">Pesanan Tersimpan</span>
    </div>
</div>

<?php if ($error): ?>
<div class="alert alert-danger"><i class="bi bi-exclamation-circle me-1"></i><?= e($error) ?></div>
<?php endif; ?>

<form method="post">
<div class="row g-4">
    <div class="col-lg-7">
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white fw-semibold">
                <i class="bi bi-geo-alt me-1 text-primary"></i>Data Pengiriman
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <label class="form-label">Nama Penerima <span class="text-danger">*</span></label>
                    <input type="text" name="nama_penerima" class="form-control"
                           value="<?= e($form['nama_penerima']) ?>" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">No. HP / WhatsApp <span class="text-danger">*</span></label>
                    <input type="text" name="no_hp" class="form-control"
                           value="<?= e($form['no_hp']) ?>" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Alamat Lengkap <span class="text-danger">*</span></label>
                    <textarea name="alamat_kirim" class="form-control" rows="3" required><?= e($form['alamat_kirim']) ?></textarea>
                </div>
                <div class="mb-0">
                    <label class="form-label">Catatan (opsional)</label>
                    <input type="text" name="catatan" class="form-control"
                           value="<?= e($form['catatan']) ?>" placeholder="Contoh: tolong dibungkus bubble wrap">
                </div>
            </div>
        </div>

        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white fw-semibold">
                <i class="bi bi-wallet2 me-1 text-primary"></i>Metode Pembayaran
            </div>
            <div class="card-body">
                <?php
                $metode = [
                    'transfer_bank' => ['Transfer Bank', 'BCA / BRI / Mandiri - a.n. MON CHÉRI', 'bank'],
                    'cod'           => ['Bayar di Tempat (COD)', 'Bayar saat barang diterima', 'cash-coin'],
                    'e_wallet'      => ['E-Wallet', 'GoPay / OVO / DANA', 'phone'],
                ];
                foreach ($metode as $nilai => [$judul, $ket, $ikon]):
                ?>
                <div class="form-check border rounded-3 p-3 ps-5 mb-2">
                    <input class="form-check-input" type="radio" name="metode_pembayaran"
                           id="mp_<?= $nilai ?>" value="<?= $nilai ?>"
                           <?= $form['metode_pembayaran'] === $nilai ? 'checked' : '' ?>>
                    <label class="form-check-label w-100" for="mp_<?= $nilai ?>">
                        <i class="bi bi-<?= $ikon ?> me-1 text-primary"></i>
                        <strong><?= e($judul) ?></strong>
                        <div class="small text-muted"><?= e($ket) ?></div>
                    </label>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <div class="col-lg-5">
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white fw-semibold">Rincian Produk</div>
            <ul class="list-group list-group-flush">
                <?php foreach ($item as $i): ?>
                <li class="list-group-item d-flex justify-content-between align-items-start">
                    <div class="me-2">
                        <div class="fw-semibold small"><?= e($i['nama_produk']) ?></div>
                        <div class="text-muted small">
                            <?= (int)$i['jumlah'] ?> x <?= rupiah($i['harga']) ?>
                        </div>
                    </div>
                    <span class="small fw-semibold text-nowrap">
                        <?= rupiah((float)$i['harga'] * (int)$i['jumlah']) ?>
                    </span>
                </li>
                <?php endforeach; ?>
            </ul>
            <div class="card-body">
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted">Subtotal</span><span><?= rupiah($subtotal) ?></span>
                </div>
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted">Ongkos Kirim</span>
                    <span><?= $ongkir > 0 ? rupiah($ongkir) : 'GRATIS' ?></span>
                </div>
                <hr>
                <div class="d-flex justify-content-between mb-3">
                    <span class="fw-semibold">Total Bayar</span>
                    <span class="fs-5 fw-bold text-primary"><?= rupiah($total) ?></span>
                </div>
                <button type="submit" class="btn btn-primary w-100"
                        onclick="this.disabled=true;this.form.submit();">
                    <i class="bi bi-bag-check me-1"></i>Buat Pesanan
                </button>
                <a href="<?= BASE_URL ?>/keranjang.php" class="btn btn-link w-100 text-muted small mt-1">
                    <i class="bi bi-arrow-left me-1"></i>Kembali ke keranjang
                </a>
            </div>
        </div>
    </div>
</div>
</form>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
