<?php
/**
 * Hesabdar - Account Controller
 * Manages bank cards, cash, and savings accounts.
 */

require_once __DIR__ . '/Controller.php';
require_once dirname(__DIR__) . '/models/Account.php';

class AccountController extends Controller {
    private Account $accountModel;

    public function __construct() {
        parent::__construct();
        $this->accountModel = new Account();
    }

    /**
     * List user accounts
     */
    public function index(): void {
        $userId = $this->requireAuth();
        $accounts = $this->accountModel->getAllForUser($userId, true);
        $totalBalance = $this->accountModel->getTotalBalance($userId);

        $this->render('accounts/index', [
            'pageTitle' => 'مدیریت حساب‌ها و کارت‌ها',
            'accounts' => $accounts,
            'totalBalance' => $totalBalance,
        ]);
    }

    /**
     * Show account creation form
     */
    public function create(): void {
        $this->requireAuth();
        $this->render('accounts/create', [
            'pageTitle' => 'افزودن حساب یا کارت جدید'
        ]);
    }

    /**
     * Store new account
     */
    public function store(): void {
        $userId = $this->requireAuth();
        Csrf::verifyOrAbort();

        $name = trim($_POST['name'] ?? '');
        $type = $_POST['type'] ?? 'bank_card';
        $cardNumber = trim($_POST['card_number'] ?? '');
        $rawOpening = $_POST['opening_balance'] ?? '0';
        $openingBalance = Format::cleanAmount($rawOpening);
        $color = trim($_POST['color'] ?? '#2563eb');
        $icon = trim($_POST['icon'] ?? 'credit-card');

        if (empty($name)) {
            Session::setFlash('error', 'لطفاً نام حساب یا کارت را وارد نمایید.');
            $this->redirect('accounts/create');
        }

        $this->accountModel->create([
            'user_id' => $userId,
            'name' => $name,
            'type' => $type,
            'card_number' => $cardNumber,
            'opening_balance' => $openingBalance,
            'color' => $color,
            'icon' => $icon,
            'status' => 'active'
        ]);

        Session::setFlash('success', 'حساب جدید با موفقیت ایجاد شد.');
        $this->redirect('accounts');
    }

    /**
     * Show edit account form
     */
    public function edit(): void {
        $userId = $this->requireAuth();
        $id = (int)$this->input('id', 0);

        $account = $this->accountModel->findById($id, $userId);
        if (!$account) {
            Session::setFlash('error', 'حساب مورد نظر یافت نشد.');
            $this->redirect('accounts');
        }

        $this->render('accounts/edit', [
            'pageTitle' => 'ویرایش حساب ' . Format::e($account['name']),
            'account' => $account
        ]);
    }

    /**
     * Update account
     */
    public function update(): void {
        $userId = $this->requireAuth();
        Csrf::verifyOrAbort();

        $id = (int)($_POST['id'] ?? 0);
        $account = $this->accountModel->findById($id, $userId);
        if (!$account) {
            Session::setFlash('error', 'حساب مورد نظر یافت نشد.');
            $this->redirect('accounts');
        }

        $name = trim($_POST['name'] ?? '');
        $type = $_POST['type'] ?? 'bank_card';
        $cardNumber = trim($_POST['card_number'] ?? '');
        $rawOpening = $_POST['opening_balance'] ?? '0';
        $openingBalance = Format::cleanAmount($rawOpening);
        $color = trim($_POST['color'] ?? '#2563eb');
        $icon = trim($_POST['icon'] ?? 'credit-card');
        $status = $_POST['status'] ?? 'active';

        if (empty($name)) {
            Session::setFlash('error', 'لطفاً نام حساب را وارد نمایید.');
            $this->redirect('accounts/edit&id=' . $id);
        }

        $this->accountModel->update($id, $userId, [
            'name' => $name,
            'type' => $type,
            'card_number' => $cardNumber,
            'opening_balance' => $openingBalance,
            'color' => $color,
            'icon' => $icon,
            'status' => $status
        ]);

        Session::setFlash('success', 'اطلاعات حساب با موفقیت بروزرسانی شد.');
        $this->redirect('accounts');
    }

    /**
     * Delete or archive account
     */
    public function delete(): void {
        $userId = $this->requireAuth();
        Csrf::verifyOrAbort();

        $id = (int)($_POST['id'] ?? 0);
        if ($id > 0) {
            $hasTx = $this->accountModel->hasTransactions($id);
            $this->accountModel->deleteOrArchive($id, $userId);

            if ($hasTx) {
                Session::setFlash('info', 'به دلیل وجود تراکنش‌های ثبت‌شده برای این حساب، به جای حذف دائم، حساب به حالت بایگانی در آمد تا تاریخچه مالی شما حفظ گردد.');
            } else {
                Session::setFlash('success', 'حساب با موفقیت حذف گردید.');
            }
        }

        $this->redirect('accounts');
    }
}
