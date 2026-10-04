/**
 * Hesabdar - Client Side Interactions & UI Logic
 * Vanilla JavaScript (No Node.js, No External Library Required)
 */

document.addEventListener('DOMContentLoaded', () => {
    initAmountFormatters();
    initTypeToggles();
    initModals();
    initDatePresets();
});

/**
 * Real-time Rial Amount Formatter and Toman Preview
 */
function initAmountFormatters() {
    const amountInputs = document.querySelectorAll('.js-amount-input');

    amountInputs.forEach(input => {
        const preview = input.closest('.form-group')?.querySelector('.js-toman-preview') 
                     || document.querySelector('.js-toman-preview');

        const updatePreview = () => {
            let raw = input.value.replace(/[^\d]/g, '');
            if (!raw) {
                if (preview) preview.textContent = 'معادل ۰ تومان';
                return;
            }

            const rialNum = parseInt(raw, 10);
            input.value = rialNum.toLocaleString('en-US'); // Formats with commas

            if (preview) {
                const toman = Math.floor(rialNum / 10);
                preview.textContent = 'معادل ' + toPersianDigits(toman.toLocaleString('en-US')) + ' تومان';
            }
        };

        input.addEventListener('input', updatePreview);
        // Initial run if value exists
        if (input.value) {
            updatePreview();
        }
    });
}

/**
 * Switcher for Income / Expense / Transfer
 */
function initTypeToggles() {
    const toggles = document.querySelectorAll('.js-type-toggle');

    toggles.forEach(toggle => {
        const buttons = toggle.querySelectorAll('.type-toggle-btn');
        const form = toggle.closest('form');
        const typeInput = form?.querySelector('input[name="type"]');
        const categoryGroup = form?.querySelector('.js-category-group');
        const toAccountGroup = form?.querySelector('.js-to-account-group');
        const categorySelect = form?.querySelector('.js-category-select');

        buttons.forEach(btn => {
            btn.addEventListener('click', (e) => {
                e.preventDefault();
                const type = btn.getAttribute('data-type');

                // Update active states
                buttons.forEach(b => {
                    b.classList.remove('active', 'expense', 'income', 'transfer');
                });
                btn.classList.add('active', type);

                if (typeInput) {
                    typeInput.value = type;
                }

                // Adjust Form Fields based on Type
                if (type === 'transfer') {
                    if (categoryGroup) categoryGroup.style.display = 'none';
                    if (toAccountGroup) toAccountGroup.style.display = 'block';
                } else {
                    if (categoryGroup) categoryGroup.style.display = 'block';
                    if (toAccountGroup) toAccountGroup.style.display = 'none';

                    // Filter category dropdown options
                    if (categorySelect) {
                        Array.from(categorySelect.options).forEach(opt => {
                            if (!opt.value) return; // placeholder
                            const optType = opt.getAttribute('data-type');
                            if (optType === type) {
                                opt.style.display = '';
                                opt.disabled = false;
                            } else {
                                opt.style.display = 'none';
                                opt.disabled = true;
                            }
                        });

                        // Select first matching valid option
                        const validFirst = Array.from(categorySelect.options).find(o => o.value && o.getAttribute('data-type') === type);
                        if (validFirst) {
                            categorySelect.value = validFirst.value;
                        }
                    }
                }
            });
        });
    });
}

/**
 * Modal and Bottom Sheet Dialogs
 */
function initModals() {
    const overlay = document.getElementById('quickModal');
    if (!overlay) return;

    window.openQuickModal = function(defaultType = 'expense') {
        overlay.classList.add('active');
        document.body.style.overflow = 'hidden';

        // Set type button
        const targetBtn = overlay.querySelector(`.type-toggle-btn[data-type="${defaultType}"]`);
        if (targetBtn) {
            targetBtn.click();
        }

        // Focus on amount input
        setTimeout(() => {
            const amountInput = overlay.querySelector('.js-amount-input');
            if (amountInput) amountInput.focus();
        }, 150);
    };

    window.closeQuickModal = function() {
        overlay.classList.remove('active');
        document.body.style.overflow = '';
    };

    overlay.addEventListener('click', (e) => {
        if (e.target === overlay) {
            window.closeQuickModal();
        }
    });

    document.querySelectorAll('.js-modal-close').forEach(btn => {
        btn.addEventListener('click', () => {
            window.closeQuickModal();
        });
    });

    // ESC key closes modal
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') {
            window.closeQuickModal();
        }
    });
}

/**
 * Quick Date Preset Buttons (امروز، دیروز)
 */
function initDatePresets() {
    document.querySelectorAll('.js-date-preset').forEach(btn => {
        btn.addEventListener('click', (e) => {
            e.preventDefault();
            const daysOffset = parseInt(btn.getAttribute('data-offset') || '0', 10);
            const targetInput = document.querySelector(btn.getAttribute('data-target') || 'input[name="transaction_date"]');
            if (!targetInput) return;

            const date = new Date();
            date.setDate(date.getDate() + daysOffset);

            const jDate = gregorianToJalali(date.getFullYear(), date.getMonth() + 1, date.getDate());
            const pad = (n) => String(n).padStart(2, '0');
            targetInput.value = `${jDate[0]}/${pad(jDate[1])}/${pad(jDate[2])}`;
        });
    });
}

/**
 * Pure JS Jalali Conversion for instant client-side date calculation
 */
function gregorianToJalali(gy, gm, gd) {
    const g_d_m = [0, 31, 59, 90, 120, 151, 181, 212, 243, 273, 304, 334];
    const gy2 = (gm > 2) ? (gy + 1) : gy;
    let days = 355666 + (365 * gy) + Math.floor((gy2 + 3) / 4) - Math.floor((gy2 + 99) / 100) + Math.floor((gy2 + 399) / 400) + gd + g_d_m[gm - 1];
    let jy = -1595 + (33 * Math.floor(days / 12053));
    days %= 12053;
    jy += 4 * Math.floor(days / 1461);
    days %= 1461;
    if (days > 365) {
        jy += Math.floor((days - 1) / 365);
        days = (days - 1) % 365;
    }
    let jm, jd;
    if (days < 186) {
        jm = 1 + Math.floor(days / 31);
        jd = 1 + (days % 31);
    } else {
        jm = 7 + Math.floor((days - 186) / 30);
        jd = 1 + ((days - 186) % 30);
    }
    return [jy, jm, jd];
}

/**
 * Convert numbers to Persian digits in UI
 */
function toPersianDigits(str) {
    const en = ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9'];
    const fa = ['۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹'];
    return String(str).replace(/[0-9]/g, w => fa[en.indexOf(w)]);
}

/**
 * Confirm deletion helper
 */
window.confirmDelete = function(msg = 'آیا از حذف این مورد اطمینان دارید؟') {
    return confirm(msg);
};
