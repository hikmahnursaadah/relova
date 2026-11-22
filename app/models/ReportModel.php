<?php

class ReportModel extends Model
{
    public function create(int $reporterId, ?int $reportedUserId, ?int $productId, string $reason, string $details): int
    {
        $stmt = $this->db->prepare(
            'INSERT INTO reports (reporter_id, reported_user_id, product_id, reason, details) VALUES (?, ?, ?, ?, ?)'
        );
        $stmt->execute([$reporterId, $reportedUserId, $productId, $reason, $details ?: null]);
        return (int) $this->db->lastInsertId();
    }

    public function recent(int $limit = 5): array
    {
        $stmt = $this->db->prepare(
            "SELECT r.*, u.name AS reporter_name, p.title AS product_title, s.name AS reported_user_name
             FROM reports r
             JOIN users u ON u.id = r.reporter_id
             LEFT JOIN products p ON p.id = r.product_id
             LEFT JOIN users s ON s.id = r.reported_user_id
             ORDER BY r.created_at DESC LIMIT {$limit}"
        );
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function all(): array
    {
        return $this->db->query(
            "SELECT r.*, u.name AS reporter_name, p.title AS product_title, s.name AS reported_user_name
             FROM reports r
             JOIN users u ON u.id = r.reporter_id
             LEFT JOIN products p ON p.id = r.product_id
             LEFT JOIN users s ON s.id = r.reported_user_id
             ORDER BY CASE r.status WHEN 'baru' THEN 0 WHEN 'diproses' THEN 1 ELSE 2 END, r.created_at DESC"
        )->fetchAll();
    }

    public function countOpen(): int
    {
        return (int) $this->db->query("SELECT COUNT(*) FROM reports WHERE status != 'selesai'")->fetchColumn();
    }

    public function updateStatus(int $id, string $status): void
    {
        if (!in_array($status, ['baru', 'diproses', 'selesai'], true)) {
            return;
        }
        $stmt = $this->db->prepare('UPDATE reports SET status = ? WHERE id = ?');
        $stmt->execute([$status, $id]);
    }
}
