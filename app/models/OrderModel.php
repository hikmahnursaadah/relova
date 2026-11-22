<?php

class OrderModel extends Model
{
    public function create(int $buyerId, int $sellerId, array $items, int $shippingCost, string $address, string $notes = ''): int
    {
        $orderIds = $this->createBatchForBuyer($buyerId, [
            $sellerId => [
                'seller_id' => $sellerId,
                'items' => $items,
            ],
        ], [$sellerId => $shippingCost], $address, $notes);
        return (int) $orderIds[0];
    }

    public function createBatchForBuyer(int $buyerId, array $groups, array $shippingCosts, string $address, string $notes = ''): array
    {
        if (!$groups) {
            throw new InvalidArgumentException('Tidak ada produk untuk dipesan.');
        }

        $productIds = [];
        foreach ($groups as $group) {
            foreach ($group['items'] ?? [] as $item) {
                $productIds[] = (int) $item['product_id'];
            }
        }

        $this->db->beginTransaction();
        try {
            $reserved = $this->reservedProductIds($productIds);
            if ($reserved) {
                throw new RuntimeException('Sebagian produk sudah dipesan pembeli lain dan tidak dapat dipesan kembali.');
            }

            $orderIds = [];
            foreach ($groups as $sellerId => $group) {
                $sellerId = (int) $sellerId;
                $items = $group['items'] ?? [];
                $subtotal = 0;
                foreach ($items as $item) {
                    $subtotal += (int) $item['price'];
                }
                $shippingCost = (int) ($shippingCosts[$sellerId] ?? 0);
                $total = $subtotal + $shippingCost;
                $orderNumber = $this->generateOrderNumber();

                $stmt = $this->db->prepare(
                    'INSERT INTO orders (order_number, buyer_id, seller_id, total_amount, shipping_cost, status, shipping_address, notes)
                     VALUES (?, ?, ?, ?, ?, ?, ?, ?)'
                );
                $stmt->execute([
                    $orderNumber,
                    $buyerId,
                    $sellerId,
                    $total,
                    $shippingCost,
                    'menunggu',
                    $address,
                    $notes ?: null,
                ]);
                $orderId = (int) $this->db->lastInsertId();
                $orderIds[] = $orderId;

                $itemStmt = $this->db->prepare(
                    'INSERT INTO order_items (order_id, product_id, product_title, product_image, price) VALUES (?, ?, ?, ?, ?)'
                );
                foreach ($items as $item) {
                    $itemStmt->execute([
                        $orderId,
                        $item['product_id'],
                        $item['title'],
                        $item['image'] ?? null,
                        $item['price'],
                    ]);
                }
            }

            $this->db->commit();
            return $orderIds;
        } catch (Throwable $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    public function reservedProductIds(array $ids): array
    {
        $ids = array_values(array_unique(array_filter(array_map('intval', $ids), fn ($id) => $id > 0)));
        if (!$ids) {
            return [];
        }
        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        $stmt = $this->db->prepare(
            "SELECT DISTINCT oi.product_id
             FROM order_items oi
             JOIN orders o ON o.id = oi.order_id
             WHERE o.status <> 'dibatalkan' AND oi.product_id IN ({$placeholders})"
        );
        $stmt->execute($ids);
        return array_values(array_unique(array_map('intval', $stmt->fetchAll(PDO::FETCH_COLUMN))));
    }

    private function generateOrderNumber(): string
    {
        do {
            $number = 'RW-' . date('ymd') . '-' . strtoupper(substr(bin2hex(random_bytes(3)), 0, 5));
            $stmt = $this->db->prepare('SELECT COUNT(*) FROM orders WHERE order_number = ?');
            $stmt->execute([$number]);
        } while ((int) $stmt->fetchColumn() > 0);
        return $number;
    }

    public function find(int $id): ?array
    {
        $stmt = $this->db->prepare(
            "SELECT o.*, b.name AS buyer_name, b.email AS buyer_email, b.username AS buyer_username, b.phone AS buyer_phone, b.city AS buyer_city,
                s.name AS seller_name, s.email AS seller_email, s.username AS seller_username, s.phone AS seller_phone, s.city AS seller_city
             FROM orders o
             JOIN users b ON b.id = o.buyer_id
             JOIN users s ON s.id = o.seller_id
             WHERE o.id = ?"
        );
        $stmt->execute([$id]);
        $order = $stmt->fetch();
        return $order ?: null;
    }

    public function items(int $orderId): array
    {
        $stmt = $this->db->prepare(
            "SELECT oi.*, p.slug AS product_slug,
                (SELECT r.id FROM reviews r WHERE r.order_id = oi.order_id AND r.product_id = oi.product_id LIMIT 1) AS review_id
             FROM order_items oi
             LEFT JOIN products p ON p.id = oi.product_id
             WHERE oi.order_id = ? ORDER BY oi.id ASC"
        );
        $stmt->execute([$orderId]);
        return $stmt->fetchAll();
    }

    public function findForUser(int $id, int $userId, bool $isAdmin = false): ?array
    {
        $order = $this->find($id);
        if (!$order) {
            return null;
        }
        if (!$isAdmin && (int) $order['buyer_id'] !== $userId && (int) $order['seller_id'] !== $userId) {
            return null;
        }
        $order['items'] = $this->items($id);
        return $order;
    }

    public function forBuyer(int $buyerId, string $status = ''): array
    {
        return $this->listByParty('buyer_id', $buyerId, $status);
    }

    public function forSeller(int $sellerId, string $status = ''): array
    {
        return $this->listByParty('seller_id', $sellerId, $status);
    }

    private function listByParty(string $column, int $userId, string $status = ''): array
    {
        $where = "WHERE o.{$column} = :user_id";
        $params = ['user_id' => $userId];
        if ($status && array_key_exists($status, order_statuses())) {
            $where .= ' AND o.status = :status';
            $params['status'] = $status;
        }
        $stmt = $this->db->prepare(
            "SELECT o.*,
                b.name AS buyer_name, b.username AS buyer_username,
                s.name AS seller_name, s.username AS seller_username,
                (SELECT product_title FROM order_items oi WHERE oi.order_id = o.id LIMIT 1) AS first_item_title,
                (SELECT product_image FROM order_items oi WHERE oi.order_id = o.id LIMIT 1) AS first_item_image,
                (SELECT COUNT(*) FROM order_items oi WHERE oi.order_id = o.id) AS item_count,
                (SELECT COUNT(*) FROM reviews r WHERE r.order_id = o.id) AS review_count
             FROM orders o
             JOIN users b ON b.id = o.buyer_id
             JOIN users s ON s.id = o.seller_id
             {$where}
             ORDER BY o.created_at DESC, o.id DESC"
        );
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function recentForSeller(int $sellerId, int $limit = 5): array
    {
        $stmt = $this->db->prepare(
            "SELECT o.*, b.name AS buyer_name,
                (SELECT product_title FROM order_items oi WHERE oi.order_id = o.id LIMIT 1) AS first_item_title
             FROM orders o JOIN users b ON b.id = o.buyer_id
             WHERE o.seller_id = ? ORDER BY o.created_at DESC LIMIT {$limit}"
        );
        $stmt->execute([$sellerId]);
        return $stmt->fetchAll();
    }

    public function recentForBuyer(int $buyerId, int $limit = 5): array
    {
        $stmt = $this->db->prepare(
            "SELECT o.*, s.name AS seller_name,
                (SELECT product_title FROM order_items oi WHERE oi.order_id = o.id LIMIT 1) AS first_item_title,
                (SELECT product_image FROM order_items oi WHERE oi.order_id = o.id LIMIT 1) AS first_item_image
             FROM orders o JOIN users s ON s.id = o.seller_id
             WHERE o.buyer_id = ? ORDER BY o.created_at DESC LIMIT {$limit}"
        );
        $stmt->execute([$buyerId]);
        return $stmt->fetchAll();
    }

    public function updateStatus(int $id, string $status): void
    {
        if (!array_key_exists($status, order_statuses())) {
            throw new InvalidArgumentException('Status pesanan tidak valid.');
        }
        $stmt = $this->db->prepare('UPDATE orders SET status = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?');
        $stmt->execute([$status, $id]);

        if (in_array($status, ['dikonfirmasi', 'diproses', 'dikirim', 'selesai'], true)) {
            $items = $this->items($id);
            $productStmt = $this->db->prepare("UPDATE products SET status = 'terjual', updated_at = CURRENT_TIMESTAMP WHERE id = ?");
            foreach ($items as $item) {
                if ($item['product_id']) {
                    $productStmt->execute([$item['product_id']]);
                }
            }
        }

        if ($status === 'dibatalkan') {
            $items = $this->items($id);
            $productStmt = $this->db->prepare("UPDATE products SET status = 'aktif', updated_at = CURRENT_TIMESTAMP WHERE id = ? AND status = 'terjual'");
            foreach ($items as $item) {
                if ($item['product_id']) {
                    $productStmt->execute([$item['product_id']]);
                }
            }
        }
    }

    public function sellerStats(int $sellerId): array
    {
        $stmt = $this->db->prepare(
            "SELECT
                COUNT(*) AS total_orders,
                SUM(CASE WHEN status = 'menunggu' THEN 1 ELSE 0 END) AS incoming,
                SUM(CASE WHEN status IN ('dikonfirmasi', 'diproses', 'dikirim') THEN 1 ELSE 0 END) AS active_orders,
                SUM(CASE WHEN status = 'selesai' THEN 1 ELSE 0 END) AS completed,
                COALESCE(SUM(CASE WHEN status = 'selesai' THEN total_amount ELSE 0 END), 0) AS revenue
             FROM orders WHERE seller_id = ?"
        );
        $stmt->execute([$sellerId]);
        return $stmt->fetch() ?: ['total_orders' => 0, 'incoming' => 0, 'active_orders' => 0, 'completed' => 0, 'revenue' => 0];
    }

    public function buyerStats(int $buyerId): array
    {
        $stmt = $this->db->prepare(
            "SELECT
                COUNT(*) AS total_orders,
                SUM(CASE WHEN status IN ('menunggu', 'dikonfirmasi', 'diproses', 'dikirim') THEN 1 ELSE 0 END) AS active_orders,
                SUM(CASE WHEN status = 'selesai' THEN 1 ELSE 0 END) AS completed,
                COALESCE(SUM(CASE WHEN status != 'dibatalkan' THEN total_amount ELSE 0 END), 0) AS spent
             FROM orders WHERE buyer_id = ?"
        );
        $stmt->execute([$buyerId]);
        return $stmt->fetch() ?: ['total_orders' => 0, 'active_orders' => 0, 'completed' => 0, 'spent' => 0];
    }

    public function adminStats(): array
    {
        $row = $this->db->query(
            "SELECT
                COUNT(*) AS total,
                SUM(CASE WHEN status = 'menunggu' THEN 1 ELSE 0 END) AS pending,
                SUM(CASE WHEN status = 'selesai' THEN 1 ELSE 0 END) AS completed,
                COALESCE(SUM(CASE WHEN status != 'dibatalkan' THEN total_amount ELSE 0 END), 0) AS gross_value
             FROM orders"
        )->fetch();
        return $row ?: ['total' => 0, 'pending' => 0, 'completed' => 0, 'gross_value' => 0];
    }

    public function adminPaginate(array $filters = [], int $page = 1, int $perPage = 15): array
    {
        $conditions = ['1 = 1'];
        $params = [];
        if (!empty($filters['q'])) {
            $conditions[] = '(lower(o.order_number) LIKE :q OR lower(b.name) LIKE :q OR lower(s.name) LIKE :q)';
            $params['q'] = '%' . mb_strtolower($filters['q']) . '%';
        }
        if (!empty($filters['status'])) {
            $conditions[] = 'o.status = :status';
            $params['status'] = $filters['status'];
        }
        $where = 'WHERE ' . implode(' AND ', $conditions);
        $offset = max(0, ($page - 1) * $perPage);

        $countStmt = $this->db->prepare(
            "SELECT COUNT(*) FROM orders o JOIN users b ON b.id = o.buyer_id JOIN users s ON s.id = o.seller_id {$where}"
        );
        $countStmt->execute($params);
        $total = (int) $countStmt->fetchColumn();

        $stmt = $this->db->prepare(
            "SELECT o.*, b.name AS buyer_name, s.name AS seller_name,
                (SELECT product_title FROM order_items oi WHERE oi.order_id = o.id LIMIT 1) AS first_item_title,
                (SELECT COUNT(*) FROM order_items oi WHERE oi.order_id = o.id) AS item_count
             FROM orders o JOIN users b ON b.id = o.buyer_id JOIN users s ON s.id = o.seller_id
             {$where} ORDER BY o.created_at DESC LIMIT {$perPage} OFFSET {$offset}"
        );
        $stmt->execute($params);
        return ['items' => $stmt->fetchAll(), 'total' => $total, 'pages' => max(1, (int) ceil($total / $perPage)), 'page' => $page];
    }

    public function recent(int $limit = 5): array
    {
        $stmt = $this->db->prepare(
            "SELECT o.*, b.name AS buyer_name, s.name AS seller_name
             FROM orders o JOIN users b ON b.id = o.buyer_id JOIN users s ON s.id = o.seller_id
             ORDER BY o.created_at DESC LIMIT {$limit}"
        );
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function dailyOrderCounts(int $days = 7): array
    {
        $start = date('Y-m-d', strtotime('-' . ($days - 1) . ' days'));
        $stmt = $this->db->prepare('SELECT created_at FROM orders WHERE created_at >= ?');
        $stmt->execute([$start . ' 00:00:00']);
        $counts = [];
        for ($i = $days - 1; $i >= 0; $i--) {
            $counts[date('Y-m-d', strtotime('-' . $i . ' days'))] = 0;
        }
        foreach ($stmt->fetchAll() as $row) {
            $date = substr($row['created_at'], 0, 10);
            if (isset($counts[$date])) {
                $counts[$date]++;
            }
        }
        return $counts;
    }

    public function canReview(int $orderId, int $buyerId): bool
    {
        $order = $this->find($orderId);
        if (!$order || (int) $order['buyer_id'] !== $buyerId || $order['status'] !== 'selesai') {
            return false;
        }
        $stmt = $this->db->prepare('SELECT COUNT(*) FROM order_items WHERE order_id = ?');
        $stmt->execute([$orderId]);
        $itemCount = (int) $stmt->fetchColumn();
        $stmt = $this->db->prepare('SELECT COUNT(*) FROM reviews WHERE order_id = ?');
        $stmt->execute([$orderId]);
        return (int) $stmt->fetchColumn() < $itemCount;
    }

    public function countByStatus(): array
    {
        return $this->db->query('SELECT status, COUNT(*) AS total FROM orders GROUP BY status')->fetchAll();
    }
}
