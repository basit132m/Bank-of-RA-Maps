<?php
/**
 * Bank of YR Maps — custom site header.
 *
 * Rendered in place of Astra's header. Everything configurable lives in
 * byrm_header_config(), which is filterable, so no markup needs editing to
 * change the Discord link or the status line.
 *
 * @package Astra_Child
 */

defined( 'ABSPATH' ) || exit;

/**
 * Header configuration.
 *
 * Override from a plugin or elsewhere with:
 *   add_filter( 'byrm_header_config', function ( $config ) { ... return $config; } );
 *
 * @return array<string, string>
 */
function byrm_header_config() {
	$defaults = array(
		'status_text'    => "Original maps for Red Alert 2 — Yuri's Revenge",
		'discord_url'    => '',
		'install_url'    => '/guides/install',
		'cta_label'      => 'Browse maps',
		'cta_url'        => '/maps',
		'search_label'   => 'Search maps',
		'logo_alt'       => get_bloginfo( 'name', 'display' ),
	);

	return apply_filters( 'byrm_header_config', $defaults );
}

/**
 * The logo: the Customizer logo when one is set, otherwise the bundled artwork.
 */
function byrm_header_logo() {
	$config = byrm_header_config();

	// the_custom_logo() renders its OWN <a>, so it must not be wrapped in another
	// one: nested anchors are invalid HTML, and browsers split them apart. That
	// leaves an empty .byrm-logo behind whose margin-right:auto shoves the logo
	// and the whole nav to the right, and puts the image outside our sizing CSS.
	if ( function_exists( 'has_custom_logo' ) && has_custom_logo() ) {
		echo '<div class="byrm-logo">';
		the_custom_logo();
		echo '</div>';

		return;
	}

	printf(
		'<a class="byrm-logo" href="%1$s" rel="home">'
		. '<img src="%2$s" alt="%3$s" width="2000" height="668" fetchpriority="high" decoding="async">'
		. '</a>',
		esc_url( home_url( '/' ) ),
		esc_url( get_stylesheet_directory_uri() . '/assets/img/logo.webp' ),
		esc_attr( $config['logo_alt'] )
	);
}

/**
 * Primary navigation, with a sensible fallback before a menu is assigned.
 */
function byrm_header_nav() {
	if ( has_nav_menu( 'primary' ) ) {
		wp_nav_menu(
			array(
				'theme_location' => 'primary',
				'container'      => false,
				'menu_class'     => 'byrm-nav__list',
				'depth'          => 2,
				'fallback_cb'    => false,
			)
		);

		return;
	}

	// No menu assigned yet — show a placeholder so the header is never empty.
	$fallback = array(
		'/maps'      => 'Maps',
		'/guides'    => 'Guides',
		'/community' => 'Community',
		'/about'     => 'About',
	);

	echo '<ul class="byrm-nav__list">';
	foreach ( $fallback as $path => $label ) {
		printf(
			'<li><a href="%1$s">%2$s</a></li>',
			esc_url( home_url( $path ) ),
			esc_html( $label )
		);
	}
	echo '</ul>';
}

/**
 * Output the whole header.
 */
function byrm_render_site_header() {
	// The shell calls this directly, and it may also be hooked into Astra or
	// wp_body_open. Whichever fires first wins; the rest are no-ops, so two
	// headers can never stack.
	static $rendered = false;

	if ( $rendered ) {
		return;
	}

	$rendered = true;

	$config = byrm_header_config();

	// Our own templates use #byrm-content; every other page keeps Astra's #content.
	// byrm_is_full_width_template() is the exact list of templates that draw
	// their own document, so the two can never drift apart as pages are added.
	$ours = function_exists( 'byrm_is_full_width_template' ) && byrm_is_full_width_template();

	$skip_target = $ours ? '#byrm-content' : '#content';
	?>
	<a class="byrm-skip" href="<?php echo esc_attr( $skip_target ); ?>"><?php esc_html_e( 'Skip to content', 'astra-child' ); ?></a>

	<header class="byrm-header" id="byrm-header" data-byrm-header>

		<div class="byrm-strip">
			<div class="byrm-header__shell byrm-strip__inner">
				<p class="byrm-strip__status">
					<span class="byrm-strip__beacon" aria-hidden="true"></span>
					<?php echo esc_html( $config['status_text'] ); ?>
				</p>

				<div class="byrm-strip__links">
					<?php if ( ! empty( $config['install_url'] ) ) : ?>
						<a href="<?php echo esc_url( home_url( $config['install_url'] ) ); ?>">
							<?php esc_html_e( 'Install guide', 'astra-child' ); ?>
						</a>
					<?php endif; ?>

					<?php if ( ! empty( $config['discord_url'] ) ) : ?>
						<a class="byrm-strip__discord" href="<?php echo esc_url( $config['discord_url'] ); ?>"
						   target="_blank" rel="noopener noreferrer">
							<svg viewBox="0 0 24 24" aria-hidden="true" focusable="false">
								<path d="M20.3 4.4A19.8 19.8 0 0 0 15.4 3l-.3.5c1.6.4 2.9 1 4.1 1.8a13.7 13.7 0 0 0-11.9-.5c-.4.2-.7.3-.9.5a15 15 0 0 1 4.2-1.8L10.3 3c-1.8.2-3.5.7-5 1.4C2.6 8.5 1.9 12.4 2.2 16.3a20 20 0 0 0 6 3l1.3-1.8c-1-.4-2-.9-2.8-1.5l.7-.5a14.2 14.2 0 0 0 12.2 0l.7.5c-.9.6-1.8 1.1-2.8 1.5l1.3 1.8a20 20 0 0 0 6-3c.4-4.5-.6-8.4-3.5-11.9ZM8.9 14.3c-1.2 0-2.1-1.1-2.1-2.4 0-1.3.9-2.4 2.1-2.4s2.2 1.1 2.2 2.4c0 1.3-1 2.4-2.2 2.4Zm6.2 0c-1.2 0-2.1-1.1-2.1-2.4 0-1.3.9-2.4 2.1-2.4s2.2 1.1 2.2 2.4c0 1.3-1 2.4-2.2 2.4Z"/>
							</svg>
							<?php esc_html_e( 'Discord', 'astra-child' ); ?>
						</a>
					<?php endif; ?>
				</div>
			</div>
		</div>

		<div class="byrm-bar">
			<div class="byrm-header__shell byrm-bar__inner">

				<?php byrm_header_logo(); ?>

				<nav class="byrm-nav" id="byrm-nav" aria-label="<?php esc_attr_e( 'Primary', 'astra-child' ); ?>">
					<?php byrm_header_nav(); ?>

					<div class="byrm-nav__cta">
						<a class="byrm-cta" href="<?php echo esc_url( home_url( $config['cta_url'] ) ); ?>">
							<?php echo esc_html( $config['cta_label'] ); ?>
						</a>
					</div>
				</nav>

				<div class="byrm-actions">
					<button class="byrm-iconbtn byrm-search-toggle" type="button"
					        aria-expanded="false" aria-controls="byrm-search">
						<span class="byrm-sr"><?php echo esc_html( $config['search_label'] ); ?></span>
						<svg viewBox="0 0 24 24" aria-hidden="true" focusable="false">
							<circle cx="11" cy="11" r="7" stroke-linecap="round"/>
							<path d="m20 20-3.5-3.5" stroke-linecap="round"/>
						</svg>
					</button>

					<a class="byrm-cta byrm-cta--bar" href="<?php echo esc_url( home_url( $config['cta_url'] ) ); ?>">
						<?php echo esc_html( $config['cta_label'] ); ?>
					</a>

					<button class="byrm-iconbtn byrm-burger" type="button"
					        aria-expanded="false" aria-controls="byrm-nav">
						<span class="byrm-sr"><?php esc_html_e( 'Menu', 'astra-child' ); ?></span>
						<span class="byrm-burger__box" aria-hidden="true">
							<span></span><span></span><span></span>
						</span>
					</button>
				</div>
			</div>
		</div>

		<div class="byrm-search" id="byrm-search">
			<div class="byrm-search__clip">
				<form class="byrm-header__shell byrm-search__form" role="search" method="get"
				      action="<?php echo esc_url( home_url( '/' ) ); ?>">
					<label class="byrm-sr" for="byrm-search-field">
						<?php echo esc_html( $config['search_label'] ); ?>
					</label>
					<input type="search" id="byrm-search-field" name="s"
					       value="<?php echo esc_attr( get_search_query() ); ?>"
					       placeholder="<?php esc_attr_e( 'Map name, player count, theater…', 'astra-child' ); ?>">
					<button class="byrm-cta" type="submit"><?php esc_html_e( 'Search', 'astra-child' ); ?></button>
				</form>
			</div>
		</div>
	</header>

	<div class="byrm-backdrop" data-byrm-backdrop hidden></div>
	<?php
}
