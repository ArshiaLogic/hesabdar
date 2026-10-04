<?php
/**
 * Hesabdar (حسابدار) - Front Controller & Application Router
 * Fast, lightweight routing compatible with cPanel shared hosting.
 */

// Define application root
define('ROOT_PATH', dirname(__DIR__));
define('APP_PATH', ROOT_PATH . '/app');

// Autoload / Require dependencies
require_once APP_PATH . '/config/database.php';
require_once APP_PATH . '/helpers/Session.php';
require_once APP_PATH . '/helpers/Csrf.php';
require_once APP_PATH . '/helpers/Format.php';
require_once APP_PATH . '/helpers/Jalali.php';
require_once APP_PATH . '/helpers/Validator.php';

// Controllers
require_once APP_PATH . '/controllers/AuthController.php';
require_once APP_PATH . '/controllers/DashboardController.php';
require_once APP_PATH . '/controllers/TransactionController.php';
require_once APP_PATH . '/controllers/AccountController.php';
require_once APP_PATH . '/controllers/CategoryController.php';
require_once APP_PATH . '/controllers/ReportController.php';
require_once APP_PATH . '/controllers/SettingsController.php';

// Determine route: priority to 'r' query param, fallback to PATH_INFO
$route = $_GET['r'] ?? '';
if (empty($route) && !empty($_SERVER['PATH_INFO'])) {
    $route = trim($_SERVER['PATH_INFO'], '/');
}
$route = trim($route, '/');
if (empty($route)) {
    $route = 'dashboard';
}

$method = $_SERVER['REQUEST_METHOD'];

try {
    switch ($route) {
        // Authentication
        case 'login':
            $controller = new AuthController();
            if ($method === 'POST') {
                $controller->login();
            } else {
                $controller->showLogin();
            }
            break;

        case 'logout':
            (new AuthController())->logout();
            break;

        // Dashboard
        case 'dashboard':
            (new DashboardController())->index();
            break;

        // Transactions
        case 'transactions':
            (new TransactionController())->index();
            break;

        case 'transactions/create':
            (new TransactionController())->create();
            break;

        case 'transactions/store':
            (new TransactionController())->store();
            break;

        case 'transactions/edit':
            (new TransactionController())->edit();
            break;

        case 'transactions/update':
            (new TransactionController())->update();
            break;

        case 'transactions/delete':
            (new TransactionController())->delete();
            break;

        // Accounts
        case 'accounts':
            (new AccountController())->index();
            break;

        case 'accounts/create':
            (new AccountController())->create();
            break;

        case 'accounts/store':
            (new AccountController())->store();
            break;

        case 'accounts/edit':
            (new AccountController())->edit();
            break;

        case 'accounts/update':
            (new AccountController())->update();
            break;

        case 'accounts/delete':
            (new AccountController())->delete();
            break;

        // Categories
        case 'categories':
            (new CategoryController())->index();
            break;

        case 'categories/store':
            (new CategoryController())->store();
            break;

        case 'categories/update':
            (new CategoryController())->update();
            break;

        case 'categories/delete':
            (new CategoryController())->delete();
            break;

        // Reports
        case 'reports':
            (new ReportController())->index();
            break;

        // Settings & Backups
        case 'settings':
            (new SettingsController())->index();
            break;

        case 'settings/updateProfile':
            (new SettingsController())->updateProfile();
            break;

        case 'settings/updatePassword':
            (new SettingsController())->updatePassword();
            break;

        case 'settings/exportCsv':
            (new SettingsController())->exportCsv();
            break;

        case 'settings/exportJson':
            (new SettingsController())->exportJson();
            break;

        case 'settings/exportSql':
            (new SettingsController())->exportSql();
            break;

        default:
            http_response_code(404);
            header("Location: index.php?r=dashboard");
            exit;
    }
} catch (Exception $e) {
    http_response_code(500);
    $errorMsg = $e->getMessage();
    require APP_PATH . '/views/layouts/auth.php'; // Renders clean error card
    echo '<div style="color:var(--danger); font-weight:bold; text-align:center; padding: 2rem;">' . htmlspecialchars($errorMsg) . '</div>';
    exit;
}
