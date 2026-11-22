<?php if (!empty($pageScripts) && is_array($pageScripts)): ?>
    <?php foreach ($pageScripts as $script): ?>
        <script src="<?= asset('js/' . ltrim($script, '/')) ?>" defer></script>
    <?php endforeach; ?>
<?php endif; ?>
</body>
</html>
