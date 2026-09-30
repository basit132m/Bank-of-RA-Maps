<?php

declare(strict_types=1);

namespace App\Core;

/** Read-only access to the current HTTP request. */
final class Request
{
    public function method(): string
    {
        return strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');
    }

    public function isPost(): bool
    {
        return $this->method() === 'POST';
    }

    /** Request path with the query string and trailing slash removed. */
    public function path(): string
    {
        $uri  = $_SERVER['REQUEST_URI'] ?? '/';
        $path = parse_url($uri, PHP_URL_PATH);
        $path = is_string($path) ? $path : '/';
        $path = '/' . trim(rawurldecode($path), '/');

        return $path === '/' ? '/' : rtrim($path, '/');
    }

    public function query(string $key, ?string $default = null): ?string
    {
        $value = $_GET[$key] ?? null;

        return is_string($value) ? $value : $default;
    }

    public function queryInt(string $key, int $default = 0): int
    {
        $value = $this->query($key);

        return $value !== null && $value !== '' && ctype_digit(ltrim($value, '-'))
            ? (int) $value
            : $default;
    }

    public function input(string $key, ?string $default = null): ?string
    {
        $value = $_POST[$key] ?? null;

        return is_string($value) ? trim($value) : $default;
    }

    /** @return array<int, string> */
    public function inputArray(string $key): array
    {
        $value = $_POST[$key] ?? [];

        if (! is_array($value)) {
            return [];
        }

        return array_values(array_filter($value, 'is_string'));
    }

    /** All query parameters, for rebuilding filter links. @return array<string, string> */
    public function queryAll(): array
    {
        return array_filter($_GET, 'is_string');
    }

    public function ip(): string
    {
        return (string) ($_SERVER['REMOTE_ADDR'] ?? '0.0.0.0');
    }

    /** Salted hash of the visitor IP — for counting and rate limiting without storing addresses. */
    public function ipHash(): string
    {
        return hash('sha256', (string) Config::get('security.app_key', '') . '|' . $this->ip());
    }
}
