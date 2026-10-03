<?php

/**
 * Local preview for the maps archive.
 *
 *   php -S 127.0.0.1:8008 -t .
 *   /tools/archive-preview/                     all maps
 *   /tools/archive-preview/?players=2&theater=desert   filtered
 *   /tools/archive-preview/?q=nothingmatches    no results
 *   /tools/archive-preview/?empty=1             empty catalogue
 *
 * The real inc/archive-helpers.php is loaded, so filter parsing, URL building
 * and the chip row are the production code. Development tool only.
 */

declare(strict_types=1);

define('ABSPATH', dirname(__DIR__, 2) . '/');
const THEME_URI = '/wp-content/themes/astra-child';
const SAMPLES   = '/tools/map-preview/samples';

define('HOUR_IN_SECONDS', 3600);
define('DAY_IN_SECONDS', 86400);

$EMPTY = isset($_GET['empty']);
if ($EMPTY) { $GLOBALS['byrm_empty'] = true; }

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
function selected($a, $b, bool $echo = true): string { $r = ((string) $a === (string) $b) ? ' selected' : ''; if ($echo) echo $r; return $r; }
function wp_strip_all_tags(string $s): string { return strip_tags($s); }
function wp_kses_post(string $s): string { return $s; }
function sanitize_key(string $s): string { return preg_replace('/[^a-z0-9_\-]/', '', strtolower($s)) ?? ''; }
function sanitize_title(string $s): string { return preg_replace('/[^a-z0-9\-]/', '', strtolower($s)) ?? ''; }
function sanitize_text_field(string $s): string { return trim(strip_tags($s)); }
function wp_unslash($v) { return $v; }
function absint($n): int { return abs((int) $n); }
function number_format_i18n($n): string { return number_format((int) $n); }
function wp_trim_words(string $t, int $n = 55, string $m = '…'): string {
    $w = preg_split('/\s+/', trim($t)); return count($w) <= $n ? $t : implode(' ', array_slice($w, 0, $n)) . $m;
}
function add_query_arg(array $args, string $url): string {
    return $url . (str_contains($url, '?') ? '&' : '?') . http_build_query($args);
}
function home_url(string $p = '/'): string { return '/' . ltrim($p, '/'); }
function get_stylesheet_directory_uri(): string { return THEME_URI; }
function get_bloginfo(string $s = '', string $f = ''): string { return $s === 'charset' ? 'UTF-8' : 'Bank of YR Maps'; }
function get_search_query(): string { return ''; }
function has_custom_logo(): bool { return false; }
function the_custom_logo(): void {}
function has_nav_menu(string $l): bool { return false; }
function wp_nav_menu(array $a = []): void {}
function is_singular(string $t = ''): bool { return false; }
function is_front_page(): bool { return false; }
function is_wp_error($t): bool { return false; }
function is_tax($t = ''): bool { return false; }
function is_paged(): bool { return (int) ($_GET['paged'] ?? 1) > 1; }
function is_post_type_archive(string $t = ''): bool { return true; }
function post_type_exists(string $t): bool { return true; }
function taxonomy_exists(string $t): bool { return in_array($t, ['map_theater','map_mode','map_tag'], true); }
function get_post_type_archive_link(string $t) { return '/tools/archive-preview/'; }
function add_action(...$a): void {}
function add_filter(...$a): void {}
function apply_filters(string $h, $v) {
    if ($h === 'byrm_header_config' && is_array($v)) { $v['discord_url'] = 'https://discord.gg/example'; }
    if ($h === 'byrm_footer_config' && is_array($v)) { $v['discord_url'] = 'https://discord.gg/example'; }
    return $v;
}
function get_transient(string $k) { return false; }
function set_transient(string $k, $v, int $t = 0): bool { return true; }
function delete_transient(string $k): bool { return true; }
function wp_count_posts(string $t) { return (object) ['publish' => isset($GLOBALS['byrm_empty']) ? 0 : 19]; }

/** Stand-in for $wpdb — only the two aggregate queries are exercised. */
final class ByrmWpdb
{
    public string $postmeta = 'wp_postmeta';
    public string $posts = 'wp_posts';
    public function prepare(string $sql, ...$a): string { return $sql; }
    public function get_var(string $sql) { return isset($GLOBALS['byrm_empty']) ? 0 : 41280; }
    public function get_results(string $sql): array {
        if (isset($GLOBALS['byrm_empty'])) { return []; }
        return [
            (object) ['players' => '2', 'total' => '7'],
            (object) ['players' => '4', 'total' => '8'],
            (object) ['players' => '6', 'total' => '3'],
            (object) ['players' => '8', 'total' => '1'],
        ];
    }
}
$GLOBALS['wpdb'] = new ByrmWpdb();

// --------------------------------------------------------------- content ----

const THEATERS = ['desert' => 'Desert', 'snow' => 'Snow', 'temperate' => 'Temperate', 'urban' => 'Urban', 'lunar' => 'Lunar'];
const MODES    = ['standard' => 'Standard', 'naval' => 'Naval', 'battle' => 'Battle'];
const MTAGS    = ['1v1' => '1v1', 'chokepoint' => 'Chokepoint', 'tournament' => 'Tournament'];

function byrm_dataset(): array
{
    if (isset($GLOBALS['byrm_empty'])) { return []; }

    $names = ['Dust Bowl Standoff','Coral Gauntlet','Siberian Crossroads','Neon District','Rust Valley',
        'Baku Oil Fields','Lunar Assembly','Frozen Delta','Harbour Watch','Crater Line','Steel Rain',
        'Red Sands','Glass Highway','Cold Front','Tiber Heights','Salt Flats','Iron Curtain','Black Ice','Last Stand'];

    $theaters = array_keys(THEATERS); $modes = array_keys(MODES); $tags = array_keys(MTAGS);
    $shots = ['minimap.jpg','shot-1.jpg','shot-2.jpg','shot-3.jpg','shot-4.jpg'];
    $out = [];

    foreach ($names as $i => $name) {
        $out[] = (object) [
            'ID' => $i + 1,
            'title' => $name,
            'slug' => strtolower(str_replace(' ', '-', $name)),
            'players' => [2, 4, 6, 8][$i % 4],
            'theater' => $theaters[$i % count($theaters)],
            'mode' => $modes[$i % count($modes)],
            'mtag' => $tags[$i % count($tags)],
            'downloads' => 300 + $i * 211,
            'thumb' => $shots[$i % count($shots)],
        ];
    }

    return $out;
}

/** Apply the same filters the real pre_get_posts would. */
function byrm_filtered_dataset(): array
{
    $f = byrm_archive_filters();

    $rows = array_filter(byrm_dataset(), static function ($m) use ($f) {
        if ($f['q'] !== '' && stripos($m->title, $f['q']) === false) { return false; }
        if ($f['players'] > 0 && $m->players !== $f['players']) { return false; }
        if ($f['theater'] !== '' && $m->theater !== $f['theater']) { return false; }
        if ($f['mode'] !== '' && $m->mode !== $f['mode']) { return false; }
        if ($f['mtag'] !== '' && $m->mtag !== $f['mtag']) { return false; }
        return true;
    });

    $rows = array_values($rows);

    if ($f['sort'] === 'title')   { usort($rows, static fn ($a, $b) => strcmp($a->title, $b->title)); }
    if ($f['sort'] === 'popular') { usort($rows, static fn ($a, $b) => $b->downloads <=> $a->downloads); }
    if ($f['sort'] === 'oldest')  { $rows = array_reverse($rows); }

    return $rows;
}

function get_terms(array $args = []) {
    if (isset($GLOBALS['byrm_empty'])) { return []; }
    $map = ['map_theater' => THEATERS, 'map_mode' => MODES, 'map_tag' => MTAGS];
    $source = $map[$args['taxonomy']] ?? [];
    $all = byrm_dataset();
    $key = ['map_theater' => 'theater', 'map_mode' => 'mode', 'map_tag' => 'mtag'][$args['taxonomy']];
    $out = [];
    foreach ($source as $slug => $name) {
        $n = count(array_filter($all, static fn ($m) => $m->$key === $slug));
        if ($n > 0) { $out[] = (object) ['slug' => $slug, 'name' => $name, 'count' => $n]; }
    }
    return $out;
}
function get_term_by(string $field, string $value, string $tax) {
    foreach (get_terms(['taxonomy' => $tax]) as $t) { if ($t->slug === $value) { return $t; } }
    return false;
}
function get_term_link($t): string { return '/theater/' . $t->slug; }
function get_the_terms($id, string $tax) {
    foreach (byrm_dataset() as $m) {
        if ((int) $m->ID !== (int) $id) { continue; }
        $slug = ['map_theater' => $m->theater, 'map_mode' => $m->mode, 'map_tag' => $m->mtag][$tax] ?? null;
        if ($slug === null) { return false; }
        $names = ['map_theater' => THEATERS, 'map_mode' => MODES, 'map_tag' => MTAGS][$tax];
        return [(object) ['slug' => $slug, 'name' => $names[$slug]]];
    }
    return false;
}

function byrm_map_meta(string $key, $id = null) {
    foreach (byrm_dataset() as $m) { if ((int) $m->ID === (int) $id) { return $key === 'players' ? (string) $m->players : ''; } }
    return '';
}
function byrm_map_downloads($id = null): int {
    foreach (byrm_dataset() as $m) { if ((int) $m->ID === (int) $id) { return $m->downloads; } }
    return 0;
}
function byrm_map_download_url($id = null): string { return '/map-download/' . $id . '/'; }

function get_permalink($p = null): string { return is_object($p) ? '/maps/' . $p->slug : '/maps/'; }
function get_the_title($p = null): string { return is_object($p) ? $p->title : ''; }
function has_post_thumbnail($id = null): bool { return true; }
function get_the_post_thumbnail($id = null, $size = 'full', array $attr = []): string {
    $file = 'shot-1.jpg';
    foreach (byrm_dataset() as $m) { if ((int) $m->ID === (int) $id) { $file = $m->thumb; } }
    $out = '<img src="' . esc_url(SAMPLES . '/' . $file) . '" width="1200" height="900"';
    foreach ($attr as $k => $v) { $out .= ' ' . $k . '="' . esc_attr((string) $v) . '"'; }
    return $out . '>';
}
function has_excerpt($id = null): bool { return true; }
function get_the_excerpt($p = null): string { return 'Hand-designed and tested in real games, with notes on how it actually plays.'; }

// The loop.
$GLOBALS['byrm_rows'] = [];
$GLOBALS['byrm_i'] = 0;
function have_posts(): bool { return $GLOBALS['byrm_i'] < count($GLOBALS['byrm_rows']); }
function the_post(): void { $GLOBALS['byrm_current'] = $GLOBALS['byrm_rows'][$GLOBALS['byrm_i']++]; }
function get_post() { return $GLOBALS['byrm_current']; }

function paginate_links(array $args = []) {
    $pages = (int) ceil(count(byrm_filtered_dataset()) / 12);
    if ($pages < 2) { return []; }
    $cur = max(1, (int) ($_GET['paged'] ?? 1));
    $out = [];
    if ($cur > 1) { $out[] = '<a class="prev page-numbers" href="?paged=' . ($cur - 1) . '">Previous</a>'; }
    for ($i = 1; $i <= $pages; $i++) {
        $out[] = $i === $cur
            ? '<span aria-current="page" class="page-numbers current">' . $i . '</span>'
            : '<a class="page-numbers" href="?paged=' . $i . '">' . $i . '</a>';
    }
    if ($cur < $pages) { $out[] = '<a class="next page-numbers" href="?paged=' . ($cur + 1) . '">Next</a>'; }
    return $out;
}

// Document shell stubs.
function language_attributes(): void { echo 'lang="en"'; }
function bloginfo(string $s = ''): void { echo $s === 'charset' ? 'UTF-8' : ''; }
function body_class(): void { echo 'class="archive post-type-archive-map byrm-fullwidth"'; }
function wp_body_open(): void {}
function wp_head(): void {
    echo '<title>Maps — Bank of YR Maps</title>' . "\n";
    foreach (['header', 'footer', 'home', 'archive'] as $s) {
        echo '<link rel="stylesheet" href="' . THEME_URI . '/assets/css/' . $s . '.css">' . "\n";
    }
    echo '<style>body{margin:0;background:#0d1117;}</style>' . "\n";
}
function wp_footer(): void {
    foreach (['header', 'footer', 'archive'] as $s) {
        echo '<script src="' . THEME_URI . '/assets/js/' . $s . '.js" defer></script>' . "\n";
    }
}

// ------------------------------------------------------------- templates ----

require ABSPATH . 'wp-content/themes/astra-child/inc/site-header.php';
require ABSPATH . 'wp-content/themes/astra-child/inc/site-footer.php';
require ABSPATH . 'wp-content/themes/astra-child/inc/home-helpers.php';
require ABSPATH . 'wp-content/themes/astra-child/inc/page-shell.php';
require ABSPATH . 'wp-content/themes/astra-child/inc/archive-helpers.php';

$rows = byrm_filtered_dataset();
$page = max(1, (int) ($_GET['paged'] ?? 1));
$GLOBALS['byrm_rows'] = array_slice($rows, ($page - 1) * 12, 12);
$GLOBALS['wp_query'] = (object) ['found_posts' => count($rows)];

require ABSPATH . 'wp-content/themes/astra-child/archive-map.php';
