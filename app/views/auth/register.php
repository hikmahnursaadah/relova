<section class="auth-section auth-section--register">
    <div class="auth-visual auth-visual--register">
        <img src="<?= asset('images/products/flatlay-putih.jpg') ?>" alt="Kemeja putih, jeans, dan sepatu yang ditata rapi" loading="eager">
        <div class="auth-visual__overlay">
            <span class="eyebrow eyebrow--light">Satu akun, dua peran</span>
            <p>Beli untuk melengkapi gaya. Jual untuk melanjutkan cerita.</p>
            <span class="auth-visual__index">RW / 03</span>
        </div>
    </div>
    <div class="auth-panel">
        <div class="auth-panel__inner auth-panel__inner--wide">
            <span class="eyebrow">Bergabung dengan re:wear</span>
            <h1>Buat ruang untuk<br><em>cerita berikutnya.</em></h1>
            <p class="auth-panel__lead">Satu akun untuk membeli dan menjual pakaian serta sepatu bekas terawat.</p>

            <form action="<?= url('/daftar') ?>" method="post" class="form-stack auth-form">
                <?= csrf_field() ?>
                <div class="field-row">
                    <div class="field">
                        <label for="register-name">Nama lengkap</label>
                        <input id="register-name" type="text" name="name" value="<?= old('name') ?>" autocomplete="name" placeholder="Nama kamu" required>
                    </div>
                    <div class="field">
                        <label for="register-username">Nama pengguna</label>
                        <input id="register-username" type="text" name="username" value="<?= old('username') ?>" autocomplete="username" placeholder="huruf kecil, tanpa spasi" pattern="[a-z0-9._]{3,20}" required>
                    </div>
                </div>
                <div class="field-row">
                    <div class="field">
                        <label for="register-email">Surel</label>
                        <input id="register-email" type="email" name="email" value="<?= old('email') ?>" autocomplete="email" placeholder="nama@contoh.id" required>
                    </div>
                    <div class="field">
                        <label for="register-city">Kota</label>
                        <input id="register-city" type="text" name="city" value="<?= old('city') ?>" autocomplete="address-level2" placeholder="Bandung, Jakarta, …">
                    </div>
                </div>
                <div class="field-row">
                    <div class="field">
                        <label for="register-password">Kata sandi</label>
                        <div class="password-field">
                            <input id="register-password" type="password" name="password" autocomplete="new-password" placeholder="Minimal 8 karakter" minlength="8" required>
                            <button type="button" class="password-toggle" data-password-toggle aria-label="Tampilkan kata sandi"><?= icon('eye') ?></button>
                        </div>
                    </div>
                    <div class="field">
                        <label for="register-confirmation">Ulangi kata sandi</label>
                        <input id="register-confirmation" type="password" name="password_confirmation" autocomplete="new-password" placeholder="Ketik ulang" minlength="8" required>
                    </div>
                </div>
                <label class="checkbox-field">
                    <input type="checkbox" name="terms" value="1" required>
                    <span>Saya menyetujui ketentuan komunitas dan bersedia mengunggah foto barang yang sebenarnya.</span>
                </label>
                <button type="submit" class="button button--dark button--full">Buat Akun <?= icon('arrow-right') ?></button>
            </form>

            <p class="auth-switch">Sudah punya akun? <a href="<?= url('/masuk') ?>">Masuk di sini</a></p>
            <p class="auth-note">Dengan membuat akun, kamu dapat membeli dan menjual menggunakan profil yang sama.</p>
        </div>
    </div>
</section>
