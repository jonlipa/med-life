<?php

declare(strict_types=1);

namespace App\Support;

final class Validator
{
    public static function email(string $value): bool
    {
        return filter_var($value, FILTER_VALIDATE_EMAIL) !== false;
    }

    public static function required(mixed $value, string $label = ''): bool
    {
        return is_string($value) && trim($value) !== '';
    }

    public static function minLength(string $value, int $min): bool
    {
        return strlen($value) >= $min;
    }

    public static function maxLength(string $value, int $max): bool
    {
        return strlen($value) <= $max;
    }

    public static function in(mixed $value, array $allowed): bool
    {
        return in_array($value, $allowed, true);
    }

    public static function integer(mixed $value, int $min = 0, ?int $max = null): bool
    {
        $filtered = filter_var($value, FILTER_VALIDATE_INT);
        if ($filtered === false) {
            return false;
        }
        $int = (int) $filtered;
        if ($int < $min) {
            return false;
        }
        if ($max !== null && $int > $max) {
            return false;
        }
        return true;
    }

    public static function validate(array $rules, array $data): array
    {
        $errors = [];

        foreach ($rules as $field => $ruleString) {
            $rulesList = explode('|', $ruleString);

            foreach ($rulesList as $rule) {
                if ($rule === 'required') {
                    $value = $data[$field] ?? '';
                    if (!self::required($value)) {
                        $errors[$field][] = "Fusha '$field' eshte e detyrueshme.";
                    }
                    continue;
                }

                if (str_starts_with($rule, 'min:')) {
                    $min = (int) substr($rule, 4);
                    $value = (string) ($data[$field] ?? '');
                    if (!self::minLength($value, $min)) {
                        $errors[$field][] = "Fusha '$field' duhet te kete te pakten $min karaktere.";
                    }
                    continue;
                }

                if (str_starts_with($rule, 'max:')) {
                    $max = (int) substr($rule, 4);
                    $value = (string) ($data[$field] ?? '');
                    if (!self::maxLength($value, $max)) {
                        $errors[$field][] = "Fusha '$field' nuk duhet te kaloje $max karaktere.";
                    }
                    continue;
                }

                if ($rule === 'email') {
                    $value = (string) ($data[$field] ?? '');
                    if ($value !== '' && !self::email($value)) {
                        $errors[$field][] = "Fusha '$field' duhet te jete nje email i vlefshem.";
                    }
                    continue;
                }

                if (str_starts_with($rule, 'in:')) {
                    $allowed = explode(',', substr($rule, 3));
                    $value = $data[$field] ?? '';
                    if (!self::in($value, $allowed)) {
                        $errors[$field][] = "Fusha '$field' duhet te jete nje nga: " . implode(', ', $allowed) . ".";
                    }
                    continue;
                }

                if ($rule === 'integer') {
                    $value = $data[$field] ?? '';
                    if (!self::integer($value)) {
                        $errors[$field][] = "Fusha '$field' duhet te jete numer i plote.";
                    }
                }
            }
        }

        return $errors;
    }
}
