<?php

declare(strict_types=1);

namespace App\Core;

final class App
{
    private static array $container = [];

    public static function set(string $key, mixed $value): void
    {
        self::$container[$key] = $value;
    }

    public static function get(string $key, mixed $default = null): mixed
    {
        return self::$container[$key] ?? $default;
    }

    public static function all(): array
    {
        return self::$container;
    }
}
