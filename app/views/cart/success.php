<section class="success-page">
    <div class="success-page__inner">
        <span class="success-page__icon"><?= icon('check') ?></span>
        <span class="eyebrow">Pesanan tercatat</span>
        <h1>Cerita baru sedang<br><em>disiapkan.</em></h1>
        <p>Pesananmu sudah diteruskan ke penjual. Pantau status dan lanjutkan komunikasi melalui pesan sampai barang tiba di tanganmu.</p>
        <div class="success-page__orders">
            <?php foreach ($orderIds as $orderId): ?>
                <?php $order = (new OrderModel())->find((int) $orderId); ?>
                <?php if ($order): ?>
                    <a href="<?= url('/pesanan/' . $order['id']) ?>" class="success-order">
                        <span class="success-order__icon"><?= icon('box') ?></span>
                        <span><small>Nomor pesanan</small><strong><?= e($order['order_number']) ?></strong></span>
                        <span class="status <?= e(order_status_class($order['status'])) ?>"><?= e(order_status_label($order['status'])) ?></span>
                        <?= icon('arrow-right') ?>
                    </a>
                <?php endif; ?>
            <?php endforeach; ?>
        </div>
        <div class="success-page__actions">
            <a href="<?= url('/pesanan') ?>" class="button button--dark">Lihat Pesanan Saya <?= icon('arrow-right') ?></a>
            <a href="<?= url('/jelajahi') ?>" class="button button--ghost">Lanjut Menjelajah</a>
        </div>
        <div class="success-page__note"><?= icon('info') ?> Pembayaran dan pengiriman disepakati langsung dengan penjual. Relova hanya mencatat pesanan dan status transaksi.</div>
    </div>
</section>
