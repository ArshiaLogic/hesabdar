<?php
/**
 * Hesabdar - Account Model
 * Manages bank cards, cash wallets, and savings accounts.
 * Balance is computed dynamically from opening_balance and all related transactions.
 */

require_once __DIR__ . '/Model.php';

class Account extends Model {
    /**
     * Get all active accounts for user with real-time calculated balance
     */
    public function getAllForUser(int $userId, bool $includeArchived = false): array {
        $statusCondition = $includeArchived ? "" : "AND a.status = 'active'";

        $sql = "
            SELECT 
                a.id,
                a.user_id,
                a.name,
                a.type,
                a.card_number,
                a.opening_balance,
                a.color,
                a.icon,
                a.status,
                a.created_at,
                (
                    a.opening_balance 
                    + COALESCE((SELECT SUM(amount_rial) FROM transactions WHERE account_id = a.id AND type = 'income'), 0)
                    - COALESCE((SELECT SUM(amount_rial) FROM transactions WHERE account_id = a.id AND type = 'expense'), 0)
                    - COALESCE((SELECT SUM(amount_rial) FROM transactions WHERE account_id = a.id AND type = 'transfer'), 0)
                    + COALESCE((SELECT SUM(amount_rial) FROM transactions WHERE to_account_id = a.id AND type = 'transfer'), 0)
                ) AS current_balance
            FROM accounts a
            WHERE a.user_id = :user_id {$statusCondition}
            ORDER BY a.id ASC
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute(['user_id' => $userId]);
        return $stmt->fetchAll();
    }

    /**
     * Find single account for user with calculated balance
     */
    public function findById(int $accountId, int $userId): ?array {
        $sql = "
            SELECT 
                a.id,
                a.user_id,
                a.name,
                a.type,
                a.card_number,
                a.opening_balance,
                a.color,
                a.icon,
                a.status,
                a.created_at,
                (
                    a.opening_balance 
                    + COALESCE((SELECT SUM(amount_rial) FROM transactions WHERE account_id = a.id AND type = 'income'), 0)
                    - COALESCE((SELECT SUM(amount_rial) FROM transactions WHERE account_id = a.id AND type = 'expense'), 0)
                    - COALESCE((SELECT SUM(amount_rial) FROM transactions WHERE account_id = a.id AND type = 'transfer'), 0)
                    + COALESCE((SELECT SUM(amount_rial) FROM transactions WHERE to_account_id = a.id AND type = 'transfer'), 0)
                ) AS current_balance
            FROM accounts a
            WHERE a.id = :id AND a.user_id = :user_id
            LIMIT 1
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'id' => $accountId,
            'user_id' => $userId
        ]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    /**
     * Compute total net balance across all active accounts of user
     */
    public function getTotalBalance(int $userId): int {
        $accounts = $this->getAllForUser($userId, false);
        $total = 0;
        foreach ($accounts as $acc) {
            $total += (int)$acc['current_balance'];
        }
        return $total;
    }

    /**
     * Create a new account
     */
    public function create(array $data): int {
        $stmt = $this->db->prepare("
            INSERT INTO accounts (user_id, name, type, card_number, opening_balance, color, icon, status, created_at)
            VALUES (:user_id, :name, :type, :card_number, :opening_balance, :color, :icon, :status, NOW())
        ");
        $stmt->execute([
            'user_id' => $data['user_id'],
            'name' => trim($data['name']),
            'type' => $data['type'] ?? 'bank_card',
            'card_number' => !empty($data['card_number']) ? trim($data['card_number']) : null,
            'opening_balance' => (int)($data['opening_balance'] ?? 0),
            'color' => $data['color'] ?? '#2563eb',
            'icon' => $data['icon'] ?? 'credit-card',
            'status' => $data['status'] ?? 'active'
        ]);

        return (int)$this->db->lastInsertId();
    }

    /**
     * Update account details
     */
    public function update(int $id, int $userId, array $data): bool {
        $stmt = $this->db->prepare("
            UPDATE accounts 
            SET name = :name,
                type = :type,
                card_number = :card_number,
                opening_balance = :opening_balance,
                color = :color,
                icon = :icon,
                status = :status,
                updated_at = NOW()
            WHERE id = :id AND user_id = :user_id
        ");

        return $stmt->execute([
            'name' => trim($data['name']),
            'type' => $data['type'] ?? 'bank_card',
            'card_number' => !empty($data['card_number']) ? trim($data['card_number']) : null,
            'opening_balance' => (int)($data['opening_balance'] ?? 0),
            'color' => $data['color'] ?? '#2563eb',
            'icon' => $data['icon'] ?? 'credit-card',
            'status' => $data['status'] ?? 'active',
            'id' => $id,
            'user_id' => $userId
        ]);
    }

    /**
     * Check if account has any associated transactions
     */
    public function hasTransactions(int $accountId): bool {
        $stmt = $this->db->prepare("
            SELECT COUNT(*) FROM transactions 
            WHERE account_id = :id1 OR to_account_id = :id2
        ");
        $stmt->execute([
            'id1' => $accountId,
            'id2' => $accountId
        ]);
        return (int)$stmt->fetchColumn() > 0;
    }

    /**
     * Safe delete or archive: If transactions exist, archive to preserve financial history
     */
    public function deleteOrArchive(int $accountId, int $userId): bool {
        if ($this->hasTransactions($accountId)) {
            // Archive instead of hard delete to preserve balance ledger integrity
            $stmt = $this->db->prepare("UPDATE accounts SET status = 'archived' WHERE id = :id AND user_id = :user_id");
            return $stmt->execute(['id' => $accountId, 'user_id' => $userId]);
        }

        $stmt = $this->db->prepare("DELETE FROM accounts WHERE id = :id AND user_id = :user_id");
        return $stmt->execute(['id' => $accountId, 'user_id' => $userId]);
    }
}
