<?php
/**
 * =====================================================================
 * KONFIGURASI KONEKSI DATABASE (MySQLi)
 * MON CHERI - Luxury Heels Marketplace
 * =====================================================================
 *
 * LOKAL (XAMPP):
 *   DB_HOST = localhost | DB_USER = root | DB_PASS = '' | DB_NAME = db_marketplace
 *
 * INFINITYFREE (ubah 4 baris di bawah sesuai data dari Control Panel):
 *   DB_HOST = sqlXXX.infinityfree.com
 *   DB_USER = if0_XXXXXXXX
 *   DB_PASS = password_vpanel_anda
 *   DB_NAME = if0_XXXXXXXX_marketplace
 * =====================================================================
 */

// ------------------------- Pengaturan Database ----------------------
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'db_marketplace');

// ------------------------- Pengaturan Aplikasi ----------------------
define('APP_NAME', 'MON CHÉRI');
define('APP_TAGLINE', 'Luxury Heels Boutique');

// Zona waktu
date_default_timezone_set('Asia/Jakarta');

// Tampilkan error saat development (matikan / set 0 ketika sudah online)
define('APP_DEBUG', true);
if (APP_DEBUG) {
    error_reporting(E_ALL);
    ini_set('display_errors', '1');
} else {
    error_reporting(0);
    ini_set('display_errors', '0');
}

// ------------------------- Base URL otomatis ------------------------
// Menghitung sendiri path folder aplikasi sehingga tetap benar
// baik dijalankan di http://localhost/UTSWebSemantic/ maupun di hosting.
if (!defined('BASE_URL')) {
    $docRoot = str_replace('\\', '/', realpath($_SERVER['DOCUMENT_ROOT'] ?? '') ?: '');
    $appRoot = str_replace('\\', '/', realpath(__DIR__ . '/..') ?: '');
    $base    = ($docRoot && strpos($appRoot, $docRoot) === 0)
        ? substr($appRoot, strlen($docRoot))
        : '';
    define('BASE_URL', rtrim($base, '/'));
}
define('UPLOAD_DIR', __DIR__ . '/../uploads');
define('UPLOAD_URL', BASE_URL . '/uploads');

// ------------------------- Session ----------------------------------
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ------------------------- Koneksi MySQLi ---------------------------
$koneksi = @mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME);

if (!$koneksi) {
    http_response_code(500);
    echo '<div style="font-family:system-ui;padding:24px;border:1px solid #f5c2c7;'
       . 'background:#f8d7da;color:#842029;border-radius:8px;margin:40px auto;max-width:720px">'
       . '<h3 style="margin-top:0">Koneksi database gagal</h3>'
       . '<p>' . htmlspecialchars(mysqli_connect_error(), ENT_QUOTES, 'UTF-8') . '</p>'
       . '<p style="margin-bottom:0">Periksa pengaturan <code>DB_HOST</code>, <code>DB_USER</code>, '
       . '<code>DB_PASS</code>, dan <code>DB_NAME</code> pada file <code>config/koneksi.php</code>.</p>'
       . '</div>';
    exit;
}
mysqli_set_charset($koneksi, 'utf8mb4');
