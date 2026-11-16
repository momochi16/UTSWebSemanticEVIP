<?php
/**
 * Keluar dari akun (Logout)
 */
require_once __DIR__ . '/config/koneksi.php';
require_once __DIR__ . '/includes/fungsi.php';

unset($_SESSION['user']);
session_regenerate_id(true);

set_flash('success', 'Anda telah keluar dari akun.');
redirect('index.php');
