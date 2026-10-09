<?php
/**
 * Helpers for the image wall at /gallery/.
 *
 * A map is a picture before it is a list of specifications, and some people
 * browse entirely by eye. This page is that: nothing but minimaps, loading as
 * you scroll.
 *
 * Infinite scroll is built on top of real pagination rather than instead of it.
 * The "Load more" control is a genuine link to the next page, so the wall still
 * works with JavaScript off, for a crawler, and for anyone whose connection
 * drops the script. The script upgrades that link into an auto-loader; it never
 * becomes the only way through.
 *
 * @package Astra_Child
 */

defined( 'ABSPATH' ) || exit;

/**
 * How many tiles load at a time.
 *
 * @return int
 */
function byrm_gallery_per_page() {
	return max( 1, (int) apply_filters( 'byrm_gallery_per_page', 24 ) );
}

/**
 * The most pages the no-JavaScript fallback will stack into one request.
 *
 * Without it, ?gal=99999 would ask the database for every map at once — a free
 * denial of service for anyone who edits the URL.
 *
 * @return int
 */
function byrm_gallery_max_pages() {
	return max( 1, (int) apply_filters( 'byrm_gallery_max_pages', 20 ) );
}

/**
 * The gallery page's ID, or 0 before it has been created.
 *
 * Both slugs work, so the choice between them does not matter.
 *
 * @return int
 */
function byrm_gallery_page_id() {
	static $id = null;

	if ( null !== $id ) {
		return $id;
	}

	$id = 0;

	foreach ( array( 'gallery', 'map-gallery' ) as $slug ) {
		$page = get_page_by_path( $slug );

		if ( $page ) {
			$id = (int) $page->ID;
			break;
		}
	}

	return $id;
}

/**
 * Is this the image wall?
 *
 * @return bool
 */
function byrm_is_gallery_page() {
	return did_action( 'wp' ) && is_page( array( 'gallery', 'map-gallery' ) );
}

/**
 * Maps that have an image, newest first.
 *
 * Only maps with a featured image: this page is a wall of pictures, and a map
 * without one would be an empty frame in the middle of it.
 *
 * @param  int $page    Which page of results.
 * @param  int $stacked How many pages to return at once — the no-JS fallback
 *                      asks for 1..N so "Load more" genuinely appends.
 * @return WP_Query
 */
function byrm_gallery_query( $page = 1, $stacked = 1 ) {
	$page    = max( 1, (int) $page );
	$stacked = max( 1, (int) $stacked );
	$per     = byrm_gallery_per_page();

	return new WP_Query(
		array(
			'post_type'           => 'map',
			'post_status'         => 'publish',
			// Offset only, never 'paged': WP_Query ignores one when both are set,
			// and the stacked case needs page 1..N as a single run of posts.
			'posts_per_page'      => $per * $stacked,
			'offset'              => $stacked > 1 ? 0 : ( ( $page - 1 ) * $per ),
			'orderby'             => 'date',
			'order'               => 'DESC',
			'ignore_sticky_posts' => true,

			// A featured image is what makes a map eligible for this page.
			'meta_query'          => array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query -- indexed key, one query per page.
				array(
					'key'     => '_thumbnail_id',
					'compare' => 'EXISTS',
				),
			),
		)
	);
}

/**
 * One tile.
 *
 * Renders. Two real links, never nested: the map link IS the tile — image,
 * veil and caption all live inside it — and the download sits above it as a
 * sibling. An earlier version stretched a pseudo-element over the tile instead,
 * which silently covered only the caption, because the pseudo resolved against
 * the absolutely-positioned caption rather than the tile. Clicking the picture
 * did nothing, on a page whose whole purpose is clicking pictures.
 *
 * @param WP_Post $map Map to render.
 */
function byrm_gallery_tile( $map ) {
	$thumb = get_post_thumbnail_id( $map->ID );

	if ( ! $thumb ) {
		return;
	}

	$players  = function_exists( 'byrm_map_meta' ) ? byrm_map_meta( 'players', $map->ID ) : '';
	$theaters = get_the_terms( $map->ID, 'map_theater' );
	$theater  = ( $theaters && ! is_wp_error( $theaters ) ) ? $theaters[0]->name : '';
	$download = function_exists( 'byrm_map_download_url' ) ? byrm_map_download_url( $map->ID ) : '';
	$link     = get_permalink( $map );
	$title    = get_the_title( $map );
	?>
	<article class="byrm-gal__tile">
		<a class="byrm-gal__link" href="<?php echo esc_url( $link ); ?>">
			<?php
			echo wp_get_attachment_image(
				$thumb,
				'medium_large',
				false,
				array(
					'class'    => 'byrm-gal__img',
					'alt'      => '',
					'loading'  => 'lazy',
					'decoding' => 'async',
					'sizes'    => '(max-width: 600px) 100vw, (max-width: 1100px) 50vw, 33vw',
				)
			);
			?>

			<span class="byrm-gal__veil" aria-hidden="true"></span>

			<span class="byrm-gal__cap">
				<h2 class="byrm-gal__name"><?php echo esc_html( $title ); ?></h2>

				<?php if ( $players || $theater ) : ?>
					<span class="byrm-gal__meta">
						<?php if ( $players ) : ?>
							<?php
							printf(
								/* translators: %s: number of players */
								esc_html__( '%s players', 'astra-child' ),
								esc_html( $players )
							);
							?>
						<?php endif; ?>
						<?php if ( $players && $theater ) : ?>
							<span aria-hidden="true">&middot;</span>
						<?php endif; ?>
						<?php if ( $theater ) : ?>
							<?php echo esc_html( $theater ); ?>
						<?php endif; ?>
					</span>
				<?php endif; ?>
			</span>
		</a>

		<?php if ( $download ) : ?>
			<a
				class="byrm-gal__dl"
				href="<?php echo esc_url( $download ); ?>"
				target="_blank"
				rel="noopener"
				aria-label="
				<?php
				printf(
					/* translators: %s: map title */
					esc_attr__( 'Download %s (opens in a new tab)', 'astra-child' ),
					esc_attr( $title )
				);
				?>
				">
				<svg viewBox="0 0 24 24" aria-hidden="true" focusable="false">
					<path d="M12 4v11M7 11l5 5 5-5M5 20h14" stroke-linecap="round" stroke-linejoin="round"/>
				</svg>
			</a>
		<?php endif; ?>
	</article>
	<?php
}

/**
 * The REST route the scroll loader calls for the next page of tiles.
 *
 * It returns rendered HTML rather than JSON for the script to assemble, so the
 * tile markup lives in exactly one place and a change to it cannot leave the
 * two halves of this page disagreeing.
 */
function byrm_gallery_rest_route() {
	register_rest_route(
		'byrm/v1',
		'/gallery',
		array(
			'methods'             => WP_REST_Server::READABLE,
			'callback'            => 'byrm_gallery_rest',

			// Published maps are public; this reads nothing else.
			'permission_callback' => '__return_true',
			'args'                => array(
				'page' => array(
					'type'              => 'integer',
					'default'           => 2,
					'sanitize_callback' => 'absint',
				),
			),
		)
	);
}
add_action( 'rest_api_init', 'byrm_gallery_rest_route' );

/**
 * @param  WP_REST_Request $request Incoming request.
 * @return WP_REST_Response
 */
function byrm_gallery_rest( $request ) {
	$page  = max( 1, (int) $request->get_param( 'page' ) );
	$query = byrm_gallery_query( $page );

	ob_start();

	foreach ( $query->posts as $map ) {
		byrm_gallery_tile( $map );
	}

	$html = (string) ob_get_clean();

	// found_posts comes from SQL_CALC_FOUND_ROWS, which ignores LIMIT and OFFSET,
	// so it is the real total. max_num_pages is not trustworthy once an offset
	// is in play, so it is not used.
	$seen = $page * byrm_gallery_per_page();

	return rest_ensure_response(
		array(
			'html'     => $html,
			'page'     => $page,
			'has_more' => $seen < (int) $query->found_posts,
		)
	);
}

/**
 * Send a page called "map-gallery" through page-gallery.php.
 *
 * WordPress picks page-gallery.php on its own for the slug "gallery"; it would
 * look for page-map-gallery.php for the other, so that slug is routed here
 * rather than keeping two copies of the template.
 *
 * @param  string $template Template WordPress chose.
 * @return string
 */
function byrm_gallery_template( $template ) {
	if ( ! byrm_is_gallery_page() ) {
		return $template;
	}

	// A template WordPress picked by slug already wins.
	if ( 0 === strpos( basename( $template ), 'page-' ) ) {
		return $template;
	}

	$gallery = locate_template( 'page-gallery.php' );

	return $gallery ? $gallery : $template;
}
add_filter( 'template_include', 'byrm_gallery_template' );
