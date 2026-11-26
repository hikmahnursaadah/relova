<section class="auth-section auth-section--login">
    <div class="auth-visual">
        <div class="auth-visual__copy">
            <span class="auth-visual__kicker">
                <strong>RELOVA</strong>
                <span aria-hidden="true">/</span>
                MEMBER ACCESS
            </span>
            <h2>Setiap Gaya Punya <span class="auth-visual__accent">Cerita Kedua</span>.</h2>
            <p class="auth-visual__desc">Masuk kembali dan lanjutkan perjalanan menemukan pakaian dengan cerita baru.</p>

            <figure class="auth-visual__figure">
                <img src="<?= asset('images/products/rak-netral.jpg') ?>" alt="Rak pakaian preloved dengan palet warna netral" loading="eager">
                <figcaption class="auth-visual__caption">PRELOVED. RELOVED. REWORN.</figcaption>
            </figure>
        </div>
    </div>

    <div class="auth-panel">
        <div class="auth-panel__inner">
            <span class="eyebrow eyebrow--plain">WELCOME BACK</span>
            <h1>Senang melihatmu kembali.</h1>
            <p class="auth-panel__lead">Masuk ke akun Relova untuk melanjutkan aktivitasmu.</p>

            <form action="<?= url('/masuk') ?>" method="post" class="form-stack auth-form">
                <?= csrf_field() ?>
                <input type="hidden" name="next" value="<?= e($next ?? '') ?>">

                <div class="field">
                    <label for="login-email">Email atau Username</label>
                    <input
                        id="login-email"
                        type="text"
                        name="email"
                        value="<?= old('email') ?>"
                        autocomplete="username"
                        placeholder="nama@contoh.id atau username"
                        required
                    >
                </div>

                <div class="field">
                    <label for="login-password">Kata sandi</label>
                    <div class="password-field">
                        <input
                            id="login-password"
                            type="password"
                            name="password"
                            autocomplete="current-password"
                            placeholder="Masukkan kata sandi"
                            required
                        >
                        <button type="button" class="password-toggle" data-password-toggle aria-label="Tampilkan kata sandi"><?= icon('eye') ?></button>
                    </div>
                </div>

                <div class="auth-form__meta">
                    <label class="checkbox-field" for="login-remember">
                        <input id="login-remember" type="checkbox" name="remember" value="1">
                        <span>Ingat saya</span>
                    </label>
                    <span
                        class="auth-forgot"
                        title="Fitur pemulihan kata sandi belum tersedia."
                        aria-label="Lupa password? Fitur pemulihan kata sandi belum tersedia."
                    >Lupa password?</span>
                </div>

                <button type="submit" class="button button--dark button--full">MASUK <?= icon('arrow-right') ?></button>
            </form>

            <p class="auth-switch">Belum punya akun? <a href="<?= url('/daftar') ?>">Daftar sekarang.</a></p>

            <div class="auth-divider">Akun demo</div>
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
