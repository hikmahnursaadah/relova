<?php
$featuredSellers = $sellers;
$liveStats = $liveStats ?? ['sellers' => 0, 'cities' => 0];
?>
<section class="about-hero section">
    <div class="container about-hero__grid">
        <div class="about-hero__copy">
            <span class="eyebrow" data-reveal>Tentang re:wear</span>
            <h1 data-reveal>Pakaian yang <em>bertahan,</em><br>bukan yang berhenti.</h1>
            <p class="about-hero__lead" data-reveal>re:wear lahir dari keyakinan sederhana: pakaian bagus tidak seharusnya berakhir di lemari. Kami menghubungkan orang yang siap melepas dengan orang yang siap memberi cerita baru.</p>
            <div class="about-hero__meta" data-reveal>
                <div><strong>2026</strong><span>mulai dikurasi</span></div>
                <div><strong><?= number_format((int) ($liveStats['sellers'] ?? 0), 0, ',', '.') ?></strong><span>penjual terhubung</span></div>
                <div><strong><?= number_format((int) ($liveStats['cities'] ?? 0), 0, ',', '.') ?></strong><span>kota terjangkau</span></div>
            </div>
        </div>
        <div class="about-hero__media" data-reveal>
            <img src="<?= asset('images/products/gaun-merah.jpg') ?>" alt="Gaun satin merah dengan gerakan kain yang lembut" loading="lazy">
            <span class="about-hero__caption">Gerak, bukan sekadar barang.</span>
        </div>
    </div>
</section>

<section class="about-manifesto section section--flush-top">
    <div class="container">
        <div class="about-manifesto__line" data-reveal></div>
        <div class="about-manifesto__grid">
            <span class="eyebrow" data-reveal>Yang kami percaya</span>
            <div class="about-manifesto__copy">
                <p data-reveal>Mode bergerak cepat. Kualitas tidak harus ikut menghilang.</p>
                <p class="about-manifesto__small" data-reveal>Kami memilih pakaian dan sepatu yang masih layak dipakai, memastikan kondisinya jujur, dan mempertemukan pemilik lama dengan pemilik baru melalui percakapan yang manusiawi. Tidak ada lelang otomatis, tidak ada janji berlebihan — hanya transaksi yang jelas.</p>
            </div>
        </div>
    </div>
</section>

<section class="about-values section" data-reveal-section>
    <div class="container">
        <header class="section-heading">
            <div>
                <span class="eyebrow" data-reveal>Tiga prinsip</span>
                <h2 data-reveal>Kurasi yang <em>bertanggung jawab.</em></h2>
            </div>
        </header>
        <div class="value-grid">
            <article class="value-card" data-reveal>
                <span class="value-card__number">01</span>
                <span class="value-card__icon"><?= icon('shield') ?></span>
                <h3>Jujur pada kondisi</h3>
                <p>Setiap cacat kecil dicatat. Foto harus mewakili barang yang sebenarnya, bukan versi yang dipercantik.</p>
            </article>
            <article class="value-card" data-reveal>
                <span class="value-card__number">02</span>
                <span class="value-card__icon"><?= icon('recycle') ?></span>
                <h3>Memperpanjang pemakaian</h3>
                <p>Membeli pakaian bekas berarti mengurangi limbah tekstil dan memberi pakaian kesempatan untuk terus dipakai.</p>
            </article>
            <article class="value-card" data-reveal>
                <span class="value-card__number">03</span>
                <span class="value-card__icon"><?= icon('chat') ?></span>
                <h3>Manusia yang terhubung</h3>
                <p>Pembeli dan penjual berbicara langsung melalui pesan. Harga, ongkir, dan rincian pengiriman disepakati bersama.</p>
            </article>
        </div>
    </div>
</section>

<section class="about-process section" data-reveal-section>
    <div class="container about-process__grid">
        <div class="about-process__media" data-reveal>
            <img src="<?= asset('images/products/rak-netral.jpg') ?>" alt="Pakaian warna netral tergantung rapi" loading="lazy">
            <span class="about-process__label">Rak kurasi / Bandung</span>
        </div>
        <div class="about-process__copy">
            <span class="eyebrow" data-reveal>Bagaimana re:wear bekerja</span>
            <h2 data-reveal>Dari satu lemari<br>ke <em>lemari lain.</em></h2>
            <ol class="about-steps">
                <li data-reveal><span>01</span><div><strong>Penjual mengunggah</strong><p>Lengkapi kondisi, ukuran, harga, dan foto barang dari beberapa sisi.</p></div></li>
                <li data-reveal><span>02</span><div><strong>Pembeli menemukan</strong><p>Gunakan pencarian dan filter untuk menemukan potongan yang sesuai gaya dan ukuran.</p></div></li>
                <li data-reveal><span>03</span><div><strong>Bicara langsung</strong><p>Tanyakan detail, sepakati ongkir, lalu buat pesanan melalui akunmu.</p></div></li>
                <li data-reveal><span>04</span><div><strong>Cerita berlanjut</strong><p>Setelah barang diterima, beri ulasan dan bantu pembeli berikutnya.</p></div></li>
            </ol>
        </div>
    </div>
</section>

<section class="about-sellers section" data-reveal-section>
    <div class="container">
        <header class="section-heading section-heading--split">
            <div>
                <span class="eyebrow" data-reveal>Orang di balik barang</span>
                <h2 data-reveal>Penjual yang <em>merawat cerita.</em></h2>
            </div>
            <p class="section-heading__copy" data-reveal>Mereka adalah pembeli, penjual, dan penikmat mode yang sama. Kenali beberapa suara dari komunitas re:wear.</p>
        </header>
        <div class="seller-profile-grid">
            <?php foreach ($featuredSellers as $seller): ?>
                <article class="seller-profile" data-reveal>
                    <span class="avatar avatar--lg" style="--avatar-color: <?= e(avatar_color($seller['name'])) ?>"><?= e(initials($seller['name'])) ?></span>
                    <div>
                        <h3><?= e($seller['name']) ?></h3>
                        <span><?= e($seller['city'] ?: 'Indonesia') ?> · <?= (int) ($seller['product_count'] ?? 0) ?> produk</span>
                    </div>
                    <p>“<?= e($seller['bio'] ?: 'Menjual pilihan pakaian bekas terawat yang masih layak dipakai.') ?>”</p>
                    <a href="<?= url('/jelajahi?penjual=' . $seller['id']) ?>" class="text-link">Lihat lemari <?= icon('arrow-up-right') ?></a>
                </article>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<?php if ($reviews): ?>
<section class="about-reviews section" data-reveal-section>
    <div class="container about-reviews__grid">
        <div>
            <span class="eyebrow">Dari pembeli</span>
            <h2 data-reveal>Kepercayaan<br><em>itu terasa.</em></h2>
        </div>
        <div class="review-quotes">
            <?php foreach ($reviews as $review): ?>
                <article class="review-quote" data-reveal>
                    <?= star_markup((int) $review['rating']) ?>
                    <p>“<?= e($review['comment'] ?: 'Pengalaman transaksi yang menyenangkan.') ?>”</p>
                    <span><?= e($review['buyer_name']) ?> — pembeli terverifikasi</span>
                </article>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<section class="about-cta section">
    <div class="container about-cta__inner" data-reveal>
        <span class="eyebrow">Giliranmu</span>
        <h2>Temukan potongan<br>yang <em>menunggu.</em></h2>
        <div class="about-cta__actions">
            <a href="<?= url('/jelajahi') ?>" class="button button--light">Jelajahi Koleksi <?= icon('arrow-up-right') ?></a>
            <a href="<?= url('/jual') ?>" class="button button--outline-light">Mulai Menjual <?= icon('arrow-right') ?></a>
        </div>
    </div>
</section>
