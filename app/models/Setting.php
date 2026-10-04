<?php
/**
 * Hesabdar - Setting Model
 */

require_once __DIR__ . '/Model.php';

class Setting extends Model {
    /**
     * Get all settings for user as associative array
     */
    public function getAll(int $userId): array {
        $stmt = $this->db->prepare("SELECT setting_key, setting_value FROM settings WHERE user_id = :user_id");
        $stmt->execute(['user_id' => $userId]);
        $rows = $stmt->fetchAll();

        $settings = [];
        foreach ($rows as $row) {
            $settings[$row['setting_key']] = $row['setting_value'];
        }
        return $settings;
    }

    /**
     * Get single setting value
     */
    public function get(int $userId, string $key, ?string $default = null): ?string {
        $stmt = $this->db->prepare("SELECT setting_value FROM settings WHERE user_id = :user_id AND setting_key = :key LIMIT 1");
        $stmt->execute(['user_id' => $userId, 'key' => $key]);
        $val = $stmt->fetchColumn();
        return ($val !== false) ? $val : $default;
    }

    /**
     * Set setting value
     */
    public function set(int $userId, string $key, ?string $value): bool {
        $stmt = $this->db->prepare("
            INSERT INTO settings (user_id, setting_key, setting_value, updated_at)
            VALUES (:user_id, :key, :val, NOW())
            ON DUPLICATE KEY UPDATE setting_value = :val2, updated_at = NOW()
        ");
        return $stmt->execute([
            'user_id' => $userId,
            'key' => $key,
            'val' => $value,
            'val2' => $value
        ]);
    }
}
