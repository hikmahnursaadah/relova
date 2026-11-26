<?php
$user = Auth::user();
$firstName = explode(' ', $user['name'])[0];
$activeOrder = null;
foreach ($orders as $order) {
    if (in_array($order['status'], ['menunggu', 'dikonfirmasi', 'diproses', 'dikirim'], true)) {
        $activeOrder = $order;
        break;
    }
}
?>
<section class="dashboard-page buyer-home">
    <header class="buyer-home__greeting">
        <div>
            <span class="eyebrow">Ruang pembeli — <?= e(date('Y')) ?></span>
            <h1>Halo, <em><?= e($firstName) ?>.</em></h1>
            <p>Ini ringkasan lemari, pesanan, dan percakapanmu di Relova.</p>
        </div>
        <div class="buyer-home__quick">
            <a href="<?= url('/jelajahi') ?>" class="button button--dark">Jelajahi Koleksi <?= icon('arrow-up-right') ?></a>
            <a href="<?= url('/chat') ?>" class="button button--ghost">Pesan <?php if ($unreadChats): ?><span class="mini-badge"><?= $unreadChats ?></span><?php endif; ?></a>
        </div>
    </header>

    <div class="stats-strip">
        <div class="stat-card"><span>Total pesanan</span><strong><?= (int) ($stats['total_orders'] ?? 0) ?></strong><small>sejak bergabung</small></div>
        <div class="stat-card"><span>Sedang berjalan</span><strong><?= (int) ($stats['active_orders'] ?? 0) ?></strong><small>menunggu atau dikirim</small></div>
        <div class="stat-card"><span>Favorit</span><strong><?= count($wishlistIds) ?></strong><small>barang tersimpan</small></div>
        <div class="stat-card"><span>Total belanja</span><strong class="stat-card__money"><?= rupiah($stats['spent'] ?? 0) ?></strong><small>tidak termasuk pembatalan</small></div>
    </div>

    <div class="buyer-home__layout">
        <div class="buyer-home__main">
            <?php if ($activeOrder): ?>
                <section class="panel-card active-order-card">
                    <div class="active-order-card__head">
                        <div>
                            <span class="eyebrow">Sedang berjalan</span>
                            <h2><?= e($activeOrder['first_item_title'] ?: 'Pesananmu') ?></h2>
                            <span class="active-order-card__meta"><?= e($activeOrder['order_number']) ?> · dari <?= e($activeOrder['seller_name']) ?></span>
                        </div>
                        <span class="status <?= e(order_status_class($activeOrder['status'])) ?>"><?= e(order_status_label($activeOrder['status'])) ?></span>
                    </div>
                    <div class="active-order-card__progress">
                        <?php $flow = order_status_flow(); $index = array_search($activeOrder['status'], $flow, true); ?>
                        <?php foreach ($flow as $step => $status): ?>
                            <span class="<?= $index !== false && $step <= $index ? 'is-done' : '' ?>"><i></i><?= e(order_status_label($status)) ?></span>
                        <?php endforeach; ?>
                    </div>
                    <div class="active-order-card__foot">
                        <span><?= icon('truck') ?> Diperbarui <?= e(waktu_relatif($activeOrder['updated_at'])) ?></span>
                        <a href="<?= url('/pesanan/' . $activeOrder['id']) ?>" class="text-link">Lacak pesanan <?= icon('arrow-right') ?></a>
                    </div>
                </section>
            <?php else: ?>
                <section class="panel-card buyer-empty-order">
                    <div>
                        <span class="eyebrow">Belum ada pesanan berjalan</span>
                        <h2>Lemari berikutnya<br>sedang menunggu.</h2>
                        <p>Mulai dari koleksi terkurasi minggu ini. Setiap barang hanya ada satu.</p>
                        <a href="<?= url('/jelajahi') ?>" class="button button--dark">Mulai Menjelajah <?= icon('arrow-right') ?></a>
                    </div>
                    <span class="buyer-empty-order__mark">RW</span>
                </section>
            <?php endif; ?>

            <section class="panel-card">
                <div class="panel-card__head">
                    <div><span class="eyebrow">Riwayat terakhir</span><h2>Pesananmu</h2></div>
                    <a href="<?= url('/pesanan') ?>" class="text-link">Semua pesanan <?= icon('arrow-right') ?></a>
                </div>
                <?php if ($orders): ?>
                    <div class="mini-order-list">
                        <?php foreach ($orders as $order): ?>
                            <a href="<?= url('/pesanan/' . $order['id']) ?>" class="mini-order">
                                <img src="<?= e(product_image($order['first_item_image'])) ?>" alt="">
                                <span class="mini-order__copy">
                                    <strong><?= e($order['first_item_title'] ?: 'Pesanan') ?></strong>
                                    <small><?= e($order['order_number']) ?> · <?= e(tanggal($order['created_at'])) ?></small>
                                </span>
                                <span class="status <?= e(order_status_class($order['status'])) ?>"><?= e(order_status_label($order['status'])) ?></span>
                                <?= icon('chevron-right') ?>
                            </a>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <p class="panel-empty">Belum ada riwayat pesanan.</p>
                <?php endif; ?>
            </section>

            <section class="panel-card">
                <div class="panel-card__head">
                    <div><span class="eyebrow">Untukmu</span><h2>Mungkin cocok.</h2></div>
                    <a href="<?= url('/jelajahi') ?>" class="text-link">Jelajahi semua <?= icon('arrow-right') ?></a>
                </div>
                <div class="buyer-recommendations">
                    <?php foreach ($recommendations as $product): ?>
                        <a href="<?= url('/produk/' . $product['id']) ?>" class="buyer-recommendation">
                            <img src="<?= e(product_image($product['primary_image'])) ?>" alt="<?= e($product['title']) ?>">
                            <span><strong><?= e($product['title']) ?></strong><small><?= e($product['seller_name']) ?> · <?= e($product['size']) ?></small></span>
                            <b><?= rupiah($product['price']) ?></b>
                        </a>
                    <?php endforeach; ?>
                </div>
            </section>
        </div>

        <aside class="buyer-home__aside">
            <section class="panel-card">
                <div class="panel-card__head">
                    <div><span class="eyebrow">Tersimpan</span><h2>Favorit.</h2></div>
                    <a href="<?= url('/favorit') ?>" class="text-link">Lihat semua</a>
                </div>
                <?php if ($wishlist): ?>
                    <div class="wishlist-mini">
                        <?php foreach ($wishlist as $item): ?>
                            <a href="<?= url('/produk/' . $item['id']) ?>" class="wishlist-mini__item">
                                <img src="<?= e(product_image($item['primary_image'])) ?>" alt="">
                                <span><strong><?= e($item['title']) ?></strong><small><?= rupiah($item['price']) ?></small></span>
                                <?= icon('chevron-right') ?>
                            </a>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <p class="panel-empty">Belum ada barang favorit.</p>
                <?php endif; ?>
            </section>

            <section class="panel-card">
                <div class="panel-card__head">
                    <div><span class="eyebrow">Aktivitas</span><h2>Notifikasi.</h2></div>
                    <a href="<?= url('/notifikasi') ?>" class="text-link">Semua</a>
                </div>
                <?php if ($notifications): ?>
                    <div class="notification-mini-list notification-mini-list--panel">
                        <?php foreach ($notifications as $notification): ?>
                            <form action="<?= url('/notifikasi/' . $notification['id'] . '/baca') ?>" method="post" class="notification-mini-form">
                                <?= csrf_field() ?>
                                <button type="submit" class="notification-mini <?= !$notification['is_read'] ? 'is-unread' : '' ?>">
                                    <span class="notification-mini__dot"></span>
                                    <span>
                                        <strong><?= e($notification['title']) ?></strong>
                                        <small><?= e(excerpt($notification['message'], 70)) ?></small>
                                        <time><?= e(waktu_relatif($notification['created_at'])) ?></time>
                                    </span>
                                </button>
                            </form>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <p class="panel-empty">Tidak ada notifikasi baru.</p>
                <?php endif; ?>
            </section>

            <section class="panel-card panel-card--accent buyer-sell-card">
                <span class="eyebrow">Satu akun, dua peran</span>
                <h2>Punya barang yang siap bercerita lagi?</h2>
                <p>Jual pakaian atau sepatu yang masih layak pakai dari akun yang sama.</p>
                <a href="<?= Auth::isSeller() ? url('/penjual') : url('/jual') ?>" class="button button--dark button--full"><?= Auth::isSeller() ? 'Buka Dasbor Penjual' : 'Mulai Menjual' ?> <?= icon('arrow-up-right') ?></a>
            </section>
        </aside>
    </div>
</section>
