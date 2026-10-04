<?php
/**
 * Hesabdar - Main Responsive Layout
 * Mobile-First, RTL, Iranian Persian Typography
 */

$currentRoute = $_GET['r'] ?? 'dashboard';
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title><?= Format::e($pageTitle ?? 'مدیریت مالی شخصی') ?> | حسابدار</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <meta name="theme-color" content="#2563eb">
</head>
<body>

    <!-- Top Application Bar -->
    <header class="app-header">
        <a href="index.php?r=dashboard" class="brand">
            <div class="brand-icon">﷼</div>
            <span>حسابدار</span>
        </a>

        <!-- Desktop Navigation Links -->
        <nav class="desktop-nav">
            <a href="index.php?r=dashboard" class="<?= str_starts_with($currentRoute, 'dashboard') ? 'active' : '' ?>">داشبورد</a>
            <a href="index.php?r=transactions" class="<?= str_starts_with($currentRoute, 'transactions') ? 'active' : '' ?>">تراکنش‌ها</a>
            <a href="index.php?r=accounts" class="<?= str_starts_with($currentRoute, 'accounts') ? 'active' : '' ?>">حساب‌ها</a>
            <a href="index.php?r=categories" class="<?= str_starts_with($currentRoute, 'categories') ? 'active' : '' ?>">دسته‌بندی‌ها</a>
            <a href="index.php?r=reports" class="<?= str_starts_with($currentRoute, 'reports') ? 'active' : '' ?>">گزارش‌ها</a>
            <a href="index.php?r=settings" class="<?= str_starts_with($currentRoute, 'settings') ? 'active' : '' ?>">تنظیمات</a>
        </nav>

        <div class="header-user">
            <?php if ($user): ?>
                <span class="user-badge"><?= Format::e($user['name']) ?></span>
                <a href="index.php?r=logout" class="btn-icon-link" title="خروج از حساب" onclick="return confirm('آیا مایل به خروج از حساب کاربری هستید؟');">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path>
                        <polyline points="16 17 21 12 16 7"></polyline>
                        <line x1="21" y1="12" x2="9" y2="12"></line>
                    </svg>
                </a>
            <?php endif; ?>
        </div>
    </header>

    <!-- Main Container -->
    <main class="container">
        <!-- Flash Alert Messages -->
        <?php if (!empty($flashes)): ?>
            <div class="flash-container">
                <?php foreach ($flashes as $type => $message): ?>
                    <div class="alert alert-<?= $type === 'error' ? 'error' : ($type === 'warning' ? 'warning' : 'success') ?>">
                        <span><?= $message ?></span>
                        <button type="button" class="btn-icon-link" style="width:24px;height:24px;" onclick="this.parentElement.remove();">&times;</button>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <!-- Page View Content -->
        <?= $content ?>
    </main>

    <!-- Mobile-First Bottom Navigation Bar -->
    <nav class="bottom-nav">
        <a href="index.php?r=dashboard" class="nav-link <?= str_starts_with($currentRoute, 'dashboard') ? 'active' : '' ?>">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="m3 9 9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path>
                <polyline points="9 22 9 12 15 12 15 22"></polyline>
            </svg>
            <span>خانه</span>
        </a>

        <a href="index.php?r=transactions" class="nav-link <?= str_starts_with($currentRoute, 'transactions') ? 'active' : '' ?>">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <line x1="12" y1="1" x2="12" y2="23"></line>
                <path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"></path>
            </svg>
            <span>تراکنش‌ها</span>
        </a>

        <!-- Floating Action Button for Instant Quick Transaction -->
        <button type="button" class="nav-fab-btn" onclick="openQuickModal('expense');" title="ثبت سریع تراکنش">
            <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                <line x1="12" y1="5" x2="12" y2="19"></line>
                <line x1="5" y1="12" x2="19" y2="12"></line>
            </svg>
        </button>

        <a href="index.php?r=accounts" class="nav-link <?= str_starts_with($currentRoute, 'accounts') ? 'active' : '' ?>">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <rect x="2" y="5" width="20" height="14" rx="2"></rect>
                <line x1="2" y1="10" x2="22" y2="10"></line>
            </svg>
            <span>حساب‌ها</span>
        </a>

        <a href="index.php?r=reports" class="nav-link <?= str_starts_with($currentRoute, 'reports') ? 'active' : '' ?>">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M3 3v18h18"></path>
                <path d="m19 9-5 5-4-4-3 3"></path>
            </svg>
            <span>گزارش‌ها</span>
        </a>
    </nav>

    <!-- Quick Action Bottom Sheet Modal -->
    <?php require __DIR__ . '/quick_modal.php'; ?>

    <script src="assets/js/app.js"></script>
</body>
</html>
