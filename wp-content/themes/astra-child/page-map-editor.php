<?php
/**
 * Map tweaker — /map-editor/
 *
 * WordPress uses this automatically for a Page whose slug is "map-editor".
 *
 * The form posts back to this same page. byrm_tweak_handle() picks it up on
 * template_redirect: on success it streams the edited map straight back as a
 * download and the visitor never leaves this page, and on any failure it
 * redirects here with ?byrm_err= so the problem can be shown in context.
 *
 * @package Astra_Child
 */

defined( 'ABSPATH' ) || exit;

byrm_open_document();

while ( have_posts() ) :
	the_post();

	$tweaks   = byrm_tweaks();
	$limits   = byrm_tweak_limits();
	$messages = byrm_tweak_errors();
	$maps_url = post_type_exists( 'map' ) ? get_post_type_archive_link( 'map' ) : home_url( '/maps/' );

	$error_code = isset( $_GET['byrm_err'] ) ? sanitize_key( wp_unslash( $_GET['byrm_err'] ) ) : '';
	$error      = isset( $messages[ $error_code ] ) ? $messages[ $error_code ] : '';
	?>

	<div id="byrm-content" class="byrm-tool">

		<!-- --------------------------------------------------------- banner -->
		<header class="byrm-tbanner">
			<div class="byrm-tbanner__veil" aria-hidden="true"></div>

			<div class="byrm-tool__shell byrm-tbanner__inner">
				<nav class="byrm-tcrumbs" aria-label="<?php esc_attr_e( 'Breadcrumb', 'astra-child' ); ?>">
					<a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Home', 'astra-child' ); ?></a>
					<span aria-hidden="true">/</span>
					<span aria-current="page"><?php the_title(); ?></span>
				</nav>

				<p class="byrm-tbanner__eyebrow"><?php esc_html_e( 'Tool', 'astra-child' ); ?></p>
				<h1 class="byrm-tbanner__title"><?php the_title(); ?></h1>
				<p class="byrm-tbanner__lead">
					<?php esc_html_e( 'Upload a map, tick what you want changed, and get the edited file back straight away. Nothing is saved on our server — the map is edited as it passes through.', 'astra-child' ); ?>
				</p>
			</div>
		</header>

		<div class="byrm-tool__shell byrm-tool__body">

			<?php if ( $error ) : ?>
				<p class="byrm-terror" role="alert">
					<strong><?php esc_html_e( 'That did not work.', 'astra-child' ); ?></strong>
					<?php echo esc_html( $error ); ?>
				</p>
			<?php endif; ?>

			<form id="byrm-tweak-form" class="byrm-tform" method="post"
			      action="<?php echo esc_url( get_permalink() ); ?>"
			      enctype="multipart/form-data" data-byrm-tweak-form>

				<?php wp_nonce_field( 'byrm_tweak', 'byrm_tweak_nonce' ); ?>

				<!-- ------------------------------------------------- step one -->
				<section class="byrm-tstep">
					<div class="byrm-tstep__num" aria-hidden="true">1</div>

					<div class="byrm-tstep__body">
						<h2 class="byrm-tstep__title"><?php esc_html_e( 'Choose your map file', 'astra-child' ); ?></h2>
						<p class="byrm-tstep__note">
							<?php
							printf(
								/* translators: 1: list of file extensions, 2: maximum file size. */
								esc_html__( 'A %1$s file, up to %2$s. If yours is inside a zip, unzip it first.', 'astra-child' ),
								esc_html( '.' . implode( ', .', $limits['extensions'] ) ),
								esc_html( size_format( $limits['bytes'] ) )
							);
							?>
						</p>

						<div class="byrm-tdrop" data-byrm-drop>
							<input class="byrm-tdrop__input" type="file" id="byrm-map-file" name="byrm_map"
							       accept=".<?php echo esc_attr( implode( ',.', $limits['extensions'] ) ); ?>" required>

							<label class="byrm-tdrop__face" for="byrm-map-file">
								<span class="byrm-tdrop__icon" aria-hidden="true">
									<svg viewBox="0 0 24 24" focusable="false">
										<path d="M12 16V4M7 9l5-5 5 5M4 20h16" stroke-linecap="round" stroke-linejoin="round"/>
									</svg>
								</span>
								<span class="byrm-tdrop__label" data-byrm-drop-label>
									<?php esc_html_e( 'Choose a file, or drag one here', 'astra-child' ); ?>
								</span>
							</label>
						</div>
					</div>
				</section>

				<!-- ------------------------------------------------- step two -->
				<section class="byrm-tstep">
					<div class="byrm-tstep__num" aria-hidden="true">2</div>

					<div class="byrm-tstep__body">
						<h2 class="byrm-tstep__title"><?php esc_html_e( 'Pick what to change', 'astra-child' ); ?></h2>
						<p class="byrm-tstep__note"><?php esc_html_e( 'As many as you like. They all go into the same file.', 'astra-child' ); ?></p>

						<div class="byrm-topts">
							<?php foreach ( $tweaks as $key => $tweak ) : ?>
								<label class="byrm-topt" for="byrm-tweak-<?php echo esc_attr( $key ); ?>">
									<input class="byrm-topt__box" type="checkbox"
									       id="byrm-tweak-<?php echo esc_attr( $key ); ?>"
									       name="byrm_tweak[]" value="<?php echo esc_attr( $key ); ?>"
									       data-byrm-opt>
									<span class="byrm-topt__text">
										<span class="byrm-topt__label"><?php echo esc_html( $tweak['label'] ); ?></span>
										<span class="byrm-topt__note"><?php echo esc_html( $tweak['note'] ); ?></span>
									</span>
								</label>
							<?php endforeach; ?>
						</div>
					</div>
				</section>

				<!-- ----------------------------------------------- step three -->
				<section class="byrm-tstep">
					<div class="byrm-tstep__num" aria-hidden="true">3</div>

					<div class="byrm-tstep__body">
						<h2 class="byrm-tstep__title"><?php esc_html_e( 'Download the edited map', 'astra-child' ); ?></h2>
						<p class="byrm-tstep__note"><?php esc_html_e( 'The file comes straight back to your browser. The original is untouched.', 'astra-child' ); ?></p>

						<div class="byrm-tgo">
							<button class="byrm-btn byrm-btn--primary byrm-tgo__submit" type="submit">
								<?php esc_html_e( 'Edit my map', 'astra-child' ); ?>
							</button>
							<span class="byrm-tgo__count" data-byrm-count aria-live="polite"></span>
						</div>
					</div>
				</section>
			</form>

			<!-- --------------------------------------------- things to know -->
			<section class="byrm-tfacts" aria-labelledby="byrm-tknow">
				<h2 id="byrm-tknow" class="byrm-tfacts__title"><?php esc_html_e( 'Worth knowing', 'astra-child' ); ?></h2>

				<div class="byrm-tfact">
					<h3><?php esc_html_e( 'Online, everyone needs the same file', 'astra-child' ); ?></h3>
					<p><?php esc_html_e( 'Editing a map changes it, so it no longer matches the copy anybody else has. For a game on CnCNet, send the edited file to the other players and have them all use it. On your own against the computer, none of that matters.', 'astra-child' ); ?></p>
				</div>

				<div class="byrm-tfact">
					<h3><?php esc_html_e( 'The terrain is never touched', 'astra-child' ); ?></h3>
					<p><?php esc_html_e( 'Only the game rules in the file are changed. The map itself — the ground, the ore, the starting positions, the preview image — comes out exactly as it went in.', 'astra-child' ); ?></p>
				</div>

				<div class="byrm-tfact">
					<h3><?php esc_html_e( 'Nothing is kept', 'astra-child' ); ?></h3>
					<p><?php esc_html_e( 'Your map is edited in memory and handed straight back. It is never written to disk here and there is no copy of it afterwards.', 'astra-child' ); ?></p>
				</div>

				<div class="byrm-tfact">
					<h3><?php esc_html_e( 'Try it before you rely on it', 'astra-child' ); ?></h3>
					<p><?php esc_html_e( 'Load the edited map in a skirmish first and check it plays how you expected. If a change does not do what you wanted, say so on the board and it gets adjusted.', 'astra-child' ); ?></p>
				</div>
			</section>

			<?php
			// Anything typed into the page editor renders here.
			$extra = trim( get_the_content() );

			if ( '' !== $extra ) :
				?>
				<section class="byrm-tnote"><?php the_content(); ?></section>
			<?php endif; ?>

			<div class="byrm-tcta">
				<h2><?php esc_html_e( 'Need a map to edit?', 'astra-child' ); ?></h2>
				<p><?php esc_html_e( 'Every map on this site works with this tool.', 'astra-child' ); ?></p>
				<a class="byrm-btn byrm-btn--primary" href="<?php echo esc_url( $maps_url ); ?>">
					<?php esc_html_e( 'Browse the maps', 'astra-child' ); ?>
				</a>
			</div>
		</div>
	</div>

	<?php
endwhile;

byrm_close_document();
