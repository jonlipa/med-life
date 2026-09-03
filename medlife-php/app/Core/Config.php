<?php

declare(strict_types=1);

namespace App\Core;

final class Config
{
    public function __construct(private array $items)
    {
    }

    public static function load(string $rootPath): self
    {
        $defaults = require $rootPath . '/config/defaults.php';
        $items = $defaults;

        foreach ([$rootPath . '/.env.example', $rootPath . '/.env'] as $envFile) {
            if (!is_file($envFile)) {
                continue;
            }

            foreach (file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) ?: [] as $line) {
                $line = trim($line);
                if ($line === '' || str_starts_with($line, '#') || !str_contains($line, '=')) {
                    continue;
                }

                [$name, $value] = array_map('trim', explode('=', $line, 2));
                $items[$name] = self::normalize($value);
            }
        }

        foreach (array_keys($items) as $key) {
            $runtimeValue = self::runtimeValue($key);
            if ($runtimeValue !== null) {
                $items[$key] = self::normalize($runtimeValue);
            }
        }

        return new self($items);
    }

    public function get(string $key, mixed $default = null): mixed
    {
        return $this->items[$key] ?? $default;
    }

    public function bool(string $key, bool $default = false): bool
    {
        $value = $this->get($key, $default);
        if (is_bool($value)) {
            return $value;
        }

        return filter_var((string) $value, FILTER_VALIDATE_BOOL);
    }

    private static function normalize(string $value): string
    {
        $value = trim($value);

        if (
            (str_starts_with($value, '"') && str_ends_with($value, '"')) ||
            (str_starts_with($value, "'") && str_ends_with($value, "'"))
        ) {
            return substr($value, 1, -1);
        }

        return $value;
    }

    private static function runtimeValue(string $key): ?string
    {
        $value = getenv($key);
        if ($value !== false) {
            return (string) $value;
        }

        if (array_key_exists($key, $_ENV)) {
            return (string) $_ENV[$key];
        }

        if (array_key_exists($key, $_SERVER)) {
            return (string) $_SERVER[$key];
        }

        return null;
    }
}
