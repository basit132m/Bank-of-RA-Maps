<?php
/**
 * Install guide — /guides/install.
 *
 * WordPress uses page-install.php automatically for a Page whose slug is
 * "install", so no template needs assigning in the admin.
 *
 * The instructions here are the site owner's own, not generic advice: maps are
 * renamed to .map, placed in RA2 > Maps > Custom, and picked from STANDARD in
 * CnCNet. Anything typed into the page editor is rendered underneath, so notes
 * can be added without touching this file.
 *
 * @package Astra_Child
 */

defined( 'ABSPATH' ) || exit;

byrm_open_document();

while ( have_posts() ) :
	the_post();

	// Link the breadcrumb to the parent page when there is one, so it never
	// points at a URL that does not exist.
	$parent_id  = wp_get_post_parent_id( get_the_ID() );
	$parent_url = $parent_id ? get_permalink( $parent_id ) : '';
	$maps_url   = post_type_exists( 'map' ) ? get_post_type_archive_link( 'map' ) : home_url( '/maps/' );
	?>

	<div id="byrm-content" class="byrm-guide">

		<!-- --------------------------------------------------------- head -->
		<header class="byrm-gbanner">
			<div class="byrm-gbanner__veil" aria-hidden="true"></div>

			<div class="byrm-guide__shell byrm-gbanner__inner">
				<nav class="byrm-crumbs" aria-label="<?php esc_attr_e( 'Breadcrumb', 'astra-child' ); ?>">
					<a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Home', 'astra-child' ); ?></a>
					<span aria-hidden="true">/</span>
					<?php if ( $parent_url ) : ?>
						<a href="<?php echo esc_url( $parent_url ); ?>"><?php echo esc_html( get_the_title( $parent_id ) ); ?></a>
						<span aria-hidden="true">/</span>
					<?php endif; ?>
					<span aria-current="page"><?php the_title(); ?></span>
				</nav>

				<p class="byrm-gbanner__eyebrow"><?php esc_html_e( 'Guide', 'astra-child' ); ?></p>
				<h1 class="byrm-gbanner__title"><?php the_title(); ?></h1>
				<p class="byrm-gbanner__lead">
					<?php esc_html_e( 'Every map here can be played on CnCNet. Four steps, about a minute, and you are in the game.', 'astra-child' ); ?>
				</p>
			</div>
		</header>

		<div class="byrm-guide__shell byrm-guide__body">

			<!-- ------------------------------------------------ at a glance -->
			<aside class="byrm-glance" aria-label="<?php esc_attr_e( 'Summary', 'astra-child' ); ?>">
				<h2><?php esc_html_e( 'The short version', 'astra-child' ); ?></h2>
				<ol>
					<li><?php esc_html_e( 'Download the version that matches your game.', 'astra-child' ); ?></li>
					<li><?php esc_html_e( 'Rename the file so it ends in .map', 'astra-child' ); ?></li>
					<li><?php esc_html_e( 'Put it in RA2 → Maps → Custom', 'astra-child' ); ?></li>
					<li><?php esc_html_e( 'In CnCNet, look under STANDARD to pick it.', 'astra-child' ); ?></li>
				</ol>
			</aside>

			<!-- ------------------------------------------------------ step 1 -->
			<section class="byrm-step">
				<div class="byrm-step__num" aria-hidden="true">1</div>
				<div class="byrm-step__body">
					<h2><?php esc_html_e( 'Download the right version', 'astra-child' ); ?></h2>
					<p>
						<?php esc_html_e( 'Maps are published in two versions. Take the one that matches the game you actually play — they are not interchangeable.', 'astra-child' ); ?>
					</p>

					<div class="byrm-versions">
						<div class="byrm-version">
							<span class="byrm-version__tag"><?php esc_html_e( "Yuri's Revenge", 'astra-child' ); ?></span>
							<p class="byrm-version__file"><code>.yrm</code></p>
							<p><?php esc_html_e( 'The Yuri\'s Revenge version of the map.', 'astra-child' ); ?></p>
						</div>

						<div class="byrm-version">
							<span class="byrm-version__tag"><?php esc_html_e( 'Red Alert 2', 'astra-child' ); ?></span>
							<p class="byrm-version__file"><?php esc_html_e( 'RA2 version', 'astra-child' ); ?></p>
							<p><?php esc_html_e( 'The Red Alert 2 version of the same map.', 'astra-child' ); ?></p>
						</div>
					</div>
				</div>
			</section>

			<!-- ------------------------------------------------------ step 2 -->
			<section class="byrm-step">
				<div class="byrm-step__num" aria-hidden="true">2</div>
				<div class="byrm-step__body">
					<h2><?php esc_html_e( 'Rename it to .map', 'astra-child' ); ?></h2>
					<p>
						<?php esc_html_e( 'Change the file extension so the name ends in .map — that is the extension CnCNet reads.', 'astra-child' ); ?>
					</p>
					<p class="byrm-rename">
						<span class="byrm-rename__from"><code>mapname.yrm</code></span>
						<span class="byrm-rename__arrow" aria-hidden="true">&rarr;</span>
						<span class="byrm-rename__to"><code>mapname.map</code></span>
					</p>
					<p class="byrm-hint">
						<?php esc_html_e( 'On Windows, if you cannot see the extension, turn on "File name extensions" in File Explorer\'s View menu first — otherwise you will end up with mapname.map.yrm by mistake.', 'astra-child' ); ?>
					</p>
				</div>
			</section>

			<!-- ------------------------------------------------------ step 3 -->
			<section class="byrm-step">
				<div class="byrm-step__num" aria-hidden="true">3</div>
				<div class="byrm-step__body">
					<h2><?php esc_html_e( 'Put it in the Custom folder', 'astra-child' ); ?></h2>
					<p><?php esc_html_e( 'Move the renamed file here:', 'astra-child' ); ?></p>
					<p class="byrm-path"><code>RA2 &rsaquo; Maps &rsaquo; Custom</code></p>
					<p>
						<?php esc_html_e( 'That is the folder CnCNet looks in for custom maps. Nothing else needs changing.', 'astra-child' ); ?>
					</p>
				</div>
			</section>

			<!-- ------------------------------------------------------ step 4 -->
			<section class="byrm-step">
				<div class="byrm-step__num" aria-hidden="true">4</div>
				<div class="byrm-step__body">
					<h2><?php esc_html_e( 'Find it under STANDARD', 'astra-child' ); ?></h2>
					<p>
						<?php esc_html_e( 'Open CnCNet and look under STANDARD in the map list. Your map will be there, ready to select.', 'astra-child' ); ?>
					</p>
					<p class="byrm-hint">
						<?php esc_html_e( 'If the game was already open while you copied the file, close it and open it again — the map list is read at launch.', 'astra-child' ); ?>
					</p>
				</div>
			</section>

			<!-- ------------------------------------ RA2 with YR maps callout -->
			<section class="byrm-note byrm-note--info">
				<h2><?php esc_html_e( 'Playing Red Alert 2 with maps made for Yuri\'s Revenge', 'astra-child' ); ?></h2>
				<p>
					<?php esc_html_e( 'CnCNet has a Red Alert 2 option, including a BRUTAL AI setting that is considerably harder than the standard one.', 'astra-child' ); ?>
				</p>
				<p>
					<?php esc_html_e( 'You can play offline in skirmish mode exactly as you did before — but you need CnCNet installed to do it.', 'astra-child' ); ?>
				</p>
			</section>

			<!-- --------------------------------------- unknown author note -->
			<section class="byrm-note">
				<h2><?php esc_html_e( 'Why some maps say "unknown author"', 'astra-child' ); ?></h2>
				<p>
					<?php esc_html_e( 'When a map is captioned "unknown author", it means it was found on the internet without any author information attached. It is unknown to us — it may well be known to someone else.', 'astra-child' ); ?>
				</p>
				<p>
					<?php esc_html_e( 'If you know who made one of these maps, tell us and we will credit them properly.', 'astra-child' ); ?>
				</p>
			</section>

			<!-- ------------------------------------------ not the forum -->
			<?php // Deliberately blunt: it saves everyone time. ?>
			<section class="byrm-note byrm-note--warn">
				<h2><?php esc_html_e( 'Problems with the game itself?', 'astra-child' ); ?></h2>
				<p>
					<?php esc_html_e( 'If the game will not install, will not run, or is broken in some other way, this is not the right place to ask. This site publishes maps — it is not a support channel for the game.', 'astra-child' ); ?>
				</p>
				<p>
					<?php esc_html_e( 'Head for the Command & Conquer forums instead. That is where the answers are, and where people can actually help.', 'astra-child' ); ?>
				</p>
			</section>

			<!-- ------------------------------------------ troubleshooting -->
			<section class="byrm-mblock">
				<h2><?php esc_html_e( 'The map is not showing up', 'astra-child' ); ?></h2>
				<ul class="byrm-checks">
					<li>
						<strong><?php esc_html_e( 'Still not renamed.', 'astra-child' ); ?></strong>
						<?php esc_html_e( 'The file has to end in .map, not .yrm', 'astra-child' ); ?>
					</li>
					<li>
						<strong><?php esc_html_e( 'Renamed twice.', 'astra-child' ); ?></strong>
						<?php esc_html_e( 'With hidden extensions on, you may have created mapname.map.yrm — check the real file name.', 'astra-child' ); ?>
					</li>
					<li>
						<strong><?php esc_html_e( 'Wrong folder.', 'astra-child' ); ?></strong>
						<?php esc_html_e( 'It belongs in RA2 › Maps › Custom, not loose in the game folder.', 'astra-child' ); ?>
					</li>
					<li>
						<strong><?php esc_html_e( 'Looking in the wrong list.', 'astra-child' ); ?></strong>
						<?php esc_html_e( 'Custom maps appear under STANDARD, not alongside the maps that shipped with the game.', 'astra-child' ); ?>
					</li>
					<li>
						<strong><?php esc_html_e( 'Game was already running.', 'astra-child' ); ?></strong>
						<?php esc_html_e( 'Restart it so the map list is read again.', 'astra-child' ); ?>
					</li>
				</ul>
			</section>

			<?php
			// Anything typed into the page editor appears here, so notes can be
			// added or amended without editing this template.
			$extra = trim( get_the_content() );

			if ( '' !== $extra ) :
				?>
				<section class="byrm-mblock byrm-prose"><?php the_content(); ?></section>
			<?php endif; ?>

			<div class="byrm-cta-panel">
				<h2><?php esc_html_e( 'Ready to play?', 'astra-child' ); ?></h2>
				<p><?php esc_html_e( 'Pick a map and go.', 'astra-child' ); ?></p>
				<a class="byrm-btn byrm-btn--primary" href="<?php echo esc_url( $maps_url ); ?>">
					<?php esc_html_e( 'Browse the maps', 'astra-child' ); ?>
				</a>
			</div>
		</div>
	</div>

	<?php
endwhile;

byrm_close_document();
