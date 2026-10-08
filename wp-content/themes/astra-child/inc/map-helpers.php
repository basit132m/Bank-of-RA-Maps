<?php
/**
 * Helpers for the single map template.
 *
 * @package Astra_Child
 */

defined( 'ABSPATH' ) || exit;

/**
 * Map page settings.
 *
 * `banner_image` is the one background shared by every map page. Paste a media
 * library URL there and it is used across all of them; leave it empty and the
 * page falls back to a CSS-only backdrop that still looks deliberate.
 *
 * @return array<string, string>
 */
function byrm_map_config() {
	return apply_filters(
		'byrm_map_config',
		array(
			'banner_image' => '',
		)
	);
}

/**
 * Inline style for the shared banner background, or an empty string.
 *
 * @return string
 */
function byrm_map_banner_style() {
	$config = byrm_map_config();
	$image  = trim( (string) $config['banner_image'] );

	if ( '' === $image ) {
		return '';
	}

	return '--byrm-banner-image:url(' . esc_url_raw( $image ) . ')';
}

/**
 * Other maps worth showing, as posts.
 *
 * Split out of byrm_related_maps() because that function renders — it echoes a
 * whole section and returns nothing. Any page that wants the maps but not that
 * markup needs this instead; the download wait page draws them with
 * byrm_map_card() so they match the cards everywhere else on the site.
 *
 * @param  int        $post_id Current map.
 * @param  string|int $players Its player count.
 * @param  int        $limit   How many to return.
 * @return WP_Post[]
 */
function byrm_related_map_posts( $post_id, $players = '', $limit = 3 ) {
	$limit = max( 1, (int) $limit );

	$args = array(
		'post_type'           => 'map',
		'posts_per_page'      => $limit,
		'post__not_in'        => array( (int) $post_id ),
		'ignore_sticky_posts' => true,
		'no_found_rows'       => true,
	);

	$related = array();

	if ( $players ) {
		$related = get_posts(
			$args + array(
				'meta_key'   => '_byrm_players',
				'meta_value' => (string) $players,
			)
		);
	}

	// Top up with the most recent maps when there are not enough matches.
	if ( count( $related ) < $limit ) {
		$exclude = array_merge( array( (int) $post_id ), wp_list_pluck( $related, 'ID' ) );

		$filler = get_posts(
			array(
				'post_type'           => 'map',
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
 * Other maps worth showing underneath.
 *
 * Renders. Echoes a section and returns nothing — use byrm_related_map_posts()
 * above when you want the posts themselves.
 *
 * Prefers the same player count, because that is what decides whether a visitor
 * can actually play it tonight.
 *
 * @param int        $post_id Current map.
 * @param string|int $players Its player count.
 */
function byrm_related_maps( $post_id, $players = '' ) {
	$related = byrm_related_map_posts( $post_id, $players );

	if ( ! $related ) {
		return;
	}
	?>
	<section class="byrm-related">
		<div class="byrm-map__shell">
			<div class="byrm-related__head">
				<h2><?php esc_html_e( 'More maps', 'astra-child' ); ?></h2>
				<a class="byrm-arrow" href="<?php echo esc_url( get_post_type_archive_link( 'map' ) ); ?>">
					<?php esc_html_e( 'Browse all', 'astra-child' ); ?>
				</a>
			</div>

			<div class="byrm-related__grid">
				<?php foreach ( $related as $map ) : ?>
					<?php
					$map_players  = function_exists( 'byrm_map_meta' ) ? byrm_map_meta( 'players', $map->ID ) : '';
					$map_theaters = get_the_terms( $map->ID, 'map_theater' );
					$map_theater  = ( $map_theaters && ! is_wp_error( $map_theaters ) ) ? $map_theaters[0]->name : '';
					?>
					<article class="byrm-rcard">
						<a class="byrm-rcard__media" href="<?php echo esc_url( get_permalink( $map ) ); ?>" tabindex="-1" aria-hidden="true">
							<?php if ( has_post_thumbnail( $map->ID ) ) : ?>
								<?php echo get_the_post_thumbnail( $map->ID, 'medium_large', array( 'loading' => 'lazy', 'alt' => '' ) ); ?>
							<?php else : ?>
								<span class="byrm-rcard__blank" aria-hidden="true"></span>
							<?php endif; ?>
						</a>

						<div class="byrm-rcard__body">
							<h3><a href="<?php echo esc_url( get_permalink( $map ) ); ?>"><?php echo esc_html( get_the_title( $map ) ); ?></a></h3>
							<p class="byrm-rcard__meta">
								<?php if ( $map_players ) : ?>
									<?php echo esc_html( $map_players ); ?> <?php esc_html_e( 'players', 'astra-child' ); ?>
								<?php endif; ?>
								<?php if ( $map_theater ) : ?>
									&middot; <?php echo esc_html( $map_theater ); ?>
								<?php endif; ?>
							</p>
						</div>
					</article>
				<?php endforeach; ?>
			</div>
		</div>
	</section>
	<?php
}

/**
 * The lightbox shell.
 *
 * Printed once per map page and filled in by map.js, so the markup exists
 * before any image is clicked and screen readers see a stable dialog.
 */
function byrm_render_lightbox() {
	if ( ! is_singular( 'map' ) ) {
		return;
	}
	?>
	<div class="byrm-lb" id="byrm-lightbox" role="dialog" aria-modal="true"
	     aria-label="<?php esc_attr_e( 'Image viewer', 'astra-child' ); ?>" hidden>

		<div class="byrm-lb__backdrop" data-byrm-lb-close></div>

		<div class="byrm-lb__bar">
			<span class="byrm-lb__count" data-byrm-lb-count></span>
			<span class="byrm-lb__title"><?php echo esc_html( get_the_title() ); ?></span>

			<div class="byrm-lb__tools">
				<button type="button" class="byrm-lb__btn" data-byrm-lb-zoom
				        aria-pressed="false">
					<span class="byrm-sr"><?php esc_html_e( 'Toggle full resolution', 'astra-child' ); ?></span>
					<svg viewBox="0 0 24 24" aria-hidden="true" focusable="false">
						<circle cx="11" cy="11" r="7"/><path d="m20 20-3.6-3.6M8 11h6M11 8v6"/>
					</svg>
				</button>
				<button type="button" class="byrm-lb__btn" data-byrm-lb-close>
					<span class="byrm-sr"><?php esc_html_e( 'Close', 'astra-child' ); ?></span>
					<svg viewBox="0 0 24 24" aria-hidden="true" focusable="false">
						<path d="M6 6l12 12M18 6L6 18"/>
					</svg>
				</button>
			</div>
		</div>

		<button type="button" class="byrm-lb__nav byrm-lb__nav--prev" data-byrm-lb-prev>
			<span class="byrm-sr"><?php esc_html_e( 'Previous image', 'astra-child' ); ?></span>
			<svg viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path d="M15 5l-7 7 7 7"/></svg>
		</button>

		<figure class="byrm-lb__stage" data-byrm-lb-stage>
			<img class="byrm-lb__img" data-byrm-lb-img alt="">
			<figcaption class="byrm-lb__caption" data-byrm-lb-caption></figcaption>
		</figure>

		<button type="button" class="byrm-lb__nav byrm-lb__nav--next" data-byrm-lb-next>
			<span class="byrm-sr"><?php esc_html_e( 'Next image', 'astra-child' ); ?></span>
			<svg viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path d="M9 5l7 7-7 7"/></svg>
		</button>

		<div class="byrm-lb__strip" data-byrm-lb-strip></div>
	</div>
	<?php
}
add_action( 'wp_footer', 'byrm_render_lightbox' );

/**
 * Is this the pause between the download button and the file?
 *
 * The plugin routes /map-download/{id}/ here by query var rather than through
 * the template hierarchy, so there is no is_page() to ask. The /go/ step never
 * renders anything — it counts and redirects — so it is excluded.
 *
 * @return bool
 */
function byrm_is_download_page() {
	return did_action( 'parse_query' )
		&& (bool) get_query_var( 'byrm_map_download' )
		&& ! get_query_var( 'byrm_map_download_go' );
}
