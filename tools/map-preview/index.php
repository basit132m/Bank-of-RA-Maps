<?php

/**
 * Local preview for the single map template.
 *
 * Renders the real single-map.php, map-helpers.php, header and footer against
 * stubbed WordPress functions, so the gallery, viewer and sidebar can be checked
 * in a browser without a WordPress install. Development tool only.
 *
 *   php -S 127.0.0.1:8004 -t .
 *   open http://127.0.0.1:8004/tools/map-preview/
 */

declare(strict_types=1);

define('ABSPATH', dirname(__DIR__, 2) . '/');
const THEME_URI = '/wp-content/themes/astra-child';
const SAMPLES   = '/tools/map-preview/samples';

// Sample attachment table: id => [file, width, height, caption, alt].
const SHOTS = [
    11 => ['minimap.jpg', 2400, 2400, 'Minimap render at full size.', 'Minimap of Dust Bowl Standoff'],
    12 => ['shot-1.jpg', 2400, 1800, 'The central ramp, looking north from the southern plateau.', 'Central ramp'],
    13 => ['shot-2.jpg', 2400, 1800, 'Northern ore field and the two oil derricks beside it.', 'Northern ore field'],
    14 => ['shot-3.jpg', 2400, 1800, 'Southern base position at game start.', 'Southern base'],
    15 => ['shot-4.jpg', 2400, 1800, '', 'Eastern approach'],
    // Extra ids reusing the same files, so ?shots=N can exercise any gallery size.
    16 => ['shot-1.jpg', 2400, 1800, '', 'Extra one'],
    17 => ['shot-2.jpg', 2400, 1800, '', 'Extra two'],
    18 => ['shot-3.jpg', 2400, 1800, '', 'Extra three'],
    19 => ['shot-4.jpg', 2400, 1800, '', 'Extra four'],
    20 => ['shot-1.jpg', 2400, 1800, '', 'Extra five'],
];

// ---------------------------------------------------------- WordPress stubs --

function esc_url(string $u): string { return htmlspecialchars($u, ENT_QUOTES, 'UTF-8'); }
function esc_url_raw(string $u): string { return $u; }
function esc_attr($t): string { return htmlspecialchars((string) $t, ENT_QUOTES, 'UTF-8'); }
function esc_html($t): string { return htmlspecialchars((string) $t, ENT_QUOTES, 'UTF-8'); }
function esc_textarea($t): string { return esc_html($t); }
function esc_html_e(string $t, string $d = ''): void { echo esc_html($t); }
function esc_attr_e(string $t, string $d = ''): void { echo esc_attr($t); }
function esc_html__(string $t, string $d = ''): string { return esc_html($t); }
function __(string $t, string $d = ''): string { return $t; }

function home_url(string $p = '/'): string { return '/' . ltrim($p, '/'); }
function get_stylesheet_directory_uri(): string { return THEME_URI; }
function get_bloginfo(string $s = '', string $f = ''): string { return 'Bank of YR Maps'; }
function get_search_query(): string { return ''; }
function has_custom_logo(): bool { return false; }
function the_custom_logo(): void {}
function has_nav_menu(string $l): bool { return false; }
function wp_nav_menu(array $a = []): void {}
function is_singular(string $t = ''): bool { return true; }
function is_wp_error($t): bool { return false; }
function number_format_i18n($n): string { return number_format((int) $n); }
function size_format(int $b, int $d = 0): string { return round($b / 1024, $d) . ' KB'; }
function wpautop(string $t): string { return '<p>' . str_replace("\n\n", '</p><p>', trim($t)) . '</p>'; }
function wp_list_pluck(array $rows, string $field): array {
    return array_map(static fn ($r) => is_object($r) ? $r->{$field} : $r[$field], $rows);
}

function apply_filters(string $hook, $value) {
    if ($hook === 'byrm_map_config' && is_array($value)) {
        $value['banner_image'] = isset($_GET['banner']) ? SAMPLES . '/shot-2.jpg' : '';
    }
    if ($hook === 'byrm_header_config' && is_array($value)) {
        $value['discord_url'] = 'https://discord.gg/example';
    }
    if ($hook === 'byrm_footer_config' && is_array($value)) {
        $value['discord_url'] = 'https://discord.gg/example';
    }
    return $value;
}
function add_action(...$a): void {}
function add_filter(...$a): void {}
function post_type_exists(string $t): bool { return false; }

// ------------------------------------------------------------- the post ----

$GLOBALS['byrm_loop'] = true;
function have_posts(): bool { return $GLOBALS['byrm_loop']; }
function the_post(): void { $GLOBALS['byrm_loop'] = false; }
function get_the_ID(): int { return 7; }
function the_title(): void { echo esc_html('Dust Bowl Standoff'); }
function get_the_title($p = null): string { return is_object($p) ? $p->title : 'Dust Bowl Standoff'; }
function get_the_date(): string { return '21 August 2026'; }
function get_permalink($p = null): string { return is_object($p) ? '/maps/' . $p->slug : '/maps/dust-bowl-standoff'; }
function get_post_type_archive_link(string $t): string { return '/maps'; }

function get_the_content(): string
{
    return "Built for players who want the fight to start early.\n\n"
        . "The two bases sit on opposite plateaus with a single wide ramp between them, so there is "
        . "exactly one ground approach and both players can see it. Expect contact by the four minute mark.\n\n"
        . "Position notes: the northern start has slightly better ore proximity, the southern start has "
        . "the shorter walk to the oil derricks.";
}
function the_content(): void { echo wpautop(esc_html(get_the_content())); }

// ------------------------------------------------------------ media ----

function get_post_thumbnail_id($id = null): int { return 11; }
function has_post_thumbnail($id = null): bool { return true; }

function wp_get_attachment_image_src(int $id, string $size = 'full')
{
    if (! isset(SHOTS[$id])) { return false; }
    [$file, $w, $h] = SHOTS[$id];
    return [SAMPLES . '/' . $file, $w, $h];
}

function wp_get_attachment_image(int $id, $size = 'full', bool $icon = false, array $attr = []): string
{
    if (! isset(SHOTS[$id])) { return ''; }
    [$file, $w, $h] = SHOTS[$id];
    $out = '<img src="' . esc_url(SAMPLES . '/' . $file) . '" width="' . $w . '" height="' . $h . '"';
    foreach ($attr as $k => $v) { $out .= ' ' . $k . '="' . esc_attr((string) $v) . '"'; }
    return $out . '>';
}

function get_the_post_thumbnail($id = null, $size = 'full', array $attr = []): string
{
    return wp_get_attachment_image(11, $size, false, $attr);
}

function wp_get_attachment_caption(int $id): string { return SHOTS[$id][3] ?? ''; }
function get_post_meta(int $id, string $key, bool $single = false)
{
    if ($key === '_wp_attachment_image_alt') { return SHOTS[$id][4] ?? ''; }
    return '';
}

// ------------------------------------------------- map fields and terms ----

/**
 * ?shots=N renders a gallery of exactly N images (the featured image counts as
 * the first), so every gallery size can be checked for trailing grid gaps.
 */
function byrm_map_gallery($id = null): array
{
    if (isset($_GET['single'])) { return []; }

    if (isset($_GET['shots'])) {
        $n = max(1, min(10, (int) $_GET['shots']));

        // The template prepends the featured image, so ask for one fewer.
        return array_slice([12, 13, 14, 15, 16, 17, 18, 19, 20], 0, $n - 1);
    }

    return [12, 13, 14, 15];
}

function byrm_map_meta(string $key, $id = null)
{
    return [
        'players' => '2', 'version' => '1.2', 'ore_density' => 'medium',
        'cncnet_ready' => '1', 'designer' => 'Basit', 'install_notes' => '',
    ][$key] ?? '';
}

// ?nolink=1 renders the map as it looks before a download link has been pasted in.
function byrm_map_file_url($id = null): string { return isset($_GET['nolink']) ? '' : 'https://datadock-host.site/d/a1b2c3/arctic-crossroads.yrm'; }
function byrm_map_download_url($id = null): string { return byrm_map_file_url($id) ? '/map-download/7/' : ''; }
function byrm_map_file_size($id = null): string { return '48.2 KB'; }
function byrm_map_file_type($id = null): string { return '.yrm'; }
function byrm_map_file_host($id = null): string { return byrm_map_file_url($id) ? 'datadock-host.site' : ''; }
function byrm_map_downloads($id = null): int { return 1473; }

function get_the_terms($id, string $tax)
{
    $terms = [
        'map_theater' => [(object) ['name' => 'Desert', 'slug' => 'desert']],
        'map_mode'    => [(object) ['name' => 'Standard', 'slug' => 'standard']],
        'map_tag'     => [
            (object) ['name' => '1v1', 'slug' => '1v1'],
            (object) ['name' => 'Chokepoint', 'slug' => 'chokepoint'],
            (object) ['name' => 'Tournament', 'slug' => 'tournament'],
        ],
    ];
    return $terms[$tax] ?? false;
}
function get_term_link($term): string { return '/map-tag/' . $term->slug; }

// Honours posts_per_page and post__not_in, so a change to how many related
// maps are asked for actually shows up here. Returning a fixed three made this
// stub blind to the limit entirely.
function get_posts(array $args = []): array
{
    $all = [
        (object) ['ID' => 8,  'title' => 'Coral Gauntlet', 'slug' => 'coral-gauntlet'],
        (object) ['ID' => 9,  'title' => 'Baku Oil Fields', 'slug' => 'baku-oil-fields'],
        (object) ['ID' => 10, 'title' => 'Siberian Crossroads', 'slug' => 'siberian-crossroads'],
        (object) ['ID' => 11, 'title' => 'Dead Man\'s Gulch', 'slug' => 'dead-mans-gulch'],
        (object) ['ID' => 12, 'title' => 'Arctic Shelf', 'slug' => 'arctic-shelf'],
        (object) ['ID' => 13, 'title' => 'Tiber Delta', 'slug' => 'tiber-delta'],
    ];

    $skip = array_map('intval', (array) ($args['post__not_in'] ?? []));
    $all  = array_values(array_filter($all, static fn($m) => !in_array($m->ID, $skip, true)));

    $per = (int) ($args['posts_per_page'] ?? 3);

    return $per > 0 ? array_slice($all, 0, $per) : $all;
}

// ------------------------------------------------------------- template ----

require dirname(__DIR__, 2) . '/wp-content/themes/astra-child/inc/site-header.php';
require dirname(__DIR__, 2) . '/wp-content/themes/astra-child/inc/site-footer.php';
require dirname(__DIR__, 2) . '/wp-content/themes/astra-child/inc/map-helpers.php';

function byrm_open_document(): void
{
    ?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Dust Bowl Standoff — Bank of YR Maps</title>
<link rel="stylesheet" href="<?= THEME_URI ?>/assets/css/header.css">
<link rel="stylesheet" href="<?= THEME_URI ?>/assets/css/footer.css">
<link rel="stylesheet" href="<?= THEME_URI ?>/assets/css/map.css">
<style>body { margin: 0; background: #0d1117; }</style>
</head>
<body>
<?php
    byrm_render_site_header();
}

function byrm_close_document(): void
{
    byrm_render_site_footer();
    byrm_render_lightbox();
    ?>
<script src="<?= THEME_URI ?>/assets/js/header.js" defer></script>
<script src="<?= THEME_URI ?>/assets/js/footer.js" defer></script>
<script src="<?= THEME_URI ?>/assets/js/map.js" defer></script>
</body>
</html>
<?php
}

require dirname(__DIR__, 2) . '/wp-content/themes/astra-child/single-map.php';
