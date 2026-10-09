<?php

/**
 * Local preview for the image wall.
 *
 *   /tools/gallery-preview/               the wall, 60 maps, 24 per page
 *   /tools/gallery-preview/?gal=2         the no-JavaScript second page
 *   /tools/gallery-preview/?empty=1       no maps with images
 *   /tools/gallery-preview/?rest=1&page=N stands in for the REST route
 *   /tools/gallery-preview/?fail=1        REST returns 500, to test standing down
 *
 * byrm_gallery_query(), byrm_gallery_tile() and the REST callback are the code
 * under test and come from gallery-helpers.php. Only WordPress is stubbed.
 *
 * Development tool only.
 */

declare(strict_types=1);

define('ABSPATH', dirname(__DIR__, 2) . '/');
const THEME_URI = '/wp-content/themes/astra-child';
define('HOUR_IN_SECONDS', 3600);

$REST  = isset($_GET['rest']);
$EMPTY = isset($_GET['empty']);
$FAIL  = isset($_GET['fail']);

/* ---------------------------------------------------------------- escaping */

function esc_url(string $u): string { return htmlspecialchars($u, ENT_QUOTES, 'UTF-8'); }
function esc_attr($t): string { return htmlspecialchars((string) $t, ENT_QUOTES, 'UTF-8'); }
function esc_html($t): string { return htmlspecialchars((string) $t, ENT_QUOTES, 'UTF-8'); }
function esc_html_e(string $t, string $d = ''): void { echo esc_html($t); }
function esc_attr_e(string $t, string $d = ''): void { echo esc_attr($t); }
function esc_html__(string $t, string $d = ''): string { return esc_html($t); }
function esc_attr__(string $t, string $d = ''): string { return esc_attr($t); }
function __(string $t, string $d = ''): string { return $t; }
function _n(string $s, string $p, int $n, string $d = ''): string { return 1 === $n ? $s : $p; }
function number_format_i18n($n): string { return number_format((int) $n); }
function absint($n): int { return abs((int) $n); }
function wp_unslash($v) { return $v; }
function wp_strip_all_tags($t, bool $b = false): string { return trim(strip_tags((string) $t)); }

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
function is_page($p = ''): bool { return true; }
function is_wp_error($t): bool { return false; }
function post_type_exists(string $t): bool { return true; }
function get_post_type_archive_link(string $t) { return '/maps/'; }
function wp_count_posts(string $t) { return (object) ['publish' => $GLOBALS['EMPTY'] ? 0 : 60]; }
function get_transient(string $k) { return false; }
function set_transient(string $k, $v, int $t = 0): bool { return true; }
function get_page_by_path(string $p) { return (object) ['ID' => 90]; }
function wp_reset_postdata(): void {}
function locate_template($t) { return ''; }
function register_rest_route(...$a): void {}
function rest_ensure_response($d) { return $d; }
function rest_url(string $path = ''): string { return '/tools/gallery-preview/?rest=1'; }
function add_query_arg($key, $value = null, $url = '')
{
    $sep = (strpos((string) $url, '?') === false) ? '?' : '&';
    return $url . $sep . rawurlencode((string) $key) . '=' . rawurlencode((string) $value);
}
class WP_REST_Server { const READABLE = 'GET'; }

/* ------------------------------------------------------------- the catalogue */

final class ByrmGalMap
{
    public function __construct(public int $ID, public string $slug, public string $title, public string $theater, public int $players) {}
}

const THEATERS = ['Snow', 'Temperate', 'Urban', 'Desert', 'Lunar', 'New Urban'];

function byrm_gal_fixture(): array
{
    static $maps = null;
    if ($maps !== null) { return $maps; }
    if ($GLOBALS['EMPTY']) { return $maps = []; }

    $maps = [];
    for ($i = 1; $i <= 60; $i++) {
        $maps[] = new ByrmGalMap(
            500 + $i,
            'map-' . $i,
            'Contested Ridge ' . $i,
            THEATERS[$i % 6],
            [2, 4, 6, 8][$i % 4]
        );
    }
    return $maps;
}

// Stands in for WP_Query: honours posts_per_page and offset, and reports
// found_posts as the real total the way SQL_CALC_FOUND_ROWS does.
class WP_Query
{
    public array $posts = [];
    public int $found_posts = 0;
    public int $max_num_pages = 0;

    public function __construct(array $args)
    {
        $all = byrm_gal_fixture();
        $per = (int) ($args['posts_per_page'] ?? 10);
        $off = (int) ($args['offset'] ?? 0);

        $this->posts         = array_slice($all, $off, $per);
        $this->found_posts   = count($all);
        $this->max_num_pages = $per > 0 ? (int) ceil(count($all) / $per) : 0;
    }
}

function get_post_thumbnail_id($id = null) { return 7000 + (int) (is_object($id) ? $id->ID : $id); }
function get_permalink($p = null): string { return is_object($p) ? '/maps/' . $p->slug . '/' : '/gallery/'; }
function get_the_title($p = null): string { return is_object($p) ? $p->title : 'Map gallery'; }
function the_title(): void { echo esc_html(get_the_title()); }
function get_the_content(): string { return ''; }
function the_content(): void {}
function get_the_terms($id, string $tax)
{
    foreach (byrm_gal_fixture() as $m) {
        if ($m->ID === $id) { return [(object) ['name' => $m->theater]]; }
    }
    return [];
}
function byrm_map_meta(string $key, $id = null)
{
    foreach (byrm_gal_fixture() as $m) {
        if ($m->ID === $id && 'players' === $key) { return (string) $m->players; }
    }
    return '';
}
function byrm_map_download_url($id = null): string { return '/map-download/' . (int) $id . '/'; }

function wp_get_attachment_image($id, $size = '', $icon = false, $attr = []): string
{
    // A coloured SVG stands in for a minimap: real dimensions, no files needed.
    $hue = ((int) $id * 37) % 360;
    $svg = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 320 180">'
        . '<rect width="320" height="180" fill="hsl(' . $hue . ',28%,22%)"/>'
        . '<path d="M0 120 L80 70 L160 110 L240 60 L320 100 V180 H0Z" fill="hsl(' . $hue . ',30%,16%)"/>'
        . '</svg>';
    $src = 'data:image/svg+xml;base64,' . base64_encode($svg);

    $out = '<img src="' . esc_url($src) . '" width="320" height="180"';
    foreach ($attr as $k => $v) { $out .= ' ' . esc_attr($k) . '="' . esc_attr($v) . '"'; }
    return $out . '>';
}

/* ---------------------------------------------------------- document shell */

function language_attributes(): void { echo 'lang="en"'; }
function bloginfo(string $s = ''): void { echo 'charset' === $s ? 'UTF-8' : ''; }
function body_class(): void { echo 'class="page byrm-fullwidth"'; }
function wp_body_open(): void {}
function wp_head(): void
{
    echo '<title>Map gallery — Bank of YR Maps</title>' . "\n";
    foreach (['header', 'footer', 'home', 'gallery'] as $s) {
        echo '<link rel="stylesheet" href="' . THEME_URI . '/assets/css/' . $s . '.css">' . "\n";
    }
    echo '<style>body{margin:0;background:#0d1117;}</style>' . "\n";
}
function wp_footer(): void
{
    foreach (['header', 'footer', 'gallery'] as $s) {
        echo '<script src="' . THEME_URI . '/assets/js/' . $s . '.js" defer></script>' . "\n";
    }
}

$GLOBALS['EMPTY'] = $EMPTY;

require ABSPATH . 'wp-content/themes/astra-child/inc/gallery-helpers.php';

/* ------------------------------------------------------------ the REST leg */

if ($REST) {
    header('Content-Type: application/json');

    if ($FAIL) {
        http_response_code(500);
        echo json_encode(['error' => 'deliberate failure']);
        exit;
    }

    $page = isset($_GET['page']) ? max(1, (int) $_GET['page']) : 2;

    // Call the real callback through a minimal request object.
    $request = new class($page) {
        public function __construct(private int $page) {}
        public function get_param(string $k) { return $this->page; }
    };

    echo json_encode(byrm_gallery_rest($request));
    exit;
}

require ABSPATH . 'wp-content/themes/astra-child/inc/site-header.php';
require ABSPATH . 'wp-content/themes/astra-child/inc/site-footer.php';
require ABSPATH . 'wp-content/themes/astra-child/inc/page-shell.php';
require ABSPATH . 'wp-content/themes/astra-child/page-gallery.php';
