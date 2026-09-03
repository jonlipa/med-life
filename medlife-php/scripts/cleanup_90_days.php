<?php

declare(strict_types=1);

require __DIR__ . '/../bootstrap/init.php';

$db = app('db');
if (!$db instanceof \PDO || !db_available()) {
    fwrite(STDERR, 'Database unavailable: ' . (db_status()['message'] ?? 'Lidhja me databazen deshtoi.') . PHP_EOL);
    exit(1);
}

$sql = 'DELETE FROM audit_logs WHERE created_at < DATE_SUB(NOW(), INTERVAL 90 DAY)';
$count = $db->exec($sql);

echo "Deleted {$count} audit log records older than 90 days.\n";
