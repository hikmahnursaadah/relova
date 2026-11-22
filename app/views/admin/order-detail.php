<?php
$flow = order_status_flow();
$currentIndex = array_search($order['status'], $flow, true);
$isCancelled = $order['status'] === 'dibatalkan';
$subtotal = array_sum(array_map(fn ($item) => (int) $item['price'], $order['items']));
?>
<section class="admin-page order-detail-page">
    <header class="admin-page__head">
        <div>
            <span class="eyebrow">Detail transaksi</span>
            <h1><?= e($order['order_number']) ?></h1>
            <p>Dibuat <?= e(tanggal($order['created_at'], true)) ?> · diperbarui <?= e(tanggal($order['updated_at'], true)) ?></p>
        </div>
        <a href="<?= url('/admin/transaksi') ?>" class="admin-link"><?= icon('arrow-left') ?> Semua transaksi</a>
    </header>

    <div class="order-detail-layout">
        <div class="order-detail-main">
            <section class="admin-panel order-timeline-card">
                <div class="admin-panel__head">
                    <div><span class="eyebrow">Status</span><h2><?= e(order_status_label($order['status'])) ?></h2></div>
                    <span class="status <?= e(order_status_class($order['status'])) ?>"><?= e(order_status_label($order['status'])) ?></span>
                </div>
                <?php if ($isCancelled): ?>
                    <div class="cancelled-note"><?= icon('alert') ?> Transaksi dibatalkan. Produk dikembalikan ke etalase jika memungkinkan.</div>
                <?php else: ?>
                    <ol class="order-timeline">
                        <?php foreach ($flow as $index => $status): ?>
                            <?php
                            $state = 'is-upcoming';
                            if ($currentIndex !== false) {
                                if ($index < $currentIndex) {
                                    $state = 'is-done';
                                } elseif ($index === $currentIndex) {
                                    $state = 'is-current';
                                }
                            }
                            ?>
                            <li class="<?= $state ?>">
                                <span class="order-timeline__dot"><?= $index < $currentIndex ? icon('check') : ($index + 1) ?></span>
                                <span class="order-timeline__label"><?= e(order_status_label($status)) ?></span>
                            </li>
                        <?php endforeach; ?>
                    </ol>
                <?php endif; ?>
            </section>

            <section class="admin-panel">
                <div class="admin-panel__head"><div><span class="eyebrow">Barang</span><h2>Isi transaksi</h2></div></div>
                <div class="order-items">
                    <?php foreach ($order['items'] as $item): ?>
                        <article class="order-item">
                            <a href="<?= $item['product_slug'] ? url('/produk/' . $item['product_id']) : '#' ?>" class="order-item__image">
                                <img src="<?= e(product_image($item['product_image'])) ?>" alt="<?= e($item['product_title']) ?>">
                            </a>
                            <div class="order-item__info"><h3><?= e($item['product_title']) ?></h3><span>Harga satuan <?= rupiah($item['price']) ?></span></div>
                            <strong><?= rupiah($item['price']) ?></strong>
                        </article>
                    <?php endforeach; ?>
                </div>
            </section>

            <section class="admin-panel">
                <div class="admin-panel__head"><div><span class="eyebrow">Catatan</span><h2>Alamat & pesan pembeli</h2></div></div>
                <p class="address-text"><?= nl2br(e($order['shipping_address'])) ?></p>
                <?php if ($order['notes']): ?><div class="order-note"><span>Catatan pembeli</span><p><?= e($order['notes']) ?></p></div><?php endif; ?>
            </section>
        </div>

        <aside class="order-detail-aside">
            <section class="admin-panel">
                <span class="eyebrow">Pihak transaksi</span>
                <div class="admin-party">
                    <div class="admin-party__head"><span class="avatar avatar--sm" style="--avatar-color: <?= e(avatar_color($order['buyer_name'])) ?>"><?= e(initials($order['buyer_name'])) ?></span><div><span>Pembeli</span><strong><?= e($order['buyer_name']) ?></strong></div></div>
                    <a href="mailto:<?= e($order['buyer_email']) ?>"><?= icon('mail') ?> <?= e($order['buyer_email']) ?></a>
                    <?php if ($order['buyer_phone']): ?><a href="tel:<?= e(preg_replace('/\s+/', '', $order['buyer_phone'])) ?>"><?= icon('phone') ?> <?= e($order['buyer_phone']) ?></a><?php endif; ?>
                    <span><?= icon('map-pin') ?> <?= e($order['buyer_city'] ?: 'Indonesia') ?></span>
                </div>
                <div class="admin-party">
                    <div class="admin-party__head"><span class="avatar avatar--sm" style="--avatar-color: <?= e(avatar_color($order['seller_name'])) ?>"><?= e(initials($order['seller_name'])) ?></span><div><span>Penjual</span><strong><?= e($order['seller_name']) ?></strong></div></div>
                    <a href="mailto:<?= e($order['seller_email']) ?>"><?= icon('mail') ?> <?= e($order['seller_email']) ?></a>
                    <?php if ($order['seller_phone']): ?><a href="tel:<?= e(preg_replace('/\s+/', '', $order['seller_phone'])) ?>"><?= icon('phone') ?> <?= e($order['seller_phone']) ?></a><?php endif; ?>
                    <span><?= icon('map-pin') ?> <?= e($order['seller_city'] ?: 'Indonesia') ?></span>
                </div>
            </section>

            <section class="admin-panel">
                <span class="eyebrow">Ringkasan</span>
                <div class="order-total">
                    <div><span>Subtotal barang</span><strong><?= rupiah($subtotal) ?></strong></div>
                    <div><span>Ongkos kirim</span><strong><?= rupiah($order['shipping_cost']) ?></strong></div>
                    <div class="order-total__grand"><span>Total</span><strong><?= rupiah($order['total_amount']) ?></strong></div>
                </div>
            </section>

            <section class="admin-panel">
                <span class="eyebrow">Tindakan admin</span>
                <h2 class="aside-card-title">Ubah status</h2>
                <p class="aside-note">Gunakan hanya jika penjual atau pembeli membutuhkan bantuan penyelesaian.</p>
                <form action="<?= url('/admin/transaksi/' . $order['id'] . '/status') ?>" method="post" class="form-stack" data-confirm="Ubah status transaksi ini? Pembeli dan penjual akan menerima notifikasi.">
                    <?= csrf_field() ?>
                    <div class="field">
                        <label for="admin-order-status">Status baru</label>
                        <select id="admin-order-status" name="status">
                            <?php foreach (order_statuses() as $value => $label): ?>
                                <option value="<?= e($value) ?>" <?= $order['status'] === $value ? 'selected' : '' ?>><?= e($label) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <button type="submit" class="button button--dark button--full">Simpan Status</button>
                </form>
            </section>
        </aside>
    </div>
</section>
