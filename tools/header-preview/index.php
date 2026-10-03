<?php

/**
 * Local preview harness for the site header.
 *
 * Renders the real inc/site-header.php template with the real stylesheet, by
 * stubbing the handful of WordPress functions it calls. This exists so the
 * header can be checked in a browser without a WordPress install — it is a
 * development tool and is never deployed.
 *
 *   php -S 127.0.0.1:8001 -t . 
 *   open http://127.0.0.1:8001/tools/header-preview/
 */

declare(strict_types=1);

define('ABSPATH', dirname(__DIR__, 2) . '/');

const THEME_URI = '/wp-content/themes/astra-child';

// --------------------------------------------------------- WordPress stubs ---

function esc_url(string $url): string      { return htmlspecialchars($url, ENT_QUOTES, 'UTF-8'); }
function esc_attr(string $text): string    { return htmlspecialchars($text, ENT_QUOTES, 'UTF-8'); }
function esc_html(string $text): string    { return htmlspecialchars($text, ENT_QUOTES, 'UTF-8'); }
function esc_html_e(string $text, string $domain = ''): void { echo esc_html($text); }
function esc_attr_e(string $text, string $domain = ''): void { echo esc_attr($text); }

function home_url(string $path = '/'): string { return '/' . ltrim($path, '/'); }
function get_stylesheet_directory_uri(): string { return THEME_URI; }
function get_bloginfo(string $show = '', string $filter = ''): string { return 'Bank of YR Maps'; }
function get_search_query(): string { return ''; }

// ?customlogo=1 reproduces a WordPress Customizer logo, which renders its own
// <a class="custom-logo-link"> wrapper and carries Astra's sizing CSS.
function has_custom_logo(): bool { return isset($_GET['customlogo']); }
function post_type_exists(string $type): bool { return isset($_GET['stats']); }
function wp_count_posts(string $type): object { return (object) ['publish' => (int) ($_GET['stats'] ?? 0)]; }
function number_format_i18n(int $n): string { return number_format($n); }
function the_custom_logo(): void
{
    printf(
        '<a href="/" class="custom-logo-link" rel="home"><img src="%s" class="custom-logo"'
        . ' alt="Bank of YR Maps" width="2000" height="668"></a>',
        THEME_URI . '/assets/img/logo.webp'
    );
}
function has_nav_menu(string $location): bool { return false; }
function wp_nav_menu(array $args = []): void {}

/** Applies the same filter functions.php registers, so previews match production. */
function apply_filters(string $hook, mixed $value): mixed
{
    if ($hook === 'byrm_header_config' && is_array($value)) {
        // Mirrors byrm_header_settings() in functions.php.
        $value['discord_url'] = $_GET['discord'] ?? 'https://discord.gg/example';
        $value['cta_url']     = '/maps';
        $value['cta_label']   = 'Browse maps';
    }

    if ($hook === 'byrm_footer_config' && is_array($value)) {
        // Mirrors byrm_footer_settings() in functions.php.
        $value['discord_url']   = $_GET['discord'] ?? 'https://discord.gg/example';
        $value['contact_email'] = 'hello@bankofyrmaps.com';
    }

    return $value;
}

require dirname(__DIR__, 2) . '/wp-content/themes/astra-child/inc/site-header.php';
require dirname(__DIR__, 2) . '/wp-content/themes/astra-child/inc/site-footer.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Header preview — Bank of YR Maps</title>
<link rel="stylesheet" href="<?= THEME_URI ?>/assets/css/header.css">
<link rel="stylesheet" href="<?= THEME_URI ?>/assets/css/footer.css">
<?php if (isset($_GET['customlogo'])): ?>
<style id="fake-astra-logo-css">
  /* Stands in for the inline CSS Astra generates from the Customizer logo
     width, which is what our own rules have to beat. */
  .custom-logo-link img { max-width: 305px; height: auto; }
</style>
<?php endif; ?>
<style>
  /* Stand-in for the page body, so scroll behaviour can be checked. */
  body { margin: 0; background: #0d1117; color: #c3ced8;
         font: 16px/1.7 system-ui, sans-serif; }
  .demo { max-width: 1240px; margin-inline: auto; padding: 64px 20px 120px; }
  .demo h1 { font-family: "Chakra Petch", sans-serif; color: #f4f7f9;
             font-size: 34px; margin: 0 0 16px; }
  .demo p { max-width: 65ch; }
  .demo__filler { height: 1200px; display: grid; place-items: center;
                  margin-top: 48px; border: 1px dashed #26303c; color: #7c8894; }
</style>
</head>
<body>
<?php byrm_render_site_header(); ?>

<main class="demo" id="content">
  <h1>Header preview</h1>
  <p>This page exists only to check the header. Scroll down to see it condense,
     resize the window to check the mobile panel, and use the search icon to
     open the drawer.</p>
  <div class="demo__filler">page content</div>
</main>

<?php byrm_render_site_footer(); ?>

<script src="<?= THEME_URI ?>/assets/js/header.js" defer></script>
<script src="<?= THEME_URI ?>/assets/js/footer.js" defer></script>
</body>
</html>
