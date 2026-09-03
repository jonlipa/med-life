<?php

declare(strict_types=1);

use App\Core\App;
use App\Core\Csrf;
use App\Core\Session;
use App\Support\Validator;

function validate(array $rules, array $data): array
{
    return Validator::validate($rules, $data);
}

function base_path(string $path = ''): string
{
    $base = defined('ROOT_PATH') ? ROOT_PATH : dirname(__DIR__, 2);
    if ($path === '') {
        return $base;
    }

    return $base . DIRECTORY_SEPARATOR . str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $path);
}

function app(?string $key = null, mixed $default = null): mixed
{
    if ($key === null) {
        return App::all();
    }

    return App::get($key, $default);
}

function db_status(): array
{
    return app('db_status', [
        'available' => false,
        'message' => 'Databaza nuk eshte inicializuar ende.',
        'host' => '127.0.0.1',
        'port' => '3306',
        'name' => 'medlife',
        'user' => 'root',
    ]);
}

function db_available(): bool
{
    return (bool) (db_status()['available'] ?? false);
}

function setup_mode(): bool
{
    return !db_available();
}

function setup_view_data(array $overrides = []): array
{
    $status = db_status();
    $request = app('request');

    $data = [
        'title' => 'Databaza nuk eshte gati',
        'pageTitle' => 'Databaza nuk eshte gati',
        'db_error' => (string) ($status['message'] ?? 'Lidhja me databazen deshtoi.'),
        'db_host' => (string) ($status['host'] ?? config('DB_HOST', '127.0.0.1')),
        'db_port' => (string) ($status['port'] ?? config('DB_PORT', '3306')),
        'db_name' => (string) ($status['name'] ?? config('DB_NAME', 'medlife')),
        'db_user' => (string) ($status['user'] ?? config('DB_USER', 'root')),
        'setup_message' => 'Databaza lokale nuk eshte ende gati. Faqet publike funksionojne me fallback data, por login-i, dashboards dhe ruajtja e te dhenave kerkojne MySQL aktive.',
        'request_path' => $request ? $request->path() : '/',
        'request_method' => $request ? $request->method() : 'GET',
    ];

    return array_merge($data, $overrides);
}

function config(string $key, mixed $default = null): mixed
{
    $config = App::get('config');

    return $config ? $config->get($key, $default) : $default;
}

function e(mixed $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function url(string $path = '/'): string
{
    $base = rtrim((string) config('APP_URL', ''), '/');
    $path = '/' . ltrim($path, '/');

    return $base === '' ? $path : $base . $path;
}

function asset(string $path): string
{
    $base = rtrim((string) config('APP_ASSET_BASE', ''), '/');

    return ($base === '' ? '' : $base) . '/assets/' . ltrim($path, '/');
}

function current_path(): string
{
    $request = App::get('request');
    if (!$request) {
        return '/';
    }

    return $request->path();
}

function is_active(string $path, bool $exact = false): bool
{
    $current = current_path();
    if ($exact) {
        return $current === $path;
    }

    if ($path === '/') {
        return $current === '/';
    }

    return str_starts_with($current, $path);
}

function csrf_token(): string
{
    return Csrf::token();
}

function csrf_field(): string
{
    return '<input type="hidden" name="_token" value="' . e(csrf_token()) . '">';
}

function flash(string $key, mixed $default = null): mixed
{
    return Session::get($key, $default);
}

function old(string $key, mixed $default = ''): mixed
{
    $oldInput = Session::get('old_input', []);

    return $oldInput[$key] ?? $default;
}

function money(float|int|string|null $amount): string
{
    return 'EUR ' . number_format((float) $amount, 2, '.', ',');
}

function format_date(?string $value, bool $includeTime = true): string
{
    if (!$value) {
        return 'N/A';
    }

    try {
        $date = new DateTimeImmutable($value);
    } catch (Throwable) {
        return $value;
    }

    return $date->format($includeTime ? 'd M Y, H:i' : 'd M Y');
}

function status_class(string $status): string
{
    $status = strtolower(trim($status));

    return match ($status) {
        'active', 'aktive', 'scheduled', 'confirmed', 'paid', 'completed', 'gati', 'read' => 'badge badge-success',
        'pending', 'requested', 'new', 'in_progress', 'ne pritje', 'aktual', 'draft' => 'badge badge-warning',
        'overdue', 'cancelled', 'high', 'urgent', 'error' => 'badge badge-danger',
        'admin', 'doctor', 'reception', 'patient' => 'badge badge-info',
        default => 'badge badge-info',
    };
}

function severity_class(string $severity): string
{
    return match (strtolower($severity)) {
        'high', 'critical' => 'badge badge-danger',
        'medium', 'warning' => 'badge badge-warning',
        default => 'badge badge-info',
    };
}

function public_navigation(): array
{
    return [
        ['label' => 'Ballina', 'path' => '/'],
        ['label' => 'Shërbimet', 'path' => '/services'],
        ['label' => 'Doktorët', 'path' => '/doctors'],
        ['label' => 'Rreth Nesh', 'path' => '/about'],
        ['label' => 'Kontakt', 'path' => '/contact'],
    ];
}

function dashboard_navigation(): array
{
    return [
        'admin' => [
            ['label' => 'Dashboard', 'path' => '/admin'],
            ['label' => 'Perdoruesit', 'path' => '/admin/users'],
            ['label' => 'Raportet', 'path' => '/admin/reports'],
            ['label' => 'Audit', 'path' => '/admin/audit'],
            ['label' => 'Settings', 'path' => '/admin/settings'],
            ['label' => 'Profili', 'path' => '/profile'],
        ],
        'doctor' => [
            ['label' => 'Dashboard', 'path' => '/doctor'],
            ['label' => 'Pacientet', 'path' => '/doctor/patients'],
            ['label' => 'Kartelat', 'path' => '/doctor/records'],
            ['label' => 'Disponueshmeria', 'path' => '/doctor/availability'],
            ['label' => 'Raportet', 'path' => '/reports'],
            ['label' => 'Settings', 'path' => '/settings'],
            ['label' => 'Profili', 'path' => '/profile'],
        ],
        'reception' => [
            ['label' => 'Dashboard', 'path' => '/reception'],
            ['label' => 'Intake', 'path' => '/reception/intake'],
            ['label' => 'Lista e Pritjes', 'path' => '/reception/queue'],
            ['label' => 'Terminet', 'path' => '/reception/appointments'],
            ['label' => 'Settings', 'path' => '/settings'],
            ['label' => 'Profili', 'path' => '/profile'],
        ],
        'patient' => [
            ['label' => 'Dashboard', 'path' => '/patient'],
            ['label' => 'Terminet', 'path' => '/patient/appointments'],
            ['label' => 'Rezultatet', 'path' => '/patient/results'],
            ['label' => 'Faturat', 'path' => '/patient/billing'],
            ['label' => 'Njoftimet', 'path' => '/patient/notifications'],
            ['label' => 'Settings', 'path' => '/settings'],
            ['label' => 'Profili', 'path' => '/profile'],
        ],
    ];
}

function role_home(string $role): string
{
    return match ($role) {
        'admin' => '/admin',
        'doctor' => '/doctor',
        'reception' => '/reception',
        'patient' => '/patient',
        default => '/',
    };
}

function paginate(int $totalItems, int $perPage = 15): \App\Support\Paginator
{
    return \App\Support\Paginator::fromRequest($totalItems, $perPage);
}
