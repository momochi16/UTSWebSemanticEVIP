<?php
/**
 * Kumpulan fungsi bantu aplikasi (helper) + wrapper query MySQLi
 */

// ---------------------------------------------------------------------
// Escape output
// ---------------------------------------------------------------------
function e($text)
{
    return htmlspecialchars((string)$text, ENT_QUOTES, 'UTF-8');
}

// ---------------------------------------------------------------------
// Format angka menjadi Rupiah
// ---------------------------------------------------------------------
function rupiah($angka)
{
    return 'Rp ' . number_format((float)$angka, 0, ',', '.');
}

// ---------------------------------------------------------------------
// Redirect
// ---------------------------------------------------------------------
function redirect($path)
{
    header('Location: ' . BASE_URL . '/' . ltrim($path, '/'));
    exit;
}

// ---------------------------------------------------------------------
// Wrapper query MySQLi (prepared statement)
// ---------------------------------------------------------------------
function db()
{
    global $koneksi;
    return $koneksi;
}

function db_bind($stmt, array $params)
{
    $types = '';
    foreach ($params as $p) {
        if (is_int($p))        $types .= 'i';
        elseif (is_float($p))  $types .= 'd';
        else                   $types .= 's';
    }
    mysqli_stmt_bind_param($stmt, $types, ...$params);
}

function db_query($sql, array $params = [])
{
    $stmt = mysqli_prepare(db(), $sql);
    if (!$stmt) {
        throw new RuntimeException('Query gagal disiapkan: ' . mysqli_error(db()));
    }
    if ($params) {
        db_bind($stmt, $params);
    }
    if (!mysqli_stmt_execute($stmt)) {
        throw new RuntimeException('Query gagal dieksekusi: ' . mysqli_stmt_error($stmt));
    }
    return $stmt;
}

/** Ambil semua baris hasil query */
function db_all($sql, array $params = [])
{
    $stmt = db_query($sql, $params);
    $res  = mysqli_stmt_get_result($stmt);
    $rows = $res ? mysqli_fetch_all($res, MYSQLI_ASSOC) : [];
    mysqli_stmt_close($stmt);
    return $rows;
}

/** Ambil satu baris hasil query */
function db_row($sql, array $params = [])
{
    $rows = db_all($sql, $params);
    return $rows ? $rows[0] : null;
}

/** Ambil satu nilai (kolom pertama) */
function db_value($sql, array $params = [])
{
    $row = db_row($sql, $params);
    return $row ? array_values($row)[0] : null;
}

/** Untuk INSERT / UPDATE / DELETE. Mengembalikan insert_id atau jumlah baris terpengaruh */
function db_exec($sql, array $params = [])
{
    $stmt = db_query($sql, $params);
    $id   = mysqli_stmt_insert_id($stmt);
    $rows = mysqli_stmt_affected_rows($stmt);
    mysqli_stmt_close($stmt);
    return $id ?: $rows;
}

// ---------------------------------------------------------------------
// Flash message
// ---------------------------------------------------------------------
function set_flash($tipe, $pesan)
{
    $_SESSION['flash'][] = ['tipe' => $tipe, 'pesan' => $pesan];
}

function tampil_flash()
{
    if (empty($_SESSION['flash'])) {
        return;
    }
    foreach ($_SESSION['flash'] as $f) {
        $tipe = in_array($f['tipe'], ['success', 'danger', 'warning', 'info'], true) ? $f['tipe'] : 'info';
        $ikon = ['success' => 'check-circle', 'danger' => 'exclamation-octagon',
                 'warning' => 'exclamation-triangle', 'info' => 'info-circle'][$tipe];
        echo '<div class="alert alert-' . $tipe . ' alert-dismissible fade show shadow-sm" role="alert">'
           . '<i class="bi bi-' . $ikon . ' me-2"></i>' . e($f['pesan'])
           . '<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>';
    }
    unset($_SESSION['flash']);
}

// ---------------------------------------------------------------------
// Autentikasi & hak akses
// ---------------------------------------------------------------------
function is_login()
{
    return !empty($_SESSION['user']);
}

function user_login()
{
    return $_SESSION['user'] ?? null;
}

function is_admin()
{
    return is_login() && ($_SESSION['user']['role'] ?? '') === 'admin';
}

function require_login()
{
    if (!is_login()) {
        set_flash('warning', 'Silakan masuk terlebih dahulu untuk melanjutkan.');
        $_SESSION['redirect_setelah_login'] = $_SERVER['REQUEST_URI'] ?? '';
        redirect('login.php');
    }
}

function require_admin()
{
    require_login();
    if (!is_admin()) {
        set_flash('danger', 'Anda tidak memiliki hak akses ke halaman administrator.');
        redirect('index.php');
    }
}

// ---------------------------------------------------------------------
// Gambar produk
// ---------------------------------------------------------------------
function gambar_produk($gambar)
{
    if ($gambar && is_file(UPLOAD_DIR . '/' . $gambar)) {
        return UPLOAD_URL . '/' . rawurlencode($gambar);
    }
    return BASE_URL . '/assets/img/placeholder.svg';
}

// ---------------------------------------------------------------------
// Keranjang
// ---------------------------------------------------------------------
function jumlah_item_keranjang()
{
    if (!is_login()) {
        return 0;
    }
    return (int) db_value('SELECT COALESCE(SUM(jumlah),0) FROM keranjang WHERE id_user = ?',
        [user_login()['id_user']]);
}

// ---------------------------------------------------------------------
// Label status pesanan
// ---------------------------------------------------------------------
function label_status($status)
{
    $map = [
        'menunggu'   => ['secondary', 'Menunggu'],
        'diproses'   => ['info', 'Diproses'],
        'dikirim'    => ['primary', 'Dikirim'],
        'selesai'    => ['success', 'Selesai'],
        'dibatalkan' => ['danger', 'Dibatalkan'],
    ];
    [$warna, $teks] = $map[$status] ?? ['secondary', ucfirst($status)];
    return '<span class="badge text-bg-' . $warna . '">' . e($teks) . '</span>';
}

function label_pembayaran($metode)
{
    $map = [
        'transfer_bank' => 'Transfer Bank',
        'cod'           => 'Bayar di Tempat (COD)',
        'e_wallet'      => 'E-Wallet',
    ];
    return $map[$metode] ?? $metode;
}

// ---------------------------------------------------------------------
// Membuat kode pesanan unik, contoh: INV-20261116-0007
// ---------------------------------------------------------------------
function buat_kode_pesanan()
{
    $prefix = 'INV-' . date('Ymd') . '-';
    $urut   = (int) db_value(
        'SELECT COUNT(*) FROM pesanan WHERE kode_pesanan LIKE ?',
        [$prefix . '%']
    );
    do {
        $urut++;
        $kode = $prefix . str_pad((string)$urut, 4, '0', STR_PAD_LEFT);
        $ada  = (int) db_value('SELECT COUNT(*) FROM pesanan WHERE kode_pesanan = ?', [$kode]);
    } while ($ada > 0);
    return $kode;
}

// ---------------------------------------------------------------------
// Ambil nilai input POST/GET dengan aman
// ---------------------------------------------------------------------
function input($key, $default = '')
{
    $nilai = $_POST[$key] ?? $_GET[$key] ?? $default;
    return is_string($nilai) ? trim($nilai) : $nilai;
}

function input_int($key, $default = 0)
{
    return (int) input($key, $default);
}
