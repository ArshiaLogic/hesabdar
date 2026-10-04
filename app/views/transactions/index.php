<?php
/**
 * Hesabdar - Transactions Ledger View with Filters and Pagination
 */
?>

<div class="section-header">
    <h1 class="section-title">دفتر کل تراکنش‌ها</h1>
    <a href="index.php?r=transactions/create" class="btn btn-primary" style="padding: 0.5rem 1rem; font-size: 0.88rem;">
        + ثبت تراکنش
    </a>
</div>

<!-- Filters Card (Collapsible) -->
<div class="card" style="margin-bottom: 1.25rem;">
    <form action="index.php" method="GET">
        <input type="hidden" name="r" value="transactions">

        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 0.75rem; margin-bottom: 0.75rem;">
            <!-- Type Filter -->
            <div>
                <label class="form-label" style="font-size: 0.78rem;">نوع</label>
                <select name="type" class="form-control" style="padding: 0.5rem 0.75rem; font-size: 0.85rem;">
                    <option value="">همه انواع</option>
                    <option value="expense" <?= ($filters['type'] ?? '') === 'expense' ? 'selected' : '' ?>>هزینه‌ها</option>
                    <option value="income" <?= ($filters['type'] ?? '') === 'income' ? 'selected' : '' ?>>درآمدها</option>
                    <option value="transfer" <?= ($filters['type'] ?? '') === 'transfer' ? 'selected' : '' ?>>انتقال‌ها</option>
                </select>
            </div>

            <!-- Account Filter -->
            <div>
                <label class="form-label" style="font-size: 0.78rem;">حساب</label>
                <select name="account_id" class="form-control" style="padding: 0.5rem 0.75rem; font-size: 0.85rem;">
                    <option value="">همه حساب‌ها</option>
                    <?php foreach ($accounts as $acc): ?>
                        <option value="<?= $acc['id'] ?>" <?= ((int)($filters['account_id'] ?? 0) === (int)$acc['id']) ? 'selected' : '' ?>>
                            <?= Format::e($acc['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Category Filter -->
            <div>
                <label class="form-label" style="font-size: 0.78rem;">دسته‌بندی</label>
                <select name="category_id" class="form-control" style="padding: 0.5rem 0.75rem; font-size: 0.85rem;">
                    <option value="">همه دسته‌ها</option>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?= $cat['id'] ?>" <?= ((int)($filters['category_id'] ?? 0) === (int)$cat['id']) ? 'selected' : '' ?>>
                            <?= Format::e($cat['name']) ?> (<?= $cat['type'] === 'income' ? 'درآمد' : 'هزینه' ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Start Date Filter (Jalali) -->
            <div>
                <label class="form-label" style="font-size: 0.78rem;">از تاریخ (شمسی)</label>
                <input 
                    type="text" 
                    name="start_date" 
                    class="form-control" 
                    style="padding: 0.5rem 0.75rem; font-size: 0.85rem;" 
                    placeholder="۱۴۰۳/۰۱/۰۱" 
                    value="<?= Format::e($rawStartDate ?? '') ?>"
                >
            </div>

            <!-- End Date Filter (Jalali) -->
            <div>
                <label class="form-label" style="font-size: 0.78rem;">تا تاریخ (شمسی)</label>
                <input 
                    type="text" 
                    name="end_date" 
                    class="form-control" 
                    style="padding: 0.5rem 0.75rem; font-size: 0.85rem;" 
                    placeholder="۱۴۰۳/۱۲/۲۹" 
                    value="<?= Format::e($rawEndDate ?? '') ?>"
                >
            </div>

            <!-- Search Description -->
            <div>
                <label class="form-label" style="font-size: 0.78rem;">جستجو در توضیحات</label>
                <input 
                    type="text" 
                    name="search" 
                    class="form-control" 
                    style="padding: 0.5rem 0.75rem; font-size: 0.85rem;" 
                    placeholder="کلمه کلیدی..." 
                    value="<?= Format::e($filters['search'] ?? '') ?>"
                >
            </div>
        </div>

        <div style="display: flex; gap: 0.5rem; justify-content: flex-end;">
            <a href="index.php?r=transactions" class="btn btn-secondary" style="padding: 0.45rem 0.85rem; font-size: 0.82rem;">
                پاک کردن فیلترها
            </a>
            <button type="submit" class="btn btn-primary" style="padding: 0.45rem 1rem; font-size: 0.82rem;">
                اعمال فیلتر
            </button>
        </div>
    </form>
</div>

<!-- Transactions Table / Mobile Card List -->
<div class="transactions-card">
    <?php if (empty($transactions)): ?>
        <div class="empty-state">
            <div class="empty-state-icon">🔍</div>
            <p>هیچ تراکنشی مطابق با فیلترهای انتخابی یافت نشد.</p>
        </div>
    <?php else: ?>
        <?php foreach ($transactions as $tx): ?>
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
                            <span class="badge <?= Format::transactionBadgeClass($tx['type']) ?>">
                                <?= Format::transactionTypeFa($tx['type']) ?>
                            </span>
                            <?php if ($tx['type'] !== 'transfer' && !empty($tx['category_name'])): ?>
                                <span><?= Format::e($tx['category_name']) ?></span> •
                            <?php endif; ?>
                            <span><?= Format::e($tx['account_name']) ?></span> •
                            <span><?= Jalali::toJalali($tx['transaction_date'], 'Y/m/d') ?></span>
                        </span>
                    </div>
                </div>

                <div class="tx-left">
                    <span class="tx-amount <?= $tx['type'] ?>">
                        <?= $tx['type'] === 'income' ? '+ ' : ($tx['type'] === 'expense' ? '- ' : '') ?>
                        <?= Format::rial($tx['amount_rial']) ?>
                    </span>
                    <span class="tx-toman"><?= Format::toman($tx['amount_rial']) ?></span>

                    <!-- Quick Actions (Edit / Delete) -->
                    <div style="display: flex; gap: 0.35rem; margin-top: 0.4rem;">
                        <a href="index.php?r=transactions/edit&id=<?= $tx['id'] ?>" class="btn-icon-link" style="width: 28px; height: 28px;" title="ویرایش">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 20h9"></path><path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"></path></svg>
                        </a>
                        <form action="index.php?r=transactions/delete" method="POST" onsubmit="return confirmDelete('آیا از حذف این تراکنش اطمینان دارید؟ موجودی حساب مربوطه اصلاح خواهد شد.');" style="display: inline;">
                            <?= Csrf::field() ?>
                            <input type="hidden" name="id" value="<?= $tx['id'] ?>">
                            <button type="submit" class="btn-icon-link" style="width: 28px; height: 28px;" title="حذف">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path></svg>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<!-- Pagination Controls -->
<?php if ($pagination['total_pages'] > 1): ?>
    <div class="pagination">
        <?php
        $queryParams = $_GET;
        for ($p = 1; $p <= $pagination['total_pages']; $p++):
            $queryParams['page'] = $p;
            $pageUrl = 'index.php?' . http_build_query($queryParams);
        ?>
            <a href="<?= $pageUrl ?>" class="page-btn <?= $p === $pagination['current_page'] ? 'active' : '' ?>">
                <?= Jalali::persianDigits((string)$p) ?>
            </a>
        <?php endfor; ?>
    </div>
<?php endif; ?>
