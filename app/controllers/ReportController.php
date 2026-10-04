<?php
/**
 * Hesabdar - Report Controller
 * Financial Analytics, Cash Flow, Category and Account Breakdown.
 */

require_once __DIR__ . '/Controller.php';
require_once dirname(__DIR__) . '/models/Transaction.php';
require_once dirname(__DIR__) . '/models/Account.php';
require_once dirname(__DIR__) . '/models/Category.php';

class ReportController extends Controller {
    private Transaction $transactionModel;
    private Account $accountModel;
    private Category $categoryModel;

    public function __construct() {
        parent::__construct();
        $this->transactionModel = new Transaction();
        $this->accountModel = new Account();
        $this->categoryModel = new Category();
    }

    /**
     * Reports Overview
     */
    public function index(): void {
        $userId = $this->requireAuth();

        // Selected Jalali Year and Month (defaults to current)
        $currentJalali = Jalali::getJalaliMonthRange();
        $selectedYear = (int)$this->input('year', $currentJalali['year']);
        $selectedMonth = (int)$this->input('month', $currentJalali['month']);

        if ($selectedMonth < 1 || $selectedMonth > 12) {
            $selectedMonth = $currentJalali['month'];
        }

        $monthRange = Jalali::getJalaliMonthRange($selectedYear, $selectedMonth);
        $startDate = $monthRange['start_date'];
        $endDate = $monthRange['end_date'];

        // Period Summary (Income, Expense, Net)
        $summary = $this->transactionModel->getPeriodSummary($userId, $startDate, $endDate);

        // Expense by Category
        $expensesByCategory = $this->transactionModel->getExpensesByCategory($userId, $startDate, $endDate);

        // Calculate Category percentages
        $totalExpense = $summary['total_expense'];
        foreach ($expensesByCategory as &$cat) {
            $cat['percentage'] = ($totalExpense > 0) ? round(($cat['total_amount'] / $totalExpense) * 100, 1) : 0;
        }
        unset($cat);

        // Expense by Account
        $expensesByAccount = $this->transactionModel->getExpensesByAccount($userId, $startDate, $endDate);
        foreach ($expensesByAccount as &$acc) {
            $acc['percentage'] = ($totalExpense > 0) ? round(($acc['total_amount'] / $totalExpense) * 100, 1) : 0;
        }
        unset($acc);

        // Accounts Net Balances
        $accounts = $this->accountModel->getAllForUser($userId, false);
        $totalBalance = $this->accountModel->getTotalBalance($userId);

        $this->render('reports/index', [
            'pageTitle' => 'گزارش‌های مالی',
            'monthRange' => $monthRange,
            'selectedYear' => $selectedYear,
            'selectedMonth' => $selectedMonth,
            'summary' => $summary,
            'expensesByCategory' => $expensesByCategory,
            'expensesByAccount' => $expensesByAccount,
            'accounts' => $accounts,
            'totalBalance' => $totalBalance,
        ]);
    }
}
