<?php
$statusFlow = order_status_flow();
$currentIndex = array_search($order['status'], $statusFlow, true);
$isCancelled = $order['status'] === 'dibatalkan';
$subtotal = array_sum(array_map(fn ($item) => (int) $item['price'], $order['items']));
$layoutSeller = (int) $order['seller_id'] === Auth::id() && (int) $order['buyer_id'] !== Auth::id();
?>
<section class="dashboard-page order-detail-page">
    <header class="dashboard-page__head">
        <div>
            <span class="eyebrow">Detail pesanan</span>
            <h1><?= e($order['order_number']) ?></h1>
            <p class="dashboard-page__sub">Dibuat <?= e(tanggal($order['created_at'], true)) ?> · <?= count($order['items']) ?> barang</p>
        </div>
        <a href="<?= $layoutSeller ? url('/penjual/pesanan') : url('/pesanan') ?>" class="text-link"><?= icon('arrow-left') ?> Kembali</a>
    </header>

    <div class="order-detail-layout">
        <div class="order-detail-main">
            <section class="panel-card order-timeline-card">
                <div class="panel-card__head">
                    <div><span class="eyebrow">Status transaksi</span><h2><?= e(order_status_label($order['status'])) ?></h2></div>
                    <span class="status <?= e(order_status_class($order['status'])) ?>"><?= e(order_status_label($order['status'])) ?></span>
                </div>
                <?php if ($isCancelled): ?>
                    <div class="cancelled-note"><?= icon('alert') ?> Pesanan ini dibatalkan. Barang telah dikembalikan ke etalase penjual.</div>
                <?php else: ?>
                    <ol class="order-timeline">
                        <?php foreach ($statusFlow as $index => $status): ?>
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
                <p class="order-timeline__note">Status diperbarui oleh penjual. Hubungi penjual melalui pesan jika ada pertanyaan tentang pengiriman.</p>
            </section>

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

            <?php if ($canReview): ?>
                <section class="panel-card review-form-card" id="ulasan">
                    <div class="panel-card__head"><div><span class="eyebrow">Pengalamanmu</span><h2>Beri ulasan</h2></div></div>
                    <p>Ceritakan kondisi barang dan pengalaman transaksi. Ulasan hanya dapat dikirim sekali untuk setiap produk.</p>
                    <?php foreach ($order['items'] as $item): ?>
                        <?php if (!$item['review_id']): ?>
                            <form action="<?= url('/pesanan/' . $order['id'] . '/ulasan') ?>" method="post" class="review-form">
                                <?= csrf_field() ?>
                                <input type="hidden" name="product_id" value="<?= (int) $item['product_id'] ?>">
                                <div class="review-form__product">
                                    <img src="<?= e(product_image($item['product_image'])) ?>" alt="">
                                    <strong><?= e($item['product_title']) ?></strong>
                                </div>
                                <fieldset class="rating-input">
                                    <legend>Rating</legend>
                                    <div class="rating-input__stars">
                                        <?php for ($star = 5; $star >= 1; $star--): ?>
                                            <label><input type="radio" name="rating" value="<?= $star ?>" required><span><?= icon('star') ?><em><?= $star ?></em></span></label>
                                        <?php endfor; ?>
                                    </div>
                                </fieldset>
                                <div class="field">
                                    <label for="review-<?= (int) $item['id'] ?>">Ulasan</label>
                                    <textarea id="review-<?= (int) $item['id'] ?>" name="comment" rows="4" maxlength="600" placeholder="Bagaimana kondisi barang saat diterima?"></textarea>
                                </div>
                                <button type="submit" class="button button--dark">Kirim Ulasan <?= icon('arrow-right') ?></button>
                            </form>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </section>
            <?php endif; ?>
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
                <?php if (!$layoutSeller): ?>
                    <form action="<?= url('/chat/mulai') ?>" method="post" class="order-chat-form">
                        <?= csrf_field() ?>
                        <?php if (!empty($order['items'][0]['product_id'])): ?>
                            <input type="hidden" name="product_id" value="<?= (int) $order['items'][0]['product_id'] ?>">
                        <?php endif; ?>
                        <input type="hidden" name="message" value="Halo, saya ingin menanyakan pesanan <?= e($order['order_number']) ?>.">
                        <button type="submit" class="button button--ghost button--full"><?= icon('chat') ?> Tanya Penjual</button>
                    </form>
                <?php endif; ?>
                <?php if ($canCancel): ?>
                    <form action="<?= url('/pesanan/' . $order['id'] . '/batal') ?>" method="post" data-confirm="Batalkan pesanan ini? Penjual akan menerima pemberitahuan.">
                        <?= csrf_field() ?>
                        <button type="submit" class="button button--danger-outline button--full">Batalkan Pesanan</button>
                    </form>
                <?php endif; ?>
            </section>

            <section class="panel-card">
                <span class="eyebrow">Pengiriman</span>
                <h2 class="aside-card-title">Alamat tujuan</h2>
                <p class="address-text"><?= nl2br(e($order['shipping_address'])) ?></p>
                <?php if ($order['notes']): ?>
                    <div class="order-note"><span>Catatan pembeli</span><p><?= e($order['notes']) ?></p></div>
                <?php endif; ?>
                <p class="aside-note"><?= icon('info') ?> Pengiriman diatur langsung oleh penjual. Relova tidak menyediakan layanan logistik otomatis.</p>
            </section>

            <?php if ($order['status'] === 'selesai'): ?>
                <section class="panel-card panel-card--accent">
                    <span class="eyebrow">Terima kasih</span>
                    <h2 class="aside-card-title">Barang sudah di tanganmu.</h2>
                    <p>Semoga potongan ini menemani banyak cerita baru. Ulasanmu membantu komunitas memilih dengan lebih yakin.</p>
                </section>
            <?php endif; ?>
        </aside>
    </div>
</section>
