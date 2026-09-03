<?php

declare(strict_types=1);

namespace App\Core;

final class Response
{
    public static function view(string $template, array $data = [], ?string $layout = 'layouts/public', int $status = 200): void
    {
        http_response_code($status);
        /** @var View $view */
        $view = App::get('view');
        $view->render($template, $data, $layout);
    }

    public static function redirect(string $path, int $status = 302): never
    {
        header('Location: ' . $path, true, $status);
        exit;
    }

    public static function abort(int $status, string $template = 'shared/not_found', array $data = [], ?string $layout = 'layouts/public'): void
    {
        self::view($template, $data, $layout, $status);
    }

    public static function setupRequired(
        string $message = 'Ky veprim kerkon databazen aktive.',
        int $status = 503,
        ?string $title = null
    ): never {
        self::view(
            'shared/database_unavailable',
            setup_view_data([
                'title' => $title ?? 'Databaza nuk eshte gati',
                'pageTitle' => $title ?? 'Databaza nuk eshte gati',
                'setup_message' => $message,
            ]),
            'layouts/public',
            $status
        );

        exit;
    }
}
