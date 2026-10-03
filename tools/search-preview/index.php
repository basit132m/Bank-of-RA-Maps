<?php

/**
 * Local preview for the search results page.
 *
 *   php -S 127.0.0.1:8013 -t .
 *
 *   /tools/search-preview/?s=snow        maps + a matching theater
 *   /tools/search-preview/?s=basit       a designer match only
 *   /tools/search-preview/?s=guide       pages and guides only
 *   /tools/search-preview/?s=zzzz        nothing found
 *   /tools/search-preview/?s=            no term typed yet
 *   /tools/search-preview/?s=snow&paged=2  page two (extras suppressed)
 *
 * Development tool only.
 */

declare(strict_types=1);

define('ABSPATH', dirname(__DIR__, 2) . '/');
const THEME_URI = '/wp-content/themes/astra-child';
define('HOUR_IN_SECONDS', 3600);
define('MINUTE_IN_SECONDS', 60);
define('DAY_IN_SECONDS', 86400);

$TERM  = isset($_GET['s']) ? (string) $_GET['s'] : 'snow';
$PAGED = (int) ($_GET['paged'] ?? 1);

/* ---------------------------------------------------------------- escaping */

function esc_url(string $u): string { return htmlspecialchars($u, ENT_QUOTES, 'UTF-8'); }
function esc_attr($t): string { return htmlspecialchars((string) $t, ENT_QUOTES, 'UTF-8'); }
function esc_html($t): string { return htmlspecialchars((string) $t, ENT_QUOTES, 'UTF-8'); }
function esc_html_e(string $t, string $d = ''): void { echo esc_html($t); }
function esc_attr_e(string $t, string $d = ''): void { echo esc_attr($t); }
function esc_html__(string $t, string $d = ''): string { return esc_html($t); }
function esc_attr__(string $t, string $d = ''): string { return esc_attr($t); }
function __(string $t, string $d = ''): string { return $t; }
function _n(string $s, string $p, int $n, string $d = ''): string { return $n === 1 ? $s : $p; }
function number_format_i18n($n): string { return number_format((int) $n); }
function absint($n): int { return abs((int) $n); }
function wp_strip_all_tags($t, bool $b = false): string { return trim(strip_tags((string) $t)); }
function wp_kses_post(string $h): string { return $h; }
function add_query_arg($k, $v = null, $url = '') { return $url . (str_contains((string) $url, '?') ? '&' : '?') . $k . '=' . rawurlencode((string) $v); }

function wp_trim_words(string $text, int $n = 55, ?string $more = null): string
{
    $words = preg_split('/\s+/', trim($text)) ?: [];
    if (count($words) <= $n) { return implode(' ', $words); }
    return implode(' ', array_slice($words, 0, $n)) . ($more ?? '…');
}

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
function has_custom_logo(): bool { return false; }
function the_custom_logo(): void {}
function has_nav_menu(string $l): bool { return false; }
function wp_nav_menu(array $a = []): void {}
function is_singular(string $t = ''): bool { return false; }
function is_front_page(): bool { return false; }
function is_page($p = ''): bool { return false; }
function is_search(): bool { return true; }
function is_wp_error($t): bool { return $t instanceof RuntimeException; }
function post_type_exists(string $t): bool { return true; }
function get_post_type_archive_link(string $t) { return '/maps/'; }
function get_transient(string $k) { return false; }
function set_transient(string $k, $v, int $t = 0): bool { return true; }
function wp_count_posts(string $t) { return (object) ['publish' => 7]; }
function has_excerpt($p = null): bool { return true; }
function wp_list_pluck(array $rows, string $field): array
{
    return array_map(static fn ($r) => is_object($r) ? $r->{$field} : $r[$field], $rows);
}

/* --------------------------------------------------------------- the posts */

final class ByrmPost
{
    public function __construct(
        public int $ID,
        public string $post_type,
        public string $title,
        public string $post_content = '',
        public string $designer = '',
        public string $players = '',
        public string $theater = ''
    ) {}
}

$ALL = [
    new ByrmPost(21, 'map',  'Snowbound Ridge',    'A tight four-player snow map with one central ramp.', 'Basit', '4', 'Snow'),
    new ByrmPost(22, 'map',  'Frozen Delta',       'Naval routes through broken ice.', 'Basit', '6', 'Snow'),
    new ByrmPost(23, 'map',  'Winter Crossroads',  'Six players, heavy snow, no naval.', 'unknown author', '6', 'Snow'),
    new ByrmPost(24, 'map',  'Desert Standoff',    'Two plateaus, one ramp.', 'Basit', '2', 'Desert'),
    new ByrmPost(31, 'page', 'Installing maps',    'Download the version that matches your game, rename it to .map, and put it in RA2 > Maps > Custom. This guide walks through every step.'),
    new ByrmPost(32, 'page', 'Community',          'The board, Discord, and the house rules for talking about maps here.'),
];

/**
 * Crude title/content match, standing in for WordPress core search.
 */
function byrm_preview_results(string $term, array $all): array
{
    if ('' === trim($term)) { return []; }
    $needle = mb_strtolower($term);
    return array_values(array_filter($all, static function ($p) use ($needle) {
        return str_contains(mb_strtolower($p->title), $needle)
            || str_contains(mb_strtolower($p->post_content), $needle);
    }));
}

$RESULTS = byrm_preview_results($TERM, $ALL);

final class ByrmQuery { public int $found_posts = 0; }
$GLOBALS['wp_query'] = new ByrmQuery();
$GLOBALS['wp_query']->found_posts = count($RESULTS);

$GLOBALS['BYRM_RESULTS'] = $RESULTS;
$GLOBALS['BYRM_I'] = 0;
$GLOBALS['BYRM_CURRENT'] = null;

function have_posts(): bool { return $GLOBALS['BYRM_I'] < count($GLOBALS['BYRM_RESULTS']); }
function the_post(): void { $GLOBALS['BYRM_CURRENT'] = $GLOBALS['BYRM_RESULTS'][$GLOBALS['BYRM_I']++]; }
function rewind_posts(): void { $GLOBALS['BYRM_I'] = 0; }
function get_post($p = null) { return $p ?? $GLOBALS['BYRM_CURRENT']; }
function get_post_type($p = null): string { return ($p ?? $GLOBALS['BYRM_CURRENT'])->post_type; }
function get_the_title($p = null): string { return is_object($p) ? $p->title : ($GLOBALS['BYRM_CURRENT']->title ?? ''); }
function get_permalink($p = null): string
{
    $post = is_object($p) ? $p : $GLOBALS['BYRM_CURRENT'];
    return ($post->post_type === 'map' ? '/maps/' : '/') . sanitize_title($post->title) . '/';
}
function sanitize_title(string $t): string { return trim(preg_replace('/[^a-z0-9]+/', '-', mb_strtolower($t)) ?? '', '-'); }
function get_the_excerpt($p = null): string { return (is_object($p) ? $p : $GLOBALS['BYRM_CURRENT'])->post_content; }
function get_search_query(): string { return esc_attr($GLOBALS['BYRM_TERM']); }
function get_query_var(string $v, $d = '') { return $v === 'paged' ? $GLOBALS['BYRM_PAGED'] : $d; }

function get_post_type_object(string $t)
{
    return (object) ['labels' => (object) ['singular_name' => $t === 'page' ? 'Page' : ucfirst($t)]];
}

/* -------------------------------------------------- map card dependencies */

function byrm_map_meta(string $key, $id = null)
{
    foreach ($GLOBALS['BYRM_ALL'] as $p) {
        if ($p->ID === (int) $id) { return $key === 'players' ? $p->players : ($key === 'designer' ? $p->designer : ''); }
    }
    return '';
}
function byrm_map_downloads($id = null): int { return 418; }
function get_the_terms($id, string $tax)
{
    foreach ($GLOBALS['BYRM_ALL'] as $p) {
        if ($p->ID === (int) $id && $p->theater !== '') { return [(object) ['name' => $p->theater]]; }
    }
    return false;
}
function has_post_thumbnail($id = null): bool { return false; }
function get_the_post_thumbnail($id = null, $size = 'full', array $attr = []): string { return ''; }

/* ------------------------------------------- designer and taxonomy lookups */

function get_posts(array $args): array
{
    // Stands in for the designer meta_query.
    $needle = mb_strtolower((string) ($args['meta_query'][0]['value'] ?? ''));
    if ('' === $needle) { return []; }
    $skip = $args['post__not_in'] ?? [];
    return array_values(array_filter($GLOBALS['BYRM_ALL'], static function ($p) use ($needle, $skip) {
        return $p->post_type === 'map'
            && $p->designer !== ''
            && str_contains(mb_strtolower($p->designer), $needle)
            && ! in_array($p->ID, $skip, true);
    }));
}

function get_terms(array $args)
{
    $needle = mb_strtolower((string) ($args['name__like'] ?? ''));
    if ('' === $needle) { return []; }
    $terms = [
        ['name' => 'Snow', 'taxonomy' => 'map_theater', 'count' => 3],
        ['name' => 'Desert', 'taxonomy' => 'map_theater', 'count' => 1],
        ['name' => 'Naval', 'taxonomy' => 'map_mode', 'count' => 2],
    ];
    $hits = array_filter($terms, static fn ($t) => str_contains(mb_strtolower($t['name']), $needle));
    return array_map(static fn ($t) => (object) $t, array_values($hits));
}
function get_term_link($t) { return '/theater/' . sanitize_title($t->name) . '/'; }

function paginate_links(array $args = [])
{
    if (count($GLOBALS['BYRM_RESULTS']) < 3) { return []; }
    return [
        '<span class="page-numbers current">1</span>',
        '<a class="page-numbers" href="?s=' . rawurlencode($GLOBALS['BYRM_TERM']) . '&paged=2">2</a>',
        '<a class="next page-numbers" href="?s=' . rawurlencode($GLOBALS['BYRM_TERM']) . '&paged=2">Next</a>',
    ];
}

/* ---------------------------------------------------------- document shell */

function language_attributes(): void { echo 'lang="en"'; }
function bloginfo(string $s = ''): void { echo $s === 'charset' ? 'UTF-8' : ''; }
function body_class(): void { echo 'class="search byrm-fullwidth"'; }
function wp_body_open(): void {}
function wp_head(): void
{
    echo '<title>Search — Bank of YR Maps</title>' . "\n";
    foreach (['header', 'footer', 'home', 'search'] as $s) {
        echo '<link rel="stylesheet" href="' . THEME_URI . '/assets/css/' . $s . '.css">' . "\n";
    }
    echo '<style>body{margin:0;background:#0d1117;}</style>' . "\n";
}
function wp_footer(): void
{
    foreach (['header', 'footer'] as $s) {
        echo '<script src="' . THEME_URI . '/assets/js/' . $s . '.js" defer></script>' . "\n";
    }
}

$GLOBALS['BYRM_TERM']  = $TERM;
$GLOBALS['BYRM_PAGED'] = $PAGED;
$GLOBALS['BYRM_ALL']   = $ALL;

require ABSPATH . 'wp-content/themes/astra-child/inc/site-header.php';
require ABSPATH . 'wp-content/themes/astra-child/inc/site-footer.php';
require ABSPATH . 'wp-content/themes/astra-child/inc/home-helpers.php';
require ABSPATH . 'wp-content/themes/astra-child/inc/page-shell.php';
require ABSPATH . 'wp-content/themes/astra-child/inc/search-helpers.php';
require ABSPATH . 'wp-content/themes/astra-child/search.php';
