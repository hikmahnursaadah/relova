<?php

class WishlistModel extends Model
{
    public function toggle(int $userId, int $productId): bool
    {
        $stmt = $this->db->prepare('SELECT id FROM wishlists WHERE user_id = ? AND product_id = ?');
        $stmt->execute([$userId, $productId]);
        $existing = $stmt->fetchColumn();
        if ($existing) {
            $this->db->prepare('DELETE FROM wishlists WHERE id = ?')->execute([$existing]);
            return false;
        }
        $this->db->prepare('INSERT INTO wishlists (user_id, product_id) VALUES (?, ?)')->execute([$userId, $productId]);
        return true;
    }

    public function exists(int $userId, int $productId): bool
    {
        $stmt = $this->db->prepare('SELECT COUNT(*) FROM wishlists WHERE user_id = ? AND product_id = ?');
        $stmt->execute([$userId, $productId]);
        return (int) $stmt->fetchColumn() > 0;
    }

    public function ids(int $userId): array
    {
        $stmt = $this->db->prepare('SELECT product_id FROM wishlists WHERE user_id = ?');
        $stmt->execute([$userId]);
        return array_map('intval', $stmt->fetchAll(PDO::FETCH_COLUMN));
    }

    public function count(int $userId): int
    {
        $stmt = $this->db->prepare('SELECT COUNT(*) FROM wishlists WHERE user_id = ?');
        $stmt->execute([$userId]);
        return (int) $stmt->fetchColumn();
    }

    public function items(int $userId): array
    {
        $stmt = $this->db->prepare(
            "SELECT p.*, c.name AS category_name, c.slug AS category_slug,
                u.name AS seller_name, u.username AS seller_username, u.city AS seller_city,
                w.created_at AS saved_at,
                (SELECT pi.image_path FROM product_images pi WHERE pi.product_id = p.id ORDER BY pi.is_primary DESC, pi.sort_order ASC LIMIT 1) AS primary_image,
                (SELECT COALESCE(AVG(r.rating), 0) FROM reviews r WHERE r.product_id = p.id) AS rating_avg,
                (SELECT COUNT(*) FROM reviews r WHERE r.product_id = p.id) AS review_count
             FROM wishlists w
             JOIN products p ON p.id = w.product_id
             JOIN categories c ON c.id = p.category_id
             JOIN users u ON u.id = p.seller_id
             WHERE w.user_id = ? AND p.status = 'aktif'
             ORDER BY w.created_at DESC"
        );
        $stmt->execute([$userId]);
        return $stmt->fetchAll();
    }

    public function deleteForProduct(int $productId): void
    {
        $this->db->prepare('DELETE FROM wishlists WHERE product_id = ?')->execute([$productId]);
    }
}
