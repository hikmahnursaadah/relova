<?php

class UserModel extends Model
{
    public function find(int $id): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM users WHERE id = ?');
        $stmt->execute([$id]);
        $user = $stmt->fetch();
        return $user ?: null;
    }

    public function findByEmail(string $email): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM users WHERE lower(email) = lower(?) LIMIT 1');
        $stmt->execute([$email]);
        $user = $stmt->fetch();
        return $user ?: null;
    }

    public function findByUsername(string $username): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM users WHERE lower(username) = lower(?) LIMIT 1');
        $stmt->execute([$username]);
        $user = $stmt->fetch();
        return $user ?: null;
    }

    public function create(array $data): int
    {
        $stmt = $this->db->prepare(
            'INSERT INTO users (name, username, email, password_hash, role, phone, city, bio)
             VALUES (:name, :username, :email, :password_hash, :role, :phone, :city, :bio)'
        );
        $stmt->execute([
            'name' => $data['name'],
            'username' => $data['username'],
            'email' => $data['email'],
            'password_hash' => password_hash($data['password'], PASSWORD_DEFAULT),
            'role' => $data['role'] ?? 'pembeli',
            'phone' => $data['phone'] ?? null,
            'city' => $data['city'] ?? null,
            'bio' => $data['bio'] ?? null,
        ]);
        return (int) $this->db->lastInsertId();
    }

    public function updateProfile(int $id, array $data): void
    {
        $stmt = $this->db->prepare(
            'UPDATE users SET name = ?, username = ?, email = ?, phone = ?, city = ?, address = ?, bio = ? WHERE id = ?'
        );
        $stmt->execute([
            $data['name'],
            $data['username'],
            $data['email'],
            $data['phone'] ?: null,
            $data['city'] ?: null,
            $data['address'] ?: null,
            $data['bio'] ?: null,
            $id,
        ]);
    }

    public function updatePassword(int $id, string $password): void
    {
        $stmt = $this->db->prepare('UPDATE users SET password_hash = ? WHERE id = ?');
        $stmt->execute([password_hash($password, PASSWORD_DEFAULT), $id]);
    }

    public function setRole(int $id, string $role): void
    {
        $stmt = $this->db->prepare('UPDATE users SET role = ? WHERE id = ?');
        $stmt->execute([$role, $id]);
    }

    public function setStatus(int $id, string $status): void
    {
        $stmt = $this->db->prepare('UPDATE users SET status = ? WHERE id = ?');
        $stmt->execute([$status, $id]);
    }

    public function all(array $filters = [], int $page = 1, int $perPage = 12): array
    {
        [$where, $params] = $this->buildFilters($filters);
        $offset = max(0, ($page - 1) * $perPage);
        $stmt = $this->db->prepare(
            "SELECT u.*,
                (SELECT COUNT(*) FROM products p WHERE p.seller_id = u.id AND p.status != 'dihapus') AS product_count,
                (SELECT COUNT(*) FROM orders o WHERE o.seller_id = u.id AND o.status = 'selesai') AS completed_sales
             FROM users u {$where}
             ORDER BY u.created_at DESC
             LIMIT {$perPage} OFFSET {$offset}"
        );
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function countAll(array $filters = []): int
    {
        [$where, $params] = $this->buildFilters($filters);
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM users u {$where}");
        $stmt->execute($params);
        return (int) $stmt->fetchColumn();
    }

    private function buildFilters(array $filters): array
    {
        $conditions = [];
        $params = [];
        if (!empty($filters['q'])) {
            $conditions[] = '(lower(u.name) LIKE :q OR lower(u.email) LIKE :q OR lower(u.username) LIKE :q)';
            $params['q'] = '%' . mb_strtolower($filters['q']) . '%';
        }
        if (!empty($filters['role'])) {
            $conditions[] = 'u.role = :role';
            $params['role'] = $filters['role'];
        }
        if (!empty($filters['status'])) {
            $conditions[] = 'u.status = :status';
            $params['status'] = $filters['status'];
        }
        return [$conditions ? 'WHERE ' . implode(' AND ', $conditions) : '', $params];
    }

    public function countByRole(): array
    {
        return $this->db->query('SELECT role, COUNT(*) AS total FROM users GROUP BY role')->fetchAll();
    }

    public function total(): int
    {
        return (int) $this->db->query('SELECT COUNT(*) FROM users')->fetchColumn();
    }

    public function activeSellers(): int
    {
        $stmt = $this->db->prepare(
            "SELECT COUNT(DISTINCT u.id) FROM users u
             JOIN products p ON p.seller_id = u.id
             WHERE u.status = 'aktif' AND u.role IN ('penjual', 'pembeli') AND p.status = 'aktif'"
        );
        $stmt->execute();
        return (int) $stmt->fetchColumn();
    }

    public function sellerCities(): int
    {
        $stmt = $this->db->prepare(
            "SELECT COUNT(DISTINCT lower(trim(u.city))) FROM users u
             JOIN products p ON p.seller_id = u.id
             WHERE u.status = 'aktif' AND u.role IN ('penjual', 'pembeli')
               AND p.status = 'aktif' AND u.city IS NOT NULL AND trim(u.city) != ''"
        );
        $stmt->execute();
        return (int) $stmt->fetchColumn();
    }

    public function newThisMonth(): int
    {
        $stmt = $this->db->prepare('SELECT COUNT(*) FROM users WHERE created_at >= ?');
        $stmt->execute([date('Y-m-01 00:00:00')]);
        return (int) $stmt->fetchColumn();
    }

    public function sellerProfile(int $id): ?array
    {
        $stmt = $this->db->prepare(
            "SELECT u.*,
                (SELECT COUNT(*) FROM products p WHERE p.seller_id = u.id AND p.status = 'aktif') AS active_products,
                (SELECT COUNT(*) FROM orders o WHERE o.seller_id = u.id AND o.status = 'selesai') AS completed_sales,
                (SELECT COALESCE(AVG(r.rating), 0) FROM reviews r WHERE r.seller_id = u.id) AS rating_avg,
                (SELECT COUNT(*) FROM reviews r WHERE r.seller_id = u.id) AS review_count
             FROM users u WHERE u.id = ?"
        );
        $stmt->execute([$id]);
        $seller = $stmt->fetch();
        return $seller ?: null;
    }
}
