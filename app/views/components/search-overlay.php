<div class="search-overlay" data-search-overlay aria-hidden="true">
    <div class="search-overlay__scrim" data-search-close></div>
    <div class="search-overlay__panel" role="dialog" aria-modal="true" aria-labelledby="search-title">
        <div class="search-overlay__head">
            <span class="eyebrow">Cari di re:wear</span>
            <button type="button" class="icon-button" data-search-close aria-label="Tutup pencarian"><?= icon('close') ?></button>
        </div>
        <h2 id="search-title" class="search-overlay__title">Apa yang sedang kamu cari?</h2>
        <form action="<?= url('/jelajahi') ?>" method="get" class="search-overlay__form" role="search">
            <label class="sr-only" for="search-overlay-input">Kata kunci produk</label>
            <?= icon('search') ?>
            <input id="search-overlay-input" type="search" name="q" placeholder="Kemeja linen, sneakers, jaket…" autocomplete="off" data-search-input>
            <button type="submit" class="button button--dark">Cari</button>
        </form>
        <div class="search-overlay__quick">
            <span>Pencarian populer</span>
            <div class="chip-row">
                <a class="chip" href="<?= url('/jelajahi?q=denim') ?>">Denim</a>
                <a class="chip" href="<?= url('/jelajahi?q=rajut') ?>">Rajut</a>
                <a class="chip" href="<?= url('/jelajahi?q=jaket') ?>">Jaket</a>
                <a class="chip" href="<?= url('/jelajahi?kategori[]=sepatu') ?>">Sepatu</a>
                <a class="chip" href="<?= url('/jelajahi?q=linen') ?>">Linen</a>
            </div>
        </div>
        <div class="search-overlay__foot">
            <span><?= icon('shield') ?> Semua transaksi aman dan melalui akun terverifikasi.</span>
        </div>
    </div>
</div>
