<?php
/**
 * Helpers for the About page.
 *
 * The page itself is an ordinary WordPress Page — create one with the slug
 * "about" (or "about-us") and page-about.php renders it. Everything on it that
 * is a matter of fact rather than design comes from byrm_about_config() below,
 * so the copy can be corrected from functions.php without touching the
 * template.
 *
 * The numbers on the page are read from the catalogue, never typed in. A count
 * someone has to remember to update is a count that goes stale and makes the
 * whole page look careless.
 *
 * @package Astra_Child
 */

defined( 'ABSPATH' ) || exit;

/**
 * Who runs the site, and how to reach them.
 *
 * Every value is optional. An empty string means "do not mention it", and the
 * template drops the whole row rather than printing a label with nothing after
 * it — so this is safe to leave half-filled.
 *
 * @return array<string, string>
 */
function byrm_about_config() {
	$defaults = array(
		// Shown as "Run by —". Leave empty to say "a small team" instead.
		'maintainer'    => '',

		// Shown as "Making maps since —". A year, e.g. "2019". Omitted if empty.
		'since'         => '',

		// Reached from the contact block. All three are optional.
		'contact_email' => '',
		'discord_url'   => '',
		'forum_url'     => '',

		// Where the map files themselves are stored, in plain words. This is
		// worth saying out loud: people are right to be wary of a download that
		// leaves the site it was linked from.
		'file_host'     => '',
	);

	$config = apply_filters( 'byrm_about_config', $defaults );

	return is_array( $config ) ? wp_parse_args( $config, $defaults ) : $defaults;
}

/**
 * The About page's ID, or 0 before it has been created.
 *
 * Checks both slugs, because "about" and "about-us" are both reasonable and
 * there is no sense making the choice matter.
 *
 * @return int
 */
function byrm_about_page_id() {
	static $id = null;

	if ( null !== $id ) {
		return $id;
	}

	$id = 0;

	foreach ( array( 'about', 'about-us' ) as $slug ) {
		$page = get_page_by_path( $slug );

		if ( $page ) {
			$id = (int) $page->ID;
			break;
		}
	}

	return $id;
}

/**
 * Is this the About page?
 *
 * @return bool
 */
function byrm_is_about_page() {
	return did_action( 'wp' ) && is_page( array( 'about', 'about-us' ) );
}

/**
 * Figures for the strip under the banner.
 *
 * Maps and downloads come from the shared catalogue totals, which are already
 * cached for an hour; theaters and guides are cheap term and child-page counts.
 * Anything that comes back as zero is dropped by the template, so a brand new
 * site shows two honest numbers instead of four zeroes.
 *
 * @return array<int, array{value: string, label: string}>
 */
function byrm_about_stats() {
	$totals = function_exists( 'byrm_catalogue_totals' )
		? byrm_catalogue_totals()
		: array(
			'maps'      => 0,
			'downloads' => 0,
		);

	$stats = array();

	if ( ! empty( $totals['maps'] ) ) {
		$stats[] = array(
			'value' => number_format_i18n( (int) $totals['maps'] ),
			'label' => _n( 'Map published', 'Maps published', (int) $totals['maps'], 'astra-child' ),
		);
	}

	if ( ! empty( $totals['downloads'] ) ) {
		$stats[] = array(
			'value' => number_format_i18n( (int) $totals['downloads'] ),
			'label' => _n( 'Download', 'Downloads', (int) $totals['downloads'], 'astra-child' ),
		);
	}

	if ( taxonomy_exists( 'map_theater' ) ) {
		$theaters = wp_count_terms(
			array(
				'taxonomy'   => 'map_theater',
				'hide_empty' => true,
			)
		);

		if ( ! is_wp_error( $theaters ) && (int) $theaters > 0 ) {
			$stats[] = array(
				'value' => number_format_i18n( (int) $theaters ),
				'label' => _n( 'Theater', 'Theaters', (int) $theaters, 'astra-child' ),
			);
		}
	}

	$guides = function_exists( 'byrm_guide_children' ) ? count( byrm_guide_children() ) : 0;

	if ( $guides > 0 ) {
		$stats[] = array(
			'value' => number_format_i18n( $guides ),
			'label' => _n( 'Guide written', 'Guides written', $guides, 'astra-child' ),
		);
	}

	return $stats;
}

/**
 * Send a page called "about-us" through page-about.php.
 *
 * WordPress picks page-about.php on its own for the slug "about". It will not
 * for "about-us" — it would look for page-about-us.php — so rather than keep
 * two copies of the same template, that slug is routed here.
 *
 * @param  string $template Template WordPress chose.
 * @return string
 */
function byrm_about_template( $template ) {
	if ( ! byrm_is_about_page() ) {
		return $template;
	}

	// A template WordPress picked by slug already wins.
	if ( 0 === strpos( basename( $template ), 'page-' ) ) {
		return $template;
	}

	$about = locate_template( 'page-about.php' );

	return $about ? $about : $template;
}
add_filter( 'template_include', 'byrm_about_template' );
