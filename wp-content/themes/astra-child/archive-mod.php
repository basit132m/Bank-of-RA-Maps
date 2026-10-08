<?php
/**
 * Mods archive — the browse page at /mods/.
 *
 * Also used for the type and tag term archives, routed here from
 * inc/mod-helpers.php so they look the same everywhere.
 *
 * Simpler than the maps archive on purpose: there will be far fewer mods than
 * maps, so a filter rail down the side would be a lot of furniture around a
 * short list. A row of type chips does the same job until the catalogue is big
 * enough to need more.
 *
 * @package Astra_Child
 */

defined( 'ABSPATH' ) || exit;

byrm_open_document();

$totals   = byrm_mod_totals();
$types    = byrm_mod_types();
$base_url = get_post_type_archive_link( 'mod' ) ? get_post_type_archive_link( 'mod' ) : home_url( '/mods/' );
$found    = (int) $GLOBALS['wp_query']->found_posts;

// On a term archive the heading is the term, not the generic title.
$term        = is_tax() ? get_queried_object() : null;
$is_term     = ( $term && ! is_wp_error( $term ) );
$heading     = $is_term ? $term->name : __( 'Mods', 'astra-child' );
$description = ( $is_term && $term->description )
	? $term->description
	: __( 'Rule changes, new units, graphics packs and total conversions for Red Alert 2 and Yuri\'s Revenge. A mod changes the game itself, so read the install notes before you drop one in.', 'astra-child' );
?>

<div id="byrm-content" class="byrm-archive byrm-modlist">

	<!-- ------------------------------------------------------------ head -->
	<header class="byrm-abanner">
		<div class="byrm-abanner__veil" aria-hidden="true"></div>

		<div class="byrm-archive__shell byrm-abanner__inner">
			<nav class="byrm-crumbs" aria-label="<?php esc_attr_e( 'Breadcrumb', 'astra-child' ); ?>">
				<a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Home', 'astra-child' ); ?></a>
				<span aria-hidden="true">/</span>
				<?php if ( $is_term ) : ?>
					<a href="<?php echo esc_url( $base_url ); ?>"><?php esc_html_e( 'Mods', 'astra-child' ); ?></a>
					<span aria-hidden="true">/</span>
					<span aria-current="page"><?php echo esc_html( $heading ); ?></span>
				<?php else : ?>
					<span aria-current="page"><?php esc_html_e( 'Mods', 'astra-child' ); ?></span>
				<?php endif; ?>
			</nav>

			<h1 class="byrm-abanner__title"><?php echo esc_html( $heading ); ?></h1>
			<p class="byrm-abanner__lead"><?php echo esc_html( wp_strip_all_tags( $description ) ); ?></p>

			<?php if ( $totals['mods'] > 0 ) : ?>
				<p class="byrm-abanner__stat">
					<?php
					printf(
						/* translators: 1: number of mods, 2: number of downloads */
						esc_html__( '%1$s published · %2$s downloads', 'astra-child' ),
						esc_html( number_format_i18n( $totals['mods'] ) ),
						esc_html( number_format_i18n( $totals['downloads'] ) )
					);
					?>
				</p>
			<?php endif; ?>
		</div>
	</header>

	<div class="byrm-archive__shell byrm-modlist__body">

		<!-- ----------------------------------------------------- the types -->
		<?php if ( $types ) : ?>
			<nav class="byrm-modtypes" aria-label="<?php esc_attr_e( 'Mod types', 'astra-child' ); ?>">
				<a
					class="byrm-chip<?php echo $is_term ? '' : ' byrm-chip--on'; ?>"
					href="<?php echo esc_url( $base_url ); ?>"
					<?php echo $is_term ? '' : 'aria-current="page"'; ?>>
					<?php esc_html_e( 'All', 'astra-child' ); ?>
				</a>

				<?php foreach ( $types as $type ) : ?>
					<?php $on = ( $is_term && (int) $term->term_id === (int) $type->term_id ); ?>
					<a
						class="byrm-chip<?php echo $on ? ' byrm-chip--on' : ''; ?>"
						href="<?php echo esc_url( get_term_link( $type ) ); ?>"
						<?php echo $on ? 'aria-current="page"' : ''; ?>>
						<?php echo esc_html( $type->name ); ?>
						<span class="byrm-chip__n"><?php echo esc_html( number_format_i18n( $type->count ) ); ?></span>
					</a>
				<?php endforeach; ?>
			</nav>
		<?php endif; ?>

		<main class="byrm-results">
			<div class="byrm-results__bar">
				<p class="byrm-results__count" aria-live="polite">
					<?php if ( $found > 0 ) : ?>
						<strong><?php echo esc_html( number_format_i18n( $found ) ); ?></strong>
						<?php echo esc_html( _n( 'mod', 'mods', $found, 'astra-child' ) ); ?>
					<?php else : ?>
						<?php esc_html_e( 'Nothing here yet', 'astra-child' ); ?>
					<?php endif; ?>
				</p>
			</div>

			<?php if ( have_posts() ) : ?>
				<div class="byrm-grid">
					<?php
					while ( have_posts() ) :
						the_post();
						byrm_mod_card( get_post() );
					endwhile;
					?>
				</div>

				<?php
				$pagination = paginate_links(
					array(
						'type'      => 'array',
						'mid_size'  => 2,
						'prev_text' => __( 'Previous', 'astra-child' ),
						'next_text' => __( 'Next', 'astra-child' ),
					)
				);

				if ( $pagination ) :
					?>
					<nav class="byrm-pagination" aria-label="<?php esc_attr_e( 'Pagination', 'astra-child' ); ?>">
						<?php foreach ( $pagination as $link ) : ?>
							<?php echo wp_kses_post( $link ); ?>
						<?php endforeach; ?>
					</nav>
				<?php endif; ?>

			<?php elseif ( $totals['mods'] > 0 ) : ?>
				<div class="byrm-empty">
					<span class="byrm-empty__mark" aria-hidden="true">
						<svg viewBox="0 0 48 48" focusable="false">
							<circle cx="21" cy="21" r="13"/><path d="m40 40-8.5-8.5"/>
						</svg>
					</span>
					<h2><?php esc_html_e( 'Nothing filed under that', 'astra-child' ); ?></h2>
					<p><?php esc_html_e( 'No mods of that kind yet. The full list is a click away.', 'astra-child' ); ?></p>
					<a class="byrm-btn byrm-btn--primary" href="<?php echo esc_url( $base_url ); ?>">
						<?php esc_html_e( 'All mods', 'astra-child' ); ?>
					</a>
				</div>

			<?php else : ?>
				<div class="byrm-empty">
					<span class="byrm-empty__mark" aria-hidden="true">
						<svg viewBox="0 0 48 48" focusable="false">
							<path d="M24 4 6 12v14c0 10 7.6 17.6 18 20 10.4-2.4 18-10 18-20V12L24 4Z"/>
							<path d="M15 24h18M24 15v18"/>
						</svg>
					</span>
					<h2><?php esc_html_e( 'The first mods are on the way', 'astra-child' ); ?></h2>
					<p><?php esc_html_e( 'Nothing is published yet. In the meantime there are plenty of maps to play.', 'astra-child' ); ?></p>
					<a class="byrm-btn byrm-btn--ghost" href="<?php echo esc_url( post_type_exists( 'map' ) ? get_post_type_archive_link( 'map' ) : home_url( '/maps/' ) ); ?>">
						<?php esc_html_e( 'Browse the maps', 'astra-child' ); ?>
					</a>
				</div>
			<?php endif; ?>
		</main>
	</div>
</div>

<?php
byrm_close_document();
