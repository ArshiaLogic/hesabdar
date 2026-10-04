<?php
/**
 * Hesabdar - Quick Action Bottom Sheet Modal
 * Enables logging transactions in under 3 seconds on mobile or desktop
 */

// If modal is rendered outside DashboardController, fetch accounts and categories if needed
if (!isset($accounts)) {
    $accModel = new Account();
    $accounts = $accModel->getAllForUser(Session::userId(), false);
}
if (!isset($expenseCategories) || !isset($incomeCategories)) {
    $catModel = new Category();
    $expenseCategories = $catModel->getAllForUser(Session::userId(), 'expense');
    $incomeCategories = $catModel->getAllForUser(Session::userId(), 'income');
}
$todayJalali = Jalali::toJalali(date('Y-m-d'), 'Y/m/d', false);
?>

<div id="quickModal" class="modal-overlay">
    <div class="modal-content">
        <div class="modal-header">
            <h3 class="modal-title">ثبت سریع تراکنش</h3>
            <button type="button" class="modal-close js-modal-close">&times;</button>
        </div>

        <form action="index.php?r=transactions/store" method="POST" id="quickTransactionForm">
            <?= Csrf::field() ?>
            <input type="hidden" name="type" value="expense">

            <!-- Type Selector Toggle -->
            <div class="type-toggle js-type-toggle">
                <button type="button" class="type-toggle-btn active expense" data-type="expense">هزینه</button>
                <button type="button" class="type-toggle-btn" data-type="income">درآمد</button>
                <button type="button" class="type-toggle-btn" data-type="transfer">انتقال</button>
            </div>

            <!-- Amount Input (Rial) -->
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
                        style="font-size: 1.25rem; font-weight: 800; letter-spacing: 0.5px;"
                    >
                </div>
                <div class="amount-toman-preview js-toman-preview">معادل ۰ تومان</div>
            </div>

            <!-- Source Account Selector -->
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

            <!-- Destination Account (Only for Transfers) -->
            <div class="form-group js-to-account-group" style="display: none;">
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

            <!-- Category Selector (Only for Income and Expense) -->
            <div class="form-group js-category-group">
                <label class="form-label">دسته‌بندی</label>
                <select name="category_id" class="form-control js-category-select">
                    <option value="">انتخاب دسته‌بندی...</option>
                    <!-- Expense Categories -->
                    <?php foreach ($expenseCategories as $cat): ?>
                        <option value="<?= $cat['id'] ?>" data-type="expense">
                            <?= Format::e($cat['name']) ?>
                        </option>
                    <?php endforeach; ?>
                    <!-- Income Categories -->
                    <?php foreach ($incomeCategories as $cat): ?>
                        <option value="<?= $cat['id'] ?>" data-type="income" style="display: none;" disabled>
                            <?= Format::e($cat['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Date Selector with Quick Buttons -->
            <div class="form-group">
                <label class="form-label">تاریخ تراکنش (شمسی)</label>
                <input 
                    type="text" 
                    name="transaction_date" 
                    id="quickModalDateInput" 
                    class="form-control" 
                    value="<?= $todayJalali ?>" 
                    placeholder="۱۴۰۳/۰۷/۱۲" 
                    required
                >
                <div class="date-presets">
                    <button type="button" class="date-preset-btn js-date-preset" data-offset="0" data-target="#quickModalDateInput">امروز</button>
                    <button type="button" class="date-preset-btn js-date-preset" data-offset="-1" data-target="#quickModalDateInput">دیروز</button>
                </div>
            </div>

            <!-- Description / Note -->
            <div class="form-group">
                <label class="form-label">توضیحات (اختیاری)</label>
                <input type="text" name="description" class="form-control" placeholder="بابت خرید، واریزی، یا جزییات...">
            </div>

            <button type="submit" class="btn btn-primary btn-block" style="padding: 0.9rem; font-size: 1.05rem;">
                ثبت و ذخیره تراکنش
            </button>
        </form>
    </div>
</div>
