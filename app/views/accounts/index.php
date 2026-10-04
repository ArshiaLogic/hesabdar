<?php
/**
 * Hesabdar - Accounts List View
 */
?>

<div class="section-header">
    <div>
        <h1 class="section-title">حساب‌ها و کارت‌ها</h1>
        <div style="font-size: 0.85rem; color: var(--text-muted); margin-top: 0.15rem;">
            مجموع دارایی: <strong style="color: var(--text-main);"><?= Format::rial($totalBalance) ?></strong>
        </div>
    </div>
    <a href="index.php?r=accounts/create" class="btn btn-primary" style="padding: 0.5rem 1rem; font-size: 0.88rem;">
        + افزودن حساب
    </a>
</div>

<div class="grid-2">
    <?php foreach ($accounts as $acc): ?>
        <div class="card" style="border-right: 5px solid <?= Format::e($acc['color']) ?>; position: relative;">
            <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 0.75rem;">
                <div>
                    <h3 style="font-size: 1.05rem; font-weight: 800; color: var(--text-main);">
                        <?= Format::e($acc['name']) ?>
                    </h3>
                    <div style="font-size: 0.78rem; color: var(--text-muted);">
                        <?= Format::accountTypeFa($acc['type']) ?>
                        <?php if ($acc['status'] === 'archived'): ?>
                            <span class="badge badge-neutral" style="margin-right: 0.4rem;">بایگانی‌شده</span>
                        <?php endif; ?>
                    </div>
                </div>

                <div style="display: flex; gap: 0.35rem;">
                    <a href="index.php?r=accounts/edit&id=<?= $acc['id'] ?>" class="btn-icon-link" style="width: 32px; height: 32px;" title="ویرایش">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 20h9"></path><path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"></path></svg>
                    </a>
                    <form action="index.php?r=accounts/delete" method="POST" onsubmit="return confirmDelete('آیا از حذف/بایگانی این حساب اطمینان دارید؟');" style="display: inline;">
                        <?= Csrf::field() ?>
                        <input type="hidden" name="id" value="<?= $acc['id'] ?>">
                        <button type="submit" class="btn-icon-link" style="width: 32px; height: 32px;" title="حذف/بایگانی">
                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path></svg>
                        </button>
                    </form>
                </div>
            </div>

            <!-- Card Number Preview -->
            <?php if (!empty($acc['card_number'])): ?>
                <div style="font-family: monospace; font-size: 0.88rem; color: var(--text-muted); direction: ltr; text-align: right; margin-bottom: 0.75rem;">
                    •••• •••• •••• <?= substr($acc['card_number'], -4) ?>
                </div>
            <?php endif; ?>

            <!-- Balances -->
            <div style="background: var(--bg-input); padding: 0.85rem; border-radius: var(--radius-md);">
                <div style="display: flex; justify-content: space-between; align-items: baseline;">
                    <span style="font-size: 0.82rem; color: var(--text-muted);">موجودی فعلی:</span>
                    <span style="font-size: 1.15rem; font-weight: 800; color: var(--text-main);">
                        <?= Format::rial($acc['current_balance']) ?>
                    </span>
                </div>
                <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 0.25rem; font-size: 0.75rem; color: var(--text-subtle);">
                    <span>موجودی اولیه: <?= Format::rial($acc['opening_balance']) ?></span>
                    <span>معادل: <?= Format::toman($acc['current_balance']) ?></span>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
</div>
