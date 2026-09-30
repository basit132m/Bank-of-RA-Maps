<?php
/**
 * Astra Child — Bank of YR Maps
 *
 * @package Astra_Child
 */

defined( 'ABSPATH' ) || exit;

define( 'BYRM_CHILD_VERSION', '1.0.0' );

require_once get_stylesheet_directory() . '/inc/site-header.php';

/**
 * Styles and scripts.
 */
function byrm_enqueue_assets() {
	$dir = get_stylesheet_directory();
	$uri = get_stylesheet_directory_uri();

	// The child stylesheet, loaded after Astra's so it can override cleanly.
	wp_enqueue_style(
		'astra-child-theme-css',
		$uri . '/style.css',
		array( 'astra-theme-css' ),
		BYRM_CHILD_VERSION
	);

	// Header styles. Versioned by file modification time so browsers pick up
	// edits immediately instead of serving a stale cached copy.
	$header_css = $dir . '/assets/css/header.css';
	wp_enqueue_style(
		'byrm-header',
		$uri . '/assets/css/header.css',
		array( 'astra-child-theme-css' ),
		file_exists( $header_css ) ? (string) filemtime( $header_css ) : BYRM_CHILD_VERSION
	);

	$header_js = $dir . '/assets/js/header.js';
	wp_enqueue_script(
		'byrm-header',
		$uri . '/assets/js/header.js',
		array(),
		file_exists( $header_js ) ? (string) filemtime( $header_js ) : BYRM_CHILD_VERSION,
		true
	);
}
add_action( 'wp_enqueue_scripts', 'byrm_enqueue_assets', 15 );

/**
 * Preload the header font files.
 *
 * They are self-hosted, so there is no third-party DNS lookup and no data sent
 * to Google. Preloading stops the nav flashing in a fallback face on first paint.
 */
function byrm_preload_fonts() {
	$uri = get_stylesheet_directory_uri();

	foreach ( array( '600', '700' ) as $weight ) {
		printf(
			'<link rel="preload" href="%s" as="font" type="font/woff2" crossorigin>' . "\n",
			esc_url( $uri . '/assets/fonts/chakra-petch-' . $weight . '.woff2' )
		);
	}
}
add_action( 'wp_head', 'byrm_preload_fonts', 1 );

/**
 * Swap Astra's header for ours.
 *
 * Astra renders its header by hooking astra_header_markup() onto the
 * `astra_header` action. Removing that and hooking our own in its place keeps
 * our header inside Astra's own wrappers, which is the tidiest result.
 *
 * If a future Astra release moves that callback, remove_action() returns false.
 * Rather than silently rendering two headers, we fall back to printing ours at
 * wp_body_open and hiding Astra's with CSS.
 */
function byrm_replace_astra_header() {
	$replaced = remove_action( 'astra_header', 'astra_header_markup' );

	if ( $replaced ) {
		add_action( 'astra_header', 'byrm_render_site_header' );

		return;
	}

	add_action( 'wp_body_open', 'byrm_render_site_header' );
	add_action( 'wp_head', 'byrm_hide_astra_header_css', 20 );
}
add_action( 'init', 'byrm_replace_astra_header' );

/**
 * Fallback only: hide Astra's header when it could not be unhooked.
 */
function byrm_hide_astra_header_css() {
	echo '<style id="byrm-astra-header-fallback">'
		. '.site-header,.ast-above-header,.ast-main-header-wrap,.ast-below-header{display:none !important;}'
		. '</style>' . "\n";
}

/**
 * Point the header's configurable bits at real values.
 *
 * Edit these two lines once you have a Discord invite — nothing else needs
 * touching, and the strip hides the Discord link while the URL is empty.
 */
function byrm_header_settings( $config ) {
	$config['discord_url'] = '';                        // e.g. https://discord.gg/xxxxxxx
	$config['cta_url']     = '/maps';
	$config['cta_label']   = 'Browse maps';

	return $config;
}
add_filter( 'byrm_header_config', 'byrm_header_settings' );
