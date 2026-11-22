<?php
$userId = Auth::id();
$conversationCount = count($conversations);
?>
<section class="dashboard-page chat-page">
    <header class="dashboard-page__head chat-page__head">
        <div>
            <span class="eyebrow">Percakapan</span>
            <h1>Pesan <em>(<?= $conversationCount ?>)</em></h1>
            <p class="dashboard-page__sub">Tanyakan detail, sepakati ongkir, dan jaga komunikasi tetap hangat.</p>
        </div>
        <a href="<?= url('/jelajahi') ?>" class="text-link">Cari barang lain <?= icon('arrow-right') ?></a>
    </header>

    <div class="chat-shell <?= $activeChat ? 'has-active' : '' ?>">
        <aside class="chat-list-panel">
            <div class="chat-list-panel__head">
                <span class="eyebrow">Semua percakapan</span>
                <span class="chat-list-panel__count"><?= $conversationCount ?></span>
            </div>
            <?php if ($conversations): ?>
                <div class="chat-conversations">
                    <?php foreach ($conversations as $conversation): ?>
                        <?php
                        $isBuyer = (int) $conversation['buyer_id'] === $userId;
                        $otherName = $isBuyer ? $conversation['seller_name'] : $conversation['buyer_name'];
                        $isActive = $activeChat && (int) $activeChat['id'] === (int) $conversation['id'];
                        ?>
                        <a href="<?= url('/chat/' . $conversation['id']) ?>" class="chat-conversation <?= $isActive ? 'is-active' : '' ?> <?= (int) $conversation['unread_count'] > 0 ? 'has-unread' : '' ?>">
                            <img src="<?= e(product_image($conversation['product_image'])) ?>" alt="" class="chat-conversation__image">
                            <span class="chat-conversation__copy">
                                <span class="chat-conversation__top">
                                    <strong><?= e($otherName) ?></strong>
                                    <time><?= e(waktu_relatif($conversation['last_message_at'] ?: $conversation['updated_at'])) ?></time>
                                </span>
                                <small><?= e($conversation['product_title']) ?></small>
                                <span class="chat-conversation__preview"><?= e(excerpt($conversation['last_message'] ?: 'Mulai percakapan…', 46)) ?></span>
                            </span>
                            <?php if ((int) $conversation['unread_count'] > 0): ?><span class="chat-conversation__unread"><?= (int) $conversation['unread_count'] ?></span><?php endif; ?>
                        </a>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="chat-list-empty">
                    <span class="empty-state__icon"><?= icon('chat') ?></span>
                    <h2>Belum ada percakapan.</h2>
                    <p>Buka produk yang kamu minati, lalu tanyakan langsung kepada penjual.</p>
                    <a href="<?= url('/jelajahi') ?>" class="button button--dark button--sm">Jelajahi Produk</a>
                </div>
            <?php endif; ?>
        </aside>

        <section class="chat-thread-panel">
            <?php if ($activeChat): ?>
                <?php
                $isBuyer = (int) $activeChat['buyer_id'] === $userId;
                $otherName = $isBuyer ? $activeChat['seller_name'] : $activeChat['buyer_name'];
                $otherRole = $isBuyer ? 'Penjual' : 'Pembeli';
                ?>
                <header class="chat-thread-head">
                    <a href="<?= url('/chat') ?>" class="icon-button chat-thread-back" aria-label="Kembali ke daftar pesan"><?= icon('arrow-left') ?></a>
                    <span class="avatar avatar--sm" style="--avatar-color: <?= e(avatar_color($otherName)) ?>"><?= e(initials($otherName)) ?></span>
                    <div>
                        <strong><?= e($otherName) ?></strong>
                        <span><?= e($otherRole) ?> · produk ini</span>
                    </div>
                    <a href="<?= url('/produk/' . $activeChat['product_id']) ?>" class="text-link chat-thread-product-link">Lihat produk <?= icon('arrow-up-right') ?></a>
                </header>

                <div class="chat-product-strip">
                    <img src="<?= e(product_image($activeChat['product_image'])) ?>" alt="">
                    <div>
                        <span class="eyebrow">Tentang produk</span>
                        <strong><?= e($activeChat['product_title']) ?></strong>
                        <span><?= rupiah($activeChat['product_price']) ?> · <?= $activeChat['product_status'] === 'terjual' ? 'Sudah terjual' : 'Masih tayang' ?></span>
                    </div>
                    <a href="<?= url('/produk/' . $activeChat['product_id']) ?>" class="button button--ghost button--sm">Buka Produk</a>
                </div>

                <div class="chat-messages" data-chat-messages>
                    <?php if (!$messages): ?>
                        <div class="chat-messages__empty">
                            <span><?= icon('sparkle') ?></span>
                            <p>Belum ada pesan. Sapa penjual dengan pertanyaan yang jelas dan spesifik.</p>
                        </div>
                    <?php endif; ?>
                    <?php $lastDate = ''; ?>
                    <?php foreach ($messages as $message): ?>
                        <?php
                        $messageDate = tanggal($message['created_at']);
                        $isMine = (int) $message['sender_id'] === $userId;
                        ?>
                        <?php if ($messageDate !== $lastDate): ?>
                            <div class="chat-date-separator"><span><?= e($messageDate) ?></span></div>
                            <?php $lastDate = $messageDate; ?>
                        <?php endif; ?>
                        <div class="chat-message <?= $isMine ? 'is-mine' : 'is-theirs' ?>">
                            <?php if (!$isMine): ?><span class="avatar avatar--xs" style="--avatar-color: <?= e(avatar_color($message['sender_name'])) ?>"><?= e(initials($message['sender_name'])) ?></span><?php endif; ?>
                            <div class="chat-bubble">
                                <p><?= nl2br(e($message['message'])) ?></p>
                                <time><?= date('H.i', strtotime($message['created_at'])) ?></time>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <form action="<?= url('/chat/' . $activeChat['id'] . '/kirim') ?>" method="post" class="chat-compose" data-chat-form>
                    <?= csrf_field() ?>
                    <label class="sr-only" for="chat-message-input">Tulis pesan</label>
                    <textarea id="chat-message-input" name="message" rows="2" maxlength="1000" placeholder="Tulis pesan untuk <?= e($otherName) ?>…" required data-chat-input></textarea>
                    <div class="chat-compose__foot">
                        <span class="chat-compose__hint"><?= icon('shield') ?> Tetap sopan. Jangan bagikan data sensitif.</span>
                        <button type="submit" class="button button--dark button--sm">Kirim <?= icon('arrow-right') ?></button>
                    </div>
                </form>
            <?php else: ?>
                <div class="chat-placeholder">
                    <div class="chat-placeholder__mark">
                        <span class="chat-placeholder__ring"></span>
                        <?= icon('chat') ?>
                    </div>
                    <span class="eyebrow">Ruang percakapan</span>
                    <h2>Pilih satu percakapan<br><em>untuk mulai.</em></h2>
                    <p>Pertanyaan yang baik tentang ukuran, bahan, atau ongkir membuat transaksi preloved terasa lebih tenang.</p>
                    <?php if ($conversations): ?>
                        <a href="<?= url('/chat/' . $conversations[0]['id']) ?>" class="button button--dark">Buka Pesan Terakhir <?= icon('arrow-right') ?></a>
                    <?php else: ?>
                        <a href="<?= url('/jelajahi') ?>" class="button button--dark">Temukan Produk <?= icon('arrow-right') ?></a>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </section>

        <?php if ($activeChat): ?>
            <aside class="chat-context-panel">
                <span class="eyebrow">Konteks transaksi</span>
                <div class="chat-context-panel__product">
                    <img src="<?= e(product_image($activeChat['product_image'])) ?>" alt="">
                    <strong><?= e($activeChat['product_title']) ?></strong>
                    <span><?= rupiah($activeChat['product_price']) ?></span>
                </div>
                <div class="chat-context-panel__facts">
                    <div><span>Pembeli</span><strong><?= e($activeChat['buyer_name']) ?></strong></div>
                    <div><span>Penjual</span><strong><?= e($activeChat['seller_name']) ?></strong></div>
                </div>
                <div class="chat-context-panel__note">
                    <span><?= icon('info') ?></span>
                    <p>Ongkir, pembayaran, dan pengiriman disepakati langsung di percakapan ini. re:wear hanya mencatat pesanan.</p>
                </div>
                <a href="<?= url('/pesanan') ?>" class="text-link">Lihat pesanan saya <?= icon('arrow-right') ?></a>
            </aside>
        <?php endif; ?>
    </div>
</section>
