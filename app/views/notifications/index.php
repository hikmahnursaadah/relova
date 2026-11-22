<?php
$typeIcons = ['pesanan' => 'box', 'pesan' => 'chat', 'ulasan' => 'star', 'laporan' => 'alert', 'moderasi' => 'shield', 'akun' => 'user'];
$typeLabels = ['pesanan' => 'Pesanan', 'pesan' => 'Pesan', 'ulasan' => 'Ulasan', 'laporan' => 'Laporan', 'moderasi' => 'Moderasi', 'akun' => 'Akun'];
$groups = [];
foreach ($notifications as $notification) {
    $groups[tanggal($notification['created_at'])][] = $notification;
}
?>
<section class="dashboard-page notifications-page">
    <header class="dashboard-page__head">
        <div>
            <span class="eyebrow">Aktivitas akun</span>
            <h1>Notifikasi <em>(<?= count($notifications) ?>)</em></h1>
            <p class="dashboard-page__sub">Semua kabar terbaru tentang pesanan, pesan, dan aktivitas komunitasmu.</p>
        </div>
        <form action="<?= url('/notifikasi/baca-semua') ?>" method="post">
            <?= csrf_field() ?>
            <button type="submit" class="text-link"><?= icon('check') ?> Tandai semua dibaca</button>
        </form>
    </header>

    <?php if ($notifications): ?>
        <div class="notification-groups">
            <?php foreach ($groups as $date => $group): ?>
                <section class="notification-group">
                    <h2 class="notification-group__date"><?= e($date) ?></h2>
                    <div class="notification-list">
                        <?php foreach ($group as $notification): ?>
                            <?php $type = $notification['type'] ?: 'info'; ?>
                            <a href="<?= e($notification['link'] ?: url('/')) ?>" class="notification-row <?= !$notification['is_read'] ? 'is-unread' : '' ?>">
                                <span class="notification-row__icon notification-row__icon--<?= e($type) ?>"><?= icon($typeIcons[$type] ?? 'bell') ?></span>
                                <span class="notification-row__copy">
                                    <span class="notification-row__meta">
                                        <span class="notification-row__type"><?= e($typeLabels[$type] ?? 'Aktivitas') ?></span>
                                        <time><?= e(waktu_relatif($notification['created_at'])) ?></time>
                                    </span>
                                    <strong><?= e($notification['title']) ?></strong>
                                    <p><?= e($notification['message']) ?></p>
                                </span>
                                <span class="notification-row__arrow"><?= icon('arrow-up-right') ?></span>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </section>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <div class="empty-state empty-state--panel">
            <span class="empty-state__icon"><?= icon('bell') ?></span>
            <h2>Belum ada notifikasi.</h2>
            <p>Kabar tentang pesanan, pesan, dan aktivitas akunmu akan muncul di sini.</p>
            <a href="<?= url(Auth::isAdmin() ? '/admin' : '/akun') ?>" class="button button--dark">Kembali ke Dasbor <?= icon('arrow-right') ?></a>
        </div>
    <?php endif; ?>
</section>
