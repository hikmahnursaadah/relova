<?php $settingsFooter = (new SettingModel())->all(); ?>
<footer class="site-footer">
    <div class="site-footer__top">
        <div class="site-footer__brand">
            <a class="brand brand--footer" href="<?= url('/') ?>">
                <span class="brand__word">Relova</span>
            </a>
            <p>Pakaian punya kehidupan kedua.<br>Pakaian dan sepatu bekas terawat yang dikurasi dengan teliti.</p>
            <a class="footer-email" href="mailto:<?= e($settingsFooter['contact_email'] ?? 'halo@rewear.id') ?>"><?= e($settingsFooter['contact_email'] ?? 'halo@rewear.id') ?></a>
        </div>
        <div class="site-footer__columns">
            <div>
                <h3>Jelajahi</h3>
                <a href="<?= url('/jelajahi') ?>">Semua produk</a>
                <a href="<?= url('/jelajahi?kategori[]=atasan') ?>">Atasan</a>
                <a href="<?= url('/jelajahi?kategori[]=bawahan') ?>">Bawahan</a>
                <a href="<?= url('/jelajahi?kategori[]=outerwear') ?>">Outerwear</a>
                <a href="<?= url('/jelajahi?kategori[]=dress') ?>">Dress</a>
                <a href="<?= url('/jelajahi?kategori[]=sepatu') ?>">Sepatu</a>
            </div>
            <div>
                <h3>Komunitas</h3>
                <a href="<?= url('/tentang') ?>">Tentang kami</a>
                <a href="<?= url('/jual') ?>">Mulai menjual</a>
                <a href="<?= url('/studio-3d') ?>">Studio Manekin</a>
                <a href="<?= url('/masuk') ?>">Masuk ke akun</a>
            </div>
            <div>
                <h3>Bantuan</h3>
                <a href="<?= url('/pesanan') ?>">Lacak pesanan</a>
                <a href="<?= url('/chat') ?>">Hubungi penjual</a>
                <a href="<?= url('/profil') ?>">Pengaturan akun</a>
                <a href="mailto:<?= e($settingsFooter['contact_email'] ?? 'halo@rewear.id') ?>">Laporkan masalah</a>
            </div>
        </div>
    </div>
    <div class="site-footer__wordmark" aria-hidden="true">Relova</div>
    <div class="site-footer__bottom">
        <span>© <?= date('Y') ?> Relova. Dibuat untuk memberi pakaian kesempatan kedua.</span>
        <span>Bandung — Jakarta, Indonesia</span>
        <button type="button" class="back-to-top" data-back-to-top>Kembali ke atas <?= icon('arrow-up-right') ?></button>
    </div>
</footer>
