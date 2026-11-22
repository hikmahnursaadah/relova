<?php
$activeFilterCount = count($filters['category']);
foreach (['q', 'type', 'size', 'condition'] as $key) {
    if (!empty($filters[$key])) {
        $activeFilterCount++;
    }
}
if ($filters['min_price'] !== '' || $filters['max_price'] !== '') {
    $activeFilterCount++;
}
$queryForLinks = $_GET;
unset($queryForLinks['halaman']);
$queryString = http_build_query($queryForLinks);
$pageBase = url('/jelajahi') . ($queryString ? '?' . $queryString . '&' : '?') . 'halaman=';
$currentSort = $filters['sort'] ?: 'terbaru';
?>
<section class="catalog-head">
    <div class="container catalog-head__inner">
        <div>
            <span class="eyebrow" data-reveal>Arsip pakaian bekas — <?= date('Y') ?></span>
            <h1 data-reveal><?= $seller ? 'Lemari ' . e($seller['name']) : 'Temukan <em>potongan</em><br>yang menunggumu.' ?></h1>
        </div>
        <div class="catalog-head__meta" data-reveal>
            <span><?= (int) $pagination['total'] ?> produk ditemukan</span>
            <p><?= $seller ? 'Semua produk aktif dari penjual ini.' : 'Setiap barang satu-satunya. Jika kamu menyukainya, jangan menunggu terlalu lama.' ?></p>
        </div>
    </div>
</section>

<section class="catalog-section">
    <div class="container catalog-layout">
        <aside class="filter-panel" data-filter-panel>
            <div class="filter-panel__head">
                <div>
                    <span class="eyebrow">Saring koleksi</span>
                    <strong>Penyaringan</strong>
                </div>
                <button type="button" class="icon-button filter-close" data-filter-close aria-label="Tutup penyaringan"><?= icon('close') ?></button>
            </div>
            <form action="<?= url('/jelajahi') ?>" method="get" class="filter-form">
                <input type="hidden" name="urutkan" value="<?= e($currentSort) ?>">
                <?php if ($filters['seller_id']): ?>
                    <input type="hidden" name="penjual" value="<?= (int) $filters['seller_id'] ?>">
                <?php endif; ?>
                <div class="filter-group">
                    <label class="filter-label" for="filter-q">Cari produk</label>
                    <div class="input-with-icon">
                        <?= icon('search') ?>
                        <input id="filter-q" type="search" name="q" value="<?= e($filters['q']) ?>" placeholder="Nama, merek, warna…">
                    </div>
                </div>

                <fieldset class="filter-group">
                    <legend class="filter-label">Kategori</legend>
                    <?php foreach ($categories as $category): ?>
                        <label class="check-row">
                            <input type="checkbox" name="kategori[]" value="<?= e($category['slug']) ?>" <?= in_array($category['slug'], $filters['category'], true) ? 'checked' : '' ?>>
                            <span><?= e($category['name']) ?></span>
                            <small><?= (int) $category['product_count'] ?></small>
                        </label>
                    <?php endforeach; ?>
                </fieldset>

                <div class="filter-group">
                    <label class="filter-label" for="filter-type">Jenis barang</label>
                    <select id="filter-type" name="jenis">
                        <option value="">Semua jenis</option>
                        <?php foreach (product_types() as $value => $label): ?>
                            <option value="<?= e($value) ?>" <?= $filters['type'] === $value ? 'selected' : '' ?>><?= e($label) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="filter-group">
                    <label class="filter-label" for="filter-size">Ukuran</label>
                    <select id="filter-size" name="ukuran">
                        <option value="">Semua ukuran</option>
                        <?php foreach (sizes() as $size): ?>
                            <option value="<?= e($size) ?>" <?= $filters['size'] === $size ? 'selected' : '' ?>><?= e($size) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <fieldset class="filter-group">
                    <legend class="filter-label">Kondisi</legend>
                    <label class="check-row check-row--radio">
                        <input type="radio" name="kondisi" value="" <?= $filters['condition'] === '' ? 'checked' : '' ?>>
                        <span>Semua kondisi</span>
                    </label>
                    <?php foreach (conditions() as $condition): ?>
                        <label class="check-row check-row--radio">
                            <input type="radio" name="kondisi" value="<?= e($condition) ?>" <?= $filters['condition'] === $condition ? 'checked' : '' ?>>
                            <span><?= e($condition) ?></span>
                        </label>
                    <?php endforeach; ?>
                </fieldset>

                <div class="filter-group">
                    <span class="filter-label">Rentang harga</span>
                    <div class="price-inputs">
                        <label>
                            <span>Min.</span>
                            <input type="number" name="min" value="<?= e($filters['min_price']) ?>" min="0" step="10000" placeholder="0">
                        </label>
                        <span class="price-inputs__dash">—</span>
                        <label>
                            <span>Maks.</span>
                            <input type="number" name="max" value="<?= e($filters['max_price']) ?>" min="0" step="10000" placeholder="1.000.000">
                        </label>
                    </div>
                </div>

                <div class="filter-actions">
                    <button type="submit" class="button button--dark button--full">Terapkan</button>
                    <a href="<?= url('/jelajahi' . ($filters['seller_id'] ? '?penjual=' . (int) $filters['seller_id'] : '')) ?>" class="text-link text-link--center">Atur ulang</a>
                </div>
            </form>
        </aside>
        <div class="filter-backdrop" data-filter-backdrop></div>

        <div class="catalog-main">
            <div class="catalog-toolbar">
                <button type="button" class="filter-trigger" data-filter-open><?= icon('filter') ?> Saring <?php if ($activeFilterCount): ?><span><?= $activeFilterCount ?></span><?php endif; ?></button>
                <p class="catalog-toolbar__count"><span><?= (int) $pagination['total'] ?></span> produk</p>
                <div class="catalog-sort">
                    <label for="sort-select">Urutkan</label>
                    <select id="sort-select" data-sort-select>
                        <option value="terbaru" <?= $currentSort === 'terbaru' ? 'selected' : '' ?>>Terbaru</option>
                        <option value="termurah" <?= $currentSort === 'termurah' ? 'selected' : '' ?>>Harga terendah</option>
                        <option value="termahal" <?= $currentSort === 'termahal' ? 'selected' : '' ?>>Harga tertinggi</option>
                        <option value="populer" <?= $currentSort === 'populer' ? 'selected' : '' ?>>Paling dilihat</option>
                        <option value="rating" <?= $currentSort === 'rating' ? 'selected' : '' ?>>Penilaian tertinggi</option>
                    </select>
                </div>
            </div>

            <?php if ($seller): ?>
                <div class="seller-filter-banner">
                    <span class="avatar avatar--sm" style="--avatar-color: <?= e(avatar_color($seller['name'])) ?>"><?= e(initials($seller['name'])) ?></span>
                    <div><strong>Menampilkan lemari <?= e($seller['name']) ?></strong><span><?= e($seller['city'] ?: 'Indonesia') ?> · <?= (int) $seller['active_products'] ?> produk aktif</span></div>
                    <a href="<?= url('/jelajahi') ?>" class="text-link">Lihat semua produk</a>
                </div>
            <?php endif; ?>

            <?php if ($products): ?>
                <div class="product-grid product-grid--catalog">
                    <?php foreach ($products as $product): ?>
                        <?php View::partial('components/product-card', ['product' => $product, 'wishlistIds' => $wishlistIds]); ?>
                    <?php endforeach; ?>
                </div>

                <?php if ($pagination['pages'] > 1): ?>
                    <nav class="pagination" aria-label="Navigasi halaman">
                        <?php if ($pagination['page'] > 1): ?>
                            <a href="<?= e($pageBase . ($pagination['page'] - 1)) ?>" class="pagination__arrow" aria-label="Halaman sebelumnya"><?= icon('chevron-left') ?></a>
                        <?php else: ?>
                            <span class="pagination__arrow is-disabled"><?= icon('chevron-left') ?></span>
                        <?php endif; ?>
                        <?php for ($i = 1; $i <= $pagination['pages']; $i++): ?>
                            <?php if ($i === 1 || $i === $pagination['pages'] || abs($i - $pagination['page']) <= 1): ?>
                                <a href="<?= e($pageBase . $i) ?>" class="pagination__number <?= $i === $pagination['page'] ? 'is-active' : '' ?>" <?= $i === $pagination['page'] ? 'aria-current="page"' : '' ?>><?= $i ?></a>
                            <?php elseif (abs($i - $pagination['page']) === 2): ?>
                                <span class="pagination__ellipsis">…</span>
                            <?php endif; ?>
                        <?php endfor; ?>
                        <?php if ($pagination['page'] < $pagination['pages']): ?>
                            <a href="<?= e($pageBase . ($pagination['page'] + 1)) ?>" class="pagination__arrow" aria-label="Halaman berikutnya"><?= icon('chevron-right') ?></a>
                        <?php else: ?>
                            <span class="pagination__arrow is-disabled"><?= icon('chevron-right') ?></span>
                        <?php endif; ?>
                    </nav>
                <?php endif; ?>
            <?php else: ?>
                <div class="empty-state">
                    <span class="empty-state__icon"><?= icon('hanger') ?></span>
                    <h2>Belum ada potongan yang cocok.</h2>
                    <p>Coba ubah kata kunci, rentang harga, atau kategori. Koleksi baru masuk setiap minggu.</p>
                    <a href="<?= url('/jelajahi') ?>" class="button button--dark">Lihat semua produk</a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</section>
