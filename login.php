<?php
/**
 * Halaman Masuk (Login)
 */
require_once __DIR__ . '/config/koneksi.php';
require_once __DIR__ . '/includes/fungsi.php';

$judul_halaman = 'Masuk';
$email = '';
$error = '';

if (is_login()) {
    redirect(is_admin() ? 'admin/index.php' : 'index.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = input('email');
    $password = (string)($_POST['password'] ?? '');

    if ($email === '' || $password === '') {
        $error = 'Email dan password wajib diisi.';
    } else {
        $user = db_row('SELECT * FROM users WHERE email = ?', [$email]);
        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user'] = [
                'id_user' => (int)$user['id_user'],
                'nama'    => $user['nama'],
                'email'   => $user['email'],
                'role'    => $user['role'],
            ];
            set_flash('success', 'Selamat datang kembali, ' . $user['nama'] . '!');

            $tujuan = $_SESSION['redirect_setelah_login'] ?? '';
            unset($_SESSION['redirect_setelah_login']);
            if ($tujuan && strpos($tujuan, 'login.php') === false) {
                header('Location: ' . $tujuan);
                exit;
            }
            redirect($user['role'] === 'admin' ? 'admin/index.php' : 'index.php');
        }
        $error = 'Email atau password salah.';
    }
}

require_once __DIR__ . '/includes/header.php';
?>

<div class="row justify-content-center">
    <div class="col-md-6 col-lg-5">
        <div class="card border-0 shadow-sm">
            <div class="card-body p-4">
                <div class="text-center mb-4">
                    <i class="bi bi-person-circle fs-1 text-primary"></i>
                    <h4 class="fw-bold mt-2 mb-1">Masuk ke Akun</h4>
                    <p class="text-muted small mb-0">Gunakan email dan password Anda</p>
                </div>

                <?php if ($error): ?>
                <div class="alert alert-danger py-2 small">
                    <i class="bi bi-exclamation-circle me-1"></i><?= e($error) ?>
                </div>
                <?php endif; ?>

                <form method="post">
                    <div class="mb-3">
                        <label class="form-label">Email</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                            <input type="email" name="email" class="form-control" value="<?= e($email) ?>"
                                   placeholder="nama@email.com" required autofocus>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Password</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-lock"></i></span>
                            <input type="password" name="password" class="form-control"
                                   placeholder="Masukkan password" required>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="bi bi-box-arrow-in-right me-1"></i>Masuk
                    </button>
                </form>

                <p class="text-center text-muted small mt-3 mb-0">
                    Belum punya akun? <a href="<?= BASE_URL ?>/register.php">Daftar di sini</a>
                </p>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
