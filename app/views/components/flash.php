<?php $flashes = Session::pullFlash(); ?>
<?php if ($flashes): ?>
    <div class="toast-stack" aria-live="polite" aria-atomic="true">
        <?php foreach ($flashes as $flash): ?>
            <?php
            $type = in_array($flash['type'], ['success', 'error', 'info'], true) ? $flash['type'] : 'info';
            $iconName = $type === 'success' ? 'check' : ($type === 'error' ? 'alert' : 'info');
            ?>
            <div class="toast toast--<?= e($type) ?>" data-toast>
                <span class="toast__icon"><?= icon($iconName) ?></span>
                <p><?= e($flash['message']) ?></p>
                <button type="button" class="toast__close" data-toast-close aria-label="Tutup notifikasi"><?= icon('close') ?></button>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>
