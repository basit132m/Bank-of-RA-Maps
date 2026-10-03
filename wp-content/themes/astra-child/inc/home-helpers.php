<?php
/**
 * Helpers for the home page.
 *
 * @package Astra_Child
 */

defined( 'ABSPATH' ) || exit;

/**
 * Add a "Feature on the home page" switch to the Map edit screen.
 *
 * This lives in the theme rather than the plugin on purpose: what a map *is*
 * belongs to the plugin, but what gets promoted on the front page is a
 * presentation decision. The plugin reads its field list through a filter, so
 * the switch saves and renders with no change to the plugin itself.
 *
 * @param  array<string, array<string, mixed>> $fields Existing fields.
 * @return array<string, array<string, mixed>>
 */
function byrm_add_featured_field( $fields ) {
	$fields['featured'] = array(
		'label' => __( 'Feature on the home page', 'astra-child' ),
		'type'  => 'checkbox',
		'hint'  => __( 'The most recently published featured map takes the hero slot. Leave unticked and the newest map is used.', 'astra-child' ),
	);

	return $fields;
}
add_filter( 'byrm_map_fields', 'byrm_add_featured_field' );

/**
 * The map to show in the featured slot.
 *
 * A map explicitly marked as featured wins; otherwise the newest published map
 * stands in, so the slot is never empty while any map exists.
 *
 * @return WP_Post|null
 */
function byrm_featured_map() {
	if ( ! post_type_exists( 'map' ) ) {
		return null;
	}

	$featured = get_posts(
		array(
			'post_type'           => 'map',
			'posts_per_page'      => 1,
			'meta_key'            => '_byrm_featured',
			'meta_value'          => '1',
			'ignore_sticky_posts' => true,
			'no_found_rows'       => true,
		)
	);

	if ( $featured ) {
		return $featured[0];
	}

	$newest = get_posts(
		array(
			'post_type'           => 'map',
			'posts_per_page'      => 1,
			'ignore_sticky_posts' => true,
			'no_found_rows'       => true,
		)
	);

	return $newest ? $newest[0] : null;
}

/**
 * Recent maps for the home page grid.
 *
 * @param  int   $count   How many to return.
 * @param  int[] $exclude Post IDs to leave out, e.g. the featured map.
 * @return WP_Post[]
 */
function byrm_recent_maps( $count = 6, $exclude = array() ) {
	if ( ! post_type_exists( 'map' ) ) {
		return array();
	}

	return get_posts(
		array(
			'post_type'           => 'map',
			'posts_per_page'      => (int) $count,
			'post__not_in'        => array_map( 'absint', $exclude ),
			'ignore_sticky_posts' => true,
			'no_found_rows'       => true,
		)
	);
}

/**
 * Catalogue totals for the hero.
 *
 * Cached for an hour, because summing download counters across every map is not
 * worth doing on every home page view.
 *
 * @return array{maps: int, downloads: int}
 */
function byrm_catalogue_totals() {
	$cached = get_transient( 'byrm_catalogue_totals' );

	if ( is_array( $cached ) ) {
		return $cached;
	}

	$totals = array(
		'maps'      => 0,
		'downloads' => 0,
	);

	if ( post_type_exists( 'map' ) ) {
		$counts          = wp_count_posts( 'map' );
		$totals['maps']  = isset( $counts->publish ) ? (int) $counts->publish : 0;

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
				'map'
			)
		);
	}

	set_transient( 'byrm_catalogue_totals', $totals, HOUR_IN_SECONDS );

	return $totals;
}

/**
 * Clear the cached totals whenever a map is published, edited or removed, so a
 * newly published map shows in the count straight away.
 */
function byrm_flush_totals() {
	delete_transient( 'byrm_catalogue_totals' );
}
add_action( 'save_post_map', 'byrm_flush_totals' );
add_action( 'deleted_post', 'byrm_flush_totals' );
add_action( 'trashed_post', 'byrm_flush_totals' );

/**
 * One map card for the home page grid.
 *
 * @param WP_Post $map Map to render.
 */
function byrm_map_card( $map ) {
	$players  = function_exists( 'byrm_map_meta' ) ? byrm_map_meta( 'players', $map->ID ) : '';
	$theaters = get_the_terms( $map->ID, 'map_theater' );
	$theater  = ( $theaters && ! is_wp_error( $theaters ) ) ? $theaters[0]->name : '';
	$downloads = function_exists( 'byrm_map_downloads' ) ? byrm_map_downloads( $map->ID ) : 0;
	$link     = get_permalink( $map );
	?>
	<article class="byrm-mcard">
		<a class="byrm-mcard__media" href="<?php echo esc_url( $link ); ?>" tabindex="-1" aria-hidden="true">
			<?php if ( has_post_thumbnail( $map->ID ) ) : ?>
				<?php
				echo get_the_post_thumbnail(
					$map->ID,
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
				<a href="<?php echo esc_url( $link ); ?>"><?php echo esc_html( get_the_title( $map ) ); ?></a>
			</h3>

			<ul class="byrm-mcard__meta">
				<?php if ( $players ) : ?>
					<li><?php echo esc_html( $players ); ?> <?php esc_html_e( 'players', 'astra-child' ); ?></li>
				<?php endif; ?>
				<?php if ( $theater ) : ?>
					<li><?php echo esc_html( $theater ); ?></li>
				<?php endif; ?>
			</ul>

			<?php if ( has_excerpt( $map->ID ) ) : ?>
				<p class="byrm-mcard__excerpt">
					<?php echo esc_html( wp_trim_words( get_the_excerpt( $map ), 18 ) ); ?>
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
