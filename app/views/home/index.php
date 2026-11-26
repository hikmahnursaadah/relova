<?php
$featuredTop = array_slice($featured, 0, 4);
$featuredRest = array_slice($featured, 4, 4);
$styleClasses = ['style-card--wide', 'style-card--tall', 'style-card--medium', 'style-card--medium', 'style-card--wide'];
$categoryCopy = [
    'atasan' => 'Kemeja, kaos, blus, dan rajutan dengan cerita baru.',
    'bawahan' => 'Denim, rok, dan celana dengan siluet yang tetap relevan.',
    'outerwear' => 'Jaket dan mantel untuk hari yang lebih dingin dan berani.',
    'dress' => 'Gaun dengan jatuh kain yang indah untuk banyak kesempatan.',
    'sepatu' => 'Dari sneakers harian sampai sepatu kulit terawat.',
];
$liveStats = $liveStats ?? ['products' => 0, 'sellers' => 0, 'cities' => 0];
?>
<section class="hero" data-hero>
    <div class="hero__shell">
        <div class="hero__card">
            <div class="hero__grid">
                <div class="hero__copy">
                    <span class="hero__badge" data-hero-reveal>
                        <span class="hero__badge-icon"><?= icon('heart') ?></span>
                        Kurasi edisi 02
                    </span>
                    <h1 class="hero__title" data-hero-title>
                        <span>Setiap Gaya</span>
                        <span>Punya <em>Cerita</em></span>
                        <span>Kedua.</span>
                    </h1>
                    <p class="hero__description" data-hero-reveal>
                        Temukan pakaian dan sepatu bekas pilihan, atau beri kesempatan baru pada barang yang sudah tidak kamu gunakan. Setiap produk dikurasi dengan teliti dan siap menemani cerita berikutnya.
                    </p>
                    <div class="hero__proof" data-hero-reveal>
                        <div class="hero__stat">
                            <strong data-count="<?= (int) $liveStats['products'] ?>"><?= number_format((int) $liveStats['products'], 0, ',', '.') ?></strong>
                            <span>produk aktif</span>
                        </div>
                        <div class="hero__stat">
                            <strong data-count="<?= (int) $liveStats['sellers'] ?>"><?= number_format((int) $liveStats['sellers'], 0, ',', '.') ?></strong>
                            <span>penjual terkurasi</span>
                        </div>
                        <p class="hero__proof-note">Setiap produk diperiksa.<br>Setiap cerita dijaga.</p>
                    </div>
                </div>

                <figure class="hero__media" data-hero-reveal>
                    <img src="<?= asset('images/hero/relova-hero.jpg') ?>" alt="Model berambut pirang mengenakan blazer hitam dari koleksi Relova" width="1200" height="1800" fetchpriority="high" decoding="async">
                    <figcaption class="hero__media-caption">
                        <span>Koleksi 02</span>
                        <span>Bandung — Jakarta</span>
                    </figcaption>
                </figure>
            </div>

            <div class="hero__footer" data-hero-reveal>
                <a href="<?= url('/jelajahi') ?>" class="hero__cta">Jelajahi Koleksi <?= icon('arrow-up-right') ?></a>
                <a href="<?= url('/jual') ?>" class="hero__cta-secondary">Mulai Menjual <?= icon('arrow-right') ?></a>
            </div>
        </div>
    </div>
</section>

<div class="marquee-band" aria-hidden="true">
    <div class="marquee-band__track">
        <span>Kurasi manual</span><span class="marquee-band__icon">✳</span>
        <span>Foto asli penjual</span><span class="marquee-band__icon">✳</span>
        <span>Pakaian terawat</span><span class="marquee-band__icon">✳</span>
        <span>Cerita yang berlanjut</span><span class="marquee-band__icon">✳</span>
        <span>Kurasi manual</span><span class="marquee-band__icon">✳</span>
        <span>Foto asli penjual</span><span class="marquee-band__icon">✳</span>
        <span>Pakaian terawat</span><span class="marquee-band__icon">✳</span>
        <span>Cerita yang berlanjut</span><span class="marquee-band__icon">✳</span>
    </div>
</div>

<section class="style-section section" id="gaya" data-reveal-section>
    <div class="container">
        <header class="section-heading">
            <div>
                <span class="eyebrow" data-reveal>Kurasi berdasarkan karakter</span>
                <h2 data-reveal>Jelajahi <em>Berdasarkan Gaya</em></h2>
            </div>
            <p class="section-heading__copy" data-reveal>Temukan pakaian dan sepatu bekas terawat yang sesuai dengan karakter dan gaya kamu. Setiap potong dipilih untuk tetap relevan, bukan sekadar bertahan.</p>
        </header>

        <div class="style-grid">
            <?php foreach ($categories as $index => $category): ?>
                <article class="style-card <?= e($styleClasses[$index] ?? '') ?>" data-reveal-card>
                    <a href="<?= url('/jelajahi?kategori[]=' . $category['slug']) ?>" class="style-card__link">
                        <div class="style-card__media">
                            <img src="<?= e(media_url($category['image'])) ?>" alt="<?= e($category['name']) ?>" loading="lazy" decoding="async">
                            <span class="style-card__index"><?= str_pad((string) ($index + 1), 2, '0', STR_PAD_LEFT) ?></span>
                            <span class="style-card__count"><?= (int) $category['product_count'] ?> produk</span>
                        </div>
                        <div class="style-card__body">
                            <div>
                                <h3><?= e($category['name']) ?></h3>
                                <p><?= e($categoryCopy[$category['slug']] ?? $category['description']) ?></p>
                            </div>
                            <span class="style-card__arrow"><?= icon('arrow-up-right') ?></span>
                        </div>
                    </a>
                </article>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<section class="featured-section section section--flush-top" data-reveal-section>
    <div class="container">
        <header class="section-heading section-heading--split">
            <div>
                <span class="eyebrow" data-reveal>Dari lemari mereka</span>
                <h2 data-reveal>Sedang <em>dicari.</em></h2>
            </div>
            <div class="section-heading__aside" data-reveal>
                <p>Pilihan paling banyak disimpan minggu ini. Satu barang, satu kesempatan.</p>
                <a href="<?= url('/jelajahi') ?>" class="text-link">Lihat semua koleksi <?= icon('arrow-right') ?></a>
            </div>
        </header>

        <div class="product-grid product-grid--featured">
            <?php foreach ($featuredTop as $product): ?>
                <?php View::partial('components/product-card', ['product' => $product, 'wishlistIds' => $wishlistIds]); ?>
            <?php endforeach; ?>
        </div>

        <div class="featured-split">
            <div class="featured-split__note" data-reveal>
                <span class="featured-split__number"><?= (int) $liveStats['products'] ?></span>
                <p>produk aktif<br>dari lemari terkurasi</p>
                <span class="featured-split__line"></span>
                <a href="<?= url('/jelajahi?urutkan=terbaru') ?>">Lihat yang baru masuk <?= icon('arrow-up-right') ?></a>
            </div>
            <div class="product-grid product-grid--three">
                <?php foreach ($featuredRest as $product): ?>
                    <?php View::partial('components/product-card', ['product' => $product, 'wishlistIds' => $wishlistIds]); ?>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</section>

<section class="editorial-section section" data-reveal-section>
    <div class="container editorial-section__grid">
        <div class="editorial-section__media" data-reveal>
            <div class="editorial-frame">
                <img src="<?= asset('images/products/rak-netral.jpg') ?>" alt="Deretan pakaian netral yang siap diberi cerita baru" loading="lazy">
                <span class="editorial-frame__caption">Arsip 02 — warna yang tidak lekang</span>
            </div>
            <span class="editorial-stamp"><?= icon('recycle') ?> dipilih dengan teliti</span>
        </div>
        <div class="editorial-section__copy">
            <span class="eyebrow" data-reveal>Filosofi Relova</span>
            <h2 data-reveal>Bukan sekadar <em>bekas.</em></h2>
            <p class="editorial-section__lead" data-reveal>Setiap pakaian yang sampai di Relova melewati cerita, pemeriksaan, dan perawatan. Kami percaya kualitas tidak berhenti pada pemilik pertama.</p>
            <div class="process-list" data-reveal>
                <div class="process-item">
                    <span>01</span>
                    <div>
                        <h3>Dikurasi, bukan sekadar diunggah</h3>
                        <p>Penjual menjelaskan kondisi dan setiap cacat kecil secara jujur. Foto diambil dari barang yang sebenarnya.</p>
                    </div>
                </div>
                <div class="process-item">
                    <span>02</span>
                    <div>
                        <h3>Dirawat sebelum berpindah tangan</h3>
                        <p>Barang dicuci, disikat, atau dibersihkan sesuai bahan sebelum siap dikirim ke pemilik berikutnya.</p>
                    </div>
                </div>
                <div class="process-item">
                    <span>03</span>
                    <div>
                        <h3>Cerita berlanjut bersamamu</h3>
                        <p>Setelah transaksi selesai, kamu bisa memberi ulasan dan membantu pembeli lain menemukan barang yang tepat.</p>
                    </div>
                </div>
            </div>
            <blockquote class="editorial-quote" data-reveal>
                “Cara paling indah merawat pakaian adalah memakainya lebih lama.”
                <cite>— Catatan kurator Relova</cite>
            </blockquote>
        </div>
    </div>
</section>

<section class="impact-section" data-reveal-section>
    <div class="container">
        <div class="impact-section__head">
            <span class="eyebrow" data-reveal>Jejak yang berlanjut</span>
            <p data-reveal>Angka kecil dari keputusan besar: memilih yang sudah ada daripada membeli yang baru.</p>
        </div>
        <div class="impact-grid">
            <div class="impact-stat" data-reveal>
                <strong data-count="<?= (int) $liveStats['products'] ?>"><?= number_format((int) $liveStats['products'], 0, ',', '.') ?></strong>
                <span>pakaian dan sepatu<br>sedang ditawarkan</span>
            </div>
            <div class="impact-stat" data-reveal>
                <strong data-count="<?= (int) $liveStats['sellers'] ?>"><?= number_format((int) $liveStats['sellers'], 0, ',', '.') ?></strong>
                <span>penjual dengan<br>etalase aktif</span>
            </div>
            <div class="impact-stat" data-reveal>
                <strong data-count="<?= (int) $liveStats['cities'] ?>"><?= number_format((int) $liveStats['cities'], 0, ',', '.') ?></strong>
                <span>kota asal penjual<br>yang terhubung</span>
            </div>
            <div class="impact-stat impact-stat--text" data-reveal>
                <strong>1 : 1</strong>
                <span>setiap barang yang dijual<br>adalah satu yang tetap dipakai</span>
            </div>
        </div>
    </div>
</section>

<section class="sell-cta section" data-reveal-section>
    <div class="container">
        <div class="sell-cta__inner">
            <div class="sell-cta__copy">
                <span class="eyebrow eyebrow--light" data-reveal>Untuk lemarimu</span>
                <h2 data-reveal>Ada cerita yang<br>belum <em>selesai.</em></h2>
                <p data-reveal>Pakaian yang sudah tidak kamu pakai mungkin sedang menunggu pemilik berikutnya. Jual dengan cara yang jujur, mudah, dan tetap manusiawi.</p>
                <div class="sell-cta__actions" data-reveal>
                    <a href="<?= Auth::isSeller() ? url('/penjual/produk/tambah') : url('/jual') ?>" class="button button--light"><?= Auth::isSeller() ? 'Tambah Produk' : 'Mulai Menjual' ?> <?= icon('arrow-up-right') ?></a>
                    <a href="<?= url('/tentang') ?>" class="text-link text-link--light">Kenali cara kerja Relova <?= icon('arrow-right') ?></a>
                </div>
            </div>
            <div class="sell-cta__visual" data-reveal>
                <img src="<?= asset('images/products/flatlay-putih.jpg') ?>" alt="Kemeja dan celana denim yang ditata untuk dijual kembali" loading="lazy">
                <div class="sell-cta__tag">
                    <span class="clothing-tag__icon"><?= icon('hanger') ?></span>
                    <span>Jual barangmu<br><small>dalam 3 menit</small></span>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="journal-strip section" data-reveal-section>
    <div class="container journal-strip__grid">
        <div class="journal-strip__intro" data-reveal>
            <span class="eyebrow">Baru masuk</span>
            <h2>Tiga cerita<br><em>terbaru.</em></h2>
            <a href="<?= url('/jelajahi?urutkan=terbaru') ?>" class="text-link">Telusuri arsip <?= icon('arrow-right') ?></a>
        </div>
        <?php foreach ($newest as $product): ?>
            <?php View::partial('components/product-card', ['product' => $product, 'wishlistIds' => $wishlistIds, 'class' => 'product-card--compact']); ?>
        <?php endforeach; ?>
    </div>
</section>
