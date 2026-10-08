<?php
/**
 * Helpers for the Mods section.
 *
 * Mods are a separate post type with their own admin menu, but on the front end
 * they are the same kind of page as a map: a banner, a description, screenshots,
 * a download panel and some details beside it. So these templates reuse the map
 * and archive stylesheets rather than growing a second copy of the same design —
 * mod.css only holds the handful of things that have no map equivalent.
 *
 * @package Astra_Child
 */

defined( 'ABSPATH' ) || exit;

/**
 * The mods browse page, or one of its term archives.
 *
 * @return bool
 */
function byrm_is_mod_archive() {
	return is_post_type_archive( 'mod' ) || is_tax( array( 'mod_type', 'mod_tag' ) );
}

/**
 * Any mod page at all — the browse page, a term archive, or a single mod.
 *
 * @return bool
 */
function byrm_is_mod_page() {
	return did_action( 'wp' ) && ( byrm_is_mod_archive() || is_singular( 'mod' ) );
}

/**
 * How many mods are published, and how many times they have been downloaded.
 *
 * Cached for an hour, like the map totals, because summing a meta column on
 * every archive view is not worth the query.
 *
 * @return array{mods: int, downloads: int}
 */
function byrm_mod_totals() {
	$cached = get_transient( 'byrm_mod_totals' );

	if ( is_array( $cached ) ) {
		return $cached;
	}

	$totals = array(
		'mods'      => 0,
		'downloads' => 0,
	);

	if ( post_type_exists( 'mod' ) ) {
		$counts         = wp_count_posts( 'mod' );
		$totals['mods'] = isset( $counts->publish ) ? (int) $counts->publish : 0;

		global $wpdb;

		$totals['downloads'] = (int) $wpdb->get_var(
			$wpdb->prepare(
				"SELECT SUM(meta.meta_value)
				 FROM {$wpdb->postmeta} AS meta
				 INNER JOIN {$wpdb->posts} AS posts ON posts.ID = meta.post_id
				 WHERE meta.meta_key = %s
				   AND posts.post_type = %s
				   AND posts.post_status = 'publish'",
				'_byrm_downloads',
				'mod'
			)
		);
	}

	set_transient( 'byrm_mod_totals', $totals, HOUR_IN_SECONDS );

	return $totals;
}

/**
 * Clear the cached totals when a mod is published, edited or removed.
 */
function byrm_flush_mod_totals() {
	delete_transient( 'byrm_mod_totals' );
}
add_action( 'save_post_mod', 'byrm_flush_mod_totals' );
add_action( 'deleted_post', 'byrm_flush_mod_totals' );
add_action( 'trashed_post', 'byrm_flush_mod_totals' );

/**
 * The mod types that actually have something filed under them.
 *
 * @return WP_Term[]
 */
function byrm_mod_types() {
	if ( ! taxonomy_exists( 'mod_type' ) ) {
		return array();
	}

	$terms = get_terms(
		array(
			'taxonomy'   => 'mod_type',
			'hide_empty' => true,
			'orderby'    => 'name',
		)
	);

	return ( is_array( $terms ) && ! is_wp_error( $terms ) ) ? $terms : array();
}

/**
 * Other mods worth showing, as posts.
 *
 * A getter, not a renderer — the mistake that put a map page's "More maps"
 * block above the download page was exactly this distinction, so it is spelled
 * out here.
 *
 * @param  int $post_id Current mod.
 * @param  int $limit   How many to return.
 * @return WP_Post[]
 */
function byrm_related_mod_posts( $post_id, $limit = 3 ) {
	$limit = max( 1, (int) $limit );
	$types = wp_get_post_terms( (int) $post_id, 'mod_type', array( 'fields' => 'ids' ) );

	$args = array(
		'post_type'           => 'mod',
		'posts_per_page'      => $limit,
		'post__not_in'        => array( (int) $post_id ),
		'ignore_sticky_posts' => true,
		'no_found_rows'       => true,
	);

	$related = array();

	// Same kind of mod first, because that is what somebody looking at this one
	// is most likely to want next.
	if ( is_array( $types ) && $types && ! is_wp_error( $types ) ) {
		$related = get_posts(
			$args + array(
				'tax_query' => array(  // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query -- three rows, once per page.
					array(
						'taxonomy' => 'mod_type',
						'field'    => 'term_id',
						'terms'    => $types,
					),
				),
			)
		);
	}

	if ( count( $related ) < $limit ) {
		$exclude = array_merge( array( (int) $post_id ), wp_list_pluck( $related, 'ID' ) );

		$filler = get_posts(
			array(
				'post_type'           => 'mod',
				'posts_per_page'      => $limit - count( $related ),
				'post__not_in'        => $exclude,
				'ignore_sticky_posts' => true,
				'no_found_rows'       => true,
			)
		);

		$related = array_merge( $related, $filler );
	}

	return $related;
}

/**
 * One mod card for a grid.
 *
 * Renders. Uses the same .byrm-mcard markup as a map card so the two look
 * identical wherever they appear side by side; only the middle line differs,
 * because player count and theater mean nothing for a mod.
 *
 * @param WP_Post $mod Mod to render.
 */
function byrm_mod_card( $mod ) {
	$requires  = function_exists( 'byrm_mod_requires_label' ) ? byrm_mod_requires_label( $mod->ID ) : '';
	$types     = get_the_terms( $mod->ID, 'mod_type' );
	$type      = ( $types && ! is_wp_error( $types ) ) ? $types[0]->name : '';
	$downloads = function_exists( 'byrm_map_downloads' ) ? byrm_map_downloads( $mod->ID ) : 0;
	$link      = get_permalink( $mod );
	?>
	<article class="byrm-mcard">
		<a class="byrm-mcard__media" href="<?php echo esc_url( $link ); ?>" tabindex="-1" aria-hidden="true">
			<?php if ( has_post_thumbnail( $mod->ID ) ) : ?>
				<?php
				echo get_the_post_thumbnail(
					$mod->ID,
					'medium_large',
					array(
						'loading'  => 'lazy',
						'decoding' => 'async',
						'alt'      => '',
					)
				);
				?>
			<?php else : ?>
				<span class="byrm-mcard__blank" aria-hidden="true"></span>
			<?php endif; ?>
		</a>

		<div class="byrm-mcard__body">
			<h3 class="byrm-mcard__title">
				<a href="<?php echo esc_url( $link ); ?>"><?php echo esc_html( get_the_title( $mod ) ); ?></a>
			</h3>

			<ul class="byrm-mcard__meta">
				<?php if ( $type ) : ?>
					<li><?php echo esc_html( $type ); ?></li>
				<?php endif; ?>
				<?php if ( $requires ) : ?>
					<li><?php echo esc_html( $requires ); ?></li>
				<?php endif; ?>
			</ul>

			<?php if ( has_excerpt( $mod->ID ) ) : ?>
				<p class="byrm-mcard__excerpt">
					<?php echo esc_html( wp_trim_words( get_the_excerpt( $mod ), 18 ) ); ?>
				</p>
			<?php endif; ?>

			<footer class="byrm-mcard__foot">
				<span><?php echo esc_html( number_format_i18n( $downloads ) ); ?> <?php esc_html_e( 'downloads', 'astra-child' ); ?></span>
				<span class="byrm-mcard__go" aria-hidden="true">&rarr;</span>
			</footer>
		</div>
	</article>
	<?php
}

/**
 * Send the mod archive and term archives through archive-mod.php.
 *
 * WordPress finds archive-mod.php on its own for the post type archive, but not
 * for mod_type and mod_tag terms — they would fall through to Astra's default
 * archive and lose the design, the same way the map taxonomies would.
 *
 * @param  string $template Template WordPress chose.
 * @return string
 */
function byrm_mod_taxonomy_template( $template ) {
	if ( ! is_tax( array( 'mod_type', 'mod_tag' ) ) ) {
		return $template;
	}

	$archive = locate_template( 'archive-mod.php' );

	return $archive ? $archive : $template;
}
add_filter( 'template_include', 'byrm_mod_taxonomy_template' );
