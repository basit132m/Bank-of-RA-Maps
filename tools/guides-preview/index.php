<?php

/**
 * Local preview for the guides index and a guide written in the editor.
 *
 *   php -S 127.0.0.1:8014 -t .
 *
 *   /tools/guides-preview/              index: start-here plus three guides
 *   /tools/guides-preview/?only=1       index with just the install guide
 *   /tools/guides-preview/?none=1       index with nothing filed under it
 *   /tools/guides-preview/?guide=1      a guide written in the block editor
 *   /tools/guides-preview/?guide=1&last=1   the last guide (no "next")
 *
 * Development tool only.
 */

declare(strict_types=1);

define('ABSPATH', dirname(__DIR__, 2) . '/');
const THEME_URI = '/wp-content/themes/astra-child';
define('HOUR_IN_SECONDS', 3600);
define('MINUTE_IN_SECONDS', 60);

$ONLY  = isset($_GET['only']);
$NONE  = isset($_GET['none']);
$GUIDE = isset($_GET['guide']);
$LAST  = isset($_GET['last']);

/* ---------------------------------------------------------------- escaping */

function esc_url(string $u): string { return htmlspecialchars($u, ENT_QUOTES, 'UTF-8'); }
function esc_attr($t): string { return htmlspecialchars((string) $t, ENT_QUOTES, 'UTF-8'); }
function esc_html($t): string { return htmlspecialchars((string) $t, ENT_QUOTES, 'UTF-8'); }
function esc_html_e(string $t, string $d = ''): void { echo esc_html($t); }
function esc_attr_e(string $t, string $d = ''): void { echo esc_attr($t); }
function esc_html__(string $t, string $d = ''): string { return esc_html($t); }
function __(string $t, string $d = ''): string { return $t; }
function _n(string $s, string $p, int $n, string $d = ''): string { return $n === 1 ? $s : $p; }
function wp_strip_all_tags($t, bool $b = false): string { return trim(strip_tags((string) $t)); }
function number_format_i18n($n): string { return number_format((int) $n); }
function absint($n): int { return abs((int) $n); }

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
function get_post_type_archive_link(string $t) { return '/maps/'; }
function get_transient(string $k) { return false; }
function set_transient(string $k, $v, int $t = 0): bool { return true; }
function wp_count_posts(string $t) { return (object) ['publish' => 7]; }

/* ------------------------------------------------------------- the guides */

final class ByrmPage
{
    public function __construct(
        public int $ID,
        public string $post_name,
        public string $title,
        public string $post_content = '',
        public string $excerpt = ''
    ) {}
}

const GUIDES_ID = 7;

$CHILDREN = [
    new ByrmPage(8,  'install',       'Installing maps', '', ''),
    new ByrmPage(9,  'cncnet-setup',  'Setting up CnCNet', 'CnCNet is how almost everyone plays online now.', 'Download it, point it at your game folder, and you are on the ladder in about five minutes.'),
    new ByrmPage(10, 'ra2-with-yr',   'Playing RA2 with Yuri maps', 'There is a Red Alert 2 option inside CnCNet.', 'Including a BRUTAL AI setting that is considerably harder than the standard one.'),
    new ByrmPage(11, 'making-maps',   'Making your own map', 'The Final Alert 2 editor is still the tool everybody uses.', 'Where to get it, how to lay out starting positions, and what makes a map fair.'),
];

if ($ONLY) { $CHILDREN = [$CHILDREN[0]]; }
if ($NONE) { $CHILDREN = []; }

$GLOBALS['BYRM_CHILDREN'] = $CHILDREN;

function get_page_by_path(string $p) { return (object) ['ID' => GUIDES_ID]; }
function get_queried_object_id(): int { return $GLOBALS['BYRM_CURRENT']->ID; }
function get_post_ancestors($id): array { return (int) $id === GUIDES_ID ? [] : [GUIDES_ID]; }
function get_posts(array $args): array { return $GLOBALS['BYRM_CHILDREN']; }
function has_excerpt($p = null): bool
{
    $post = is_object($p) ? $p : $GLOBALS['BYRM_CURRENT'];
    return ($post->excerpt ?? '') !== '';
}
function get_the_excerpt($p = null): string
{
    $post = is_object($p) ? $p : $GLOBALS['BYRM_CURRENT'];
    return $post->excerpt ?? '';
}
function get_permalink($p = null): string
{
    $post = is_object($p) ? $p : (is_int($p) ? null : $GLOBALS['BYRM_CURRENT']);
    if ($post === null) { return '/guides/'; }
    return $post->ID === GUIDES_ID ? '/guides/' : '/guides/' . $post->post_name . '/';
}
function get_the_title($p = null): string
{
    if (is_object($p)) { return $p->title; }
    if ((int) $p === GUIDES_ID) { return 'Guides'; }
    return $GLOBALS['BYRM_CURRENT']->title;
}
function wp_get_post_parent_id($id): int { return (int) $id === GUIDES_ID ? 0 : GUIDES_ID; }
function get_post($p = null) { return $GLOBALS['BYRM_CURRENT']; }
function get_the_ID(): int { return $GLOBALS['BYRM_CURRENT']->ID; }
function the_title(): void { echo esc_html($GLOBALS['BYRM_CURRENT']->title); }
function get_the_content(): string { return $GLOBALS['BYRM_CURRENT']->post_content; }
function the_content(): void
{
    $c = get_the_content();
    echo $c === '' ? '' : '<p>' . esc_html($c) . '</p><p>More body copy would follow here, written in the block editor.</p>';
}

$GLOBALS['byrm_loop'] = true;
function have_posts(): bool { return $GLOBALS['byrm_loop']; }
function the_post(): void { $GLOBALS['byrm_loop'] = false; }

/* ---------------------------------------------------------- document shell */

function language_attributes(): void { echo 'lang="en"'; }
function bloginfo(string $s = ''): void { echo $s === 'charset' ? 'UTF-8' : ''; }
function body_class(): void { echo 'class="page byrm-fullwidth"'; }
function wp_body_open(): void {}
function wp_head(): void
{
    echo '<title>Guides — Bank of YR Maps</title>' . "\n";
    foreach (['header', 'footer', 'home', 'guide'] as $s) {
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

// Which page is being rendered.
if ($GUIDE) {
    $GLOBALS['BYRM_CURRENT'] = $LAST ? end($CHILDREN) : $CHILDREN[1];
} else {
    $GLOBALS['BYRM_CURRENT'] = new ByrmPage(GUIDES_ID, 'guides', 'Guides');
}

require ABSPATH . 'wp-content/themes/astra-child/inc/site-header.php';
require ABSPATH . 'wp-content/themes/astra-child/inc/site-footer.php';
require ABSPATH . 'wp-content/themes/astra-child/inc/home-helpers.php';
require ABSPATH . 'wp-content/themes/astra-child/inc/page-shell.php';
require ABSPATH . 'wp-content/themes/astra-child/inc/guide-helpers.php';
require ABSPATH . 'wp-content/themes/astra-child/' . ($GUIDE ? 'guide-page.php' : 'page-guides.php');
