<?php
/**
 * Hesabdar - Application Configuration
 * Compatible with cPanel Shared Hosting & Local Development
 */

// Timezone configuration (Iran Standard Time)
date_default_timezone_set('Asia/Tehran');

// Environment ('production' on live cPanel, 'development' for debugging)
defined('APP_ENV') or define('APP_ENV', 'production');

if (APP_ENV === 'development') {
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);
} else {
    ini_set('display_errors', 0);
    ini_set('display_startup_errors', 0);
    error_reporting(0);
}

// Database Credentials
// NOTE: On cPanel, edit these details to match your MySQL database and user created in cPanel
return [
    'db' => [
        'host' => '127.0.0.1',
        'port' => 3306,
        'database' => 'hesabdar_db',
        'username' => 'root',
        'password' => '',
        'charset' => 'utf8mb4',
    ],
    'app' => [
        'name' => 'حسابدار شخصی',
        'version' => '1.0.0',
        'currency_name' => 'ریال',
        'currency_symbol' => 'ریال',
        'session_lifetime' => 86400 * 7, // 7 days
        'max_login_attempts' => 5,
        'lockout_time_minutes' => 15,
        'records_per_page' => 15,
    ],
    'paths' => [
        'root' => dirname(__DIR__, 2),
        'app' => dirname(__DIR__),
        'storage' => dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'storage',
        'views' => dirname(__DIR__) . DIRECTORY_SEPARATOR . 'views',
    ]
];
