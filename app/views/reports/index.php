<?php
/**
 * Hesabdar - Financial Reports & Cash Flow Analytics View
 */
?>

<div class="section-header">
    <h1 class="section-title">گزارش‌های مالی</h1>

    <!-- Month Selector Form -->
    <form action="index.php" method="GET" style="display: flex; gap: 0.5rem; align-items: center;">
        <input type="hidden" name="r" value="reports">
        
        <select name="month" class="form-control" style="padding: 0.4rem 0.65rem; font-size: 0.85rem;" onchange="this.form.submit();">
            <?php for ($m = 1; $m <= 12; $m++): ?>
                <option value="<?= $m ?>" <?= $m === $selectedMonth ? 'selected' : '' ?>>
                    <?= Jalali::getJalaliMonthRange($selectedYear, $m)['month_name'] ?>
                </option>
            <?php endfor; ?>
        </select>

        <select name="year" class="form-control" style="padding: 0.4rem 0.65rem; font-size: 0.85rem;" onchange="this.form.submit();">
            <?php for ($y = $selectedYear - 2; $y <= $selectedYear + 1; $y++): ?>
                <option value="<?= $y ?>" <?= $y === $selectedYear ? 'selected' : '' ?>>
                    <?= Jalali::persianDigits((string)$y) ?>
                </option>
            <?php endfor; ?>
        </select>
    </form>
</div>

<!-- Cash Flow Summary Cards -->
<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 0.85rem; margin-bottom: 1.5rem;">
    <!-- Income Card -->
    <div class="card" style="border-top: 4px solid var(--success); margin-bottom: 0;">
        <div style="font-size: 0.82rem; color: var(--text-muted); margin-bottom: 0.35rem;">مجموع درآمد ماه</div>
        <div style="font-size: 1.35rem; font-weight: 800; color: var(--success); direction: ltr; text-align: right;">
            + <?= Format::rial($summary['total_income']) ?>
        </div>
        <div style="font-size: 0.76rem; color: var(--text-subtle); margin-top: 0.2rem;">
            <?= Format::toman($summary['total_income']) ?>
        </div>
    </div>

    <!-- Expense Card -->
    <div class="card" style="border-top: 4px solid var(--danger); margin-bottom: 0;">
        <div style="font-size: 0.82rem; color: var(--text-muted); margin-bottom: 0.35rem;">مجموع هزینه ماه</div>
        <div style="font-size: 1.35rem; font-weight: 800; color: var(--danger); direction: ltr; text-align: right;">
            - <?= Format::rial($summary['total_expense']) ?>
        </div>
        <div style="font-size: 0.76rem; color: var(--text-subtle); margin-top: 0.2rem;">
            <?= Format::toman($summary['total_expense']) ?>
        </div>
    </div>

    <!-- Net Flow Card -->
    <div class="card" style="border-top: 4px solid <?= $summary['net_flow'] >= 0 ? 'var(--success)' : 'var(--danger)' ?>; margin-bottom: 0;">
        <div style="font-size: 0.82rem; color: var(--text-muted); margin-bottom: 0.35rem;">خالص جریان مالی (پس‌انداز/کسری)</div>
        <div style="font-size: 1.35rem; font-weight: 800; color: <?= $summary['net_flow'] >= 0 ? 'var(--success)' : 'var(--danger)' ?>; direction: ltr; text-align: right;">
            <?= ($summary['net_flow'] > 0 ? '+ ' : '') . Format::rial($summary['net_flow']) ?>
        </div>
        <div style="font-size: 0.76rem; color: var(--text-subtle); margin-top: 0.2rem;">
            <?= Format::toman($summary['net_flow']) ?>
        </div>
    </div>
</div>

<div class="grid-2">
    <!-- Expense by Category -->
    <div class="card">
        <h2 style="font-size: 1.05rem; font-weight: 800; margin-bottom: 1.15rem; display: flex; align-items: center; justify-content: space-between;">
            <span>سهم هزینه‌ها به تفکیک دسته‌بندی</span>
            <span style="font-size: 0.78rem; font-weight: 600; color: var(--text-muted);"><?= Format::e($monthRange['title']) ?></span>
        </h2>

        <?php if (empty($expensesByCategory)): ?>
            <div class="empty-state" style="padding: 1.5rem 0;">
                <p>در این ماه هیچ هزینه‌ای ثبت نشده است.</p>
            </div>
        <?php else: ?>
            <div style="display: flex; flex-direction: column; gap: 1rem;">
                <?php foreach ($expensesByCategory as $cat): ?>
                    <div>
                        <div style="display: flex; justify-content: space-between; align-items: center; font-size: 0.88rem;">
                            <span style="font-weight: 700; display: flex; align-items: center; gap: 0.4rem;">
                                <span style="display: inline-block; width: 10px; height: 10px; border-radius: 50%; background: <?= Format::e($cat['category_color']) ?>;"></span>
                                <?= Format::e($cat['category_name']) ?>
                            </span>
                            <div style="text-align: left;">
                                <span style="font-weight: 800;"><?= Format::rial($cat['total_amount']) ?></span>
                                <span style="font-size: 0.75rem; color: var(--text-muted); margin-right: 0.35rem;">(<?= Jalali::persianDigits((string)$cat['percentage']) ?>٪)</span>
                            </div>
                        </div>

                        <!-- Progress Bar -->
                        <div class="progress-bar-container">
                            <div class="progress-bar-fill" style="width: <?= $cat['percentage'] ?>%; background: <?= Format::e($cat['category_color']) ?>;"></div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <!-- Expense by Account -->
    <div class="card">
        <h2 style="font-size: 1.05rem; font-weight: 800; margin-bottom: 1.15rem; display: flex; align-items: center; justify-content: space-between;">
            <span>مبادی پرداخت (هزینه بر اساس حساب)</span>
            <span style="font-size: 0.78rem; font-weight: 600; color: var(--text-muted);"><?= Format::e($monthRange['title']) ?></span>
        </h2>

        <?php if (empty($expensesByAccount)): ?>
            <div class="empty-state" style="padding: 1.5rem 0;">
                <p>در این ماه تراکنش خروجی از حساب‌ها وجود ندارد.</p>
            </div>
        <?php else: ?>
            <div style="display: flex; flex-direction: column; gap: 1rem;">
                <?php foreach ($expensesByAccount as $acc): ?>
                    <div>
                        <div style="display: flex; justify-content: space-between; align-items: center; font-size: 0.88rem;">
                            <span style="font-weight: 700; display: flex; align-items: center; gap: 0.4rem;">
                                <span style="display: inline-block; width: 10px; height: 10px; border-radius: 50%; background: <?= Format::e($acc['account_color']) ?>;"></span>
                                <?= Format::e($acc['account_name']) ?>
                            </span>
                            <div style="text-align: left;">
                                <span style="font-weight: 800;"><?= Format::rial($acc['total_amount']) ?></span>
                                <span style="font-size: 0.75rem; color: var(--text-muted); margin-right: 0.35rem;">(<?= Jalali::persianDigits((string)$acc['percentage']) ?>٪)</span>
                            </div>
                        </div>

                        <div class="progress-bar-container">
                            <div class="progress-bar-fill" style="width: <?= $acc['percentage'] ?>%; background: <?= Format::e($acc['account_color']) ?>;"></div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>
