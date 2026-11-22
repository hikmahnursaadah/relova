<?php
$subtotal = array_sum(array_map(fn ($item) => (int) $item['price'], $items));
$user = Auth::user();
$defaultAddress = old('shipping_address', $user['address'] ?: '');
?>
<section class="dashboard-page checkout-page">
    <header class="dashboard-page__head">
        <div>
            <span class="eyebrow">Langkah terakhir</span>
            <h1>Konfirmasi <em>pesanan.</em></h1>
        </div>
        <a href="<?= url('/keranjang') ?>" class="text-link"><?= icon('arrow-left') ?> Kembali ke keranjang</a>
    </header>

    <form action="<?= url('/checkout') ?>" method="post" class="checkout-layout" data-checkout-form>
        <?= csrf_field() ?>
        <div class="checkout-main">
            <section class="checkout-block">
                <div class="checkout-block__head">
                    <span class="checkout-step">01</span>
                    <div><h2>Alamat pengiriman</h2><p>Isi alamat lengkap agar penjual dapat menyiapkan pengiriman.</p></div>
                </div>
                <div class="field">
                    <label for="shipping-address">Alamat lengkap</label>
                    <textarea id="shipping-address" name="shipping_address" rows="4" placeholder="Nama jalan, nomor rumah, kelurahan, kecamatan, kota, kode pos" required minlength="15"><?= e($defaultAddress) ?></textarea>
                    <small class="field-help">Alamat ini hanya dibagikan kepada penjual terkait.</small>
                </div>
                <div class="field">
                    <label for="order-notes">Catatan untuk penjual <span class="optional">(opsional)</span></label>
                    <textarea id="order-notes" name="notes" rows="3" placeholder="Contoh: tolong tambahkan bubble wrap, atau konfirmasi ketersediaan sebelum dikirim."><?= e(old('notes')) ?></textarea>
                </div>
            </section>

            <section class="checkout-block">
                <div class="checkout-block__head">
                    <span class="checkout-step">02</span>
                    <div><h2>Ongkos kirim & pengiriman</h2><p>re:wear tidak mengintegrasikan layanan pengiriman otomatis. Sepakati ongkir dengan penjual melalui chat, lalu masukkan jumlahnya di bawah.</p></div>
                </div>
                <?php foreach ($groups as $sellerId => $group): ?>
                    <div class="shipping-group">
                        <header>
                            <span class="avatar avatar--xs" style="--avatar-color: <?= e(avatar_color($group['seller_name'])) ?>"><?= e(initials($group['seller_name'])) ?></span>
                            <div><strong><?= e($group['seller_name']) ?></strong><span><?= count($group['items']) ?> barang · <?= e($group['seller_city'] ?: 'Indonesia') ?></span></div>
                        </header>
                        <div class="shipping-group__items">
                            <?php foreach ($group['items'] as $item): ?>
                                <div class="mini-line">
                                    <span><?= e($item['title']) ?></span>
                                    <strong><?= rupiah($item['price']) ?></strong>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        <div class="field field--inline">
                            <label for="shipping-<?= $sellerId ?>">Ongkos kirim yang disepakati</label>
                            <div class="currency-input">
                                <span>Rp</span>
                                <input id="shipping-<?= $sellerId ?>" type="number" name="shipping[<?= $sellerId ?>]" value="<?= e(old('shipping_' . $sellerId, '0')) ?>" min="0" max="500000" step="1000" data-checkout-shipping>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
                <p class="checkout-hint"><?= icon('chat') ?> Belum sepakat ongkir? <a href="<?= url('/chat') ?>">Buka chat</a> dan tanyakan langsung kepada penjual sebelum mengirim pesanan.</p>
            </section>

            <section class="checkout-block">
                <div class="checkout-block__head">
                    <span class="checkout-step">03</span>
                    <div><h2>Tinjau barang</h2><p>Pastikan ukuran dan kondisi sudah sesuai sebelum melanjutkan.</p></div>
                </div>
                <div class="checkout-review">
                    <?php foreach ($items as $item): ?>
                        <article class="checkout-review__item">
                            <img src="<?= e(product_image($item['primary_image'])) ?>" alt="<?= e($item['title']) ?>">
                            <div><strong><?= e($item['title']) ?></strong><span>Ukuran <?= e($item['size']) ?> · <?= e($item['item_condition']) ?></span><small>Penjual <?= e($item['seller_name']) ?></small></div>
                            <strong><?= rupiah($item['price']) ?></strong>
                        </article>
                    <?php endforeach; ?>
                </div>
            </section>
        </div>

        <aside class="checkout-summary">
            <span class="eyebrow">Total pesanan</span>
            <div class="checkout-summary__line"><span>Subtotal barang</span><strong><?= rupiah($subtotal) ?></strong></div>
            <div class="checkout-summary__line"><span>Ongkos kirim</span><strong data-checkout-shipping-total>Rp 0</strong></div>
            <div class="checkout-summary__total"><span>Total</span><strong data-checkout-grand-total><?= rupiah($subtotal) ?></strong></div>
            <p class="checkout-summary__note">Total dapat berubah sesuai ongkir yang kamu isi. Pesanan akan masuk ke penjual dengan status <strong>Menunggu Konfirmasi</strong>.</p>
            <button type="submit" class="button button--dark button--full">Buat Pesanan <?= icon('arrow-right') ?></button>
            <div class="checkout-summary__method">
                <span><?= icon('credit-card') ?></span>
                <p><strong>Pembayaran tidak otomatis.</strong> Kamu dan penjual menyepakati metode pembayaran serta pengiriman di luar sistem.</p>
            </div>
            <div class="checkout-summary__secure"><?= icon('shield') ?> Data pesanan tersimpan dengan aman.</div>
        </aside>
    </form>
</section>
