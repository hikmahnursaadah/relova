<?php
$query = $_GET;
unset($query['halaman']);
$queryString = http_build_query($query);
$pageBase = url('/admin/pengguna') . '?' . ($queryString ? $queryString . '&' : '') . 'halaman=';
$roleOptions = ['' => 'Semua peran', 'pembeli' => 'Pembeli', 'penjual' => 'Penjual', 'admin' => 'Administrator'];
$statusOptions = ['' => 'Semua status', 'aktif' => 'Aktif', 'ditangguhkan' => 'Ditangguhkan'];
?>
<section class="admin-page">
    <header class="admin-page__head">
        <div>
            <span class="eyebrow">Komunitas</span>
            <h1>Kelola <em>pengguna.</em></h1>
            <p><?= number_format($total, 0, ',', '.') ?> akun terdaftar. Tinjau peran dan status akun dengan hati-hati.</p>
        </div>
        <span class="admin-page__meta"><?= icon('users') ?> <?= number_format($total, 0, ',', '.') ?> pengguna</span>
    </header>

    <form action="<?= url('/admin/pengguna') ?>" method="get" class="admin-filter-bar">
        <div class="admin-filter-bar__search">
            <?= icon('search') ?>
            <label class="sr-only" for="admin-user-search">Cari pengguna</label>
            <input id="admin-user-search" type="search" name="q" value="<?= e($filters['q']) ?>" placeholder="Cari nama, surel, atau nama pengguna…">
        </div>
        <label class="sr-only" for="admin-user-role">Peran</label>
        <select id="admin-user-role" name="peran">
            <?php foreach ($roleOptions as $value => $label): ?>
                <option value="<?= e($value) ?>" <?= $filters['role'] === $value ? 'selected' : '' ?>><?= e($label) ?></option>
            <?php endforeach; ?>
        </select>
        <label class="sr-only" for="admin-user-status">Status</label>
        <select id="admin-user-status" name="status">
            <?php foreach ($statusOptions as $value => $label): ?>
                <option value="<?= e($value) ?>" <?= $filters['status'] === $value ? 'selected' : '' ?>><?= e($label) ?></option>
            <?php endforeach; ?>
        </select>
        <button type="submit" class="button button--dark button--sm">Terapkan</button>
        <?php if ($filters['q'] || $filters['role'] || $filters['status']): ?>
            <a href="<?= url('/admin/pengguna') ?>" class="text-link">Atur ulang</a>
        <?php endif; ?>
    </form>

    <div class="admin-panel">
        <?php if ($users): ?>
            <div class="table-wrap">
                <table class="data-table data-table--admin data-table--users">
                    <thead>
                        <tr><th>Pengguna</th><th>Peran</th><th>Status</th><th>Produk</th><th>Penjualan</th><th>Bergabung</th><th>Tindakan</th></tr>
                    </thead>
                    <tbody>
                        <?php foreach ($users as $user): ?>
                            <tr>
                                <td>
                                    <div class="admin-user-cell">
                                        <span class="avatar avatar--sm" style="--avatar-color: <?= e(avatar_color($user['name'])) ?>"><?= e(initials($user['name'])) ?></span>
                                        <div><strong><?= e($user['name']) ?></strong><small><?= e($user['email']) ?> · @<?= e($user['username']) ?></small></div>
                                    </div>
                                </td>
                                <td><span class="role-pill role-pill--<?= e($user['role']) ?>"><?= e(role_label($user['role'])) ?></span></td>
                                <td><span class="status <?= $user['status'] === 'aktif' ? 'status--done' : 'status--cancelled' ?>"><?= e($user['status'] === 'aktif' ? 'Aktif' : 'Ditangguhkan') ?></span></td>
                                <td><?= (int) $user['product_count'] ?></td>
                                <td><?= (int) $user['completed_sales'] ?></td>
                                <td><?= e(tanggal($user['created_at'])) ?></td>
                                <td>
                                    <?php if ($user['role'] === 'admin'): ?>
                                        <span class="table-muted">Dilindungi</span>
                                    <?php elseif ((int) $user['id'] === Auth::id()): ?>
                                        <span class="table-muted">Akunmu</span>
                                    <?php else: ?>
                                        <form action="<?= url('/admin/pengguna/' . $user['id'] . '/status') ?>" method="post" <?= $user['status'] === 'aktif' ? 'data-confirm="Tangguhkan akun ini? Pengguna tidak dapat masuk sampai diaktifkan kembali."' : '' ?>>
                                            <?= csrf_field() ?>
                                            <input type="hidden" name="status" value="<?= $user['status'] === 'aktif' ? 'ditangguhkan' : 'aktif' ?>">
                                            <button type="submit" class="table-link <?= $user['status'] === 'aktif' ? 'table-link--danger' : '' ?>"><?= $user['status'] === 'aktif' ? 'Tangguhkan' : 'Aktifkan' ?></button>
                                        </form>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php if ($pages > 1): ?>
                <nav class="admin-pagination" aria-label="Navigasi halaman pengguna">
                    <?php if ($currentPage > 1): ?><a href="<?= e($pageBase . ($currentPage - 1)) ?>"><?= icon('chevron-left') ?></a><?php else: ?><span class="is-disabled"><?= icon('chevron-left') ?></span><?php endif; ?>
                    <span>Halaman <?= $currentPage ?> dari <?= $pages ?></span>
                    <?php if ($currentPage < $pages): ?><a href="<?= e($pageBase . ($currentPage + 1)) ?>"><?= icon('chevron-right') ?></a><?php else: ?><span class="is-disabled"><?= icon('chevron-right') ?></span><?php endif; ?>
                </nav>
            <?php endif; ?>
        <?php else: ?>
            <div class="admin-empty-state">
                <span><?= icon('users') ?></span>
                <h2>Tidak ada pengguna yang cocok.</h2>
                <p>Ubah kata kunci atau filter untuk melihat akun lain.</p>
                <a href="<?= url('/admin/pengguna') ?>" class="button button--dark button--sm">Atur Ulang Filter</a>
            </div>
        <?php endif; ?>
    </div>
</section>
