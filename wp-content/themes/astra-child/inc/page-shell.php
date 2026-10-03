<?php
/**
 * Standalone document shell for our own templates.
 *
 * Astra wraps page content in a boxed container and reserves a sidebar column.
 * Overriding that from CSS means guessing at selectors that differ between
 * Astra versions and Customizer settings, which is fragile. Our templates draw
 * their own full-bleed sections and centre their own content, so instead of
 * fighting the wrapper they skip it: these two functions emit the document
 * directly, and Astra's page markup never appears.
 *
 * Everything WordPress needs still runs — wp_head(), wp_body_open(), body_class()
 * and wp_footer() — so plugins, the admin bar and enqueued assets all behave
 * normally. Astra's stylesheet still loads; it simply has no wrappers to style.
 *
 * @package Astra_Child
 */

defined( 'ABSPATH' ) || exit;

/**
 * Open the document and render the site header.
 *
 * Used in place of get_header() by front-page.php and single-map.php.
 */
function byrm_open_document() {
	?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<link rel="profile" href="https://gmpg.org/xfn/11">
	<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php
	wp_body_open();

	// Guarded internally, so this is a no-op if wp_body_open already rendered it.
	byrm_render_site_header();
}

/**
 * Render the site footer and close the document.
 *
 * Used in place of get_footer().
 */
function byrm_close_document() {
	byrm_render_site_footer();

	// Runs the admin bar, the image viewer markup and anything plugins add.
	wp_footer();
	?>
</body>
</html>
<?php
}
