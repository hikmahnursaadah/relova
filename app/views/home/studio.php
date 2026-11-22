<?php
$studioOutfits = [
    ['Kasual', 'Kaos katun oversize, denim straight, sneakers putih.'],
    ['Vintage', 'Gaun wrap terracotta dengan siluet yang jatuh lembut.'],
    ['Streetwear', 'Hoodie sage, cargo hitam, dan sneakers chunky.'],
    ['Minimalis', 'Atasan fit hitam, celana krem lebar, loafers.'],
    ['Outerwear', 'Trench camel di atas turtleneck dan sepatu bot.'],
];
?>
<section class="studio-hero" data-studio-page>
    <div class="studio-hero__head container">
        <span class="eyebrow" data-reveal>Studio 3D — re:wear</span>
        <div class="studio-hero__title-row">
            <h1 data-reveal>Coba dulu.<br><em>Kenali jatuhnya.</em></h1>
            <p data-reveal>Manekin digital kami memperlihatkan bagaimana sebuah potongan bergerak dan jatuh di tubuh. Gerakkan kursor untuk memutar, lalu pilih gaya untuk melihat tampilan lain.</p>
        </div>
    </div>

    <div class="studio-stage-wrap">
        <div class="studio-stage">
            <div class="studio-stage__meta">
                <span>MANEKIN 3D LANGSUNG</span>
                <span class="studio-stage__status"><i></i> Interaktif</span>
            </div>
            <div class="mannequin-canvas mannequin-canvas--studio" data-mannequin data-mode="studio" aria-label="Manekin 3D interaktif"></div>
            <div class="mannequin-fallback mannequin-fallback--studio" data-mannequin-fallback aria-hidden="true">
                <svg viewBox="0 0 260 620" role="img" aria-label="Ilustrasi manekin 3D">
                    <defs>
                        <linearGradient id="studioBody" x1="0" y1="0" x2="1" y2="1"><stop offset="0" stop-color="#faf6ee"/><stop offset="1" stop-color="#d7cdbd"/></linearGradient>
                        <linearGradient id="studioTop" x1="0" y1="0" x2="1" y2="1"><stop offset="0" stop-color="#7c8067"/><stop offset="1" stop-color="#5f6450"/></linearGradient>
                    </defs>
                    <ellipse cx="130" cy="596" rx="64" ry="10" fill="#1d1c18" opacity=".14"/>
                    <ellipse cx="130" cy="70" rx="31" ry="39" fill="url(#studioBody)"/>
                    <path d="M105 128h50l9 55h-68l9-55Z" fill="url(#studioTop)"/>
                    <path d="M75 173c-9 3-15 10-17 20l-11 78c-1 8 4 13 11 13 6 0 10-4 11-11l7-55 5 84h96l5-84 7 55c1 7 5 11 11 11 7 0 12-5 11-13l-11-78c-2-10-8-17-17-20l-27-11H102l-27 11Z" fill="url(#studioTop)"/>
                    <path d="M89 300h82l12 95-12 77h-32l3-92-10-1-10 1 3 92H93l-12-77 8-95Z" fill="#a7aab0"/>
                    <path d="M94 472h31l4 65-5 24H95l-6-24 5-65Zm44 0h31l5 65 5 24h-29l-5-24 4-65Z" fill="url(#studioBody)"/>
                    <path d="M88 558c-3 8 1 17 9 19h28c6 0 9-4 8-10l-3-9H88Zm54 0 3 10c1 6 4 9 10 9h21c7 0 11-6 9-12l-5-9-38 2Z" fill="#ebe3d6"/>
                    <path d="M82 350h97" stroke="#4f5360" stroke-width="2.5"/>
                    <circle cx="130" cy="26" r="2.4" fill="#c25a3e"/>
                </svg>
            </div>
            <div class="studio-stage__hint"><?= icon('rotate') ?><span>Gerakkan kursor untuk memutar manekin</span></div>
            <div class="studio-stage__floor" aria-hidden="true"></div>
        </div>

        <aside class="studio-panel">
            <div class="studio-panel__head">
                <span class="eyebrow">Pilih gaya</span>
                <span class="studio-panel__count">05 gaya</span>
            </div>
            <div class="studio-outfit-list">
                <?php foreach ($studioOutfits as $index => $outfit): ?>
                    <button type="button" class="studio-outfit <?= $index === 0 ? 'is-active' : '' ?>" data-studio-outfit="<?= $index ?>" aria-pressed="<?= $index === 0 ? 'true' : 'false' ?>">
                        <span class="studio-outfit__index"><?= str_pad((string) ($index + 1), 2, '0', STR_PAD_LEFT) ?></span>
                        <span class="studio-outfit__copy">
                            <strong><?= e($outfit[0]) ?></strong>
                            <small><?= e($outfit[1]) ?></small>
                        </span>
                        <span class="studio-outfit__arrow"><?= icon('arrow-up-right') ?></span>
                    </button>
                <?php endforeach; ?>
            </div>
            <div class="studio-panel__controls">
                <button type="button" class="text-link" data-studio-autoplay><?= icon('pause') ?><span>Hentikan otomatis</span></button>
                <span class="studio-panel__tip"><?= icon('sparkle') ?> Pergantian gaya berlangsung otomatis setiap beberapa detik.</span>
            </div>
        </aside>
    </div>

    <div class="container studio-notes">
        <div class="studio-note">
            <span class="eyebrow">01 / Interaksi</span>
            <h3>Seluruh tubuh merespons.</h3>
            <p>Bukan hanya kepala. Bahu, torso, dan keseimbangan tubuh ikut bergerak halus mengikuti arah kursor.</p>
        </div>
        <div class="studio-note">
            <span class="eyebrow">02 / Detail</span>
            <h3>Pakaian yang jatuh alami.</h3>
            <p>Setiap gaya dibuat mengikuti proporsi manekin, dari potongan atasan sampai sepatu.</p>
        </div>
        <div class="studio-note">
            <span class="eyebrow">03 / Performa</span>
            <h3>Ringan di semua layar.</h3>
            <p>Tampilan 3D menyesuaikan perangkat. Jika WebGL tidak tersedia, ilustrasi studio tetap dapat dilihat.</p>
        </div>
    </div>
</section>
