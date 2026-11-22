<?php
$query = $_GET;
unset($query['halaman']);
$queryString = http_build_query($query);
$pageBase = url('/admin/produk') . '?' . ($queryString ? $queryString . '&' : '') . 'halaman=';
$statusOptions = ['' => 'Semua status', 'aktif' => 'Aktif', 'ditinjau' => 'Ditinjau', 'terjual' => 'Terjual', 'dihapus' => 'Dihapus'];
?>
<section class="admin-page">
    <header class="admin-page__head">
        <div>
            <span class="eyebrow">Moderasi etalase</span>
            <h1>Kelola <em>produk.</em></h1>
            <p><?= number_format($pagination['total'], 0, ',', '.') ?> produk dalam arsip. Tinjau laporan dan jaga kualitas kurasi.</p>
        </div>
        <span class="admin-page__meta"><?= icon('hanger') ?> <?= number_format($pagination['total'], 0, ',', '.') ?> produk</span>
    </header>

    <form action="<?= url('/admin/produk') ?>" method="get" class="admin-filter-bar admin-filter-bar--wide">
        <div class="admin-filter-bar__search">
            <?= icon('search') ?>
            <label class="sr-only" for="admin-product-search">Cari produk</label>
            <input id="admin-product-search" type="search" name="q" value="<?= e($filters['q']) ?>" placeholder="Cari nama produk atau penjual…">
        </div>
        <label class="sr-only" for="admin-product-status">Status</label>
        <select id="admin-product-status" name="status">
            <?php foreach ($statusOptions as $value => $label): ?>
                <option value="<?= e($value) ?>" <?= $filters['status'] === $value ? 'selected' : '' ?>><?= e($label) ?></option>
            <?php endforeach; ?>
        </select>
        <label class="sr-only" for="admin-product-category">Kategori</label>
        <select id="admin-product-category" name="kategori">
            <option value="0">Semua kategori</option>
            <?php foreach ($categories as $category): ?>
                <option value="<?= (int) $category['id'] ?>" <?= (int) $filters['category_id'] === (int) $category['id'] ? 'selected' : '' ?>><?= e($category['name']) ?></option>
            <?php endforeach; ?>
        </select>
        <button type="submit" class="button button--dark button--sm">Terapkan</button>
        <?php if ($filters['q'] || $filters['status'] || $filters['category_id']): ?><a href="<?= url('/admin/produk') ?>" class="text-link">Atur ulang</a><?php endif; ?>
    </form>

    <div class="admin-panel">
        <?php if ($products): ?>
            <div class="table-wrap">
                <table class="data-table data-table--admin data-table--moderation">
                    <thead><tr><th>Produk</th><th>Penjual</th><th>Harga</th><th>Status</th><th>Dilihat</th><th>Diperbarui</th><th>Moderasi</th></tr></thead>
                    <tbody>
                        <?php foreach ($products as $product): ?>
                            <tr>
                                <td>
                                    <div class="table-product">
                                        <a href="<?= url('/produk/' . $product['id']) ?>"><img src="<?= e(product_image($product['primary_image'])) ?>" alt=""></a>
                                        <div>
                                            <a href="<?= url('/produk/' . $product['id']) ?>"><strong><?= e($product['title']) ?></strong></a>
                                            <small><?= e($product['category_name']) ?> · <?= e($product['item_condition']) ?> · <?= e($product['size']) ?></small>
                                        </div>
                                    </div>
                                </td>
                                <td><?= e($product['seller_name']) ?><small><?= e($product['seller_city'] ?: 'Indonesia') ?></small></td>
                                <td><?= rupiah($product['price']) ?></td>
                                <td><span class="status <?= e(status_produk_class($product['status'])) ?>"><?= e(status_produk_label($product['status'])) ?></span></td>
                                <td><?= (int) $product['view_count'] ?></td>
                                <td><?= e(tanggal($product['updated_at'])) ?></td>
                                <td>
                                    <div class="moderation-actions">
                                        <?php if ($product['status'] === 'aktif'): ?>
                                            <form action="<?= url('/admin/produk/' . $product['id'] . '/moderasi') ?>" method="post" data-confirm="Sembunyikan produk ini sementara untuk ditinjau?">
                                                <?= csrf_field() ?><input type="hidden" name="action" value="tinjau">
                                                <button type="submit" class="table-link">Tinjau</button>
                                            </form>
                                            <form action="<?= url('/admin/produk/' . $product['id'] . '/moderasi') ?>" method="post" data-confirm="Hapus produk ini dari etalase? Penjual akan menerima notifikasi.">
                                                <?= csrf_field() ?><input type="hidden" name="action" value="hapus">
                                                <button type="submit" class="table-link table-link--danger">Hapus</button>
                                            </form>
                                        <?php elseif ($product['status'] === 'ditinjau' || $product['status'] === 'dihapus'): ?>
                                            <form action="<?= url('/admin/produk/' . $product['id'] . '/moderasi') ?>" method="post">
                                                <?= csrf_field() ?><input type="hidden" name="action" value="pulihkan">
                                                <button type="submit" class="table-link">Pulihkan</button>
                                            </form>
                                            <?php if ($product['status'] === 'ditinjau'): ?>
                                                <form action="<?= url('/admin/produk/' . $product['id'] . '/moderasi') ?>" method="post" data-confirm="Hapus produk ini dari etalase?">
                                                    <?= csrf_field() ?><input type="hidden" name="action" value="hapus">
                                                    <button type="submit" class="table-link table-link--danger">Hapus</button>
                                                </form>
                                            <?php endif; ?>
                                        <?php else: ?>
                                            <a href="<?= url('/produk/' . $product['id']) ?>" class="table-link">Lihat</a>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php if ($pagination['pages'] > 1): ?>
                <nav class="admin-pagination" aria-label="Navigasi halaman produk">
                    <?php if ($pagination['page'] > 1): ?><a href="<?= e($pageBase . ($pagination['page'] - 1)) ?>"><?= icon('chevron-left') ?></a><?php else: ?><span class="is-disabled"><?= icon('chevron-left') ?></span><?php endif; ?>
                    <span>Halaman <?= $pagination['page'] ?> dari <?= $pagination['pages'] ?></span>
                    <?php if ($pagination['page'] < $pagination['pages']): ?><a href="<?= e($pageBase . ($pagination['page'] + 1)) ?>"><?= icon('chevron-right') ?></a><?php else: ?><span class="is-disabled"><?= icon('chevron-right') ?></span><?php endif; ?>
                </nav>
            <?php endif; ?>
        <?php else: ?>
            <div class="admin-empty-state">
                <span><?= icon('hanger') ?></span>
                <h2>Tidak ada produk yang cocok.</h2>
                <p>Ubah filter atau kata kunci pencarian.</p>
                <a href="<?= url('/admin/produk') ?>" class="button button--dark button--sm">Atur Ulang Filter</a>
            </div>
        <?php endif; ?>
    </div>
</section>
