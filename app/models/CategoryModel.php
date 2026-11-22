<?php

class CategoryModel extends Model
{
    public function all(bool $onlyActive = false): array
    {
        $where = $onlyActive ? 'WHERE is_active = 1' : '';
        $sql = "SELECT c.*,
                    (SELECT COUNT(*) FROM products p WHERE p.category_id = c.id AND p.status = 'aktif') AS product_count
                FROM categories c {$where}
                ORDER BY c.sort_order ASC, c.id ASC";
        return $this->db->query($sql)->fetchAll();
    }

    public function forCatalog(): array
    {
        return $this->db->query(
            "SELECT c.*,
                (SELECT COUNT(*) FROM products p WHERE p.category_id = c.id AND p.status = 'aktif') AS product_count
             FROM categories c WHERE c.is_active = 1
             ORDER BY c.sort_order ASC"
        )->fetchAll();
    }

    public function find(int $id): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM categories WHERE id = ?');
        $stmt->execute([$id]);
        $category = $stmt->fetch();
        return $category ?: null;
    }

    public function findBySlug(string $slug): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM categories WHERE slug = ?');
        $stmt->execute([$slug]);
        $category = $stmt->fetch();
        return $category ?: null;
    }

    public function create(array $data): int
    {
        $stmt = $this->db->prepare(
            'INSERT INTO categories (name, slug, description, image, type, sort_order, is_active)
             VALUES (?, ?, ?, ?, ?, ?, ?)'
        );
        $stmt->execute([
            $data['name'],
            $data['slug'],
            $data['description'] ?: null,
            $data['image'] ?: null,
            $data['type'],
            (int) ($data['sort_order'] ?? 0),
            (int) ($data['is_active'] ?? 1),
        ]);
        return (int) $this->db->lastInsertId();
    }

    public function update(int $id, array $data): void
    {
        $stmt = $this->db->prepare(
            'UPDATE categories SET name = ?, slug = ?, description = ?, image = ?, type = ?, sort_order = ?, is_active = ? WHERE id = ?'
        );
        $stmt->execute([
            $data['name'],
            $data['slug'],
            $data['description'] ?: null,
            $data['image'] ?: null,
            $data['type'],
            (int) ($data['sort_order'] ?? 0),
            (int) ($data['is_active'] ?? 1),
            $id,
        ]);
    }

    public function total(): int
    {
        return (int) $this->db->query('SELECT COUNT(*) FROM categories')->fetchColumn();
    }
}
