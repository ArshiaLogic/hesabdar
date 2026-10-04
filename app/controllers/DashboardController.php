<?php
/**
 * Hesabdar - Dashboard Controller
 * Mobile-First Overview of Personal Finances
 */

require_once __DIR__ . '/Controller.php';
require_once dirname(__DIR__) . '/models/Account.php';
require_once dirname(__DIR__) . '/models/Transaction.php';
require_once dirname(__DIR__) . '/models/Category.php';

class DashboardController extends Controller {
    private Account $accountModel;
    private Transaction $transactionModel;
    private Category $categoryModel;

    public function __construct() {
        parent::__construct();
        $this->accountModel = new Account();
        $this->transactionModel = new Transaction();
        $this->categoryModel = new Category();
    }

    /**
     * Main dashboard view
     */
    public function index(): void {
        $userId = $this->requireAuth();

        // Current Jalali Month Date Range
        $currentMonth = Jalali::getJalaliMonthRange();
        $startDate = $currentMonth['start_date'];
        $endDate = $currentMonth['end_date'];

        // Financial Metrics
        $totalBalance = $this->accountModel->getTotalBalance($userId);
        $monthSummary = $this->transactionModel->getPeriodSummary($userId, $startDate, $endDate);

        // Accounts list with live balances
        $accounts = $this->accountModel->getAllForUser($userId, false);

        // Recent Transactions (limit 8)
        $recentTransactions = $this->transactionModel->getRecent($userId, 8);

        // Expense and Income categories for quick action bottom sheet
        $expenseCategories = $this->categoryModel->getAllForUser($userId, 'expense');
        $incomeCategories = $this->categoryModel->getAllForUser($userId, 'income');

        $this->render('dashboard/index', [
            'pageTitle' => 'داشبورد مالی',
            'currentMonth' => $currentMonth,
            'totalBalance' => $totalBalance,
            'monthSummary' => $monthSummary,
            'accounts' => $accounts,
            'recentTransactions' => $recentTransactions,
            'expenseCategories' => $expenseCategories,
            'incomeCategories' => $incomeCategories,
        ]);
    }
}
