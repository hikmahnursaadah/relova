<?php
$flow = order_status_flow();
$currentIndex = array_search($order['status'], $flow, true);
$isCancelled = $order['status'] === 'dibatalkan';
$subtotal = array_sum(array_map(fn ($item) => (int) $item['price'], $order['items']));
$transitions = [
    'menunggu' => ['dikonfirmasi' => 'Konfirmasi Pesanan', 'dibatalkan' => 'Batalkan Pesanan'],
    'dikonfirmasi' => ['diproses' => 'Tandai Sedang Diproses', 'dibatalkan' => 'Batalkan Pesanan'],
    'diproses' => ['dikirim' => 'Tandai Sudah Dikirim', 'dibatalkan' => 'Batalkan Pesanan'],
    'dikirim' => ['selesai' => 'Tandai Selesai'],
    'selesai' => [],
    'dibatalkan' => [],
];
$allowed = $transitions[$order['status']] ?? [];
?>
<section class="dashboard-page order-detail-page">
    <header class="dashboard-page__head">
        <div>
            <span class="eyebrow">Kelola pesanan</span>
            <h1><?= e($order['order_number']) ?></h1>
            <p class="dashboard-page__sub">Dibuat <?= e(tanggal($order['created_at'], true)) ?> · <?= count($order['items']) ?> barang</p>
        </div>
        <a href="<?= url('/penjual/pesanan') ?>" class="text-link"><?= icon('arrow-left') ?> Kembali ke pesanan</a>
    </header>

    <div class="order-detail-layout">
        <div class="order-detail-main">
            <section class="panel-card order-timeline-card">
                <div class="panel-card__head">
                    <div><span class="eyebrow">Status transaksi</span><h2><?= e(order_status_label($order['status'])) ?></h2></div>
                    <span class="status <?= e(order_status_class($order['status'])) ?>"><?= e(order_status_label($order['status'])) ?></span>
                </div>
                <?php if ($isCancelled): ?>
                    <div class="cancelled-note"><?= icon('alert') ?> Pesanan ini dibatalkan. Produk telah dikembalikan ke etalase jika masih tersedia.</div>
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
                <p class="order-timeline__note">Perbarui status hanya setelah langkah sebelumnya benar-benar dilakukan. Pembeli menerima notifikasi setiap perubahan.</p>
            </section>

            <?php if ($allowed): ?>
                <section class="panel-card status-action-card">
                    <div class="panel-card__head">
                        <div><span class="eyebrow">Langkah berikutnya</span><h2>Perbarui status</h2></div>
                    </div>
                    <p>Pastikan kamu sudah berkomunikasi dengan pembeli sebelum mengubah status.</p>
                    <div class="status-action-card__buttons">
                        <?php foreach ($allowed as $status => $label): ?>
                            <form action="<?= url('/penjual/pesanan/' . $order['id'] . '/status') ?>" method="post" <?= $status === 'dibatalkan' ? 'data-confirm="Batalkan pesanan ini? Produk akan kembali ke etalase."' : '' ?>>
                                <?= csrf_field() ?>
                                <input type="hidden" name="status" value="<?= e($status) ?>">
                                <button type="submit" class="button <?= $status === 'dibatalkan' ? 'button--danger-outline' : 'button--dark' ?>"><?= e($label) ?> <?= $status === 'dibatalkan' ? '' : icon('arrow-right') ?></button>
                            </form>
                        <?php endforeach; ?>
                    </div>
                </section>
            <?php endif; ?>

            <section class="panel-card">
                <div class="panel-card__head"><div><span class="eyebrow">Barang</span><h2>Isi pesanan</h2></div></div>
                <div class="order-items">
                    <?php foreach ($order['items'] as $item): ?>
                        <article class="order-item">
                            <a href="<?= $item['product_slug'] ? url('/produk/' . $item['product_id']) : '#' ?>" class="order-item__image">
                                <img src="<?= e(product_image($item['product_image'])) ?>" alt="<?= e($item['product_title']) ?>">
                            </a>
                            <div class="order-item__info">
                                <h3><?= e($item['product_title']) ?></h3>
                                <span>Harga satuan <?= rupiah($item['price']) ?></span>
                                <?php if ($item['review_id']): ?><span class="reviewed-tag"><?= icon('check') ?> Sudah diulas</span><?php endif; ?>
                            </div>
                            <strong><?= rupiah($item['price']) ?></strong>
                        </article>
                    <?php endforeach; ?>
                </div>
            </section>
        </div>

        <aside class="order-detail-aside">
            <section class="panel-card">
                <span class="eyebrow">Ringkasan</span>
                <div class="order-total">
                    <div><span>Subtotal barang</span><strong><?= rupiah($subtotal) ?></strong></div>
                    <div><span>Ongkos kirim</span><strong><?= rupiah($order['shipping_cost']) ?></strong></div>
                    <div class="order-total__grand"><span>Total</span><strong><?= rupiah($order['total_amount']) ?></strong></div>
                </div>
                <div class="order-parties">
                    <div><span>Pembeli</span><strong><?= e($order['buyer_name']) ?></strong><small><?= e($order['buyer_city'] ?: 'Indonesia') ?></small></div>
                    <div><span>Penjual</span><strong><?= e($order['seller_name']) ?></strong><small><?= e($order['seller_city'] ?: 'Indonesia') ?></small></div>
                </div>
                <form action="<?= url('/chat/mulai') ?>" method="post" class="order-chat-form">
                    <?= csrf_field() ?>
                    <?php if (!empty($order['items'][0]['product_id'])): ?>
                        <input type="hidden" name="product_id" value="<?= (int) $order['items'][0]['product_id'] ?>">
                    <?php endif; ?>
                    <input type="hidden" name="message" value="Halo, saya ingin membahas pesanan <?= e($order['order_number']) ?>.">
                    <button type="submit" class="button button--ghost button--full"><?= icon('chat') ?> Tanya Pembeli</button>
                </form>
            </section>

            <section class="panel-card">
                <span class="eyebrow">Pengiriman</span>
                <h2 class="aside-card-title">Alamat tujuan</h2>
                <p class="address-text"><?= nl2br(e($order['shipping_address'])) ?></p>
                <?php if ($order['notes']): ?>
                    <div class="order-note"><span>Catatan pembeli</span><p><?= e($order['notes']) ?></p></div>
                <?php endif; ?>
                <div class="contact-card">
                    <span><?= icon('user') ?> <?= e($order['buyer_name']) ?></span>
                    <?php if ($order['buyer_phone']): ?><a href="tel:<?= e(preg_replace('/\s+/', '', $order['buyer_phone'])) ?>"><?= icon('phone') ?> <?= e($order['buyer_phone']) ?></a><?php endif; ?>
                    <a href="mailto:<?= e($order['buyer_email']) ?>"><?= icon('mail') ?> <?= e($order['buyer_email']) ?></a>
                </div>
                <p class="aside-note"><?= icon('info') ?> Hubungi pembeli melalui pesan jika ada perubahan alamat atau ongkir.</p>
            </section>

            <section class="panel-card panel-card--accent">
                <span class="eyebrow">Pengingat</span>
                <h2 class="aside-card-title">Kemas dengan hati.</h2>
                <p>Bersihkan barang sekali lagi, lipat rapi, dan sertakan catatan kecil. Pengalaman yang baik sering berubah menjadi ulasan yang hangat.</p>
            </section>
        </aside>
    </div>
</section>
