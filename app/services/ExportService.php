<?php
/**
 * Hesabdar - Export & Backup Service
 * Supports CSV (with UTF-8 BOM for Microsoft Excel Persian support), JSON, and SQL database dump.
 */

require_once dirname(__DIR__) . '/config/database.php';
require_once dirname(__DIR__) . '/helpers/Jalali.php';
require_once dirname(__DIR__) . '/helpers/Format.php';

class ExportService {
    private PDO $db;

    public function __construct() {
        $this->db = Database::getConnection();
    }

    /**
     * Export transactions as CSV with UTF-8 BOM
     */
    public function exportTransactionsCsv(int $userId): void {
        $stmt = $this->db->prepare("
            SELECT 
                t.id,
                t.transaction_date,
                t.type,
                t.amount_rial,
                acc.name AS account_name,
                to_acc.name AS to_account_name,
                cat.name AS category_name,
                t.description,
                t.created_at
            FROM transactions t
            LEFT JOIN accounts acc ON acc.id = t.account_id
            LEFT JOIN accounts to_acc ON to_acc.id = t.to_account_id
            LEFT JOIN categories cat ON cat.id = t.category_id
            WHERE t.user_id = :user_id
            ORDER BY t.transaction_date DESC, t.id DESC
        ");
        $stmt->execute(['user_id' => $userId]);
        $rows = $stmt->fetchAll();

        $filename = 'hesabdar_transactions_' . date('Y-m-d_His') . '.csv';

        header('Content-Type: text/csv; charset=UTF-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Pragma: no-cache');
        header('Expires: 0');

        $output = fopen('php://output', 'w');

        // UTF-8 BOM for Excel compatibility with Persian letters
        fputs($output, "\xEF\xBB\xBF");

        // Header row
        fputcsv($output, [
            'شناسه',
            'تاریخ شمسی',
            'تاریخ میلادی',
            'نوع تراکنش',
            'مبلغ (ریال)',
            'حساب مبدأ',
            'حساب مقصد (انتقال)',
            'دسته‌بندی',
            'توضیحات'
        ]);

        foreach ($rows as $r) {
            $typeFa = Format::transactionTypeFa($r['type']);
            $jalaliDate = Jalali::toJalali($r['transaction_date'], 'Y/m/d', false);

            fputcsv($output, [
                $r['id'],
                $jalaliDate,
                $r['transaction_date'],
                $typeFa,
                $r['amount_rial'],
                $r['account_name'] ?? '',
                $r['to_account_name'] ?? '',
                $r['category_name'] ?? '',
                $r['description'] ?? ''
            ]);
        }

        fclose($output);
        exit;
    }

    /**
     * Export all user data as JSON format
     */
    public function exportDataJson(int $userId): void {
        // Accounts
        $accStmt = $this->db->prepare("SELECT * FROM accounts WHERE user_id = :uid");
        $accStmt->execute(['uid' => $userId]);
        $accounts = $accStmt->fetchAll();

        // Categories
        $catStmt = $this->db->prepare("SELECT * FROM categories WHERE user_id = :uid");
        $catStmt->execute(['uid' => $userId]);
        $categories = $catStmt->fetchAll();

        // Transactions
        $txStmt = $this->db->prepare("SELECT * FROM transactions WHERE user_id = :uid ORDER BY transaction_date DESC");
        $txStmt->execute(['uid' => $userId]);
        $transactions = $txStmt->fetchAll();

        $payload = [
            'export_date' => date('c'),
            'generator' => 'Hesabdar Personal Finance App',
            'user_id' => $userId,
            'accounts' => $accounts,
            'categories' => $categories,
            'transactions' => $transactions
        ];

        $filename = 'hesabdar_backup_' . date('Y-m-d_His') . '.json';

        header('Content-Type: application/json; charset=UTF-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Pragma: no-cache');
        header('Expires: 0');

        echo json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        exit;
    }

    /**
     * Generate SQL Backup dump
     */
    public function generateSqlBackup(int $userId): void {
        $filename = 'hesabdar_sql_backup_' . date('Y-m-d_His') . '.sql';

        header('Content-Type: application/sql; charset=UTF-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Pragma: no-cache');
        header('Expires: 0');

        $out = fopen('php://output', 'w');
        fputs($out, "-- Hesabdar Personal Finance Backup\n");
        fputs($out, "-- Generated at: " . date('Y-m-d H:i:s') . "\n");
        fputs($out, "SET NAMES utf8mb4;\nSET FOREIGN_KEY_CHECKS = 0;\n\n");

        $tables = [
            'accounts' => "SELECT * FROM accounts WHERE user_id = {$userId}",
            'categories' => "SELECT * FROM categories WHERE user_id = {$userId}",
            'transactions' => "SELECT * FROM transactions WHERE user_id = {$userId}",
            'settings' => "SELECT * FROM settings WHERE user_id = {$userId}"
        ];

        foreach ($tables as $table => $sql) {
            $stmt = $this->db->query($sql);
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

            fputs($out, "-- Table data for `{$table}`\n");
            foreach ($rows as $row) {
                $cols = array_map(fn($c) => "`{$c}`", array_keys($row));
                $vals = array_map(function($v) {
                    if ($v === null) return 'NULL';
                    return $this->db->quote($v);
                }, array_values($row));

                fputs($out, "INSERT INTO `{$table}` (" . implode(', ', $cols) . ") VALUES (" . implode(', ', $vals) . ");\n");
            }
            fputs($out, "\n");
        }

        fputs($out, "SET FOREIGN_KEY_CHECKS = 1;\n");
        fclose($out);
        exit;
    }
}
