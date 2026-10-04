<?php
/**
 * Hesabdar - Jalali (Solar Hijri) Date & Number Conversion Helper
 * Standalone, lightweight, zero external dependencies
 */

class Jalali {
    private static array $monthNames = [
        1 => 'فروردین',
        2 => 'اردیبهشت',
        3 => 'خرداد',
        4 => 'تیر',
        5 => 'مرداد',
        6 => 'شهریور',
        7 => 'مهر',
        8 => 'آبان',
        9 => 'آذر',
        10 => 'دی',
        11 => 'بهمن',
        12 => 'اسفند'
    ];

    private static array $dayNames = [
        0 => 'شنبه',
        1 => 'یکشنبه',
        2 => 'دوشنبه',
        3 => 'سه‌شنبه',
        4 => 'چهارشنبه',
        5 => 'پنج‌شنبه',
        6 => 'جمعه'
    ];

    /**
     * Convert Gregorian date to Jalali array [year, month, day]
     */
    public static function gregorianToJalali(int $gy, int $gm, int $gd): array {
        $g_d_m = [0, 31, 59, 90, 120, 151, 181, 212, 243, 273, 304, 334];
        $gy2 = ($gm > 2) ? ($gy + 1) : $gy;
        $days = 355666 + (365 * $gy) + ((int)(($gy2 + 3) / 4)) - ((int)(($gy2 + 99) / 100)) + ((int)(($gy2 + 399) / 400)) + $gd + $g_d_m[$gm - 1];
        $jy = -1595 + (33 * ((int)($days / 12053)));
        $days %= 12053;
        $jy += 4 * ((int)($days / 1461));
        $days %= 1461;
        if ($days > 365) {
            $jy += (int)(($days - 1) / 365);
            $days = ($days - 1) % 365;
        }
        if ($days < 186) {
            $jm = 1 + (int)($days / 31);
            $jd = 1 + ($days % 31);
        } else {
            $jm = 7 + (int)(($days - 186) / 30);
            $jd = 1 + (($days - 186) % 30);
        }
        return [$jy, $jm, $jd];
    }

    /**
     * Convert Jalali date to Gregorian array [year, month, day]
     */
    public static function jalaliToGregorian(int $jy, int $jm, int $jd): array {
        $jy += 1595;
        $days = -355668 + (365 * $jy) + (((int)($jy / 33)) * 8) + ((int)((($jy % 33) + 3) / 4)) + $jd + (($jm < 7) ? ($jm - 1) * 31 : (($jm - 7) * 30) + 186);
        $gy = 400 * ((int)($days / 146097));
        $days %= 146097;
        if ($days > 36524) {
            $gy += 100 * ((int)(--$days / 36524));
            $days %= 36524;
            if ($days >= 365) $days++;
        }
        $gy += 4 * ((int)($days / 1461));
        $days %= 1461;
        if ($days > 365) {
            $gy += (int)(($days - 1) / 365);
            $days = ($days - 1) % 365;
        }
        $gd = $days + 1;
        $sal_a = [0, 31, (($gy % 4 == 0 && $gy % 100 != 0) || ($gy % 400 == 0)) ? 29 : 28, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31];
        for ($gm = 0; $gm < 13 && $gd > $sal_a[$gm]; $gm++) {
            $gd -= $sal_a[$gm];
        }
        return [$gy, $gm, $gd];
    }

    /**
     * Convert Gregorian date string (YYYY-MM-DD or YYYY-MM-DD HH:MM:SS) to Jalali formatted string
     */
    public static function toJalali(?string $gDate, string $format = 'Y/m/d', bool $persianDigits = true): string {
        if (empty($gDate)) {
            return '';
        }

        $timestamp = strtotime($gDate);
        if (!$timestamp) {
            return '';
        }

        $gy = (int)date('Y', $timestamp);
        $gm = (int)date('m', $timestamp);
        $gd = (int)date('d', $timestamp);

        [$jy, $jm, $jd] = self::gregorianToJalali($gy, $gm, $gd);

        $jMonthName = self::$monthNames[$jm] ?? '';
        $padM = str_pad((string)$jm, 2, '0', STR_PAD_LEFT);
        $padD = str_pad((string)$jd, 2, '0', STR_PAD_LEFT);

        $result = str_replace(
            ['Y', 'm', 'd', 'F', 'j'],
            [$jy, $padM, $padD, $jMonthName, $jd],
            $format
        );

        return $persianDigits ? self::persianDigits($result) : $result;
    }

    /**
     * Convert Jalali date string (YYYY/MM/DD or YYYY-MM-DD) to Gregorian date string (YYYY-MM-DD)
     */
    public static function toGregorian(?string $jDate): ?string {
        if (empty($jDate)) {
            return null;
        }

        $jDate = self::englishDigits(trim($jDate));
        $delimiter = strpos($jDate, '/') !== false ? '/' : '-';
        $parts = explode($delimiter, $jDate);

        if (count($parts) !== 3) {
            return null;
        }

        $jy = (int)$parts[0];
        $jm = (int)$parts[1];
        $jd = (int)$parts[2];

        if ($jy < 1300 || $jy > 1500 || $jm < 1 || $jm > 12 || $jd < 1 || $jd > 31) {
            return null;
        }

        [$gy, $gm, $gd] = self::jalaliToGregorian($jy, $jm, $jd);

        return sprintf("%04d-%02d-%02d", $gy, $gm, $gd);
    }

    /**
     * Get start and end Gregorian dates for a Jalali month
     * Defaults to current Jalali month if year and month are omitted
     */
    public static function getJalaliMonthRange(?int $jy = null, ?int $jm = null): array {
        if ($jy === null || $jm === null) {
            $now = time();
            $gy = (int)date('Y', $now);
            $gm = (int)date('m', $now);
            $gd = (int)date('d', $now);
            [$jy, $jm] = self::gregorianToJalali($gy, $gm, $gd);
        }

        $daysInMonth = 30;
        if ($jm <= 6) {
            $daysInMonth = 31;
        } elseif ($jm === 12) {
            // Check Jalali leap year
            $isLeap = (((($jy - (($jy > 0) ? 474 : 473)) % 2820) + 474 + 38) * 682) % 2816 < 682;
            $daysInMonth = $isLeap ? 30 : 29;
        }

        [$gy1, $gm1, $gd1] = self::jalaliToGregorian($jy, $jm, 1);
        [$gy2, $gm2, $gd2] = self::jalaliToGregorian($jy, $jm, $daysInMonth);

        $startDate = sprintf("%04d-%02d-%02d", $gy1, $gm1, $gd1);
        $endDate = sprintf("%04d-%02d-%02d", $gy2, $gm2, $gd2);

        return [
            'year' => $jy,
            'month' => $jm,
            'month_name' => self::$monthNames[$jm] ?? '',
            'title' => (self::$monthNames[$jm] ?? '') . ' ' . self::persianDigits((string)$jy),
            'start_date' => $startDate,
            'end_date' => $endDate,
            'days_in_month' => $daysInMonth
        ];
    }

    /**
     * Relative human-readable Jalali date (e.g. امروز، دیروز، ۲ روز قبل، یا تاریخ شمسی)
     */
    public static function relativeDate(string $gDate): string {
        $today = date('Y-m-d');
        $yesterday = date('Y-m-d', strtotime('-1 day'));

        $dateOnly = substr($gDate, 0, 10);

        if ($dateOnly === $today) {
            return 'امروز';
        }
        if ($dateOnly === $yesterday) {
            return 'دیروز';
        }

        return self::toJalali($dateOnly, 'j F Y');
    }

    /**
     * Convert English digits to Persian digits
     */
    public static function persianDigits(string $str): string {
        $en = ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9'];
        $fa = ['۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹'];
        return str_replace($en, $fa, (string)$str);
    }

    /**
     * Convert Persian & Arabic digits to English digits
     */
    public static function englishDigits(string $str): string {
        $fa = ['۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹'];
        $ar = ['٠', '١', '٢', '٣', '٤', '٥', '٦', '٧', '٨', '٩'];
        $en = ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9'];

        $str = str_replace($fa, $en, (string)$str);
        return str_replace($ar, $en, $str);
    }
}
