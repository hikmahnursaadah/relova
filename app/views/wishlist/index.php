<?php $count = count($items); ?>
<section class="dashboard-page">
    <header class="dashboard-page__head">
        <div>
            <span class="eyebrow">Disimpan untuk nanti</span>
            <h1>Favorit <em>(<?= $count ?>)</em></h1>
            <p class="dashboard-page__sub">Barang bekas biasanya hanya satu. Simpan yang kamu pertimbangkan, lalu kembali sebelum berpindah tangan.</p>
        </div>
        <a href="<?= url('/jelajahi') ?>" class="text-link">Temukan lebih banyak <?= icon('arrow-right') ?></a>
    </header>

    <?php if ($items): ?>
        <div class="wishlist-toolbar">
            <span><?= $count ?> potongan tersimpan</span>
            <span class="wishlist-toolbar__note"><?= icon('info') ?> Harga dan ketersediaan dapat berubah kapan saja.</span>
        </div>
        <div class="product-grid product-grid--catalog">
            <?php foreach ($items as $item): ?>
                <?php View::partial('components/product-card', ['product' => $item, 'wishlistIds' => $wishlistIds]); ?>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <div class="empty-state empty-state--panel">
            <span class="empty-state__icon"><?= icon('heart') ?></span>
            <h2>Belum ada yang kamu simpan.</h2>
            <p>Saat menemukan potongan yang membuatmu berhenti sebentar, tekan ikon hati. Daftarnya akan menunggu di sini.</p>
            <a href="<?= url('/jelajahi') ?>" class="button button--dark">Jelajahi Koleksi <?= icon('arrow-right') ?></a>
        </div>
    <?php endif; ?>
</section>
