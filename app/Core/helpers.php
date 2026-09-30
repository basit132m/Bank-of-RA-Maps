<?php

declare(strict_types=1);

use App\Core\Config;

/**
 * Escape a value for HTML output. Every dynamic value in a view goes through
 * this — there are no exceptions.
 */
function e(mixed $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

/** Build an absolute site URL from a root-relative path. */
function url(string $path = '/'): string
{
    $base = rtrim((string) Config::get('app.url', ''), '/');

    return $base . '/' . ltrim($path, '/');
}

/** Root-relative path to a static asset, with a cache-busting version stamp. */
function asset(string $path): string
{
    $path = '/assets/' . ltrim($path, '/');
    $file = BASE_PATH . '/public_html' . $path;
    $stamp = is_file($file) ? (string) filemtime($file) : '1';

    return $path . '?v=' . $stamp;
}

/** Turn arbitrary text into a URL-safe slug. */
function slugify(string $text): string
{
    $text = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $text) ?: $text;
    $text = strtolower(trim($text));
    $text = preg_replace('/[^a-z0-9]+/', '-', $text) ?? '';

    return trim($text, '-');
}

/** Human-readable file size. */
function format_bytes(int $bytes): string
{
    if ($bytes < 1024) {
        return $bytes . ' B';
    }
    if ($bytes < 1024 * 1024) {
        return round($bytes / 1024, 1) . ' KB';
    }

    return round($bytes / (1024 * 1024), 1) . ' MB';
}

/** Thousands-separated integer, e.g. 12400 -> "12,400". */
function format_count(int|string|null $number): string
{
    return number_format((int) $number);
}

/** Format a stored datetime for display. Returns an empty string if absent. */
function format_date(?string $datetime, string $format = 'j M Y'): string
{
    if ($datetime === null || $datetime === '') {
        return '';
    }
    $timestamp = strtotime($datetime);

    return $timestamp === false ? '' : date($format, $timestamp);
}

/** Public URL for a map preview image, falling back to a placeholder. */
function preview_url(?string $file): string
{
    if ($file === null || trim($file) === '') {
        return asset('img/map-placeholder.svg');
    }

    return '/uploads/previews/' . basename(trim($file));
}

/**
 * Build a URL with a query string, dropping empty values.
 *
 * @param array<string, string|int|null> $params
 */
function query_url(string $path, array $params = []): string
{
    $params = array_filter(
        $params,
        static fn (mixed $value): bool => $value !== null && $value !== '' && $value !== 0
    );

    return $params === [] ? $path : $path . '?' . http_build_query($params);
}

/** Truncate text on a word boundary for card summaries. */
function excerpt(?string $text, int $limit = 160): string
{
    $text = trim(preg_replace('/\s+/', ' ', (string) $text) ?? '');

    if ($text === '' || mb_strlen($text) <= $limit) {
        return $text;
    }

    $cut = mb_substr($text, 0, $limit);
    $lastSpace = mb_strrpos($cut, ' ');

    return rtrim($lastSpace !== false ? mb_substr($cut, 0, $lastSpace) : $cut, ',.;:') . '…';
}

/**
 * Render plain text as escaped HTML paragraphs. Blank lines start a new
 * paragraph, single newlines become line breaks. Used for designer notes and
 * other author-entered prose — the input is never treated as HTML.
 */
function paragraphs(?string $text): string
{
    $text = trim(str_replace(["\r\n", "\r"], "\n", (string) $text));

    if ($text === '') {
        return '';
    }

    $blocks = preg_split('/\n{2,}/', $text) ?: [];

    $html = '';
    foreach ($blocks as $block) {
        $html .= '<p>' . nl2br(e(trim($block)), false) . '</p>';
    }

    return $html;
}
