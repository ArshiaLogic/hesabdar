<?php
/**
 * Hesabdar - Category Management View
 */
?>

<div class="section-header">
    <h1 class="section-title">مدیریت دسته‌بندی‌ها</h1>
</div>

<div class="grid-2">
    <!-- Add New Category Form -->
    <div class="card" style="height: fit-content;">
        <h2 style="font-size: 1.05rem; font-weight: 800; margin-bottom: 1rem;">افزودن دسته‌بندی جدید</h2>

        <form action="index.php?r=categories/store" method="POST">
            <?= Csrf::field() ?>

            <div class="form-group">
                <label class="form-label">نام دسته‌بندی *</label>
                <input type="text" name="name" class="form-control" placeholder="مثال: رستوران، بیمه، فروش..." required>
            </div>

            <div class="form-group">
                <label class="form-label">نوع دسته‌بندی *</label>
                <select name="type" class="form-control" required>
                    <option value="expense">هزینه</option>
                    <option value="income">درآمد</option>
                </select>
            </div>

            <div class="form-group">
                <label class="form-label">رنگ نشانگر</label>
                <div style="display: flex; gap: 0.65rem; align-items: center;">
                    <input type="color" name="color" value="#f97316" style="width: 44px; height: 38px; border: 1px solid var(--border); border-radius: var(--radius-sm); cursor: pointer;">
                    <span style="font-size: 0.82rem; color: var(--text-muted);">رنگ برای نمایش در نمودارها</span>
                </div>
            </div>

            <button type="submit" class="btn btn-primary btn-block" style="padding: 0.8rem;">
                + ذخیره دسته‌بندی
            </button>
        </form>
    </div>

    <!-- Categories Lists -->
    <div>
        <!-- Expense Categories -->
        <div class="card" style="margin-bottom: 1.25rem;">
            <h2 style="font-size: 1rem; font-weight: 800; color: var(--danger); margin-bottom: 0.85rem; display: flex; align-items: center; gap: 0.4rem;">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="23 18 13.5 8.5 8.5 13.5 1 6"></polyline><polyline points="17 18 23 18 23 12"></polyline></svg>
                دسته‌بندی‌های هزینه (<?= count($expenseCategories) ?>)
            </h2>

            <div style="display: flex; flex-direction: column; gap: 0.5rem;">
                <?php foreach ($expenseCategories as $cat): ?>
                    <div style="display: flex; align-items: center; justify-content: space-between; padding: 0.6rem 0.85rem; background: var(--bg-input); border-radius: var(--radius-md); border-right: 4px solid <?= Format::e($cat['color']) ?>;">
                        <div>
                            <span style="font-weight: 700; font-size: 0.9rem;"><?= Format::e($cat['name']) ?></span>
                            <span style="font-size: 0.72rem; color: var(--text-muted); margin-right: 0.5rem;">(<?= Jalali::persianDigits((string)$cat['usage_count']) ?> تراکنش)</span>
                        </div>
                        <form action="index.php?r=categories/delete" method="POST" onsubmit="return confirmDelete('آیا از حذف این دسته‌بندی اطمینان دارید؟');" style="display: inline;">
                            <?= Csrf::field() ?>
                            <input type="hidden" name="id" value="<?= $cat['id'] ?>">
                            <button type="submit" class="btn-icon-link" style="width: 28px; height: 28px;" title="حذف">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path></svg>
                            </button>
                        </form>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Income Categories -->
        <div class="card">
            <h2 style="font-size: 1rem; font-weight: 800; color: var(--success); margin-bottom: 0.85rem; display: flex; align-items: center; gap: 0.4rem;">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="23 6 13.5 15.5 8.5 10.5 1 18"></polyline><polyline points="17 6 23 6 23 12"></polyline></svg>
                دسته‌بندی‌های درآمد (<?= count($incomeCategories) ?>)
            </h2>

            <div style="display: flex; flex-direction: column; gap: 0.5rem;">
                <?php foreach ($incomeCategories as $cat): ?>
                    <div style="display: flex; align-items: center; justify-content: space-between; padding: 0.6rem 0.85rem; background: var(--bg-input); border-radius: var(--radius-md); border-right: 4px solid <?= Format::e($cat['color']) ?>;">
                        <div>
                            <span style="font-weight: 700; font-size: 0.9rem;"><?= Format::e($cat['name']) ?></span>
                            <span style="font-size: 0.72rem; color: var(--text-muted); margin-right: 0.5rem;">(<?= Jalali::persianDigits((string)$cat['usage_count']) ?> تراکنش)</span>
                        </div>
                        <form action="index.php?r=categories/delete" method="POST" onsubmit="return confirmDelete('آیا از حذف این دسته‌بندی اطمینان دارید؟');" style="display: inline;">
                            <?= Csrf::field() ?>
                            <input type="hidden" name="id" value="<?= $cat['id'] ?>">
                            <button type="submit" class="btn-icon-link" style="width: 28px; height: 28px;" title="حذف">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path></svg>
                            </button>
                        </form>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>
