<?php
/**
 * Guides index — /guides/
 *
 * WordPress uses this automatically for a Page whose slug is "guides".
 *
 * The list builds itself from the child pages of /guides/, so publishing a new
 * guide is all it takes to have it appear here, and the page Order field sets
 * the sequence. Nothing on this page needs editing when a guide is added.
 *
 * @package Astra_Child
 */

defined( 'ABSPATH' ) || exit;

byrm_open_document();

while ( have_posts() ) :
	the_post();

	$guides   = byrm_guide_children();
	$featured = null;
	$rest     = array();
	$slug     = byrm_guide_featured_slug();

	foreach ( $guides as $guide ) {
		if ( '' !== $slug && $guide->post_name === $slug && ! $featured ) {
			$featured = $guide;
			continue;
		}

		$rest[] = $guide;
	}

	$maps_url = post_type_exists( 'map' ) ? get_post_type_archive_link( 'map' ) : home_url( '/maps/' );
	?>

	<div id="byrm-content" class="byrm-guide">

		<!-- --------------------------------------------------------- head -->
		<header class="byrm-gbanner">
			<div class="byrm-gbanner__veil" aria-hidden="true"></div>

			<div class="byrm-guide__shell byrm-gbanner__inner">
				<nav class="byrm-crumbs" aria-label="<?php esc_attr_e( 'Breadcrumb', 'astra-child' ); ?>">
					<a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Home', 'astra-child' ); ?></a>
					<span aria-hidden="true">/</span>
					<span aria-current="page"><?php the_title(); ?></span>
				</nav>

				<p class="byrm-gbanner__eyebrow"><?php esc_html_e( 'Guides', 'astra-child' ); ?></p>
				<h1 class="byrm-gbanner__title"><?php the_title(); ?></h1>
				<p class="byrm-gbanner__lead">
					<?php esc_html_e( 'Everything you need to get a map off this site and into a game — written for people who want to play, not to read documentation.', 'astra-child' ); ?>
				</p>
			</div>
		</header>

		<div class="byrm-guide__shell byrm-guide__body">

			<!-- ------------------------------------------------ start here -->
			<?php if ( $featured ) : ?>
				<?php $summary = byrm_guide_summary( $featured ); ?>
				<section class="byrm-gfeature" aria-labelledby="byrm-start-title">
					<p class="byrm-gfeature__eyebrow"><?php esc_html_e( 'Start here', 'astra-child' ); ?></p>

					<h2 id="byrm-start-title" class="byrm-gfeature__title">
						<a href="<?php echo esc_url( get_permalink( $featured ) ); ?>">
							<?php echo esc_html( get_the_title( $featured ) ); ?>
						</a>
					</h2>

					<?php if ( '' !== trim( $summary ) ) : ?>
						<p class="byrm-gfeature__lead"><?php echo esc_html( wp_trim_words( $summary, 36 ) ); ?></p>
					<?php endif; ?>

					<a class="byrm-btn byrm-btn--primary" href="<?php echo esc_url( get_permalink( $featured ) ); ?>">
						<?php esc_html_e( 'Read the guide', 'astra-child' ); ?>
					</a>
				</section>
			<?php endif; ?>

			<!-- ------------------------------------------------ every guide -->
			<?php if ( $rest ) : ?>
				<section class="byrm-mblock" aria-labelledby="byrm-all-title">
					<h2 id="byrm-all-title"><?php esc_html_e( 'More guides', 'astra-child' ); ?></h2>

					<div class="byrm-gindex">
						<?php foreach ( $rest as $index => $guide ) : ?>
							<?php byrm_guide_card( $guide, $index + 1 ); ?>
						<?php endforeach; ?>
					</div>
				</section>

			<?php elseif ( ! $featured ) : ?>
				<?php // Nothing filed under /guides/ at all yet. ?>
				<section class="byrm-note">
					<h2><?php esc_html_e( 'No guides published yet', 'astra-child' ); ?></h2>
					<p>
						<?php esc_html_e( 'Guides are Pages filed under this one. Create a child page of /guides/ and it appears here on its own — the Order field sets the sequence.', 'astra-child' ); ?>
					</p>
				</section>

			<?php else : ?>
				<?php // One guide so far. Saying so is better than an empty heading. ?>
				<section class="byrm-note">
					<h2><?php esc_html_e( 'More on the way', 'astra-child' ); ?></h2>
					<p>
						<?php esc_html_e( 'That is the only guide so far. Setting up CnCNet, playing Red Alert 2 with Yuri\'s Revenge maps, and making your own are the ones most asked for — tell us which you want first.', 'astra-child' ); ?>
					</p>
					<p>
						<a class="byrm-arrow" href="<?php echo esc_url( home_url( '/community' ) ); ?>">
							<?php esc_html_e( 'Ask on the community board', 'astra-child' ); ?>
						</a>
					</p>
				</section>
			<?php endif; ?>

			<?php
			// Anything typed into the page editor renders here, so notes can be
			// added without touching this file.
			$extra = trim( get_the_content() );

			if ( '' !== $extra ) :
				?>
				<section class="byrm-mblock byrm-prose"><?php the_content(); ?></section>
			<?php endif; ?>

			<!-- ----------------------------------------------- still stuck -->
			<section class="byrm-note byrm-note--warn">
				<h2><?php esc_html_e( 'The game itself is broken?', 'astra-child' ); ?></h2>
				<p>
					<?php esc_html_e( 'These guides cover getting maps into the game. If the game will not install, will not launch, or crashes, that is a question for the Command & Conquer forums — this site publishes maps, it is not a support channel for the game.', 'astra-child' ); ?>
				</p>
			</section>

			<div class="byrm-cta-panel">
				<h2><?php esc_html_e( 'Know how it works already?', 'astra-child' ); ?></h2>
				<p><?php esc_html_e( 'Go and pick something to play.', 'astra-child' ); ?></p>
				<a class="byrm-btn byrm-btn--primary" href="<?php echo esc_url( $maps_url ); ?>">
					<?php esc_html_e( 'Browse the maps', 'astra-child' ); ?>
				</a>
			</div>
		</div>
	</div>

	<?php
endwhile;

byrm_close_document();
