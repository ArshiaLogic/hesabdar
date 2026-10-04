<?php
/**
 * Hesabdar - Authentication Controller
 */

require_once __DIR__ . '/Controller.php';
require_once dirname(__DIR__) . '/services/AuthService.php';

class AuthController extends Controller {
    private AuthService $authService;

    public function __construct() {
        parent::__construct();
        $this->authService = new AuthService();
    }

    /**
     * Show login form
     */
    public function showLogin(): void {
        if (Session::check()) {
            $this->redirect('dashboard');
        }

        $this->render('auth/login', [
            'pageTitle' => 'ورود به حساب کاربری'
        ], 'auth');
    }

    /**
     * Handle login submission
     */
    public function login(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('login');
        }

        Csrf::verifyOrAbort();

        $email = trim($_POST['email'] ?? '');
        $password = trim($_POST['password'] ?? '');
        $ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';

        $validator = Validator::make($_POST)
            ->required('email', 'لطفاً آدرس ایمیل را وارد نمایید.')
            ->email('email', 'فرمت آدرس ایمیل نامعتبر است.')
            ->required('password', 'لطفاً کلمه عبور را وارد نمایید.');

        if ($validator->fails()) {
            Session::setFlash('error', $validator->firstError());
            $this->render('auth/login', [
                'pageTitle' => 'ورود به حساب کاربری',
                'email' => $email
            ], 'auth');
            return;
        }

        $result = $this->authService->attempt($email, $password, $ip);

        if (!$result['success']) {
            Session::setFlash('error', $result['error']);
            $this->render('auth/login', [
                'pageTitle' => 'ورود به حساب کاربری',
                'email' => $email
            ], 'auth');
            return;
        }

        Session::setFlash('success', 'خوش آمدید! ورود با موفقیت انجام شد.');
        $this->redirect('dashboard');
    }

    /**
     * Handle logout
     */
    public function logout(): void {
        $this->authService->logout();
        Session::start();
        Session::setFlash('info', 'شما با موفقیت از سیستم خارج شدید.');
        $this->redirect('login');
    }
}
