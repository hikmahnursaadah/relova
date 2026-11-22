<?php
$typeOptions = ['atasan' => 'Atasan', 'bawahan' => 'Bawahan', 'outerwear' => 'Outerwear', 'sepatu' => 'Sepatu', 'aksesori' => 'Aksesori'];
$reportStatus = ['' => 'Semua status', 'baru' => 'Baru', 'diproses' => 'Diproses', 'selesai' => 'Selesai'];
?>
<section class="admin-page admin-settings-page">
    <header class="admin-page__head">
        <div>
            <span class="eyebrow">Konfigurasi platform</span>
            <h1>Pengaturan <em>re:wear.</em></h1>
            <p>Identitas situs, kategori katalog, dan laporan komunitas dalam satu panel.</p>
        </div>
    </header>

    <nav class="admin-settings-tabs" aria-label="Bagian pengaturan">
        <a href="#platform">Platform</a>
        <a href="#kategori">Kategori</a>
        <a href="#laporan">Laporan <span><?= count($reports) ?></span></a>
    </nav>

    <section class="admin-panel" id="platform">
        <div class="admin-panel__head">
            <div><span class="eyebrow">Identitas</span><h2>Informasi platform</h2></div>
        </div>
        <form action="<?= url('/admin/pengaturan') ?>" method="post" class="form-stack admin-settings-form">
            <?= csrf_field() ?>
            <div class="field-row">
                <div class="field">
                    <label for="setting-site-name">Nama situs</label>
                    <input id="setting-site-name" type="text" name="site_name" value="<?= e($settings['site_name'] ?? 're:wear') ?>" required>
                </div>
                <div class="field">
                    <label for="setting-site-tagline">Tagline</label>
                    <input id="setting-site-tagline" type="text" name="site_tagline" value="<?= e($settings['site_tagline'] ?? '') ?>">
                </div>
            </div>
            <div class="field-row">
                <div class="field">
                    <label for="setting-contact-email">Email kontak</label>
                    <input id="setting-contact-email" type="email" name="contact_email" value="<?= e($settings['contact_email'] ?? '') ?>">
                </div>
                <div class="field">
                    <label for="setting-contact-phone">Telepon kontak</label>
                    <input id="setting-contact-phone" type="text" name="contact_phone" value="<?= e($settings['contact_phone'] ?? '') ?>">
                </div>
            </div>
            <div class="field-row">
                <div class="field">
                    <label for="setting-city">Kota operasional</label>
                    <input id="setting-city" type="text" name="city" value="<?= e($settings['city'] ?? '') ?>">
                </div>
                <div class="field">
                    <label for="setting-instagram">Instagram</label>
                    <input id="setting-instagram" type="text" name="instagram" value="<?= e($settings['instagram'] ?? '') ?>">
                </div>
            </div>
            <div class="field-row field-row--three">
                <div class="field">
                    <label for="setting-impact-items">Barang terselamatkan</label>
                    <input id="setting-impact-items" type="number" name="impact_items" value="<?= e($settings['impact_items'] ?? '0') ?>" min="0">
                </div>
                <div class="field">
                    <label for="setting-impact-sellers">Penjual aktif</label>
                    <input id="setting-impact-sellers" type="number" name="impact_sellers" value="<?= e($settings['impact_sellers'] ?? '0') ?>" min="0">
                </div>
                <div class="field">
                    <label for="setting-impact-cities">Kota terhubung</label>
                    <input id="setting-impact-cities" type="number" name="impact_cities" value="<?= e($settings['impact_cities'] ?? '0') ?>" min="0">
                </div>
            </div>
            <div class="field">
                <label for="setting-moderation-note">Catatan moderasi</label>
                <textarea id="setting-moderation-note" name="moderation_note" rows="3"><?= e($settings['moderation_note'] ?? '') ?></textarea>
                <small class="field-help">Ditampilkan sebagai pengingat internal tim moderasi.</small>
            </div>
            <div class="form-actions"><button type="submit" class="button button--dark">Simpan Pengaturan <?= icon('arrow-right') ?></button></div>
        </form>
    </section>

    <section class="admin-panel" id="kategori">
        <div class="admin-panel__head">
            <div><span class="eyebrow">Struktur katalog</span><h2>Kategori produk</h2></div>
            <span class="admin-panel__badge"><?= count($categories) ?> kategori</span>
        </div>
        <p class="admin-settings-note">Kategori aksesori tetap nonaktif karena re:wear hanya menjual pakaian dan sepatu preloved. Kamu dapat mengaktifkannya kembali jika layanan aksesori dibuka.</p>
        <div class="admin-category-list">
            <?php foreach ($categories as $category): ?>
                <details class="admin-category">
                    <summary>
                        <span class="admin-category__image"><img src="<?= e(media_url($category['image'])) ?>" alt=""></span>
                        <span class="admin-category__name"><strong><?= e($category['name']) ?></strong><small>/<?= e($category['slug']) ?> · <?= e($category['type']) ?></small></span>
                        <span class="admin-category__count"><?= (int) $category['product_count'] ?> produk</span>
                        <span class="status <?= (int) $category['is_active'] ? 'status--done' : 'status--cancelled' ?>"><?= (int) $category['is_active'] ? 'Aktif' : 'Nonaktif' ?></span>
                        <?= icon('chevron-down') ?>
                    </summary>
                    <form action="<?= url('/admin/kategori/' . $category['id']) ?>" method="post" class="admin-category__form">
                        <?= csrf_field() ?>
                        <div class="field-row">
                            <div class="field"><label>Nama</label><input type="text" name="name" value="<?= e($category['name']) ?>" required></div>
                            <div class="field"><label>Slug</label><input type="text" name="slug" value="<?= e($category['slug']) ?>" pattern="[a-z0-9-]+" required></div>
                        </div>
                        <div class="field"><label>Deskripsi</label><textarea name="description" rows="2"><?= e($category['description'] ?? '') ?></textarea></div>
                        <div class="field-row">
                            <div class="field"><label>Gambar</label><input type="text" name="image" value="<?= e($category['image'] ?? '') ?>" placeholder="assets/images/products/…"></div>
                            <div class="field"><label>Jenis</label><select name="type"><?php foreach ($typeOptions as $value => $label): ?><option value="<?= e($value) ?>" <?= $category['type'] === $value ? 'selected' : '' ?>><?= e($label) ?></option><?php endforeach; ?></select></div>
                        </div>
                        <div class="field-row">
                            <div class="field"><label>Urutan</label><input type="number" name="sort_order" value="<?= (int) $category['sort_order'] ?>" min="0"></div>
                            <label class="checkbox-field checkbox-field--inline"><input type="checkbox" name="is_active" value="1" <?= (int) $category['is_active'] ? 'checked' : '' ?>><span>Aktif di katalog</span></label>
                        </div>
                        <button type="submit" class="button button--dark button--sm">Simpan Kategori</button>
                    </form>
                </details>
            <?php endforeach; ?>
        </div>

        <details class="admin-category admin-category--new">
            <summary>
                <span class="admin-category__add"><?= icon('plus') ?></span>
                <span class="admin-category__name"><strong>Tambah kategori baru</strong><small>Untuk pengembangan katalog</small></span>
                <?= icon('chevron-down') ?>
            </summary>
            <form action="<?= url('/admin/kategori') ?>" method="post" class="admin-category__form">
                <?= csrf_field() ?>
                <div class="field-row">
                    <div class="field"><label>Nama</label><input type="text" name="name" required></div>
                    <div class="field"><label>Slug</label><input type="text" name="slug" pattern="[a-z0-9-]+" placeholder="nama-kategori" required></div>
                </div>
                <div class="field"><label>Deskripsi</label><textarea name="description" rows="2"></textarea></div>
                <div class="field-row">
                    <div class="field"><label>Gambar</label><input type="text" name="image" placeholder="assets/images/products/…"></div>
                    <div class="field"><label>Jenis</label><select name="type"><?php foreach ($typeOptions as $value => $label): ?><option value="<?= e($value) ?>"><?= e($label) ?></option><?php endforeach; ?></select></div>
                </div>
                <div class="field-row">
                    <div class="field"><label>Urutan</label><input type="number" name="sort_order" value="6" min="0"></div>
                    <label class="checkbox-field checkbox-field--inline"><input type="checkbox" name="is_active" value="1" checked><span>Aktif di katalog</span></label>
                </div>
                <button type="submit" class="button button--dark button--sm">Tambah Kategori</button>
            </form>
        </details>
    </section>

    <section class="admin-panel" id="laporan">
        <div class="admin-panel__head">
            <div><span class="eyebrow">Moderasi komunitas</span><h2>Laporan pengguna</h2></div>
            <span class="admin-panel__badge"><?= count($reports) ?> laporan</span>
        </div>
        <?php if ($reports): ?>
            <div class="admin-report-table">
                <?php foreach ($reports as $report): ?>
                    <article class="admin-report-row">
                        <div class="admin-report-row__main">
                            <span class="admin-report-row__icon"><?= icon('alert') ?></span>
                            <div>
                                <div class="admin-report-row__meta"><span class="status <?= $report['status'] === 'baru' ? 'status--waiting' : ($report['status'] === 'diproses' ? 'status--processing' : 'status--done') ?>"><?= e(ucfirst($report['status'])) ?></span><time><?= e(tanggal($report['created_at'], true)) ?></time></div>
                                <strong><?= e($report['reason']) ?></strong>
                                <p><?= e($report['details'] ?: 'Tanpa detail tambahan.') ?></p>
                                <span class="admin-report-row__context">Pelapor: <?= e($report['reporter_name']) ?><?php if ($report['product_title']): ?> · Produk: <?= e($report['product_title']) ?><?php endif; ?><?php if ($report['reported_user_name']): ?> · Terlapor: <?= e($report['reported_user_name']) ?><?php endif; ?></span>
                            </div>
                        </div>
                        <form action="<?= url('/admin/laporan/' . $report['id']) ?>" method="post" class="admin-report-row__form">
                            <?= csrf_field() ?>
                            <label class="sr-only" for="report-status-<?= (int) $report['id'] ?>">Status laporan</label>
                            <select id="report-status-<?= (int) $report['id'] ?>" name="status">
                                <?php foreach (['baru', 'diproses', 'selesai'] as $status): ?>
                                    <option value="<?= $status ?>" <?= $report['status'] === $status ? 'selected' : '' ?>><?= e(ucfirst($status)) ?></option>
                                <?php endforeach; ?>
                            </select>
                            <button type="submit" class="button button--ghost button--sm">Perbarui</button>
                        </form>
                    </article>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="admin-empty-state">
                <span><?= icon('shield') ?></span>
                <h2>Tidak ada laporan.</h2>
                <p>Komunitas sedang tenang. Laporan baru akan muncul di sini.</p>
            </div>
        <?php endif; ?>
    </section>
</section>
