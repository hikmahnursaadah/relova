<?php
$isSeller = $sellerStats !== null;
$isAdmin = Auth::isAdmin();
$rating = (float) ($reviewStats['average'] ?? 0);
$reviewTotal = (int) ($reviewStats['total'] ?? 0);
?>
<section class="dashboard-page profile-page">
    <header class="profile-header">
        <div class="profile-header__identity">
            <span class="avatar avatar--xl" style="--avatar-color: <?= e(avatar_color($user['name'])) ?>"><?= e(initials($user['name'])) ?></span>
            <div>
                <span class="eyebrow"><?= e(role_label($user['role'])) ?> · sejak <?= e(tanggal($user['created_at'])) ?></span>
                <h1><?= e($user['name']) ?></h1>
                <p>@<?= e($user['username']) ?> · <?= e($user['city'] ?: 'Indonesia') ?></p>
            </div>
        </div>
        <div class="profile-header__actions">
            <?php if (!$isAdmin): ?>
                <a href="<?= url('/pesanan') ?>" class="button button--ghost button--sm"><?= icon('box') ?> Pesanan</a>
                <a href="<?= url('/favorit') ?>" class="button button--ghost button--sm"><?= icon('heart') ?> Favorit</a>
            <?php endif; ?>
            <a href="<?= url('/notifikasi') ?>" class="button button--dark button--sm"><?= icon('bell') ?> Notifikasi</a>
        </div>
    </header>

    <?php if ($isSeller): ?>
        <div class="profile-seller-strip">
            <div class="profile-seller-strip__score">
                <strong><?= number_format($rating, 1, ',', '.') ?></strong>
                <?= star_markup($rating) ?>
                <span><?= $reviewTotal ?> ulasan</span>
            </div>
            <div class="profile-seller-strip__stat"><span>Produk aktif</span><strong><?= (int) ($sellerStats['active_count'] ?? 0) ?></strong></div>
            <div class="profile-seller-strip__stat"><span>Terjual</span><strong><?= (int) ($sellerStats['sold_count'] ?? 0) ?></strong></div>
            <div class="profile-seller-strip__stat"><span>Dilihat</span><strong><?= number_format((int) ($sellerStats['total_views'] ?? 0), 0, ',', '.') ?></strong></div>
            <a href="<?= url('/penjual') ?>" class="button button--dark button--sm">Buka Dasbor Penjual <?= icon('arrow-up-right') ?></a>
        </div>
    <?php endif; ?>

    <nav class="profile-tabs" aria-label="Bagian profil">
        <a href="#identitas" class="is-active">Identitas</a>
        <a href="#keamanan">Keamanan</a>
        <?php if ($isSeller): ?><a href="#ulasan">Ulasan</a><?php endif; ?>
        <a href="#akun">Akun & bantuan</a>
    </nav>

    <div class="profile-layout">
        <div class="profile-main">
            <section class="panel-card" id="identitas">
                <div class="panel-card__head">
                    <div><span class="eyebrow">Publik & pengiriman</span><h2>Identitas akun</h2></div>
                    <span class="panel-card__hint">Terlihat oleh pengguna lain</span>
                </div>
                <form action="<?= url('/profil') ?>" method="post" class="form-stack">
                    <?= csrf_field() ?>
                    <div class="field-row">
                        <div class="field">
                            <label for="profile-name">Nama lengkap</label>
                            <input id="profile-name" type="text" name="name" value="<?= old('name', $user['name']) ?>" minlength="2" required>
                        </div>
                        <div class="field">
                            <label for="profile-username">Nama pengguna</label>
                            <input id="profile-username" type="text" name="username" value="<?= old('username', $user['username']) ?>" pattern="[a-z0-9._]{3,20}" required>
                            <small class="field-help">Huruf kecil, angka, titik, atau garis bawah.</small>
                        </div>
                    </div>
                    <div class="field-row">
                        <div class="field">
                            <label for="profile-email">Email</label>
                            <input id="profile-email" type="email" name="email" value="<?= old('email', $user['email']) ?>" required>
                        </div>
                        <div class="field">
                            <label for="profile-phone">Nomor telepon <span class="optional">(opsional)</span></label>
                            <input id="profile-phone" type="tel" name="phone" value="<?= old('phone', $user['phone'] ?? '') ?>" placeholder="0812 …">
                        </div>
                    </div>
                    <div class="field-row">
                        <div class="field">
                            <label for="profile-city">Kota</label>
                            <input id="profile-city" type="text" name="city" value="<?= old('city', $user['city'] ?? '') ?>" placeholder="Bandung, Jakarta, …">
                        </div>
                        <div class="field">
                            <label for="profile-bio">Bio singkat</label>
                            <input id="profile-bio" type="text" name="bio" value="<?= old('bio', $user['bio'] ?? '') ?>" maxlength="280" placeholder="Ceritakan gaya atau caramu memilih barang.">
                        </div>
                    </div>
                    <div class="field">
                        <label for="profile-address">Alamat pengiriman utama</label>
                        <textarea id="profile-address" name="address" rows="3" placeholder="Nama jalan, nomor rumah, kelurahan, kecamatan, kota, kode pos"><?= old('address', $user['address'] ?? '') ?></textarea>
                        <small class="field-help">Dipakai sebagai isian awal saat checkout. Hanya dibagikan kepada penjual terkait.</small>
                    </div>
                    <div class="form-actions">
                        <button type="submit" class="button button--dark">Simpan Profil <?= icon('arrow-right') ?></button>
                    </div>
                </form>
            </section>

            <section class="panel-card" id="keamanan">
                <div class="panel-card__head">
                    <div><span class="eyebrow">Kata sandi</span><h2>Keamanan akun</h2></div>
                    <span class="panel-card__hint"><?= icon('shield') ?> Terenkripsi</span>
                </div>
                <form action="<?= url('/profil/kata-sandi') ?>" method="post" class="form-stack">
                    <?= csrf_field() ?>
                    <div class="field">
                        <label for="current-password">Kata sandi saat ini</label>
                        <div class="password-field">
                            <input id="current-password" type="password" name="current_password" autocomplete="current-password" required>
                            <button type="button" class="password-toggle" data-password-toggle aria-label="Tampilkan kata sandi"><?= icon('eye') ?></button>
                        </div>
                    </div>
                    <div class="field-row">
                        <div class="field">
                            <label for="new-password">Kata sandi baru</label>
                            <input id="new-password" type="password" name="new_password" minlength="8" autocomplete="new-password" required>
                        </div>
                        <div class="field">
                            <label for="password-confirmation">Ulangi kata sandi baru</label>
                            <input id="password-confirmation" type="password" name="password_confirmation" minlength="8" autocomplete="new-password" required>
                        </div>
                    </div>
                    <div class="form-actions">
                        <button type="submit" class="button button--ghost">Perbarui Kata Sandi <?= icon('arrow-right') ?></button>
                    </div>
                </form>
            </section>

            <?php if ($isSeller): ?>
                <section class="panel-card" id="ulasan">
                    <div class="panel-card__head">
                        <div><span class="eyebrow">Dari pembeli</span><h2>Ulasan tentangmu</h2></div>
                        <a href="<?= url('/penjual') ?>" class="text-link">Dasbor penjual <?= icon('arrow-up-right') ?></a>
                    </div>
                    <?php if ($reviews): ?>
                        <div class="profile-review-list">
                            <?php foreach ($reviews as $review): ?>
                                <article class="profile-review">
                                    <div class="profile-review__head">
                                        <span class="avatar avatar--sm" style="--avatar-color: <?= e(avatar_color($review['buyer_name'])) ?>"><?= e(initials($review['buyer_name'])) ?></span>
                                        <div><strong><?= e($review['buyer_name']) ?></strong><small><?= e($review['product_title']) ?> · <?= e(tanggal($review['created_at'])) ?></small></div>
                                        <?= star_markup((int) $review['rating']) ?>
                                    </div>
                                    <p>“<?= e($review['comment'] ?: 'Transaksi berjalan baik dan barang sesuai deskripsi.') ?>”</p>
                                </article>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="panel-empty-block">
                            <span><?= icon('star') ?></span>
                            <p>Ulasan akan muncul setelah pembeli menyelesaikan transaksi denganmu.</p>
                        </div>
                    <?php endif; ?>
                </section>
            <?php endif; ?>
        </div>

        <aside class="profile-aside">
            <section class="panel-card profile-summary">
                <span class="eyebrow">Ringkasan</span>
                <div class="profile-summary__row"><span>Status akun</span><span class="status status--done">Aktif</span></div>
                <div class="profile-summary__row"><span>Peran</span><strong><?= e(role_label($user['role'])) ?></strong></div>
                <div class="profile-summary__row"><span>Bergabung</span><strong><?= e(tanggal($user['created_at'])) ?></strong></div>
                <div class="profile-summary__row"><span>Kota</span><strong><?= e($user['city'] ?: 'Belum diisi') ?></strong></div>
                <div class="profile-summary__row"><span>Email</span><strong class="profile-summary__email"><?= e($user['email']) ?></strong></div>
            </section>

            <section class="panel-card" id="akun">
                <span class="eyebrow">Bantuan & aktivitas</span>
                <div class="profile-links">
                    <a href="<?= url('/notifikasi') ?>"><?= icon('bell') ?><span>Notifikasi</span><?= icon('chevron-right') ?></a>
                    <a href="<?= url('/chat') ?>"><?= icon('chat') ?><span>Pesan</span><?= icon('chevron-right') ?></a>
                    <?php if (!$isAdmin): ?>
                        <a href="<?= url('/pesanan') ?>"><?= icon('box') ?><span>Pesanan saya</span><?= icon('chevron-right') ?></a>
                        <a href="<?= url('/favorit') ?>"><?= icon('heart') ?><span>Favorit</span><?= icon('chevron-right') ?></a>
                    <?php endif; ?>
                    <a href="<?= url('/tentang') ?>"><?= icon('info') ?><span>Tentang re:wear</span><?= icon('chevron-right') ?></a>
                </div>
            </section>

            <section class="panel-card panel-card--accent profile-help">
                <span class="eyebrow">Butuh bantuan?</span>
                <h2>Kami membaca setiap pesan.</h2>
                <p>Jika ada masalah dengan pesanan atau akun, hubungi tim re:wear melalui email.</p>
                <a href="mailto:<?= e((new SettingModel())->get('contact_email', 'halo@rewear.id')) ?>" class="text-link">Kirim email <?= icon('arrow-up-right') ?></a>
            </section>

            <form action="<?= url('/keluar') ?>" method="post" class="profile-logout">
                <?= csrf_field() ?>
                <button type="submit" class="button button--danger-outline button--full"><?= icon('logout') ?> Keluar dari Akun</button>
            </form>
        </aside>
    </div>
</section>
