<?php

class NotificationModel extends Model
{
    public function add(int $userId, string $type, string $title, string $message, string $link = ''): void
    {
        $stmt = $this->db->prepare(
            'INSERT INTO notifications (user_id, type, title, message, link) VALUES (?, ?, ?, ?, ?)'
        );
        $stmt->execute([$userId, $type, $title, $message, $link ?: null]);
    }

    public function recent(int $userId, int $limit = 8): array
    {
        $stmt = $this->db->prepare('SELECT * FROM notifications WHERE user_id = ? ORDER BY created_at DESC, id DESC LIMIT ' . (int) $limit);
        $stmt->execute([$userId]);
        return $stmt->fetchAll();
    }

    public function all(int $userId, int $page = 1, int $perPage = 20): array
    {
        $offset = max(0, ($page - 1) * $perPage);
        $stmt = $this->db->prepare('SELECT * FROM notifications WHERE user_id = ? ORDER BY created_at DESC, id DESC LIMIT ? OFFSET ?');
        $stmt->bindValue(1, $userId, PDO::PARAM_INT);
        $stmt->bindValue(2, $perPage, PDO::PARAM_INT);
        $stmt->bindValue(3, $offset, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function unreadCount(int $userId): int
    {
        $stmt = $this->db->prepare('SELECT COUNT(*) FROM notifications WHERE user_id = ? AND is_read = 0');
        $stmt->execute([$userId]);
        return (int) $stmt->fetchColumn();
    }

    public function markAllRead(int $userId): void
    {
        $stmt = $this->db->prepare('UPDATE notifications SET is_read = 1 WHERE user_id = ?');
        $stmt->execute([$userId]);
    }

    public function markRead(int $id, int $userId): void
    {
        $stmt = $this->db->prepare('UPDATE notifications SET is_read = 1 WHERE id = ? AND user_id = ?');
        $stmt->execute([$id, $userId]);
    }
}
