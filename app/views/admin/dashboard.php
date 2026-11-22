<?php
$days = ['Sun' => 'Min', 'Mon' => 'Sen', 'Tue' => 'Sel', 'Wed' => 'Rab', 'Thu' => 'Kam', 'Fri' => 'Jum', 'Sat' => 'Sab'];
$chartMax = max(1, ...array_values($dailyOrders));
$grossValue = (float) ($orderStats['gross_value'] ?? 0);
$pendingTotal = (int) ($orderStats['pending'] ?? 0);
?>
<section class="admin-page admin-dashboard">
    <header class="admin-page__head">
        <div>
            <span class="eyebrow">Panel moderasi — <?= e(date('Y')) ?></span>
            <h1>Selamat datang, <em><?= e(explode(' ', Auth::user()['name'])[0]) ?>.</em></h1>
            <p>Ringkasan aktivitas komunitas, transaksi, dan hal yang perlu ditinjau.</p>
        </div>
        <div class="admin-page__head-actions">
            <a href="<?= url('/admin/produk?status=ditinjau') ?>" class="button button--ghost"><?= icon('shield') ?> Tinjau Produk</a>
            <a href="<?= url('/admin/pengguna') ?>" class="button button--dark">Kelola Pengguna <?= icon('arrow-right') ?></a>
        </div>
    </header>

    <div class="admin-kpi-grid">
        <article class="admin-kpi">
            <span class="admin-kpi__label">Total pengguna</span>
            <strong><?= number_format($userTotal, 0, ',', '.') ?></strong>
            <span class="admin-kpi__delta">+<?= (int) $newUsers ?> bulan ini</span>
            <span class="admin-kpi__icon"><?= icon('users') ?></span>
        </article>
        <article class="admin-kpi">
            <span class="admin-kpi__label">Produk tayang</span>
            <strong><?= number_format($activeProducts, 0, ',', '.') ?></strong>
            <span class="admin-kpi__delta">dari <?= number_format($productTotal, 0, ',', '.') ?> total produk</span>
            <span class="admin-kpi__icon"><?= icon('hanger') ?></span>
        </article>
        <article class="admin-kpi admin-kpi--attention">
            <span class="admin-kpi__label">Perlu tindakan</span>
            <strong><?= $pendingTotal + (int) $openReports ?></strong>
            <span class="admin-kpi__delta"><?= $pendingTotal ?> pesanan · <?= (int) $openReports ?> laporan</span>
            <span class="admin-kpi__icon"><?= icon('alert') ?></span>
        </article>
        <article class="admin-kpi">
            <span class="admin-kpi__label">Nilai transaksi</span>
            <strong class="admin-kpi__money"><?= rupiah($grossValue) ?></strong>
            <span class="admin-kpi__delta"><?= (int) ($orderStats['completed'] ?? 0) ?> transaksi selesai</span>
            <span class="admin-kpi__icon"><?= icon('box') ?></span>
        </article>
    </div>

    <div class="admin-dashboard__grid">
        <section class="admin-panel admin-chart-panel">
            <header class="admin-panel__head">
                <div><span class="eyebrow">Tujuh hari terakhir</span><h2>Pesanan masuk</h2></div>
                <span class="admin-panel__badge"><?= array_sum($dailyOrders) ?> pesanan</span>
            </header>
            <div class="admin-chart" role="img" aria-label="Grafik pesanan tujuh hari terakhir">
                <?php foreach ($dailyOrders as $date => $count): ?>
                    <div class="admin-chart__column">
                        <span class="admin-chart__value"><?= $count ?></span>
                        <span class="admin-chart__bar" style="--bar-height: <?= max(6, (int) round(($count / $chartMax) * 100)) ?>%"></span>
                        <span class="admin-chart__label"><?= e($days[date('D', strtotime($date))] ?? date('d', strtotime($date))) ?><small><?= date('d/m', strtotime($date)) ?></small></span>
                    </div>
                <?php endforeach; ?>
            </div>
        </section>

        <section class="admin-panel admin-reports-panel">
            <header class="admin-panel__head">
                <div><span class="eyebrow">Moderasi</span><h2>Laporan terbaru</h2></div>
                <a href="<?= url('/admin/pengaturan#laporan') ?>" class="admin-link">Semua laporan <?= icon('arrow-right') ?></a>
            </header>
            <?php if ($recentReports): ?>
                <div class="admin-report-list">
                    <?php foreach ($recentReports as $report): ?>
                        <article class="admin-report">
                            <span class="admin-report__icon <?= $report['status'] === 'baru' ? 'is-new' : '' ?>"><?= icon('alert') ?></span>
                            <div>
                                <strong><?= e($report['reason']) ?></strong>
                                <span><?= e($report['product_title'] ?: 'Laporan pengguna') ?> · oleh <?= e($report['reporter_name']) ?></span>
                                <time><?= e(waktu_relatif($report['created_at'])) ?></time>
                            </div>
                            <span class="admin-report__status"><?= e(ucfirst($report['status'])) ?></span>
                        </article>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <p class="admin-empty">Tidak ada laporan baru. Komunitas dalam kondisi tenang.</p>
            <?php endif; ?>
        </section>

        <section class="admin-panel admin-orders-panel">
            <header class="admin-panel__head">
                <div><span class="eyebrow">Menunggu konfirmasi</span><h2>Pesanan perlu perhatian</h2></div>
                <a href="<?= url('/admin/transaksi?status=menunggu') ?>" class="admin-link">Semua transaksi <?= icon('arrow-right') ?></a>
            </header>
            <?php if ($pendingOrders): ?>
                <div class="table-wrap">
                    <table class="data-table data-table--admin">
                        <thead><tr><th>Nomor</th><th>Pembeli</th><th>Total</th><th>Dibuat</th><th></th></tr></thead>
                        <tbody>
                            <?php foreach ($pendingOrders as $order): ?>
                                <tr>
                                    <td><strong><?= e($order['order_number']) ?></strong><small><?= e($order['first_item_title'] ?: '-') ?></small></td>
                                    <td><?= e($order['buyer_name']) ?></td>
                                    <td><?= rupiah($order['total_amount']) ?></td>
                                    <td><?= e(tanggal($order['created_at'])) ?></td>
                                    <td><a href="<?= url('/admin/transaksi/' . $order['id']) ?>" class="table-link" aria-label="Lihat transaksi"><?= icon('arrow-right') ?></a></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <p class="admin-empty">Tidak ada pesanan yang menunggu konfirmasi.</p>
            <?php endif; ?>
        </section>

        <section class="admin-panel admin-activity-panel">
            <header class="admin-panel__head">
                <div><span class="eyebrow">Produk</span><h2>Aktivitas moderasi</h2></div>
                <a href="<?= url('/admin/produk') ?>" class="admin-link">Kelola produk <?= icon('arrow-right') ?></a>
            </header>
            <?php if ($moderation): ?>
                <div class="admin-activity-list">
                    <?php foreach ($moderation as $item): ?>
                        <article class="admin-activity">
                            <span class="status <?= e(status_produk_class($item['status'])) ?>"><?= e(status_produk_label($item['status'])) ?></span>
                            <div><strong><?= e($item['title']) ?></strong><span>oleh <?= e($item['seller_name']) ?></span></div>
                            <time><?= e(waktu_relatif($item['updated_at'])) ?></time>
                        </article>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <p class="admin-empty">Belum ada aktivitas moderasi.</p>
            <?php endif; ?>
        </section>
    </div>

    <section class="admin-panel admin-recent-orders">
        <header class="admin-panel__head">
            <div><span class="eyebrow">Terbaru</span><h2>Transaksi terakhir</h2></div>
            <a href="<?= url('/admin/transaksi') ?>" class="admin-link">Lihat semua <?= icon('arrow-right') ?></a>
        </header>
        <div class="table-wrap">
            <table class="data-table data-table--admin">
                <thead><tr><th>Nomor</th><th>Pembeli</th><th>Penjual</th><th>Status</th><th>Total</th><th>Tanggal</th><th></th></tr></thead>
                <tbody>
                    <?php foreach ($recentOrders as $order): ?>
                        <tr>
                            <td><strong><?= e($order['order_number']) ?></strong></td>
                            <td><?= e($order['buyer_name']) ?></td>
                            <td><?= e($order['seller_name']) ?></td>
                            <td><span class="status <?= e(order_status_class($order['status'])) ?>"><?= e(order_status_label($order['status'])) ?></span></td>
                            <td><?= rupiah($order['total_amount']) ?></td>
                            <td><?= e(tanggal($order['created_at'])) ?></td>
                            <td><a href="<?= url('/admin/transaksi/' . $order['id']) ?>" class="table-link" aria-label="Buka transaksi"><?= icon('arrow-right') ?></a></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </section>
</section>
