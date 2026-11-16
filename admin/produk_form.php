<?php
/**
 * Panel Admin - Form Tambah / Edit Produk
 */
require_once __DIR__ . '/../config/koneksi.php';
require_once __DIR__ . '/../includes/fungsi.php';
require_admin();

$judul_halaman = 'Form Produk';
$menu_aktif    = 'produk';

$id_produk = input_int('id');
$mode_edit = $id_produk > 0;
$error     = '';

$form = [
    'nama_produk' => '',
    'id_kategori' => '',
    'harga'       => '',
    'stok'        => '',
    'deskripsi'   => '',
    'status'      => 'aktif',
    'gambar'      => null,
];

if ($mode_edit) {
    $data = db_row('SELECT * FROM produk WHERE id_produk = ?', [$id_produk]);
    if (!$data) {
        set_flash('danger', 'Produk tidak ditemukan.');
        redirect('admin/produk.php');
    }
    $form = array_merge($form, $data);
}

$daftar_kategori = db_all('SELECT id_kategori, nama_kategori FROM kategori ORDER BY nama_kategori');

// ------------------------- Proses simpan -------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $form['nama_produk'] = input('nama_produk');
    $form['id_kategori'] = input_int('id_kategori');
    $form['harga']       = input('harga');
    $form['stok']        = input_int('stok');
    $form['deskripsi']   = input('deskripsi');
    $form['status']      = input('status', 'aktif');

    if ($form['nama_produk'] === '') {
        $error = 'Nama produk wajib diisi.';
    } elseif ($form['id_kategori'] <= 0) {
        $error = 'Kategori wajib dipilih.';
    } elseif (!is_numeric($form['harga']) || (float)$form['harga'] < 0) {
        $error = 'Harga harus berupa angka yang valid.';
    } elseif ($form['stok'] < 0) {
        $error = 'Stok tidak boleh negatif.';
    } elseif (!in_array($form['status'], ['aktif', 'nonaktif'], true)) {
        $error = 'Status produk tidak valid.';
    }

    // ------------------------- Upload gambar -------------------------
    $nama_gambar = $form['gambar'];
    if ($error === '' && !empty($_FILES['gambar']['name'])) {
        $file    = $_FILES['gambar'];
        $ext     = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $izin    = ['jpg', 'jpeg', 'png', 'webp'];

        if ($file['error'] !== UPLOAD_ERR_OK) {
            $error = 'Gagal mengunggah gambar (kode error ' . $file['error'] . ').';
        } elseif (!in_array($ext, $izin, true)) {
            $error = 'Format gambar harus JPG, JPEG, PNG, atau WEBP.';
        } elseif ($file['size'] > 2 * 1024 * 1024) {
            $error = 'Ukuran gambar maksimal 2 MB.';
        } else {
            if (!is_dir(UPLOAD_DIR)) {
                @mkdir(UPLOAD_DIR, 0755, true);
            }
            $nama_gambar = 'produk_' . date('YmdHis') . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
            if (!move_uploaded_file($file['tmp_name'], UPLOAD_DIR . '/' . $nama_gambar)) {
                $error = 'Gambar gagal disimpan ke folder uploads. Periksa izin folder.';
                $nama_gambar = $form['gambar'];
            } elseif ($mode_edit && !empty($form['gambar']) && is_file(UPLOAD_DIR . '/' . $form['gambar'])) {
                @unlink(UPLOAD_DIR . '/' . $form['gambar']);
            }
        }
    }

    if ($error === '') {
        if ($mode_edit) {
            db_exec(
                'UPDATE produk SET id_kategori = ?, nama_produk = ?, deskripsi = ?, harga = ?,
                        stok = ?, gambar = ?, status = ?
                  WHERE id_produk = ?',
                [$form['id_kategori'], $form['nama_produk'], $form['deskripsi'], (float)$form['harga'],
                 $form['stok'], $nama_gambar, $form['status'], $id_produk]
            );
            set_flash('success', 'Produk "' . $form['nama_produk'] . '" berhasil diperbarui.');
        } else {
            db_exec(
                'INSERT INTO produk (id_kategori, id_user, nama_produk, deskripsi, harga, stok, gambar, status)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?)',
                [$form['id_kategori'], (int) user_login()['id_user'], $form['nama_produk'],
                 $form['deskripsi'], (float)$form['harga'], $form['stok'], $nama_gambar, $form['status']]
            );
            set_flash('success', 'Produk "' . $form['nama_produk'] . '" berhasil ditambahkan.');
        }
        redirect('admin/produk.php');
    }
}

require __DIR__ . '/includes/header.php';
?>

<nav aria-label="breadcrumb">
    <ol class="breadcrumb small">
        <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/admin/produk.php" class="text-decoration-none">Data Produk</a></li>
        <li class="breadcrumb-item active"><?= $mode_edit ? 'Edit' : 'Tambah' ?></li>
    </ol>
</nav>

<div class="row justify-content-center">
    <div class="col-lg-9">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white fw-semibold">
                <i class="bi bi-<?= $mode_edit ? 'pencil-square' : 'plus-circle' ?> me-1 text-primary"></i>
                <?= $mode_edit ? 'Edit Produk' : 'Tambah Produk Baru' ?>
            </div>
            <div class="card-body">
                <?php if ($error): ?>
                <div class="alert alert-danger py-2 small"><i class="bi bi-exclamation-circle me-1"></i><?= e($error) ?></div>
                <?php endif; ?>

                <form method="post" enctype="multipart/form-data">
                    <div class="mb-3">
                        <label class="form-label">Nama Produk <span class="text-danger">*</span></label>
                        <input type="text" name="nama_produk" class="form-control"
                               value="<?= e($form['nama_produk']) ?>" required>
                    </div>

                    <div class="row g-3">
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Kategori <span class="text-danger">*</span></label>
                            <select name="id_kategori" class="form-select" required>
                                <option value="">-- Pilih Kategori --</option>
                                <?php foreach ($daftar_kategori as $k): ?>
                                <option value="<?= (int)$k['id_kategori'] ?>"
                                    <?= (int)$form['id_kategori'] === (int)$k['id_kategori'] ? 'selected' : '' ?>>
                                    <?= e($k['nama_kategori']) ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Harga (Rp) <span class="text-danger">*</span></label>
                            <input type="number" name="harga" class="form-control" min="0" step="100"
                                   value="<?= e((string)$form['harga']) ?>" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Stok <span class="text-danger">*</span></label>
                            <input type="number" name="stok" class="form-control" min="0"
                                   value="<?= e((string)$form['stok']) ?>" required>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Deskripsi Produk</label>
                        <textarea name="deskripsi" class="form-control" rows="4"><?= e($form['deskripsi']) ?></textarea>
                    </div>

                    <div class="row g-3 align-items-start">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Gambar Produk</label>
                            <input type="file" name="gambar" class="form-control" accept=".jpg,.jpeg,.png,.webp">
                            <div class="form-text">Format JPG/PNG/WEBP, maksimal 2 MB.</div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label d-block">Pratinjau</label>
                            <img src="<?= gambar_produk($form['gambar']) ?>" alt="Pratinjau"
                                 style="width:130px;height:130px;object-fit:cover;border-radius:.5rem;background:#f1f3f5">
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label d-block">Status Produk</label>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="status" id="st_aktif" value="aktif"
                                <?= $form['status'] === 'aktif' ? 'checked' : '' ?>>
                            <label class="form-check-label" for="st_aktif">Aktif (tampil di toko)</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="status" id="st_nonaktif" value="nonaktif"
                                <?= $form['status'] === 'nonaktif' ? 'checked' : '' ?>>
                            <label class="form-check-label" for="st_nonaktif">Nonaktif</label>
                        </div>
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-save me-1"></i>Simpan
                        </button>
                        <a href="<?= BASE_URL ?>/admin/produk.php" class="btn btn-outline-secondary">Batal</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require __DIR__ . '/includes/footer.php'; ?>
