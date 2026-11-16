<?php
/**
 * Panel Admin - CRUD Kategori
 */
require_once __DIR__ . '/../config/koneksi.php';
require_once __DIR__ . '/../includes/fungsi.php';
require_admin();

$judul_halaman = 'Kategori';
$menu_aktif    = 'kategori';

$error = '';

// ------------------------- Proses aksi -------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $aksi  = input('aksi');
    $id    = input_int('id_kategori');
    $nama  = input('nama_kategori');
    $desk  = input('deskripsi');

    if ($aksi === 'simpan') {
        if ($nama === '') {
            $error = 'Nama kategori wajib diisi.';
        } else {
            $bentrok = db_value('SELECT COUNT(*) FROM kategori WHERE nama_kategori = ? AND id_kategori <> ?',
                [$nama, $id]);
            if ($bentrok > 0) {
                $error = 'Nama kategori sudah digunakan.';
            } elseif ($id > 0) {
                db_exec('UPDATE kategori SET nama_kategori = ?, deskripsi = ? WHERE id_kategori = ?',
                    [$nama, $desk, $id]);
                set_flash('success', 'Kategori "' . $nama . '" berhasil diperbarui.');
                redirect('admin/kategori.php');
            } else {
                db_exec('INSERT INTO kategori (nama_kategori, deskripsi) VALUES (?, ?)', [$nama, $desk]);
                set_flash('success', 'Kategori "' . $nama . '" berhasil ditambahkan.');
                redirect('admin/kategori.php');
            }
        }
    } elseif ($aksi === 'hapus') {
        $dipakai = (int) db_value('SELECT COUNT(*) FROM produk WHERE id_kategori = ?', [$id]);
        if ($dipakai > 0) {
            set_flash('warning', 'Kategori tidak dapat dihapus karena masih digunakan oleh '
                . $dipakai . ' produk.');
        } else {
            db_exec('DELETE FROM kategori WHERE id_kategori = ?', [$id]);
            set_flash('success', 'Kategori berhasil dihapus.');
        }
        redirect('admin/kategori.php');
    }
}

// ------------------------- Mode edit -------------------------
$edit_id   = input_int('edit');
$data_edit = $edit_id > 0 ? db_row('SELECT * FROM kategori WHERE id_kategori = ?', [$edit_id]) : null;

$kategori = db_all(
    'SELECT k.*, (SELECT COUNT(*) FROM produk p WHERE p.id_kategori = k.id_kategori) AS jml_produk
       FROM kategori k ORDER BY k.nama_kategori'
);

require __DIR__ . '/includes/header.php';
?>

<div class="row g-4">
    <div class="col-lg-4">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white fw-semibold">
                <i class="bi bi-<?= $data_edit ? 'pencil-square' : 'plus-circle' ?> me-1 text-primary"></i>
                <?= $data_edit ? 'Edit Kategori' : 'Tambah Kategori' ?>
            </div>
            <div class="card-body">
                <?php if ($error): ?>
                <div class="alert alert-danger py-2 small"><i class="bi bi-exclamation-circle me-1"></i><?= e($error) ?></div>
                <?php endif; ?>

                <form method="post">
                    <input type="hidden" name="aksi" value="simpan">
                    <input type="hidden" name="id_kategori" value="<?= (int)($data_edit['id_kategori'] ?? 0) ?>">
                    <div class="mb-3">
                        <label class="form-label">Nama Kategori <span class="text-danger">*</span></label>
                        <input type="text" name="nama_kategori" class="form-control"
                               value="<?= e($data_edit['nama_kategori'] ?? '') ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Deskripsi</label>
                        <textarea name="deskripsi" class="form-control" rows="3"><?= e($data_edit['deskripsi'] ?? '') ?></textarea>
                    </div>
                    <div class="d-flex gap-2">
                        <button class="btn btn-primary"><i class="bi bi-save me-1"></i>Simpan</button>
                        <?php if ($data_edit): ?>
                        <a href="<?= BASE_URL ?>/admin/kategori.php" class="btn btn-outline-secondary">Batal</a>
                        <?php endif; ?>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="col-lg-8">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white fw-semibold">
                <i class="bi bi-tags me-1 text-primary"></i>Daftar Kategori
                <span class="badge text-bg-light ms-1"><?= count($kategori) ?></span>
            </div>
            <div class="table-responsive">
                <table class="table table-rapat align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th style="width:60px">#</th>
                            <th>Nama Kategori</th>
                            <th>Deskripsi</th>
                            <th class="text-center">Produk</th>
                            <th class="text-end" style="width:130px">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php if (!$kategori): ?>
                        <tr><td colspan="5" class="text-center text-muted py-4">Belum ada kategori.</td></tr>
                    <?php else: $no = 1; foreach ($kategori as $k): ?>
                        <tr>
                            <td><?= $no++ ?></td>
                            <td class="fw-semibold"><?= e($k['nama_kategori']) ?></td>
                            <td class="small text-muted"><?= e($k['deskripsi'] ?: '-') ?></td>
                            <td class="text-center"><span class="badge text-bg-light"><?= (int)$k['jml_produk'] ?></span></td>
                            <td class="text-end">
                                <a href="<?= BASE_URL ?>/admin/kategori.php?edit=<?= (int)$k['id_kategori'] ?>"
                                   class="btn btn-sm btn-outline-primary" title="Edit"><i class="bi bi-pencil-square"></i></a>
                                <form action="<?= BASE_URL ?>/admin/kategori.php" method="post" class="d-inline"
                                      onsubmit="return confirm('Hapus kategori ini?')">
                                    <input type="hidden" name="aksi" value="hapus">
                                    <input type="hidden" name="id_kategori" value="<?= (int)$k['id_kategori'] ?>">
                                    <button class="btn btn-sm btn-outline-danger" title="Hapus"><i class="bi bi-trash"></i></button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php require __DIR__ . '/includes/footer.php'; ?>
