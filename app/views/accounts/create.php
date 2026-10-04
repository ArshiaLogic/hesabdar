<?php
/**
 * Hesabdar - Create Account View
 */
?>

<div class="card" style="max-width: 540px; margin: 0 auto;">
    <div class="section-header">
        <h1 class="section-title">افزودن حساب یا کارت جدید</h1>
        <a href="index.php?r=accounts" class="section-link">بازگشت ←</a>
    </div>

    <form action="index.php?r=accounts/store" method="POST">
        <?= Csrf::field() ?>

        <div class="form-group">
            <label class="form-label">نام حساب یا کارت *</label>
            <input type="text" name="name" class="form-control" placeholder="مثال: کارت بانک ملت، کیف پول، پس‌انداز" required autofocus>
        </div>

        <div class="form-group">
            <label class="form-label">نوع حساب</label>
            <select name="type" class="form-control">
                <option value="bank_card">کارت بانکی</option>
                <option value="cash">پول نقد / کیف پول</option>
                <option value="savings">حساب پس‌انداز / سپرده</option>
                <option value="other">سایر حساب‌ها</option>
            </select>
        </div>

        <div class="form-group">
            <label class="form-label">شماره کارت یا ۴ رقم آخر (اختیاری)</label>
            <input type="text" name="card_number" class="form-control" placeholder="مثال: 6104 یا شماره کامل ۱۶ رقمی" dir="ltr" style="text-align: left;">
        </div>

        <div class="form-group">
            <label class="form-label">موجودی اولیه (ریال)</label>
            <div class="amount-wrapper">
                <input 
                    type="text" 
                    inputmode="numeric" 
                    name="opening_balance" 
                    class="form-control js-amount-input" 
                    placeholder="0" 
                    value="0"
                    autocomplete="off"
                    style="font-size: 1.15rem; font-weight: 700;"
                >
            </div>
            <div class="amount-toman-preview js-toman-preview">معادل ۰ تومان</div>
        </div>

        <div class="form-group">
            <label class="form-label">رنگ نشانگر حساب</label>
            <div style="display: flex; gap: 0.65rem; align-items: center;">
                <input type="color" name="color" value="#2563eb" style="width: 44px; height: 38px; border: 1px solid var(--border); border-radius: var(--radius-sm); cursor: pointer;">
                <span style="font-size: 0.82rem; color: var(--text-muted);">رنگی برای تمایز این حساب در داشبورد انتخاب کنید</span>
            </div>
        </div>

        <button type="submit" class="btn btn-primary btn-block" style="padding: 0.85rem; font-size: 1rem; margin-top: 1.5rem;">
            ایجاد و ثبت حساب
        </button>
    </form>
</div>
