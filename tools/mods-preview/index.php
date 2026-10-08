<?php

/**
 * Local preview for the Mods section.
 *
 *   /tools/mods-preview/              the browse page at /mods/
 *   /tools/mods-preview/?term=1       a mod_type term archive
 *   /tools/mods-preview/?empty=1      nothing published yet
 *   /tools/mods-preview/?single=1     a single mod (not multiplayer safe)
 *   /tools/mods-preview/?single=1&mp=1  a single mod that is multiplayer safe
 *
 * The stubs below mirror the real contracts: byrm_mod_card() and the lightbox
 * ECHO, byrm_related_mod_posts() RETURNS. Getting that backwards in an earlier
 * harness is what let a broken page pass its tests.
 *
 * Development tool only.
 */

declare(strict_types=1);

define('ABSPATH', dirname(__DIR__, 2) . '/');
const THEME_URI = '/wp-content/themes/astra-child';
define('HOUR_IN_SECONDS', 3600);
define('MINUTE_IN_SECONDS', 60);

$SINGLE = isset($_GET['single']);
$TERM   = isset($_GET['term']);
$EMPTY  = isset($_GET['empty']);
$MP     = isset($_GET['mp']);

/* ---------------------------------------------------------------- escaping */

function esc_url(string $u): string { return htmlspecialchars($u, ENT_QUOTES, 'UTF-8'); }
function esc_attr($t): string { return htmlspecialchars((string) $t, ENT_QUOTES, 'UTF-8'); }
function esc_html($t): string { return htmlspecialchars((string) $t, ENT_QUOTES, 'UTF-8'); }
function esc_textarea($t): string { return esc_html($t); }
function esc_html_e(string $t, string $d = ''): void { echo esc_html($t); }
function esc_attr_e(string $t, string $d = ''): void { echo esc_attr($t); }
function esc_html__(string $t, string $d = ''): string { return esc_html($t); }
function __(string $t, string $d = ''): string { return $t; }
function _n(string $s, string $p, int $n, string $d = ''): string { return 1 === $n ? $s : $p; }
function number_format_i18n($n): string { return number_format((int) $n); }
function absint($n): int { return abs((int) $n); }
function wp_strip_all_tags($t, bool $b = false): string { return trim(strip_tags((string) $t)); }
function wp_kses_post($t): string { return (string) $t; }
function wpautop($t): string { return '<p>' . str_replace("\n\n", '</p><p>', (string) $t) . '</p>'; }
function wp_parse_url(string $u, int $c = -1) { return parse_url($u, $c); }
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
function is_wp_error($t): bool { return false; }
function post_type_exists(string $t): bool { return true; }
function taxonomy_exists(string $t): bool { return true; }
function get_post_type_archive_link(string $t) { return 'mod' === $t ? '/mods/' : '/maps/'; }
function get_transient(string $k) { return false; }
function set_transient(string $k, $v, int $t = 0): bool { return true; }
function is_singular($t = '') { return (bool) $GLOBALS['SINGLE']; }
function is_tax($t = '') { return (bool) $GLOBALS['TERM']; }
function get_queried_object() { return (object) ['term_id' => 2, 'name' => 'Balance patch', 'description' => '']; }
function get_term_link($t) { return '/mod-type/' . sanitize_title(is_object($t) ? $t->name : (string) $t) . '/'; }
function sanitize_title(string $s): string { return trim(preg_replace('/[^a-z0-9]+/', '-', strtolower($s)), '-'); }
function paginate_links(array $a = []) { return []; }

/* ------------------------------------------------------------------- mods */

final class ByrmMod
{
    public function __construct(
        public int $ID,
        public string $slug,
        public string $title,
        public string $type = 'Balance patch'
    ) {}
}

// byrm_mod_totals(), byrm_mod_types(), byrm_mod_card() and
// byrm_related_mod_posts() are the code under test and come from
// mod-helpers.php. Stubbed here is only what THEY call.
final class ByrmFakeWpdb
{
    public string $postmeta = 'wp_postmeta';
    public string $posts    = 'wp_posts';
    public function prepare(string $q, ...$a): string { return $q; }
    public function get_var(string $q): string { return $GLOBALS['EMPTY'] ? '0' : '2410'; }
}
$GLOBALS['wpdb'] = new ByrmFakeWpdb();

function wp_count_posts(string $t) { return (object) ['publish' => $GLOBALS['EMPTY'] ? 0 : 6]; }
function delete_transient(string $k): bool { return true; }
function wp_list_pluck(array $rows, string $field): array
{
    return array_map(static fn($r) => is_object($r) ? ($r->$field ?? null) : ($r[$field] ?? null), $rows);
}
function locate_template($t) { return ''; }

function get_terms(array $args = [])
{
    if ($GLOBALS['EMPTY']) { return []; }
    return [
        (object) ['term_id' => 1, 'name' => 'Total conversion', 'count' => 2],
        (object) ['term_id' => 2, 'name' => 'Balance patch', 'count' => 3],
        (object) ['term_id' => 3, 'name' => 'Graphics', 'count' => 1],
    ];
}
function wp_get_post_terms($id, string $tax, array $args = []): array { return [2]; }
function get_posts(array $args = []): array
{
    $all = [
        new ByrmMod(52, 'mental-omega', 'Mental Omega', 'Total conversion'),
        new ByrmMod(53, 'rock-patch', 'Rock Patch', 'Utility'),
        new ByrmMod(54, 'hd-terrain', 'HD Terrain', 'Graphics'),
    ];
    $limit = isset($args['posts_per_page']) ? (int) $args['posts_per_page'] : 3;
    return array_slice($all, 0, max(0, $limit));
}
function byrm_mod_requires_label($id = null): string { return "Yuri's Revenge"; }
function byrm_mod_meta(string $key, $id = null)
{
    return [
        'mod_version'     => '2.3',
        'mod_author'      => 'Basit',
        'mod_homepage'    => 'https://forums.cncnet.org/topic/1234-example/',
        'mod_multiplayer' => $GLOBALS['MP'] ? '1' : '',
        'install_notes'   => '',
    ][$key] ?? '';
}
function byrm_map_gallery($id = null): array { return []; }
function get_post_thumbnail_id($id = null) { return 0; }
function has_post_thumbnail($id = null): bool { return false; }
function get_the_post_thumbnail($id = null, $s = '', $a = []): string { return ''; }
function byrm_map_banner_style(): string { return ''; }
function byrm_map_download_url($id = null): string { return '/mod-download/' . MOD_ID . '/'; }
function byrm_map_file_size($id = null): string { return '14.2 MB'; }
function byrm_map_file_type($id = null): string { return '.zip'; }
function byrm_map_file_host($id = null): string { return 'datadock-host.site'; }
function byrm_map_downloads($id = null): int { return 412; }

const MOD_ID = 51;

function get_the_ID(): int { return MOD_ID; }
function get_the_title($p = null): string { return is_object($p) ? $p->title : 'Twisted Insurrection Rules'; }
function the_title(): void { echo esc_html(get_the_title()); }
function get_permalink($p = null): string { return is_object($p) ? '/mods/' . $p->slug . '/' : '/mods/twisted-insurrection-rules/'; }
function get_the_terms($id, string $tax)
{
    if ('mod_type' === $tax) {
        $t = 'Balance patch';
        foreach (get_posts([]) as $m) { if ($m->ID === $id) { $t = $m->type; } }
        return [(object) ['term_id' => 2, 'name' => $t]];
    }
    if ('mod_tag' === $tax) { return [(object) ['name' => 'rules.ini'], (object) ['name' => 'balance']]; }
    return [];
}
function get_the_content(): string { return 'Rebalances every unit in the game, with a changelog running to four pages.'; }
function the_content(): void { echo '<p>' . esc_html(get_the_content()) . '</p>'; }
function has_excerpt($p = null): bool { return true; }
function get_the_excerpt($p = null): string { return 'A full rules rebalance aimed at competitive play.'; }

$GLOBALS['BYRM_ROWS'] = [];
$GLOBALS['BYRM_I']    = 0;
function have_posts(): bool { return $GLOBALS['BYRM_I'] < count($GLOBALS['BYRM_ROWS']); }
function the_post(): void { $GLOBALS['BYRM_CURRENT'] = $GLOBALS['BYRM_ROWS'][$GLOBALS['BYRM_I']++]; }
function get_post($p = null) { return $GLOBALS['BYRM_CURRENT'] ?? null; }

/* ---------------------------------------------------------- document shell */

function language_attributes(): void { echo 'lang="en"'; }
function bloginfo(string $s = ''): void { echo 'charset' === $s ? 'UTF-8' : ''; }
function body_class(): void { echo 'class="byrm-fullwidth"'; }
function wp_body_open(): void {}
function wp_head(): void
{
    echo '<title>Mods — Bank of YR Maps</title>' . "\n";
    $sheets = $GLOBALS['SINGLE'] ? ['header', 'footer', 'home', 'map', 'mod'] : ['header', 'footer', 'home', 'archive', 'mod'];
    foreach ($sheets as $s) {
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

$GLOBALS['SINGLE'] = $SINGLE;
$GLOBALS['TERM']   = $TERM;
$GLOBALS['EMPTY']  = $EMPTY;
$GLOBALS['MP']     = $MP;

require ABSPATH . 'wp-content/themes/astra-child/inc/site-header.php';
require ABSPATH . 'wp-content/themes/astra-child/inc/site-footer.php';
require ABSPATH . 'wp-content/themes/astra-child/inc/page-shell.php';
require ABSPATH . 'wp-content/themes/astra-child/inc/mod-helpers.php';

if ($SINGLE) {
    $GLOBALS['BYRM_ROWS'] = [new ByrmMod(MOD_ID, 'twisted-insurrection-rules', 'Twisted Insurrection Rules')];
    $GLOBALS['wp_query'] = (object) ['found_posts' => 1];
    require ABSPATH . 'wp-content/themes/astra-child/single-mod.php';
} else {
    $GLOBALS['BYRM_ROWS'] = $EMPTY ? [] : get_posts([]);
    $GLOBALS['wp_query'] = (object) ['found_posts' => count($GLOBALS['BYRM_ROWS'])];
    require ABSPATH . 'wp-content/themes/astra-child/archive-mod.php';
}
