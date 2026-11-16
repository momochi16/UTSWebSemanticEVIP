<?php
/**
 * Komponen tampilan rincian/invoice pesanan.
 * Variabel yang dibutuhkan:
 *   $pesanan  : array data dari tabel pesanan
 *   $rincian  : array data dari tabel detail_pesanan
 *   $subtotal : (opsional) subtotal, dihitung otomatis jika kosong
 */
$subtotal = $subtotal ?? array_sum(array_map(fn($r) => (float)$r['subtotal'], $rincian));
$ongkir   = max(0, (float)$pesanan['total_bayar'] - $subtotal);
?>
<div class="card border-0 shadow-sm">
    <div class="card-header bg-white d-flex flex-wrap justify-content-between align-items-center gap-2">
        <div>
            <div class="fw-bold"><i class="bi bi-receipt me-1 text-primary"></i><?= e($pesanan['kode_pesanan']) ?></div>
            <div class="small text-muted">
                <?= date('d M Y, H:i', strtotime($pesanan['created_at'])) ?> WIB
            </div>
        </div>
        <div><?= label_status($pesanan['status']) ?></div>
    </div>
    <div class="table-responsive">
        <table class="table table-rapat mb-0">
            <thead class="table-light">
                <tr>
                    <th>Produk</th>
                    <th class="text-end">Harga Satuan</th>
                    <th class="text-center">Jumlah</th>
                    <th class="text-end">Subtotal</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($rincian as $r): ?>
                <tr>
                    <td><?= e($r['nama_produk']) ?></td>
                    <td class="text-end text-nowrap"><?= rupiah($r['harga_satuan']) ?></td>
                    <td class="text-center"><?= (int)$r['jumlah'] ?></td>
                    <td class="text-end text-nowrap fw-semibold"><?= rupiah($r['subtotal']) ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
            <tfoot class="table-light">
                <tr>
                    <td colspan="3" class="text-end">Subtotal Produk</td>
                    <td class="text-end text-nowrap"><?= rupiah($subtotal) ?></td>
                </tr>
                <tr>
                    <td colspan="3" class="text-end">Ongkos Kirim</td>
                    <td class="text-end text-nowrap"><?= $ongkir > 0 ? rupiah($ongkir) : 'GRATIS' ?></td>
                </tr>
                <tr>
                    <td colspan="3" class="text-end fw-bold">Total Bayar</td>
                    <td class="text-end text-nowrap fw-bold text-primary fs-6">
                        <?= rupiah($pesanan['total_bayar']) ?>
                    </td>
                </tr>
            </tfoot>
        </table>
    </div>
    <div class="card-body border-top">
        <div class="row g-3">
            <div class="col-md-6">
                <div class="small text-muted mb-1">Alamat Pengiriman</div>
                <div class="fw-semibold"><?= e($pesanan['nama_penerima']) ?></div>
                <div class="small"><?= e($pesanan['no_hp']) ?></div>
                <div class="small"><?= nl2br(e($pesanan['alamat_kirim'])) ?></div>
                <?php if (!empty($pesanan['catatan'])): ?>
                <div class="small text-muted mt-2">
                    <i class="bi bi-chat-left-text me-1"></i>Catatan: <?= e($pesanan['catatan']) ?>
                </div>
                <?php endif; ?>
            </div>
            <div class="col-md-6">
                <div class="small text-muted mb-1">Metode Pembayaran</div>
                <div class="fw-semibold"><?= e(label_pembayaran($pesanan['metode_pembayaran'])) ?></div>
                <?php if ($pesanan['metode_pembayaran'] === 'transfer_bank'): ?>
                <div class="small text-muted mt-2">
                    Silakan transfer ke rekening <strong>BCA 1234567890</strong> a.n. MON CHÉRI,
                    lalu konfirmasi ke admin.
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
