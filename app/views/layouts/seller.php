<?php
$pageScripts = ['main.js', 'dashboard.js', 'animations.js', 'products.js'];
require __DIR__ . '/partials/head.php';
$navUser = Auth::user();
$notifCount = (new NotificationModel())->unreadCount(Auth::id());
$navChatUnread = (new ChatModel())->unreadCount(Auth::id());
View::partial('components/flash');
?>
<div class="dashboard-shell dashboard-shell--seller">
    <aside class="dashboard-sidebar dashboard-sidebar--seller" data-sidebar>
        <div class="dashboard-sidebar__head">
            <a class="brand" href="<?= url('/') ?>"><span class="brand__word">re<span class="brand__colon">:</span>wear</span></a>
            <button class="icon-button dashboard-sidebar__close" data-sidebar-close aria-label="Tutup menu"><?= icon('close') ?></button>
        </div>
        <span class="dashboard-sidebar__label">Studio penjual</span>
        <nav class="dashboard-nav" aria-label="Navigasi penjual">
            <a class="<?= active_nav('/penjual') ?>" href="<?= url('/penjual') ?>"><?= icon('grid') ?><span>Dasbor</span></a>
            <a class="<?= active_nav('/penjual/produk') ?>" href="<?= url('/penjual/produk') ?>"><?= icon('hanger') ?><span>Produk Saya</span></a>
            <a class="<?= active_nav('/penjual/produk/tambah') ?>" href="<?= url('/penjual/produk/tambah') ?>"><?= icon('plus') ?><span>Tambah Produk</span></a>
            <a class="<?= active_nav('/penjual/pesanan') ?>" href="<?= url('/penjual/pesanan') ?>"><?= icon('box') ?><span>Pesanan</span></a>
            <a class="<?= active_nav('/chat') ?>" href="<?= url('/chat') ?>"><?= icon('chat') ?><span>Pesan</span><?php if ($navChatUnread): ?><span class="mini-badge"><?= $navChatUnread ?></span><?php endif; ?></a>
            <a class="<?= active_nav('/profil') ?>" href="<?= url('/profil') ?>"><?= icon('user') ?><span>Profil</span></a>
        </nav>
        <div class="dashboard-sidebar__promo dashboard-sidebar__promo--seller">
            <span class="eyebrow">Tips penjual</span>
            <p>Foto di cahaya alami dan sebutkan setiap detail kecil membuat pembeli lebih percaya.</p>
            <a href="<?= url('/penjual/produk/tambah') ?>" class="button button--light button--sm">Tambah produk <?= icon('plus') ?></a>
        </div>
        <form action="<?= url('/keluar') ?>" method="post" class="dashboard-sidebar__logout">
            <?= csrf_field() ?>
            <button type="submit"><?= icon('logout') ?> Keluar</button>
        </form>
    </aside>
    <div class="dashboard-backdrop" data-sidebar-backdrop></div>
    <div class="dashboard-main">
        <header class="dashboard-topbar">
            <div class="dashboard-topbar__left">
                <button class="icon-button dashboard-menu-toggle" data-sidebar-open aria-label="Buka menu"><?= icon('menu') ?></button>
                <span class="dashboard-topbar__crumb">Penjual / <strong><?= e($page ?? 'dasbor') ?></strong></span>
            </div>
            <div class="dashboard-topbar__right">
                <a href="<?= url('/') ?>" class="dashboard-store-link"><?= icon('arrow-up-right') ?> Lihat situs</a>
                <a href="<?= url('/notifikasi') ?>" class="icon-button" aria-label="Notifikasi">
                    <?= icon('bell') ?><?php if ($notifCount): ?><span class="nav-count nav-count--alert"><?= $notifCount ?></span><?php endif; ?>
                </a>
                <a href="<?= url('/profil') ?>" class="dashboard-user">
                    <span class="avatar avatar--sm" style="--avatar-color: <?= e(avatar_color($navUser['name'])) ?>"><?= e(initials($navUser['name'])) ?></span>
                    <span><?= e(explode(' ', $navUser['name'])[0]) ?></span>
                </a>
            </div>
        </header>
        <main id="main-content" class="dashboard-content">
            <?= $content ?>
        </main>
    </div>
</div>
<script src="<?= asset('js/vendor/gsap.min.js') ?>" defer></script>
<script src="<?= asset('js/vendor/ScrollTrigger.min.js') ?>" defer></script>
<?php require __DIR__ . '/partials/scripts.php'; ?>
