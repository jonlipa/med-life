<?php
$currentUser = app('auth')->user();
$currentRole = app('auth')->role() ?? 'admin';
$sidebarItems = dashboard_navigation()[$currentRole] ?? [];
$unreadNotifications = $currentUser ? app('clinical')->unreadCount((int) $currentUser['id']) : 0;
$displayName = (string) ($currentUser['full_name'] ?? 'User');
$nameParts = preg_split('/\s+/', trim($displayName)) ?: [];
$initials = '';

foreach (array_slice($nameParts, 0, 2) as $part) {
    $initials .= strtoupper(substr($part, 0, 1));
}

$initials = $initials !== '' ? $initials : 'ML';
$dashboardIcon = static function (string $path, string $label): string {
    $key = match (true) {
        str_ends_with($path, '/users') || str_contains($path, '/patients') => 'users',
        str_contains($path, '/records') => 'folder',
        str_contains($path, '/availability') || str_contains($path, '/appointments') => 'calendar',
        str_contains($path, '/reports') || str_contains($path, '/results') => 'chart',
        str_contains($path, '/audit') => 'activity',
        str_contains($path, '/settings') => 'settings',
        str_contains($path, '/profile') => 'profile',
        str_contains($path, '/intake') => 'user-plus',
        str_contains($path, '/queue') => 'clock',
        str_contains($path, '/billing') => 'card',
        str_contains($path, '/notifications') => 'bell',
        default => 'home',
    };

    $icons = [
        'home' => '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="m3 10 9-7 9 7"/><path d="M5 10v10h14V10"/><path d="M9 20v-6h6v6"/></svg>',
        'users' => '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M17 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9.5" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.8M16 3.2a4 4 0 0 1 0 7.6"/></svg>',
        'folder' => '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M3 6h7l2 2h9v11H3z"/><path d="M8 14h8"/></svg>',
        'calendar' => '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M7 3v4m10-4v4M4 8h16M5 5h14a1 1 0 0 1 1 1v14H4V6a1 1 0 0 1 1-1Zm4 7h6m-6 4h4"/></svg>',
        'chart' => '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 19V5"/><path d="M4 19h17"/><path d="M8 16v-5m5 5V8m5 8v-9"/></svg>',
        'activity' => '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 12h4l2-7 4 14 2-7h4"/></svg>',
        'settings' => '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 15.5a3.5 3.5 0 1 0 0-7 3.5 3.5 0 0 0 0 7Z"/><path d="M19.4 15a1.7 1.7 0 0 0 .3 1.8l.1.1-2 3.4-.2-.1a1.7 1.7 0 0 0-1.8.2l-.8.5a1.7 1.7 0 0 0-.8 1.5V22h-4v-.2a1.7 1.7 0 0 0-.8-1.5l-.8-.5a1.7 1.7 0 0 0-1.8-.2l-.2.1-2-3.4.1-.1a1.7 1.7 0 0 0 .3-1.8l-.3-.9a1.7 1.7 0 0 0-1.3-1.1H3v-4h.2a1.7 1.7 0 0 0 1.3-1.1l.3-.9a1.7 1.7 0 0 0-.3-1.8l-.1-.1 2-3.4.2.1a1.7 1.7 0 0 0 1.8-.2l.8-.5A1.7 1.7 0 0 0 10 2.2V2h4v.2a1.7 1.7 0 0 0 .8 1.5l.8.5a1.7 1.7 0 0 0 1.8.2l.2-.1 2 3.4-.1.1a1.7 1.7 0 0 0-.3 1.8l.3.9a1.7 1.7 0 0 0 1.3 1.1h.2v4h-.2a1.7 1.7 0 0 0-1.3 1.1Z"/></svg>',
        'profile' => '<svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="8" r="4"/><path d="M4 21a8 8 0 0 1 16 0"/></svg>',
        'user-plus' => '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M15 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="8.5" cy="7" r="4"/><path d="M19 8v6m3-3h-6"/></svg>',
        'clock' => '<svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="12" r="9"/><path d="M12 7v6l4 2"/></svg>',
        'card' => '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 6h16v12H4z"/><path d="M4 10h16"/><path d="M8 15h3"/></svg>',
        'bell' => '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M18 8a6 6 0 0 0-12 0c0 7-3 7-3 9h18c0-2-3-2-3-9"/><path d="M10 21h4"/></svg>',
    ];

    return $icons[$key];
};
?>
<!DOCTYPE html>
<html lang="sq">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($title ?? config('APP_NAME', 'Med Life')); ?></title>
    <link rel="stylesheet" href="<?= e(asset('css/app.css')); ?>">
</head>
<body class="dashboard-body role-<?= e($currentRole); ?>">
    <div class="dashboard-scene">
        <div class="dashboard-app-frame">
            <div class="dashboard-frame-topbar">
                <div class="window-dots">
                    <span></span>
                    <span></span>
                    <span></span>
                </div>
                <div class="frame-title"><?= e($pageTitle ?? 'Med Life Portal'); ?></div>
                <div class="frame-tools">
                    <span class="top-icon"></span>
                    <span class="top-icon"></span>
                    <span class="top-icon"></span>
                </div>
            </div>

            <div class="dashboard-shell">
                <aside class="sidebar">
                    <div class="sidebar-brand">
                        <a class="brand" href="<?= e(role_home($currentRole)); ?>">
                            <span class="brand-mark">
                                <svg viewBox="0 0 24 24" aria-hidden="true">
                                    <path d="M22 12h-4l-3 8L9 4l-3 8H2"></path>
                                </svg>
                            </span>
                            <span>Med Life</span>
                        </a>
                        <p class="sidebar-caption"><?= e(ucfirst($currentRole)); ?> workspace</p>
                    </div>

                    <nav class="sidebar-nav">
                        <?php foreach ($sidebarItems as $item): ?>
                            <a class="<?= is_active($item['path'], $item['path'] === role_home($currentRole)) ? 'sidebar-link is-active' : 'sidebar-link'; ?>" href="<?= e($item['path']); ?>">
                                <span class="sidebar-icon"><?= $dashboardIcon($item['path'], $item['label']); ?></span>
                                <span><?= e($item['label']); ?></span>
                            </a>
                        <?php endforeach; ?>
                    </nav>

                    <div class="sidebar-support">
                        <p class="sidebar-support-label">Status</p>
                        <div class="sidebar-support-card">
                            <strong><?= e((string) $unreadNotifications); ?></strong>
                            <span>Njoftime aktive</span>
                        </div>
                    </div>
                </aside>

                <div class="dashboard-main">
                    <header class="dashboard-topbar">
                        <div class="dashboard-topbar-copy">
                            <p class="eyebrow">Portal</p>
                            <h1><?= e($pageTitle ?? 'Med Life Portal'); ?></h1>
                        </div>
                        <div class="topbar-right">
                            <div class="utility-pill">Roli: <?= e(ucfirst($currentRole)); ?></div>
                            <div class="utility-pill">Njoftime: <?= e((string) $unreadNotifications); ?></div>
                            <div class="user-box user-box-compact">
                                <div class="avatar-chip"><?= e($initials); ?></div>
                                <div>
                                    <strong><?= e($displayName); ?></strong>
                                    <span><?= e($currentUser['title'] ?? ucfirst($currentRole)); ?></span>
                                </div>
                            </div>
                            <form action="/logout" method="post">
                                <?= csrf_field(); ?>
                                <button class="button button-secondary" type="submit">Dil</button>
                            </form>
                        </div>
                    </header>

                    <main class="dashboard-content">
                        <?php require base_path('app/Views/partials/flash.php'); ?>
                        <?= $content; ?>
                    </main>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
