<?php

declare(strict_types=1);

namespace App\Core;

final class RateLimiter
{
    private static ?array $store = null;

    public static function init(): void
    {
        if (self::$store === null) {
            self::$store = [];
        }
    }

    public static function attempt(string $key, int $maxAttempts, int $decaySeconds): bool
    {
        $fileResult = self::attemptWithFileStore($key, $maxAttempts, $decaySeconds);
        if ($fileResult !== null) {
            return $fileResult;
        }

        self::init();

        $now = time();
        $record = self::$store[$key] ?? ['count' => 0, 'reset' => $now + $decaySeconds];

        if ($now > $record['reset']) {
            self::$store[$key] = ['count' => 1, 'reset' => $now + $decaySeconds];
            return true;
        }

        self::$store[$key]['count'] = $record['count'] + 1;

        return self::$store[$key]['count'] <= $maxAttempts;
    }

    public static function remaining(string $key, int $maxAttempts): int
    {
        $record = self::readFileRecord($key);
        if ($record !== null) {
            if (time() > (int) ($record['reset'] ?? 0)) {
                return $maxAttempts;
            }

            return max(0, $maxAttempts - (int) ($record['count'] ?? 0));
        }

        $record = self::$store[$key] ?? ['count' => 0];
        return max(0, $maxAttempts - $record['count']);
    }

    private static function attemptWithFileStore(string $key, int $maxAttempts, int $decaySeconds): ?bool
    {
        $path = self::pathForKey($key);
        if ($path === null) {
            return null;
        }

        $handle = @fopen($path, 'c+');
        if ($handle === false) {
            return null;
        }

        try {
            if (!flock($handle, LOCK_EX)) {
                return null;
            }

            rewind($handle);
            $contents = stream_get_contents($handle);
            $record = is_string($contents) && $contents !== ''
                ? json_decode($contents, true)
                : null;

            if (!is_array($record)) {
                $record = ['count' => 0, 'reset' => time() + $decaySeconds];
            }

            $now = time();
            if ($now > (int) ($record['reset'] ?? 0)) {
                $record = ['count' => 1, 'reset' => $now + $decaySeconds];
            } else {
                $record['count'] = ((int) ($record['count'] ?? 0)) + 1;
            }

            ftruncate($handle, 0);
            rewind($handle);
            fwrite($handle, json_encode($record, JSON_THROW_ON_ERROR));
            fflush($handle);

            return (int) $record['count'] <= $maxAttempts;
        } catch (\Throwable) {
            return null;
        } finally {
            flock($handle, LOCK_UN);
            fclose($handle);
        }
    }

    private static function readFileRecord(string $key): ?array
    {
        $path = self::pathForKey($key, false);
        if ($path === null || !is_file($path)) {
            return null;
        }

        $contents = @file_get_contents($path);
        if (!is_string($contents) || $contents === '') {
            return null;
        }

        $record = json_decode($contents, true);

        return is_array($record) ? $record : null;
    }

    private static function pathForKey(string $key, bool $createDirectory = true): ?string
    {
        $basePath = function_exists('base_path')
            ? base_path('storage/ratelimits')
            : dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'storage' . DIRECTORY_SEPARATOR . 'ratelimits';

        if (!is_dir($basePath)) {
            if (!$createDirectory || !@mkdir($basePath, 0775, true)) {
                return null;
            }
        }

        if (!is_writable($basePath)) {
            return null;
        }

        return $basePath . DIRECTORY_SEPARATOR . hash('sha256', $key) . '.json';
    }
}
