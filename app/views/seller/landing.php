<?php $isLoggedIn = Auth::check(); ?>
<section class="sell-hero">
    <div class="container sell-hero__grid">
        <div class="sell-hero__copy">
            <span class="eyebrow" data-reveal>Jual di re:wear — kurasi vol. 02</span>
            <h1 data-reveal>Lemarimu<br>punya <em>cerita</em><br>yang belum selesai.</h1>
            <p class="sell-hero__lead" data-reveal>Pakaian dan sepatu yang sudah jarang kamu pakai masih layak menemani hari-hari orang lain. Unggah dengan jujur, temukan pemilik berikutnya, dan biarkan ceritanya berlanjut.</p>
            <div class="sell-hero__actions" data-reveal>
                <a href="<?= $isLoggedIn ? url('/penjual/produk/tambah') : url('/daftar') ?>" class="button button--dark"><?= $isLoggedIn ? 'Tambah Produk Pertama' : 'Buat Akun Penjual' ?> <?= icon('arrow-up-right') ?></a>
                <a href="#cara-jual" class="text-link">Lihat cara kerjanya <?= icon('arrow-down') ?></a>
            </div>
            <div class="sell-hero__meta" data-reveal>
                <div><strong>3 menit</strong><span>untuk mengunggah</span></div>
                <div><strong>0 rupiah</strong><span>biaya listing</span></div>
                <div><strong>1 akun</strong><span>untuk beli & jual</span></div>
            </div>
        </div>
        <div class="sell-hero__visual" data-reveal>
            <div class="sell-hero__frame">
                <img src="<?= asset('images/products/rak-netral.jpg') ?>" alt="Rak pakaian netral yang siap dijual kembali" loading="eager">
                <span class="sell-hero__frame-label">Lemari / Bandung</span>
            </div>
            <div class="sell-hero__tag">
                <span class="clothing-tag__icon"><?= icon('hanger') ?></span>
                <span><strong>preloved</strong><small>siap cerita baru</small></span>
                <span class="sell-hero__tag-code">RW—04</span>
            </div>
            <div class="sell-hero__note">
                <span class="eyebrow">Yang kamu butuhkan</span>
                <ul>
                    <li>Foto barang yang sebenarnya</li>
                    <li>Ukuran dan kondisi jujur</li>
                    <li>Harga yang masuk akal</li>
                </ul>
            </div>
        </div>
    </div>
</section>

<section class="sell-steps section" id="cara-jual">
    <div class="container">
        <header class="section-heading section-heading--split">
            <div>
                <span class="eyebrow" data-reveal>Tiga langkah</span>
                <h2 data-reveal>Dari lemari<br>ke <em>lemari lain.</em></h2>
            </div>
            <p class="section-heading__copy" data-reveal>Tanpa proses rumit. Kamu tetap memegang kendali atas harga, komunikasi, dan pengiriman.</p>
        </header>
        <div class="sell-step-grid">
            <article class="sell-step" data-reveal>
                <span class="sell-step__number">01</span>
                <div class="sell-step__icon"><?= icon('shirt') ?></div>
                <h3>Pilih & foto</h3>
                <p>Ambil 1–5 foto di cahaya alami. Tunjukkan bagian depan, belakang, label, dan setiap cacat kecil apa adanya.</p>
                <span class="sell-step__note">Foto asli, bukan katalog</span>
            </article>
            <article class="sell-step" data-reveal>
                <span class="sell-step__number">02</span>
                <div class="sell-step__icon"><?= icon('ruler') ?></div>
                <h3>Tulis detailnya</h3>
                <p>Ukuran, merek, bahan, dan kondisi. Ceritakan juga hal kecil yang perlu diketahui pembeli berikutnya.</p>
                <span class="sell-step__note">Jujur membuat cepat terjual</span>
            </article>
            <article class="sell-step" data-reveal>
                <span class="sell-step__number">03</span>
                <div class="sell-step__icon"><?= icon('chat') ?></div>
                <h3>Bicara & kirim</h3>
                <p>Jawab pertanyaan pembeli, sepakati ongkir, lalu perbarui status pesanan sampai barang tiba.</p>
                <span class="sell-step__note">Transaksi tetap manusiawi</span>
            </article>
        </div>
    </div>
</section>

<section class="sell-editorial section section--flush-top">
    <div class="container sell-editorial__grid">
        <div class="sell-editorial__media" data-reveal>
            <img src="<?= asset('images/products/flatlay-putih.jpg') ?>" alt="Kemeja putih dan denim ditata untuk foto produk" loading="lazy">
            <span class="sell-editorial__caption">Contoh foto yang baik: jelas, netral, dan jujur.</span>
        </div>
        <div class="sell-editorial__copy">
            <span class="eyebrow" data-reveal>Standar kurasi</span>
            <h2 data-reveal>Barang yang layak,<br>bukan sekadar <em>lama.</em></h2>
            <p data-reveal>re:wear hanya menerima pakaian dan sepatu preloved yang masih layak dipakai. Kami tidak menjual aksesori sebagai produk aktif untuk saat ini, dan setiap laporan pembeli akan ditinjau tim moderasi.</p>
            <div class="sell-checks">
                <div data-reveal><span><?= icon('check') ?></span><div><strong>Bersih dan siap pakai</strong><p>Cuci atau bersihkan sebelum difoto dan dikirim.</p></div></div>
                <div data-reveal><span><?= icon('check') ?></span><div><strong>Tanpa kerusakan tersembunyi</strong><p>Catat noda, lubang, atau lecet sekecil apa pun.</p></div></div>
                <div data-reveal><span><?= icon('check') ?></span><div><strong>Harga wajar</strong><p>Lihat barang serupa dan pertimbangkan kondisinya.</p></div></div>
            </div>
            <blockquote class="sell-quote" data-reveal>“Pembeli tidak mencari sempurna. Mereka mencari kejujuran.”<cite>— Catatan kurator re:wear</cite></blockquote>
        </div>
    </div>
</section>

<section class="sell-examples section">
    <div class="container">
        <header class="section-heading">
            <div><span class="eyebrow" data-reveal>Contoh dari komunitas</span><h2 data-reveal>Sedang <em>dicari</em> minggu ini.</h2></div>
            <a href="<?= url('/jelajahi') ?>" class="text-link" data-reveal>Lihat koleksi <?= icon('arrow-right') ?></a>
        </header>
        <div class="product-grid product-grid--three">
            <?php foreach ($products as $product): ?>
                <?php View::partial('components/product-card', ['product' => $product, 'wishlistIds' => []]); ?>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<section class="sell-faq section">
    <div class="container sell-faq__grid">
        <div>
            <span class="eyebrow" data-reveal>Sebelum mulai</span>
            <h2 data-reveal>Pertanyaan yang<br><em>sering muncul.</em></h2>
            <p data-reveal>Masih ragu? Ini hal-hal yang biasanya ditanyakan penjual baru.</p>
            <a href="<?= url('/tentang') ?>" class="text-link" data-reveal>Kenali re:wear lebih dekat <?= icon('arrow-up-right') ?></a>
        </div>
        <div class="sell-faq__list">
            <details class="faq-item" data-reveal open>
                <summary>Apakah menjual di re:wear berbayar? <?= icon('plus') ?></summary>
                <p>Tidak. Membuat akun dan mengunggah produk tidak dikenakan biaya. Kamu hanya perlu menyepakati harga dan ongkir langsung dengan pembeli.</p>
            </details>
            <details class="faq-item" data-reveal>
                <summary>Bagaimana pembayaran dilakukan? <?= icon('plus') ?></summary>
                <p>re:wear tidak memproses pembayaran otomatis. Kamu dan pembeli menyepakati metode pembayaran serta pengiriman melalui chat. Sistem hanya mencatat pesanan dan statusnya.</p>
            </details>
            <details class="faq-item" data-reveal>
                <summary>Apakah saya bisa membeli dan menjual dengan akun yang sama? <?= icon('plus') ?></summary>
                <p>Bisa. Setelah mengunggah produk pertama, akunmu otomatis memiliki akses ke dasbor penjual tanpa kehilangan fitur pembeli.</p>
            </details>
            <details class="faq-item" data-reveal>
                <summary>Barang seperti apa yang tidak boleh dijual? <?= icon('plus') ?></summary>
                <p>Barang yang tidak layak pakai, produk tiruan, aksesori (kategori belum aktif), serta barang yang melanggar ketentuan komunitas. Tim moderasi dapat meninjau dan menghapus produk yang dilaporkan.</p>
            </details>
        </div>
    </div>
</section>

<section class="sell-cta section">
    <div class="container sell-cta__inner">
        <div class="sell-cta__copy">
            <span class="eyebrow eyebrow--light" data-reveal>Mulai dari satu barang</span>
            <h2 data-reveal>Beri yang lama<br>kesempatan <em>baru.</em></h2>
            <p data-reveal>Unggah satu potongan hari ini. Mungkin itu yang sedang dicari seseorang.</p>
            <a href="<?= $isLoggedIn ? url('/penjual/produk/tambah') : url('/daftar') ?>" class="button button--light" data-reveal><?= $isLoggedIn ? 'Tambah Produk' : 'Buat Akun' ?> <?= icon('arrow-up-right') ?></a>
        </div>
        <div class="sell-cta__visual" data-reveal>
            <img src="<?= asset('images/products/jaket-coklat.jpg') ?>" alt="Jaket corduroy coklat siap diberi cerita baru" loading="lazy">
        </div>
    </div>
</section>
