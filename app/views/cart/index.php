<?php
$subtotal = array_sum(array_map(fn ($item) => (int) $item['price'], $items));
$itemCount = count($items);
?>
<section class="dashboard-page">
    <header class="dashboard-page__head">
        <div>
            <span class="eyebrow">Pilihanmu</span>
            <h1>Keranjang <em>(<?= $itemCount ?>)</em></h1>
        </div>
        <a href="<?= url('/jelajahi') ?>" class="text-link">Lanjut menjelajah <?= icon('arrow-right') ?></a>
    </header>

    <?php if (!$items): ?>
        <div class="empty-state empty-state--panel">
            <span class="empty-state__icon"><?= icon('bag') ?></span>
            <h2>Keranjangmu masih lapang.</h2>
            <p>Barang bekas biasanya hanya ada satu. Temukan potongan yang tepat sebelum berpindah tangan.</p>
            <a href="<?= url('/jelajahi') ?>" class="button button--dark">Jelajahi Koleksi <?= icon('arrow-right') ?></a>
        </div>
    <?php else: ?>
        <div class="cart-layout">
            <div class="cart-list">
                <?php foreach ($groups as $group): ?>
                    <section class="cart-group">
                        <header class="cart-group__head">
                            <span class="avatar avatar--xs" style="--avatar-color: <?= e(avatar_color($group['seller_name'])) ?>"><?= e(initials($group['seller_name'])) ?></span>
                            <div><strong>Dikirim oleh <?= e($group['seller_name']) ?></strong><span><?= e($group['seller_city'] ?: 'Indonesia') ?></span></div>
                            <span class="cart-group__count"><?= count($group['items']) ?> barang</span>
                        </header>
                        <?php foreach ($group['items'] as $item): ?>
                            <article class="cart-item">
                                <a href="<?= url('/produk/' . $item['id']) ?>" class="cart-item__image">
                                    <img src="<?= e(product_image($item['primary_image'])) ?>" alt="<?= e($item['title']) ?>">
                                </a>
                                <div class="cart-item__copy">
                                    <span class="cart-item__meta"><?= e($item['category_name']) ?> · Ukuran <?= e($item['size']) ?></span>
                                    <h2><a href="<?= url('/produk/' . $item['id']) ?>"><?= e($item['title']) ?></a></h2>
                                    <span class="cart-item__condition"><?= e($item['item_condition']) ?></span>
                                </div>
                                <div class="cart-item__end">
                                    <strong><?= rupiah($item['price']) ?></strong>
                                    <form action="<?= url('/keranjang/hapus/' . $item['id']) ?>" method="post">
                                        <?= csrf_field() ?>
                                        <button type="submit" class="text-action text-action--muted"><?= icon('trash') ?> Hapus</button>
                                    </form>
                                </div>
                            </article>
                        <?php endforeach; ?>
                    </section>
                <?php endforeach; ?>

                <form action="<?= url('/keranjang/kosongkan') ?>" method="post" class="cart-clear-form">
                    <?= csrf_field() ?>
                    <button type="submit" class="text-link">Kosongkan keranjang</button>
                </form>
            </div>

            <aside class="cart-summary">
                <span class="eyebrow">Ringkasan</span>
                <h2>Subtotal</h2>
                <div class="cart-summary__row"><span>Harga barang (<?= $itemCount ?>)</span><strong><?= rupiah($subtotal) ?></strong></div>
                <div class="cart-summary__row"><span>Ongkos kirim</span><span class="muted">Disepakati dengan penjual</span></div>
                <div class="cart-summary__total"><span>Total sementara</span><strong><?= rupiah($subtotal) ?></strong></div>
                <p class="cart-summary__note"><?= icon('info') ?> Ongkir dan metode pengiriman akan kamu tentukan di langkah berikutnya berdasarkan kesepakatan dengan penjual.</p>
                <a href="<?= url('/checkout') ?>" class="button button--dark button--full">Lanjut ke Konfirmasi <?= icon('arrow-right') ?></a>
                <a href="<?= url('/jelajahi') ?>" class="text-link text-link--center">Tambah barang lain</a>
                <div class="cart-summary__secure"><?= icon('shield') ?> Transaksi tercatat di akunmu</div>
            </aside>
        </div>
    <?php endif; ?>
</section>
