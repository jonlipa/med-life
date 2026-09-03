<?php

declare(strict_types=1);

use App\Core\App;
use App\Core\Request;
use App\Core\Router;

require_once __DIR__ . '/init.php';

$request = Request::capture();
$router = new Router($request);

App::set('request', $request);
App::set('router', $router);

require ROOT_PATH . '/routes/web.php';

if (PHP_SAPI !== 'cli') {
    $lockFile = ROOT_PATH . '/storage/logs/.last_cleanup';
    $shouldRun = true;

    if (is_file($lockFile)) {
        $lastRun = (int) file_get_contents($lockFile);
        if ((time() - $lastRun) < 86400) {
            $shouldRun = false;
        }
    }

    if ($shouldRun && isset($db)) {
        try {
            @$db->exec('DELETE FROM audit_logs WHERE created_at < DATE_SUB(NOW(), INTERVAL 90 DAY)');
            file_put_contents($lockFile, (string) time());
        } catch (Throwable) {
        }
    }
}

return $router;
