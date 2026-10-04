<?php
/**
 * Hesabdar - One-Click Database Setup & Migrator
 * Automatically executes schema.sql and configures initial administrator credentials.
 */

define('ROOT_PATH', dirname(__DIR__));
define('APP_PATH', ROOT_PATH . '/app');

require_once APP_PATH . '/config/database.php';

header('Content-Type: text/html; charset=UTF-8');
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>نصب و راه‌اندازی پایگاه داده | حسابدار</title>
    <style>
        body { font-family: Tahoma, Arial, sans-serif; background: #f8fafc; padding: 2rem; color: #1e293b; }
        .box { max-width: 600px; margin: 0 auto; background: #fff; padding: 2rem; border-radius: 12px; box-shadow: 0 4px 12px rgba(0,0,0,0.08); }
        .success { background: #ecfdf5; color: #065f46; padding: 1rem; border-radius: 8px; margin: 1rem 0; border: 1px solid #a7f3d0; }
        .error { background: #fef2f2; color: #991b1b; padding: 1rem; border-radius: 8px; margin: 1rem 0; border: 1px solid #fecaca; }
        .btn { display: inline-block; background: #2563eb; color: #fff; padding: 0.75rem 1.5rem; text-decoration: none; border-radius: 8px; font-weight: bold; margin-top: 1rem; }
    </style>
</head>
<body>
<div class="box">
    <h2>نصب و راه‌اندازی پایگاه داده حسابدار</h2>
    <p>در حال بررسی اتصال به پایگاه داده و اجرای جداول استاندارد...</p>

    <?php
    try {
        $db = Database::getConnection();

        $schemaFile = __DIR__ . '/schema.sql';
        if (!file_exists($schemaFile)) {
            throw new Exception("فایل schema.sql در مسیر database/ یافت نشد.");
        }

        $sql = file_get_contents($schemaFile);

        // Execute queries
        $db->exec($sql);

        // Ensure default administrator password is validly hashed
        $defaultPassword = 'AdminPassword123!';
        $defaultHash = password_hash($defaultPassword, PASSWORD_DEFAULT);

        $stmt = $db->prepare("UPDATE users SET password_hash = :hash WHERE id = 1");
        $stmt->execute(['hash' => $defaultHash]);

        echo '<div class="success">';
        echo '<strong>نصب پایگاه داده با موفقیت کامل انجام شد!</strong><br><br>';
        echo '✅ تمام جداول (users, accounts, categories, transactions, settings) با موفقیت ساخته شدند.<br>';
        echo '✅ دسته‌بندی‌های اولیه و حساب‌های پیش‌فرض ایجاد شدند.<br>';
        echo '✅ کاربر مدیر سیستم با موفقیت ثبت شد:<br>';
        echo '<ul style="margin: 0.5rem 0; padding-right: 1.5rem;">';
        echo '<li><strong>ایمیل:</strong> admin@hesabdar.local</li>';
        echo '<li><strong>رمز عبور:</strong> AdminPassword123!</li>';
        echo '</ul>';
        echo '</div>';

        echo '<p style="color: #b45309; font-size: 0.85rem;">نکته امنیتی: برای حفظ امنیت، پس از اولین ورود، کلمه عبور را از منوی تنظیمات تغییر داده و دسترسی به این فایل را محدود کنید.</p>';
        echo '<a href="../public/index.php?r=login" class="btn">ورود به پنل کاربری ←</a>';

    } catch (Exception $e) {
        echo '<div class="error">';
        echo '<strong>خطا در راه‌اندازی پایگاه داده:</strong><br>';
        echo htmlspecialchars($e->getMessage());
        echo '<br><br><small>لطفاً نام دیتابیس، نام کاربری و رمز عبور را در فایل <code>app/config/config.php</code> بررسی کنید.</small>';
        echo '</div>';
    }
    ?>
</div>
</body>
</html>
