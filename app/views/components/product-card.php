<?php
$wishlisted = in_array((int) $product['id'], array_map('intval', $wishlistIds ?? []), true);
$isSold = ($product['status'] ?? 'aktif') === 'terjual';
$cardClass = trim('product-card ' . ($class ?? '') . ($isSold ? ' is-sold' : ''));
?>
<article class="<?= e($cardClass) ?>" data-product-card>
    <div class="product-card__media">
        <a href="<?= url('/produk/' . $product['id']) ?>" class="product-card__image-link" aria-label="Lihat <?= e($product['title']) ?>">
            <img src="<?= e(product_image($product['primary_image'] ?? null)) ?>" alt="<?= e($product['title']) ?>" loading="lazy" decoding="async">
        </a>
        <div class="product-card__badges">
            <span class="pill pill--condition"><?= e($product['item_condition'] ?? $product['condition'] ?? 'Baik') ?></span>
            <?php if ($isSold): ?><span class="pill pill--sold">Terjual</span><?php endif; ?>
        </div>
        <?php if (!$isSold): ?>
            <form action="<?= url('/favorit/' . $product['id'] . '/toggle') ?>" method="post" class="product-card__wishlist" data-wishlist-form>
                <?= csrf_field() ?>
                <button type="submit" class="round-button <?= $wishlisted ? 'is-active' : '' ?>" aria-label="<?= $wishlisted ? 'Hapus dari favorit' : 'Simpan ke favorit' ?>" data-wishlist-button>
                    <?= icon('heart') ?>
                </button>
            </form>
            <form action="<?= url('/keranjang/tambah/' . $product['id']) ?>" method="post" class="product-card__cart" data-cart-form>
                <?= csrf_field() ?>
                <button type="submit" class="round-button round-button--dark" aria-label="Tambahkan <?= e($product['title']) ?> ke keranjang">
                    <?= icon('plus') ?>
                </button>
            </form>
        <?php endif; ?>
    </div>
    <div class="product-card__body">
        <div class="product-card__seller">
            <span class="avatar avatar--xs" style="--avatar-color: <?= e(avatar_color($product['seller_name'])) ?>"><?= e(initials($product['seller_name'])) ?></span>
            <span><?= e($product['seller_name']) ?></span>
            <span class="dot-separator"></span>
            <span><?= e($product['seller_city'] ?: 'Indonesia') ?></span>
        </div>
        <h3 class="product-card__title"><a href="<?= url('/produk/' . $product['id']) ?>"><?= e($product['title']) ?></a></h3>
        <div class="product-card__meta">
            <span>Ukuran <?= e($product['size']) ?></span>
            <span><?= e($product['brand'] ?: 'Preloved') ?></span>
        </div>
        <div class="product-card__foot">
            <strong class="product-card__price"><?= rupiah($product['price']) ?></strong>
            <?php if (($product['review_count'] ?? 0) > 0): ?>
                <span class="product-card__rating"><?= icon('star') ?> <?= number_format((float) $product['rating_avg'], 1, ',', '.') ?></span>
            <?php else: ?>
                <span class="product-card__rating product-card__rating--muted">Baru</span>
            <?php endif; ?>
        </div>
    </div>
</article>
