<?php
/**
 * Single mod.
 *
 * Deliberately the same page as a map: banner, screenshots, description, then a
 * download panel and the details beside it. A visitor should not have to learn
 * a second layout, so this reuses map.css rather than inventing one — only the
 * fields in the aside differ, because player count and terrain mean nothing for
 * a mod and "what it needs" and "will it break multiplayer" mean everything.
 *
 * Requires the "Bank of YR Maps" plugin for the mod post type and its fields;
 * without it, WordPress never routes here.
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
		return function_exists( 'byrm_mod_meta' ) ? byrm_mod_meta( $key, $post_id ) : '';
	};

	$version     = $meta( 'mod_version' );
	$author      = $meta( 'mod_author' );
	$homepage    = $meta( 'mod_homepage' );
	$multiplayer = '1' === (string) $meta( 'mod_multiplayer' );
	$notes       = (string) $meta( 'install_notes' );
	$requires    = function_exists( 'byrm_mod_requires_label' ) ? byrm_mod_requires_label( $post_id ) : '';

	$types = get_the_terms( $post_id, 'mod_type' );
	$tags  = get_the_terms( $post_id, 'mod_tag' );

	$download_url = function_exists( 'byrm_map_download_url' ) ? byrm_map_download_url( $post_id ) : '';
	$file_size    = function_exists( 'byrm_map_file_size' ) ? byrm_map_file_size( $post_id ) : '';
	$file_type    = function_exists( 'byrm_map_file_type' ) ? byrm_map_file_type( $post_id ) : '';
	$file_host    = function_exists( 'byrm_map_file_host' ) ? byrm_map_file_host( $post_id ) : '';
	$downloads    = function_exists( 'byrm_map_downloads' ) ? byrm_map_downloads( $post_id ) : 0;

	$mods_url = post_type_exists( 'mod' ) ? get_post_type_archive_link( 'mod' ) : home_url( '/mods/' );
	$banner   = function_exists( 'byrm_map_banner_style' ) ? byrm_map_banner_style() : '';
	$related  = function_exists( 'byrm_related_mod_posts' ) ? byrm_related_mod_posts( $post_id ) : array();
	?>

	<div id="byrm-content" class="byrm-map byrm-mod">

		<!-- ------------------------------------------------------- banner -->
		<header class="byrm-mbanner" <?php echo $banner ? 'style="' . esc_attr( $banner ) . '"' : ''; ?>>
			<div class="byrm-mbanner__veil" aria-hidden="true"></div>

			<div class="byrm-map__shell byrm-mbanner__inner">
				<nav class="byrm-crumbs" aria-label="<?php esc_attr_e( 'Breadcrumb', 'astra-child' ); ?>">
					<a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Home', 'astra-child' ); ?></a>
					<span aria-hidden="true">/</span>
					<a href="<?php echo esc_url( $mods_url ); ?>"><?php esc_html_e( 'Mods', 'astra-child' ); ?></a>
					<span aria-hidden="true">/</span>
					<span aria-current="page"><?php the_title(); ?></span>
				</nav>

				<h1 class="byrm-mbanner__title"><?php the_title(); ?></h1>

				<ul class="byrm-mpills">
					<?php if ( $requires ) : ?>
						<li class="byrm-pill byrm-pill--key"><?php echo esc_html( $requires ); ?></li>
					<?php endif; ?>
					<?php if ( $types && ! is_wp_error( $types ) ) : ?>
						<?php foreach ( $types as $type ) : ?>
							<li class="byrm-pill"><?php echo esc_html( $type->name ); ?></li>
						<?php endforeach; ?>
					<?php endif; ?>
				</ul>

				<?php if ( $author ) : ?>
					<p class="byrm-mbanner__by">
						<?php
						/* translators: %s: the person or team who made the mod */
						printf( esc_html__( 'Made by %s', 'astra-child' ), '<strong>' . esc_html( $author ) . '</strong>' );
						?>
					</p>
				<?php endif; ?>
			</div>
		</header>

		<div class="byrm-map__shell byrm-map__layout">

			<main class="byrm-map__main">

				<?php if ( $images ) : ?>
					<section class="byrm-shots" aria-label="<?php esc_attr_e( 'Mod images', 'astra-child' ); ?>">
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
							</a>
						<?php endforeach; ?>
					</section>

					<p class="byrm-shots__hint">
						<?php esc_html_e( 'Hover to magnify. Click any image to open it full size.', 'astra-child' ); ?>
					</p>
				<?php endif; ?>

				<?php if ( trim( get_the_content() ) ) : ?>
					<section class="byrm-mblock">
						<h2><?php esc_html_e( 'What it does', 'astra-child' ); ?></h2>
						<div class="byrm-prose"><?php the_content(); ?></div>
					</section>
				<?php endif; ?>

				<!-- ------------------------------------------------ warning -->
				<?php if ( ! $multiplayer ) : ?>
					<section class="byrm-mblock">
						<div class="byrm-modwarn">
							<h2><?php esc_html_e( 'Read this before you install', 'astra-child' ); ?></h2>
							<p>
								<?php esc_html_e( 'A mod changes the game itself, not one map. Everyone in an online match needs the same files or the game will desync, so treat this as single-player unless every player installs it too.', 'astra-child' ); ?>
							</p>
							<p>
								<?php esc_html_e( 'Back up the files it replaces first. That is the difference between undoing a mod in a minute and reinstalling the game.', 'astra-child' ); ?>
							</p>
						</div>
					</section>
				<?php endif; ?>

				<section class="byrm-mblock">
					<h2><?php esc_html_e( 'Installing it', 'astra-child' ); ?></h2>
					<div class="byrm-prose">
						<?php if ( '' !== trim( $notes ) ) : ?>
							<?php echo wp_kses_post( wpautop( $notes ) ); ?>
						<?php else : ?>
							<p><?php esc_html_e( 'Download the file, extract it if it arrives zipped, and put the contents in your Red Alert 2 folder — the one holding the game executable. Keep a copy of anything it overwrites.', 'astra-child' ); ?></p>
						<?php endif; ?>

						<p>
							<a class="byrm-arrow" href="<?php echo esc_url( home_url( '/guides/install' ) ); ?>">
								<?php esc_html_e( 'Read the full install guide', 'astra-child' ); ?>
							</a>
						</p>
					</div>
				</section>

				<?php if ( $tags && ! is_wp_error( $tags ) ) : ?>
					<section class="byrm-mblock">
						<nav class="byrm-tagcloud" aria-label="<?php esc_attr_e( 'Mod tags', 'astra-child' ); ?>">
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
			<aside class="byrm-map__aside" aria-label="<?php esc_attr_e( 'Mod details', 'astra-child' ); ?>">
				<div class="byrm-aside__sticky">

					<div class="byrm-dl">
						<span class="byrm-dl__eyebrow"><?php esc_html_e( 'Download', 'astra-child' ); ?></span>

						<?php if ( $download_url ) : ?>
							<?php // New tab, so the mod page is still there to come back to. ?>
							<a
								class="byrm-dl__btn"
								href="<?php echo esc_url( $download_url ); ?>"
								target="_blank"
								rel="noopener"
								aria-label="<?php esc_attr_e( 'Get this mod (opens in a new tab)', 'astra-child' ); ?>">
								<svg viewBox="0 0 24 24" aria-hidden="true" focusable="false">
									<path d="M12 4v11M7 11l5 5 5-5M5 20h14" stroke-linecap="round" stroke-linejoin="round"/>
								</svg>
								<?php esc_html_e( 'Get this mod', 'astra-child' ); ?>
							</a>

							<ul class="byrm-dl__facts">
								<?php if ( $file_type ) : ?>
									<li><span><?php esc_html_e( 'File type', 'astra-child' ); ?></span><strong><?php echo esc_html( $file_type ); ?></strong></li>
								<?php endif; ?>
								<?php if ( $file_size ) : ?>
									<li><span><?php esc_html_e( 'File size', 'astra-child' ); ?></span><strong><?php echo esc_html( $file_size ); ?></strong></li>
								<?php endif; ?>
								<li><span><?php esc_html_e( 'Downloads', 'astra-child' ); ?></span><strong><?php echo esc_html( number_format_i18n( $downloads ) ); ?></strong></li>
								<li>
									<span><?php esc_html_e( 'Multiplayer', 'astra-child' ); ?></span>
									<?php if ( $multiplayer ) : ?>
										<strong class="byrm-ok"><?php esc_html_e( 'Safe', 'astra-child' ); ?></strong>
									<?php else : ?>
										<strong><?php esc_html_e( 'All players need it', 'astra-child' ); ?></strong>
									<?php endif; ?>
								</li>
							</ul>

							<?php if ( $file_host ) : ?>
								<p class="byrm-dl__host">
									<?php
									/* translators: %s: name of the file host, e.g. datadock-host.site */
									printf( esc_html__( 'Hosted on %s', 'astra-child' ), '<strong>' . esc_html( $file_host ) . '</strong>' );
									?>
								</p>
							<?php endif; ?>
						<?php else : ?>
							<p class="byrm-dl__empty"><?php esc_html_e( 'No download link for this mod yet.', 'astra-child' ); ?></p>
						<?php endif; ?>
					</div>

					<div class="byrm-specs">
						<h2><?php esc_html_e( 'Details', 'astra-child' ); ?></h2>
						<dl class="byrm-specs__list">
							<?php if ( $version ) : ?>
								<div><dt><?php esc_html_e( 'Version', 'astra-child' ); ?></dt><dd><?php echo esc_html( $version ); ?></dd></div>
							<?php endif; ?>
							<?php if ( $requires ) : ?>
								<div><dt><?php esc_html_e( 'Requires', 'astra-child' ); ?></dt><dd><?php echo esc_html( $requires ); ?></dd></div>
							<?php endif; ?>
							<?php if ( $author ) : ?>
								<div><dt><?php esc_html_e( 'Made by', 'astra-child' ); ?></dt><dd><?php echo esc_html( $author ); ?></dd></div>
							<?php endif; ?>
							<?php if ( $types && ! is_wp_error( $types ) ) : ?>
								<div><dt><?php esc_html_e( 'Type', 'astra-child' ); ?></dt><dd><?php echo esc_html( $types[0]->name ); ?></dd></div>
							<?php endif; ?>
							<?php if ( $homepage ) : ?>
								<div>
									<dt><?php esc_html_e( 'Official page', 'astra-child' ); ?></dt>
									<dd>
										<?php // An address an editor typed, pointing off-site: no referrer, no link equity. ?>
										<a href="<?php echo esc_url( $homepage ); ?>" target="_blank" rel="noopener nofollow">
											<?php echo esc_html( wp_parse_url( $homepage, PHP_URL_HOST ) ); ?>
										</a>
									</dd>
								</div>
							<?php endif; ?>
						</dl>
					</div>

					<div class="byrm-aside__note">
						<p><?php esc_html_e( 'Something not working, or the link dead?', 'astra-child' ); ?></p>
						<a class="byrm-arrow" href="<?php echo esc_url( home_url( '/community' ) ); ?>">
							<?php esc_html_e( 'Tell us on the community page', 'astra-child' ); ?>
						</a>
					</div>
				</div>
			</aside>
		</div>

		<?php if ( $related ) : ?>
			<section class="byrm-related">
				<div class="byrm-map__shell">
					<div class="byrm-related__head">
						<h2><?php esc_html_e( 'More mods', 'astra-child' ); ?></h2>
						<a class="byrm-arrow" href="<?php echo esc_url( $mods_url ); ?>">
							<?php esc_html_e( 'Browse all', 'astra-child' ); ?>
						</a>
					</div>

					<div class="byrm-related__grid">
						<?php foreach ( $related as $other ) : ?>
							<?php $other_type = get_the_terms( $other->ID, 'mod_type' ); ?>
							<article class="byrm-rcard">
								<a class="byrm-rcard__media" href="<?php echo esc_url( get_permalink( $other ) ); ?>" tabindex="-1" aria-hidden="true">
									<?php if ( has_post_thumbnail( $other->ID ) ) : ?>
										<?php echo get_the_post_thumbnail( $other->ID, 'medium_large', array( 'loading' => 'lazy', 'alt' => '' ) ); ?>
									<?php else : ?>
										<span class="byrm-rcard__blank" aria-hidden="true"></span>
									<?php endif; ?>
								</a>

								<div class="byrm-rcard__body">
									<h3><a href="<?php echo esc_url( get_permalink( $other ) ); ?>"><?php echo esc_html( get_the_title( $other ) ); ?></a></h3>
									<p class="byrm-rcard__meta">
										<?php if ( $other_type && ! is_wp_error( $other_type ) ) : ?>
											<?php echo esc_html( $other_type[0]->name ); ?>
										<?php endif; ?>
									</p>
								</div>
							</article>
						<?php endforeach; ?>
					</div>
				</div>
			</section>
		<?php endif; ?>
	</div>

	<?php
endwhile;

// The lightbox renders itself on wp_footer — see byrm_render_lightbox().

byrm_close_document();
