<?php
/**
 * Home page.
 *
 * WordPress uses front-page.php for the site's front page automatically, so no
 * page needs to be created or assigned in the admin. Content is pulled from
 * published maps; every section degrades gracefully while the catalogue is
 * still empty.
 *
 * @package Astra_Child
 */

defined( 'ABSPATH' ) || exit;

byrm_open_document();

$totals   = byrm_catalogue_totals();
$featured = byrm_featured_map();
$recent   = byrm_recent_maps( 6, $featured ? array( $featured->ID ) : array() );
$has_maps = $totals['maps'] > 0;

$discord = '';
if ( function_exists( 'byrm_header_config' ) ) {
	$config  = byrm_header_config();
	$discord = isset( $config['discord_url'] ) ? $config['discord_url'] : '';
}

$maps_url = post_type_exists( 'map' ) ? get_post_type_archive_link( 'map' ) : home_url( '/maps' );

// The newest map's artwork doubles as hero atmosphere, heavily veiled.
$hero_art = ( $featured && has_post_thumbnail( $featured->ID ) )
	? get_the_post_thumbnail_url( $featured->ID, 'full' )
	: '';
?>

<div id="byrm-content" class="byrm-home">

	<!-- ------------------------------------------------------------- hero -->
	<section class="byrm-hero<?php echo $hero_art ? ' has-art' : ''; ?>"
		<?php echo $hero_art ? 'style="--byrm-hero-art:url(' . esc_url( $hero_art ) . ')"' : ''; ?>>
		<div class="byrm-hero__veil" aria-hidden="true"></div>

		<div class="byrm-home__shell byrm-hero__inner">
			<p class="byrm-hero__eyebrow"><?php esc_html_e( "Red Alert 2 — Yuri's Revenge", 'astra-child' ); ?></p>

			<h1 class="byrm-hero__title">
				<?php esc_html_e( 'Maps built to be played,', 'astra-child' ); ?>
				<span><?php esc_html_e( 'not just downloaded.', 'astra-child' ); ?></span>
			</h1>

			<p class="byrm-hero__lead">
				<?php esc_html_e( 'Every map here is designed by hand, tested in real games, and published with honest notes on how it actually plays. No reskins, no filler.', 'astra-child' ); ?>
			</p>

			<div class="byrm-hero__actions">
				<a class="byrm-btn byrm-btn--primary" href="<?php echo esc_url( $maps_url ); ?>">
					<?php esc_html_e( 'Browse the maps', 'astra-child' ); ?>
				</a>
				<a class="byrm-btn byrm-btn--ghost" href="<?php echo esc_url( home_url( '/guides/install' ) ); ?>">
					<?php esc_html_e( 'How to install', 'astra-child' ); ?>
				</a>
			</div>

			<?php if ( $has_maps ) : ?>
				<dl class="byrm-hero__stats">
					<div>
						<dt><?php esc_html_e( 'Maps published', 'astra-child' ); ?></dt>
						<dd><?php echo esc_html( number_format_i18n( $totals['maps'] ) ); ?></dd>
					</div>
					<div>
						<dt><?php esc_html_e( 'Total downloads', 'astra-child' ); ?></dt>
						<dd><?php echo esc_html( number_format_i18n( $totals['downloads'] ) ); ?></dd>
					</div>
					<div>
						<dt><?php esc_html_e( 'Price', 'astra-child' ); ?></dt>
						<dd><?php esc_html_e( 'Free', 'astra-child' ); ?></dd>
					</div>
				</dl>
			<?php endif; ?>
		</div>
	</section>

	<?php if ( $featured ) : ?>
		<!-- --------------------------------------------------- featured map -->
		<?php
		$f_players  = function_exists( 'byrm_map_meta' ) ? byrm_map_meta( 'players', $featured->ID ) : '';
		$f_theaters = get_the_terms( $featured->ID, 'map_theater' );
		$f_theater  = ( $f_theaters && ! is_wp_error( $f_theaters ) ) ? $f_theaters[0]->name : '';
		$f_link     = get_permalink( $featured );
		$f_download = function_exists( 'byrm_map_download_url' ) ? byrm_map_download_url( $featured->ID ) : '';
		?>
		<section class="byrm-section byrm-section--featured">
			<div class="byrm-home__shell">
				<div class="byrm-section__head">
					<h2><?php esc_html_e( 'Featured map', 'astra-child' ); ?></h2>
					<a class="byrm-more" href="<?php echo esc_url( $maps_url ); ?>"><?php esc_html_e( 'All maps', 'astra-child' ); ?></a>
				</div>

				<article class="byrm-feature">
					<a class="byrm-feature__media" href="<?php echo esc_url( $f_link ); ?>" tabindex="-1" aria-hidden="true">
						<?php if ( has_post_thumbnail( $featured->ID ) ) : ?>
							<?php echo get_the_post_thumbnail( $featured->ID, 'large', array( 'alt' => '', 'decoding' => 'async' ) ); ?>
						<?php else : ?>
							<span class="byrm-mcard__blank" aria-hidden="true"></span>
						<?php endif; ?>
					</a>

					<div class="byrm-feature__body">
						<h3><a href="<?php echo esc_url( $f_link ); ?>"><?php echo esc_html( get_the_title( $featured ) ); ?></a></h3>

						<ul class="byrm-feature__pills">
							<?php if ( $f_players ) : ?>
								<li class="byrm-pill byrm-pill--key"><?php echo esc_html( $f_players ); ?> <?php esc_html_e( 'players', 'astra-child' ); ?></li>
							<?php endif; ?>
							<?php if ( $f_theater ) : ?>
								<li class="byrm-pill"><?php echo esc_html( $f_theater ); ?></li>
							<?php endif; ?>
						</ul>

						<?php if ( has_excerpt( $featured->ID ) ) : ?>
							<p class="byrm-feature__summary"><?php echo esc_html( get_the_excerpt( $featured ) ); ?></p>
						<?php endif; ?>

						<div class="byrm-feature__actions">
							<a class="byrm-btn byrm-btn--primary" href="<?php echo esc_url( $f_link ); ?>">
								<?php esc_html_e( 'Map details', 'astra-child' ); ?>
							</a>
							<?php if ( $f_download ) : ?>
								<a class="byrm-btn byrm-btn--ghost" href="<?php echo esc_url( $f_download ); ?>">
									<?php esc_html_e( 'Download', 'astra-child' ); ?>
								</a>
							<?php endif; ?>
						</div>
					</div>
				</article>
			</div>
		</section>
	<?php endif; ?>

	<!-- ------------------------------------------------------ latest maps -->
	<section class="byrm-section">
		<div class="byrm-home__shell">
			<div class="byrm-section__head">
				<h2><?php echo $featured ? esc_html__( 'Latest releases', 'astra-child' ) : esc_html__( 'The maps', 'astra-child' ); ?></h2>
				<?php if ( $has_maps ) : ?>
					<a class="byrm-more" href="<?php echo esc_url( $maps_url ); ?>"><?php esc_html_e( 'Browse all', 'astra-child' ); ?></a>
				<?php endif; ?>
			</div>

			<?php if ( $recent ) : ?>
				<div class="byrm-grid">
					<?php foreach ( $recent as $map ) : ?>
						<?php byrm_map_card( $map ); ?>
					<?php endforeach; ?>
				</div>

			<?php else : ?>
				<!-- Deliberate empty state: better than an empty grid while the
				     catalogue is still being built. -->
				<div class="byrm-empty">
					<span class="byrm-empty__mark" aria-hidden="true">
						<svg viewBox="0 0 48 48" focusable="false">
							<path d="M24 4 6 12v14c0 10 7.6 17.6 18 20 10.4-2.4 18-10 18-20V12L24 4Z"/>
							<path d="M15 24h18M24 15v18"/>
						</svg>
					</span>
					<h3><?php esc_html_e( 'The first maps are on the way', 'astra-child' ); ?></h3>
					<p>
						<?php esc_html_e( 'Nothing is published yet. New releases land here the moment they are ready — and get announced in the Discord first.', 'astra-child' ); ?>
					</p>
					<?php if ( $discord ) : ?>
						<a class="byrm-btn byrm-btn--primary" href="<?php echo esc_url( $discord ); ?>" target="_blank" rel="noopener noreferrer">
							<?php esc_html_e( 'Join the Discord', 'astra-child' ); ?>
						</a>
					<?php else : ?>
						<a class="byrm-btn byrm-btn--ghost" href="<?php echo esc_url( home_url( '/guides/install' ) ); ?>">
							<?php esc_html_e( 'Read the install guide', 'astra-child' ); ?>
						</a>
					<?php endif; ?>
				</div>
			<?php endif; ?>
		</div>
	</section>

	<?php
	// Theater shortcuts, shown only once maps actually carry those terms.
	$theaters = post_type_exists( 'map' )
		? get_terms(
			array(
				'taxonomy'   => 'map_theater',
				'hide_empty' => true,
			)
		)
		: array();

	if ( $theaters && ! is_wp_error( $theaters ) ) :
		?>
		<section class="byrm-section byrm-section--theaters">
			<div class="byrm-home__shell">
				<div class="byrm-section__head">
					<h2><?php esc_html_e( 'By terrain', 'astra-child' ); ?></h2>
				</div>

				<nav class="byrm-theaters" aria-label="<?php esc_attr_e( 'Browse by terrain', 'astra-child' ); ?>">
					<?php foreach ( $theaters as $theater ) : ?>
						<a class="byrm-theater" href="<?php echo esc_url( get_term_link( $theater ) ); ?>">
							<span class="byrm-theater__name"><?php echo esc_html( $theater->name ); ?></span>
							<span class="byrm-theater__count">
								<?php
								printf(
									/* translators: %s: number of maps */
									esc_html( _n( '%s map', '%s maps', $theater->count, 'astra-child' ) ),
									esc_html( number_format_i18n( $theater->count ) )
								);
								?>
							</span>
						</a>
					<?php endforeach; ?>
				</nav>
			</div>
		</section>
	<?php endif; ?>

	<!-- ----------------------------------------------------------- panels -->
	<section class="byrm-section byrm-section--split">
		<div class="byrm-home__shell byrm-split">
			<div class="byrm-panel">
				<h2><?php esc_html_e( 'New to custom maps?', 'astra-child' ); ?></h2>
				<p>
					<?php esc_html_e( 'Installing one takes about thirty seconds, once you know where the folder is. The path differs between CnCNet, Steam, Origin and the original CD release — the guide covers all four.', 'astra-child' ); ?>
				</p>
				<a class="byrm-btn byrm-btn--ghost" href="<?php echo esc_url( home_url( '/guides/install' ) ); ?>">
					<?php esc_html_e( 'Read the install guide', 'astra-child' ); ?>
				</a>
			</div>

			<div class="byrm-panel byrm-panel--community">
				<h2><?php esc_html_e( 'Play with other people', 'astra-child' ); ?></h2>
				<p>
					<?php esc_html_e( 'Maps are better with opponents. The community is where games get organised, feedback gets given, and new releases get announced first.', 'astra-child' ); ?>
				</p>
				<?php if ( $discord ) : ?>
					<a class="byrm-btn byrm-btn--discord" href="<?php echo esc_url( $discord ); ?>" target="_blank" rel="noopener noreferrer">
						<?php esc_html_e( 'Join the Discord', 'astra-child' ); ?>
					</a>
				<?php else : ?>
					<a class="byrm-btn byrm-btn--ghost" href="<?php echo esc_url( home_url( '/community' ) ); ?>">
						<?php esc_html_e( 'See the community page', 'astra-child' ); ?>
					</a>
				<?php endif; ?>
			</div>
		</div>
	</section>
</div>

<?php
byrm_close_document();
