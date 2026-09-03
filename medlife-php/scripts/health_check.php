<?php

declare(strict_types=1);

require dirname(__DIR__) . '/bootstrap/init.php';

$errors = 0;
$warnings = 0;

$print = static function (string $level, string $message): void {
    fwrite(STDOUT, sprintf('[%s] %s%s', $level, $message, PHP_EOL));
};

$configChecks = [
    'APP_NAME',
    'APP_URL',
    'DB_HOST',
    'DB_PORT',
    'DB_NAME',
    'DB_USER',
    'SESSION_COOKIE_NAME',
];

$print('INFO', 'Med Life health check');
$print('INFO', 'App root: ' . dirname(__DIR__));

$missingConfig = [];
foreach ($configChecks as $key) {
    $value = config($key, '');
    if (trim((string) $value) === '') {
        $missingConfig[] = $key;
    }
}

if ($missingConfig === []) {
    $print('OK', 'Konfigurimi minimal eshte i plotesuar.');
} else {
    $errors++;
    $print('ERROR', 'Mungojne vlerat e konfigurimit: ' . implode(', ', $missingConfig));
}

$appUrl = strtolower((string) config('APP_URL', ''));
$forceHttps = filter_var((string) config('APP_FORCE_HTTPS', 'false'), FILTER_VALIDATE_BOOL);
$hstsEnabled = filter_var((string) config('APP_HSTS_ENABLED', 'false'), FILTER_VALIDATE_BOOL);
$secureCookie = strtolower(trim((string) config('SESSION_COOKIE_SECURE', 'false')));

if ($forceHttps && !str_starts_with($appUrl, 'https://')) {
    $warnings++;
    $print('WARN', 'APP_FORCE_HTTPS=true, por APP_URL nuk fillon me https://.');
}

if (str_starts_with($appUrl, 'https://') && $secureCookie === 'false') {
    $warnings++;
    $print('WARN', 'APP_URL eshte HTTPS, por SESSION_COOKIE_SECURE=false.');
}

if ($hstsEnabled && !$forceHttps) {
    $warnings++;
    $print('WARN', 'APP_HSTS_ENABLED=true pa APP_FORCE_HTTPS=true; aktivizoje vetem ne HTTPS production.');
}

if (is_file(base_path('.env'))) {
    $print('OK', 'U gjet .env lokal.');
} elseif (is_file(base_path('.env.example'))) {
    $warnings++;
    $print('WARN', '.env mungon; po perdoren defaults nga .env.example.');
} else {
    $errors++;
    $print('ERROR', 'As .env dhe as .env.example nuk u gjeten.');
}

if (in_array('mysql', \PDO::getAvailableDrivers(), true)) {
    $print('OK', 'PDO MySQL driver eshte i disponueshem.');
} else {
    $errors++;
    $print('ERROR', 'PDO MySQL driver mungon ne runtime-in aktiv.');
}

$storagePath = base_path('storage/logs');
if (is_dir($storagePath) && is_writable($storagePath)) {
    $print('OK', 'storage/logs eshte writable.');
} else {
    $errors++;
    $print('ERROR', 'storage/logs mungon ose nuk eshte writable.');
}

$rateLimitPath = base_path('storage/ratelimits');
if (!is_dir($rateLimitPath)) {
    @mkdir($rateLimitPath, 0775, true);
}

if (is_dir($rateLimitPath) && is_writable($rateLimitPath)) {
    $print('OK', 'storage/ratelimits eshte writable.');
} else {
    $errors++;
    $print('ERROR', 'storage/ratelimits mungon ose nuk eshte writable.');
}

if (!db_available()) {
    $warnings++;
    $print('WARN', 'Databaza nuk eshte gati: ' . (db_status()['message'] ?? 'Lidhja deshtoi.'));
} else {
    $db = app('db');

    if (!$db instanceof \PDO) {
        $errors++;
        $print('ERROR', 'db_available() eshte true, por objekti PDO mungon.');
    } else {
        $print('OK', 'Lidhja me databazen eshte aktive.');

        $migrationTableExists = (bool) $db->query("SHOW TABLES LIKE 'schema_migrations'")?->fetchColumn();
        if (!$migrationTableExists) {
            $warnings++;
            $print('WARN', 'Tabela schema_migrations mungon. Ekzekuto migrate.cmd.');
        } else {
            $count = (int) $db->query('SELECT COUNT(*) FROM schema_migrations')->fetchColumn();
            $latest = $db->query('SELECT migration FROM schema_migrations ORDER BY applied_at DESC, migration DESC LIMIT 1')->fetchColumn();
            $print('OK', 'Migrimet e aplikuara: ' . $count . ($latest ? ' | latest: ' . $latest : ''));
        }
    }
}

if ($errors > 0) {
    exit(1);
}

if ($warnings > 0) {
    exit(2);
}

exit(0);
