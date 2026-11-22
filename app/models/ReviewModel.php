<?php

class ReviewModel extends Model
{
    public function create(int $orderId, int $productId, int $buyerId, int $sellerId, int $rating, string $comment): int
    {
        $stmt = $this->db->prepare(
            'INSERT INTO reviews (order_id, product_id, buyer_id, seller_id, rating, comment) VALUES (?, ?, ?, ?, ?, ?)'
        );
        $stmt->execute([$orderId, $productId, $buyerId, $sellerId, max(1, min(5, $rating)), $comment ?: null]);
        return (int) $this->db->lastInsertId();
    }

    public function forProduct(int $productId): array
    {
        $stmt = $this->db->prepare(
            "SELECT r.*, u.name AS buyer_name, u.city AS buyer_city
             FROM reviews r JOIN users u ON u.id = r.buyer_id
             WHERE r.product_id = ? ORDER BY r.created_at DESC"
        );
        $stmt->execute([$productId]);
        return $stmt->fetchAll();
    }

    public function statsForProduct(int $productId): array
    {
        $stmt = $this->db->prepare('SELECT COUNT(*) AS total, COALESCE(AVG(rating), 0) AS average FROM reviews WHERE product_id = ?');
        $stmt->execute([$productId]);
        return $stmt->fetch();
    }

    public function forSeller(int $sellerId, int $limit = 10): array
    {
        $stmt = $this->db->prepare(
            "SELECT r.*, u.name AS buyer_name, p.title AS product_title
             FROM reviews r JOIN users u ON u.id = r.buyer_id JOIN products p ON p.id = r.product_id
             WHERE r.seller_id = ? ORDER BY r.created_at DESC LIMIT {$limit}"
        );
        $stmt->execute([$sellerId]);
        return $stmt->fetchAll();
    }

    public function statsForSeller(int $sellerId): array
    {
        $stmt = $this->db->prepare('SELECT COUNT(*) AS total, COALESCE(AVG(rating), 0) AS average FROM reviews WHERE seller_id = ?');
        $stmt->execute([$sellerId]);
        return $stmt->fetch();
    }

    public function existsForOrderProduct(int $orderId, int $productId): bool
    {
        $stmt = $this->db->prepare('SELECT COUNT(*) FROM reviews WHERE order_id = ? AND product_id = ?');
        $stmt->execute([$orderId, $productId]);
        return (int) $stmt->fetchColumn() > 0;
    }
}
