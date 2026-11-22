<?php

class SettingModel extends Model
{
    public function all(): array
    {
        $rows = $this->db->query('SELECT key, value FROM settings')->fetchAll();
        $settings = [];
        foreach ($rows as $row) {
            $settings[$row['key']] = $row['value'];
        }
        return $settings;
    }

    public function get(string $key, string $default = ''): string
    {
        $stmt = $this->db->prepare('SELECT value FROM settings WHERE key = ?');
        $stmt->execute([$key]);
        $value = $stmt->fetchColumn();
        return $value !== false ? (string) $value : $default;
    }

    public function setMany(array $data): void
    {
        $update = $this->db->prepare('UPDATE settings SET value = ? WHERE key = ?');
        $insert = $this->db->prepare('INSERT INTO settings (key, value) VALUES (?, ?)');
        foreach ($data as $key => $value) {
            $update->execute([(string) $value, $key]);
            if ($update->rowCount() === 0) {
                $exists = $this->db->prepare('SELECT COUNT(*) FROM settings WHERE key = ?');
                $exists->execute([$key]);
                if ((int) $exists->fetchColumn() === 0) {
                    $insert->execute([$key, (string) $value]);
                }
            }
        }
    }
}
