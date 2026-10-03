<?php

/**
 * Local preview for the About page.
 *
 *   php -S 127.0.0.1:8016 -t .
 *
 *   /tools/about-preview/            nothing configured yet (every optional line empty)
 *   /tools/about-preview/?filled=1   name, year, host and all three contacts set
 *   /tools/about-preview/?new=1      brand new site: no maps, no guides, no figures
 *   /tools/about-preview/?editor=1   with copy typed into the block editor
 *
 * Development tool only.
 */

declare(strict_types=1);

define('ABSPATH', dirname(__DIR__, 2) . '/');
const THEME_URI = '/wp-content/themes/astra-child';
define('HOUR_IN_SECONDS', 3600);
define('MINUTE_IN_SECONDS', 60);

$FILLED = isset($_GET['filled']);
$NEW    = isset($_GET['new']);
$EDITOR = isset($_GET['editor']);

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
function wp_strip_all_tags($t, bool $b = false): string { return trim(strip_tags((string) $t)); }

function wp_parse_args($args, array $defaults = []): array
{
    return array_merge($defaults, is_array($args) ? $args : []);
}

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
    if ('byrm_header_config' === $h && is_array($v)) { $v['discord_url'] = 'https://discord.gg/example'; }
    if ('byrm_footer_config' === $h && is_array($v)) { $v['discord_url'] = 'https://discord.gg/example'; }

    if ('byrm_about_config' === $h && is_array($v) && !empty($GLOBALS['BYRM_FILLED'])) {
        $v['maintainer']    = 'Basit';
        $v['since']         = '2019';
        $v['contact_email'] = 'hello@bankofyrmaps.com';
        $v['discord_url']   = 'https://discord.gg/example';
        $v['forum_url']     = 'https://forums.cncnet.org/';
        $v['file_host']     = 'Datadock';
    }

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
function is_singular(string $t = ''): bool { return true; }
function is_front_page(): bool { return false; }
function is_search(): bool { return false; }
function is_page($p = ''): bool { return true; }
function is_wp_error($t): bool { return false; }
function post_type_exists(string $t): bool { return true; }
function taxonomy_exists(string $t): bool { return empty($GLOBALS['BYRM_NEW']); }
function get_post_type_archive_link(string $t) { return '/maps/'; }
function locate_template($t) { return ''; }

function wp_count_terms($args = [])
{
    return empty($GLOBALS['BYRM_NEW']) ? 4 : 0;
}

// byrm_catalogue_totals() caches into a transient, so feeding it here keeps the
// harness from needing a $wpdb stub for the downloads sum.
function get_transient(string $k)
{
    if ('byrm_catalogue_totals' === $k) {
        return empty($GLOBALS['BYRM_NEW'])
            ? ['maps' => 7, 'downloads' => 1843]
            : ['maps' => 0, 'downloads' => 0];
    }

    return false;
}
function set_transient(string $k, $v, int $t = 0): bool { return true; }
function wp_count_posts(string $t) { return (object) ['publish' => 7]; }

/* ------------------------------------------------------------- the content */

final class ByrmPage
{
    public function __construct(
        public int $ID,
        public string $post_name,
        public string $title,
        public string $post_content = ''
    ) {}
}

const GUIDES_ID = 7;
const ABOUT_ID  = 12;

$CHILDREN = [
    new ByrmPage(8,  'install',      'Installing maps'),
    new ByrmPage(9,  'cncnet-setup', 'Setting up CnCNet'),
    new ByrmPage(10, 'ra2-with-yr',  'Playing RA2 with Yuri maps'),
];

$GLOBALS['BYRM_CHILDREN'] = $NEW ? [] : $CHILDREN;

function get_page_by_path(string $p) { return 'guides' === $p ? (object) ['ID' => GUIDES_ID] : (object) ['ID' => ABOUT_ID]; }
function get_queried_object_id(): int { return ABOUT_ID; }
function get_post_ancestors($id): array { return []; }
function get_posts(array $args): array { return $GLOBALS['BYRM_CHILDREN']; }
function has_excerpt($p = null): bool { return false; }
function get_the_excerpt($p = null): string { return ''; }
function get_permalink($p = null): string { return '/about/'; }
function get_the_title($p = null): string { return is_object($p) ? $p->title : 'About us'; }
function get_post($p = null) { return $GLOBALS['BYRM_CURRENT']; }
function get_the_ID(): int { return ABOUT_ID; }
function the_title(): void { echo esc_html($GLOBALS['BYRM_CURRENT']->title); }
function get_the_content(): string { return $GLOBALS['BYRM_CURRENT']->post_content; }
function the_content(): void
{
    $c = get_the_content();
    echo '' === $c ? '' : '<p>' . esc_html($c) . '</p><p>A second paragraph, to check the prose rhythm against the blocks above it.</p>';
}

$GLOBALS['byrm_loop'] = true;
function have_posts(): bool { return $GLOBALS['byrm_loop']; }
function the_post(): void { $GLOBALS['byrm_loop'] = false; }

/* ---------------------------------------------------------- document shell */

function language_attributes(): void { echo 'lang="en"'; }
function bloginfo(string $s = ''): void { echo 'charset' === $s ? 'UTF-8' : ''; }
function body_class(): void { echo 'class="page byrm-fullwidth"'; }
function wp_body_open(): void {}

function wp_head(): void
{
    echo '<title>About us — Bank of YR Maps</title>' . "\n";
    foreach (['header', 'footer', 'home', 'guide', 'about'] as $s) {
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

$GLOBALS['BYRM_FILLED'] = $FILLED;
$GLOBALS['BYRM_NEW']    = $NEW;

$GLOBALS['BYRM_CURRENT'] = new ByrmPage(
    ABOUT_ID,
    'about',
    'About us',
    $EDITOR ? 'Typed into the block editor: this paragraph proves extra copy can be added without touching the template.' : ''
);

require ABSPATH . 'wp-content/themes/astra-child/inc/site-header.php';
require ABSPATH . 'wp-content/themes/astra-child/inc/site-footer.php';
require ABSPATH . 'wp-content/themes/astra-child/inc/home-helpers.php';
require ABSPATH . 'wp-content/themes/astra-child/inc/page-shell.php';
require ABSPATH . 'wp-content/themes/astra-child/inc/guide-helpers.php';
require ABSPATH . 'wp-content/themes/astra-child/inc/about-helpers.php';
require ABSPATH . 'wp-content/themes/astra-child/page-about.php';
