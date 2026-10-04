<?php
/**
 * Hesabdar - Settings & Export Controller
 */

require_once __DIR__ . '/Controller.php';
require_once dirname(__DIR__) . '/models/User.php';
require_once dirname(__DIR__) . '/services/ExportService.php';

class SettingsController extends Controller {
    private User $userModel;
    private ExportService $exportService;

    public function __construct() {
        parent::__construct();
        $this->userModel = new User();
        $this->exportService = new ExportService();
    }

    /**
     * Settings Dashboard
     */
    public function index(): void {
        $userId = $this->requireAuth();
        $user = $this->userModel->findById($userId);

        $this->render('settings/index', [
            'pageTitle' => 'تنظیمات و پشتیبان‌گیری',
            'user' => $user
        ]);
    }

    /**
     * Update user profile (name and email)
     */
    public function updateProfile(): void {
        $userId = $this->requireAuth();
        Csrf::verifyOrAbort();

        $name = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');

        $validator = Validator::make($_POST)
            ->required('name', 'نام کاربر الزامی است.')
            ->required('email', 'آدرس ایمیل الزامی است.')
            ->email('email', 'فرمت ایمیل نامعتبر است.');

        if ($validator->fails()) {
            Session::setFlash('error', $validator->firstError());
            $this->redirect('settings');
        }

        // Check if email already used by someone else
        $existing = $this->userModel->findByEmail($email);
        if ($existing && (int)$existing['id'] !== $userId) {
            Session::setFlash('error', 'این آدرس ایمیل قبلاً توسط حساب کاربری دیگری استفاده شده است.');
            $this->redirect('settings');
        }

        $this->userModel->updateProfile($userId, $name, $email);
        $_SESSION['user_name'] = $name;
        $_SESSION['user_email'] = $email;

        Session::setFlash('success', 'اطلاعات کاربری با موفقیت بروزرسانی شد.');
        $this->redirect('settings');
    }

    /**
     * Change user password
     */
    public function updatePassword(): void {
        $userId = $this->requireAuth();
        Csrf::verifyOrAbort();

        $currentPassword = $_POST['current_password'] ?? '';
        $newPassword = $_POST['new_password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';

        $user = $this->userModel->findById($userId);
        $fullUser = $this->userModel->findByEmail($user['email']);

        if (!password_verify($currentPassword, $fullUser['password_hash'])) {
            Session::setFlash('error', 'کلمه عبور فعلی نادرست است.');
            $this->redirect('settings');
        }

        if (mb_strlen($newPassword) < 8) {
            Session::setFlash('error', 'کلمه عبور جدید باید حداقل ۸ کاراکتر باشد.');
            $this->redirect('settings');
        }

        if ($newPassword !== $confirmPassword) {
            Session::setFlash('error', 'کلمه عبور جدید با تکرار آن یکسان نیست.');
            $this->redirect('settings');
        }

        $this->userModel->updatePassword($userId, $newPassword);
        Session::setFlash('success', 'کلمه عبور شما با موفقیت تغییر یافت.');
        $this->redirect('settings');
    }

    /**
     * Export CSV of transactions
     */
    public function exportCsv(): void {
        $userId = $this->requireAuth();
        $this->exportService->exportTransactionsCsv($userId);
    }

    /**
     * Export all data as JSON
     */
    public function exportJson(): void {
        $userId = $this->requireAuth();
        $this->exportService->exportDataJson($userId);
    }

    /**
     * Export SQL database dump
     */
    public function exportSql(): void {
        $userId = $this->requireAuth();
        $this->exportService->generateSqlBackup($userId);
    }
}
