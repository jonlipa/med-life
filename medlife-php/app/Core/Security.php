<?php

declare(strict_types=1);

namespace App\Core;

final class Security
{
    public static function isHttps(array $server): bool
    {
        $https = strtolower((string) ($server['HTTPS'] ?? ''));
        if ($https !== '' && $https !== 'off') {
            return true;
        }

        if ((string) ($server['SERVER_PORT'] ?? '') === '443') {
            return true;
        }

        $forwardedProto = strtolower((string) ($server['HTTP_X_FORWARDED_PROTO'] ?? ''));
        foreach (explode(',', $forwardedProto) as $proto) {
            if (trim($proto) === 'https') {
                return true;
            }
        }

        $forwardedSsl = strtolower((string) ($server['HTTP_X_FORWARDED_SSL'] ?? ''));
        if ($forwardedSsl === 'on' || $forwardedSsl === '1') {
            return true;
        }

        $frontEndHttps = strtolower((string) ($server['HTTP_FRONT_END_HTTPS'] ?? ''));
        return $frontEndHttps !== '' && $frontEndHttps !== 'off';
    }

    public static function secureCookie(Config $config, array $server): bool
    {
        $value = strtolower(trim((string) $config->get('SESSION_COOKIE_SECURE', 'false')));
        if ($value === 'auto') {
            return self::isHttps($server);
        }

        return filter_var($value, FILTER_VALIDATE_BOOL);
    }

    public static function redirectToHttpsIfNeeded(Config $config, array $server): void
    {
        if (PHP_SAPI === 'cli' || headers_sent()) {
            return;
        }

        if (!$config->bool('APP_FORCE_HTTPS', false) || self::isHttps($server)) {
            return;
        }

        $target = self::httpsRedirectUrl($config, $server);
        if ($target === null) {
            return;
        }

        header('Location: ' . $target, true, 301);
        exit;
    }

    public static function applyHeaders(Config $config, array $server): void
    {
        if (PHP_SAPI === 'cli' || headers_sent()) {
            return;
        }

        header('X-Content-Type-Options: nosniff');
        header('X-Frame-Options: SAMEORIGIN');
        header('Referrer-Policy: strict-origin-when-cross-origin');
        header('Permissions-Policy: camera=(), microphone=(), geolocation=()');

        if (!$config->bool('APP_HSTS_ENABLED', false) || !self::isHttps($server)) {
            return;
        }

        $maxAge = max(0, (int) $config->get('APP_HSTS_MAX_AGE', '31536000'));
        $hsts = 'max-age=' . $maxAge;

        if ($config->bool('APP_HSTS_INCLUDE_SUBDOMAINS', false)) {
            $hsts .= '; includeSubDomains';
        }

        if ($config->bool('APP_HSTS_PRELOAD', false)) {
            $hsts .= '; preload';
        }

        header('Strict-Transport-Security: ' . $hsts);
    }

    private static function httpsRedirectUrl(Config $config, array $server): ?string
    {
        $uri = (string) ($server['REQUEST_URI'] ?? '/');
        if ($uri === '' || $uri[0] !== '/') {
            $uri = '/' . ltrim($uri, '/');
        }

        $appUrl = trim((string) $config->get('APP_URL', ''));
        if (str_starts_with(strtolower($appUrl), 'https://')) {
            $parts = parse_url($appUrl);
            if (is_array($parts) && !empty($parts['host'])) {
                $port = isset($parts['port']) ? ':' . (string) $parts['port'] : '';
                return 'https://' . $parts['host'] . $port . $uri;
            }
        }

        $host = self::firstHeaderValue((string) ($server['HTTP_X_FORWARDED_HOST'] ?? ''));
        if ($host === '') {
            $host = (string) ($server['HTTP_HOST'] ?? $server['SERVER_NAME'] ?? '');
        }

        return $host === '' ? null : 'https://' . $host . $uri;
    }

    private static function firstHeaderValue(string $value): string
    {
        foreach (explode(',', $value) as $part) {
            $part = trim($part);
            if ($part !== '') {
                return $part;
            }
        }

        return '';
    }
}
