<?php
/**
 * Hesabdar - Transaction Model
 * Core ledger for Income, Expense, and Inter-Account Transfers.
 * Enforces Rial integer storage and strict transfer neutrality.
 */

require_once __DIR__ . '/Model.php';

class Transaction extends Model {
    /**
     * Filtered, paginated list of transactions
     */
    public function getPaginated(int $userId, array $filters = [], int $page = 1, int $perPage = 15): array {
        $where = ["t.user_id = :user_id"];
        $params = ['user_id' => $userId];

        // Filter: Type
        if (!empty($filters['type']) && in_array($filters['type'], ['income', 'expense', 'transfer'])) {
            $where[] = "t.type = :type";
            $params['type'] = $filters['type'];
        }

        // Filter: Account (either source or target)
        if (!empty($filters['account_id'])) {
            $where[] = "(t.account_id = :acc_id OR t.to_account_id = :acc_id_target)";
            $params['acc_id'] = (int)$filters['account_id'];
            $params['acc_id_target'] = (int)$filters['account_id'];
        }

        // Filter: Category
        if (!empty($filters['category_id'])) {
            $where[] = "t.category_id = :cat_id";
            $params['cat_id'] = (int)$filters['category_id'];
        }

        // Filter: Date Range (Expects Gregorian YYYY-MM-DD)
        if (!empty($filters['start_date'])) {
            $where[] = "t.transaction_date >= :start_date";
            $params['start_date'] = $filters['start_date'];
        }
        if (!empty($filters['end_date'])) {
            $where[] = "t.transaction_date <= :end_date";
            $params['end_date'] = $filters['end_date'];
        }

        // Filter: Amount Range
        if (!empty($filters['min_amount'])) {
            $where[] = "t.amount_rial >= :min_amount";
            $params['min_amount'] = Format::cleanAmount($filters['min_amount']);
        }
        if (!empty($filters['max_amount'])) {
            $where[] = "t.amount_rial <= :max_amount";
            $params['max_amount'] = Format::cleanAmount($filters['max_amount']);
        }

        // Search: Description
        if (!empty($filters['search'])) {
            $where[] = "t.description LIKE :search";
            $params['search'] = '%' . trim($filters['search']) . '%';
        }

        $whereSql = implode(' AND ', $where);

        // Count total matching records
        $countSql = "SELECT COUNT(*) FROM transactions t WHERE {$whereSql}";
        $countStmt = $this->db->prepare($countSql);
        $countStmt->execute($params);
        $totalRecords = (int)$countStmt->fetchColumn();

        $totalPages = (int)ceil($totalRecords / $perPage);
        $page = max(1, min($page, max(1, $totalPages)));
        $offset = ($page - 1) * $perPage;

        // Fetch page items with Account and Category joins
        $dataSql = "
            SELECT 
                t.*,
                acc.name AS account_name,
                acc.color AS account_color,
                acc.icon AS account_icon,
                to_acc.name AS to_account_name,
                to_acc.color AS to_account_color,
                cat.name AS category_name,
                cat.color AS category_color,
                cat.icon AS category_icon
            FROM transactions t
            LEFT JOIN accounts acc ON acc.id = t.account_id
            LEFT JOIN accounts to_acc ON to_acc.id = t.to_account_id
            LEFT JOIN categories cat ON cat.id = t.category_id
            WHERE {$whereSql}
            ORDER BY t.transaction_date DESC, t.id DESC
            LIMIT :limit OFFSET :offset
        ";

        $stmt = $this->db->prepare($dataSql);
        foreach ($params as $key => $val) {
            $stmt->bindValue($key, $val);
        }
        $stmt->bindValue('limit', $perPage, PDO::PARAM_INT);
        $stmt->bindValue('offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        $items = $stmt->fetchAll();

        return [
            'items' => $items,
            'total' => $totalRecords,
            'per_page' => $perPage,
            'current_page' => $page,
            'total_pages' => $totalPages
        ];
    }

    /**
     * Find single transaction by ID and user
     */
    public function findById(int $id, int $userId): ?array {
        $sql = "
            SELECT 
                t.*,
                acc.name AS account_name,
                to_acc.name AS to_account_name,
                cat.name AS category_name
            FROM transactions t
            LEFT JOIN accounts acc ON acc.id = t.account_id
            LEFT JOIN accounts to_acc ON to_acc.id = t.to_account_id
            LEFT JOIN categories cat ON cat.id = t.category_id
            WHERE t.id = :id AND t.user_id = :user_id
            LIMIT 1
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $id, 'user_id' => $userId]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    /**
     * Create a new transaction
     */
    public function create(array $data): int {
        $type = $data['type'];
        $accountId = (int)$data['account_id'];
        $toAccountId = ($type === 'transfer') ? (int)($data['to_account_id'] ?? 0) : null;
        $categoryId = ($type !== 'transfer' && !empty($data['category_id'])) ? (int)$data['category_id'] : null;
        $amount = Format::cleanAmount($data['amount_rial']);

        $stmt = $this->db->prepare("
            INSERT INTO transactions (
                user_id, type, amount_rial, account_id, to_account_id, category_id,
                transaction_date, description, created_at
            ) VALUES (
                :user_id, :type, :amount_rial, :account_id, :to_account_id, :category_id,
                :transaction_date, :description, NOW()
            )
        ");

        $stmt->execute([
            'user_id' => $data['user_id'],
            'type' => $type,
            'amount_rial' => $amount,
            'account_id' => $accountId,
            'to_account_id' => $toAccountId ?: null,
            'category_id' => $categoryId ?: null,
            'transaction_date' => $data['transaction_date'],
            'description' => !empty($data['description']) ? trim($data['description']) : null
        ]);

        return (int)$this->db->lastInsertId();
    }

    /**
     * Update an existing transaction
     */
    public function update(int $id, int $userId, array $data): bool {
        $type = $data['type'];
        $accountId = (int)$data['account_id'];
        $toAccountId = ($type === 'transfer') ? (int)($data['to_account_id'] ?? 0) : null;
        $categoryId = ($type !== 'transfer' && !empty($data['category_id'])) ? (int)$data['category_id'] : null;
        $amount = Format::cleanAmount($data['amount_rial']);

        $stmt = $this->db->prepare("
            UPDATE transactions
            SET type = :type,
                amount_rial = :amount_rial,
                account_id = :account_id,
                to_account_id = :to_account_id,
                category_id = :category_id,
                transaction_date = :transaction_date,
                description = :description,
                updated_at = NOW()
            WHERE id = :id AND user_id = :user_id
        ");

        return $stmt->execute([
            'type' => $type,
            'amount_rial' => $amount,
            'account_id' => $accountId,
            'to_account_id' => $toAccountId ?: null,
            'category_id' => $categoryId ?: null,
            'transaction_date' => $data['transaction_date'],
            'description' => !empty($data['description']) ? trim($data['description']) : null,
            'id' => $id,
            'user_id' => $userId
        ]);
    }

    /**
     * Delete transaction (automatically restores calculated account balances)
     */
    public function delete(int $id, int $userId): bool {
        $stmt = $this->db->prepare("DELETE FROM transactions WHERE id = :id AND user_id = :user_id");
        return $stmt->execute([
            'id' => $id,
            'user_id' => $userId
        ]);
    }

    /**
     * Get recent transactions for Dashboard display
     */
    public function getRecent(int $userId, int $limit = 8): array {
        $sql = "
            SELECT 
                t.*,
                acc.name AS account_name,
                acc.color AS account_color,
                to_acc.name AS to_account_name,
                cat.name AS category_name,
                cat.color AS category_color,
                cat.icon AS category_icon
            FROM transactions t
            LEFT JOIN accounts acc ON acc.id = t.account_id
            LEFT JOIN accounts to_acc ON to_acc.id = t.to_account_id
            LEFT JOIN categories cat ON cat.id = t.category_id
            WHERE t.user_id = :user_id
            ORDER BY t.transaction_date DESC, t.id DESC
            LIMIT :limit
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue('user_id', $userId, PDO::PARAM_INT);
        $stmt->bindValue('limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Get monthly financial summary (Income, Expense, Net Flow)
     * CRITICAL RULE: Inter-account transfers are strictly excluded from income and expense!
     */
    public function getPeriodSummary(int $userId, string $startDate, string $endDate): array {
        $sql = "
            SELECT 
                COALESCE(SUM(CASE WHEN type = 'income' THEN amount_rial ELSE 0 END), 0) AS total_income,
                COALESCE(SUM(CASE WHEN type = 'expense' THEN amount_rial ELSE 0 END), 0) AS total_expense,
                COALESCE(SUM(CASE WHEN type = 'transfer' THEN amount_rial ELSE 0 END), 0) AS total_transfers,
                COUNT(*) AS total_count
            FROM transactions
            WHERE user_id = :user_id 
              AND transaction_date BETWEEN :start_date AND :end_date
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'user_id' => $userId,
            'start_date' => $startDate,
            'end_date' => $endDate
        ]);

        $row = $stmt->fetch();
        $income = (int)($row['total_income'] ?? 0);
        $expense = (int)($row['total_expense'] ?? 0);
        $transfers = (int)($row['total_transfers'] ?? 0);
        $count = (int)($row['total_count'] ?? 0);
        $netFlow = $income - $expense;

        return [
            'total_income' => $income,
            'total_expense' => $expense,
            'total_transfers' => $transfers,
            'net_flow' => $netFlow,
            'count' => $count
        ];
    }

    /**
     * Get expense breakdown by Category for reporting
     */
    public function getExpensesByCategory(int $userId, string $startDate, string $endDate): array {
        $sql = "
            SELECT 
                COALESCE(cat.id, 0) AS category_id,
                COALESCE(cat.name, 'بدون دسته‌بندی') AS category_name,
                COALESCE(cat.color, '#94a3b8') AS category_color,
                COALESCE(cat.icon, 'tag') AS category_icon,
                SUM(t.amount_rial) AS total_amount,
                COUNT(t.id) AS tx_count
            FROM transactions t
            LEFT JOIN categories cat ON cat.id = t.category_id
            WHERE t.user_id = :user_id 
              AND t.type = 'expense'
              AND t.transaction_date BETWEEN :start_date AND :end_date
            GROUP BY cat.id, cat.name, cat.color, cat.icon
            ORDER BY total_amount DESC
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'user_id' => $userId,
            'start_date' => $startDate,
            'end_date' => $endDate
        ]);

        return $stmt->fetchAll();
    }

    /**
     * Get expense breakdown by Account for reporting
     */
    public function getExpensesByAccount(int $userId, string $startDate, string $endDate): array {
        $sql = "
            SELECT 
                acc.id AS account_id,
                acc.name AS account_name,
                acc.color AS account_color,
                acc.type AS account_type,
                SUM(t.amount_rial) AS total_amount,
                COUNT(t.id) AS tx_count
            FROM transactions t
            INNER JOIN accounts acc ON acc.id = t.account_id
            WHERE t.user_id = :user_id 
              AND t.type = 'expense'
              AND t.transaction_date BETWEEN :start_date AND :end_date
            GROUP BY acc.id, acc.name, acc.color, acc.type
            ORDER BY total_amount DESC
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'user_id' => $userId,
            'start_date' => $startDate,
            'end_date' => $endDate
        ]);

        return $stmt->fetchAll();
    }
}
