<?php
$pageScripts = ['main.js', 'dashboard.js', 'animations.js', 'products.js'];
require __DIR__ . '/partials/head.php';
$navUser = Auth::user();
$notifCount = (new NotificationModel())->unreadCount(Auth::id());
View::partial('components/flash');
?>
<div class="dashboard-shell dashboard-shell--buyer">
    <aside class="dashboard-sidebar" data-sidebar>
        <div class="dashboard-sidebar__head">
            <a class="brand" href="<?= url('/') ?>"><span class="brand__word">Relova</span></a>
            <button class="icon-button dashboard-sidebar__close" data-sidebar-close aria-label="Tutup menu"><?= icon('close') ?></button>
        </div>
        <span class="dashboard-sidebar__label">Ruang pembeli</span>
        <nav class="dashboard-nav" aria-label="Navigasi pembeli">
            <a class="<?= is_current('/akun') ? 'is-active' : '' ?>" href="<?= url('/akun') ?>"><?= icon('grid') ?><span>Ringkasan</span></a>
            <a class="<?= active_nav('/') ?>" href="<?= url('/') ?>"><?= icon('sparkle') ?><span>Beranda</span></a>
            <a class="<?= active_nav('/jelajahi') ?>" href="<?= url('/jelajahi') ?>"><?= icon('search') ?><span>Jelajahi</span></a>
            <a class="<?= active_nav('/favorit') ?>" href="<?= url('/favorit') ?>"><?= icon('heart') ?><span>Favorit</span></a>
            <a class="<?= active_nav('/pesanan') ?>" href="<?= url('/pesanan') ?>"><?= icon('box') ?><span>Pesanan</span></a>
            <a class="<?= active_nav('/chat') ?>" href="<?= url('/chat') ?>"><?= icon('chat') ?><span>Pesan</span></a>
            <a class="<?= active_nav('/profil') ?>" href="<?= url('/profil') ?>"><?= icon('user') ?><span>Profil</span></a>
        </nav>
        <div class="dashboard-sidebar__promo">
            <span class="eyebrow">Punya lemari penuh?</span>
            <p>Beri pakaian yang jarang dipakai kesempatan untuk bercerita lagi.</p>
            <a href="<?= Auth::isSeller() ? url('/penjual') : url('/jual') ?>" class="button button--light button--sm"><?= Auth::isSeller() ? 'Buka dasbor penjual' : 'Mulai menjual' ?> <?= icon('arrow-right') ?></a>
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
                <span class="dashboard-topbar__crumb">Akun / <strong><?= e(ucfirst($page ?? 'ringkasan')) ?></strong></span>
            </div>
            <div class="dashboard-topbar__right">
                <a href="<?= url('/notifikasi') ?>" class="icon-button" aria-label="Notifikasi">
                    <?= icon('bell') ?><?php if ($notifCount): ?><span class="nav-count nav-count--alert"><?= $notifCount ?></span><?php endif; ?>
                </a>
                <a href="<?= url('/keranjang') ?>" class="icon-button" aria-label="Keranjang"><?= icon('bag') ?></a>
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
