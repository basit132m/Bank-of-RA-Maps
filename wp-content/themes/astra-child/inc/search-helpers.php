<?php
/**
 * Helpers for the search results page.
 *
 * WordPress core search only ever looks at the title, excerpt and content. On a
 * map site that misses two things people actually search for — the name of the
 * mapper, and a theater or game mode. Rather than rewrite the search SQL (which
 * is easy to get subtly wrong and affects every query on the site), both are
 * handled as separate, cheap lookups shown alongside the ordinary results.
 *
 * @package Astra_Child
 */

defined( 'ABSPATH' ) || exit;

/**
 * Show a sensible number of results per page.
 *
 * The blog default is ten, which leaves an awkward row in a three-column card
 * grid. Twelve fills it exactly.
 *
 * @param WP_Query $query Query about to run.
 */
function byrm_search_per_page( $query ) {
	if ( is_admin() || ! $query->is_main_query() || ! $query->is_search() ) {
		return;
	}

	$query->set( 'posts_per_page', (int) apply_filters( 'byrm_search_per_page', 12 ) );
}
add_action( 'pre_get_posts', 'byrm_search_per_page' );

/**
 * Maps whose designer matches the search term.
 *
 * A separate query rather than a filter on the main search: it keeps the main
 * query, its pagination and its result count exactly as WordPress built them,
 * and it lets the page label these results honestly as "maps by this designer"
 * instead of silently mixing them in.
 *
 * @param  string $term  Search term.
 * @param  int[]  $skip  Post IDs already shown, so nothing appears twice.
 * @param  int    $limit Maximum to return.
 * @return WP_Post[]
 */
function byrm_search_by_designer( $term, $skip = array(), $limit = 6 ) {
	$term = trim( (string) $term );

	if ( '' === $term || ! post_type_exists( 'map' ) ) {
		return array();
	}

	return get_posts(
		array(
			'post_type'           => 'map',
			'posts_per_page'      => (int) $limit,
			'post__not_in'        => array_map( 'absint', $skip ),
			'ignore_sticky_posts' => true,
			'no_found_rows'       => true,
			// phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query -- one small query on the search page only.
			'meta_query'          => array(
				array(
					'key'     => '_byrm_designer',
					'value'   => $term,
					'compare' => 'LIKE',
				),
			),
		)
	);
}

/**
 * Theaters, modes and tags whose name matches the search term.
 *
 * Someone typing "snow" or "naval" usually wants the whole category, not a map
 * that happens to mention the word. These are offered as links to the matching
 * archive, above the ordinary results.
 *
 * @param  string $term  Search term.
 * @param  int    $limit Maximum to return.
 * @return array<int, array{name: string, url: string, taxonomy: string, count: int}>
 */
function byrm_search_matching_terms( $term, $limit = 6 ) {
	$term = trim( (string) $term );

	if ( '' === $term ) {
		return array();
	}

	$found = get_terms(
		array(
			'taxonomy'   => array( 'map_theater', 'map_mode', 'map_tag' ),
			'name__like' => $term,
			'hide_empty' => true,
			'number'     => (int) $limit,
		)
	);

	if ( is_wp_error( $found ) || ! $found ) {
		return array();
	}

	$out = array();

	foreach ( $found as $item ) {
		$url = get_term_link( $item );

		if ( is_wp_error( $url ) ) {
			continue;
		}

		$out[] = array(
			'name'     => $item->name,
			'url'      => $url,
			'taxonomy' => $item->taxonomy,
			'count'    => (int) $item->count,
		);
	}

	return $out;
}

/**
 * A readable label for the kind of thing a result is.
 *
 * @param  WP_Post $post Result.
 * @return string
 */
function byrm_search_type_label( $post ) {
	if ( 'map' === $post->post_type ) {
		return __( 'Map', 'astra-child' );
	}

	$object = get_post_type_object( $post->post_type );

	if ( $object && ! empty( $object->labels->singular_name ) ) {
		return $object->labels->singular_name;
	}

	return __( 'Page', 'astra-child' );
}

/**
 * One non-map result — a page, a guide, a post.
 *
 * @param WP_Post $post Result to render.
 */
function byrm_search_result( $post ) {
	$link    = get_permalink( $post );
	$excerpt = has_excerpt( $post ) ? get_the_excerpt( $post ) : wp_strip_all_tags( $post->post_content );
	?>
	<article class="byrm-sitem">
		<p class="byrm-sitem__type"><?php echo esc_html( byrm_search_type_label( $post ) ); ?></p>

		<h3 class="byrm-sitem__title">
			<a href="<?php echo esc_url( $link ); ?>"><?php echo esc_html( get_the_title( $post ) ); ?></a>
		</h3>

		<?php if ( trim( $excerpt ) ) : ?>
			<p class="byrm-sitem__excerpt"><?php echo esc_html( wp_trim_words( $excerpt, 28 ) ); ?></p>
		<?php endif; ?>
	</article>
	<?php
}

/**
 * Where to send somebody whose search found nothing.
 *
 * Real destinations only — a dead end that offers nothing is worse than no
 * search page at all.
 *
 * @return array<int, array{label: string, note: string, url: string}>
 */
function byrm_search_routes() {
	$maps_url = post_type_exists( 'map' ) ? get_post_type_archive_link( 'map' ) : home_url( '/maps/' );

	return array(
		array(
			'label' => __( 'Browse every map', 'astra-child' ),
			'note'  => __( 'Filter by players, theater and game mode instead of typing.', 'astra-child' ),
			'url'   => $maps_url,
		),
		array(
			'label' => __( 'How to install a map', 'astra-child' ),
			'note'  => __( 'Four steps, about a minute, and you are in the game.', 'astra-child' ),
			'url'   => home_url( '/guides/install' ),
		),
		array(
			'label' => __( 'Ask on the board', 'astra-child' ),
			'note'  => __( 'Looking for something specific? Somebody here probably knows it.', 'astra-child' ),
			'url'   => home_url( '/community' ),
		),
	);
}
