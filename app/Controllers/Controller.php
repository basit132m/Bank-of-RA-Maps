<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Request;
use App\Core\View;

/** Shared behaviour for every controller. */
abstract class Controller
{
    public function __construct(protected Request $request)
    {
    }

    /** @param array<string, mixed> $data */
    protected function view(string $template, array $data = [], ?string $layout = 'layouts/main'): string
    {
        return View::render($template, $data, $layout);
    }
}
