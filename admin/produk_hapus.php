<?php
/**
 * Panel Admin - Hapus Produk
 * Produk yang pernah dipesan tidak boleh dihapus (dilindungi foreign key),
 * disarankan ubah statusnya menjadi "nonaktif".
 */
require_once __DIR__ . '/../config/koneksi.php';
require_once __DIR__ . '/../includes/fungsi.php';

require_admin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('admin/produk.php');
}

$id_produk = input_int('id_produk');
$produk    = db_row('SELECT nama_produk, gambar FROM produk WHERE id_produk = ?', [$id_produk]);

if (!$produk) {
    set_flash('danger', 'Produk tidak ditemukan.');
    redirect('admin/produk.php');
}

$dipakai = (int) db_value('SELECT COUNT(*) FROM detail_pesanan WHERE id_produk = ?', [$id_produk]);
if ($dipakai > 0) {
    set_flash('warning', 'Produk "' . $produk['nama_produk'] . '" tidak dapat dihapus karena sudah pernah '
        . 'dipesan. Silakan ubah statusnya menjadi Nonaktif.');
    redirect('admin/produk.php');
}

try {
    db_exec('DELETE FROM produk WHERE id_produk = ?', [$id_produk]);
    if (!empty($produk['gambar']) && is_file(UPLOAD_DIR . '/' . $produk['gambar'])) {
        @unlink(UPLOAD_DIR . '/' . $produk['gambar']);
    }
    set_flash('success', 'Produk "' . $produk['nama_produk'] . '" berhasil dihapus.');
} catch (Throwable $ex) {
    set_flash('danger', 'Produk gagal dihapus: ' . $ex->getMessage());
}

redirect('admin/produk.php');
