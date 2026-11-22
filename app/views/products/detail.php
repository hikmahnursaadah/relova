<?php
$galleryImages = $images;
if (!$galleryImages) {
    $galleryImages = [['image_path' => $product['primary_image'], 'id' => 0]];
}
$productUrl = url('/produk/' . $product['id']);
$isSold = $product['status'] === 'terjual';
$isAvailable = $product['status'] === 'aktif' && !$isOwner && !$isAdmin;
$loginNext = urlencode('/produk/' . $product['id']);
$ratingAvg = (float) ($reviewStats['average'] ?? 0);
$reviewTotal = (int) ($reviewStats['total'] ?? 0);
?>
<section class="product-page">
    <div class="container">
        <nav class="breadcrumbs" aria-label="Remah roti">
            <a href="<?= url('/') ?>">Beranda</a>
            <?= icon('chevron-right') ?>
            <a href="<?= url('/jelajahi') ?>">Jelajahi</a>
            <?= icon('chevron-right') ?>
            <a href="<?= url('/jelajahi?kategori[]=' . $product['category_slug']) ?>"><?= e($product['category_name']) ?></a>
            <?= icon('chevron-right') ?>
            <span><?= e($product['title']) ?></span>
        </nav>

        <div class="product-detail-grid">
            <div class="product-gallery" data-gallery>
                <div class="product-gallery__main">
                    <img src="<?= e(product_image($galleryImages[0]['image_path'])) ?>" alt="<?= e($product['title']) ?>" data-gallery-main>
                    <?php if ($isSold): ?><span class="product-gallery__sold">Sudah menemukan rumah baru</span><?php endif; ?>
                    <span class="product-gallery__label">Foto barang sebenarnya</span>
                </div>
                <?php if (count($galleryImages) > 1): ?>
                    <div class="product-gallery__thumbs">
                        <?php foreach ($galleryImages as $index => $image): ?>
                            <button type="button" class="product-gallery__thumb <?= $index === 0 ? 'is-active' : '' ?>" data-gallery-thumb data-src="<?= e(product_image($image['image_path'])) ?>" aria-label="Lihat foto <?= $index + 1 ?>">
                                <img src="<?= e(product_image($image['image_path'])) ?>" alt="" loading="lazy">
                            </button>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>

            <div class="product-info">
                <div class="product-info__top">
                    <a class="product-info__seller" href="<?= url('/jelajahi?penjual=' . $product['seller_id']) ?>">
                        <span class="avatar avatar--xs" style="--avatar-color: <?= e(avatar_color($product['seller_name'])) ?>"><?= e(initials($product['seller_name'])) ?></span>
                        <span>Dari lemari <strong><?= e($product['seller_name']) ?></strong></span>
                    </a>
                    <button type="button" class="product-report-link" data-report-open><?= icon('alert') ?> Laporkan produk</button>
                </div>

                <h1 class="product-info__title"><?= e($product['title']) ?></h1>
                <div class="product-info__price-row">
                    <strong class="product-info__price"><?= rupiah($product['price']) ?></strong>
                    <?php if ($reviewTotal > 0): ?>
                        <a href="#ulasan" class="product-info__rating"><?= star_markup($ratingAvg) ?> <span><?= number_format($ratingAvg, 1, ',', '.') ?> (<?= $reviewTotal ?>)</span></a>
                    <?php else: ?>
                        <span class="product-info__rating product-info__rating--muted">Belum ada ulasan</span>
                    <?php endif; ?>
                </div>

                <div class="product-info__tags">
                    <span class="pill pill--condition">Kondisi <?= e($product['item_condition']) ?></span>
                    <span class="pill">Ukuran <?= e($product['size']) ?></span>
                    <?php if ($product['brand']): ?><span class="pill"><?= e($product['brand']) ?></span><?php endif; ?>
                    <?php if ($product['color']): ?><span class="pill"><?= e($product['color']) ?></span><?php endif; ?>
                </div>

                <div class="product-info__desc">
                    <h2 class="sr-only">Deskripsi produk</h2>
                    <p><?= nl2br(e($product['description'])) ?></p>
                </div>

                <div class="defect-note">
                    <span class="defect-note__icon"><?= icon('info') ?></span>
                    <div>
                        <strong>Catatan kondisi</strong>
                        <p><?= e($product['defect'] ?: 'Penjual belum menambahkan catatan cacat. Tanyakan detail melalui pesan.') ?></p>
                    </div>
                </div>

                <?php if ($isOwner): ?>
                    <div class="owner-panel">
                        <div>
                            <span class="eyebrow">Produk milikmu</span>
                            <strong>Status: <?= e(status_produk_label($product['status'])) ?></strong>
                            <p>Kelola foto, harga, atau hapus produk dari etalase.</p>
                        </div>
                        <div class="owner-panel__actions">
                            <a href="<?= url('/penjual/produk/' . $product['id'] . '/edit') ?>" class="button button--dark">Ubah Produk <?= icon('edit') ?></a>
                            <form action="<?= url('/penjual/produk/' . $product['id'] . '/hapus') ?>" method="post" data-confirm="Hapus produk ini dari etalase? Tindakan ini tidak dapat dibatalkan.">
                                <?= csrf_field() ?>
                                <button type="submit" class="button button--danger-outline">Hapus</button>
                            </form>
                        </div>
                    </div>
                <?php elseif ($isAdmin): ?>
                    <div class="owner-panel">
                        <div><span class="eyebrow">Mode administrator</span><strong>Tinjau produk ini di panel moderasi.</strong></div>
                        <a href="<?= url('/admin/produk?q=' . urlencode($product['title'])) ?>" class="button button--dark">Buka Moderasi <?= icon('arrow-up-right') ?></a>
                    </div>
                <?php elseif ($isSold): ?>
                    <div class="sold-panel">
                        <span><?= icon('heart') ?></span>
                        <div><strong>Produk ini sudah terjual.</strong><p>Temukan potongan lain yang mungkin cocok untukmu.</p></div>
                        <a href="<?= url('/jelajahi') ?>" class="button button--dark">Jelajahi Lainnya</a>
                    </div>
                <?php else: ?>
                    <div class="product-actions">
                        <?php if (Auth::check()): ?>
                            <form action="<?= url('/keranjang/tambah/' . $product['id']) ?>" method="post" class="product-actions__cart">
                                <?= csrf_field() ?>
                                <button type="submit" class="button button--ghost button--full"><?= icon('bag') ?> Tambah ke Keranjang</button>
                            </form>
                            <form action="<?= url('/keranjang/tambah/' . $product['id']) ?>" method="post" class="product-actions__buy">
                                <?= csrf_field() ?>
                                <input type="hidden" name="mode" value="checkout">
                                <button type="submit" class="button button--dark button--full">Pesan Sekarang <?= icon('arrow-right') ?></button>
                            </form>
                        <?php else: ?>
                            <a href="<?= url('/masuk?next=' . $loginNext) ?>" class="button button--ghost button--full"><?= icon('bag') ?> Masuk untuk Membeli</a>
                            <a href="<?= url('/daftar') ?>" class="button button--dark button--full">Buat Akun <?= icon('arrow-right') ?></a>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>

                <div class="product-info__secondary-actions">
                    <?php if (Auth::check()): ?>
                        <form action="<?= url('/favorit/' . $product['id'] . '/toggle') ?>" method="post" data-wishlist-form>
                            <?= csrf_field() ?>
                            <button type="submit" class="text-action <?= $wishlisted ? 'is-active' : '' ?>" data-wishlist-button>
                                <?= icon('heart') ?> <span><?= $wishlisted ? 'Tersimpan di favorit' : 'Simpan ke favorit' ?></span>
                            </button>
                        </form>
                    <?php else: ?>
                        <a href="<?= url('/masuk?next=' . $loginNext) ?>" class="text-action"><?= icon('heart') ?> <span>Simpan ke favorit</span></a>
                    <?php endif; ?>
                    <span class="product-info__views"><?= icon('eye') ?> <?= (int) $product['view_count'] ?> kali dilihat</span>
                </div>

                <div class="transaction-note">
                    <span><?= icon('shield') ?></span>
                    <p><strong>Transaksi langsung dengan penjual.</strong> Ongkos kirim dan metode pengiriman disepakati melalui pesan. re:wear mencatat pesanan dan status transaksi tanpa memproses pembayaran otomatis.</p>
                </div>
            </div>
        </div>

        <div class="product-support-grid">
            <div class="product-specs">
                <span class="eyebrow">Detail barang</span>
                <h2>Yang perlu kamu tahu.</h2>
                <dl class="spec-list">
                    <div><dt>Kategori</dt><dd><?= e($product['category_name']) ?></dd></div>
                    <div><dt>Jenis</dt><dd><?= e(product_types()[$product['type']] ?? $product['type']) ?></dd></div>
                    <div><dt>Ukuran</dt><dd><?= e($product['size']) ?></dd></div>
                    <div><dt>Kondisi</dt><dd><?= e($product['item_condition']) ?></dd></div>
                    <div><dt>Merek</dt><dd><?= e($product['brand'] ?: 'Tidak disebutkan') ?></dd></div>
                    <div><dt>Warna</dt><dd><?= e($product['color'] ?: 'Tidak disebutkan') ?></dd></div>
                    <div><dt>Ditayangkan</dt><dd><?= e(tanggal($product['created_at'])) ?></dd></div>
                </dl>
                <p class="product-specs__disclaimer">Barang bekas dapat memiliki tanda pemakaian halus yang tidak selalu tertangkap kamera. Tanyakan melalui pesan bila perlu.</p>
            </div>

            <aside class="seller-card">
                <span class="eyebrow">Tentang penjual</span>
                <div class="seller-card__profile">
                    <span class="avatar avatar--lg" style="--avatar-color: <?= e(avatar_color($product['seller_name'])) ?>"><?= e(initials($product['seller_name'])) ?></span>
                    <div>
                        <h2><?= e($product['seller_name']) ?></h2>
                        <span><?= e($product['seller_city'] ?: 'Indonesia') ?></span>
                    </div>
                </div>
                <p class="seller-card__bio"><?= e($seller['bio'] ?? 'Penjual pakaian bekas terkurasi di re:wear.') ?></p>
                <div class="seller-card__stats">
                    <div><strong><?= (int) ($seller['active_products'] ?? 0) ?></strong><span>produk aktif</span></div>
                    <div><strong><?= (int) ($seller['completed_sales'] ?? 0) ?></strong><span>terjual</span></div>
                    <div><strong><?= number_format((float) ($seller['rating_avg'] ?? 0), 1, ',', '.') ?></strong><span>rating</span></div>
                </div>
                <?php if (Auth::check() && !$isOwner): ?>
                    <form action="<?= url('/chat/mulai') ?>" method="post" class="seller-chat-form">
                        <?= csrf_field() ?>
                        <input type="hidden" name="product_id" value="<?= (int) $product['id'] ?>">
                        <label for="chat-message">Tanya penjual</label>
                        <div class="seller-chat-form__row">
                            <input id="chat-message" type="text" name="message" placeholder="Halo, apakah masih tersedia?" maxlength="1000">
                            <button type="submit" class="round-button round-button--dark" aria-label="Kirim pesan"><?= icon('arrow-right') ?></button>
                        </div>
                    </form>
                <?php elseif (!Auth::check()): ?>
                    <a href="<?= url('/masuk?next=' . $loginNext) ?>" class="button button--ghost button--full"><?= icon('chat') ?> Masuk untuk Bertanya</a>
                <?php endif; ?>
                <a href="<?= url('/jelajahi?penjual=' . $product['seller_id']) ?>" class="text-link">Lihat semua produk penjual <?= icon('arrow-up-right') ?></a>
                <?php if ($seller && $seller['created_at']): ?>
                    <span class="seller-card__since">Bergabung sejak <?= e(tanggal($seller['created_at'])) ?></span>
                <?php endif; ?>
            </aside>
        </div>

        <section class="reviews-section" id="ulasan">
            <header class="reviews-head">
                <div>
                    <span class="eyebrow">Ulasan pembeli</span>
                    <h2>Kata mereka<br><em>yang sudah memiliki.</em></h2>
                </div>
                <div class="reviews-summary">
                    <strong><?= number_format($ratingAvg, 1, ',', '.') ?></strong>
                    <?= star_markup($ratingAvg) ?>
                    <span><?= $reviewTotal ?> ulasan terverifikasi</span>
                </div>
            </header>
            <?php if ($reviews): ?>
                <div class="reviews-grid">
                    <?php foreach ($reviews as $review): ?>
                        <article class="review-card">
                            <div class="review-card__head">
                                <span class="avatar avatar--sm" style="--avatar-color: <?= e(avatar_color($review['buyer_name'])) ?>"><?= e(initials($review['buyer_name'])) ?></span>
                                <div><strong><?= e($review['buyer_name']) ?></strong><span><?= e($review['buyer_city'] ?: 'Indonesia') ?> · <?= e(tanggal($review['created_at'])) ?></span></div>
                                <?= star_markup((int) $review['rating']) ?>
                            </div>
                            <p><?= e($review['comment'] ?: 'Transaksi berjalan baik dan barang sesuai deskripsi.') ?></p>
                        </article>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="reviews-empty">
                    <span><?= icon('star') ?></span>
                    <p>Belum ada ulasan untuk produk ini. Ulasan hanya dapat diberikan oleh pembeli setelah transaksi selesai.</p>
                </div>
            <?php endif; ?>
        </section>

        <?php if ($related): ?>
            <section class="related-section">
                <header class="section-heading section-heading--compact">
                    <div><span class="eyebrow">Mungkin cocok</span><h2>Lanjutkan <em>mencari.</em></h2></div>
                    <a href="<?= url('/jelajahi') ?>" class="text-link">Semua koleksi <?= icon('arrow-right') ?></a>
                </header>
                <div class="product-grid product-grid--four">
                    <?php foreach ($related as $item): ?>
                        <?php View::partial('components/product-card', ['product' => $item, 'wishlistIds' => $wishlistIds]); ?>
                    <?php endforeach; ?>
                </div>
            </section>
        <?php endif; ?>
    </div>
</section>

<?php if (Auth::check()): ?>
<dialog class="report-dialog" data-report-dialog aria-labelledby="report-title">
    <form action="<?= url('/produk/' . $product['id'] . '/laporan') ?>" method="post">
        <?= csrf_field() ?>
        <div class="report-dialog__head">
            <div><span class="eyebrow">Laporkan produk</span><h2 id="report-title">Ada yang tidak sesuai?</h2></div>
            <button type="button" class="icon-button" data-report-close aria-label="Tutup"><?= icon('close') ?></button>
        </div>
        <p class="report-dialog__lead">Tim moderasi akan meninjau laporanmu. Kamu tidak akan terlihat sebagai pelapor.</p>
        <fieldset class="field">
            <legend>Alasan laporan</legend>
            <?php foreach (['Produk diduga tiruan', 'Foto tidak sesuai', 'Produk tidak layak pakai', 'Konten tidak pantas', 'Lainnya'] as $reason): ?>
                <label class="check-row check-row--radio">
                    <input type="radio" name="reason" value="<?= e($reason) ?>" required>
                    <span><?= e($reason) ?></span>
                </label>
            <?php endforeach; ?>
        </fieldset>
        <div class="field">
            <label for="report-details">Detail tambahan</label>
            <textarea id="report-details" name="details" rows="4" maxlength="500" placeholder="Jelaskan hal yang kamu temukan…"></textarea>
        </div>
        <div class="report-dialog__actions">
            <button type="button" class="button button--ghost" data-report-close>Batal</button>
            <button type="submit" class="button button--dark">Kirim Laporan</button>
        </div>
    </form>
</dialog>
<?php endif; ?>
