<?php

declare(strict_types=1);

if (PHP_SAPI === 'cli-server') {
    $path = __DIR__ . parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
    if (is_file($path)) {
        return false;
    }
}

try {
    $router = require dirname(__DIR__) . '/bootstrap/app.php';
    $router->dispatch();
} catch (Throwable $exception) {
    if (!headers_sent()) {
        http_response_code(500);
    }

    $config = function_exists('app') ? app('config') : null;
    $debug = $config && method_exists($config, 'bool') && $config->bool('APP_DEBUG', false);
    $view = function_exists('app') ? app('view') : null;

    try {
        if ($view instanceof \App\Core\View) {
            $view->render('shared/error', [
                'title' => $debug ? 'Gabim: ' . get_class($exception) : 'Dicka shkoi gabim',
                'is_debug' => $debug,
                'error_message' => $debug ? $exception->getMessage() : '',
                'error_file' => $debug ? $exception->getFile() : '',
                'error_line' => $debug ? $exception->getLine() : '',
                'stack_trace' => $debug ? $exception->getTraceAsString() : '',
            ], 'layouts/public');
            exit;
        }
    } catch (Throwable) {
    }

    if (!headers_sent()) {
        header('Content-Type: text/plain; charset=UTF-8');
    }

    echo $debug
        ? 'Gabim: ' . get_class($exception) . PHP_EOL . $exception->getMessage()
        : 'Dicka shkoi gabim. Provoni perseri me vone.';
    exit;
}
