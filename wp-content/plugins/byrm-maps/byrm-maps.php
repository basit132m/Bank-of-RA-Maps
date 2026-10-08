<?php
/**
 * Plugin Name:       Bank of YR Maps — Map Library
 * Description:       The Map post type, its specification fields, screenshot gallery and counted downloads. Kept in a plugin so the map catalogue survives any theme change.
 * Version:           1.0.0
 * Requires at least: 6.0
 * Requires PHP:      7.4
 * Author:            Bank of YR Maps
 * Text Domain:       byrm-maps
 *
 * @package BYRM_Maps
 */

defined( 'ABSPATH' ) || exit;

define( 'BYRM_MAPS_VERSION', '1.0.0' );
define( 'BYRM_MAPS_FILE', __FILE__ );

/**
 * Every specification field, in one place.
 *
 * The admin panel, the save routine and the template all read this, so a new
 * field only has to be added here.
 *
 * @return array<string, array<string, mixed>>
 */
function byrm_map_fields() {
	return apply_filters(
		'byrm_map_fields',
		array(
			'players'         => array(
				'label' => __( 'Players', 'byrm-maps' ),
				'type'  => 'number',
				'min'   => 2,
				'max'   => 8,
				'hint'  => __( 'Maximum number of players.', 'byrm-maps' ),
			),
			'version'         => array(
				'label'   => __( 'Version', 'byrm-maps' ),
				'type'    => 'text',
				'default' => '1.0',
			),
			'ore_density'     => array(
				'label'   => __( 'Ore density', 'byrm-maps' ),
				'type'    => 'select',
				'options' => array(
					''       => __( '— not set —', 'byrm-maps' ),
					'low'    => __( 'Low', 'byrm-maps' ),
					'medium' => __( 'Medium', 'byrm-maps' ),
					'high'   => __( 'High', 'byrm-maps' ),
				),
			),
			'cncnet_ready'    => array(
				'label' => __( 'CnCNet ready', 'byrm-maps' ),
				'type'  => 'checkbox',
				'hint'  => __( 'Tick if the map works in online CnCNet games.', 'byrm-maps' ),
			),
			'designer'        => array(
				'label' => __( 'Designer', 'byrm-maps' ),
				'type'  => 'text',
				'hint'  => __( 'Credit the person who made the map.', 'byrm-maps' ),
			),
			'install_notes'   => array(
				'label' => __( 'Install notes', 'byrm-maps' ),
				'type'  => 'textarea',
				'hint'  => __( 'Anything specific to this map. The general install guide is linked automatically.', 'byrm-maps' ),
			),
		)
	);
}

/**
 * Read one specification value.
 *
 * @param  string   $key     Field key from byrm_map_fields().
 * @param  int|null $post_id Post to read, or the current post.
 * @return mixed
 */
function byrm_map_meta( $key, $post_id = null ) {
	$post_id = $post_id ? (int) $post_id : get_the_ID();

	if ( ! $post_id ) {
		return '';
	}

	$value  = get_post_meta( $post_id, '_byrm_' . $key, true );
	$fields = byrm_map_fields();

	if ( '' === $value && isset( $fields[ $key ]['default'] ) ) {
		return $fields[ $key ]['default'];
	}

	return $value;
}

/* ==========================================================================
   Post type and taxonomies
   ========================================================================== */

/**
 * Register the Map post type.
 *
 * The main editor holds the designer's notes, the featured image is the minimap,
 * and screenshots live in a separate gallery field.
 */
function byrm_register_map_post_type() {
	register_post_type(
		'map',
		array(
			'labels'       => array(
				'name'               => __( 'Maps', 'byrm-maps' ),
				'singular_name'      => __( 'Map', 'byrm-maps' ),
				'add_new'            => __( 'Add New Map', 'byrm-maps' ),
				'add_new_item'       => __( 'Add New Map', 'byrm-maps' ),
				'edit_item'          => __( 'Edit Map', 'byrm-maps' ),
				'new_item'           => __( 'New Map', 'byrm-maps' ),
				'view_item'          => __( 'View Map', 'byrm-maps' ),
				'search_items'       => __( 'Search Maps', 'byrm-maps' ),
				'not_found'          => __( 'No maps yet', 'byrm-maps' ),
				'not_found_in_trash' => __( 'No maps in the bin', 'byrm-maps' ),
				'all_items'          => __( 'All Maps', 'byrm-maps' ),
				'menu_name'          => __( 'Maps', 'byrm-maps' ),
			),
			'public'       => true,
			'has_archive'  => 'maps',
			'menu_icon'    => 'dashicons-location-alt',
			'menu_position' => 5,
			'rewrite'      => array( 'slug' => 'maps', 'with_front' => false ),
			'supports'     => array( 'title', 'editor', 'thumbnail', 'excerpt', 'revisions', 'comments' ),
			'show_in_rest' => true,
		)
	);
}
add_action( 'init', 'byrm_register_map_post_type' );

/**
 * Terrain theater, game mode and free-form tags.
 *
 * These are taxonomies rather than plain fields so each one gets its own
 * archive page, which is worth real search traffic.
 */
function byrm_register_map_taxonomies() {
	register_taxonomy(
		'map_theater',
		'map',
		array(
			'labels'            => array(
				'name'          => __( 'Theaters', 'byrm-maps' ),
				'singular_name' => __( 'Theater', 'byrm-maps' ),
				'menu_name'     => __( 'Theaters', 'byrm-maps' ),
			),
			'public'            => true,
			'hierarchical'      => true,
			'show_admin_column' => true,
			'show_in_rest'      => true,
			'rewrite'           => array( 'slug' => 'theater' ),
		)
	);

	register_taxonomy(
		'map_mode',
		'map',
		array(
			'labels'            => array(
				'name'          => __( 'Game modes', 'byrm-maps' ),
				'singular_name' => __( 'Game mode', 'byrm-maps' ),
				'menu_name'     => __( 'Game modes', 'byrm-maps' ),
			),
			'public'            => true,
			'hierarchical'      => true,
			'show_admin_column' => true,
			'show_in_rest'      => true,
			'rewrite'           => array( 'slug' => 'mode' ),
		)
	);

	register_taxonomy(
		'map_tag',
		'map',
		array(
			'labels'            => array(
				'name'          => __( 'Map tags', 'byrm-maps' ),
				'singular_name' => __( 'Map tag', 'byrm-maps' ),
				'menu_name'     => __( 'Tags', 'byrm-maps' ),
			),
			'public'            => true,
			'hierarchical'      => false,
			'show_admin_column' => true,
			'show_in_rest'      => true,
			'rewrite'           => array( 'slug' => 'map-tag' ),
		)
	);
}
add_action( 'init', 'byrm_register_map_taxonomies' );

/**
 * Seed the theater and mode lists on activation, and flush rewrite rules so
 * /maps/ URLs work immediately instead of 404ing until permalinks are re-saved.
 */
function byrm_maps_activate() {
	byrm_register_map_post_type();
	byrm_register_map_taxonomies();

	$seed = array(
		'map_theater' => array( 'Temperate', 'Snow', 'Urban', 'New Urban', 'Desert', 'Lunar' ),
		'map_mode'    => array( 'Standard', 'Naval', 'Co-op', 'Battle', 'Megawealth', 'Free-for-all' ),
	);

	foreach ( $seed as $taxonomy => $terms ) {
		foreach ( $terms as $term ) {
			if ( ! term_exists( $term, $taxonomy ) ) {
				wp_insert_term( $term, $taxonomy );
			}
		}
	}

	flush_rewrite_rules();
}
register_activation_hook( BYRM_MAPS_FILE, 'byrm_maps_activate' );

/**
 * Leave no stale rewrite rules behind.
 */
function byrm_maps_deactivate() {
	flush_rewrite_rules();
}
register_deactivation_hook( BYRM_MAPS_FILE, 'byrm_maps_deactivate' );

/* ==========================================================================
   Gallery and file accessors
   ========================================================================== */

/**
 * Screenshot attachment IDs for a map.
 *
 * @param  int|null $post_id Post to read, or the current post.
 * @return array<int, int>
 */
function byrm_map_gallery( $post_id = null ) {
	$post_id = $post_id ? (int) $post_id : get_the_ID();
	$stored  = get_post_meta( $post_id, '_byrm_gallery', true );

	if ( empty( $stored ) ) {
		return array();
	}

	$ids = array_filter( array_map( 'absint', explode( ',', (string) $stored ) ) );

	// Drop anything that has since been deleted from the media library.
	return array_values( array_filter( $ids, 'wp_attachment_is_image' ) );
}

/**
 * Where the map file actually lives.
 *
 * Map files are hosted off-site rather than in the media library: they are
 * uploaded to a file host, and only the shareable link is stored here. That
 * keeps the catalogue off the hosting disk quota and off its bandwidth bill,
 * which matters on shared hosting once a map gets popular.
 *
 * Only http and https URLs are ever returned. The link is set by an editor, not
 * a visitor, but /map-download/ redirects to whatever comes back from here, so
 * the scheme is checked on the way out as well as on the way in.
 *
 * @param  int|null $post_id Post to read, or the current post.
 * @return string Empty when no link has been set.
 */
function byrm_map_file_url( $post_id = null ) {
	$post_id = $post_id ? (int) $post_id : get_the_ID();
	$url     = (string) get_post_meta( $post_id, '_byrm_file_url', true );

	if ( '' === $url ) {
		return '';
	}

	$scheme = wp_parse_url( $url, PHP_URL_SCHEME );

	return in_array( $scheme, array( 'http', 'https' ), true ) ? $url : '';
}

/**
 * The host serving the download, e.g. "datadock-host.site".
 *
 * Shown on the map page so nobody has to click an unexplained third-party link
 * to find out where it goes.
 *
 * @param  int|null $post_id Post to read, or the current post.
 * @return string
 */
function byrm_map_file_host( $post_id = null ) {
	$url = byrm_map_file_url( $post_id );

	if ( '' === $url ) {
		return '';
	}

	$host = (string) wp_parse_url( $url, PHP_URL_HOST );

	// "www." tells the reader nothing.
	return preg_replace( '/^www\./i', '', $host );
}

/**
 * File extension as typed on the edit screen, e.g. ".yrm".
 *
 * Typed rather than detected, because the file is not on this server to inspect.
 *
 * @param  int|null $post_id Post to read, or the current post.
 * @return string
 */
function byrm_map_file_type( $post_id = null ) {
	$post_id = $post_id ? (int) $post_id : get_the_ID();

	return (string) get_post_meta( $post_id, '_byrm_file_type', true );
}

/**
 * File size as typed on the edit screen, e.g. "248 KB".
 *
 * @param  int|null $post_id Post to read, or the current post.
 * @return string
 */
function byrm_map_file_size( $post_id = null ) {
	$post_id = $post_id ? (int) $post_id : get_the_ID();

	return (string) get_post_meta( $post_id, '_byrm_file_size', true );
}

/**
 * Download count for a map.
 *
 * @param  int|null $post_id Post to read, or the current post.
 * @return int
 */
function byrm_map_downloads( $post_id = null ) {
	$post_id = $post_id ? (int) $post_id : get_the_ID();

	return (int) get_post_meta( $post_id, '_byrm_downloads', true );
}

/**
 * Public URL that counts the download before handing over the file.
 *
 * @param  int|null $post_id Post to read, or the current post.
 * @return string Empty when the map has no file attached.
 */
function byrm_map_download_url( $post_id = null ) {
	$post_id = $post_id ? (int) $post_id : get_the_ID();

	if ( '' === byrm_map_file_url( $post_id ) ) {
		return '';
	}

	return home_url( 'map-download/' . $post_id . '/' );
}

/**
 * The URL the wait page's button points at: this is the one that counts the
 * download and forwards to the file host.
 *
 * @param  int|null $post_id Post to read, or the current post.
 * @return string Empty when the map has no file attached.
 */
function byrm_map_download_go_url( $post_id = null ) {
	$post_id = $post_id ? (int) $post_id : get_the_ID();

	if ( '' === byrm_map_file_url( $post_id ) ) {
		return '';
	}

	return home_url( 'map-download/' . $post_id . '/go/' );
}

/**
 * How long the wait page counts down for, in seconds.
 *
 * Filterable so it can be changed without editing this file, and so it can be
 * set to 0 to turn the wait off entirely.
 *
 * @return int
 */
function byrm_download_wait_seconds() {
	return max( 0, (int) apply_filters( 'byrm_download_wait_seconds', 10 ) );
}

/* ==========================================================================
   Counted downloads
   ========================================================================== */

/**
 * Pretty URL for the download endpoint.
 */
function byrm_download_rewrite() {
	// Both patterns are anchored, so /go/ can never fall through to the first.
	add_rewrite_rule(
		'^map-download/([0-9]+)/go/?$',
		'index.php?byrm_map_download=$matches[1]&byrm_map_download_go=1',
		'top'
	);
	add_rewrite_rule( '^map-download/([0-9]+)/?$', 'index.php?byrm_map_download=$matches[1]', 'top' );
}
add_action( 'init', 'byrm_download_rewrite' );

/**
 * Rebuild the rewrite rules when the set above changes.
 *
 * Rules are only written to the database on activation, so adding one to an
 * already-active plugin would otherwise 404 until somebody thought to open
 * Settings, Permalinks and press Save. Bumping the constant does it for them,
 * once, on the next page load.
 */
function byrm_maybe_flush_rewrites() {
	$version = '2';

	if ( get_option( 'byrm_rewrite_version' ) === $version ) {
		return;
	}

	flush_rewrite_rules( false );
	update_option( 'byrm_rewrite_version', $version, true );
}
add_action( 'init', 'byrm_maybe_flush_rewrites', 99 );

/**
 * @param  array<int, string> $vars Registered query vars.
 * @return array<int, string>
 */
function byrm_download_query_var( $vars ) {
	$vars[] = 'byrm_map_download';
	$vars[] = 'byrm_map_download_go';

	return $vars;
}
add_filter( 'query_vars', 'byrm_download_query_var' );

/**
 * Two steps: a wait page, then the file.
 *
 * /map-download/{id}/      shows map-download.php — the map's details and a
 *                          countdown, after which the real button appears.
 * /map-download/{id}/go/   counts the download and forwards to the file host.
 *
 * The file lives on a third-party host and its link is public anyway, so the
 * second step is a counter rather than an access control: it records the
 * download, then forwards the visitor on. Routing through here instead of
 * linking the host directly is what makes the counter — and therefore the
 * "most downloaded" sort and the site totals — possible at all. It also keeps
 * the host's URL out of the page source until the visitor asks for it.
 *
 * Repeat hits from the same visitor inside 24 hours are not counted twice.
 *
 * If the theme has no map-download.php — a different theme, or the plugin used
 * on another site — the wait step is skipped and this behaves exactly as it did
 * before, forwarding straight on. The download must never be the thing that
 * breaks.
 */
function byrm_handle_download() {
	$post_id = (int) get_query_var( 'byrm_map_download' );

	if ( ! $post_id ) {
		return;
	}

	$post = get_post( $post_id );

	if ( ! $post || 'map' !== $post->post_type || 'publish' !== $post->post_status ) {
		wp_die(
			esc_html__( 'That map is not available.', 'byrm-maps' ),
			esc_html__( 'Map not found', 'byrm-maps' ),
			array( 'response' => 404 )
		);
	}

	// Already filtered to http/https, so this can never emit a javascript: or
	// data: redirect even if one were somehow stored.
	$url = byrm_map_file_url( $post_id );

	if ( ! $url ) {
		wp_die(
			esc_html__( 'That map has no download link yet.', 'byrm-maps' ),
			esc_html__( 'No download', 'byrm-maps' ),
			array( 'response' => 404 )
		);
	}

	// Step one: the wait page. Skipped when the countdown is filtered to 0, and
	// skipped when the theme has no template for it.
	if ( ! get_query_var( 'byrm_map_download_go' ) && byrm_download_wait_seconds() > 0 ) {
		$template = locate_template( 'map-download.php' );

		if ( $template ) {
			global $wp_query;

			// The rewrite above matches no post, so WordPress has this down as a
			// 404. Saying otherwise keeps the status line, the body classes and
			// any caching layer in front of the site all honest.
			$wp_query->is_404 = false;
			status_header( 200 );

			// Make the map the current post so ordinary template tags work.
			$wp_query->posts         = array( $post );
			$wp_query->post          = $post;
			$wp_query->post_count    = 1;
			$wp_query->found_posts   = 1;
			$wp_query->is_singular   = true;
			$wp_query->is_single     = true;
			$wp_query->queried_object    = $post;
			$wp_query->queried_object_id = $post_id;

			setup_postdata( $post );

			// A step on the way to a file is not a page anybody should land on
			// from a search result.
			add_filter( 'wp_robots', 'wp_robots_no_robots' );

			require $template;
			exit;
		}
	}

	// Salted so no raw IP address is ever stored.
	$fingerprint = 'byrm_dl_' . md5( wp_salt() . '|' . $post_id . '|' . byrm_visitor_ip() );

	if ( false === get_transient( $fingerprint ) ) {
		update_post_meta( $post_id, '_byrm_downloads', byrm_map_downloads( $post_id ) + 1 );
		set_transient( $fingerprint, 1, DAY_IN_SECONDS );
	}

	// wp_redirect() rather than wp_safe_redirect(): the latter refuses any host
	// but this one, which is exactly what this endpoint has to do. The scheme
	// check in byrm_map_file_url() is what keeps that safe.
	wp_redirect( $url, 302, 'Bank of YR Maps' );
	exit;
}
add_action( 'template_redirect', 'byrm_handle_download' );

/**
 * Visitor IP, used only to build a salted hash for download de-duplication.
 *
 * @return string
 */
function byrm_visitor_ip() {
	$ip = isset( $_SERVER['REMOTE_ADDR'] ) ? wp_unslash( $_SERVER['REMOTE_ADDR'] ) : '';

	return is_string( $ip ) ? $ip : '';
}

/* ==========================================================================
   Admin
   ========================================================================== */

require_once plugin_dir_path( BYRM_MAPS_FILE ) . 'admin.php';
