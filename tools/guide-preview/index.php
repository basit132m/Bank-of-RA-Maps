<?php

/**
 * Local preview for the install guide.
 *
 *   php -S 127.0.0.1:8009 -t .
 *   /tools/guide-preview/            as published
 *   /tools/guide-preview/?extra=1    with extra content typed into the editor
 *
 * Development tool only.
 */

declare(strict_types=1);

define('ABSPATH', dirname(__DIR__, 2) . '/');
const THEME_URI = '/wp-content/themes/astra-child';
define('HOUR_IN_SECONDS', 3600);

function esc_url(string $u): string { return htmlspecialchars($u, ENT_QUOTES, 'UTF-8'); }
function esc_attr($t): string { return htmlspecialchars((string) $t, ENT_QUOTES, 'UTF-8'); }
function esc_html($t): string { return htmlspecialchars((string) $t, ENT_QUOTES, 'UTF-8'); }
function esc_html_e(string $t, string $d = ''): void { echo esc_html($t); }
function esc_attr_e(string $t, string $d = ''): void { echo esc_attr($t); }
function esc_html__(string $t, string $d = ''): string { return esc_html($t); }
function __(string $t, string $d = ''): string { return $t; }
function _n(string $s, string $p, int $n, string $d = ''): string { return $n === 1 ? $s : $p; }

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
function is_wp_error($t): bool { return false; }
function number_format_i18n($n): string { return number_format((int) $n); }
function add_action(...$a): void {}
function add_filter(...$a): void {}
function apply_filters(string $h, $v) {
    if ($h === 'byrm_header_config' && is_array($v)) { $v['discord_url'] = 'https://discord.gg/example'; }
    if ($h === 'byrm_footer_config' && is_array($v)) { $v['discord_url'] = 'https://discord.gg/example'; }
    return $v;
}
function post_type_exists(string $t): bool { return true; }
function get_post_type_archive_link(string $t) { return '/maps/'; }
function get_transient(string $k) { return false; }
function set_transient(string $k, $v, int $t = 0): bool { return true; }
function wp_count_posts(string $t) { return (object) ['publish' => 7]; }
final class ByrmWpdb {
    public string $postmeta = 'wp_postmeta'; public string $posts = 'wp_posts';
    public function prepare(string $s, ...$a): string { return $s; }
    public function get_var(string $s) { return 18432; }
}
$GLOBALS['wpdb'] = new ByrmWpdb();

// The page being rendered.
$GLOBALS['byrm_loop'] = true;
function have_posts(): bool { return $GLOBALS['byrm_loop']; }
function the_post(): void { $GLOBALS['byrm_loop'] = false; }
function get_the_ID(): int { return 42; }
function the_title(): void { echo esc_html('Installing maps'); }
function get_the_title($p = null): string { return (int) $p === 7 ? 'Guides' : 'Installing maps'; }
function wp_get_post_parent_id($id): int { return 7; }
function get_permalink($p = null): string { return '/guides/'; }
function get_the_content(): string { return isset($_GET['extra']) ? "A note typed into the page editor.\n\nIt appears underneath the designed sections." : ''; }
function the_content(): void { echo '<p>' . str_replace("\n\n", '</p><p>', esc_html(get_the_content())) . '</p>'; }

// Document shell.
function language_attributes(): void { echo 'lang="en"'; }
function bloginfo(string $s = ''): void { echo $s === 'charset' ? 'UTF-8' : ''; }
function body_class(): void { echo 'class="page byrm-fullwidth"'; }
function wp_body_open(): void {}
function wp_head(): void {
    echo '<title>Installing maps — Bank of YR Maps</title>' . "\n";
    foreach (['header', 'footer', 'home', 'guide'] as $s) {
        echo '<link rel="stylesheet" href="' . THEME_URI . '/assets/css/' . $s . '.css">' . "\n";
    }
    echo '<style>body{margin:0;background:#0d1117;}</style>' . "\n";
}
function wp_footer(): void {
    foreach (['header', 'footer'] as $s) {
        echo '<script src="' . THEME_URI . '/assets/js/' . $s . '.js" defer></script>' . "\n";
    }
}

require ABSPATH . 'wp-content/themes/astra-child/inc/site-header.php';
require ABSPATH . 'wp-content/themes/astra-child/inc/site-footer.php';
require ABSPATH . 'wp-content/themes/astra-child/inc/home-helpers.php';
require ABSPATH . 'wp-content/themes/astra-child/inc/page-shell.php';
require ABSPATH . 'wp-content/themes/astra-child/page-install.php';
