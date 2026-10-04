<?php
/**
 * Hesabdar - Create Transaction View
 */
?>

<div class="card" style="max-width: 600px; margin: 0 auto;">
    <div class="section-header">
        <h1 class="section-title">ثبت تراکنش جدید</h1>
        <a href="index.php?r=transactions" class="section-link">بازگشت ←</a>
    </div>

    <form action="index.php?r=transactions/store" method="POST">
        <?= Csrf::field() ?>
        <input type="hidden" name="type" value="<?= Format::e($defaultType ?? 'expense') ?>">

        <!-- Type Selector -->
        <div class="type-toggle js-type-toggle">
            <button type="button" class="type-toggle-btn <?= ($defaultType ?? 'expense') === 'expense' ? 'active expense' : '' ?>" data-type="expense">هزینه</button>
            <button type="button" class="type-toggle-btn <?= ($defaultType ?? '') === 'income' ? 'active income' : '' ?>" data-type="income">درآمد</button>
            <button type="button" class="type-toggle-btn <?= ($defaultType ?? '') === 'transfer' ? 'active transfer' : '' ?>" data-type="transfer">انتقال</button>
        </div>

        <!-- Amount (Rial) -->
        <div class="form-group">
            <label class="form-label">مبلغ (ریال) *</label>
            <div class="amount-wrapper">
                <input 
                    type="text" 
                    inputmode="numeric" 
                    name="amount_rial" 
                    class="form-control js-amount-input" 
                    placeholder="مثال: ۱۵,۰۰۰,۰۰۰" 
                    required 
                    autocomplete="off"
                    style="font-size: 1.25rem; font-weight: 800;"
                    autofocus
                >
            </div>
            <div class="amount-toman-preview js-toman-preview">معادل ۰ تومان</div>
        </div>

        <!-- Source Account -->
        <div class="form-group">
            <label class="form-label">حساب مبدأ / پرداختی *</label>
            <select name="account_id" class="form-control" required>
                <?php foreach ($accounts as $acc): ?>
                    <option value="<?= $acc['id'] ?>">
                        <?= Format::e($acc['name']) ?> (موجودی: <?= Format::rial($acc['current_balance']) ?>)
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <!-- Destination Account (Only for transfers) -->
        <div class="form-group js-to-account-group" style="display: <?= ($defaultType ?? '') === 'transfer' ? 'block' : 'none' ?>;">
            <label class="form-label">حساب مقصد انتقال *</label>
            <select name="to_account_id" class="form-control">
                <option value="">انتخاب حساب مقصد...</option>
                <?php foreach ($accounts as $acc): ?>
                    <option value="<?= $acc['id'] ?>">
                        <?= Format::e($acc['name']) ?> (موجودی: <?= Format::rial($acc['current_balance']) ?>)
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <!-- Category (Only for income & expense) -->
        <div class="form-group js-category-group" style="display: <?= ($defaultType ?? '') === 'transfer' ? 'none' : 'block' ?>;">
            <label class="form-label">دسته‌بندی</label>
            <select name="category_id" class="form-control js-category-select">
                <option value="">انتخاب دسته‌بندی...</option>
                <?php foreach ($expenseCategories as $cat): ?>
                    <option value="<?= $cat['id'] ?>" data-type="expense" style="<?= ($defaultType ?? 'expense') === 'income' ? 'display:none;' : '' ?>">
                        <?= Format::e($cat['name']) ?>
                    </option>
                <?php endforeach; ?>
                <?php foreach ($incomeCategories as $cat): ?>
                    <option value="<?= $cat['id'] ?>" data-type="income" style="<?= ($defaultType ?? 'expense') !== 'income' ? 'display:none;' : '' ?>">
                        <?= Format::e($cat['name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <!-- Date (Jalali) -->
        <div class="form-group">
            <label class="form-label">تاریخ تراکنش (شمسی) *</label>
            <input 
                type="text" 
                name="transaction_date" 
                id="createTxDate" 
                class="form-control" 
                value="<?= $todayJalali ?>" 
                required
            >
            <div class="date-presets">
                <button type="button" class="date-preset-btn js-date-preset" data-offset="0" data-target="#createTxDate">امروز</button>
                <button type="button" class="date-preset-btn js-date-preset" data-offset="-1" data-target="#createTxDate">دیروز</button>
            </div>
        </div>

        <!-- Description -->
        <div class="form-group">
            <label class="form-label">توضیحات (اختیاری)</label>
            <input type="text" name="description" class="form-control" placeholder="بابت خرید، واریزی، یا یادداشت...">
        </div>

        <button type="submit" class="btn btn-primary btn-block" style="padding: 0.9rem; font-size: 1.05rem;">
            ثبت و ذخیره تراکنش
        </button>
    </form>
</div>
