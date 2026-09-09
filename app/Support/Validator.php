<?php

declare(strict_types=1);

namespace App\Support;

/**
 * Base class for the validators in app/Validation.
 *
 * It is deliberately small: a few reusable rules and a list of error messages.
 * Validators return an array like ['email' => 'Please enter a valid email.'].
 */
abstract class Validator
{
    /** @var array<string, string> */
    protected array $errors = [];

    /**
     * @return array<string, string>
     */
    public function errors(): array
    {
        return $this->errors;
    }

    public function passes(): bool
    {
        return $this->errors === [];
    }

    protected function reset(): void
    {
        $this->errors = [];
    }

    /**
     * Record the first error for a field (later rules do not overwrite it).
     */
    protected function addError(string $field, string $message): void
    {
        if (!isset($this->errors[$field])) {
            $this->errors[$field] = $message;
        }
    }

    protected function required(string $field, ?string $value, string $label): bool
    {
        if ($value === null || trim($value) === '') {
            $this->addError($field, "{$label} is required.");

            return false;
        }

        return true;
    }

    protected function between(string $field, string $value, int $min, int $max, string $label): void
    {
        $length = mb_strlen(trim($value));

        if ($length < $min) {
            $this->addError($field, "{$label} must be at least {$min} characters.");
        } elseif ($length > $max) {
            $this->addError($field, "{$label} may not be longer than {$max} characters.");
        }
    }

    protected function email(string $field, string $value): void
    {
        if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
            $this->addError($field, 'Please enter a valid email address.');
        } elseif (mb_strlen($value) > 190) {
            $this->addError($field, 'The email address is too long.');
        }
    }

    /**
     * A positive money amount with at most two decimals.
     */
    protected function price(string $field, string $value, string $label): void
    {
        if (!is_numeric($value)) {
            $this->addError($field, "{$label} must be a number.");

            return;
        }

        $amount = (float) $value;

        if ($amount <= 0) {
            $this->addError($field, "{$label} must be greater than zero.");
        } elseif ($amount > 9999999.99) {
            $this->addError($field, "{$label} is too large.");
        } elseif (round($amount, 2) !== $amount) {
            $this->addError($field, "{$label} may have at most 2 decimal places.");
        }
    }

    protected function integerBetween(string $field, ?string $value, int $min, int $max, string $label): void
    {
        if ($value === null || !preg_match('/^\d+$/', $value)) {
            $this->addError($field, "{$label} must be a whole number.");

            return;
        }

        $number = (int) $value;

        if ($number < $min || $number > $max) {
            $this->addError($field, "{$label} must be between {$min} and {$max}.");
        }
    }
}
