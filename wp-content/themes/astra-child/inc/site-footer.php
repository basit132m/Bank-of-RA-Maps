<?php
/**
 * Bank of YR Maps — custom site footer.
 *
 * Rendered in place of Astra's footer. Link columns come from WordPress menus
 * when they are assigned, and fall back to sensible defaults until then.
 *
 * @package Astra_Child
 */

defined( 'ABSPATH' ) || exit;

/**
 * Footer configuration.
 *
 * Override with:
 *   add_filter( 'byrm_footer_config', function ( $config ) { ... return $config; } );
 *
 * @return array<string, mixed>
 */
function byrm_footer_config() {
	$defaults = array(
		'tagline'       => 'Original multiplayer maps for Command & Conquer: Red Alert 2 — '
			. "Yuri's Revenge. Designed by hand, tested in real games, free to download.",
		'discord_url'   => '',
		'contact_email' => '',

		'cta_title'     => 'Ready to deploy?',
		'cta_text'      => 'Pick a map, drop it in your game folder, and go.',
		'cta_label'     => 'Browse the maps',
		'cta_url'       => '/maps',

		// Each column falls back to these links until a menu is assigned to the
		// matching location (footer-maps, footer-guides, footer-site).
		'columns'       => array(
			'maps'   => array(
				'title' => 'Maps',
				'menu'  => 'footer-maps',
				'links' => array(
					'/maps'                 => 'Browse all',
					'/maps?players=2'       => '1v1 maps',
					'/maps?players=4'       => '2v2 maps',
					'/maps?sort=popular'    => 'Most downloaded',
				),
			),
			'guides' => array(
				'title' => 'Guides',
				'menu'  => 'footer-guides',
				'links' => array(
					'/guides/install' => 'Installing maps',
					'/guides'         => 'All guides',
				),
			),
			'site'   => array(
				'title' => 'Site',
				'menu'  => 'footer-site',
				'links' => array(
					'/about'     => 'About',
					'/community' => 'Community',
				),
			),
		),
	);

	return apply_filters( 'byrm_footer_config', $defaults );
}

/**
 * Catalogue counters for the footer readout.
 *
 * Returns an empty array until a `map` post type exists with published entries,
 * so the readout stays hidden rather than showing invented numbers.
 *
 * @return array<int, array{label: string, value: string}>
 */
function byrm_footer_stats() {
	if ( ! post_type_exists( 'map' ) ) {
		return apply_filters( 'byrm_footer_stats', array() );
	}

	$counts = wp_count_posts( 'map' );
	$total  = isset( $counts->publish ) ? (int) $counts->publish : 0;

	if ( $total < 1 ) {
		return apply_filters( 'byrm_footer_stats', array() );
	}

	$stats = array(
		array(
			'label' => 'Maps published',
			'value' => number_format_i18n( $total ),
		),
	);

	return apply_filters( 'byrm_footer_stats', $stats );
}

/**
 * One column of footer links.
 *
 * @param array{title: string, menu: string, links: array<string, string>} $column Column definition.
 */
function byrm_footer_column( array $column ) {
	printf(
		'<div class="byrm-fcol"><h2 class="byrm-fcol__title">%s</h2>',
		esc_html( $column['title'] )
	);

	if ( has_nav_menu( $column['menu'] ) ) {
		wp_nav_menu(
			array(
				'theme_location' => $column['menu'],
				'container'      => false,
				'menu_class'     => 'byrm-fcol__list',
				'depth'          => 1,
				'fallback_cb'    => false,
			)
		);
	} else {
		echo '<ul class="byrm-fcol__list">';
		foreach ( $column['links'] as $path => $label ) {
			printf(
				'<li><a href="%1$s"><span>%2$s</span></a></li>',
				esc_url( home_url( $path ) ),
				esc_html( $label )
			);
		}
		echo '</ul>';
	}

	echo '</div>';
}

/**
 * Output the whole footer.
 */
function byrm_render_site_footer() {
	// Same guard as the header: called directly by the shell and possibly also
	// hooked, so only the first call renders.
	static $rendered = false;

	if ( $rendered ) {
		return;
	}

	$rendered = true;

	$config = byrm_footer_config();
	$stats  = byrm_footer_stats();
	$logo   = get_stylesheet_directory_uri() . '/assets/img/logo.webp';
	?>
	<footer class="byrm-footer" id="byrm-footer">

		<section class="byrm-footer__cta">
			<div class="byrm-footer__shell byrm-fcta">
				<div class="byrm-fcta__copy">
					<h2><?php echo esc_html( $config['cta_title'] ); ?></h2>
					<p><?php echo esc_html( $config['cta_text'] ); ?></p>
				</div>

				<div class="byrm-fcta__actions">
					<a class="byrm-cta" href="<?php echo esc_url( home_url( $config['cta_url'] ) ); ?>">
						<?php echo esc_html( $config['cta_label'] ); ?>
					</a>

					<?php if ( ! empty( $config['discord_url'] ) ) : ?>
						<a class="byrm-fbtn byrm-fbtn--discord" href="<?php echo esc_url( $config['discord_url'] ); ?>"
						   target="_blank" rel="noopener noreferrer">
							<svg viewBox="0 0 24 24" aria-hidden="true" focusable="false">
								<path d="M20.3 4.4A19.8 19.8 0 0 0 15.4 3l-.3.5c1.6.4 2.9 1 4.1 1.8a13.7 13.7 0 0 0-11.9-.5c-.4.2-.7.3-.9.5a15 15 0 0 1 4.2-1.8L10.3 3c-1.8.2-3.5.7-5 1.4C2.6 8.5 1.9 12.4 2.2 16.3a20 20 0 0 0 6 3l1.3-1.8c-1-.4-2-.9-2.8-1.5l.7-.5a14.2 14.2 0 0 0 12.2 0l.7.5c-.9.6-1.8 1.1-2.8 1.5l1.3 1.8a20 20 0 0 0 6-3c.4-4.5-.6-8.4-3.5-11.9ZM8.9 14.3c-1.2 0-2.1-1.1-2.1-2.4 0-1.3.9-2.4 2.1-2.4s2.2 1.1 2.2 2.4c0 1.3-1 2.4-2.2 2.4Zm6.2 0c-1.2 0-2.1-1.1-2.1-2.4 0-1.3.9-2.4 2.1-2.4s2.2 1.1 2.2 2.4c0 1.3-1 2.4-2.2 2.4Z"/>
							</svg>
							<?php esc_html_e( 'Join the Discord', 'astra-child' ); ?>
						</a>
					<?php endif; ?>
				</div>
			</div>
		</section>

		<div class="byrm-footer__main">
			<div class="byrm-footer__shell byrm-footer__grid">

				<div class="byrm-fbrand">
					<?php
					// As in the header: the_custom_logo() brings its own <a>, so it is
					// never wrapped in a second one.
					if ( function_exists( 'has_custom_logo' ) && has_custom_logo() ) :
						?>
						<div class="byrm-fbrand__logo"><?php the_custom_logo(); ?></div>
					<?php else : ?>
						<a class="byrm-fbrand__logo" href="<?php echo esc_url( home_url( '/' ) ); ?>" rel="home">
							<img src="<?php echo esc_url( $logo ); ?>"
							     alt="<?php echo esc_attr( get_bloginfo( 'name', 'display' ) ); ?>"
							     width="2000" height="668" loading="lazy" decoding="async">
						</a>
					<?php endif; ?>

					<p class="byrm-fbrand__text"><?php echo esc_html( $config['tagline'] ); ?></p>

					<?php if ( ! empty( $config['contact_email'] ) ) : ?>
						<p class="byrm-fbrand__contact">
							<a href="mailto:<?php echo esc_attr( $config['contact_email'] ); ?>">
								<?php echo esc_html( $config['contact_email'] ); ?>
							</a>
						</p>
					<?php endif; ?>
				</div>

				<nav class="byrm-footer__cols" aria-label="<?php esc_attr_e( 'Footer', 'astra-child' ); ?>">
					<?php foreach ( $config['columns'] as $column ) : ?>
						<?php byrm_footer_column( $column ); ?>
					<?php endforeach; ?>
				</nav>
			</div>

			<?php if ( ! empty( $stats ) ) : ?>
				<div class="byrm-footer__shell">
					<dl class="byrm-fstats">
						<?php foreach ( $stats as $stat ) : ?>
							<div class="byrm-fstats__item">
								<dt><?php echo esc_html( $stat['label'] ); ?></dt>
								<dd><?php echo esc_html( $stat['value'] ); ?></dd>
							</div>
						<?php endforeach; ?>
					</dl>
				</div>
			<?php endif; ?>
		</div>

		<div class="byrm-footer__base">
			<div class="byrm-footer__shell byrm-fbase">
				<div class="byrm-fbase__legal">
					<p>
						&copy; <?php echo esc_html( gmdate( 'Y' ) ); ?>
						<?php echo esc_html( get_bloginfo( 'name', 'display' ) ); ?>.
						<?php esc_html_e( 'Maps are the work of their credited designers.', 'astra-child' ); ?>
					</p>
					<p class="byrm-fbase__disclaimer">
						<?php esc_html_e( 'Command & Conquer and Red Alert are trademarks of Electronic Arts Inc. This is an unofficial fan site with no affiliation to EA.', 'astra-child' ); ?>
					</p>
				</div>

				<button class="byrm-totop" type="button" data-byrm-totop>
					<span><?php esc_html_e( 'Back to top', 'astra-child' ); ?></span>
					<svg viewBox="0 0 24 24" aria-hidden="true" focusable="false">
						<path d="M12 19V5M5 12l7-7 7 7" stroke-linecap="round" stroke-linejoin="round"/>
					</svg>
				</button>
			</div>
		</div>
	</footer>
	<?php
}
