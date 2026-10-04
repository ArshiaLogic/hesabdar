<?php
/**
 * Hesabdar - Dashboard View
 */
?>

<!-- Financial Hero Summary Card -->
<section class="hero-card">
    <div class="hero-subtitle">موجودی کل دارایی‌ها (تمامی حساب‌ها)</div>
    <div class="hero-balance">
        <span><?= Format::rial($totalBalance, false) ?></span>
        <span class="unit">ریال</span>
    </div>
    <div class="hero-toman"><?= Format::toman($totalBalance) ?></div>

    <div class="hero-stats-row">
        <!-- Monthly Income -->
        <div class="stat-item">
            <span class="stat-item-label">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="23 6 13.5 15.5 8.5 10.5 1 18"></polyline><polyline points="17 6 23 6 23 12"></polyline></svg>
                درآمد <?= Format::e($currentMonth['month_name']) ?>
            </span>
            <span class="stat-item-value text-income">
                + <?= Format::rial($monthSummary['total_income']) ?>
            </span>
        </div>

        <!-- Monthly Expense -->
        <div class="stat-item">
            <span class="stat-item-label">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="23 18 13.5 8.5 8.5 13.5 1 6"></polyline><polyline points="17 18 23 18 23 12"></polyline></svg>
                هزینه <?= Format::e($currentMonth['month_name']) ?>
            </span>
            <span class="stat-item-value text-expense">
                - <?= Format::rial($monthSummary['total_expense']) ?>
            </span>
        </div>
    </div>

    <!-- Net Cash Flow -->
    <div style="margin-top: 0.85rem; padding-top: 0.75rem; border-top: 1px dashed rgba(255,255,255,0.15); display: flex; justify-content: space-between; align-items: center; font-size: 0.82rem;">
        <span style="color: #cbd5e1;">خالص جریان مالی این ماه:</span>
        <span style="font-weight: 800; font-size: 0.95rem; color: <?= $monthSummary['net_flow'] >= 0 ? '#34d399' : '#f87171' ?>;">
            <?= ($monthSummary['net_flow'] > 0 ? '+ ' : '') . Format::rial($monthSummary['net_flow']) ?>
        </span>
    </div>
</section>

<!-- Quick Action Buttons for One-Hand Mobile Use -->
<section class="quick-actions-bar">
    <button type="button" class="quick-action-btn btn-expense-quick" onclick="openQuickModal('expense');">
        <div class="quick-icon-wrapper">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
        </div>
        <span>ثبت هزینه</span>
    </button>

    <button type="button" class="quick-action-btn btn-income-quick" onclick="openQuickModal('income');">
        <div class="quick-icon-wrapper">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="23 6 13.5 15.5 8.5 10.5 1 18"></polyline><polyline points="17 6 23 6 23 12"></polyline></svg>
        </div>
        <span>ثبت درآمد</span>
    </button>

    <button type="button" class="quick-action-btn btn-transfer-quick" onclick="openQuickModal('transfer');">
        <div class="quick-icon-wrapper">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="17 1 21 5 17 9"></polyline><path d="M3 11V9a4 4 0 0 1 4-4h14"></path><polyline points="7 23 3 19 7 15"></polyline><path d="M21 13v2a4 4 0 0 1-4 4H3"></path></svg>
        </div>
        <span>انتقال وجه</span>
    </button>
</section>

<!-- Accounts Overview Slider -->
<section style="margin-bottom: 1.5rem;">
    <div class="section-header">
        <h2 class="section-title">حساب‌ها و کارت‌ها</h2>
        <a href="index.php?r=accounts" class="section-link">مدیریت حساب‌ها ←</a>
    </div>

    <div class="accounts-scroll">
        <?php foreach ($accounts as $acc): ?>
            <div class="account-card-mini" style="border-top-color: <?= Format::e($acc['color']) ?>;">
                <div class="card-top">
                    <span class="card-name"><?= Format::e($acc['name']) ?></span>
                    <?php if (!empty($acc['card_number'])): ?>
                        <span class="card-num">•••• <?= substr($acc['card_number'], -4) ?></span>
                    <?php endif; ?>
                </div>
                <div>
                    <div class="card-balance"><?= Format::rial($acc['current_balance']) ?></div>
                    <div class="card-type"><?= Format::accountTypeFa($acc['type']) ?></div>
                </div>
            </div>
        <?php endforeach; ?>
        <a href="index.php?r=accounts/create" class="account-card-mini" style="align-items: center; justify-content: center; border-style: dashed; border-color: var(--border); border-top-color: var(--border); min-width: 130px; cursor: pointer;">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="color: var(--text-muted);"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
            <span style="font-size: 0.82rem; font-weight: 700; color: var(--text-muted); margin-top: 0.4rem;">+ افزودن حساب</span>
        </a>
    </div>
</section>

<!-- Recent Transactions List -->
<section>
    <div class="section-header">
        <h2 class="section-title">آخرین تراکنش‌ها</h2>
        <a href="index.php?r=transactions" class="section-link">مشاهده همه ←</a>
    </div>

    <div class="transactions-card">
        <?php if (empty($recentTransactions)): ?>
            <div class="empty-state">
                <div class="empty-state-icon">📋</div>
                <p>هنوز هیچ تراکنشی ثبت نشده است.</p>
                <button type="button" class="btn btn-primary" style="margin-top: 0.85rem;" onclick="openQuickModal('expense');">
                    ثبت اولین تراکنش
                </button>
            </div>
        <?php else: ?>
            <?php foreach ($recentTransactions as $tx): ?>
                <div class="tx-item">
                    <div class="tx-right">
                        <div class="tx-icon <?= $tx['type'] ?>">
                            <?php if ($tx['type'] === 'income'): ?>
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="23 6 13.5 15.5 8.5 10.5 1 18"></polyline><polyline points="17 6 23 6 23 12"></polyline></svg>
                            <?php elseif ($tx['type'] === 'expense'): ?>
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="23 18 13.5 8.5 8.5 13.5 1 6"></polyline><polyline points="17 18 23 18 23 12"></polyline></svg>
                            <?php else: ?>
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="17 1 21 5 17 9"></polyline><path d="M3 11V9a4 4 0 0 1 4-4h14"></path><polyline points="7 23 3 19 7 15"></polyline><path d="M21 13v2a4 4 0 0 1-4 4H3"></path></svg>
                            <?php endif; ?>
                        </div>

                        <div class="tx-details">
                            <span class="tx-title">
                                <?php if ($tx['type'] === 'transfer'): ?>
                                    انتقال: <?= Format::e($tx['account_name']) ?> ← <?= Format::e($tx['to_account_name']) ?>
                                <?php else: ?>
                                    <?= !empty($tx['description']) ? Format::e($tx['description']) : Format::e($tx['category_name'] ?? 'تراکنش') ?>
                                <?php endif; ?>
                            </span>
                            <span class="tx-meta">
                                <?php if ($tx['type'] !== 'transfer' && !empty($tx['category_name'])): ?>
                                    <span><?= Format::e($tx['category_name']) ?></span> •
                                <?php endif; ?>
                                <span><?= Format::e($tx['account_name']) ?></span> •
                                <span><?= Jalali::relativeDate($tx['transaction_date']) ?></span>
                            </span>
                        </div>
                    </div>

                    <div class="tx-left">
                        <span class="tx-amount <?= $tx['type'] ?>">
                            <?= $tx['type'] === 'income' ? '+ ' : ($tx['type'] === 'expense' ? '- ' : '') ?>
                            <?= Format::rial($tx['amount_rial']) ?>
                        </span>
                        <span class="tx-toman"><?= Format::toman($tx['amount_rial']) ?></span>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</section>
