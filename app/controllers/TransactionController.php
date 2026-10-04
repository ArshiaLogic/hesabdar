<?php
/**
 * Hesabdar - Transaction Controller
 * Full CRUD, filtering, pagination, and transfer processing.
 */

require_once __DIR__ . '/Controller.php';
require_once dirname(__DIR__) . '/models/Transaction.php';
require_once dirname(__DIR__) . '/models/Account.php';
require_once dirname(__DIR__) . '/models/Category.php';

class TransactionController extends Controller {
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
     * List transactions with filter & pagination
     */
    public function index(): void {
        $userId = $this->requireAuth();

        $page = (int)($this->input('page', 1));
        $perPage = 15;

        // Parse date filters (may be provided as Jalali or Gregorian)
        $rawStartDate = $this->input('start_date');
        $rawEndDate = $this->input('end_date');

        $gStartDate = null;
        if (!empty($rawStartDate)) {
            $gStartDate = Jalali::toGregorian($rawStartDate) ?: $rawStartDate;
        }

        $gEndDate = null;
        if (!empty($rawEndDate)) {
            $gEndDate = Jalali::toGregorian($rawEndDate) ?: $rawEndDate;
        }

        $filters = [
            'type' => $this->input('type'),
            'account_id' => $this->input('account_id'),
            'category_id' => $this->input('category_id'),
            'start_date' => $gStartDate,
            'end_date' => $gEndDate,
            'min_amount' => $this->input('min_amount'),
            'max_amount' => $this->input('max_amount'),
            'search' => $this->input('search'),
        ];

        $result = $this->transactionModel->getPaginated($userId, $filters, $page, $perPage);
        $accounts = $this->accountModel->getAllForUser($userId, true);
        $categories = $this->categoryModel->getAllForUser($userId, null, true);

        $this->render('transactions/index', [
            'pageTitle' => 'لیست تراکنش‌ها',
            'transactions' => $result['items'],
            'pagination' => $result,
            'filters' => $filters,
            'rawStartDate' => $rawStartDate,
            'rawEndDate' => $rawEndDate,
            'accounts' => $accounts,
            'categories' => $categories,
        ]);
    }

    /**
     * Show create transaction form
     */
    public function create(): void {
        $userId = $this->requireAuth();

        $accounts = $this->accountModel->getAllForUser($userId, false);
        $expenseCategories = $this->categoryModel->getAllForUser($userId, 'expense');
        $incomeCategories = $this->categoryModel->getAllForUser($userId, 'income');

        $defaultType = $this->input('type', 'expense');

        $this->render('transactions/create', [
            'pageTitle' => 'ثبت تراکنش جدید',
            'accounts' => $accounts,
            'expenseCategories' => $expenseCategories,
            'incomeCategories' => $incomeCategories,
            'defaultType' => $defaultType,
            'todayJalali' => Jalali::toJalali(date('Y-m-d'), 'Y/m/d', false),
        ]);
    }

    /**
     * Store new transaction
     */
    public function store(): void {
        $userId = $this->requireAuth();
        Csrf::verifyOrAbort();

        $type = $_POST['type'] ?? 'expense';
        $rawAmount = $_POST['amount_rial'] ?? '';
        $amount = Format::cleanAmount($rawAmount);
        $accountId = (int)($_POST['account_id'] ?? 0);
        $toAccountId = ($type === 'transfer') ? (int)($_POST['to_account_id'] ?? 0) : null;
        $categoryId = ($type !== 'transfer' && !empty($_POST['category_id'])) ? (int)$_POST['category_id'] : null;
        $rawDate = trim($_POST['transaction_date'] ?? '');
        $description = trim($_POST['description'] ?? '');

        // Convert date to Gregorian
        $gregorianDate = null;
        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $rawDate)) {
            $gregorianDate = $rawDate;
        } else {
            $gregorianDate = Jalali::toGregorian($rawDate);
        }

        // Validate
        $errors = [];
        if (!in_array($type, ['income', 'expense', 'transfer'], true)) {
            $errors[] = 'نوع تراکنش نامعتبر است.';
        }
        if ($amount <= 0) {
            $errors[] = 'مبلغ باید عددی بزرگتر از صفر باشد.';
        }
        if ($accountId <= 0) {
            $errors[] = 'لطفاً حساب مورد نظر را انتخاب کنید.';
        }
        if ($type === 'transfer') {
            if ($toAccountId <= 0) {
                $errors[] = 'لطفاً حساب مقصد انتقال را انتخاب کنید.';
            } elseif ($accountId === $toAccountId) {
                $errors[] = 'حساب مبدأ و مقصد انتقال نمی‌توانند یکسان باشند.';
            }
        }
        if (!$gregorianDate) {
            $errors[] = 'تاریخ وارد شده نامعتبر است.';
        }

        if (!empty($errors)) {
            Session::setFlash('error', implode('<br>', $errors));
            $this->redirect('transactions/create');
        }

        $this->transactionModel->create([
            'user_id' => $userId,
            'type' => $type,
            'amount_rial' => $amount,
            'account_id' => $accountId,
            'to_account_id' => $toAccountId,
            'category_id' => $categoryId,
            'transaction_date' => $gregorianDate,
            'description' => $description,
        ]);

        Session::setFlash('success', 'تراکنش با موفقیت ثبت شد.');
        $this->redirect('transactions');
    }

    /**
     * Show edit form
     */
    public function edit(): void {
        $userId = $this->requireAuth();
        $id = (int)$this->input('id', 0);

        $tx = $this->transactionModel->findById($id, $userId);
        if (!$tx) {
            Session::setFlash('error', 'تراکنش مورد نظر یافت نشد.');
            $this->redirect('transactions');
        }

        $accounts = $this->accountModel->getAllForUser($userId, true);
        $expenseCategories = $this->categoryModel->getAllForUser($userId, 'expense', true);
        $incomeCategories = $this->categoryModel->getAllForUser($userId, 'income', true);

        $jalaliDate = Jalali::toJalali($tx['transaction_date'], 'Y/m/d', false);

        $this->render('transactions/edit', [
            'pageTitle' => 'ویرایش تراکنش',
            'transaction' => $tx,
            'accounts' => $accounts,
            'expenseCategories' => $expenseCategories,
            'incomeCategories' => $incomeCategories,
            'jalaliDate' => $jalaliDate,
        ]);
    }

    /**
     * Update transaction
     */
    public function update(): void {
        $userId = $this->requireAuth();
        Csrf::verifyOrAbort();

        $id = (int)($_POST['id'] ?? 0);
        $tx = $this->transactionModel->findById($id, $userId);
        if (!$tx) {
            Session::setFlash('error', 'تراکنش مورد نظر یافت نشد.');
            $this->redirect('transactions');
        }

        $type = $_POST['type'] ?? 'expense';
        $rawAmount = $_POST['amount_rial'] ?? '';
        $amount = Format::cleanAmount($rawAmount);
        $accountId = (int)($_POST['account_id'] ?? 0);
        $toAccountId = ($type === 'transfer') ? (int)($_POST['to_account_id'] ?? 0) : null;
        $categoryId = ($type !== 'transfer' && !empty($_POST['category_id'])) ? (int)$_POST['category_id'] : null;
        $rawDate = trim($_POST['transaction_date'] ?? '');
        $description = trim($_POST['description'] ?? '');

        // Convert date to Gregorian
        $gregorianDate = null;
        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $rawDate)) {
            $gregorianDate = $rawDate;
        } else {
            $gregorianDate = Jalali::toGregorian($rawDate);
        }

        if ($amount <= 0 || $accountId <= 0 || !$gregorianDate) {
            Session::setFlash('error', 'اطلاعات وارد شده نامعتبر است.');
            $this->redirect('transactions/edit&id=' . $id);
        }

        if ($type === 'transfer' && ($toAccountId <= 0 || $accountId === $toAccountId)) {
            Session::setFlash('error', 'حساب مبدأ و مقصد انتقال نمی‌توانند یکسان باشند.');
            $this->redirect('transactions/edit&id=' . $id);
        }

        $this->transactionModel->update($id, $userId, [
            'type' => $type,
            'amount_rial' => $amount,
            'account_id' => $accountId,
            'to_account_id' => $toAccountId,
            'category_id' => $categoryId,
            'transaction_date' => $gregorianDate,
            'description' => $description,
        ]);

        Session::setFlash('success', 'تراکنش با موفقیت بروزرسانی شد.');
        $this->redirect('transactions');
    }

    /**
     * Delete transaction
     */
    public function delete(): void {
        $userId = $this->requireAuth();
        Csrf::verifyOrAbort();

        $id = (int)($_POST['id'] ?? 0);
        if ($id > 0) {
            $this->transactionModel->delete($id, $userId);
            Session::setFlash('success', 'تراکنش با موفقیت حذف شد.');
        }

        $this->redirect('transactions');
    }
}
