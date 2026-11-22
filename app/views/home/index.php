<?php
$featuredTop = array_slice($featured, 0, 4);
$featuredRest = array_slice($featured, 4, 4);
$styleClasses = ['style-card--wide', 'style-card--tall', 'style-card--medium', 'style-card--medium', 'style-card--wide'];
$categoryCopy = [
    'atasan' => 'Kemeja, kaos, blus, dan rajutan dengan cerita baru.',
    'bawahan' => 'Denim, rok, dan celana dengan siluet yang tetap relevan.',
    'outerwear' => 'Lapisan luar untuk hari yang lebih dingin dan berani.',
    'sepatu' => 'Dari sneakers harian sampai sepatu kulit terawat.',
    'aksesori' => 'Kurasi aksesori sedang kami siapkan. Untuk sekarang, temukan pakaian dan sepatu pilihan.',
];
?>
<section class="hero" data-hero>
    <div class="hero__noise" aria-hidden="true"></div>
    <div class="hero__grid">
        <div class="hero__copy">
            <div class="hero__eyebrow" data-hero-reveal>
                <span class="eyebrow">Preloved fashion — kurasi vol. 02</span>
                <span class="hero__edition">Bandung / Jakarta</span>
            </div>
            <h1 class="hero__title" data-hero-title>
                <span>Setiap Gaya</span>
                <span>Punya <em>Cerita</em></span>
                <span class="hero__title-last"><em>Kedua.</em><span class="hero__title-mark">(02)</span></span>
            </h1>
            <p class="hero__description" data-hero-reveal>
                Temukan fashion preloved pilihan atau beri kesempatan baru pada pakaian yang sudah tidak kamu gunakan. Dikurasi dengan teliti, siap dipakai untuk cerita berikutnya.
            </p>
            <div class="hero__actions" data-hero-reveal>
                <a href="<?= url('/jelajahi') ?>" class="button button--dark">Jelajahi Koleksi <?= icon('arrow-up-right') ?></a>
                <a href="<?= url('/jual') ?>" class="button button--ghost">Mulai Menjual <?= icon('arrow-right') ?></a>
            </div>
            <div class="hero__footnotes" data-hero-reveal>
                <div class="hero__stat">
                    <strong><?= e($settings['impact_items'] ?? '1284') ?><span>+</span></strong>
                    <small>pakaian terselamatkan</small>
                </div>
                <div class="hero__stat">
                    <strong><?= e($settings['impact_cities'] ?? '38') ?></strong>
                    <small>kota di Indonesia</small>
                </div>
                <p class="hero__note">Setiap produk diperiksa.<br>Setiap cerita dijaga.</p>
            </div>
        </div>

        <div class="hero__visual" data-mannequin-area>
            <div class="hero__arch" aria-hidden="true">
                <span class="hero__arch-label">THE PRELOVED EDIT / 2026</span>
                <span class="hero__arch-index">N° 02</span>
            </div>
            <div class="hero__stage">
                <div class="mannequin-canvas" data-mannequin data-mode="hero" aria-label="Manekin 3D interaktif yang dapat digerakkan dengan kursor"></div>
                <div class="mannequin-fallback" data-mannequin-fallback aria-hidden="true">
                    <svg viewBox="0 0 260 620" role="img" aria-label="Ilustrasi manekin mengenakan pakaian preloved">
                        <defs>
                            <linearGradient id="bodyIvory" x1="0" y1="0" x2="1" y2="1">
                                <stop offset="0" stop-color="#faf6ee"/>
                                <stop offset=".55" stop-color="#e9e1d3"/>
                                <stop offset="1" stop-color="#d7cdbd"/>
                            </linearGradient>
                            <linearGradient id="topSage" x1="0" y1="0" x2="1" y2="1">
                                <stop offset="0" stop-color="#7c8067"/>
                                <stop offset="1" stop-color="#5f6450"/>
                            </linearGradient>
                        </defs>
                        <ellipse cx="132" cy="595" rx="62" ry="10" fill="#1d1c18" opacity=".12"/>
                        <path d="M130 42c-18 0-30 14-30 34 0 18 12 33 30 33s30-15 30-33c0-20-12-34-30-34Z" fill="url(#bodyIvory)"/>
                        <path d="M116 104h28l7 24h-42l7-24Z" fill="url(#bodyIvory)"/>
                        <path d="M95 131c-9 1-17 6-22 15l-18 55c-2 8 4 14 11 15 6 1 10-3 12-9l13-37-6 66h92l-6-66 13 37c2 6 6 10 12 9 7-1 13-7 11-15l-18-55c-5-9-13-14-22-15l-21-7H116l-21 7Z" fill="url(#topSage)"/>
                        <path d="M83 179h97l-8 110H90l-7-110Z" fill="url(#topSage)"/>
                        <path d="M88 289h87l13 106-14 73h-30l2-98-10-1-10 1 2 98h-30l-14-73 14-106Z" fill="#a7aab0"/>
                        <path d="M93 474h28l4 64-5 23H94l-6-24 5-63Zm48 0h28l5 63 5 24h-27l-5-23 4-64Z" fill="url(#bodyIvory)"/>
                        <path d="M88 559c-4 8 0 18 9 20h27c6 0 9-4 8-10l-3-9H88Zm52 0 3 10c1 6 4 10 10 10h20c7 0 11-6 9-12l-5-10-37 2Z" fill="#ebe3d6"/>
                        <path d="M63 198c-7 4-10 12-8 19l9 62c1 7 6 10 11 9 6-1 9-6 8-12l-9-58 8-8-19-12Zm134 0c7 4 10 12 8 19l-9 62c-1 7-6 10-11 9-6-1-9-6-8-12l9-58-8-8 19-12Z" fill="url(#bodyIvory)"/>
                        <path d="M70 289h19v13H70zM172 289h19v13h-19z" fill="#6a6e58"/>
                        <path d="M97 151c9 6 18 9 34 9s26-3 34-9" fill="none" stroke="#505443" stroke-width="2" opacity=".5"/>
                        <path d="M90 290h84" fill="none" stroke="#4f5360" stroke-width="3"/>
                        <path d="M107 397h11M144 397h11" stroke="#7c7f86" stroke-width="1.5" stroke-dasharray="3 4"/>
                        <path d="M124 42c0-7 3-11 7-11s7 4 7 11" fill="none" stroke="#cfc5b5" stroke-width="2"/>
                        <circle cx="131" cy="28" r="2.3" fill="#c25a3e"/>
                    </svg>
                    <span class="mannequin-fallback__caption">Tampilan studio sedang dimuat</span>
                </div>

                <div class="hero__visual-tag hero__visual-tag--top">
                    <span class="hero__visual-dot"></span>
                    <span>Manekin interaktif</span>
                </div>
                <div class="hero__visual-tag hero__visual-tag--side">
                    <span>Gerakkan kursor</span>
                    <?= icon('rotate') ?>
                </div>

                <div class="outfit-panel" data-outfit-panel>
                    <div class="outfit-panel__head">
                        <span class="eyebrow">Gaya aktif</span>
                        <span class="outfit-panel__counter" data-outfit-counter>01 / 05</span>
                    </div>
                    <div class="outfit-panel__name-row">
                        <strong data-outfit-name>Kasual</strong>
                        <button type="button" class="outfit-play" data-outfit-play aria-label="Hentikan pergantian outfit otomatis">
                            <span data-outfit-play-icon><?= icon('pause') ?></span>
                        </button>
                    </div>
                    <div class="outfit-dots" data-outfit-dots>
                        <button type="button" class="outfit-dot is-active" data-outfit-index="0" aria-label="Gaya Kasual"><span>01</span></button>
                        <button type="button" class="outfit-dot" data-outfit-index="1" aria-label="Gaya Vintage"><span>02</span></button>
                        <button type="button" class="outfit-dot" data-outfit-index="2" aria-label="Gaya Streetwear"><span>03</span></button>
                        <button type="button" class="outfit-dot" data-outfit-index="3" aria-label="Gaya Minimalis"><span>04</span></button>
                        <button type="button" class="outfit-dot" data-outfit-index="4" aria-label="Gaya Outerwear"><span>05</span></button>
                    </div>
                    <div class="outfit-progress"><span data-outfit-progress></span></div>
                </div>
            </div>
            <span class="hero__vertical-note">SCROLL TO DISCOVER</span>
        </div>
    </div>
    <div class="hero__bottom">
        <a href="#gaya" class="scroll-cue" data-scroll-cue>
            <span class="scroll-cue__line"></span>
            <span>Turun untuk menjelajah</span>
        </a>
        <p class="hero__manifesto">Bukan sekadar bekas.<br><em>Ini babak baru.</em></p>
        <div class="hero__coordinates">
            <span>6°54' S / 107°36' E</span>
            <span>Koleksi 02 — 2026</span>
        </div>
    </div>
</section>

<div class="marquee-band" aria-hidden="true">
    <div class="marquee-band__track">
        <span>Kurasi manual</span><span class="marquee-band__icon">✳</span>
        <span>Foto asli penjual</span><span class="marquee-band__icon">✳</span>
        <span>Preloved terawat</span><span class="marquee-band__icon">✳</span>
        <span>Cerita yang berlanjut</span><span class="marquee-band__icon">✳</span>
        <span>Kurasi manual</span><span class="marquee-band__icon">✳</span>
        <span>Foto asli penjual</span><span class="marquee-band__icon">✳</span>
        <span>Preloved terawat</span><span class="marquee-band__icon">✳</span>
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
            <p class="section-heading__copy" data-reveal>Temukan pilihan fashion preloved yang sesuai dengan karakter dan gaya kamu. Setiap potong dipilih untuk tetap relevan, bukan sekadar bertahan.</p>
        </header>

        <div class="style-grid">
            <?php foreach ($categories as $index => $category): ?>
                <?php $isSoon = $category['slug'] === 'aksesori'; ?>
                <article class="style-card <?= e($styleClasses[$index] ?? '') ?> <?= $isSoon ? 'style-card--soon' : '' ?>" data-reveal-card>
                    <a href="<?= $isSoon ? url('/jelajahi') : url('/jelajahi?kategori[]=' . $category['slug']) ?>" class="style-card__link">
                        <div class="style-card__media">
                            <img src="<?= e(media_url($category['image'])) ?>" alt="<?= e($category['name']) ?>" loading="lazy" decoding="async">
                            <span class="style-card__index"><?= str_pad((string) ($index + 1), 2, '0', STR_PAD_LEFT) ?></span>
                            <?php if ($isSoon): ?>
                                <span class="style-card__badge">Segera</span>
                            <?php else: ?>
                                <span class="style-card__count"><?= (int) $category['product_count'] ?> produk</span>
                            <?php endif; ?>
                        </div>
                        <div class="style-card__body">
                            <div>
                                <h3><?= e($category['name']) ?></h3>
                                <p><?= e($categoryCopy[$category['slug']] ?? $category['description']) ?></p>
                            </div>
                            <span class="style-card__arrow"><?= $isSoon ? icon('sparkle') : icon('arrow-up-right') ?></span>
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
                <span class="featured-split__number"><?= count($featured) + count($newest) ?></span>
                <p>potongan pilihan<br>dari lemari terkurasi</p>
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
            <span class="editorial-stamp"><?= icon('recycle') ?> selected with care</span>
        </div>
        <div class="editorial-section__copy">
            <span class="eyebrow" data-reveal>Filosofi re:wear</span>
            <h2 data-reveal>Bukan sekadar <em>bekas.</em></h2>
            <p class="editorial-section__lead" data-reveal>Setiap pakaian yang sampai di re:wear melewati cerita, pemeriksaan, dan perawatan. Kami percaya kualitas tidak berhenti pada pemilik pertama.</p>
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
                “Cara paling indah merawat fashion adalah memakainya lebih lama.”
                <cite>— Catatan kurator re:wear</cite>
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
                <strong data-count="<?= (int) ($settings['impact_items'] ?? 1284) ?>"><?= e($settings['impact_items'] ?? '1.284') ?></strong>
                <span>pakaian & sepatu<br>mendapat rumah baru</span>
            </div>
            <div class="impact-stat" data-reveal>
                <strong data-count="<?= (int) ($settings['impact_sellers'] ?? 326) ?>"><?= e($settings['impact_sellers'] ?? '326') ?></strong>
                <span>penjual aktif<br>di seluruh Indonesia</span>
            </div>
            <div class="impact-stat" data-reveal>
                <strong data-count="<?= (int) ($settings['impact_cities'] ?? 38) ?>"><?= e($settings['impact_cities'] ?? '38') ?></strong>
                <span>kota terhubung<br>oleh cerita fashion</span>
            </div>
            <div class="impact-stat impact-stat--text" data-reveal>
                <strong>1 : 1</strong>
                <span>setiap barang yang terjual<br>adalah satu yang tidak dibuang</span>
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
                    <a href="<?= url('/tentang') ?>" class="text-link text-link--light">Kenali cara kerja re:wear <?= icon('arrow-right') ?></a>
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
