<?php
$tabs = ['' => 'Semua', 'menunggu' => 'Menunggu', 'dikonfirmasi' => 'Dikonfirmasi', 'diproses' => 'Diproses', 'dikirim' => 'Dikirim', 'selesai' => 'Selesai', 'dibatalkan' => 'Dibatalkan'];
?>
<section class="dashboard-page">
    <header class="dashboard-page__head">
        <div>
            <span class="eyebrow">Pesanan masuk</span>
            <h1>Transaksi <em>penjual.</em></h1>
            <p class="dashboard-page__sub">Konfirmasi, kemas, dan kirim dengan komunikasi yang jelas.</p>
        </div>
        <a href="<?= url('/chat') ?>" class="text-link">Buka pesan <?= icon('chat') ?></a>
    </header>

    <div class="stats-strip stats-strip--compact">
        <div class="stat-card"><span>Total pesanan</span><strong><?= (int) ($stats['total_orders'] ?? 0) ?></strong></div>
        <div class="stat-card"><span>Perlu konfirmasi</span><strong><?= (int) ($stats['incoming'] ?? 0) ?></strong></div>
        <div class="stat-card"><span>Sedang berjalan</span><strong><?= (int) ($stats['active_orders'] ?? 0) ?></strong></div>
        <div class="stat-card"><span>Selesai</span><strong><?= (int) ($stats['completed'] ?? 0) ?></strong></div>
        <div class="stat-card"><span>Pendapatan</span><strong class="stat-card__money"><?= rupiah($stats['revenue'] ?? 0) ?></strong></div>
    </div>

    <nav class="filter-tabs" aria-label="Filter status pesanan">
        <?php foreach ($tabs as $value => $label): ?>
            <a href="<?= url('/penjual/pesanan' . ($value ? '?status=' . $value : '')) ?>" class="<?= $statusFilter === $value ? 'is-active' : '' ?>"><?= e($label) ?></a>
        <?php endforeach; ?>
    </nav>

    <?php if ($orders): ?>
        <div class="seller-order-list">
            <?php foreach ($orders as $order): ?>
                <article class="seller-order-card">
                    <header class="seller-order-card__head">
                        <div>
                            <span class="order-card__number"><?= e($order['order_number']) ?></span>
                            <span class="order-card__date"><?= e(tanggal($order['created_at'], true)) ?></span>
                        </div>
                        <span class="status <?= e(order_status_class($order['status'])) ?>"><?= e(order_status_label($order['status'])) ?></span>
                    </header>
                    <div class="seller-order-card__body">
                        <div class="seller-order-card__item">
                            <img src="<?= e(product_image($order['first_item_image'])) ?>" alt="">
                            <div>
                                <h2><a href="<?= url('/penjual/pesanan/' . $order['id']) ?>"><?= e($order['first_item_title'] ?: 'Pesanan') ?></a></h2>
                                <span><?= (int) $order['item_count'] ?> barang · <?= rupiah($order['total_amount']) ?></span>
                            </div>
                        </div>
                        <div class="seller-order-card__buyer">
                            <span class="eyebrow">Pembeli</span>
                            <div>
                                <span class="avatar avatar--xs" style="--avatar-color: <?= e(avatar_color($order['buyer_name'])) ?>"><?= e(initials($order['buyer_name'])) ?></span>
                                <strong><?= e($order['buyer_name']) ?></strong>
                            </div>
                        </div>
                        <div class="seller-order-card__actions">
                            <?php if ($order['status'] === 'menunggu'): ?>
                                <a href="<?= url('/penjual/pesanan/' . $order['id']) ?>" class="button button--dark button--sm">Konfirmasi <?= icon('arrow-right') ?></a>
                            <?php else: ?>
                                <a href="<?= url('/penjual/pesanan/' . $order['id']) ?>" class="button button--ghost button--sm">Kelola Pesanan <?= icon('arrow-right') ?></a>
                            <?php endif; ?>
                        </div>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <div class="empty-state empty-state--panel">
            <span class="empty-state__icon"><?= icon('box') ?></span>
            <h2><?= $statusFilter ? 'Tidak ada pesanan dengan status ini.' : 'Belum ada pesanan masuk.' ?></h2>
            <p><?= $statusFilter ? 'Coba lihat semua pesanan atau pilih status lain.' : 'Saat pembeli memesan produkmu, detail dan statusnya akan muncul di sini.' ?></p>
            <a href="<?= $statusFilter ? url('/penjual/pesanan') : url('/penjual/produk') ?>" class="button button--dark"><?= $statusFilter ? 'Lihat Semua Pesanan' : 'Kelola Produk' ?> <?= icon('arrow-right') ?></a>
        </div>
    <?php endif; ?>
</section>
