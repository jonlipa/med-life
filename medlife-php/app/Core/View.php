<?php

declare(strict_types=1);

namespace App\Core;

use RuntimeException;

final class View
{
    public function __construct(private string $viewPath)
    {
    }

    public function render(string $template, array $data = [], ?string $layout = 'layouts/public'): void
    {
        $templateFile = $this->viewPath . '/' . $template . '.php';
        if (!is_file($templateFile)) {
            throw new RuntimeException("View [{$template}] not found.");
        }

        extract($data, EXTR_SKIP);

        ob_start();
        require $templateFile;
        $content = (string) ob_get_clean();

        if ($layout === null) {
            echo $content;
            return;
        }

        $layoutFile = $this->viewPath . '/' . $layout . '.php';
        if (!is_file($layoutFile)) {
            throw new RuntimeException("Layout [{$layout}] not found.");
        }

        require $layoutFile;
    }
}
