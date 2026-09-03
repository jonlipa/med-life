<?php

declare(strict_types=1);

namespace App\Support;

final class Totp
{
    private const BASE32_ALPHABET = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';

    public static function generateSecret(int $length = 32): string
    {
        $length = max(16, $length);
        $secret = '';

        for ($i = 0; $i < $length; $i++) {
            $secret .= self::BASE32_ALPHABET[random_int(0, 31)];
        }

        return $secret;
    }

    public static function provisioningUri(string $issuer, string $accountName, string $secret): string
    {
        $issuer = trim($issuer) !== '' ? trim($issuer) : 'Med Life';
        $accountName = trim($accountName) !== '' ? trim($accountName) : 'user';
        $secret = strtoupper(trim($secret));

        $label = rawurlencode($issuer . ':' . $accountName);
        $params = http_build_query([
            'secret' => $secret,
            'issuer' => $issuer,
            'algorithm' => 'SHA1',
            'digits' => 6,
            'period' => 30,
        ]);

        return 'otpauth://totp/' . $label . '?' . $params;
    }

    public static function verifyCode(string $secret, string $code, int $window = 1, ?int $timestamp = null): bool
    {
        $code = preg_replace('/\s+/', '', $code) ?? '';
        if (!preg_match('/^\d{6}$/', $code)) {
            return false;
        }

        $secretBinary = self::decodeBase32($secret);
        if ($secretBinary === null) {
            return false;
        }

        $timestamp = $timestamp ?? time();
        $counter = intdiv($timestamp, 30);
        $window = max(0, $window);

        for ($offset = -$window; $offset <= $window; $offset++) {
            $candidate = self::generateCode($secretBinary, $counter + $offset);
            if (hash_equals($candidate, $code)) {
                return true;
            }
        }

        return false;
    }

    private static function generateCode(string $secretBinary, int $counter): string
    {
        if ($counter < 0) {
            return '000000';
        }

        $binaryCounter = pack('N2', 0, $counter);
        $hash = hash_hmac('sha1', $binaryCounter, $secretBinary, true);
        $offset = ord(substr($hash, -1)) & 0x0F;

        $truncated = (
            ((ord($hash[$offset]) & 0x7F) << 24)
            | ((ord($hash[$offset + 1]) & 0xFF) << 16)
            | ((ord($hash[$offset + 2]) & 0xFF) << 8)
            | (ord($hash[$offset + 3]) & 0xFF)
        );

        return str_pad((string) ($truncated % 1000000), 6, '0', STR_PAD_LEFT);
    }

    private static function decodeBase32(string $value): ?string
    {
        $value = strtoupper(trim($value));
        $value = str_replace(['=', ' '], '', $value);

        if ($value === '') {
            return null;
        }

        $bits = '';
        $length = strlen($value);

        for ($i = 0; $i < $length; $i++) {
            $char = $value[$i];
            $index = strpos(self::BASE32_ALPHABET, $char);

            if ($index === false) {
                return null;
            }

            $bits .= str_pad(decbin($index), 5, '0', STR_PAD_LEFT);
        }

        $binary = '';
        $bitLength = strlen($bits);
        $fullBytes = intdiv($bitLength, 8);

        for ($i = 0; $i < $fullBytes; $i++) {
            $chunk = substr($bits, $i * 8, 8);
            $binary .= chr(bindec($chunk));
        }

        return $binary === '' ? null : $binary;
    }
}
