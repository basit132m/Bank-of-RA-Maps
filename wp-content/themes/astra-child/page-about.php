<?php
/**
 * About — /about/ (or /about-us/)
 *
 * WordPress uses this automatically for a Page whose slug is "about";
 * byrm_about_template() routes "about-us" here as well.
 *
 * The page reuses the guide section's banner, block and panel styles rather
 * than inventing a second set, so it inherits any later change to those. Only
 * the parts unique to this page — the figures strip, the three pillars, the
 * fact list and the contact rows — are styled in about.css.
 *
 * Nothing here is hardcoded that could go out of date: the figures are read
 * from the catalogue, and the name, year, host and contact details come from
 * byrm_about_config(), which is set in functions.php.
 *
 * @package Astra_Child
 */

defined( 'ABSPATH' ) || exit;

byrm_open_document();

while ( have_posts() ) :
	the_post();

	$about = byrm_about_config();
	$stats = byrm_about_stats();

	$maps_url   = post_type_exists( 'map' ) ? get_post_type_archive_link( 'map' ) : home_url( '/maps/' );
	$guides_url = home_url( '/guides/' );
	$editor_url = home_url( '/map-editor/' );

	// A contact row is only worth a line if there is something to put after it.
	$has_contact = ( '' !== $about['contact_email'] )
		|| ( '' !== $about['discord_url'] )
		|| ( '' !== $about['forum_url'] );
	?>

	<div id="byrm-content" class="byrm-guide byrm-about">

		<!-- --------------------------------------------------------- head -->
		<header class="byrm-gbanner">
			<div class="byrm-gbanner__veil" aria-hidden="true"></div>

			<div class="byrm-guide__shell byrm-gbanner__inner">
				<nav class="byrm-crumbs" aria-label="<?php esc_attr_e( 'Breadcrumb', 'astra-child' ); ?>">
					<a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Home', 'astra-child' ); ?></a>
					<span aria-hidden="true">/</span>
					<span aria-current="page"><?php the_title(); ?></span>
				</nav>

				<p class="byrm-gbanner__eyebrow"><?php esc_html_e( 'About us', 'astra-child' ); ?></p>
				<h1 class="byrm-gbanner__title"><?php the_title(); ?></h1>
				<p class="byrm-gbanner__lead">
					<?php esc_html_e( 'Bank of YR Maps publishes original multiplayer maps for Command & Conquer: Red Alert 2 — Yuri\'s Revenge. Every map here was built from an empty grid, played before it went up, and is free to download.', 'astra-child' ); ?>
				</p>
			</div>
		</header>

		<div class="byrm-guide__shell byrm-guide__body">

			<!-- ------------------------------------------------ the figures -->
			<?php if ( $stats ) : ?>
				<ul class="byrm-about-stats">
					<?php foreach ( $stats as $stat ) : ?>
						<li class="byrm-about-stat">
							<span class="byrm-about-stat__num"><?php echo esc_html( $stat['value'] ); ?></span>
							<span class="byrm-about-stat__label"><?php echo esc_html( $stat['label'] ); ?></span>
						</li>
					<?php endforeach; ?>
				</ul>
			<?php endif; ?>

			<!-- --------------------------------------------- the short version -->
			<section class="byrm-glance" aria-labelledby="byrm-about-short">
				<h2 id="byrm-about-short"><?php esc_html_e( 'The short version', 'astra-child' ); ?></h2>
				<p>
					<?php
					if ( '' !== $about['maintainer'] && '' !== $about['since'] ) {
						printf(
							/* translators: 1: name of the person running the site, 2: year, e.g. "2019". */
							esc_html__( 'This site is run by %1$s, who has been making Yuri\'s Revenge maps since %2$s. It exists because good custom maps were scattered across dead forum threads and dead links, and they deserved somewhere that stays up.', 'astra-child' ),
							esc_html( $about['maintainer'] ),
							esc_html( $about['since'] )
						);
					} elseif ( '' !== $about['maintainer'] ) {
						printf(
							/* translators: %s: name of the person running the site. */
							esc_html__( 'This site is run by %s. It exists because good custom maps were scattered across dead forum threads and dead links, and they deserved somewhere that stays up.', 'astra-child' ),
							esc_html( $about['maintainer'] )
						);
					} else {
						esc_html_e( 'This site is run by a mapper, not a company. It exists because good custom maps were scattered across dead forum threads and dead links, and they deserved somewhere that stays up.', 'astra-child' );
					}
					?>
				</p>
				<p>
					<?php esc_html_e( 'No account to make, no email to hand over, no download timer, no ads between you and the file. Pick a map, read what it plays like, take it.', 'astra-child' ); ?>
				</p>
			</section>

			<!-- ------------------------------------------------ what we do -->
			<section class="byrm-mblock" aria-labelledby="byrm-about-what">
				<h2 id="byrm-about-what"><?php esc_html_e( 'What we actually do', 'astra-child' ); ?></h2>

				<ol class="byrm-about-pillars">
					<li class="byrm-about-pillar">
						<span class="byrm-about-pillar__num" aria-hidden="true">01</span>
						<div class="byrm-about-pillar__body">
							<h3 class="byrm-about-pillar__title"><?php esc_html_e( 'Build maps', 'astra-child' ); ?></h3>
							<p class="byrm-about-pillar__text">
								<?php esc_html_e( 'Each map is laid out by hand in the map editor — terrain, ore, cliffs, bridges, tech buildings and start positions all placed deliberately. None of it is generated, and none of it is somebody else\'s map with the name changed.', 'astra-child' ); ?>
							</p>
						</div>
					</li>

					<li class="byrm-about-pillar">
						<span class="byrm-about-pillar__num" aria-hidden="true">02</span>
						<div class="byrm-about-pillar__body">
							<h3 class="byrm-about-pillar__title"><?php esc_html_e( 'Play them first', 'astra-child' ); ?></h3>
							<p class="byrm-about-pillar__text">
								<?php esc_html_e( 'A map goes up once it has been played through: start positions checked for a fair share of ore, choke points checked for whether they are interesting or just annoying, and the whole thing checked for the small faults that ruin a match — unreachable ore, a player who cannot leave their own base, a bridge that drops you in the water.', 'astra-child' ); ?>
							</p>
						</div>
					</li>

					<li class="byrm-about-pillar">
						<span class="byrm-about-pillar__num" aria-hidden="true">03</span>
						<div class="byrm-about-pillar__body">
							<h3 class="byrm-about-pillar__title"><?php esc_html_e( 'Say what they are', 'astra-child' ); ?></h3>
							<p class="byrm-about-pillar__text">
								<?php esc_html_e( 'Every map page gives you the player count, the theater, the file size and type, and screenshots of the real thing rather than a render. You should be able to tell whether a map suits your group before you download it, not after.', 'astra-child' ); ?>
							</p>
						</div>
					</li>
				</ol>
			</section>

			<!-- --------------------------------------------- good to know -->
			<section class="byrm-mblock" aria-labelledby="byrm-about-facts">
				<h2 id="byrm-about-facts"><?php esc_html_e( 'Good to know', 'astra-child' ); ?></h2>

				<dl class="byrm-about-facts">
					<div class="byrm-about-fact">
						<dt class="byrm-about-fact__term"><?php esc_html_e( 'The game', 'astra-child' ); ?></dt>
						<dd class="byrm-about-fact__desc">
							<?php esc_html_e( 'Maps here are for Red Alert 2 with the Yuri\'s Revenge expansion installed. Most will not load in plain Red Alert 2, because they use the expansion\'s own map format.', 'astra-child' ); ?>
						</dd>
					</div>

					<div class="byrm-about-fact">
						<dt class="byrm-about-fact__term"><?php esc_html_e( 'The price', 'astra-child' ); ?></dt>
						<dd class="byrm-about-fact__desc">
							<?php esc_html_e( 'Nothing. Download as many as you like, play them with whoever you like, and host them on your own server if you run one. A credit back to this site is appreciated, never demanded.', 'astra-child' ); ?>
						</dd>
					</div>

					<div class="byrm-about-fact">
						<dt class="byrm-about-fact__term"><?php esc_html_e( 'The downloads', 'astra-child' ); ?></dt>
						<dd class="byrm-about-fact__desc">
							<?php
							if ( '' !== $about['file_host'] ) {
								printf(
									/* translators: %s: name of the file host, e.g. "Datadock". */
									esc_html__( 'Map files are stored on %s rather than on this server, so a busy day never takes the downloads down with the site. Every link is listed with its file type and size before you click it.', 'astra-child' ),
									esc_html( $about['file_host'] )
								);
							} else {
								esc_html_e( 'Map files are stored on a dedicated file host rather than on this server, so a busy day never takes the downloads down with the site. Every link is listed with its file type and size before you click it.', 'astra-child' );
							}
							?>
						</dd>
					</div>

					<div class="byrm-about-fact">
						<dt class="byrm-about-fact__term"><?php esc_html_e( 'The tools', 'astra-child' ); ?></dt>
						<dd class="byrm-about-fact__desc">
							<?php
							printf(
								/* translators: 1: opening link tag to the map editor, 2: closing link tag. */
								esc_html__( 'There is a %1$smap tweaker%2$s here too: upload a map, tick the changes you want — unlimited money, brutal AI, faster building — and it hands the edited map straight back. Nothing is kept on the server.', 'astra-child' ),
								'<a href="' . esc_url( $editor_url ) . '">',
								'</a>'
							);
							?>
						</dd>
					</div>

					<div class="byrm-about-fact">
						<dt class="byrm-about-fact__term"><?php esc_html_e( 'Your own maps', 'astra-child' ); ?></dt>
						<dd class="byrm-about-fact__desc">
							<?php esc_html_e( 'Built something and want it published here? Send it over. Maps that play well go up with your name on them — this site is not precious about who made what.', 'astra-child' ); ?>
						</dd>
					</div>

					<div class="byrm-about-fact">
						<dt class="byrm-about-fact__term"><?php esc_html_e( 'Something broken', 'astra-child' ); ?></dt>
						<dd class="byrm-about-fact__desc">
							<?php
							printf(
								/* translators: 1: opening link tag to the community page, 2: closing link tag. */
								esc_html__( 'A dead download link, a map that will not load, a start position that is plainly unfair — say so on the %1$sboard%2$s and it gets looked at. Naming the map helps.', 'astra-child' ),
								'<a href="' . esc_url( home_url( '/community' ) ) . '">',
								'</a>'
							);
							?>
						</dd>
					</div>
				</dl>
			</section>

			<?php
			// Anything typed into the page editor renders here, so this page can
			// be added to without editing the template.
			$extra = trim( get_the_content() );

			if ( '' !== $extra ) :
				?>
				<section class="byrm-mblock byrm-prose"><?php the_content(); ?></section>
			<?php endif; ?>

			<!-- ------------------------------------------------- reach us -->
			<?php if ( $has_contact ) : ?>
				<section class="byrm-mblock" aria-labelledby="byrm-about-reach">
					<h2 id="byrm-about-reach"><?php esc_html_e( 'Getting hold of us', 'astra-child' ); ?></h2>

					<ul class="byrm-about-contact">
						<?php if ( '' !== $about['contact_email'] ) : ?>
							<li class="byrm-about-contact__row">
								<span class="byrm-about-contact__label"><?php esc_html_e( 'Email', 'astra-child' ); ?></span>
								<a href="<?php echo esc_url( 'mailto:' . $about['contact_email'] ); ?>">
									<?php echo esc_html( $about['contact_email'] ); ?>
								</a>
							</li>
						<?php endif; ?>

						<?php if ( '' !== $about['discord_url'] ) : ?>
							<li class="byrm-about-contact__row">
								<span class="byrm-about-contact__label"><?php esc_html_e( 'Discord', 'astra-child' ); ?></span>
								<a href="<?php echo esc_url( $about['discord_url'] ); ?>" rel="noopener">
									<?php esc_html_e( 'Join the server', 'astra-child' ); ?>
								</a>
							</li>
						<?php endif; ?>

						<?php if ( '' !== $about['forum_url'] ) : ?>
							<li class="byrm-about-contact__row">
								<span class="byrm-about-contact__label"><?php esc_html_e( 'Forum', 'astra-child' ); ?></span>
								<a href="<?php echo esc_url( $about['forum_url'] ); ?>" rel="noopener nofollow">
									<?php esc_html_e( 'Command & Conquer community', 'astra-child' ); ?>
								</a>
							</li>
						<?php endif; ?>
					</ul>

					<p class="byrm-about-contact__note">
						<?php
						printf(
							/* translators: 1: opening link tag to the community page, 2: closing link tag. */
							esc_html__( 'Anything that other players would benefit from reading is better posted on the %1$scommunity board%2$s, where it stays searchable.', 'astra-child' ),
							'<a href="' . esc_url( home_url( '/community' ) ) . '">',
							'</a>'
						);
						?>
					</p>
				</section>
			<?php else : ?>
				<section class="byrm-note">
					<h2><?php esc_html_e( 'Getting hold of us', 'astra-child' ); ?></h2>
					<p>
						<?php
						printf(
							/* translators: 1: opening link tag to the community page, 2: closing link tag. */
							esc_html__( 'The %1$scommunity board%2$s is the place to ask about a map, report a fault in one, or suggest what to build next. Posts are read.', 'astra-child' ),
							'<a href="' . esc_url( home_url( '/community' ) ) . '">',
							'</a>'
						);
						?>
					</p>
				</section>
			<?php endif; ?>

			<!-- --------------------------------------------- what we are not -->
			<section class="byrm-note byrm-note--warn">
				<h2><?php esc_html_e( 'What this site is not', 'astra-child' ); ?></h2>
				<p>
					<?php esc_html_e( 'We publish maps. We do not sell the game, host copies of it, or hand out keys, and we cannot help if the game will not install or will not launch — that belongs on the Command & Conquer forums.', 'astra-child' ); ?>
				</p>
				<p>
					<?php esc_html_e( 'Command & Conquer, Red Alert and Yuri\'s Revenge are trademarks of Electronic Arts. This is an unofficial fan site with no connection to EA, and the maps here are fan-made work, not EA content.', 'astra-child' ); ?>
				</p>
			</section>

			<!-- ------------------------------------------------------ cta -->
			<div class="byrm-cta-panel">
				<h2><?php esc_html_e( 'That is us. Go and play something.', 'astra-child' ); ?></h2>
				<p><?php esc_html_e( 'Pick a map, or read how to get it into the game first.', 'astra-child' ); ?></p>

				<div class="byrm-about-actions">
					<a class="byrm-btn byrm-btn--primary" href="<?php echo esc_url( $maps_url ); ?>">
						<?php esc_html_e( 'Browse the maps', 'astra-child' ); ?>
					</a>
					<a class="byrm-btn byrm-btn--ghost" href="<?php echo esc_url( $guides_url ); ?>">
						<?php esc_html_e( 'Read the guides', 'astra-child' ); ?>
					</a>
				</div>
			</div>
		</div>
	</div>

	<?php
endwhile;

byrm_close_document();
