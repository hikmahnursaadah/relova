<?php
$tabs = ['' => 'Semua', 'aktif' => 'Aktif', 'terjual' => 'Terjual', 'ditinjau' => 'Ditinjau'];
?>
<section class="dashboard-page">
    <header class="dashboard-page__head">
        <div>
            <span class="eyebrow">Etalase</span>
            <h1>Produk <em>saya.</em></h1>
            <p class="dashboard-page__sub">Kelola foto, harga, dan status setiap potongan yang kamu titipkan.</p>
        </div>
        <a href="<?= url('/penjual/produk/tambah') ?>" class="button button--dark"><?= icon('plus') ?> Tambah Produk</a>
    </header>

    <div class="stats-strip stats-strip--compact">
        <div class="stat-card"><span>Total produk</span><strong><?= (int) ($productStats['total'] ?? 0) ?></strong></div>
        <div class="stat-card"><span>Aktif</span><strong><?= (int) ($productStats['active_count'] ?? 0) ?></strong></div>
        <div class="stat-card"><span>Terjual</span><strong><?= (int) ($productStats['sold_count'] ?? 0) ?></strong></div>
        <div class="stat-card"><span>Ditinjau</span><strong><?= (int) ($productStats['review_count'] ?? 0) ?></strong></div>
        <div class="stat-card"><span>Total dilihat</span><strong><?= number_format((int) ($productStats['total_views'] ?? 0), 0, ',', '.') ?></strong></div>
    </div>

    <nav class="filter-tabs" aria-label="Filter status produk">
        <?php foreach ($tabs as $value => $label): ?>
            <a href="<?= url('/penjual/produk' . ($value ? '?status=' . $value : '')) ?>" class="<?= $statusFilter === $value ? 'is-active' : '' ?>"><?= e($label) ?></a>
        <?php endforeach; ?>
    </nav>

    <?php if ($products): ?>
        <div class="seller-products-table panel-card">
            <div class="table-wrap">
                <table class="data-table data-table--products">
                    <thead>
                        <tr><th>Produk</th><th>Harga</th><th>Status</th><th>Dilihat</th><th>Tanggal</th><th>Aksi</th></tr>
                    </thead>
                    <tbody>
                        <?php foreach ($products as $product): ?>
                            <tr>
                                <td>
                                    <div class="table-product">
                                        <a href="<?= url('/produk/' . $product['id']) ?>"><img src="<?= e(product_image($product['primary_image'])) ?>" alt=""></a>
                                        <div>
                                            <a href="<?= url('/produk/' . $product['id']) ?>"><strong><?= e($product['title']) ?></strong></a>
                                            <small><?= e($product['category_name']) ?> · Ukuran <?= e($product['size']) ?> · <?= e($product['item_condition']) ?></small>
                                        </div>
                                    </div>
                                </td>
                                <td><strong><?= rupiah($product['price']) ?></strong></td>
                                <td><span class="status <?= e(status_produk_class($product['status'])) ?>"><?= e(status_produk_label($product['status'])) ?></span></td>
                                <td><?= (int) $product['view_count'] ?></td>
                                <td><?= e(tanggal($product['created_at'])) ?></td>
                                <td>
                                    <div class="table-actions">
                                        <a href="<?= url('/penjual/produk/' . $product['id'] . '/edit') ?>" class="table-link" aria-label="Edit <?= e($product['title']) ?>"><?= icon('edit') ?></a>
                                        <a href="<?= url('/produk/' . $product['id']) ?>" class="table-link" aria-label="Lihat <?= e($product['title']) ?>"><?= icon('eye') ?></a>
                                        <?php if ($product['status'] !== 'terjual'): ?>
                                            <form action="<?= url('/penjual/produk/' . $product['id'] . '/hapus') ?>" method="post" data-confirm="Hapus produk ini dari etalase? Tindakan ini tidak dapat dibatalkan.">
                                                <?= csrf_field() ?>
                                                <button type="submit" class="table-link table-link--danger" aria-label="Hapus <?= e($product['title']) ?>"><?= icon('trash') ?></button>
                                            </form>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    <?php else: ?>
        <div class="empty-state empty-state--panel">
            <span class="empty-state__icon"><?= icon('hanger') ?></span>
            <h2><?= $statusFilter ? 'Tidak ada produk dengan status ini.' : 'Etalase masih kosong.' ?></h2>
            <p><?= $statusFilter ? 'Coba lihat semua produk atau ubah filter status.' : 'Unggah produk pertamamu dan biarkan ia menemukan pemilik berikutnya.' ?></p>
            <a href="<?= $statusFilter ? url('/penjual/produk') : url('/penjual/produk/tambah') ?>" class="button button--dark"><?= $statusFilter ? 'Lihat Semua Produk' : 'Tambah Produk' ?> <?= icon('arrow-right') ?></a>
        </div>
    <?php endif; ?>
</section>
