<?php
$pageScripts = $pageScripts ?? ['main.js', 'navbar.js', 'animations.js', 'products.js'];
if (in_array($page ?? '', ['home', 'studio'], true)) {
    $pageScripts[] = 'hero.js';
}
require __DIR__ . '/partials/head.php';
View::partial('components/navbar');
View::partial('components/flash');
?>
<main id="main-content" class="site-main">
    <?= $content ?>
</main>
<?php if (empty($hideFooter)): ?>
    <?php View::partial('components/footer'); ?>
<?php endif; ?>
<script src="<?= asset('js/vendor/gsap.min.js') ?>" defer></script>
<script src="<?= asset('js/vendor/ScrollTrigger.min.js') ?>" defer></script>
<?php require __DIR__ . '/partials/scripts.php'; ?>
