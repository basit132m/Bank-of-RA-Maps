<?php

/**
 * Local preview for the home page.
 *
 *   php -S 127.0.0.1:8005 -t .
 *   http://127.0.0.1:8005/tools/home-preview/          maps published
 *   http://127.0.0.1:8005/tools/home-preview/?empty=1  empty catalogue
 *
 * Development tool only.
 */

declare(strict_types=1);

define('ABSPATH', dirname(__DIR__, 2) . '/');
const THEME_URI = '/wp-content/themes/astra-child';

// Constants WordPress defines for us in a real install.
define('HOUR_IN_SECONDS', 3600);
define('DAY_IN_SECONDS', 86400);
const SAMPLES   = '/tools/map-preview/samples';

$EMPTY = isset($_GET['empty']);

// ---------------------------------------------------------- WordPress stubs --

function esc_url(string $u): string { return htmlspecialchars($u, ENT_QUOTES, 'UTF-8'); }
function esc_url_raw(string $u): string { return $u; }
function esc_attr($t): string { return htmlspecialchars((string) $t, ENT_QUOTES, 'UTF-8'); }
function esc_html($t): string { return htmlspecialchars((string) $t, ENT_QUOTES, 'UTF-8'); }
function esc_html_e(string $t, string $d = ''): void { echo esc_html($t); }
function esc_attr_e(string $t, string $d = ''): void { echo esc_attr($t); }
function esc_html__(string $t, string $d = ''): string { return esc_html($t); }
function __(string $t, string $d = ''): string { return $t; }
function _n(string $s, string $p, int $n, string $d = ''): string { return $n === 1 ? $s : $p; }

function home_url(string $p = '/'): string { return '/' . ltrim($p, '/'); }
function get_stylesheet_directory_uri(): string { return THEME_URI; }
function get_bloginfo(string $s = '', string $f = ''): string { return 'Bank of YR Maps'; }
function get_search_query(): string { return ''; }
function has_custom_logo(): bool { return false; }
function the_custom_logo(): void {}
function has_nav_menu(string $l): bool { return false; }
function wp_nav_menu(array $a = []): void {}
function is_singular(string $t = ''): bool { return false; }
function is_front_page(): bool { return true; }
function is_wp_error($t): bool { return false; }
function number_format_i18n($n): string { return number_format((int) $n); }
function absint($n): int { return abs((int) $n); }
function sanitize_text_field(string $s): string { return trim(strip_tags($s)); }
function wp_trim_words(string $text, int $n = 55, string $more = '…'): string {
    $w = preg_split('/\s+/', trim($text));
    return count($w) <= $n ? $text : implode(' ', array_slice($w, 0, $n)) . $more;
}
function add_action(...$a): void {}
function add_filter(...$a): void {}
function apply_filters(string $hook, $value) {
    if ($hook === 'byrm_header_config' && is_array($value)) { $value['discord_url'] = 'https://discord.gg/example'; }
    if ($hook === 'byrm_footer_config' && is_array($value)) { $value['discord_url'] = 'https://discord.gg/example'; }
    if ($hook === 'body_class_list' && is_array($value)) { $value[] = 'byrm-fullwidth'; }
    return $value;
}

function post_type_exists(string $t): bool { return ! isset($GLOBALS['byrm_no_cpt']); }
function get_post_type_archive_link(string $t): string { return '/maps'; }
function get_permalink($p = null): string { return is_object($p) ? '/maps/' . $p->slug : '/maps/'; }
function get_the_title($p = null): string { return is_object($p) ? $p->title : 'Map'; }
function has_post_thumbnail($id = null): bool { return true; }
function get_the_post_thumbnail($id = null, $size = 'full', array $attr = []): string {
    $file = $GLOBALS['byrm_thumbs'][$id] ?? 'shot-1.jpg';
    $out = '<img src="' . esc_url(SAMPLES . '/' . $file) . '" width="1200" height="900"';
    foreach ($attr as $k => $v) { $out .= ' ' . $k . '="' . esc_attr((string) $v) . '"'; }
    return $out . '>';
}
function get_the_post_thumbnail_url($id = null, $size = 'full'): string {
    return SAMPLES . '/' . ($GLOBALS['byrm_thumbs'][$id] ?? 'minimap.jpg');
}
function has_excerpt($id = null): bool { return true; }
function get_the_excerpt($p = null): string {
    return 'A tight 1v1 desert map with one central chokepoint and no room to hide a tech-up.';
}
function get_the_terms($id, string $tax) {
    if ($tax !== 'map_theater') { return false; }
    $names = ['Desert', 'Snow', 'Urban', 'Temperate', 'Lunar', 'New Urban', 'Desert'];
    return [(object) ['name' => $names[$id % count($names)], 'slug' => 'x']];
}
function get_term_link($t): string { return '/theater/' . $t->slug; }
function get_terms(array $args = []) {
    if (isset($GLOBALS['byrm_empty'])) { return []; }
    return [
        (object) ['name' => 'Desert', 'slug' => 'desert', 'count' => 3],
        (object) ['name' => 'Snow', 'slug' => 'snow', 'count' => 2],
        (object) ['name' => 'Temperate', 'slug' => 'temperate', 'count' => 4],
        (object) ['name' => 'Urban', 'slug' => 'urban', 'count' => 1],
        (object) ['name' => 'Lunar', 'slug' => 'lunar', 'count' => 1],
    ];
}

function byrm_map_meta(string $key, $id = null) {
    return ['players' => (string) (2 + (($id ?? 0) % 3) * 2), 'size_x' => '90', 'size_y' => '90'][$key] ?? '';
}
function byrm_map_downloads($id = null): int { return 400 + (int) $id * 137; }
function byrm_map_download_url($id = null): string { return '/map-download/' . $id . '/'; }

// Transients and the totals query.
function get_transient(string $k) { return false; }
function set_transient(string $k, $v, int $t = 0): bool { return true; }
function delete_transient(string $k): bool { return true; }
function wp_count_posts(string $type) {
    return (object) ['publish' => isset($GLOBALS['byrm_empty']) ? 0 : 7];
}

/** Minimal stand-in for $wpdb, enough for the totals query. */
final class ByrmWpdb
{
    public string $postmeta = 'wp_postmeta';
    public string $posts = 'wp_posts';
    public function prepare(string $sql, ...$args): string { return $sql; }
    public function get_var(string $sql) { return isset($GLOBALS['byrm_empty']) ? 0 : 18432; }
}
$GLOBALS['wpdb'] = new ByrmWpdb();

// Sample maps.
$GLOBALS['byrm_thumbs'] = [
    1 => 'minimap.jpg', 2 => 'shot-1.jpg', 3 => 'shot-2.jpg',
    4 => 'shot-3.jpg', 5 => 'shot-4.jpg', 6 => 'shot-1.jpg', 7 => 'shot-2.jpg',
];

function get_posts(array $args = []): array
{
    if (isset($GLOBALS['byrm_empty'])) { return []; }

    $all = [
        (object) ['ID' => 1, 'title' => 'Dust Bowl Standoff', 'slug' => 'dust-bowl-standoff'],
        (object) ['ID' => 2, 'title' => 'Coral Gauntlet', 'slug' => 'coral-gauntlet'],
        (object) ['ID' => 3, 'title' => 'Siberian Crossroads', 'slug' => 'siberian-crossroads'],
        (object) ['ID' => 4, 'title' => 'Neon District', 'slug' => 'neon-district'],
        (object) ['ID' => 5, 'title' => 'Rust Valley', 'slug' => 'rust-valley'],
        (object) ['ID' => 6, 'title' => 'Baku Oil Fields', 'slug' => 'baku-oil-fields'],
        (object) ['ID' => 7, 'title' => 'Lunar Assembly', 'slug' => 'lunar-assembly'],
    ];

    $exclude = array_map('intval', $args['post__not_in'] ?? []);
    $all = array_values(array_filter($all, static fn ($m) => ! in_array((int) $m->ID, $exclude, true)));

    return array_slice($all, 0, (int) ($args['posts_per_page'] ?? 3));
}

if ($EMPTY) { $GLOBALS['byrm_empty'] = true; }

// ------------------------------------------------------------- templates ----

require ABSPATH . 'wp-content/themes/astra-child/inc/site-header.php';
require ABSPATH . 'wp-content/themes/astra-child/inc/site-footer.php';
require ABSPATH . 'wp-content/themes/astra-child/inc/home-helpers.php';

function language_attributes(): void { echo 'lang="en"'; }
function bloginfo(string $show = ''): void { echo $show === 'charset' ? 'UTF-8' : ''; }
function body_class(): void
{
    $classes = ['home', 'ast-right-sidebar'];
    $classes = apply_filters('body_class_list', $classes);
    echo 'class="' . implode(' ', $classes) . '"';
}
function wp_body_open(): void {}

function wp_head(): void
{
    echo '<title>Bank of YR Maps</title>' . "\n";
    foreach (['header', 'footer', 'home'] as $sheet) {
        echo '<link rel="stylesheet" href="' . THEME_URI . '/assets/css/' . $sheet . '.css">' . "\n";
    }
    echo '<style>body{margin:0;background:#fff;}</style>' . "\n";

    if (isset($_GET['astra'])) {
        // Astra's container rules, still present and still hostile. With the
        // shell there is no Astra markup for them to match.
        echo '<style id="fake-astra-css">'
            . '.ast-container{max-width:1022px;margin:0 auto;}'
            . '#content.site-content .ast-container{max-width:1022px;}'
            . '#primary{width:82%;float:left;}'
            . '#secondary{width:18%;float:right;}'
            . '</style>' . "\n";
    }
}

function wp_footer(): void
{
    foreach (['header', 'footer'] as $script) {
        echo '<script src="' . THEME_URI . '/assets/js/' . $script . '.js" defer></script>' . "\n";
    }
}

require ABSPATH . 'wp-content/themes/astra-child/inc/page-shell.php';

require ABSPATH . 'wp-content/themes/astra-child/front-page.php';
