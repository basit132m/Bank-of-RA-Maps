<?php
/**
 * The image wall — /gallery/ (or /map-gallery/).
 *
 * WordPress uses this automatically for a Page whose slug is "gallery";
 * byrm_gallery_template() routes "map-gallery" here as well.
 *
 * Nothing but minimaps. The title and player count sit on the image rather than
 * under it, so the grid stays a wall of pictures, and they are always visible
 * rather than appearing on hover — a caption nobody can reach with a keyboard
 * or a touchscreen is not a caption.
 *
 * @package Astra_Child
 */

defined( 'ABSPATH' ) || exit;

byrm_open_document();

// How many pages deep the visitor is. Only the no-JavaScript path uses this;
// with the script running, everything after the first page arrives over REST.
$stacked = isset( $_GET['gal'] ) ? absint( wp_unslash( $_GET['gal'] ) ) : 1; // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- a page number, not an action.
$stacked = min( max( 1, $stacked ), byrm_gallery_max_pages() );

$per    = byrm_gallery_per_page();
$query  = byrm_gallery_query( 1, $stacked );
$total  = (int) $query->found_posts;
$shown  = count( $query->posts );
$more   = $shown < $total;

$maps_url = post_type_exists( 'map' ) ? get_post_type_archive_link( 'map' ) : home_url( '/maps/' );

// Where "Load more" points without JavaScript: the same page, one deeper.
$next_url = add_query_arg( 'gal', $stacked + 1, get_permalink() ) . '#byrm-gal-more';
?>

<div id="byrm-content" class="byrm-gal">

	<!-- ------------------------------------------------------------ head -->
	<header class="byrm-gal__head">
		<div class="byrm-gal__veilhead" aria-hidden="true"></div>

		<div class="byrm-gal__shell byrm-gal__headinner">
			<nav class="byrm-crumbs" aria-label="<?php esc_attr_e( 'Breadcrumb', 'astra-child' ); ?>">
				<a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Home', 'astra-child' ); ?></a>
				<span aria-hidden="true">/</span>
				<span aria-current="page"><?php the_title(); ?></span>
			</nav>

			<p class="byrm-gal__eyebrow"><?php esc_html_e( 'Browse by image', 'astra-child' ); ?></p>
			<h1 class="byrm-gal__title"><?php the_title(); ?></h1>
			<p class="byrm-gal__lead">
				<?php esc_html_e( 'Every map in the catalogue, as a wall of minimaps. Pick one by eye — the shape of a map tells you more about how it plays than any list of numbers.', 'astra-child' ); ?>
			</p>

			<?php if ( $total > 0 ) : ?>
				<p class="byrm-gal__stat">
					<?php
					printf(
						/* translators: %s: number of maps */
						esc_html( _n( '%s map', '%s maps', $total, 'astra-child' ) ),
						esc_html( number_format_i18n( $total ) )
					);
					?>
				</p>
			<?php endif; ?>
		</div>
	</header>

	<div class="byrm-gal__shell byrm-gal__body">

		<?php if ( $query->posts ) : ?>

			<?php
			// Anything typed into the page editor renders above the wall.
			$extra = trim( get_the_content() );

			if ( '' !== $extra ) :
				?>
				<section class="byrm-gal__intro"><?php the_content(); ?></section>
			<?php endif; ?>

			<div
				class="byrm-gal__grid"
				data-byrm-gallery
				data-rest="<?php echo esc_url( rest_url( 'byrm/v1/gallery' ) ); ?>"
				data-page="<?php echo esc_attr( (string) $stacked ); ?>">
				<?php
				foreach ( $query->posts as $map ) {
					byrm_gallery_tile( $map );
				}
				?>
			</div>

			<?php // Announced to screen readers as tiles arrive; silent otherwise. ?>
			<p class="byrm-gal__sr" role="status" aria-live="polite" data-byrm-gal-status></p>

			<div class="byrm-gal__foot">
				<?php if ( $more ) : ?>
					<?php
					// A real link, not a button: without JavaScript it loads the
					// next page, and the script upgrades it in place.
					?>
					<a
						class="byrm-btn byrm-btn--ghost byrm-gal__more"
						id="byrm-gal-more"
						href="<?php echo esc_url( $next_url ); ?>"
						data-byrm-gal-more>
						<?php esc_html_e( 'Load more maps', 'astra-child' ); ?>
					</a>
				<?php else : ?>
					<p class="byrm-gal__end" data-byrm-gal-end>
						<?php esc_html_e( 'That is every map.', 'astra-child' ); ?>
						<a href="<?php echo esc_url( $maps_url ); ?>"><?php esc_html_e( 'Browse with filters instead', 'astra-child' ); ?></a>
					</p>
				<?php endif; ?>
			</div>

		<?php else : ?>
			<div class="byrm-empty">
				<span class="byrm-empty__mark" aria-hidden="true">
					<svg viewBox="0 0 48 48" focusable="false">
						<path d="M24 4 6 12v14c0 10 7.6 17.6 18 20 10.4-2.4 18-10 18-20V12L24 4Z"/>
						<path d="M15 24h18M24 15v18"/>
					</svg>
				</span>
				<h2><?php esc_html_e( 'No map images yet', 'astra-child' ); ?></h2>
				<p>
					<?php esc_html_e( 'This page shows maps that have a featured image set. Add one to a map and it appears here straight away.', 'astra-child' ); ?>
				</p>
				<a class="byrm-btn byrm-btn--primary" href="<?php echo esc_url( $maps_url ); ?>">
					<?php esc_html_e( 'Browse the maps', 'astra-child' ); ?>
				</a>
			</div>
		<?php endif; ?>
	</div>
</div>

<?php
wp_reset_postdata();

byrm_close_document();
