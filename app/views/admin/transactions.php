<?php
$query = $_GET;
unset($query['halaman']);
$queryString = http_build_query($query);
$pageBase = url('/admin/transaksi') . '?' . ($queryString ? $queryString . '&' : '') . 'halaman=';
$statusOptions = ['' => 'Semua status'] + order_statuses();
?>
<section class="admin-page">
    <header class="admin-page__head">
        <div>
            <span class="eyebrow">Arus transaksi</span>
            <h1>Semua <em>transaksi.</em></h1>
            <p><?= number_format($pagination['total'], 0, ',', '.') ?> pesanan tercatat. Pantau status dan selesaikan sengketa dengan tenang.</p>
        </div>
        <span class="admin-page__meta"><?= icon('box') ?> <?= number_format($pagination['total'], 0, ',', '.') ?> pesanan</span>
    </header>

    <div class="admin-kpi-grid admin-kpi-grid--compact">
        <article class="admin-kpi"><span class="admin-kpi__label">Total transaksi</span><strong><?= number_format((int) ($stats['total'] ?? 0), 0, ',', '.') ?></strong><span class="admin-kpi__delta">seluruh waktu</span></article>
        <article class="admin-kpi"><span class="admin-kpi__label">Menunggu</span><strong><?= (int) ($stats['pending'] ?? 0) ?></strong><span class="admin-kpi__delta">perlu konfirmasi penjual</span></article>
        <article class="admin-kpi"><span class="admin-kpi__label">Selesai</span><strong><?= (int) ($stats['completed'] ?? 0) ?></strong><span class="admin-kpi__delta">transaksi tuntas</span></article>
        <article class="admin-kpi"><span class="admin-kpi__label">Nilai transaksi</span><strong class="admin-kpi__money"><?= rupiah($stats['gross_value'] ?? 0) ?></strong><span class="admin-kpi__delta">tidak termasuk pembatalan</span></article>
    </div>

    <form action="<?= url('/admin/transaksi') ?>" method="get" class="admin-filter-bar">
        <div class="admin-filter-bar__search">
            <?= icon('search') ?>
            <label class="sr-only" for="admin-order-search">Cari transaksi</label>
            <input id="admin-order-search" type="search" name="q" value="<?= e($filters['q']) ?>" placeholder="Nomor pesanan, pembeli, atau penjual…">
        </div>
        <label class="sr-only" for="admin-order-status">Status</label>
        <select id="admin-order-status" name="status">
            <?php foreach ($statusOptions as $value => $label): ?>
                <option value="<?= e($value) ?>" <?= $filters['status'] === $value ? 'selected' : '' ?>><?= e($label) ?></option>
            <?php endforeach; ?>
        </select>
        <button type="submit" class="button button--dark button--sm">Terapkan</button>
        <?php if ($filters['q'] || $filters['status']): ?><a href="<?= url('/admin/transaksi') ?>" class="text-link">Atur ulang</a><?php endif; ?>
    </form>

    <div class="admin-panel">
        <?php if ($orders): ?>
            <div class="table-wrap">
                <table class="data-table data-table--admin data-table--transactions">
                    <thead><tr><th>Nomor</th><th>Barang</th><th>Pembeli</th><th>Penjual</th><th>Status</th><th>Total</th><th>Tanggal</th><th></th></tr></thead>
                    <tbody>
                        <?php foreach ($orders as $order): ?>
                            <tr>
                                <td><strong><?= e($order['order_number']) ?></strong></td>
                                <td><span class="table-title"><?= e($order['first_item_title'] ?: '-') ?></span><small><?= (int) $order['item_count'] ?> barang</small></td>
                                <td><?= e($order['buyer_name']) ?></td>
                                <td><?= e($order['seller_name']) ?></td>
                                <td><span class="status <?= e(order_status_class($order['status'])) ?>"><?= e(order_status_label($order['status'])) ?></span></td>
                                <td><strong><?= rupiah($order['total_amount']) ?></strong></td>
                                <td><?= e(tanggal($order['created_at'])) ?></td>
                                <td><a href="<?= url('/admin/transaksi/' . $order['id']) ?>" class="table-link" aria-label="Buka transaksi"><?= icon('arrow-right') ?></a></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php if ($pagination['pages'] > 1): ?>
                <nav class="admin-pagination" aria-label="Navigasi halaman transaksi">
                    <?php if ($pagination['page'] > 1): ?><a href="<?= e($pageBase . ($pagination['page'] - 1)) ?>"><?= icon('chevron-left') ?></a><?php else: ?><span class="is-disabled"><?= icon('chevron-left') ?></span><?php endif; ?>
                    <span>Halaman <?= $pagination['page'] ?> dari <?= $pagination['pages'] ?></span>
                    <?php if ($pagination['page'] < $pagination['pages']): ?><a href="<?= e($pageBase . ($pagination['page'] + 1)) ?>"><?= icon('chevron-right') ?></a><?php else: ?><span class="is-disabled"><?= icon('chevron-right') ?></span><?php endif; ?>
                </nav>
            <?php endif; ?>
        <?php else: ?>
            <div class="admin-empty-state">
                <span><?= icon('box') ?></span>
                <h2>Tidak ada transaksi yang cocok.</h2>
                <p>Ubah filter atau kata kunci untuk melihat transaksi lain.</p>
                <a href="<?= url('/admin/transaksi') ?>" class="button button--dark button--sm">Atur Ulang Filter</a>
            </div>
        <?php endif; ?>
    </div>
</section>
