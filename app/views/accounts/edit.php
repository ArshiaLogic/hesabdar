<?php
/**
 * Hesabdar - Edit Account View
 */
?>

<div class="card" style="max-width: 540px; margin: 0 auto;">
    <div class="section-header">
        <h1 class="section-title">ویرایش حساب</h1>
        <a href="index.php?r=accounts" class="section-link">بازگشت ←</a>
    </div>

    <form action="index.php?r=accounts/update" method="POST">
        <?= Csrf::field() ?>
        <input type="hidden" name="id" value="<?= $account['id'] ?>">

        <div class="form-group">
            <label class="form-label">نام حساب یا کارت *</label>
            <input type="text" name="name" class="form-control" value="<?= Format::e($account['name']) ?>" required>
        </div>

        <div class="form-group">
            <label class="form-label">نوع حساب</label>
            <select name="type" class="form-control">
                <option value="bank_card" <?= $account['type'] === 'bank_card' ? 'selected' : '' ?>>کارت بانکی</option>
                <option value="cash" <?= $account['type'] === 'cash' ? 'selected' : '' ?>>پول نقد / کیف پول</option>
                <option value="savings" <?= $account['type'] === 'savings' ? 'selected' : '' ?>>حساب پس‌انداز / سپرده</option>
                <option value="other" <?= $account['type'] === 'other' ? 'selected' : '' ?>>سایر حساب‌ها</option>
            </select>
        </div>

        <div class="form-group">
            <label class="form-label">شماره کارت یا ۴ رقم آخر</label>
            <input type="text" name="card_number" class="form-control" value="<?= Format::e($account['card_number'] ?? '') ?>" dir="ltr" style="text-align: left;">
        </div>

        <div class="form-group">
            <label class="form-label">موجودی اولیه (ریال)</label>
            <div class="amount-wrapper">
                <input 
                    type="text" 
                    inputmode="numeric" 
                    name="opening_balance" 
                    class="form-control js-amount-input" 
                    value="<?= number_format($account['opening_balance'], 0, '.', ',') ?>"
                    autocomplete="off"
                    style="font-size: 1.15rem; font-weight: 700;"
                >
            </div>
            <div class="amount-toman-preview js-toman-preview">معادل <?= Format::toman($account['opening_balance']) ?></div>
        </div>

        <div class="form-group">
            <label class="form-label">وضعیت حساب</label>
            <select name="status" class="form-control">
                <option value="active" <?= $account['status'] === 'active' ? 'selected' : '' ?>>فعال (نمایش در داشبورد)</option>
                <option value="archived" <?= $account['status'] === 'archived' ? 'selected' : '' ?>>بایگانی‌شده</option>
            </select>
        </div>

        <div class="form-group">
            <label class="form-label">رنگ نشانگر حساب</label>
            <div style="display: flex; gap: 0.65rem; align-items: center;">
                <input type="color" name="color" value="<?= Format::e($account['color'] ?? '#2563eb') ?>" style="width: 44px; height: 38px; border: 1px solid var(--border); border-radius: var(--radius-sm); cursor: pointer;">
                <span style="font-size: 0.82rem; color: var(--text-muted);">رنگ تمایز دهنده حساب</span>
            </div>
        </div>

        <button type="submit" class="btn btn-primary btn-block" style="padding: 0.85rem; font-size: 1rem; margin-top: 1.5rem;">
            ذخیره تغییرات حساب
        </button>
    </form>
</div>
