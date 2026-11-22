<?php
$user = Auth::user();
$firstName = explode(' ', $user['name'])[0];
$rating = (float) ($reviewStats['average'] ?? 0);
$reviewTotal = (int) ($reviewStats['total'] ?? 0);
?>
<section class="dashboard-page seller-dashboard">
    <header class="seller-dashboard__greeting">
        <div>
            <span class="eyebrow">Studio penjual — <?= e(date('Y')) ?></span>
            <h1>Selamat datang, <em><?= e($firstName) ?>.</em></h1>
            <p>Etalase, pesanan, dan cerita dari pembelimu dalam satu tempat.</p>
        </div>
        <div class="seller-dashboard__actions">
            <a href="<?= url('/penjual/produk/tambah') ?>" class="button button--dark"><?= icon('plus') ?> Tambah Produk</a>
            <a href="<?= url('/jelajahi?penjual=' . Auth::id()) ?>" class="button button--ghost">Lihat Etalase <?= icon('arrow-up-right') ?></a>
        </div>
    </header>

    <div class="stats-strip">
        <div class="stat-card stat-card--highlight">
            <span>Pesanan masuk</span>
            <strong><?= (int) ($orderStats['incoming'] ?? 0) ?></strong>
            <small>menunggu konfirmasi</small>
            <?php if ((int) ($orderStats['incoming'] ?? 0) > 0): ?><a href="<?= url('/penjual/pesanan?status=menunggu') ?>">Konfirmasi sekarang <?= icon('arrow-right') ?></a><?php endif; ?>
        </div>
        <div class="stat-card"><span>Produk aktif</span><strong><?= (int) ($productStats['active_count'] ?? 0) ?></strong><small>dari <?= (int) ($productStats['total'] ?? 0) ?> produk</small></div>
        <div class="stat-card"><span>Terjual</span><strong><?= (int) ($productStats['sold_count'] ?? 0) ?></strong><small>barang menemukan rumah</small></div>
        <div class="stat-card"><span>Pendapatan selesai</span><strong class="stat-card__money"><?= rupiah($orderStats['revenue'] ?? 0) ?></strong><small>dari transaksi selesai</small></div>
    </div>

    <div class="seller-dashboard__layout">
        <div class="seller-dashboard__main">
            <section class="panel-card">
                <div class="panel-card__head">
                    <div><span class="eyebrow">Perlu perhatian</span><h2>Pesanan terbaru</h2></div>
                    <a href="<?= url('/penjual/pesanan') ?>" class="text-link">Semua pesanan <?= icon('arrow-right') ?></a>
                </div>
                <?php if ($recentOrders): ?>
                    <div class="table-wrap">
                        <table class="data-table">
                            <thead><tr><th>Pesanan</th><th>Pembeli</th><th>Status</th><th>Total</th><th></th></tr></thead>
                            <tbody>
                                <?php foreach ($recentOrders as $order): ?>
                                    <tr>
                                        <td><strong><?= e($order['order_number']) ?></strong><small><?= e($order['first_item_title'] ?: '-') ?></small></td>
                                        <td><?= e($order['buyer_name']) ?><small><?= e(tanggal($order['created_at'])) ?></small></td>
                                        <td><span class="status <?= e(order_status_class($order['status'])) ?>"><?= e(order_status_label($order['status'])) ?></span></td>
                                        <td><?= rupiah($order['total_amount']) ?></td>
                                        <td><a href="<?= url('/penjual/pesanan/' . $order['id']) ?>" class="table-link" aria-label="Kelola pesanan"><?= icon('arrow-right') ?></a></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <p class="panel-empty">Belum ada pesanan masuk. Etalase yang lengkap dan foto yang jelas membantu pembeli menemukanmu.</p>
                <?php endif; ?>
            </section>

            <section class="panel-card">
                <div class="panel-card__head">
                    <div><span class="eyebrow">Etalase</span><h2>Produk yang tayang</h2></div>
                    <a href="<?= url('/penjual/produk') ?>" class="text-link">Kelola produk <?= icon('arrow-right') ?></a>
                </div>
                <?php if ($products): ?>
                    <div class="seller-product-list">
                        <?php foreach (array_slice($products, 0, 5) as $product): ?>
                            <article class="seller-product-row">
                                <img src="<?= e(product_image($product['primary_image'])) ?>" alt="<?= e($product['title']) ?>">
                                <div class="seller-product-row__copy">
                                    <strong><?= e($product['title']) ?></strong>
                                    <span><?= e($product['category_name']) ?> · Ukuran <?= e($product['size']) ?> · <?= rupiah($product['price']) ?></span>
                                </div>
                                <span class="status <?= e(status_produk_class($product['status'])) ?>"><?= e(status_produk_label($product['status'])) ?></span>
                                <span class="seller-product-row__views"><?= icon('eye') ?> <?= (int) $product['view_count'] ?></span>
                                <a href="<?= url('/penjual/produk/' . $product['id'] . '/edit') ?>" class="table-link" aria-label="Edit <?= e($product['title']) ?>"><?= icon('edit') ?></a>
                            </article>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="seller-empty">
                        <span class="empty-state__icon"><?= icon('hanger') ?></span>
                        <div><strong>Etalase masih kosong.</strong><p>Tambahkan produk pertama dan mulai cerita barunya.</p></div>
                        <a href="<?= url('/penjual/produk/tambah') ?>" class="button button--dark button--sm">Tambah Produk</a>
                    </div>
                <?php endif; ?>
            </section>
        </div>

        <aside class="seller-dashboard__aside">
            <section class="panel-card seller-rating-card">
                <span class="eyebrow">Reputasi penjual</span>
                <div class="seller-rating-card__score">
                    <strong><?= number_format($rating, 1, ',', '.') ?></strong>
                    <?= star_markup($rating) ?>
                    <span><?= $reviewTotal ?> ulasan</span>
                </div>
                <div class="seller-rating-card__stats">
                    <div><span>Dilihat</span><strong><?= number_format((int) ($productStats['total_views'] ?? 0), 0, ',', '.') ?></strong></div>
                    <div><span>Transaksi selesai</span><strong><?= (int) ($orderStats['completed'] ?? 0) ?></strong></div>
                </div>
                <?php if ($reviewTotal === 0): ?>
                    <p class="panel-empty">Ulasan pertama akan muncul setelah transaksi selesai.</p>
                <?php endif; ?>
            </section>

            <section class="panel-card">
                <div class="panel-card__head">
                    <div><span class="eyebrow">Suara pembeli</span><h2>Ulasan terbaru</h2></div>
                    <a href="<?= url('/profil#ulasan') ?>" class="text-link">Semua</a>
                </div>
                <?php if ($reviews): ?>
                    <div class="seller-review-list">
                        <?php foreach ($reviews as $review): ?>
                            <article class="seller-review">
                                <div class="seller-review__head">
                                    <span class="avatar avatar--xs" style="--avatar-color: <?= e(avatar_color($review['buyer_name'])) ?>"><?= e(initials($review['buyer_name'])) ?></span>
                                    <div><strong><?= e($review['buyer_name']) ?></strong><small><?= e(tanggal($review['created_at'])) ?></small></div>
                                    <?= star_markup((int) $review['rating']) ?>
                                </div>
                                <p>“<?= e(excerpt($review['comment'] ?: 'Transaksi berjalan baik.', 120)) ?>”</p>
                                <span class="seller-review__product"><?= e($review['product_title']) ?></span>
                            </article>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <p class="panel-empty">Belum ada ulasan. Setiap transaksi selesai membuka kesempatan untuk mendapat ulasan.</p>
                <?php endif; ?>
            </section>

            <section class="panel-card panel-card--accent seller-tip-card">
                <span class="eyebrow">Catatan studio</span>
                <h2>Foto yang baik menjual lebih cepat.</h2>
                <p>Gunakan cahaya alami di dekat jendela, latar polos, dan tunjukkan detail kain dari dekat.</p>
                <a href="<?= url('/penjual/produk/tambah') ?>" class="text-link">Unggah produk baru <?= icon('arrow-up-right') ?></a>
            </section>
        </aside>
    </div>
</section>
