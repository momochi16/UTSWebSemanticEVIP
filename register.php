<?php
/**
 * Halaman Daftar Akun Pembeli
 */
require_once __DIR__ . '/config/koneksi.php';
require_once __DIR__ . '/includes/fungsi.php';

$judul_halaman = 'Daftar Akun';
$error = '';
$data  = ['nama' => '', 'email' => '', 'no_hp' => '', 'alamat' => ''];

if (is_login()) {
    redirect('index.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data['nama']   = input('nama');
    $data['email']  = input('email');
    $data['no_hp']  = input('no_hp');
    $data['alamat'] = input('alamat');
    $password       = (string)($_POST['password'] ?? '');
    $password2      = (string)($_POST['password2'] ?? '');

    if ($data['nama'] === '' || $data['email'] === '' || $password === '') {
        $error = 'Nama, email, dan password wajib diisi.';
    } elseif (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
        $error = 'Format email tidak valid.';
    } elseif (strlen($password) < 6) {
        $error = 'Password minimal 6 karakter.';
    } elseif ($password !== $password2) {
        $error = 'Konfirmasi password tidak sama.';
    } elseif (db_value('SELECT COUNT(*) FROM users WHERE email = ?', [$data['email']]) > 0) {
        $error = 'Email sudah terdaftar, silakan gunakan email lain.';
    } else {
        $id = db_exec(
            'INSERT INTO users (nama, email, password, role, no_hp, alamat) VALUES (?, ?, ?, "pembeli", ?, ?)',
            [$data['nama'], $data['email'], password_hash($password, PASSWORD_DEFAULT),
             $data['no_hp'], $data['alamat']]
        );
        $_SESSION['user'] = [
            'id_user' => (int)$id,
            'nama'    => $data['nama'],
            'email'   => $data['email'],
            'role'    => 'pembeli',
        ];
        set_flash('success', 'Akun berhasil dibuat. Selamat berbelanja!');
        redirect('index.php');
    }
}

require_once __DIR__ . '/includes/header.php';
?>

<div class="row justify-content-center">
    <div class="col-md-8 col-lg-6">
        <div class="card border-0 shadow-sm">
            <div class="card-body p-4">
                <div class="text-center mb-4">
                    <i class="bi bi-person-plus-fill fs-1 text-primary"></i>
                    <h4 class="fw-bold mt-2 mb-1">Daftar Akun Pembeli</h4>
                    <p class="text-muted small mb-0">Gratis, hanya butuh beberapa detik</p>
                </div>

                <?php if ($error): ?>
                <div class="alert alert-danger py-2 small">
                    <i class="bi bi-exclamation-circle me-1"></i><?= e($error) ?>
                </div>
                <?php endif; ?>

                <form method="post">
                    <div class="mb-3">
                        <label class="form-label">Nama Lengkap <span class="text-danger">*</span></label>
                        <input type="text" name="nama" class="form-control" value="<?= e($data['nama']) ?>" required>
                    </div>
                    <div class="row g-3">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Email <span class="text-danger">*</span></label>
                            <input type="email" name="email" class="form-control" value="<?= e($data['email']) ?>" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">No. HP</label>
                            <input type="text" name="no_hp" class="form-control" value="<?= e($data['no_hp']) ?>">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Alamat</label>
                        <textarea name="alamat" class="form-control" rows="2"><?= e($data['alamat']) ?></textarea>
                    </div>
                    <div class="row g-3">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Password <span class="text-danger">*</span></label>
                            <input type="password" name="password" class="form-control" minlength="6" required>
                            <div class="form-text">Minimal 6 karakter.</div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Ulangi Password <span class="text-danger">*</span></label>
                            <input type="password" name="password2" class="form-control" minlength="6" required>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="bi bi-check2-circle me-1"></i>Buat Akun
                    </button>
                </form>

                <p class="text-center text-muted small mt-3 mb-0">
                    Sudah punya akun? <a href="<?= BASE_URL ?>/login.php">Masuk di sini</a>
                </p>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
