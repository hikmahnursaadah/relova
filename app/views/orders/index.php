<?php
$tabs = ['' => 'Semua', 'menunggu' => 'Menunggu', 'dikonfirmasi' => 'Dikonfirmasi', 'diproses' => 'Diproses', 'dikirim' => 'Dikirim', 'selesai' => 'Selesai', 'dibatalkan' => 'Dibatalkan'];
?>
<section class="dashboard-page">
    <header class="dashboard-page__head">
        <div>
            <span class="eyebrow">Riwayat transaksi</span>
            <h1>Pesanan <em>saya.</em></h1>
        </div>
        <a href="<?= url('/jelajahi') ?>" class="text-link">Cari barang lain <?= icon('arrow-right') ?></a>
    </header>

    <div class="stats-strip">
        <div class="stat-card"><span>Total pesanan</span><strong><?= (int) ($stats['total_orders'] ?? 0) ?></strong><small>sejak bergabung</small></div>
        <div class="stat-card"><span>Sedang berjalan</span><strong><?= (int) ($stats['active_orders'] ?? 0) ?></strong><small>menunggu atau dikirim</small></div>
        <div class="stat-card"><span>Selesai</span><strong><?= (int) ($stats['completed'] ?? 0) ?></strong><small>transaksi tuntas</small></div>
        <div class="stat-card"><span>Total belanja</span><strong class="stat-card__money"><?= rupiah($stats['spent'] ?? 0) ?></strong><small>tidak termasuk pembatalan</small></div>
    </div>

    <nav class="filter-tabs" aria-label="Filter status pesanan">
        <?php foreach ($tabs as $value => $label): ?>
            <a href="<?= url('/pesanan' . ($value ? '?status=' . $value : '')) ?>" class="<?= $statusFilter === $value ? 'is-active' : '' ?>"><?= e($label) ?></a>
        <?php endforeach; ?>
    </nav>

    <?php if ($orders): ?>
        <div class="order-list">
            <?php foreach ($orders as $order): ?>
                <article class="order-card">
                    <header class="order-card__head">
                        <div>
                            <span class="order-card__number"><?= e($order['order_number']) ?></span>
                            <span class="order-card__date"><?= e(tanggal($order['created_at'], true)) ?></span>
                        </div>
                        <span class="status <?= e(order_status_class($order['status'])) ?>"><?= e(order_status_label($order['status'])) ?></span>
                    </header>
                    <div class="order-card__body">
                        <a href="<?= url('/pesanan/' . $order['id']) ?>" class="order-card__image">
                            <img src="<?= e(product_image($order['first_item_image'])) ?>" alt="<?= e($order['first_item_title'] ?: 'Produk pesanan') ?>">
                        </a>
                        <div class="order-card__info">
                            <span class="eyebrow">Dari <?= e($order['seller_name']) ?></span>
                            <h2><a href="<?= url('/pesanan/' . $order['id']) ?>"><?= e($order['first_item_title'] ?: 'Pesanan') ?></a></h2>
                            <span><?= (int) $order['item_count'] ?> barang · Total <?= rupiah($order['total_amount']) ?></span>
                        </div>
                        <div class="order-card__actions">
                            <a href="<?= url('/pesanan/' . $order['id']) ?>" class="button button--ghost button--sm">Lihat Detail <?= icon('arrow-right') ?></a>
                            <?php if ($order['status'] === 'selesai' && (int) $order['review_count'] < (int) $order['item_count']): ?>
                                <a href="<?= url('/pesanan/' . $order['id'] . '#ulasan') ?>" class="text-action"><?= icon('star') ?> Beri ulasan</a>
                            <?php endif; ?>
                        </div>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <div class="empty-state empty-state--panel">
            <span class="empty-state__icon"><?= icon('box') ?></span>
            <h2>Belum ada pesanan di sini.</h2>
            <p>Ketika kamu menemukan potongan yang tepat, pesanan dan statusnya akan muncul di halaman ini.</p>
            <a href="<?= url('/jelajahi') ?>" class="button button--dark">Mulai Menjelajah <?= icon('arrow-right') ?></a>
        </div>
    <?php endif; ?>
</section>
