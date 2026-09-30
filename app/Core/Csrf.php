<?php

declare(strict_types=1);

namespace App\Core;

/**
 * Per-session CSRF token. Every state-changing form includes Csrf::field() and
 * every POST handler calls Csrf::verify() before touching data.
 */
final class Csrf
{
    private const KEY = '_csrf_token';

    public static function token(): string
    {
        if (empty($_SESSION[self::KEY]) || ! is_string($_SESSION[self::KEY])) {
            $_SESSION[self::KEY] = bin2hex(random_bytes(32));
        }

        return $_SESSION[self::KEY];
    }

    /** Hidden input to drop into a form. */
    public static function field(): string
    {
        return '<input type="hidden" name="' . self::KEY . '" value="' . e(self::token()) . '">';
    }

    public static function isValid(?string $submitted): bool
    {
        $expected = $_SESSION[self::KEY] ?? null;

        if (! is_string($expected) || ! is_string($submitted) || $submitted === '') {
            return false;
        }

        return hash_equals($expected, $submitted);
    }

    /** Abort the request with 419 when the submitted token is missing or wrong. */
    public static function verify(Request $request): void
    {
        if (self::isValid($request->input(self::KEY))) {
            return;
        }

        http_response_code(419);
        echo View::render('errors/419', ['title' => 'Session expired']);
        exit;
    }
}
