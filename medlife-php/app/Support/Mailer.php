<?php

declare(strict_types=1);

namespace App\Support;

final class Mailer
{
    public static function sendPlainText(string $to, string $subject, string $body): bool
    {
        $to = trim($to);
        if ($to === '' || !filter_var($to, FILTER_VALIDATE_EMAIL)) {
            return false;
        }

        if (!function_exists('mail')) {
            return false;
        }

        $fromAddress = trim((string) config('MAIL_FROM_ADDRESS', 'noreply@medlife.local'));
        $fromName = trim((string) config('MAIL_FROM_NAME', (string) config('APP_NAME', 'Med Life')));
        $fromHeader = self::formatFromHeader($fromAddress, $fromName);
        $replyTo = self::sanitizeAddress($fromAddress);
        $subject = str_replace(["\r", "\n"], '', $subject);

        $headers = [
            'MIME-Version: 1.0',
            'Content-Type: text/plain; charset=UTF-8',
            'From: ' . $fromHeader,
            'Reply-To: ' . $replyTo,
            'X-Mailer: PHP/' . PHP_VERSION,
        ];

        return @mail($to, $subject, $body, implode("\r\n", $headers));
    }

    public static function sendLoginOtp(string $email, string $fullName, string $code): bool
    {
        $appName = (string) config('APP_NAME', 'Med Life');
        $subject = (string) config('MAIL_LOGIN_OTP_SUBJECT', $appName . ' - Kodi i verifikimit');
        $name = trim($fullName) !== '' ? trim($fullName) : 'Perdorues';

        $body = implode("\n", [
            'Pershendetje ' . $name . ',',
            '',
            'Kodi juaj i verifikimit per hyrje eshte: ' . $code,
            'Ky kod skadon pas 10 minutash.',
            '',
            'Nese nuk e keni kerkuar ju kete hyrje, injoroni kete email.',
            '',
            'Med Life Portal',
        ]);

        return self::sendPlainText($email, $subject, $body);
    }

    public static function sendPasswordReset(string $email, string $fullName, string $resetUrl): bool
    {
        $appName = (string) config('APP_NAME', 'Med Life');
        $subject = $appName . ' - Reset i fjalkalimit';
        $name = trim($fullName) !== '' ? trim($fullName) : 'Perdorues';

        $body = implode("\n", [
            'Pershendetje ' . $name . ',',
            '',
            'U regjistrua nje kerkese per reset te fjalkalimit ne ' . $appName . '.',
            'Hapni linkun me poshte per te vendosur fjalkalimin e ri:',
            $resetUrl,
            '',
            'Ky link skadon pas 30 minutash.',
            'Nese nuk e keni kerkuar ju kete ndryshim, injoroni kete email.',
            '',
            'Med Life Portal',
        ]);

        return self::sendPlainText($email, $subject, $body);
    }

    private static function formatFromHeader(string $address, string $name): string
    {
        $address = self::sanitizeAddress($address);
        $name = trim($name);

        if ($name === '') {
            return $address;
        }

        $safeName = str_replace(['"', "\r", "\n"], '', $name);

        return sprintf('"%s" <%s>', $safeName, $address);
    }

    private static function sanitizeAddress(string $address): string
    {
        $address = str_replace(["\r", "\n"], '', trim($address));

        return filter_var($address, FILTER_VALIDATE_EMAIL) ? $address : 'noreply@medlife.local';
    }
}
