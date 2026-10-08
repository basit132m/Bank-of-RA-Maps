<?php
/**
 * The pause between the download button and the file.
 *
 * Reached at /map-download/{id}/, which the plugin routes here. The button on
 * this page points at /map-download/{id}/go/, and that is what counts the
 * download and forwards to the file host — so the host's URL is not in this
 * page's source, and the count still only moves when somebody really leaves.
 *
 * The wait is only worth imposing if it gives something back, so the time is
 * spent showing what the file is, where it is hosted, how to install it, and
 * what else is worth playing. Nobody should sit here watching an empty number.
 *
 * Without JavaScript the countdown cannot run, so the <noscript> block hands
 * over the button straight away rather than stranding the visitor. A timer is a
 * courtesy, not a lock.
 *
 * @package Astra_Child
 */

defined( 'ABSPATH' ) || exit;

$post_id = get_the_ID();

$go_url   = function_exists( 'byrm_map_download_go_url' ) ? byrm_map_download_go_url( $post_id ) : '';
$seconds  = function_exists( 'byrm_download_wait_seconds' ) ? byrm_download_wait_seconds() : 10;
$meta     = static function ( $key ) use ( $post_id ) {
	return function_exists( 'byrm_map_meta' ) ? byrm_map_meta( $key, $post_id ) : '';
};

$file_type = function_exists( 'byrm_map_file_type' ) ? byrm_map_file_type( $post_id ) : '';
$file_size = function_exists( 'byrm_map_file_size' ) ? byrm_map_file_size( $post_id ) : '';
$file_host = function_exists( 'byrm_map_file_host' ) ? byrm_map_file_host( $post_id ) : '';

$players  = $meta( 'players' );
$version  = $meta( 'version' );
$theaters = get_the_terms( $post_id, 'map_theater' );
$theater  = ( $theaters && ! is_wp_error( $theaters ) ) ? $theaters[0]->name : '';

$map_url    = get_permalink( $post_id );
$guides_url = home_url( '/guides/' );
$install    = get_page_by_path( 'install' );
$install_url = $install ? get_permalink( $install ) : $guides_url;

$related = function_exists( 'byrm_related_maps' ) ? byrm_related_maps( $post_id, $players ) : array();

// The browser tab should say what is happening, not repeat the map's own title.
add_filter(
	'document_title_parts',
	static function ( $parts ) {
		$parts['title'] = __( 'Preparing your download', 'astra-child' );

		return $parts;
	}
);

byrm_open_document();
?>

<div id="byrm-content" class="byrm-guide byrm-dlwait">

	<!-- ------------------------------------------------------------- head -->
	<header class="byrm-gbanner">
		<div class="byrm-gbanner__veil" aria-hidden="true"></div>

		<div class="byrm-guide__shell byrm-gbanner__inner">
			<nav class="byrm-crumbs" aria-label="<?php esc_attr_e( 'Breadcrumb', 'astra-child' ); ?>">
				<a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Home', 'astra-child' ); ?></a>
				<span aria-hidden="true">/</span>
				<a href="<?php echo esc_url( $map_url ); ?>"><?php echo esc_html( get_the_title( $post_id ) ); ?></a>
				<span aria-hidden="true">/</span>
				<span aria-current="page"><?php esc_html_e( 'Download', 'astra-child' ); ?></span>
			</nav>

			<p class="byrm-gbanner__eyebrow"><?php esc_html_e( 'Preparing your download', 'astra-child' ); ?></p>
			<h1 class="byrm-gbanner__title"><?php echo esc_html( get_the_title( $post_id ) ); ?></h1>
			<p class="byrm-gbanner__lead">
				<?php
				if ( $go_url ) {
					esc_html_e( 'Your file is on its way. The button appears below in a moment — nothing to click until then.', 'astra-child' );
				} else {
					esc_html_e( 'There is no file attached to this map yet, so there is nothing to wait for.', 'astra-child' );
				}
				?>
			</p>
		</div>
	</header>

	<div class="byrm-guide__shell byrm-guide__body">

		<!-- -------------------------------------------------------- timer -->
		<section
			class="byrm-dlwait__panel"
			data-byrm-countdown
			data-seconds="<?php echo esc_attr( (string) $seconds ); ?>"
			aria-labelledby="byrm-dlwait-title">

			<h2 id="byrm-dlwait-title" class="byrm-dlwait__sr"><?php esc_html_e( 'Download', 'astra-child' ); ?></h2>

			<?php if ( $go_url ) : ?>

				<!-- counting down ------------------------------------------>
				<div class="byrm-dlwait__counting" data-byrm-counting>
					<div class="byrm-dlring">
						<svg viewBox="0 0 120 120" aria-hidden="true" focusable="false">
							<circle class="byrm-dlring__track" cx="60" cy="60" r="52"></circle>
							<circle class="byrm-dlring__fill" cx="60" cy="60" r="52" data-byrm-ring></circle>
						</svg>
						<span class="byrm-dlring__num" data-byrm-number aria-hidden="true"><?php echo esc_html( number_format_i18n( $seconds ) ); ?></span>
					</div>

					<p class="byrm-dlwait__status" role="status" data-byrm-status>
						<?php
						printf(
							/* translators: %s: number of seconds remaining. */
							esc_html( _n( 'Your download will be ready in %s second.', 'Your download will be ready in %s seconds.', $seconds, 'astra-child' ) ),
							esc_html( number_format_i18n( $seconds ) )
						);
						?>
					</p>
				</div>

				<!-- ready -------------------------------------------------->
				<div class="byrm-dlwait__ready" data-byrm-ready hidden>
					<p class="byrm-dlwait__go"><?php esc_html_e( 'Ready.', 'astra-child' ); ?></p>

					<a class="byrm-dlwait__btn" href="<?php echo esc_url( $go_url ); ?>" data-byrm-go rel="nofollow">
						<svg viewBox="0 0 24 24" aria-hidden="true" focusable="false">
							<path d="M12 4v11M7 11l5 5 5-5M5 20h14" stroke-linecap="round" stroke-linejoin="round"/>
						</svg>
						<?php esc_html_e( 'Download the map', 'astra-child' ); ?>
					</a>
				</div>

				<noscript>
					<?php // No JavaScript means no countdown. Stranding the visitor would be worse than skipping the wait. ?>
					<p class="byrm-dlwait__go"><?php esc_html_e( 'Ready.', 'astra-child' ); ?></p>

					<a class="byrm-dlwait__btn" href="<?php echo esc_url( $go_url ); ?>" rel="nofollow">
						<?php esc_html_e( 'Download the map', 'astra-child' ); ?>
					</a>
				</noscript>

				<ul class="byrm-dlwait__facts">
					<?php if ( $file_type ) : ?>
						<li><span><?php esc_html_e( 'File type', 'astra-child' ); ?></span><strong><?php echo esc_html( $file_type ); ?></strong></li>
					<?php endif; ?>
					<?php if ( $file_size ) : ?>
						<li><span><?php esc_html_e( 'File size', 'astra-child' ); ?></span><strong><?php echo esc_html( $file_size ); ?></strong></li>
					<?php endif; ?>
					<?php if ( $players ) : ?>
						<li><span><?php esc_html_e( 'Players', 'astra-child' ); ?></span><strong><?php echo esc_html( $players ); ?></strong></li>
					<?php endif; ?>
					<?php if ( $theater ) : ?>
						<li><span><?php esc_html_e( 'Theater', 'astra-child' ); ?></span><strong><?php echo esc_html( $theater ); ?></strong></li>
					<?php endif; ?>
					<?php if ( $version ) : ?>
						<li><span><?php esc_html_e( 'Version', 'astra-child' ); ?></span><strong><?php echo esc_html( $version ); ?></strong></li>
					<?php endif; ?>
				</ul>

				<?php if ( $file_host ) : ?>
					<p class="byrm-dlwait__host">
						<?php
						/* translators: %s: name of the file host, e.g. datadock-host.site */
						printf( esc_html__( 'The file is hosted on %s. The button above takes you there.', 'astra-child' ), '<strong>' . esc_html( $file_host ) . '</strong>' );
						?>
					</p>
				<?php endif; ?>

			<?php else : ?>
				<?php // Its own class: __go is a one-word label in small caps, which a sentence cannot live in. ?>
				<p class="byrm-dlwait__empty"><?php esc_html_e( 'This map has no download link yet. It will appear here as soon as one is added.', 'astra-child' ); ?></p>
				<a class="byrm-btn byrm-btn--ghost" href="<?php echo esc_url( $map_url ); ?>">
					<?php esc_html_e( 'Back to the map', 'astra-child' ); ?>
				</a>
			<?php endif; ?>
		</section>

		<!-- --------------------------------------------------- while you wait -->
		<?php if ( $go_url ) : ?>
			<section class="byrm-note byrm-note--info">
				<h2><?php esc_html_e( 'First time installing a map?', 'astra-child' ); ?></h2>
				<p>
					<?php esc_html_e( 'The file goes in your Red Alert 2 folder and shows up in the map list next time you start a skirmish. It takes about a minute.', 'astra-child' ); ?>
				</p>
				<p>
					<a class="byrm-arrow" href="<?php echo esc_url( $install_url ); ?>">
						<?php esc_html_e( 'Read the install guide', 'astra-child' ); ?>
					</a>
				</p>
			</section>
		<?php endif; ?>

		<?php if ( $related ) : ?>
			<section class="byrm-mblock" aria-labelledby="byrm-dlwait-more">
				<h2 id="byrm-dlwait-more"><?php esc_html_e( 'While you wait', 'astra-child' ); ?></h2>

				<div class="byrm-dlwait__grid">
					<?php
					foreach ( $related as $map ) {
						if ( function_exists( 'byrm_map_card' ) ) {
							byrm_map_card( $map );
						}
					}
					?>
				</div>
			</section>
		<?php endif; ?>

		<p class="byrm-dlwait__back">
			<a class="byrm-arrow" href="<?php echo esc_url( $map_url ); ?>">
				<?php esc_html_e( 'Back to the map page', 'astra-child' ); ?>
			</a>
		</p>
	</div>
</div>

<?php
wp_reset_postdata();

byrm_close_document();
