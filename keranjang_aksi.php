<?php
/**
 * Pemroses aksi keranjang belanja:
 * tambah | ubah | hapus | kosongkan
 */
require_once __DIR__ . '/config/koneksi.php';
require_once __DIR__ . '/includes/fungsi.php';

require_login();
$id_user = (int) user_login()['id_user'];
$aksi    = input('aksi');

/** Kembali ke halaman asal (jika berasal dari aplikasi ini) atau ke keranjang */
function kembali_ke_halaman()
{
    $ref = $_SERVER['HTTP_REFERER'] ?? '';
    $host = $_SERVER['HTTP_HOST'] ?? '';
    if ($ref && $host && strpos($ref, $host) !== false && strpos($ref, 'keranjang_aksi.php') === false) {
        header('Location: ' . $ref);
        exit;
    }
    redirect('keranjang.php');
}

switch ($aksi) {

    // -----------------------------------------------------------------
    case 'tambah':
        $id_produk = input_int('id_produk');
        $jumlah    = max(1, input_int('jumlah', 1));

        $produk = db_row(
            'SELECT id_produk, nama_produk, stok, status FROM produk WHERE id_produk = ?',
            [$id_produk]
        );

        if (!$produk || $produk['status'] !== 'aktif') {
            set_flash('danger', 'Produk tidak tersedia.');
            kembali_ke_halaman();
        }
        if ((int)$produk['stok'] < 1) {
            set_flash('warning', 'Stok produk sudah habis.');
            kembali_ke_halaman();
        }

        $stok  = (int)$produk['stok'];
        $ada   = db_row('SELECT id_keranjang, jumlah FROM keranjang WHERE id_user = ? AND id_produk = ?',
            [$id_user, $id_produk]);

        if ($ada) {
            $jumlah_baru = min((int)$ada['jumlah'] + $jumlah, $stok);
            db_exec('UPDATE keranjang SET jumlah = ? WHERE id_keranjang = ?',
                [$jumlah_baru, (int)$ada['id_keranjang']]);
        } else {
            $jumlah = min($jumlah, $stok);
            db_exec('INSERT INTO keranjang (id_user, id_produk, jumlah) VALUES (?, ?, ?)',
                [$id_user, $id_produk, $jumlah]);
        }

        set_flash('success', 'Produk "' . $produk['nama_produk'] . '" berhasil ditambahkan ke keranjang.');
        kembali_ke_halaman();

    // -----------------------------------------------------------------
    case 'ubah':
        $id_keranjang = input_int('id_keranjang');
        $jumlah       = max(1, input_int('jumlah', 1));

        $item = db_row(
            'SELECT kr.id_keranjang, p.stok
               FROM keranjang kr
               JOIN produk p ON p.id_produk = kr.id_produk
              WHERE kr.id_keranjang = ? AND kr.id_user = ?',
            [$id_keranjang, $id_user]
        );

        if (!$item) {
            set_flash('danger', 'Item keranjang tidak ditemukan.');
            redirect('keranjang.php');
        }

        $jumlah = min($jumlah, max(1, (int)$item['stok']));
        db_exec('UPDATE keranjang SET jumlah = ? WHERE id_keranjang = ?', [$jumlah, $id_keranjang]);

        set_flash('success', 'Jumlah item berhasil diperbarui.');
        redirect('keranjang.php');

    // -----------------------------------------------------------------
    case 'hapus':
        $id_keranjang = input_int('id_keranjang');
        db_exec('DELETE FROM keranjang WHERE id_keranjang = ? AND id_user = ?',
            [$id_keranjang, $id_user]);
        set_flash('success', 'Item berhasil dihapus dari keranjang.');
        redirect('keranjang.php');

    // -----------------------------------------------------------------
    case 'kosongkan':
        db_exec('DELETE FROM keranjang WHERE id_user = ?', [$id_user]);
        set_flash('success', 'Keranjang belanja dikosongkan.');
        redirect('keranjang.php');

    // -----------------------------------------------------------------
    default:
        set_flash('danger', 'Aksi tidak dikenali.');
        redirect('keranjang.php');
}
