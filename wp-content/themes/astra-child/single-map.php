<?php
/**
 * Single map.
 *
 * The images are the point of this page, so the gallery leads and everything
 * else supports it. Requires the "Bank of YR Maps — Map Library" plugin for the
 * map post type and its fields; without it, WordPress never routes here.
 *
 * @package Astra_Child
 */

defined( 'ABSPATH' ) || exit;

byrm_open_document();

while ( have_posts() ) :
	the_post();

	$post_id = get_the_ID();

	// Gallery: the featured image leads, then the screenshots.
	$images = function_exists( 'byrm_map_gallery' ) ? byrm_map_gallery( $post_id ) : array();
	$thumb  = get_post_thumbnail_id( $post_id );

	if ( $thumb ) {
		array_unshift( $images, (int) $thumb );
	}

	$images = array_values( array_unique( array_filter( $images ) ) );

	$meta = static function ( $key ) use ( $post_id ) {
		return function_exists( 'byrm_map_meta' ) ? byrm_map_meta( $key, $post_id ) : '';
	};

	$players  = $meta( 'players' );
	$designer = $meta( 'designer' );
	$version  = $meta( 'version' );

	$theaters = get_the_terms( $post_id, 'map_theater' );
	$modes    = get_the_terms( $post_id, 'map_mode' );
	$tags     = get_the_terms( $post_id, 'map_tag' );

	$theater_name = ( $theaters && ! is_wp_error( $theaters ) ) ? $theaters[0]->name : '';

	$download_url = function_exists( 'byrm_map_download_url' ) ? byrm_map_download_url( $post_id ) : '';
	$file_size    = function_exists( 'byrm_map_file_size' ) ? byrm_map_file_size( $post_id ) : '';
	$file_type    = function_exists( 'byrm_map_file_type' ) ? byrm_map_file_type( $post_id ) : '';
	$file_host    = function_exists( 'byrm_map_file_host' ) ? byrm_map_file_host( $post_id ) : '';
	$downloads    = function_exists( 'byrm_map_downloads' ) ? byrm_map_downloads( $post_id ) : 0;

	$banner = byrm_map_banner_style();
	?>

	<div id="byrm-content" class="byrm-map">

		<!-- ------------------------------------------------------- banner -->
		<header class="byrm-mbanner" <?php echo $banner ? 'style="' . esc_attr( $banner ) . '"' : ''; ?>>
			<div class="byrm-mbanner__veil" aria-hidden="true"></div>

			<div class="byrm-map__shell byrm-mbanner__inner">
				<nav class="byrm-crumbs" aria-label="<?php esc_attr_e( 'Breadcrumb', 'astra-child' ); ?>">
					<a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Home', 'astra-child' ); ?></a>
					<span aria-hidden="true">/</span>
					<a href="<?php echo esc_url( get_post_type_archive_link( 'map' ) ); ?>"><?php esc_html_e( 'Maps', 'astra-child' ); ?></a>
					<span aria-hidden="true">/</span>
					<span aria-current="page"><?php the_title(); ?></span>
				</nav>

				<h1 class="byrm-mbanner__title"><?php the_title(); ?></h1>

				<ul class="byrm-mpills">
					<?php if ( $players ) : ?>
						<li class="byrm-pill byrm-pill--key"><?php echo esc_html( $players ); ?> <?php esc_html_e( 'players', 'astra-child' ); ?></li>
					<?php endif; ?>
					<?php if ( $theater_name ) : ?>
						<li class="byrm-pill"><?php echo esc_html( $theater_name ); ?></li>
					<?php endif; ?>
					<?php if ( $modes && ! is_wp_error( $modes ) ) : ?>
						<?php foreach ( $modes as $mode ) : ?>
							<li class="byrm-pill"><?php echo esc_html( $mode->name ); ?></li>
						<?php endforeach; ?>
					<?php endif; ?>
				</ul>

				<p class="byrm-mbanner__by">
					<?php if ( $version ) : ?>
						<?php esc_html_e( 'Version', 'astra-child' ); ?> <?php echo esc_html( $version ); ?>
					<?php endif; ?>
					<?php if ( $designer ) : ?>
						&middot; <?php esc_html_e( 'by', 'astra-child' ); ?> <strong><?php echo esc_html( $designer ); ?></strong>
					<?php endif; ?>
					&middot; <?php echo esc_html( get_the_date() ); ?>
				</p>
			</div>
		</header>

		<!-- -------------------------------------------------------- body -->
		<div class="byrm-map__shell byrm-map__layout">

			<main class="byrm-map__main">

				<?php if ( $images ) : ?>
					<section class="byrm-shots" aria-label="<?php esc_attr_e( 'Map images', 'astra-child' ); ?>">
						<?php
						foreach ( $images as $index => $image_id ) :
							$full = wp_get_attachment_image_src( $image_id, 'full' );

							if ( ! $full ) {
								continue;
							}

							$caption = wp_get_attachment_caption( $image_id );
							$alt     = get_post_meta( $image_id, '_wp_attachment_image_alt', true );
							$label   = $alt ? $alt : get_the_title() . ' — ' . sprintf(
								/* translators: %d: image number */
								esc_html__( 'image %d', 'astra-child' ),
								$index + 1
							);
							?>
							<a class="byrm-shot<?php echo 0 === $index ? ' byrm-shot--lead' : ''; ?>"
							   href="<?php echo esc_url( $full[0] ); ?>"
							   data-byrm-shot
							   data-index="<?php echo esc_attr( $index ); ?>"
							   data-full="<?php echo esc_url( $full[0] ); ?>"
							   data-width="<?php echo esc_attr( $full[1] ); ?>"
							   data-height="<?php echo esc_attr( $full[2] ); ?>"
							   data-caption="<?php echo esc_attr( $caption ); ?>"
							   data-alt="<?php echo esc_attr( $label ); ?>">
								<span class="byrm-shot__frame">
									<?php
									echo wp_get_attachment_image(
										$image_id,
										0 === $index ? 'large' : 'medium_large',
										false,
										array(
											'class'    => 'byrm-shot__img',
											'alt'      => $label,
											'loading'  => 0 === $index ? 'eager' : 'lazy',
											'decoding' => 'async',
										)
									);
									?>
									<span class="byrm-shot__glass" aria-hidden="true"></span>
								</span>

								<span class="byrm-shot__badge" aria-hidden="true">
									<svg viewBox="0 0 24 24" focusable="false">
										<circle cx="11" cy="11" r="7"/>
										<path d="m20 20-3.6-3.6M11 8v6M8 11h6"/>
									</svg>
								</span>

								<?php if ( 0 === $index && $thumb && (int) $thumb === (int) $image_id ) : ?>
									<span class="byrm-shot__tag"><?php esc_html_e( 'Minimap', 'astra-child' ); ?></span>
								<?php endif; ?>
							</a>
						<?php endforeach; ?>
					</section>

					<p class="byrm-shots__hint">
						<?php esc_html_e( 'Hover to magnify. Click any image to open it full size.', 'astra-child' ); ?>
					</p>
				<?php endif; ?>

				<?php if ( get_the_content() ) : ?>
					<section class="byrm-mblock">
						<h2><?php esc_html_e( "Designer's notes", 'astra-child' ); ?></h2>
						<div class="byrm-prose"><?php the_content(); ?></div>
					</section>
				<?php endif; ?>

				<section class="byrm-mblock">
					<h2><?php esc_html_e( 'Installing this map', 'astra-child' ); ?></h2>
					<div class="byrm-prose">
						<?php if ( $meta( 'install_notes' ) ) : ?>
							<?php echo wpautop( esc_html( (string) $meta( 'install_notes' ) ) ); ?>
						<?php else : ?>
							<p>
								<?php esc_html_e( 'Unzip if needed, then drop the file into your Yuri\'s Revenge folder — the one containing gamemd.exe. The map then appears in the skirmish and multiplayer map lists.', 'astra-child' ); ?>
							</p>
						<?php endif; ?>
						<p>
							<a class="byrm-arrow" href="<?php echo esc_url( home_url( '/guides/install' ) ); ?>">
								<?php esc_html_e( 'Full install guide for every version of the game', 'astra-child' ); ?>
							</a>
						</p>
					</div>
				</section>

				<?php if ( $tags && ! is_wp_error( $tags ) ) : ?>
					<section class="byrm-mblock">
						<h2><?php esc_html_e( 'Tags', 'astra-child' ); ?></h2>
						<nav class="byrm-tagcloud" aria-label="<?php esc_attr_e( 'Map tags', 'astra-child' ); ?>">
							<?php foreach ( $tags as $tag ) : ?>
								<a class="byrm-tag" href="<?php echo esc_url( get_term_link( $tag ) ); ?>">
									<?php echo esc_html( $tag->name ); ?>
								</a>
							<?php endforeach; ?>
						</nav>
					</section>
				<?php endif; ?>
			</main>

			<!-- ----------------------------------------------------- aside -->
			<aside class="byrm-map__aside" aria-label="<?php esc_attr_e( 'Map details', 'astra-child' ); ?>">
				<div class="byrm-aside__sticky">

					<div class="byrm-dl">
						<span class="byrm-dl__eyebrow"><?php esc_html_e( 'Download', 'astra-child' ); ?></span>

						<?php if ( $download_url ) : ?>
							<?php
							// Opens in a new tab so the map page — the screenshots, the
							// specs, the description — is still there to come back to.
							// The accessible name starts with the visible text and then
							// says what the link does, which is what a screen reader
							// needs and a sighted user cannot see from the icon.
							?>
							<a
								class="byrm-dl__btn"
								href="<?php echo esc_url( $download_url ); ?>"
								target="_blank"
								rel="noopener"
								aria-label="<?php esc_attr_e( 'Get this map (opens in a new tab)', 'astra-child' ); ?>">
								<svg viewBox="0 0 24 24" aria-hidden="true" focusable="false">
									<path d="M12 4v11M7 11l5 5 5-5M5 20h14" stroke-linecap="round" stroke-linejoin="round"/>
								</svg>
								<?php esc_html_e( 'Get this map', 'astra-child' ); ?>
							</a>

							<ul class="byrm-dl__facts">
								<?php if ( $file_type ) : ?>
									<li><span><?php esc_html_e( 'File type', 'astra-child' ); ?></span><strong><?php echo esc_html( $file_type ); ?></strong></li>
								<?php endif; ?>
								<?php if ( $file_size ) : ?>
									<li><span><?php esc_html_e( 'File size', 'astra-child' ); ?></span><strong><?php echo esc_html( $file_size ); ?></strong></li>
								<?php endif; ?>
								<li><span><?php esc_html_e( 'Downloads', 'astra-child' ); ?></span><strong><?php echo esc_html( number_format_i18n( $downloads ) ); ?></strong></li>
								<?php if ( '1' === (string) $meta( 'cncnet_ready' ) ) : ?>
									<li><span><?php esc_html_e( 'CnCNet', 'astra-child' ); ?></span><strong class="byrm-ok"><?php esc_html_e( 'Ready', 'astra-child' ); ?></strong></li>
								<?php endif; ?>
							</ul>

							<?php if ( $file_host ) : ?>
								<?php // Naming the host up front: nobody should have to click an unexplained third-party link to find out where it goes. ?>
								<p class="byrm-dl__host">
									<?php
									/* translators: %s: name of the file host, e.g. datadock-host.site */
									printf( esc_html__( 'Hosted on %s', 'astra-child' ), '<strong>' . esc_html( $file_host ) . '</strong>' );
									?>
								</p>
							<?php endif; ?>
						<?php else : ?>
							<p class="byrm-dl__empty"><?php esc_html_e( 'No download link for this map yet.', 'astra-child' ); ?></p>
						<?php endif; ?>
					</div>

					<div class="byrm-specs">
						<h2><?php esc_html_e( 'Specifications', 'astra-child' ); ?></h2>
						<dl class="byrm-specs__list">
							<?php if ( $players ) : ?>
								<div><dt><?php esc_html_e( 'Players', 'astra-child' ); ?></dt><dd><?php echo esc_html( $players ); ?></dd></div>
							<?php endif; ?>
							<?php if ( $theater_name ) : ?>
								<div><dt><?php esc_html_e( 'Theater', 'astra-child' ); ?></dt><dd><?php echo esc_html( $theater_name ); ?></dd></div>
							<?php endif; ?>
							<?php if ( $modes && ! is_wp_error( $modes ) ) : ?>
								<div><dt><?php esc_html_e( 'Modes', 'astra-child' ); ?></dt><dd><?php echo esc_html( implode( ', ', wp_list_pluck( $modes, 'name' ) ) ); ?></dd></div>
							<?php endif; ?>
							<?php if ( $meta( 'ore_density' ) ) : ?>
								<div><dt><?php esc_html_e( 'Ore density', 'astra-child' ); ?></dt><dd><?php echo esc_html( ucfirst( (string) $meta( 'ore_density' ) ) ); ?></dd></div>
							<?php endif; ?>
							<?php if ( $version ) : ?>
								<div><dt><?php esc_html_e( 'Version', 'astra-child' ); ?></dt><dd><?php echo esc_html( $version ); ?></dd></div>
							<?php endif; ?>
						</dl>
					</div>

					<div class="byrm-aside__note">
						<h2><?php esc_html_e( 'Played it?', 'astra-child' ); ?></h2>
						<p><?php esc_html_e( 'Found a starting position that is clearly stronger, or ore that runs dry too fast? That feedback is genuinely useful — maps here get revised.', 'astra-child' ); ?></p>
						<a class="byrm-arrow" href="<?php echo esc_url( home_url( '/community' ) ); ?>">
							<?php esc_html_e( 'Tell us on the community page', 'astra-child' ); ?>
						</a>
					</div>
				</div>
			</aside>
		</div>

		<?php byrm_related_maps( $post_id, $players ); ?>
	</div>

	<?php
endwhile;

byrm_close_document();
