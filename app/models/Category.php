<?php
/**
 * Hesabdar - Category Model
 * Manages income and expense classification.
 */

require_once __DIR__ . '/Model.php';

class Category extends Model {
    /**
     * Get categories for user, optionally filtered by type (income or expense)
     */
    public function getAllForUser(int $userId, ?string $type = null, bool $includeArchived = false): array {
        $where = ["c.user_id = :user_id"];
        $params = ['user_id' => $userId];

        if ($type !== null) {
            $where[] = "c.type = :type";
            $params['type'] = $type;
        }

        if (!$includeArchived) {
            $where[] = "c.status = 'active'";
        }

        $whereClause = implode(' AND ', $where);

        $sql = "
            SELECT 
                c.*,
                (SELECT COUNT(*) FROM transactions t WHERE t.category_id = c.id) AS usage_count
            FROM categories c
            WHERE {$whereClause}
            ORDER BY c.sort_order ASC, c.id ASC
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /**
     * Find single category by ID
     */
    public function findById(int $id, int $userId): ?array {
        $stmt = $this->db->prepare("SELECT * FROM categories WHERE id = :id AND user_id = :user_id LIMIT 1");
        $stmt->execute([
            'id' => $id,
            'user_id' => $userId
        ]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    /**
     * Create category
     */
    public function create(array $data): int {
        $stmt = $this->db->prepare("
            INSERT INTO categories (user_id, name, type, color, icon, status, sort_order, created_at)
            VALUES (:user_id, :name, :type, :color, :icon, :status, :sort_order, NOW())
        ");
        $stmt->execute([
            'user_id' => $data['user_id'],
            'name' => trim($data['name']),
            'type' => $data['type'],
            'color' => $data['color'] ?? '#64748b',
            'icon' => $data['icon'] ?? 'tag',
            'status' => $data['status'] ?? 'active',
            'sort_order' => (int)($data['sort_order'] ?? 0)
        ]);

        return (int)$this->db->lastInsertId();
    }

    /**
     * Update category
     */
    public function update(int $id, int $userId, array $data): bool {
        $stmt = $this->db->prepare("
            UPDATE categories 
            SET name = :name,
                type = :type,
                color = :color,
                icon = :icon,
                status = :status,
                sort_order = :sort_order
            WHERE id = :id AND user_id = :user_id
        ");

        return $stmt->execute([
            'name' => trim($data['name']),
            'type' => $data['type'],
            'color' => $data['color'] ?? '#64748b',
            'icon' => $data['icon'] ?? 'tag',
            'status' => $data['status'] ?? 'active',
            'sort_order' => (int)($data['sort_order'] ?? 0),
            'id' => $id,
            'user_id' => $userId
        ]);
    }

    /**
     * Safe delete or archive
     */
    public function deleteOrArchive(int $id, int $userId): bool {
        // Check if referenced by transactions
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM transactions WHERE category_id = :id");
        $stmt->execute(['id' => $id]);
        $hasTransactions = (int)$stmt->fetchColumn() > 0;

        if ($hasTransactions) {
            $stmt = $this->db->prepare("UPDATE categories SET status = 'archived' WHERE id = :id AND user_id = :user_id");
            return $stmt->execute(['id' => $id, 'user_id' => $userId]);
        }

        $stmt = $this->db->prepare("DELETE FROM categories WHERE id = :id AND user_id = :user_id");
        return $stmt->execute(['id' => $id, 'user_id' => $userId]);
    }

    /**
     * Seed default categories for a new user
     */
    public function seedDefaults(int $userId): void {
        $defaults = [
            // Expense
            ['name' => 'غذا و رستوران', 'type' => 'expense', 'color' => '#f97316', 'icon' => 'utensils', 'sort' => 1],
            ['name' => 'حمل‌ونقل و بنزین', 'type' => 'expense', 'color' => '#0284c7', 'icon' => 'car', 'sort' => 2],
            ['name' => 'خرید و پوشاک', 'type' => 'expense', 'color' => '#ec4899', 'icon' => 'shopping-bag', 'sort' => 3],
            ['name' => 'قبوض و شارژ', 'type' => 'expense', 'color' => '#eab308', 'icon' => 'receipt', 'sort' => 4],
            ['name' => 'اینترنت و ارتباطات', 'type' => 'expense', 'color' => '#6366f1', 'icon' => 'wifi', 'sort' => 5],
            ['name' => 'تفریح و سرگرمی', 'type' => 'expense', 'color' => '#8b5cf6', 'icon' => 'film', 'sort' => 6],
            ['name' => 'آموزش و کتاب', 'type' => 'expense', 'color' => '#14b8a6', 'icon' => 'book-open', 'sort' => 7],
            ['name' => 'سلامت و درمان', 'type' => 'expense', 'color' => '#ef4444', 'icon' => 'heart-pulse', 'sort' => 8],
            ['name' => 'خانه و نگهداری', 'type' => 'expense', 'color' => '#84cc16', 'icon' => 'home', 'sort' => 9],
            ['name' => 'اشتراک‌ها و نرم‌افزار', 'type' => 'expense', 'color' => '#06b6d4', 'icon' => 'repeat', 'sort' => 10],
            ['name' => 'سایر هزینه‌ها', 'type' => 'expense', 'color' => '#64748b', 'icon' => 'more-horizontal', 'sort' => 11],

            // Income
            ['name' => 'حقوق ماهیانه', 'type' => 'income', 'color' => '#10b981', 'icon' => 'briefcase', 'sort' => 1],
            ['name' => 'پروژه و فریلنسری', 'type' => 'income', 'color' => '#06b6d4', 'icon' => 'laptop', 'sort' => 2],
            ['name' => 'فروش کالا', 'type' => 'income', 'color' => '#3b82f6', 'icon' => 'dollar-sign', 'sort' => 3],
            ['name' => 'هدیه و پاداش', 'type' => 'income', 'color' => '#f59e0b', 'icon' => 'gift', 'sort' => 4],
            ['name' => 'سایر درآمدها', 'type' => 'income', 'color' => '#64748b', 'icon' => 'plus-circle', 'sort' => 5],
        ];

        foreach ($defaults as $d) {
            $this->create([
                'user_id' => $userId,
                'name' => $d['name'],
                'type' => $d['type'],
                'color' => $d['color'],
                'icon' => $d['icon'],
                'sort_order' => $d['sort']
            ]);
        }
    }
}
