<?php

/**
 * Local preview and end-to-end test rig for the map tweaker.
 *
 *   php -S 127.0.0.1:8015 -t .
 *
 *   GET  /tools/tweaker-preview/              the form
 *   GET  /tools/tweaker-preview/?byrm_err=big an error state
 *   POST /tools/tweaker-preview/              runs the real upload handler
 *
 * The handler, the validation and the INI writer are the real ones; only
 * WordPress is faked. Development tool only.
 */

declare(strict_types=1);

define('ABSPATH', dirname(__DIR__, 2) . '/');
const THEME_URI = '/wp-content/themes/astra-child';
define('HOUR_IN_SECONDS', 3600);
define('MINUTE_IN_SECONDS', 60);
define('MB_IN_BYTES', 1048576);

/* ---------------------------------------------------------------- escaping */

function esc_url(string $u): string { return htmlspecialchars($u, ENT_QUOTES, 'UTF-8'); }
function esc_attr($t): string { return htmlspecialchars((string) $t, ENT_QUOTES, 'UTF-8'); }
function esc_html($t): string { return htmlspecialchars((string) $t, ENT_QUOTES, 'UTF-8'); }
function esc_html_e(string $t, string $d = ''): void { echo esc_html($t); }
function esc_attr_e(string $t, string $d = ''): void { echo esc_attr($t); }
function esc_html__(string $t, string $d = ''): string { return esc_html($t); }
function __(string $t, string $d = ''): string { return $t; }
function _n(string $s, string $p, int $n, string $d = ''): string { return $n === 1 ? $s : $p; }
function sanitize_key($k): string { return preg_replace('/[^a-z0-9_\-]/', '', strtolower((string) $k)) ?? ''; }
function sanitize_text_field($t): string { return trim(strip_tags((string) $t)); }
function sanitize_file_name(string $n): string { return preg_replace('/[^A-Za-z0-9._-]/', '-', $n) ?? 'map'; }
function wp_unslash($v) { return is_array($v) ? array_map('wp_unslash', $v) : stripslashes((string) $v); }
function wp_strip_all_tags($t, bool $b = false): string { return trim(strip_tags((string) $t)); }
function number_format_i18n($n): string { return number_format((int) $n); }
function size_format($bytes, $decimals = 0): string { return round($bytes / MB_IN_BYTES, $decimals) . ' MB'; }

/* ------------------------------------------------------------- environment */

function add_action(...$a): void {}
function add_filter(...$a): void {}
function apply_filters(string $h, $v, ...$r)
{
    if ($h === 'byrm_header_config' && is_array($v)) { $v['discord_url'] = 'https://discord.gg/example'; }
    if ($h === 'byrm_footer_config' && is_array($v)) { $v['discord_url'] = 'https://discord.gg/example'; }
    return $v;
}
function did_action(string $h): int { return 1; }
function home_url(string $p = '/'): string { return '/' . ltrim($p, '/'); }
function get_stylesheet_directory_uri(): string { return THEME_URI; }
function get_bloginfo(string $s = '', string $f = ''): string { return $s === 'charset' ? 'UTF-8' : 'Bank of YR Maps'; }
function get_search_query(): string { return ''; }
function has_custom_logo(): bool { return false; }
function the_custom_logo(): void {}
function has_nav_menu(string $l): bool { return false; }
function wp_nav_menu(array $a = []): void {}
function is_singular(string $t = ''): bool { return true; }
function is_front_page(): bool { return false; }
function is_search(): bool { return false; }
function is_page($p = ''): bool { return true; }
function is_wp_error($t): bool { return false; }
function post_type_exists(string $t): bool { return true; }
function wp_count_posts(string $t) { return (object) ['publish' => 7]; }
function get_post_type_archive_link(string $t) { return '/maps/'; }
function wp_salt(string $s = ''): string { return 'preview-salt'; }
function add_query_arg($k, $v = null, $url = '') { return $url . '?' . $k . '=' . rawurlencode((string) $v); }
function wp_nonce_field(string $a, string $n): void { echo '<input type="hidden" name="' . esc_attr($n) . '" value="preview-nonce">'; }
function wp_verify_nonce($n, $a) { return $n === 'preview-nonce' ? 1 : false; }
function nocache_headers(): void {}

// The rate limiter, backed by a file so repeated requests actually accumulate.
function byrm_preview_store(): string { return sys_get_temp_dir() . '/byrm-tweak-rate.json'; }
function get_transient(string $k)
{
    $all = json_decode((string) @file_get_contents(byrm_preview_store()), true) ?: [];
    return $all[$k] ?? false;
}
function set_transient(string $k, $v, int $t = 0): bool
{
    $all = json_decode((string) @file_get_contents(byrm_preview_store()), true) ?: [];
    $all[$k] = $v;
    file_put_contents(byrm_preview_store(), json_encode($all));
    return true;
}

function wp_safe_redirect(string $url, int $status = 302): void
{
    header('Location: ' . $url, true, $status);
}

/* --------------------------------------------------------------- the page */

$GLOBALS['byrm_loop'] = true;
function have_posts(): bool { return $GLOBALS['byrm_loop']; }
function the_post(): void { $GLOBALS['byrm_loop'] = false; }
function get_the_ID(): int { return 55; }
function the_title(): void { echo esc_html('Map editor'); }
function get_the_title($p = null): string { return 'Map editor'; }
function get_permalink($p = null): string { return '/tools/tweaker-preview/'; }
function get_the_content(): string { return ''; }
function the_content(): void {}

/* ---------------------------------------------------------- document shell */

function language_attributes(): void { echo 'lang="en"'; }
function bloginfo(string $s = ''): void { echo $s === 'charset' ? 'UTF-8' : ''; }
function body_class(): void { echo 'class="page byrm-fullwidth"'; }
function wp_body_open(): void {}
function wp_head(): void
{
    echo '<title>Map editor — Bank of YR Maps</title>' . "\n";
    foreach (['header', 'footer', 'home', 'tweaker'] as $s) {
        echo '<link rel="stylesheet" href="' . THEME_URI . '/assets/css/' . $s . '.css">' . "\n";
    }
    echo '<style>body{margin:0;background:#0d1117;}</style>' . "\n";
}
function wp_footer(): void
{
    foreach (['header', 'footer', 'tweaker'] as $s) {
        echo '<script src="' . THEME_URI . '/assets/js/' . $s . '.js" defer></script>' . "\n";
    }
}

require ABSPATH . 'wp-content/themes/astra-child/inc/site-header.php';
require ABSPATH . 'wp-content/themes/astra-child/inc/site-footer.php';
require ABSPATH . 'wp-content/themes/astra-child/inc/home-helpers.php';
require ABSPATH . 'wp-content/themes/astra-child/inc/page-shell.php';
require ABSPATH . 'wp-content/themes/astra-child/inc/tweaker.php';

// add_action() is a no-op here, so the handler is invoked directly — the same
// function the live site runs on template_redirect.
if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    byrm_tweak_handle();
    exit;
}

require ABSPATH . 'wp-content/themes/astra-child/page-map-editor.php';
