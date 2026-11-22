<?php
$navUser = Auth::user();
$wishlistCount = $navUser && !Auth::isAdmin() ? (new WishlistModel())->count(Auth::id()) : 0;
$cartCount = count((array) Session::get('cart', []));
$notificationCount = $navUser ? (new NotificationModel())->unreadCount(Auth::id()) : 0;
$recentNotifications = $navUser ? (new NotificationModel())->recent(Auth::id(), 4) : [];
$chatUnread = $navUser && !Auth::isAdmin() ? (new ChatModel())->unreadCount(Auth::id()) : 0;
?>
<header class="site-header" data-navbar>
    <div class="navbar-shell">
        <div class="navbar-top">
            <a class="brand" href="<?= url('/') ?>" aria-label="re:wear — Beranda">
                <span class="brand__word">re<span class="brand__colon">:</span>wear</span>
                <span class="brand__note">preloved fashion</span>
            </a>

            <div class="nav-utilities">
                <button type="button" class="nav-icon-button nav-search-button" data-search-open aria-label="Buka pencarian">
                    <?= icon('search') ?>
                    <span class="nav-icon-label">Cari</span>
                </button>

                <a class="nav-icon-button" href="<?= $navUser ? url('/favorit') : url('/masuk?next=' . urlencode('/favorit')) ?>" aria-label="Favorit">
                    <?= icon('heart') ?>
                    <span class="nav-count" data-wishlist-count><?= $wishlistCount ?></span>
                </a>

                <a class="nav-icon-button" href="<?= $navUser ? url('/keranjang') : url('/masuk?next=' . urlencode('/keranjang')) ?>" aria-label="Keranjang">
                    <?= icon('bag') ?>
                    <span class="nav-count" data-cart-count><?= $cartCount ?></span>
                </a>

                <?php if ($navUser): ?>
                    <details class="nav-popover">
                        <summary class="nav-icon-button" aria-label="Notifikasi">
                            <?= icon('bell') ?>
                            <?php if ($notificationCount > 0): ?>
                                <span class="nav-count nav-count--alert"><?= $notificationCount ?></span>
                            <?php endif; ?>
                        </summary>
                        <div class="nav-popover__panel nav-notifications">
                            <div class="nav-popover__head">
                                <strong>Notifikasi</strong>
                                <a href="<?= url('/notifikasi') ?>">Lihat semua</a>
                            </div>
                            <?php if ($recentNotifications): ?>
                                <div class="notification-mini-list">
                                    <?php foreach ($recentNotifications as $notification): ?>
                                        <form action="<?= url('/notifikasi/' . $notification['id'] . '/baca') ?>" method="post">
                                            <?= csrf_field() ?>
                                            <button type="submit" class="notification-mini <?= !$notification['is_read'] ? 'is-unread' : '' ?>">
                                                <span class="notification-mini__dot"></span>
                                                <span>
                                                    <strong><?= e($notification['title']) ?></strong>
                                                    <small><?= e(excerpt($notification['message'], 74)) ?></small>
                                                    <time><?= e(waktu_relatif($notification['created_at'])) ?></time>
                                                </span>
                                            </button>
                                        </form>
                                    <?php endforeach; ?>
                                </div>
                                <form action="<?= url('/notifikasi/baca-semua') ?>" method="post" class="nav-popover__foot">
                                    <?= csrf_field() ?>
                                    <button type="submit" class="text-link">Tandai semua sudah dibaca</button>
                                </form>
                            <?php else: ?>
                                <div class="nav-empty">
                                    <?= icon('bell') ?>
                                    <p>Belum ada notifikasi baru.</p>
                                </div>
                            <?php endif; ?>
                        </div>
                    </details>
                <?php endif; ?>

                <?php if ($navUser): ?>
                    <details class="nav-popover nav-popover--profile">
                        <summary class="nav-profile-button" aria-label="Menu akun">
                            <span class="avatar avatar--sm" style="--avatar-color: <?= e(avatar_color($navUser['name'])) ?>"><?= e(initials($navUser['name'])) ?></span>
                            <span class="nav-profile-button__name"><?= e(explode(' ', $navUser['name'])[0]) ?></span>
                            <?= icon('chevron-down') ?>
                        </summary>
                        <div class="nav-popover__panel nav-account">
                            <div class="nav-account__head">
                                <span class="avatar" style="--avatar-color: <?= e(avatar_color($navUser['name'])) ?>"><?= e(initials($navUser['name'])) ?></span>
                                <span>
                                    <strong><?= e($navUser['name']) ?></strong>
                                    <small><?= e(role_label($navUser['role'])) ?></small>
                                </span>
                            </div>
                            <nav class="nav-account__links" aria-label="Menu akun">
                                <?php if (Auth::isAdmin()): ?>
                                    <a href="<?= url('/admin') ?>"><?= icon('grid') ?> Dasbor admin</a>
                                <?php else: ?>
                                    <a href="<?= url('/profil') ?>"><?= icon('user') ?> Profil saya</a>
                                    <a href="<?= url('/pesanan') ?>"><?= icon('box') ?> Pesanan</a>
                                    <a href="<?= url('/favorit') ?>"><?= icon('heart') ?> Favorit</a>
                                    <a href="<?= url('/chat') ?>"><?= icon('chat') ?> Pesan <?php if ($chatUnread > 0): ?><span class="mini-badge"><?= $chatUnread ?></span><?php endif; ?></a>
                                    <?php if (Auth::isSeller()): ?>
                                        <a href="<?= url('/penjual') ?>"><?= icon('hanger') ?> Dasbor penjual</a>
                                    <?php else: ?>
                                        <a href="<?= url('/jual') ?>"><?= icon('tag') ?> Mulai menjual</a>
                                    <?php endif; ?>
                                <?php endif; ?>
                            </nav>
                            <form action="<?= url('/keluar') ?>" method="post" class="nav-account__logout">
                                <?= csrf_field() ?>
                                <button type="submit"><?= icon('logout') ?> Keluar</button>
                            </form>
                        </div>
                    </details>
                <?php else: ?>
                    <a href="<?= url('/masuk') ?>" class="nav-login-link">Masuk</a>
                    <a href="<?= url('/daftar') ?>" class="button button--dark button--sm nav-register">Daftar</a>
                <?php endif; ?>
            </div>
        </div>

        <div class="navbar-zip" data-navbar-zip>
            <div class="nav-drawer" id="nav-drawer" data-nav-drawer aria-hidden="true">
                <nav class="nav-links" aria-label="Navigasi utama" data-nav-links>
                    <a class="nav-link <?= active_nav('/') ?>" href="<?= url('/') ?>"><span>01</span>Beranda</a>
                    <div class="nav-link-wrap">
                        <a class="nav-link <?= active_nav('/jelajahi') ?>" href="<?= url('/jelajahi') ?>" data-tag-trigger><span>02</span>Jelajahi</a>
                        <div class="clothing-tag" data-clothing-tag aria-hidden="true">
                            <span class="clothing-tag__string"></span>
                            <span class="clothing-tag__card">
                                <span class="clothing-tag__icon"><?= icon('hanger') ?></span>
                                <span class="clothing-tag__copy">
                                    <strong>preloved</strong>
                                    <small>pilih cerita berikutnya</small>
                                </span>
                                <span class="clothing-tag__code">RW—02</span>
                            </span>
                        </div>
                    </div>
                    <a class="nav-link <?= active_nav('/studio-3d') ?>" href="<?= url('/studio-3d') ?>"><span>03</span>Studio 3D</a>
                    <a class="nav-link <?= active_nav('/jual') ?>" href="<?= url('/jual') ?>"><span>04</span>Jual</a>
                    <a class="nav-link <?= active_nav('/tentang') ?>" href="<?= url('/tentang') ?>"><span>05</span>Tentang Kami</a>
                </nav>
                <div class="nav-drawer__meta">
                    <span>Bandung — Jakarta</span>
                    <span>Kurasi mingguan</span>
                </div>
            </div>

            <div class="zipper" data-zipper>
                <div class="zipper__teeth zipper__teeth--top" aria-hidden="true"></div>
                <div class="zipper__teeth zipper__teeth--bottom" aria-hidden="true"></div>
                <div class="zipper__tape" aria-hidden="true"></div>
                <button type="button" class="zipper__slider" data-zipper-slider aria-expanded="false" aria-controls="nav-drawer" aria-label="Tarik zipper untuk membuka navigasi">
                    <span class="zipper__pull">
                        <svg viewBox="0 0 34 30" aria-hidden="true">
                            <path d="M10 2h14l3 7-2 8H9l-2-8 3-7Z" fill="url(#sliderMetal)" stroke="#4e4a42" stroke-width="1"/>
                            <path d="M12 17h10v4H12z" fill="#6a655a"/>
                            <path d="M16 21h2v7h-2z" fill="url(#sliderMetal)" stroke="#4e4a42" stroke-width=".7"/>
                            <path d="M13 28h8" stroke="#4e4a42" stroke-width="1.5" stroke-linecap="round"/>
                            <circle cx="17" cy="9" r="2.2" fill="#282621"/>
                            <defs>
                                <linearGradient id="sliderMetal" x1="0" y1="0" x2="1" y2="1">
                                    <stop offset="0" stop-color="#f0e9d9"/>
                                    <stop offset=".32" stop-color="#a9a191"/>
                                    <stop offset=".55" stop-color="#e5ddcd"/>
                                    <stop offset=".8" stop-color="#7b7467"/>
                                    <stop offset="1" stop-color="#d8d0c0"/>
                                </linearGradient>
                            </defs>
                        </svg>
                    </span>
                    <span class="zipper__slider-label">Tarik</span>
                </button>
                <span class="zipper__hint" data-zipper-hint>GESER ZIPPER UNTUK MENU</span>
                <span class="zipper__open-hint" data-zipper-open-hint><?= icon('chevron-down') ?> Menutup menu</span>
            </div>
        </div>
    </div>
</header>
<?php View::partial('components/search-overlay'); ?>
