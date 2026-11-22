<?php

class ProductModel extends Model
{
    private string $baseSelect = "
        SELECT p.*, c.name AS category_name, c.slug AS category_slug,
            u.name AS seller_name, u.username AS seller_username, u.city AS seller_city, u.role AS seller_role,
            (SELECT pi.image_path FROM product_images pi WHERE pi.product_id = p.id ORDER BY pi.is_primary DESC, pi.sort_order ASC, pi.id ASC LIMIT 1) AS primary_image,
            (SELECT COALESCE(AVG(r.rating), 0) FROM reviews r WHERE r.product_id = p.id) AS rating_avg,
            (SELECT COUNT(*) FROM reviews r WHERE r.product_id = p.id) AS review_count,
            (SELECT COUNT(*) FROM wishlists w WHERE w.product_id = p.id) AS wishlist_count
        FROM products p
        JOIN categories c ON c.id = p.category_id
        JOIN users u ON u.id = p.seller_id
    ";

    public function paginate(array $filters = [], int $page = 1, int $perPage = 12): array
    {
        [$where, $params] = $this->buildFilters($filters);
        $order = $this->buildSort($filters['sort'] ?? 'terbaru');
        $offset = max(0, ($page - 1) * $perPage);

        $countStmt = $this->db->prepare(
            "SELECT COUNT(*) FROM products p JOIN categories c ON c.id = p.category_id JOIN users u ON u.id = p.seller_id {$where}"
        );
        $countStmt->execute($params);
        $total = (int) $countStmt->fetchColumn();

        $stmt = $this->db->prepare(
            $this->baseSelect . " {$where} ORDER BY {$order} LIMIT {$perPage} OFFSET {$offset}"
        );
        $stmt->execute($params);

        return [
            'items' => $stmt->fetchAll(),
            'total' => $total,
            'page' => $page,
            'per_page' => $perPage,
            'pages' => max(1, (int) ceil($total / $perPage)),
        ];
    }

    private function buildFilters(array $filters): array
    {
        $conditions = ["p.status = 'aktif'", "u.status = 'aktif'", 'c.is_active = 1'];
        $params = [];

        if (!empty($filters['q'])) {
            $conditions[] = '(lower(p.title) LIKE :q OR lower(p.brand) LIKE :q OR lower(p.description) LIKE :q)';
            $params['q'] = '%' . mb_strtolower(trim($filters['q'])) . '%';
        }
        if (!empty($filters['category'])) {
            $slugs = is_array($filters['category']) ? $filters['category'] : [$filters['category']];
            $placeholders = [];
            foreach ($slugs as $i => $slug) {
                $key = 'cat' . $i;
                $placeholders[] = ':' . $key;
                $params[$key] = $slug;
            }
            $conditions[] = 'c.slug IN (' . implode(',', $placeholders) . ')';
        }
        if (!empty($filters['type'])) {
            $conditions[] = 'p.type = :type';
            $params['type'] = $filters['type'];
        }
        if (!empty($filters['size'])) {
            $conditions[] = 'p.size = :size';
            $params['size'] = $filters['size'];
        }
        if (!empty($filters['condition'])) {
            $conditions[] = 'p.item_condition = :condition';
            $params['condition'] = $filters['condition'];
        }
        if (isset($filters['min_price']) && $filters['min_price'] !== '') {
            $conditions[] = 'p.price >= :min_price';
            $params['min_price'] = (int) $filters['min_price'];
        }
        if (isset($filters['max_price']) && $filters['max_price'] !== '') {
            $conditions[] = 'p.price <= :max_price';
            $params['max_price'] = (int) $filters['max_price'];
        }
        if (!empty($filters['seller_id'])) {
            $conditions[] = 'p.seller_id = :seller_id';
            $params['seller_id'] = (int) $filters['seller_id'];
        }

        return ['WHERE ' . implode(' AND ', $conditions), $params];
    }

    private function buildSort(string $sort): string
    {
        return match ($sort) {
            'termurah' => 'p.price ASC, p.created_at DESC',
            'termahal' => 'p.price DESC, p.created_at DESC',
            'populer' => 'p.view_count DESC, p.created_at DESC',
            'rating' => 'rating_avg DESC, review_count DESC, p.created_at DESC',
            default => 'p.created_at DESC, p.id DESC',
        };
    }

    public function find(int $id): ?array
    {
        $stmt = $this->db->prepare($this->baseSelect . ' WHERE p.id = ?');
        $stmt->execute([$id]);
        $product = $stmt->fetch();
        return $product ?: null;
    }

    public function images(int $productId): array
    {
        $stmt = $this->db->prepare('SELECT * FROM product_images WHERE product_id = ? ORDER BY is_primary DESC, sort_order ASC, id ASC');
        $stmt->execute([$productId]);
        return $stmt->fetchAll();
    }

    public function create(int $sellerId, array $data, array $images): int
    {
        $this->db->beginTransaction();
        try {
            $slug = $this->uniqueSlug($data['title']);
            $stmt = $this->db->prepare(
                'INSERT INTO products (seller_id, category_id, title, slug, type, size, price, item_condition, description, brand, color, defect, status)
                 VALUES (:seller_id, :category_id, :title, :slug, :type, :size, :price, :item_condition, :description, :brand, :color, :defect, :status)'
            );
            $stmt->execute([
                'seller_id' => $sellerId,
                'category_id' => (int) $data['category_id'],
                'title' => $data['title'],
                'slug' => $slug,
                'type' => $data['type'],
                'size' => $data['size'],
                'price' => (int) $data['price'],
                'item_condition' => $data['condition'],
                'description' => $data['description'],
                'brand' => $data['brand'] ?: null,
                'color' => $data['color'] ?: null,
                'defect' => $data['defect'] ?: null,
                'status' => 'aktif',
            ]);
            $productId = (int) $this->db->lastInsertId();
            $this->insertImages($productId, $images);
            $this->db->commit();
            return $productId;
        } catch (Throwable $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    public function update(int $id, array $data, array $newImages = [], array $removeImageIds = []): void
    {
        $this->db->beginTransaction();
        try {
            $stmt = $this->db->prepare(
                'UPDATE products SET category_id = ?, title = ?, type = ?, size = ?, price = ?, item_condition = ?, description = ?, brand = ?, color = ?, defect = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?'
            );
            $stmt->execute([
                (int) $data['category_id'],
                $data['title'],
                $data['type'],
                $data['size'],
                (int) $data['price'],
                $data['condition'],
                $data['description'],
                $data['brand'] ?: null,
                $data['color'] ?: null,
                $data['defect'] ?: null,
                $id,
            ]);

            if ($removeImageIds) {
                $placeholders = implode(',', array_fill(0, count($removeImageIds), '?'));
                $stmt = $this->db->prepare("DELETE FROM product_images WHERE product_id = ? AND id IN ({$placeholders})");
                $stmt->execute(array_merge([$id], $removeImageIds));
            }

            if ($newImages) {
                $this->insertImages($id, $newImages);
            }

            $remaining = $this->images($id);
            if (!$remaining && $newImages) {
                // Tidak mungkin terjadi karena gambar baru baru saja dimasukkan.
            } elseif ($remaining) {
                $hasPrimary = array_filter($remaining, fn ($img) => (int) $img['is_primary'] === 1);
                if (!$hasPrimary) {
                    $this->db->prepare('UPDATE product_images SET is_primary = 1 WHERE id = ?')->execute([$remaining[0]['id']]);
                }
            }

            $this->db->commit();
        } catch (Throwable $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    private function insertImages(int $productId, array $images): void
    {
        $stmt = $this->db->prepare(
            'INSERT INTO product_images (product_id, image_path, is_primary, sort_order) VALUES (?, ?, ?, ?)'
        );
        $hasPrimary = (int) $this->db->query("SELECT COUNT(*) FROM product_images WHERE product_id = " . (int) $productId . " AND is_primary = 1")->fetchColumn() > 0;
        foreach (array_values($images) as $index => $image) {
            $stmt->execute([
                $productId,
                $image,
                (!$hasPrimary && $index === 0) ? 1 : 0,
                $index,
            ]);
        }
    }

    public function softDelete(int $id): void
    {
        $stmt = $this->db->prepare("UPDATE products SET status = 'dihapus', updated_at = CURRENT_TIMESTAMP WHERE id = ?");
        $stmt->execute([$id]);
        $this->db->prepare('DELETE FROM wishlists WHERE product_id = ?')->execute([$id]);
    }

    public function setStatus(int $id, string $status): void
    {
        $stmt = $this->db->prepare('UPDATE products SET status = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?');
        $stmt->execute([$status, $id]);
    }

    public function markSold(int $id): void
    {
        $this->setStatus($id, 'terjual');
    }

    public function reopen(int $id): void
    {
        $this->setStatus($id, 'aktif');
    }

    public function incrementView(int $id): void
    {
        $stmt = $this->db->prepare('UPDATE products SET view_count = view_count + 1 WHERE id = ?');
        $stmt->execute([$id]);
    }

    public function forSeller(int $sellerId, array $filters = []): array
    {
        $status = $filters['status'] ?? '';
        $where = "WHERE p.seller_id = ? AND p.status != 'dihapus'";
        $params = [$sellerId];
        if ($status && in_array($status, ['aktif', 'terjual', 'ditinjau'], true)) {
            $where .= ' AND p.status = ?';
            $params[] = $status;
        }
        $stmt = $this->db->prepare(
            $this->baseSelect . " {$where} ORDER BY p.created_at DESC"
        );
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function countForSeller(int $sellerId): int
    {
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM products WHERE seller_id = ? AND status != 'dihapus'");
        $stmt->execute([$sellerId]);
        return (int) $stmt->fetchColumn();
    }

    public function sellerStats(int $sellerId): array
    {
        $stmt = $this->db->prepare(
            "SELECT
                COUNT(*) AS total,
                SUM(CASE WHEN status = 'aktif' THEN 1 ELSE 0 END) AS active_count,
                SUM(CASE WHEN status = 'terjual' THEN 1 ELSE 0 END) AS sold_count,
                SUM(CASE WHEN status = 'ditinjau' THEN 1 ELSE 0 END) AS review_count,
                COALESCE(SUM(view_count), 0) AS total_views
             FROM products WHERE seller_id = ? AND status != 'dihapus'"
        );
        $stmt->execute([$sellerId]);
        return $stmt->fetch() ?: ['total' => 0, 'active_count' => 0, 'sold_count' => 0, 'review_count' => 0, 'total_views' => 0];
    }

    public function featured(int $limit = 8): array
    {
        $stmt = $this->db->prepare(
            $this->baseSelect . " WHERE p.status = 'aktif' AND u.status = 'aktif' AND c.is_active = 1
                ORDER BY (SELECT COUNT(*) FROM wishlists w WHERE w.product_id = p.id) DESC, p.created_at DESC LIMIT {$limit}"
        );
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function newest(int $limit = 8): array
    {
        $stmt = $this->db->prepare(
            $this->baseSelect . " WHERE p.status = 'aktif' AND u.status = 'aktif' AND c.is_active = 1 ORDER BY p.created_at DESC LIMIT {$limit}"
        );
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function related(array $product, int $limit = 4): array
    {
        $stmt = $this->db->prepare(
            $this->baseSelect . " WHERE p.status = 'aktif' AND p.id != ? AND (p.category_id = ? OR p.type = ?)
                ORDER BY (p.category_id = ?) DESC, p.view_count DESC LIMIT {$limit}"
        );
        $stmt->execute([$product['id'], $product['category_id'], $product['type'], $product['category_id']]);
        return $stmt->fetchAll();
    }

    public function bySeller(int $sellerId, int $excludeId = 0, int $limit = 4): array
    {
        $stmt = $this->db->prepare(
            $this->baseSelect . " WHERE p.seller_id = ? AND p.status = 'aktif' AND p.id != ? ORDER BY p.created_at DESC LIMIT {$limit}"
        );
        $stmt->execute([$sellerId, $excludeId]);
        return $stmt->fetchAll();
    }

    public function adminPaginate(array $filters = [], int $page = 1, int $perPage = 15): array
    {
        $conditions = ['1 = 1'];
        $params = [];
        if (!empty($filters['q'])) {
            $conditions[] = '(lower(p.title) LIKE :q OR lower(u.name) LIKE :q)';
            $params['q'] = '%' . mb_strtolower($filters['q']) . '%';
        }
        if (!empty($filters['status'])) {
            $conditions[] = 'p.status = :status';
            $params['status'] = $filters['status'];
        }
        if (!empty($filters['category_id'])) {
            $conditions[] = 'p.category_id = :category_id';
            $params['category_id'] = (int) $filters['category_id'];
        }
        $where = 'WHERE ' . implode(' AND ', $conditions);
        $offset = max(0, ($page - 1) * $perPage);

        $countStmt = $this->db->prepare(
            "SELECT COUNT(*) FROM products p JOIN categories c ON c.id = p.category_id JOIN users u ON u.id = p.seller_id {$where}"
        );
        $countStmt->execute($params);
        $total = (int) $countStmt->fetchColumn();

        $stmt = $this->db->prepare(
            $this->baseSelect . " {$where} ORDER BY p.created_at DESC LIMIT {$perPage} OFFSET {$offset}"
        );
        $stmt->execute($params);
        return ['items' => $stmt->fetchAll(), 'total' => $total, 'pages' => max(1, (int) ceil($total / $perPage)), 'page' => $page];
    }

    public function total(string $status = ''): int
    {
        if ($status) {
            $stmt = $this->db->prepare('SELECT COUNT(*) FROM products WHERE status = ?');
            $stmt->execute([$status]);
            return (int) $stmt->fetchColumn();
        }
        return (int) $this->db->query('SELECT COUNT(*) FROM products')->fetchColumn();
    }

    public function recent(int $limit = 5): array
    {
        $stmt = $this->db->prepare(
            "SELECT p.*, u.name AS seller_name, c.name AS category_name,
                (SELECT pi.image_path FROM product_images pi WHERE pi.product_id = p.id ORDER BY pi.is_primary DESC, pi.id ASC LIMIT 1) AS primary_image
             FROM products p JOIN users u ON u.id = p.seller_id JOIN categories c ON c.id = p.category_id
             WHERE p.status != 'dihapus' ORDER BY p.created_at DESC LIMIT {$limit}"
        );
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function moderationActivity(int $limit = 5): array
    {
        $stmt = $this->db->prepare(
            "SELECT p.id, p.title, p.status, p.updated_at, u.name AS seller_name
             FROM products p JOIN users u ON u.id = p.seller_id
             WHERE p.status IN ('ditinjau', 'dihapus', 'terjual')
             ORDER BY p.updated_at DESC LIMIT {$limit}"
        );
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function ownProduct(int $productId, int $sellerId): ?array
    {
        $product = $this->find($productId);
        if ($product && (int) $product['seller_id'] === $sellerId) {
            return $product;
        }
        return null;
    }

    private function uniqueSlug(string $title): string
    {
        $base = strtolower(trim(preg_replace('/[^a-zA-Z0-9]+/', '-', $title), '-'));
        if ($base === '') {
            $base = 'produk';
        }
        $slug = $base;
        $i = 2;
        while (true) {
            $stmt = $this->db->prepare('SELECT COUNT(*) FROM products WHERE slug = ?');
            $stmt->execute([$slug]);
            if ((int) $stmt->fetchColumn() === 0) {
                return $slug;
            }
            $slug = $base . '-' . $i++;
        }
    }

    public function searchSuggestions(string $q, int $limit = 5): array
    {
        $stmt = $this->db->prepare(
            "SELECT p.id, p.title, p.price, p.size,
                (SELECT pi.image_path FROM product_images pi WHERE pi.product_id = p.id ORDER BY pi.is_primary DESC, pi.id ASC LIMIT 1) AS primary_image
             FROM products p WHERE p.status = 'aktif' AND lower(p.title) LIKE ? ORDER BY p.view_count DESC LIMIT {$limit}"
        );
        $stmt->execute(['%' . mb_strtolower($q) . '%']);
        return $stmt->fetchAll();
    }
}
