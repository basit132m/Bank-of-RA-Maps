<?php

/**
 * Local preview for the download wait page.
 *
 *   php -S 127.0.0.1:8017 -t .
 *
 *   /tools/download-preview/             the real ten-second wait
 *   /tools/download-preview/?seconds=2   a short one, for testing the reveal
 *   /tools/download-preview/?bare=1      a map with no related maps and no host
 *   /tools/download-preview/?nolink=1    a map with no download link set
 *
 * Development tool only.
 */

declare(strict_types=1);

define('ABSPATH', dirname(__DIR__, 2) . '/');
const THEME_URI = '/wp-content/themes/astra-child';
define('HOUR_IN_SECONDS', 3600);
define('MINUTE_IN_SECONDS', 60);

$SECONDS = isset($_GET['seconds']) ? max(0, (int) $_GET['seconds']) : 10;
$BARE    = isset($_GET['bare']);
$NOLINK  = isset($_GET['nolink']);

/* ---------------------------------------------------------------- escaping */

function esc_url(string $u): string { return htmlspecialchars($u, ENT_QUOTES, 'UTF-8'); }
function esc_attr($t): string { return htmlspecialchars((string) $t, ENT_QUOTES, 'UTF-8'); }
function esc_html($t): string { return htmlspecialchars((string) $t, ENT_QUOTES, 'UTF-8'); }
function esc_html_e(string $t, string $d = ''): void { echo esc_html($t); }
function esc_attr_e(string $t, string $d = ''): void { echo esc_attr($t); }
function esc_html__(string $t, string $d = ''): string { return esc_html($t); }
function __(string $t, string $d = ''): string { return $t; }
function _n(string $s, string $p, int $n, string $d = ''): string { return 1 === $n ? $s : $p; }
function number_format_i18n($n): string { return number_format((int) $n); }
function absint($n): int { return abs((int) $n); }
function wp_trim_words(string $t, int $n = 55, ?string $m = null): string
{
    $w = preg_split('/\s+/', trim($t)) ?: [];
    return count($w) <= $n ? implode(' ', $w) : implode(' ', array_slice($w, 0, $n)) . ($m ?? '…');
}

/* ------------------------------------------------------------- environment */

function add_action(...$a): void {}
function add_filter(...$a): void {}
function apply_filters(string $h, $v, ...$r)
{
    if ('byrm_header_config' === $h && is_array($v)) { $v['discord_url'] = 'https://discord.gg/example'; }
    if ('byrm_footer_config' === $h && is_array($v)) { $v['discord_url'] = 'https://discord.gg/example'; }
    return $v;
}
function did_action(string $h): int { return 1; }
function home_url(string $p = '/'): string { return '/' . ltrim($p, '/'); }
function get_stylesheet_directory_uri(): string { return THEME_URI; }
function get_bloginfo(string $s = '', string $f = ''): string { return 'charset' === $s ? 'UTF-8' : 'Bank of YR Maps'; }
function get_search_query(): string { return ''; }
function has_custom_logo(): bool { return false; }
function the_custom_logo(): void {}
function has_nav_menu(string $l): bool { return false; }
function wp_nav_menu(array $a = []): void {}
function is_front_page(): bool { return false; }
function is_search(): bool { return false; }
function is_page($p = ''): bool { return false; }
function is_singular(string $t = ''): bool { return true; }
function is_wp_error($t): bool { return false; }
function post_type_exists(string $t): bool { return true; }
function get_post_type_archive_link(string $t) { return '/maps/'; }
function get_transient(string $k) { return false; }
function set_transient(string $k, $v, int $t = 0): bool { return true; }
function wp_count_posts(string $t) { return (object) ['publish' => 7]; }
function wp_reset_postdata(): void {}

/* -------------------------------------------------------------- this map */

const MAP_ID = 42;

function get_the_ID(): int { return MAP_ID; }
function get_query_var(string $v, $default = '') { return 'byrm_map_download' === $v ? MAP_ID : $default; }
function get_the_title($p = null): string
{
    if (is_object($p)) { return $p->title; }
    return 'Britain\'s Lost Outpost';
}
function get_permalink($p = null): string
{
    if (is_object($p)) { return '/maps/' . $p->slug . '/'; }
    return '/maps/britains-lost-outpost/';
}
function get_page_by_path(string $p) { return (object) ['ID' => 8, 'slug' => 'install']; }
function get_the_terms($id, string $tax)
{
    return 'map_theater' === $tax ? [(object) ['name' => 'Snow']] : [];
}

function byrm_map_meta(string $key, $id = null): string
{
    return ['players' => '4', 'version' => '1.1', 'cncnet_ready' => '1'][$key] ?? '';
}
function byrm_map_file_type($id = null): string { return '.yrm'; }
function byrm_map_file_size($id = null): string { return '327 KB'; }
function byrm_map_file_host($id = null): string { return $GLOBALS['BARE'] ? '' : 'datadock-host.site'; }
function byrm_map_downloads($id = null): int { return 1843; }
function byrm_map_download_go_url($id = null): string
{
    return $GLOBALS['NOLINK'] ? '' : '/map-download/' . MAP_ID . '/go/';
}
function byrm_download_wait_seconds(): int { return $GLOBALS['SECONDS']; }

/* ----------------------------------------------------------- other maps */

final class ByrmMap
{
    public function __construct(public int $ID, public string $slug, public string $title) {}
}

// The getter. Returns posts and prints nothing.
function byrm_related_map_posts($id, string $players = '', int $limit = 3): array
{
    if ($GLOBALS['BARE']) { return []; }
    return array_slice([
        new ByrmMap(43, 'frozen-divide', 'Frozen Divide'),
        new ByrmMap(44, 'tundra-crossing', 'Tundra Crossing'),
        new ByrmMap(45, 'glacier-run', 'Glacier Run'),
    ], 0, $limit);
}

// The renderer, stubbed to match the real one: it ECHOES and returns nothing.
// Stubbing this as a getter is exactly how the first version of this harness
// missed the template calling it in the wrong place, so it now behaves like the
// real function and the verification asserts nothing is printed before the
// doctype.
function byrm_related_maps($id, string $players = ''): void
{
    echo '<section class="byrm-related">THIS SHOULD NEVER APPEAR BEFORE THE DOCTYPE</section>';
}
function has_post_thumbnail($id = null): bool { return false; }
function get_the_post_thumbnail($id = null, $s = '', $a = []): string { return ''; }
function has_excerpt($p = null): bool { return true; }
function get_the_excerpt($p = null): string { return 'A four-player snow map built around a single contested bridge.'; }

/* ---------------------------------------------------------- document shell */

function language_attributes(): void { echo 'lang="en"'; }
function bloginfo(string $s = ''): void { echo 'charset' === $s ? 'UTF-8' : ''; }
function body_class(): void { echo 'class="single single-map byrm-fullwidth"'; }
function wp_body_open(): void {}
function wp_head(): void
{
    echo '<title>Preparing your download — Bank of YR Maps</title>' . "\n";
    foreach (['header', 'footer', 'home', 'guide', 'download'] as $s) {
        echo '<link rel="stylesheet" href="' . THEME_URI . '/assets/css/' . $s . '.css">' . "\n";
    }
    echo '<style>body{margin:0;background:#0d1117;}</style>' . "\n";
}
function wp_footer(): void
{
    foreach (['header', 'footer', 'download'] as $s) {
        echo '<script src="' . THEME_URI . '/assets/js/' . $s . '.js" defer></script>' . "\n";
    }
}

$GLOBALS['SECONDS'] = $SECONDS;
$GLOBALS['BARE']    = $BARE;
$GLOBALS['NOLINK']  = $NOLINK;

require ABSPATH . 'wp-content/themes/astra-child/inc/site-header.php';
require ABSPATH . 'wp-content/themes/astra-child/inc/site-footer.php';
require ABSPATH . 'wp-content/themes/astra-child/inc/home-helpers.php';
require ABSPATH . 'wp-content/themes/astra-child/inc/page-shell.php';
require ABSPATH . 'wp-content/themes/astra-child/map-download.php';
