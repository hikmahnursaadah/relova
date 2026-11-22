<section class="auth-section">
    <div class="auth-visual">
        <img src="<?= asset('images/products/rak-netral.jpg') ?>" alt="Rak pakaian bekas terawat dengan warna netral" loading="eager">
        <div class="auth-visual__overlay">
            <span class="eyebrow eyebrow--light">Kembali ke lemari</span>
            <p>Barang yang tepat mungkin sudah menunggumu.</p>
            <span class="auth-visual__index">RW / 02</span>
        </div>
    </div>
    <div class="auth-panel">
        <div class="auth-panel__inner">
            <span class="eyebrow">Selamat datang kembali</span>
            <h1>Masuk ke <em>re:wear.</em></h1>
            <p class="auth-panel__lead">Lanjutkan belanja, kelola pesanan, atau mulai jualan dari akun yang sama.</p>

            <form action="<?= url('/masuk') ?>" method="post" class="form-stack auth-form">
                <?= csrf_field() ?>
                <input type="hidden" name="next" value="<?= e($next ?? '') ?>">
                <div class="field">
                    <label for="login-email">Surel</label>
                    <input id="login-email" type="email" name="email" value="<?= old('email') ?>" autocomplete="email" placeholder="nama@contoh.id" required>
                </div>
                <div class="field">
                    <label for="login-password">Kata sandi</label>
                    <div class="password-field">
                        <input id="login-password" type="password" name="password" autocomplete="current-password" placeholder="Minimal 8 karakter" required>
                        <button type="button" class="password-toggle" data-password-toggle aria-label="Tampilkan kata sandi"><?= icon('eye') ?></button>
                    </div>
                </div>
                <button type="submit" class="button button--dark button--full">Masuk <?= icon('arrow-right') ?></button>
            </form>

            <p class="auth-switch">Belum punya akun? <a href="<?= url('/daftar') ?>">Buat akun baru</a></p>

            <details class="demo-accounts">
                <summary>Akun demo untuk menjelajah</summary>
                <div class="demo-accounts__grid">
                    <button type="button" data-demo-email="raka@rewear.id" data-demo-password="rewear123">
                        <span>Pembeli</span><small>raka@rewear.id</small>
                    </button>
                    <button type="button" data-demo-email="alya@rewear.id" data-demo-password="rewear123">
                        <span>Penjual</span><small>alya@rewear.id</small>
                    </button>
                    <button type="button" data-demo-email="admin@rewear.id" data-demo-password="admin123">
                        <span>Administrator</span><small>admin@rewear.id</small>
                    </button>
                </div>
                <small class="demo-accounts__hint">Pilih salah satu untuk mengisi formulir secara otomatis.</small>
            </details>
        </div>
    </div>
</section>
