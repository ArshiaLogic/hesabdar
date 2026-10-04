<?php
/**
 * Hesabdar - Financial Formatting & Presentation Helper
 * Strictly operates on Iranian Rial (IRR) integers.
 */

class Format {
    /**
     * Format Iranian Rial with 3-digit comma separators and Persian digits
     * Example: 15000000 -> "۱۵,۰۰۰,۰۰۰ ریال"
     */
    public static function rial(int|float|string|null $amount, bool $showUnit = true): string {
        $cleanAmount = (int)($amount ?? 0);
        $isNegative = $cleanAmount < 0;
        $absAmount = abs($cleanAmount);

        $formatted = number_format($absAmount, 0, '.', '،');
        $persian = Jalali::persianDigits($formatted);

        $prefix = $isNegative ? '- ' : '';
        $unit = $showUnit ? ' ریال' : '';

        return $prefix . $persian . $unit;
    }

    /**
     * Helper displaying Toman equivalent for familiar Iranian mental accounting
     * Example: 15000000 -> "۱,۵۰۰,۰۰۰ تومان"
     */
    public static function toman(int|float|string|null $amount): string {
        $cleanAmount = (int)($amount ?? 0);
        $toman = (int)floor($cleanAmount / 10);
        $isNegative = $toman < 0;
        $absAmount = abs($toman);

        $formatted = number_format($absAmount, 0, '.', '،');
        $persian = Jalali::persianDigits($formatted);

        $prefix = $isNegative ? '- ' : '';
        return $prefix . $persian . ' تومان';
    }

    /**
     * Sanitizes and extracts pure integer Rial amount from user input
     * Strips commas, whitespace, and handles Persian/Arabic digits
     */
    public static function cleanAmount(mixed $input): int {
        if (empty($input)) {
            return 0;
        }

        $str = Jalali::englishDigits((string)$input);
        // Remove commas, spaces, currency words
        $str = preg_replace('/[^\d]/', '', $str);

        return (int)$str;
    }

    /**
     * HTML escape for XSS protection
     */
    public static function e(?string $string): string {
        return htmlspecialchars((string)($string ?? ''), ENT_QUOTES, 'UTF-8');
    }

    /**
     * Persian label for account types
     */
    public static function accountTypeFa(string $type): string {
        return match ($type) {
            'bank_card' => 'کارت بانکی',
            'cash' => 'پول نقد / کیف پول',
            'savings' => 'حساب پس‌انداز',
            'other' => 'سایر حساب‌ها',
            default => 'حساب بانکی'
        };
    }

    /**
     * Persian label for transaction types
     */
    public static function transactionTypeFa(string $type): string {
        return match ($type) {
            'income' => 'درآمد',
            'expense' => 'هزینه',
            'transfer' => 'انتقال بین حساب‌ها',
            default => $type
        };
    }

    /**
     * Persian badge styling classes
     */
    public static function transactionBadgeClass(string $type): string {
        return match ($type) {
            'income' => 'badge-income',
            'expense' => 'badge-expense',
            'transfer' => 'badge-transfer',
            default => 'badge-neutral'
        };
    }
}
