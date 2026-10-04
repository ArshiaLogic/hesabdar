<?php
/**
 * Hesabdar - Base Controller
 */

require_once dirname(__DIR__) . '/helpers/Session.php';
require_once dirname(__DIR__) . '/helpers/Csrf.php';
require_once dirname(__DIR__) . '/helpers/Format.php';
require_once dirname(__DIR__) . '/helpers/Jalali.php';
require_once dirname(__DIR__) . '/helpers/Validator.php';

abstract class Controller {
    protected array $config;

    public function __construct() {
        $this->config = require dirname(__DIR__) . '/config/config.php';
        Session::start();
    }

    /**
     * Require authenticated session, or redirect to login
     */
    protected function requireAuth(): int {
        if (!Session::check()) {
            Session::setFlash('warning', 'برای دسترسی به این بخش، لطفاً ابتدا وارد حساب کاربری خود شوید.');
            $this->redirect('login');
        }
        return Session::userId();
    }

    /**
     * Render a view within layouts
     */
    protected function render(string $viewPath, array $data = [], string $layout = 'main'): void {
        extract($data);
        $user = Session::user();
        $flashes = Session::getFlashes();
        $csrfToken = Csrf::token();

        $viewFile = dirname(__DIR__) . '/views/' . $viewPath . '.php';

        if (!file_exists($viewFile)) {
            die("View file not found: " . htmlspecialchars($viewPath));
        }

        // Start output buffering for view content
        ob_start();
        require $viewFile;
        $content = ob_get_clean();

        // Render with layout
        if ($layout === 'main') {
            require dirname(__DIR__) . '/views/layouts/main.php';
        } elseif ($layout === 'auth') {
            require dirname(__DIR__) . '/views/layouts/auth.php';
        } else {
            echo $content;
        }
    }

    /**
     * Return JSON response
     */
    protected function json(array $data, int $status = 200): void {
        http_response_code($status);
        header('Content-Type: application/json; charset=UTF-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        exit;
    }

    /**
     * Redirect to relative route or full URL
     */
    protected function redirect(string $route): void {
        // Build base path dynamically
        $baseUrl = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\');
        if ($route === '') {
            $url = $baseUrl . '/';
        } else {
            $url = $baseUrl . '/index.php?r=' . ltrim($route, '/');
        }
        header("Location: " . $url);
        exit;
    }

    /**
     * Safely read request input
     */
    protected function input(string $key, mixed $default = null): mixed {
        return $_POST[$key] ?? $_GET[$key] ?? $default;
    }
}
