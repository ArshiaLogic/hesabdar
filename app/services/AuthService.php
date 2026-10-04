<?php
/**
 * Hesabdar - Authentication Service
 */

require_once dirname(__DIR__) . '/models/User.php';
require_once dirname(__DIR__) . '/helpers/Session.php';

class AuthService {
    private User $userModel;
    private array $config;

    public function __construct() {
        $this->userModel = new User();
        $this->config = require dirname(__DIR__) . '/config/config.php';
    }

    /**
     * Attempt to log in a user
     */
    public function attempt(string $email, string $password, string $ip): array {
        $maxAttempts = (int)($this->config['app']['max_login_attempts'] ?? 5);
        $lockoutMinutes = (int)($this->config['app']['lockout_time_minutes'] ?? 15);

        // Check rate limiting / lockout
        if ($this->userModel->isLockedOut($ip, $email, $maxAttempts, $lockoutMinutes)) {
            return [
                'success' => false,
                'error' => "تعداد تلاش‌های ناموفق شما بیش از حد مجاز بوده است. به دلایل امنیتی لطفاً {$lockoutMinutes} دقیقه صبر کرده و مجدداً تلاش کنید."
            ];
        }

        $user = $this->userModel->authenticate($email, $password);

        if (!$user) {
            $this->userModel->recordFailedAttempt($ip, $email);
            return [
                'success' => false,
                'error' => 'ایمیل یا کلمه عبور وارد شده نادرست است.'
            ];
        }

        // Reset rate limiting counter on success
        $this->userModel->clearLoginAttempts($ip, $email);

        // Establish secure session
        Session::login($user);

        return [
            'success' => true,
            'user' => $user
        ];
    }

    /**
     * Terminate user session
     */
    public function logout(): void {
        Session::logout();
    }
}
