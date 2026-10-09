<?php
/**
 * Astra Child — Bank of YR Maps
 *
 * @package Astra_Child
 */

defined( 'ABSPATH' ) || exit;

define( 'BYRM_CHILD_VERSION', '1.0.0' );

require_once get_stylesheet_directory() . '/inc/site-header.php';
require_once get_stylesheet_directory() . '/inc/site-footer.php';
require_once get_stylesheet_directory() . '/inc/map-helpers.php';
require_once get_stylesheet_directory() . '/inc/home-helpers.php';
require_once get_stylesheet_directory() . '/inc/page-shell.php';
require_once get_stylesheet_directory() . '/inc/archive-helpers.php';
require_once get_stylesheet_directory() . '/inc/community-helpers.php';
require_once get_stylesheet_directory() . '/inc/search-helpers.php';
require_once get_stylesheet_directory() . '/inc/guide-helpers.php';
require_once get_stylesheet_directory() . '/inc/about-helpers.php';
require_once get_stylesheet_directory() . '/inc/mod-helpers.php';
require_once get_stylesheet_directory() . '/inc/tweaker.php';

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

	// Footer. Depends on the header stylesheet for the shared .byrm-cta button.
	$footer_css = $dir . '/assets/css/footer.css';
	wp_enqueue_style(
		'byrm-footer',
		$uri . '/assets/css/footer.css',
		array( 'byrm-header' ),
		file_exists( $footer_css ) ? (string) filemtime( $footer_css ) : BYRM_CHILD_VERSION
	);

	$footer_js = $dir . '/assets/js/footer.js';
	wp_enqueue_script(
		'byrm-footer',
		$uri . '/assets/js/footer.js',
		array(),
		file_exists( $footer_js ) ? (string) filemtime( $footer_js ) : BYRM_CHILD_VERSION,
		true
	);

	// Guide pages: buttons come from home.css, the rest from guide.css.
	if ( byrm_is_guide_page() ) {
		$home_css = $dir . '/assets/css/home.css';
		wp_enqueue_style(
			'byrm-home',
			$uri . '/assets/css/home.css',
			array( 'byrm-header' ),
			file_exists( $home_css ) ? (string) filemtime( $home_css ) : BYRM_CHILD_VERSION
		);

		$guide_css = $dir . '/assets/css/guide.css';
		wp_enqueue_style(
			'byrm-guide',
			$uri . '/assets/css/guide.css',
			array( 'byrm-home' ),
			file_exists( $guide_css ) ? (string) filemtime( $guide_css ) : BYRM_CHILD_VERSION
		);
	}

	// The maps archive shares home.css for its card, button and pill
	// components, then adds its own filter rail styles.
	if ( byrm_is_map_archive() ) {
		$home_css = $dir . '/assets/css/home.css';
		wp_enqueue_style(
			'byrm-home',
			$uri . '/assets/css/home.css',
			array( 'byrm-header' ),
			file_exists( $home_css ) ? (string) filemtime( $home_css ) : BYRM_CHILD_VERSION
		);

		$archive_css = $dir . '/assets/css/archive.css';
		wp_enqueue_style(
			'byrm-archive',
			$uri . '/assets/css/archive.css',
			array( 'byrm-home' ),
			file_exists( $archive_css ) ? (string) filemtime( $archive_css ) : BYRM_CHILD_VERSION
		);

		$archive_js = $dir . '/assets/js/archive.js';
		wp_enqueue_script(
			'byrm-archive',
			$uri . '/assets/js/archive.js',
			array(),
			file_exists( $archive_js ) ? (string) filemtime( $archive_js ) : BYRM_CHILD_VERSION,
			true
		);
	}

	// The community page shares home.css for its buttons, then adds the board.
	if ( byrm_is_community_page() ) {
		$home_css = $dir . '/assets/css/home.css';
		wp_enqueue_style(
			'byrm-home',
			$uri . '/assets/css/home.css',
			array( 'byrm-header' ),
			file_exists( $home_css ) ? (string) filemtime( $home_css ) : BYRM_CHILD_VERSION
		);

		$community_css = $dir . '/assets/css/community.css';
		wp_enqueue_style(
			'byrm-community',
			$uri . '/assets/css/community.css',
			array( 'byrm-home' ),
			file_exists( $community_css ) ? (string) filemtime( $community_css ) : BYRM_CHILD_VERSION
		);

		$community_js = $dir . '/assets/js/community.js';
		wp_enqueue_script(
			'byrm-community',
			$uri . '/assets/js/community.js',
			array(),
			file_exists( $community_js ) ? (string) filemtime( $community_js ) : BYRM_CHILD_VERSION,
			true
		);

		// WordPress's own threading script, which moves the form under the
		// message being replied to. Without it the Reply links still work, they
		// just reload the page first.
		if ( comments_open() ) {
			wp_enqueue_script( 'comment-reply' );
		}
	}

	// Search results reuse the card grid, buttons and empty-state mark from
	// home.css, then add their own layout.
	if ( is_search() ) {
		$home_css = $dir . '/assets/css/home.css';
		wp_enqueue_style(
			'byrm-home',
			$uri . '/assets/css/home.css',
			array( 'byrm-header' ),
			file_exists( $home_css ) ? (string) filemtime( $home_css ) : BYRM_CHILD_VERSION
		);

		$search_css = $dir . '/assets/css/search.css';
		wp_enqueue_style(
			'byrm-search',
			$uri . '/assets/css/search.css',
			array( 'byrm-home' ),
			file_exists( $search_css ) ? (string) filemtime( $search_css ) : BYRM_CHILD_VERSION
		);
	}

	// The map tweaker reuses the buttons from home.css, then adds its own form.
	if ( byrm_is_tweaker_page() ) {
		$home_css = $dir . '/assets/css/home.css';
		wp_enqueue_style(
			'byrm-home',
			$uri . '/assets/css/home.css',
			array( 'byrm-header' ),
			file_exists( $home_css ) ? (string) filemtime( $home_css ) : BYRM_CHILD_VERSION
		);

		$tweaker_css = $dir . '/assets/css/tweaker.css';
		wp_enqueue_style(
			'byrm-tweaker',
			$uri . '/assets/css/tweaker.css',
			array( 'byrm-home' ),
			file_exists( $tweaker_css ) ? (string) filemtime( $tweaker_css ) : BYRM_CHILD_VERSION
		);

		$tweaker_js = $dir . '/assets/js/tweaker.js';
		wp_enqueue_script(
			'byrm-tweaker',
			$uri . '/assets/js/tweaker.js',
			array(),
			file_exists( $tweaker_js ) ? (string) filemtime( $tweaker_js ) : BYRM_CHILD_VERSION,
			true
		);
	}

	// Mods reuse the map and archive designs, then add the few pieces that have
	// no map equivalent.
	if ( byrm_is_mod_page() ) {
		$home_css = $dir . '/assets/css/home.css';
		wp_enqueue_style(
			'byrm-home',
			$uri . '/assets/css/home.css',
			array( 'byrm-header' ),
			file_exists( $home_css ) ? (string) filemtime( $home_css ) : BYRM_CHILD_VERSION
		);

		$deps = array( 'byrm-home' );

		if ( byrm_is_mod_archive() ) {
			$archive_css = $dir . '/assets/css/archive.css';
			wp_enqueue_style(
				'byrm-archive',
				$uri . '/assets/css/archive.css',
				array( 'byrm-home' ),
				file_exists( $archive_css ) ? (string) filemtime( $archive_css ) : BYRM_CHILD_VERSION
			);
			$deps[] = 'byrm-archive';
		}

		if ( is_singular( 'mod' ) ) {
			$map_css = $dir . '/assets/css/map.css';
			wp_enqueue_style(
				'byrm-map',
				$uri . '/assets/css/map.css',
				array( 'byrm-home' ),
				file_exists( $map_css ) ? (string) filemtime( $map_css ) : BYRM_CHILD_VERSION
			);
			$deps[] = 'byrm-map';

			// The gallery and lightbox are the same script the map page uses.
			$map_js = $dir . '/assets/js/map.js';
			wp_enqueue_script(
				'byrm-map',
				$uri . '/assets/js/map.js',
				array(),
				file_exists( $map_js ) ? (string) filemtime( $map_js ) : BYRM_CHILD_VERSION,
				true
			);
		}

		$mod_css = $dir . '/assets/css/mod.css';
		wp_enqueue_style(
			'byrm-mod',
			$uri . '/assets/css/mod.css',
			$deps,
			file_exists( $mod_css ) ? (string) filemtime( $mod_css ) : BYRM_CHILD_VERSION
		);
	}

	// The download wait page: map cards from home.css, the banner and blocks
	// from guide.css, then the countdown itself.
	if ( byrm_is_download_page() ) {
		$home_css = $dir . '/assets/css/home.css';
		wp_enqueue_style(
			'byrm-home',
			$uri . '/assets/css/home.css',
			array( 'byrm-header' ),
			file_exists( $home_css ) ? (string) filemtime( $home_css ) : BYRM_CHILD_VERSION
		);

		$guide_css = $dir . '/assets/css/guide.css';
		wp_enqueue_style(
			'byrm-guide',
			$uri . '/assets/css/guide.css',
			array( 'byrm-home' ),
			file_exists( $guide_css ) ? (string) filemtime( $guide_css ) : BYRM_CHILD_VERSION
		);

		$download_css = $dir . '/assets/css/download.css';
		wp_enqueue_style(
			'byrm-download',
			$uri . '/assets/css/download.css',
			array( 'byrm-guide' ),
			file_exists( $download_css ) ? (string) filemtime( $download_css ) : BYRM_CHILD_VERSION
		);

		$download_js = $dir . '/assets/js/download.js';
		wp_enqueue_script(
			'byrm-download',
			$uri . '/assets/js/download.js',
			array(),
			file_exists( $download_js ) ? (string) filemtime( $download_js ) : BYRM_CHILD_VERSION,
			true
		);
	}

	// The About page borrows the guide section's banner, blocks and panels, then
	// adds its own figures strip and fact list on top.
	if ( byrm_is_about_page() ) {
		$home_css = $dir . '/assets/css/home.css';
		wp_enqueue_style(
			'byrm-home',
			$uri . '/assets/css/home.css',
			array( 'byrm-header' ),
			file_exists( $home_css ) ? (string) filemtime( $home_css ) : BYRM_CHILD_VERSION
		);

		$guide_css = $dir . '/assets/css/guide.css';
		wp_enqueue_style(
			'byrm-guide',
			$uri . '/assets/css/guide.css',
			array( 'byrm-home' ),
			file_exists( $guide_css ) ? (string) filemtime( $guide_css ) : BYRM_CHILD_VERSION
		);

		$about_css = $dir . '/assets/css/about.css';
		wp_enqueue_style(
			'byrm-about',
			$uri . '/assets/css/about.css',
			array( 'byrm-guide' ),
			file_exists( $about_css ) ? (string) filemtime( $about_css ) : BYRM_CHILD_VERSION
		);
	}

	// Home page styles, front page only.
	if ( is_front_page() ) {
		$home_css = $dir . '/assets/css/home.css';
		wp_enqueue_style(
			'byrm-home',
			$uri . '/assets/css/home.css',
			array( 'byrm-header' ),
			file_exists( $home_css ) ? (string) filemtime( $home_css ) : BYRM_CHILD_VERSION
		);
	}

	// Map page assets only load on a map, so no other page pays for them.
	if ( is_singular( 'map' ) ) {
		$map_css = $dir . '/assets/css/map.css';
		wp_enqueue_style(
			'byrm-map',
			$uri . '/assets/css/map.css',
			array( 'byrm-header' ),
			file_exists( $map_css ) ? (string) filemtime( $map_css ) : BYRM_CHILD_VERSION
		);

		$map_js = $dir . '/assets/js/map.js';
		wp_enqueue_script(
			'byrm-map',
			$uri . '/assets/js/map.js',
			array(),
			file_exists( $map_js ) ? (string) filemtime( $map_js ) : BYRM_CHILD_VERSION,
			true
		);
	}
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

	$dir = get_stylesheet_directory();

	foreach ( array( '600', '700' ) as $weight ) {
		$file = '/assets/fonts/chakra-petch-' . $weight . '.woff2';

		// Skip anything not actually uploaded, so a missing font never 404s.
		if ( ! file_exists( $dir . $file ) ) {
			continue;
		}

		printf(
			'<link rel="preload" href="%s" as="font" type="font/woff2" crossorigin>' . "\n",
			esc_url( $uri . $file )
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
 * Menu locations for the three footer link columns.
 *
 * Each column falls back to a default link list until a menu is assigned, so
 * the footer is never empty.
 */
function byrm_register_menus() {
	register_nav_menus(
		array(
			'footer-maps'   => __( 'Footer — Maps', 'astra-child' ),
			'footer-guides' => __( 'Footer — Guides', 'astra-child' ),
			'footer-site'   => __( 'Footer — Site', 'astra-child' ),
		)
	);
}
add_action( 'after_setup_theme', 'byrm_register_menus' );

/**
 * Swap Astra's footer for ours, using the same self-detecting approach as the
 * header: if the callback cannot be unhooked, render at wp_footer instead and
 * hide Astra's with CSS, so two footers can never stack.
 */
function byrm_replace_astra_footer() {
	$replaced = remove_action( 'astra_footer', 'astra_footer_markup' );

	if ( $replaced ) {
		add_action( 'astra_footer', 'byrm_render_site_footer' );

		return;
	}

	add_action( 'wp_footer', 'byrm_render_site_footer', 5 );
	add_action( 'wp_head', 'byrm_hide_astra_footer_css', 20 );
}
add_action( 'init', 'byrm_replace_astra_footer' );

/**
 * Fallback only: hide Astra's footer when it could not be unhooked.
 */
function byrm_hide_astra_footer_css() {
	echo '<style id="byrm-astra-footer-fallback">'
		. '.site-footer,.ast-small-footer,.footer-adv,.ast-footer-overlay{display:none !important;}'
		. '</style>' . "\n";
}

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

/**
 * Footer settings. Mirrors the header — add your Discord invite and contact
 * address once you have them.
 */
function byrm_footer_settings( $config ) {
	$config['discord_url']   = '';   // e.g. https://discord.gg/xxxxxxx
	$config['contact_email'] = '';   // e.g. hello@bankofyrmaps.com

	return $config;
}
add_filter( 'byrm_footer_config', 'byrm_footer_settings' );

/**
 * Community page settings.
 *
 * The Discord invite is read from the header settings above, so it only needs
 * pasting once. These two are extra:
 *
 *   forum_url     — an external Command & Conquer forum to point game problems at.
 *   contact_email — shown as a private alternative to posting on the board.
 *
 * @param  array<string, mixed> $config Defaults.
 * @return array<string, mixed>
 */
function byrm_community_settings( $config ) {
	$config['forum_url']     = '';   // e.g. https://forums.cncnet.org/
	$config['contact_email'] = '';   // e.g. hello@bankofyrmaps.com

	return $config;
}
add_filter( 'byrm_community_config', 'byrm_community_settings' );

/**
 * About page settings.
 *
 * Every one of these is optional. Leave a line empty and the page omits that
 * sentence or row entirely rather than printing a gap — so it reads correctly
 * filled in or not.
 *
 *   maintainer    — your name or handle, shown as who runs the site.
 *   since         — the year you started making maps, e.g. "2019".
 *   contact_email — shown in the contact list.
 *   discord_url   — shown in the contact list.
 *   forum_url     — an external Command & Conquer forum to point people at.
 *   file_host     — where the map files live, e.g. "Datadock".
 *
 * @param  array<string, string> $config Defaults.
 * @return array<string, string>
 */
function byrm_about_settings( $config ) {
	$config['maintainer']    = 'Bank of YR Maps';
	$config['since']         = '2026';
	$config['contact_email'] = 'bankofyrmaps@gmail.com';
	$config['discord_url']   = '';   // e.g. https://discord.gg/xxxxxxx
	$config['forum_url']     = '';   // e.g. https://forums.cncnet.org/
	$config['file_host']     = 'Partner | DataDock Host';

	return $config;
}
add_filter( 'byrm_about_config', 'byrm_about_settings' );

/**
 * Image sizes for the map gallery.
 *
 * The grid frames crop to 4:3; the viewer always serves the original upload, so
 * high-resolution screenshots stay worth uploading.
 */
function byrm_image_sizes() {
	add_theme_support( 'post-thumbnails' );
	add_image_size( 'byrm-shot', 900, 675, true );
	add_image_size( 'byrm-shot-lead', 1600, 1000, true );
}
add_action( 'after_setup_theme', 'byrm_image_sizes' );

/**
 * The one background shared by every map page.
 *
 * Upload an image to the media library, copy its URL, and paste it here.
 * Leave it empty and the banner uses a CSS-only backdrop instead.
 */
function byrm_map_page_settings( $config ) {
	$config['banner_image'] = '';   // e.g. https://www.bankofyrmaps.com/wp-content/uploads/2026/01/map-banner.jpg

	return $config;
}
add_filter( 'byrm_map_config', 'byrm_map_page_settings' );

/**
 * Which templates manage their own full-width layout.
 *
 * @return bool
 */
function byrm_is_full_width_template() {
	return is_front_page()
		|| is_singular( 'map' )
		|| is_post_type_archive( 'map' )
		|| is_tax( array( 'map_theater', 'map_mode', 'map_tag' ) )
		|| byrm_is_guide_page()
		|| byrm_is_community_page()
		|| byrm_is_tweaker_page()
		|| byrm_is_about_page()
		|| byrm_is_download_page()
		|| byrm_is_mod_page()
		|| is_search();
}

/**
 * Ask Astra for a stretched, container-free layout on our templates.
 *
 * "page-builder" is the layout Astra uses for page builder content: no boxed
 * container, no padding, full width — which is what our templates expect, since
 * each section draws its own background edge to edge and centres its own content.
 *
 * @param  string $layout Astra's chosen content layout.
 * @return string
 */
function byrm_full_width_content_layout( $layout ) {
	return byrm_is_full_width_template() ? 'page-builder' : $layout;
}
add_filter( 'astra_get_content_layout', 'byrm_full_width_content_layout' );

/**
 * No sidebar on our templates either — otherwise Astra reserves a column for
 * one and the page sits in a narrow strip with dead space beside it.
 *
 * @param  string $layout Astra's chosen page layout.
 * @return string
 */
function byrm_no_sidebar_layout( $layout ) {
	return byrm_is_full_width_template() ? 'no-sidebar' : $layout;
}
add_filter( 'astra_page_layout', 'byrm_no_sidebar_layout' );

/**
 * A body class our stylesheet can key the full-width rules off, so the layout
 * is corrected even if the Astra filters above are named differently in a
 * future release.
 *
 * @param  string[] $classes Existing body classes.
 * @return string[]
 */
function byrm_body_classes( $classes ) {
	if ( byrm_is_full_width_template() ) {
		$classes[] = 'byrm-fullwidth';
	}

	return $classes;
}
add_filter( 'body_class', 'byrm_body_classes' );

/**
 * Is this the maps archive, or one of the map taxonomy archives?
 *
 * @return bool
 */
function byrm_is_map_archive() {
	return is_post_type_archive( 'map' ) || is_tax( array( 'map_theater', 'map_mode', 'map_tag' ) );
}

/**
 * Route the map taxonomy archives through archive-map.php.
 *
 * Theater, mode and tag archives are the same browse page with one filter
 * pre-applied, so they share a template instead of duplicating it three times.
 *
 * @param  string $template Template WordPress chose.
 * @return string
 */
function byrm_map_taxonomy_template( $template ) {
	if ( is_tax( array( 'map_theater', 'map_mode', 'map_tag' ) ) ) {
		$archive = locate_template( 'archive-map.php' );

		if ( $archive ) {
			return $archive;
		}
	}

	return $template;
}
add_filter( 'template_include', 'byrm_map_taxonomy_template' );

/**
 * Send guides written in the editor through guide-page.php.
 *
 * A page with a template of its own — page-install.php, page-guides.php — is
 * left alone; WordPress picked it deliberately. Everything else under /guides/
 * would otherwise fall through to Astra's default page template and lose the
 * site's design entirely.
 *
 * @param  string $template Template WordPress chose.
 * @return string
 */
function byrm_guide_template( $template ) {
	if ( ! byrm_is_guide_page() ) {
		return $template;
	}

	// page-install.php, page-guides.php and friends win.
	if ( 0 === strpos( basename( $template ), 'page-' ) ) {
		return $template;
	}

	$guide = locate_template( 'guide-page.php' );

	return $guide ? $guide : $template;
}
add_filter( 'template_include', 'byrm_guide_template' );
