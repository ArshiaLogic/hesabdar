<?php
/**
 * Hesabdar - Authentication Minimal Layout
 */
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= Format::e($pageTitle ?? 'ورود به سیستم') ?> | حسابدار</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        body {
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            background: linear-gradient(135deg, #f1f5f9 0%, #e2e8f0 100%);
            padding: 1.5rem 1rem;
        }
        .auth-card {
            background: #ffffff;
            width: 100%;
            max-width: 420px;
            border-radius: var(--radius-xl);
            padding: 2.25rem 2rem;
            box-shadow: var(--shadow-xl);
            border: 1px solid var(--border);
        }
        .auth-brand {
            display: flex;
            flex-direction: column;
            align-items: center;
            text-align: center;
            margin-bottom: 2rem;
        }
        .auth-brand-icon {
            width: 58px;
            height: 58px;
            border-radius: var(--radius-lg);
            background: linear-gradient(135deg, var(--primary), #3b82f6);
            color: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.8rem;
            font-weight: 800;
            box-shadow: 0 8px 20px rgba(37, 99, 235, 0.3);
            margin-bottom: 0.75rem;
        }
        .auth-brand-title {
            font-size: 1.45rem;
            font-weight: 900;
            color: var(--text-main);
        }
        .auth-brand-subtitle {
            font-size: 0.88rem;
            color: var(--text-muted);
            margin-top: 0.2rem;
        }
    </style>
</head>
<body>

    <div class="auth-card">
        <div class="auth-brand">
            <div class="auth-brand-icon">﷼</div>
            <h1 class="auth-brand-title">حسابدار شخصی</h1>
            <p class="auth-brand-subtitle">مدیریت هوشمند و امن امور مالی شما</p>
        </div>

        <?php if (!empty($flashes)): ?>
            <div class="flash-container">
                <?php foreach ($flashes as $type => $message): ?>
                    <div class="alert alert-<?= $type === 'error' ? 'error' : ($type === 'warning' ? 'warning' : 'success') ?>">
                        <span><?= $message ?></span>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <?= $content ?>
    </div>

</body>
</html>
