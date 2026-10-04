<?php
/**
 * Hesabdar - Secure Session & Flash Message Helper
 */

class Session {
    private static bool $started = false;

    /**
     * Start session with hardened security settings
     */
    public static function start(): void {
        if (self::$started || session_status() === PHP_SESSION_ACTIVE) {
            self::$started = true;
            return;
        }

        $config = require dirname(__DIR__) . '/config/config.php';
        $lifetime = $config['app']['session_lifetime'] ?? 604800; // 7 days

        $isHttps = (
            (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ||
            ($_SERVER['SERVER_PORT'] ?? 80) == 443 ||
            (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https')
        );

        session_set_cookie_params([
            'lifetime' => $lifetime,
            'path' => '/',
            'domain' => '',
            'secure' => $isHttps,
            'httponly' => true,
            'samesite' => 'Lax'
        ]);

        session_name('HESABDAR_SESS');
        session_start();
        self::$started = true;
    }

    /**
     * Authenticate user session
     */
    public static function login(array $user): void {
        self::start();
        session_regenerate_id(true);
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_name'] = $user['name'];
        $_SESSION['user_email'] = $user['email'];
        $_SESSION['last_activity'] = time();
    }

    /**
     * Logout and destroy session completely
     */
    public static function logout(): void {
        self::start();
        $_SESSION = [];

        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(),
                '',
                time() - 42000,
                $params['path'],
                $params['domain'],
                $params['secure'],
                $params['httponly']
            );
        }

        session_destroy();
        self::$started = false;
    }

    /**
     * Check if user is logged in
     */
    public static function check(): bool {
        self::start();
        return !empty($_SESSION['user_id']);
    }

    /**
     * Get logged-in user ID
     */
    public static function userId(): ?int {
        self::start();
        return isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : null;
    }

    /**
     * Get logged-in user profile info
     */
    public static function user(): ?array {
        self::start();
        if (!self::check()) {
            return null;
        }

        return [
            'id' => (int)$_SESSION['user_id'],
            'name' => $_SESSION['user_name'] ?? '',
            'email' => $_SESSION['user_email'] ?? '',
        ];
    }

    /**
     * Set a flash message (success, error, warning, info)
     */
    public static function setFlash(string $type, string $message): void {
        self::start();
        $_SESSION['flash'][$type] = $message;
    }

    /**
     * Retrieve and clear flash messages
     */
    public static function getFlashes(): array {
        self::start();
        $flashes = $_SESSION['flash'] ?? [];
        unset($_SESSION['flash']);
        return $flashes;
    }
}
