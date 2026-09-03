<?php

declare(strict_types=1);

namespace App\Core;

final class Session
{
    public static function start(array $options = []): void
    {
        if (PHP_SAPI === 'cli') {
            return;
        }

        if (session_status() === PHP_SESSION_ACTIVE) {
            return;
        }

        session_name((string) ($options['name'] ?? 'med_life_portal'));

        $secure = (bool) ($options['secure'] ?? false);
        $samesite = (string) ($options['samesite'] ?? 'Lax');

        session_set_cookie_params([
            'lifetime' => 0,
            'path' => '/',
            'domain' => '',
            'secure' => $secure,
            'httponly' => true,
            'samesite' => $samesite,
        ]);

        session_start();
        self::ageFlashData();
    }

    public static function put(string $key, mixed $value): void
    {
        $_SESSION[$key] = $value;
    }

    public static function get(string $key, mixed $default = null): mixed
    {
        return $_SESSION[$key] ?? $default;
    }

    public static function forget(string $key): void
    {
        unset($_SESSION[$key]);
    }

    public static function flash(string $key, mixed $value): void
    {
        $_SESSION[$key] = $value;
        $_SESSION['_flash_new'] ??= [];
        if (!in_array($key, $_SESSION['_flash_new'], true)) {
            $_SESSION['_flash_new'][] = $key;
        }
    }

    public static function regenerate(): void
    {
        if (PHP_SAPI === 'cli') {
            return;
        }

        session_regenerate_id(true);
    }

    public static function destroy(): void
    {
        if (PHP_SAPI === 'cli') {
            return;
        }

        $_SESSION = [];
        if (session_status() === PHP_SESSION_ACTIVE) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', [
                'expires' => time() - 42000,
                'path' => $params['path'] ?? '/',
                'domain' => $params['domain'] ?? '',
                'secure' => (bool) ($params['secure'] ?? false),
                'httponly' => true,
                'samesite' => (string) ($params['samesite'] ?? 'Lax'),
            ]);

            session_destroy();
        }
    }

    private static function ageFlashData(): void
    {
        $old = $_SESSION['_flash_old'] ?? [];
        foreach ($old as $key) {
            unset($_SESSION[$key]);
        }

        $_SESSION['_flash_old'] = $_SESSION['_flash_new'] ?? [];
        $_SESSION['_flash_new'] = [];
    }
}
