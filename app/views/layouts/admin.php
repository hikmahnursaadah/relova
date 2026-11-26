<?php
$pageScripts = ['main.js', 'dashboard.js', 'animations.js'];
require __DIR__ . '/partials/head.php';
$navUser = Auth::user();
$notifCount = (new NotificationModel())->unreadCount(Auth::id());
View::partial('components/flash');
?>
<div class="admin-shell">
    <aside class="admin-sidebar" data-sidebar>
        <div class="admin-sidebar__head">
            <a class="brand brand--admin" href="<?= url('/admin') ?>"><span class="brand__word">Relova</span></a>
            <span class="admin-badge">Administrator</span>
            <button class="icon-button admin-sidebar__close" data-sidebar-close aria-label="Tutup menu"><?= icon('close') ?></button>
        </div>
        <nav class="admin-nav" aria-label="Navigasi administrator">
            <a class="<?= is_current('/admin') ? 'is-active' : '' ?>" href="<?= url('/admin') ?>"><?= icon('grid') ?><span>Dasbor</span></a>
            <a class="<?= active_nav('/admin/pengguna') ?>" href="<?= url('/admin/pengguna') ?>"><?= icon('users') ?><span>Pengguna</span></a>
            <a class="<?= active_nav('/admin/produk') ?>" href="<?= url('/admin/produk') ?>"><?= icon('hanger') ?><span>Produk</span></a>
            <a class="<?= active_nav('/admin/transaksi') ?>" href="<?= url('/admin/transaksi') ?>"><?= icon('box') ?><span>Transaksi</span></a>
            <a class="<?= active_nav('/admin/pengaturan') ?>" href="<?= url('/admin/pengaturan') ?>"><?= icon('settings') ?><span>Pengaturan</span></a>
        </nav>
        <div class="admin-sidebar__bottom">
            <a href="<?= url('/') ?>" class="admin-site-link"><?= icon('arrow-up-right') ?> Lihat situs</a>
            <form action="<?= url('/keluar') ?>" method="post">
                <?= csrf_field() ?>
                <button type="submit"><?= icon('logout') ?> Keluar</button>
            </form>
        </div>
    </aside>
    <div class="dashboard-backdrop" data-sidebar-backdrop></div>
    <div class="admin-main">
        <header class="admin-topbar">
            <div class="admin-topbar__left">
                <button class="icon-button dashboard-menu-toggle" data-sidebar-open aria-label="Buka menu"><?= icon('menu') ?></button>
                <span>Panel moderasi Relova</span>
            </div>
            <div class="admin-topbar__right">
                <span class="admin-live"><span></span> Sistem aktif</span>
                <a href="<?= url('/notifikasi') ?>" class="icon-button" aria-label="Notifikasi">
                    <?= icon('bell') ?><?php if ($notifCount): ?><span class="nav-count nav-count--alert"><?= $notifCount ?></span><?php endif; ?>
                </a>
                <a href="<?= url('/profil') ?>" class="admin-user">
                    <span class="avatar avatar--sm" style="--avatar-color: <?= e(avatar_color($navUser['name'])) ?>"><?= e(initials($navUser['name'])) ?></span>
                    <span><?= e($navUser['name']) ?></span>
                </a>
            </div>
        </header>
        <main id="main-content" class="admin-content">
            <?= $content ?>
        </main>
    </div>
</div>
<script src="<?= asset('js/vendor/gsap.min.js') ?>" defer></script>
<script src="<?= asset('js/vendor/ScrollTrigger.min.js') ?>" defer></script>
<?php require __DIR__ . '/partials/scripts.php'; ?>
