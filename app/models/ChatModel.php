<?php

class ChatModel extends Model
{
    public function findOrCreate(int $productId, int $buyerId, int $sellerId): int
    {
        $stmt = $this->db->prepare('SELECT id FROM chats WHERE product_id = ? AND buyer_id = ? AND seller_id = ?');
        $stmt->execute([$productId, $buyerId, $sellerId]);
        $id = $stmt->fetchColumn();
        if ($id) {
            return (int) $id;
        }
        $stmt = $this->db->prepare('INSERT INTO chats (product_id, buyer_id, seller_id) VALUES (?, ?, ?)');
        $stmt->execute([$productId, $buyerId, $sellerId]);
        return (int) $this->db->lastInsertId();
    }

    public function findForUser(int $id, int $userId): ?array
    {
        $stmt = $this->db->prepare(
            "SELECT c.*, p.title AS product_title, p.price AS product_price,
                (SELECT pi.image_path FROM product_images pi WHERE pi.product_id = p.id ORDER BY pi.is_primary DESC, pi.id ASC LIMIT 1) AS product_image,
                b.name AS buyer_name, s.name AS seller_name
             FROM chats c
             JOIN products p ON p.id = c.product_id
             JOIN users b ON b.id = c.buyer_id
             JOIN users s ON s.id = c.seller_id
             WHERE c.id = ? AND (c.buyer_id = ? OR c.seller_id = ?)"
        );
        $stmt->execute([$id, $userId, $userId]);
        $chat = $stmt->fetch();
        return $chat ?: null;
    }

    public function conversations(int $userId): array
    {
        $stmt = $this->db->prepare(
            "SELECT c.*, p.title AS product_title, p.status AS product_status,
                (SELECT pi.image_path FROM product_images pi WHERE pi.product_id = p.id ORDER BY pi.is_primary DESC, pi.id ASC LIMIT 1) AS product_image,
                b.name AS buyer_name, s.name AS seller_name,
                (SELECT m.message FROM chat_messages m WHERE m.chat_id = c.id ORDER BY m.created_at DESC, m.id DESC LIMIT 1) AS last_message,
                (SELECT m.created_at FROM chat_messages m WHERE m.chat_id = c.id ORDER BY m.created_at DESC, m.id DESC LIMIT 1) AS last_message_at,
                (SELECT COUNT(*) FROM chat_messages m WHERE m.chat_id = c.id AND m.sender_id != :uid AND m.is_read = 0) AS unread_count
             FROM chats c
             JOIN products p ON p.id = c.product_id
             JOIN users b ON b.id = c.buyer_id
             JOIN users s ON s.id = c.seller_id
             WHERE c.buyer_id = :uid OR c.seller_id = :uid
             ORDER BY COALESCE(last_message_at, c.updated_at) DESC"
        );
        $stmt->execute(['uid' => $userId]);
        return $stmt->fetchAll();
    }

    public function messages(int $chatId): array
    {
        $stmt = $this->db->prepare(
            'SELECT m.*, u.name AS sender_name FROM chat_messages m JOIN users u ON u.id = m.sender_id WHERE m.chat_id = ? ORDER BY m.created_at ASC, m.id ASC'
        );
        $stmt->execute([$chatId]);
        return $stmt->fetchAll();
    }

    public function send(int $chatId, int $senderId, string $message): int
    {
        $stmt = $this->db->prepare('INSERT INTO chat_messages (chat_id, sender_id, message) VALUES (?, ?, ?)');
        $stmt->execute([$chatId, $senderId, $message]);
        $messageId = (int) $this->db->lastInsertId();
        $this->db->prepare('UPDATE chats SET updated_at = CURRENT_TIMESTAMP WHERE id = ?')->execute([$chatId]);
        return $messageId;
    }

    public function markRead(int $chatId, int $userId): void
    {
        $stmt = $this->db->prepare('UPDATE chat_messages SET is_read = 1 WHERE chat_id = ? AND sender_id != ? AND is_read = 0');
        $stmt->execute([$chatId, $userId]);
    }

    public function unreadCount(int $userId): int
    {
        $stmt = $this->db->prepare(
            'SELECT COUNT(*) FROM chat_messages m JOIN chats c ON c.id = m.chat_id
             WHERE (c.buyer_id = ? OR c.seller_id = ?) AND m.sender_id != ? AND m.is_read = 0'
        );
        $stmt->execute([$userId, $userId, $userId]);
        return (int) $stmt->fetchColumn();
    }

    public function latestForProduct(int $productId, int $buyerId, int $sellerId): ?array
    {
        $stmt = $this->db->prepare('SELECT id FROM chats WHERE product_id = ? AND buyer_id = ? AND seller_id = ?');
        $stmt->execute([$productId, $buyerId, $sellerId]);
        $id = $stmt->fetchColumn();
        return $id ? $this->findForUser((int) $id, $buyerId) : null;
    }
}
