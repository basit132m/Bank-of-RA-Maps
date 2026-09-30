<?php

declare(strict_types=1);

namespace App\Core;

use RuntimeException;
use Throwable;

/**
 * Plain-PHP templates. Views live in app/Views and are rendered inside a
 * layout, which receives the view output as $content.
 */
final class View
{
    /**
     * @param  array<string, mixed>  $data
     * @param  string|null  $layout  Layout template, or null to render the view bare
     */
    public static function render(string $template, array $data = [], ?string $layout = 'layouts/main'): string
    {
        $content = self::capture($template, $data);

        if ($layout === null) {
            return $content;
        }

        return self::capture($layout, $data + ['content' => $content]);
    }

    /** Render a partial for inclusion inside another view. @param array<string, mixed> $data */
    public static function partial(string $template, array $data = []): string
    {
        return self::capture($template, $data);
    }

    /** @param array<string, mixed> $data */
    private static function capture(string $template, array $data): string
    {
        $file = BASE_PATH . '/app/Views/' . $template . '.php';

        if (! is_file($file)) {
            throw new RuntimeException('View not found: ' . $template);
        }

        ob_start();

        try {
            (static function (string $__file, array $__data): void {
                extract($__data, EXTR_SKIP);
                require $__file;
            })($file, $data);
        } catch (Throwable $e) {
            ob_end_clean();
            throw $e;
        }

        return (string) ob_get_clean();
    }
}
