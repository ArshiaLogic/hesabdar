<?php
/**
 * Hesabdar - Login View
 */
?>

<form action="index.php?r=login" method="POST">
    <?= Csrf::field() ?>

    <div class="form-group">
        <label class="form-label" for="email">آدرس ایمیل</label>
        <input 
            type="email" 
            id="email" 
            name="email" 
            class="form-control" 
            placeholder="name@example.com" 
            value="<?= Format::e($email ?? '') ?>" 
            required 
            autocomplete="email"
            autofocus
            dir="ltr"
            style="text-align: left;"
        >
    </div>

    <div class="form-group">
        <label class="form-label" for="password">کلمه عبور</label>
        <input 
            type="password" 
            id="password" 
            name="password" 
            class="form-control" 
            placeholder="••••••••" 
            required 
            autocomplete="current-password"
            dir="ltr"
            style="text-align: left;"
        >
    </div>

    <button type="submit" class="btn btn-primary btn-block" style="padding: 0.85rem; font-size: 1.05rem; margin-top: 1.5rem;">
        ورود به حساب کاربری
    </button>

    <div style="margin-top: 1.75rem; padding: 0.85rem; background: var(--bg-input); border-radius: var(--radius-md); font-size: 0.78rem; color: var(--text-muted); line-height: 1.6;">
        <strong style="color: var(--text-main); display: block; margin-bottom: 0.2rem;">اطلاعات ورود پیش‌فرض اولیه:</strong>
        <div>ایمیل: <span style="font-family: monospace; color: var(--primary);" dir="ltr">admin@hesabdar.local</span></div>
        <div>رمز عبور: <span style="font-family: monospace; color: var(--primary);" dir="ltr">AdminPassword123!</span></div>
        <div style="margin-top: 0.35rem; color: #b45309;">پس از ورود می‌توانید در منوی تنظیمات رمز را تغییر دهید.</div>
    </div>
</form>
