<?php
/**
 * Hesabdar - CSRF (Cross-Site Request Forgery) Protection Helper
 */

class Csrf {
    private const SESSION_KEY = 'csrf_token';

    /**
     * Generate or retrieve existing CSRF token
     */
    public static function token(): string {
        Session::start();
        if (empty($_SESSION[self::SESSION_KEY])) {
            $_SESSION[self::SESSION_KEY] = bin2hex(random_bytes(32));
        }
        return $_SESSION[self::SESSION_KEY];
    }

    /**
     * Verify CSRF token from request
     */
    public static function validate(?string $token): bool {
        Session::start();
        if (empty($token) || empty($_SESSION[self::SESSION_KEY])) {
            return false;
        }
        return hash_equals($_SESSION[self::SESSION_KEY], $token);
    }

    /**
     * Render hidden input element for forms
     */
    public static function field(): string {
        $token = self::token();
        return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars($token, ENT_QUOTES, 'UTF-8') . '">';
    }

    /**
     * Verify token or terminate with 403 Forbidden
     */
    public static function verifyOrAbort(): void {
        $token = $_POST['csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? null;
        if (!self::validate($token)) {
            http_response_code(403);
            die('خطای امنیتی: نشست یا توکن امنیتی (CSRF) نامعتبر است. لطفاً صفحه را رفرش کرده و مجدداً تلاش کنید.');
        }
    }
}
