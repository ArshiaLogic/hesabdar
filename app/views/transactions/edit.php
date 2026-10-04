<?php
/**
 * Hesabdar - Edit Transaction View
 */
?>

<div class="card" style="max-width: 600px; margin: 0 auto;">
    <div class="section-header">
        <h1 class="section-title">ویرایش تراکنش</h1>
        <a href="index.php?r=transactions" class="section-link">بازگشت ←</a>
    </div>

    <form action="index.php?r=transactions/update" method="POST">
        <?= Csrf::field() ?>
        <input type="hidden" name="id" value="<?= $transaction['id'] ?>">
        <input type="hidden" name="type" value="<?= Format::e($transaction['type']) ?>">

        <!-- Type Selector -->
        <div class="type-toggle js-type-toggle">
            <button type="button" class="type-toggle-btn <?= $transaction['type'] === 'expense' ? 'active expense' : '' ?>" data-type="expense">هزینه</button>
            <button type="button" class="type-toggle-btn <?= $transaction['type'] === 'income' ? 'active income' : '' ?>" data-type="income">درآمد</button>
            <button type="button" class="type-toggle-btn <?= $transaction['type'] === 'transfer' ? 'active transfer' : '' ?>" data-type="transfer">انتقال</button>
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
                    value="<?= number_format($transaction['amount_rial'], 0, '.', ',') ?>" 
                    required 
                    autocomplete="off"
                    style="font-size: 1.25rem; font-weight: 800;"
                >
            </div>
            <div class="amount-toman-preview js-toman-preview">معادل <?= Format::toman($transaction['amount_rial']) ?></div>
        </div>

        <!-- Source Account -->
        <div class="form-group">
            <label class="form-label">حساب مبدأ / پرداختی *</label>
            <select name="account_id" class="form-control" required>
                <?php foreach ($accounts as $acc): ?>
                    <option value="<?= $acc['id'] ?>" <?= ((int)$transaction['account_id'] === (int)$acc['id']) ? 'selected' : '' ?>>
                        <?= Format::e($acc['name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <!-- Destination Account (Only for transfers) -->
        <div class="form-group js-to-account-group" style="display: <?= $transaction['type'] === 'transfer' ? 'block' : 'none' ?>;">
            <label class="form-label">حساب مقصد انتقال *</label>
            <select name="to_account_id" class="form-control">
                <option value="">انتخاب حساب مقصد...</option>
                <?php foreach ($accounts as $acc): ?>
                    <option value="<?= $acc['id'] ?>" <?= ((int)($transaction['to_account_id'] ?? 0) === (int)$acc['id']) ? 'selected' : '' ?>>
                        <?= Format::e($acc['name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <!-- Category (Only for income & expense) -->
        <div class="form-group js-category-group" style="display: <?= $transaction['type'] === 'transfer' ? 'none' : 'block' ?>;">
            <label class="form-label">دسته‌بندی</label>
            <select name="category_id" class="form-control js-category-select">
                <option value="">انتخاب دسته‌بندی...</option>
                <?php foreach ($expenseCategories as $cat): ?>
                    <option value="<?= $cat['id'] ?>" data-type="expense" <?= ((int)($transaction['category_id'] ?? 0) === (int)$cat['id']) ? 'selected' : '' ?>>
                        <?= Format::e($cat['name']) ?>
                    </option>
                <?php endforeach; ?>
                <?php foreach ($incomeCategories as $cat): ?>
                    <option value="<?= $cat['id'] ?>" data-type="income" <?= ((int)($transaction['category_id'] ?? 0) === (int)$cat['id']) ? 'selected' : '' ?>>
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
                id="editTxDate" 
                class="form-control" 
                value="<?= $jalaliDate ?>" 
                required
            >
            <div class="date-presets">
                <button type="button" class="date-preset-btn js-date-preset" data-offset="0" data-target="#editTxDate">امروز</button>
                <button type="button" class="date-preset-btn js-date-preset" data-offset="-1" data-target="#editTxDate">دیروز</button>
            </div>
        </div>

        <!-- Description -->
        <div class="form-group">
            <label class="form-label">توضیحات (اختیاری)</label>
            <input type="text" name="description" class="form-control" value="<?= Format::e($transaction['description'] ?? '') ?>">
        </div>

        <button type="submit" class="btn btn-primary btn-block" style="padding: 0.9rem; font-size: 1.05rem;">
            ذخیره تغییرات تراکنش
        </button>
    </form>
</div>
