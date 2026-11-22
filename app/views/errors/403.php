<section class="error-page">
    <div class="container error-page__inner">
        <span class="error-page__code">403</span>
        <span class="eyebrow">Akses dibatasi</span>
        <h1>Halaman ini bukan<br><em>untuk akunmu.</em></h1>
        <p>Kamu tidak memiliki izin untuk membuka halaman ini. Jika ini terasa keliru, kembali ke ruang yang sesuai atau hubungi tim re:wear.</p>
        <div class="error-page__actions">
            <a href="<?= url(Auth::isAdmin() ? '/admin' : '/') ?>" class="button button--dark">Kembali ke Dasbor <?= icon('arrow-right') ?></a>
            <a href="<?= url('/jelajahi') ?>" class="button button--ghost">Jelajahi Koleksi</a>
        </div>
    </div>
</section>
