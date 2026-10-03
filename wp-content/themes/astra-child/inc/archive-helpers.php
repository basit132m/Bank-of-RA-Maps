<?php
/**
 * Maps archive: filter state, option lists and query handling.
 *
 * Filter state lives entirely in the query string, so a filtered view can be
 * bookmarked, shared and reached with the browser's back button. The form is a
 * plain GET form, so the page works with JavaScript disabled.
 *
 * @package Astra_Child
 */

defined( 'ABSPATH' ) || exit;

/**
 * Sort options, and the ORDER BY each one maps to.
 *
 * @return array<string, string>
 */
function byrm_archive_sorts() {
	return array(
		'newest'  => __( 'Newest first', 'astra-child' ),
		'oldest'  => __( 'Oldest first', 'astra-child' ),
		'popular' => __( 'Most downloaded', 'astra-child' ),
		'title'   => __( 'Title A–Z', 'astra-child' ),
	);
}

/**
 * The filters currently requested, sanitised.
 *
 * Parameter names avoid WordPress's own query vars: `tag` and `p` are reserved
 * by core, so the tag filter is `mtag` and there is no `p`.
 *
 * @return array<string, string|int>
 */
function byrm_archive_filters() {
	$sorts = byrm_archive_sorts();
	$sort  = isset( $_GET['sort'] ) ? sanitize_key( wp_unslash( $_GET['sort'] ) ) : 'newest';

	return array(
		'q'       => isset( $_GET['q'] ) ? sanitize_text_field( wp_unslash( $_GET['q'] ) ) : '',
		'players' => isset( $_GET['players'] ) ? absint( $_GET['players'] ) : 0,
		'theater' => isset( $_GET['theater'] ) ? sanitize_title( wp_unslash( $_GET['theater'] ) ) : '',
		'mode'    => isset( $_GET['mode'] ) ? sanitize_title( wp_unslash( $_GET['mode'] ) ) : '',
		'mtag'    => isset( $_GET['mtag'] ) ? sanitize_title( wp_unslash( $_GET['mtag'] ) ) : '',
		'sort'    => isset( $sorts[ $sort ] ) ? $sort : 'newest',
	);
}

/**
 * Is any filter narrowing the list right now?
 *
 * Sort alone does not count — it changes order, not which maps are shown.
 *
 * @return bool
 */
function byrm_archive_is_filtered() {
	$f = byrm_archive_filters();

	return '' !== $f['q'] || $f['players'] > 0 || '' !== $f['theater'] || '' !== $f['mode'] || '' !== $f['mtag'];
}

/**
 * Build an archive URL with one filter changed.
 *
 * Passing null for a key removes it. Paging always resets, because page 4 of
 * the old result set is meaningless against a new one.
 *
 * @param  array<string, string|int|null> $changes Filters to override.
 * @return string
 */
function byrm_archive_url( $changes = array() ) {
	$base = get_post_type_archive_link( 'map' );

	if ( ! $base ) {
		$base = home_url( '/maps/' );
	}

	$params = array_merge( byrm_archive_filters(), $changes );

	// Drop defaults and empties so URLs stay short and readable.
	foreach ( $params as $key => $value ) {
		if ( null === $value || '' === $value || 0 === $value || ( 'sort' === $key && 'newest' === $value ) ) {
			unset( $params[ $key ] );
		}
	}

	return $params ? add_query_arg( $params, $base ) : $base;
}

/**
 * Player counts across published maps, for the filter rail.
 *
 * Returned as [ players => number of maps ], so the rail can show counts and
 * never offers a filter that would return nothing.
 *
 * @return array<int, int>
 */
function byrm_archive_player_counts() {
	$cached = get_transient( 'byrm_player_counts' );

	if ( is_array( $cached ) ) {
		return $cached;
	}

	global $wpdb;

	$rows = $wpdb->get_results(
		$wpdb->prepare(
			"SELECT meta.meta_value AS players, COUNT(*) AS total
			 FROM {$wpdb->postmeta} AS meta
			 INNER JOIN {$wpdb->posts} AS posts ON posts.ID = meta.post_id
			 WHERE meta.meta_key = %s
			   AND posts.post_type = %s
			   AND posts.post_status = 'publish'
			   AND meta.meta_value <> ''
			 GROUP BY meta.meta_value
			 ORDER BY CAST(meta.meta_value AS UNSIGNED) ASC",
			'_byrm_players',
			'map'
		)
	);

	$counts = array();

	foreach ( (array) $rows as $row ) {
		$counts[ (int) $row->players ] = (int) $row->total;
	}

	set_transient( 'byrm_player_counts', $counts, HOUR_IN_SECONDS );

	return $counts;
}

/**
 * Terms that actually have published maps, for one taxonomy.
 *
 * @param  string $taxonomy Taxonomy name.
 * @return WP_Term[]
 */
function byrm_archive_terms( $taxonomy ) {
	if ( ! taxonomy_exists( $taxonomy ) ) {
		return array();
	}

	$terms = get_terms(
		array(
			'taxonomy'   => $taxonomy,
			'hide_empty' => true,
			'orderby'    => 'name',
		)
	);

	return ( $terms && ! is_wp_error( $terms ) ) ? $terms : array();
}

/**
 * Apply the requested filters to the archive's main query.
 *
 * Modifying the main query rather than running a second WP_Query keeps
 * pagination, result counts and canonical URLs correct.
 *
 * @param WP_Query $query The query being prepared.
 */
function byrm_filter_map_archive( $query ) {
	if ( is_admin() || ! $query->is_main_query() ) {
		return;
	}

	$is_map_archive = $query->is_post_type_archive( 'map' )
		|| $query->is_tax( array( 'map_theater', 'map_mode', 'map_tag' ) );

	if ( ! $is_map_archive ) {
		return;
	}

	$filters = byrm_archive_filters();

	$query->set( 'posts_per_page', 12 );

	if ( '' !== $filters['q'] ) {
		$query->set( 's', $filters['q'] );
	}

	if ( $filters['players'] > 0 ) {
		$query->set(
			'meta_query',
			array(
				array(
					'key'     => '_byrm_players',
					'value'   => (string) $filters['players'],
					'compare' => '=',
				),
			)
		);
	}

	// Taxonomy filters stack with whatever term archive we may already be on.
	$tax_query = array();

	foreach ( array(
		'theater' => 'map_theater',
		'mode'    => 'map_mode',
		'mtag'    => 'map_tag',
	) as $param => $taxonomy ) {
		if ( '' !== $filters[ $param ] && taxonomy_exists( $taxonomy ) ) {
			$tax_query[] = array(
				'taxonomy' => $taxonomy,
				'field'    => 'slug',
				'terms'    => $filters[ $param ],
			);
		}
	}

	if ( $tax_query ) {
		$existing = $query->get( 'tax_query' );
		$query->set( 'tax_query', array_merge( is_array( $existing ) ? $existing : array(), $tax_query ) );
	}

	switch ( $filters['sort'] ) {
		case 'oldest':
			$query->set( 'orderby', 'date' );
			$query->set( 'order', 'ASC' );
			break;

		case 'popular':
			// Every map carries _byrm_downloads (see byrm_seed_download_counter),
			// so ordering by it never silently drops maps that have no downloads.
			$query->set( 'meta_key', '_byrm_downloads' );
			$query->set( 'orderby', array( 'meta_value_num' => 'DESC', 'date' => 'DESC' ) );
			break;

		case 'title':
			$query->set( 'orderby', 'title' );
			$query->set( 'order', 'ASC' );
			break;

		default:
			$query->set( 'orderby', 'date' );
			$query->set( 'order', 'DESC' );
	}
}
add_action( 'pre_get_posts', 'byrm_filter_map_archive' );

/**
 * Make sure every map has a download counter, even at zero.
 *
 * Sorting by "most downloaded" uses this meta key, and WordPress drops posts
 * that do not have the key at all — so a map nobody has downloaded yet would
 * vanish from that view without this.
 *
 * @param int $post_id Map being saved.
 */
function byrm_seed_download_counter( $post_id ) {
	if ( '' === get_post_meta( $post_id, '_byrm_downloads', true ) ) {
		update_post_meta( $post_id, '_byrm_downloads', 0 );
	}

	delete_transient( 'byrm_player_counts' );
}
add_action( 'save_post_map', 'byrm_seed_download_counter' );

/**
 * Keep filtered and paged views out of search engines.
 *
 * Every combination of filters is the same content in a different order, and
 * indexing them competes with the map pages that should actually rank.
 */
function byrm_archive_robots() {
	if ( ! is_post_type_archive( 'map' ) && ! is_tax( array( 'map_theater', 'map_mode', 'map_tag' ) ) ) {
		return;
	}

	if ( byrm_archive_is_filtered() || is_paged() ) {
		echo '<meta name="robots" content="noindex, follow">' . "\n";
	}
}
add_action( 'wp_head', 'byrm_archive_robots', 1 );

/**
 * Human-readable summary of the active filters, for the chip row.
 *
 * @return array<int, array{label: string, url: string}>
 */
function byrm_archive_chips() {
	$filters = byrm_archive_filters();
	$chips   = array();

	if ( '' !== $filters['q'] ) {
		$chips[] = array(
			/* translators: %s: search term */
			'label' => sprintf( __( 'Search: %s', 'astra-child' ), $filters['q'] ),
			'url'   => byrm_archive_url( array( 'q' => null ) ),
		);
	}

	if ( $filters['players'] > 0 ) {
		$chips[] = array(
			/* translators: %d: number of players */
			'label' => sprintf( __( '%d players', 'astra-child' ), $filters['players'] ),
			'url'   => byrm_archive_url( array( 'players' => null ) ),
		);
	}

	foreach ( array(
		'theater' => 'map_theater',
		'mode'    => 'map_mode',
		'mtag'    => 'map_tag',
	) as $param => $taxonomy ) {
		if ( '' === $filters[ $param ] || ! taxonomy_exists( $taxonomy ) ) {
			continue;
		}

		$term = get_term_by( 'slug', $filters[ $param ], $taxonomy );

		if ( $term && ! is_wp_error( $term ) ) {
			$chips[] = array(
				'label' => $term->name,
				'url'   => byrm_archive_url( array( $param => null ) ),
			);
		}
	}

	return $chips;
}
