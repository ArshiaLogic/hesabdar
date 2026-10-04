<?php
/**
 * Hesabdar - Server-Side Request Validator
 */

class Validator {
    private array $data;
    private array $errors = [];

    public function __construct(array $data) {
        $this->data = $data;
    }

    public static function make(array $data): self {
        return new self($data);
    }

    /**
     * Check if a field is present and not empty
     */
    public function required(string $field, string $errorMessage): self {
        $val = trim((string)($this->data[$field] ?? ''));
        if ($val === '') {
            $this->errors[$field] = $errorMessage;
        }
        return $this;
    }

    /**
     * Check if value is a valid email
     */
    public function email(string $field, string $errorMessage): self {
        $val = trim((string)($this->data[$field] ?? ''));
        if ($val !== '' && !filter_var($val, FILTER_VALIDATE_EMAIL)) {
            $this->errors[$field] = $errorMessage;
        }
        return $this;
    }

    /**
     * Validate positive integer amount
     */
    public function positiveAmount(string $field, string $errorMessage): self {
        $raw = $this->data[$field] ?? '';
        $amount = Format::cleanAmount($raw);

        if ($amount <= 0) {
            $this->errors[$field] = $errorMessage;
        }
        return $this;
    }

    /**
     * Validate value is in allowed whitelist
     */
    public function in(string $field, array $allowed, string $errorMessage): self {
        $val = $this->data[$field] ?? null;
        if (!in_array($val, $allowed, true)) {
            $this->errors[$field] = $errorMessage;
        }
        return $this;
    }

    /**
     * Validate date string (handles both Gregorian YYYY-MM-DD and Jalali YYYY/MM/DD)
     */
    public function validDate(string $field, string $errorMessage): self {
        $val = trim((string)($this->data[$field] ?? ''));
        if ($val === '') {
            $this->errors[$field] = $errorMessage;
            return $this;
        }

        // Try Gregorian
        $gregorian = null;
        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $val)) {
            $gregorian = $val;
        } else {
            // Try Jalali conversion
            $gregorian = Jalali::toGregorian($val);
        }

        if (!$gregorian || !strtotime($gregorian)) {
            $this->errors[$field] = $errorMessage;
        }

        return $this;
    }

    /**
     * Minimum string length
     */
    public function minLength(string $field, int $min, string $errorMessage): self {
        $val = (string)($this->data[$field] ?? '');
        if (mb_strlen($val, 'UTF-8') < $min) {
            $this->errors[$field] = $errorMessage;
        }
        return $this;
    }

    /**
     * Check whether validation passed
     */
    public function passes(): bool {
        return empty($this->errors);
    }

    /**
     * Check whether validation failed
     */
    public function fails(): bool {
        return !empty($this->errors);
    }

    /**
     * Get all errors
     */
    public function errors(): array {
        return $this->errors;
    }

    /**
     * Get first error message
     */
    public function firstError(): string {
        return reset($this->errors) ?: 'خطایی در اعتبارسنجی اطلاعات رخ داده است.';
    }
}
