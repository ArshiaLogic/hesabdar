<?php
/**
 * Hesabdar - Settings & Data Export View
 */
?>

<div class="section-header">
    <h1 class="section-title">تنظیمات و پشتیبان‌گیری</h1>
</div>

<div class="grid-2">
    <!-- User Profile & Password -->
    <div>
        <!-- Profile Form -->
        <div class="card">
            <h2 style="font-size: 1.05rem; font-weight: 800; margin-bottom: 1rem;">اطلاعات کاربری</h2>
            <form action="index.php?r=settings/updateProfile" method="POST">
                <?= Csrf::field() ?>

                <div class="form-group">
                    <label class="form-label">نام و نام خانوادگی</label>
                    <input type="text" name="name" class="form-control" value="<?= Format::e($user['name']) ?>" required>
                </div>

                <div class="form-group">
                    <label class="form-label">آدرس ایمیل</label>
                    <input type="email" name="email" class="form-control" value="<?= Format::e($user['email']) ?>" required dir="ltr" style="text-align: left;">
                </div>

                <button type="submit" class="btn btn-primary" style="padding: 0.65rem 1.25rem;">
                    ذخیره مشخصات
                </button>
            </form>
        </div>

        <!-- Change Password Form -->
        <div class="card">
            <h2 style="font-size: 1.05rem; font-weight: 800; margin-bottom: 1rem;">تغییر کلمه عبور</h2>
            <form action="index.php?r=settings/updatePassword" method="POST">
                <?= Csrf::field() ?>

                <div class="form-group">
                    <label class="form-label">کلمه عبور فعلی</label>
                    <input type="password" name="current_password" class="form-control" placeholder="••••••••" required dir="ltr" style="text-align: left;">
                </div>

                <div class="form-group">
                    <label class="form-label">کلمه عبور جدید (حداقل ۸ کاراکتر)</label>
                    <input type="password" name="new_password" class="form-control" placeholder="••••••••" required minlength="8" dir="ltr" style="text-align: left;">
                </div>

                <div class="form-group">
                    <label class="form-label">تکرار کلمه عبور جدید</label>
                    <input type="password" name="confirm_password" class="form-control" placeholder="••••••••" required minlength="8" dir="ltr" style="text-align: left;">
                </div>

                <button type="submit" class="btn btn-primary" style="padding: 0.65rem 1.25rem;">
                    تغییر کلمه عبور
                </button>
            </form>
        </div>
    </div>

    <!-- Data Management & Backups -->
    <div>
        <div class="card">
            <h2 style="font-size: 1.05rem; font-weight: 800; margin-bottom: 1rem;">خروجی گرفتن و پشتیبان‌گیری</h2>
            <p style="font-size: 0.85rem; color: var(--text-muted); line-height: 1.6; margin-bottom: 1.25rem;">
                شما می‌توانید در هر لحظه از تمامی تراکنش‌ها، دسته‌بندی‌ها و حساب‌های خود نسخه‌ی پشتیبان یا خروجی اکسل تهیه کنید.
            </p>

            <div style="display: flex; flex-direction: column; gap: 0.75rem;">
                <!-- Export CSV -->
                <div style="display: flex; justify-content: space-between; align-items: center; padding: 0.85rem; background: var(--bg-input); border-radius: var(--radius-md);">
                    <div>
                        <strong style="font-size: 0.92rem; display: block;">خروجی اکسل (CSV)</strong>
                        <span style="font-size: 0.76rem; color: var(--text-muted);">سازگار با اکسل و حروف فارسی (UTF-8 BOM)</span>
                    </div>
                    <a href="index.php?r=settings/exportCsv" class="btn btn-secondary" style="padding: 0.45rem 0.85rem; font-size: 0.82rem;">
                        دانلود CSV
                    </a>
                </div>

                <!-- Export JSON -->
                <div style="display: flex; justify-content: space-between; align-items: center; padding: 0.85rem; background: var(--bg-input); border-radius: var(--radius-md);">
                    <div>
                        <strong style="font-size: 0.92rem; display: block;">خروجی کامل داده‌ها (JSON)</strong>
                        <span style="font-size: 0.76rem; color: var(--text-muted);">پشتیبان‌گیری ساختاریافته از تمام جداول</span>
                    </div>
                    <a href="index.php?r=settings/exportJson" class="btn btn-secondary" style="padding: 0.45rem 0.85rem; font-size: 0.82rem;">
                        دانلود JSON
                    </a>
                </div>

                <!-- Export SQL -->
                <div style="display: flex; justify-content: space-between; align-items: center; padding: 0.85rem; background: var(--bg-input); border-radius: var(--radius-md);">
                    <div>
                        <strong style="font-size: 0.92rem; display: block;">پشتیبان پایگاه داده (SQL Dump)</strong>
                        <span style="font-size: 0.76rem; color: var(--text-muted);">فایل SQL برای بازیابی در phpMyAdmin</span>
                    </div>
                    <a href="index.php?r=settings/exportSql" class="btn btn-secondary" style="padding: 0.45rem 0.85rem; font-size: 0.82rem;">
                        دانلود SQL
                    </a>
                </div>
            </div>
        </div>

        <!-- Quick Management Links -->
        <div class="card">
            <h2 style="font-size: 1.05rem; font-weight: 800; margin-bottom: 0.75rem;">دسترسی سریع</h2>
            <div style="display: flex; gap: 0.75rem; flex-wrap: wrap;">
                <a href="index.php?r=categories" class="btn btn-outline" style="font-size: 0.85rem;">
                    مدیریت دسته‌بندی‌ها
                </a>
                <a href="index.php?r=accounts" class="btn btn-outline" style="font-size: 0.85rem;">
                    مدیریت حساب‌ها و کارت‌ها
                </a>
            </div>
        </div>
    </div>
</div>
