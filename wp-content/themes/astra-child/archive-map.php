<?php
/**
 * Maps archive — the browse page at /maps/.
 *
 * Also used for the theater, mode and tag term archives, routed here from
 * functions.php so filtering works the same way everywhere.
 *
 * @package Astra_Child
 */

defined( 'ABSPATH' ) || exit;

byrm_open_document();

$filters   = byrm_archive_filters();
$chips     = byrm_archive_chips();
$filtered  = byrm_archive_is_filtered();
$total     = (int) $GLOBALS['wp_query']->found_posts;
$players   = byrm_archive_player_counts();
$theaters  = byrm_archive_terms( 'map_theater' );
$modes     = byrm_archive_terms( 'map_mode' );
$tags      = byrm_archive_terms( 'map_tag' );
$base_url  = get_post_type_archive_link( 'map' ) ? get_post_type_archive_link( 'map' ) : home_url( '/maps/' );
$catalogue = byrm_catalogue_totals();

// On a term archive the heading is the term, not the generic title.
$term        = is_tax() ? get_queried_object() : null;
$heading     = ( $term && ! is_wp_error( $term ) ) ? $term->name : __( 'All maps', 'astra-child' );
$description = ( $term && ! is_wp_error( $term ) && $term->description )
	? $term->description
	: __( 'Every map in the catalogue. Filter by player count, terrain, mode or tag to find one that suits tonight\'s game.', 'astra-child' );
?>

<div id="byrm-content" class="byrm-archive">

	<!-- ------------------------------------------------------------ head -->
	<header class="byrm-abanner">
		<div class="byrm-abanner__veil" aria-hidden="true"></div>

		<div class="byrm-archive__shell byrm-abanner__inner">
			<nav class="byrm-crumbs" aria-label="<?php esc_attr_e( 'Breadcrumb', 'astra-child' ); ?>">
				<a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Home', 'astra-child' ); ?></a>
				<span aria-hidden="true">/</span>
				<?php if ( $term && ! is_wp_error( $term ) ) : ?>
					<a href="<?php echo esc_url( $base_url ); ?>"><?php esc_html_e( 'Maps', 'astra-child' ); ?></a>
					<span aria-hidden="true">/</span>
					<span aria-current="page"><?php echo esc_html( $heading ); ?></span>
				<?php else : ?>
					<span aria-current="page"><?php esc_html_e( 'Maps', 'astra-child' ); ?></span>
				<?php endif; ?>
			</nav>

			<h1 class="byrm-abanner__title"><?php echo esc_html( $heading ); ?></h1>
			<p class="byrm-abanner__lead"><?php echo esc_html( wp_strip_all_tags( $description ) ); ?></p>

			<?php if ( $catalogue['maps'] > 0 ) : ?>
				<p class="byrm-abanner__stat">
					<?php
					printf(
						/* translators: 1: number of maps, 2: number of downloads */
						esc_html__( '%1$s maps published · %2$s downloads so far · always free', 'astra-child' ),
						esc_html( number_format_i18n( $catalogue['maps'] ) ),
						esc_html( number_format_i18n( $catalogue['downloads'] ) )
					);
					?>
				</p>
			<?php endif; ?>
		</div>
	</header>

	<div class="byrm-archive__shell byrm-archive__layout">

		<!-- ------------------------------------------------------ filters -->
		<button class="byrm-filters__toggle" type="button" data-byrm-filters-toggle
		        aria-expanded="false" aria-controls="byrm-filters">
			<svg viewBox="0 0 24 24" aria-hidden="true" focusable="false">
				<path d="M3 6h18M7 12h10M11 18h2" stroke-linecap="round"/>
			</svg>
			<?php esc_html_e( 'Filters', 'astra-child' ); ?>
			<?php if ( $chips ) : ?>
				<span class="byrm-filters__badge"><?php echo esc_html( count( $chips ) ); ?></span>
			<?php endif; ?>
		</button>

		<aside class="byrm-filters" id="byrm-filters" aria-label="<?php esc_attr_e( 'Filter maps', 'astra-child' ); ?>">
			<form class="byrm-filters__form" method="get" action="<?php echo esc_url( $base_url ); ?>" data-byrm-filter-form>

				<div class="byrm-fgroup">
					<label class="byrm-fgroup__title" for="byrm-q"><?php esc_html_e( 'Search', 'astra-child' ); ?></label>
					<div class="byrm-fsearch">
						<input type="search" id="byrm-q" name="q" value="<?php echo esc_attr( $filters['q'] ); ?>"
						       placeholder="<?php esc_attr_e( 'Map name or keyword…', 'astra-child' ); ?>">
						<button type="submit" aria-label="<?php esc_attr_e( 'Search', 'astra-child' ); ?>">
							<svg viewBox="0 0 24 24" aria-hidden="true" focusable="false">
								<circle cx="11" cy="11" r="7"/><path d="m20 20-3.5-3.5" stroke-linecap="round"/>
							</svg>
						</button>
					</div>
				</div>

				<?php if ( $players ) : ?>
					<fieldset class="byrm-fgroup">
						<legend class="byrm-fgroup__title"><?php esc_html_e( 'Players', 'astra-child' ); ?></legend>
						<div class="byrm-chipset">
							<?php foreach ( $players as $count => $maps ) : ?>
								<?php $on = (int) $filters['players'] === (int) $count; ?>
								<a class="byrm-choice<?php echo $on ? ' is-on' : ''; ?>"
								   href="<?php echo esc_url( byrm_archive_url( array( 'players' => $on ? null : $count ) ) ); ?>"
								   <?php echo $on ? 'aria-current="true"' : ''; ?>>
									<?php echo esc_html( $count ); ?>
									<span><?php echo esc_html( $maps ); ?></span>
								</a>
							<?php endforeach; ?>
						</div>
					</fieldset>
				<?php endif; ?>

				<?php
				foreach ( array(
					array( 'param' => 'theater', 'title' => __( 'Terrain', 'astra-child' ), 'terms' => $theaters ),
					array( 'param' => 'mode',    'title' => __( 'Game mode', 'astra-child' ), 'terms' => $modes ),
					array( 'param' => 'mtag',    'title' => __( 'Tags', 'astra-child' ), 'terms' => $tags ),
				) as $group ) :
					if ( ! $group['terms'] ) {
						continue;
					}
					?>
					<fieldset class="byrm-fgroup">
						<legend class="byrm-fgroup__title"><?php echo esc_html( $group['title'] ); ?></legend>
						<ul class="byrm-flist">
							<?php foreach ( $group['terms'] as $t ) : ?>
								<?php $on = $filters[ $group['param'] ] === $t->slug; ?>
								<li>
									<a class="byrm-flist__item<?php echo $on ? ' is-on' : ''; ?>"
									   href="<?php echo esc_url( byrm_archive_url( array( $group['param'] => $on ? null : $t->slug ) ) ); ?>"
									   <?php echo $on ? 'aria-current="true"' : ''; ?>>
										<span class="byrm-flist__box" aria-hidden="true"></span>
										<span class="byrm-flist__name"><?php echo esc_html( $t->name ); ?></span>
										<span class="byrm-flist__count"><?php echo esc_html( number_format_i18n( $t->count ) ); ?></span>
									</a>
								</li>
							<?php endforeach; ?>
						</ul>
					</fieldset>
				<?php endforeach; ?>

				<?php if ( $filtered ) : ?>
					<a class="byrm-filters__clear" href="<?php echo esc_url( $base_url ); ?>">
						<?php esc_html_e( 'Clear all filters', 'astra-child' ); ?>
					</a>
				<?php endif; ?>

				<?php // Keeps the current sort when the search box is submitted. ?>
				<input type="hidden" name="sort" value="<?php echo esc_attr( $filters['sort'] ); ?>">

				<noscript>
					<button class="byrm-btn byrm-btn--primary byrm-filters__apply" type="submit">
						<?php esc_html_e( 'Apply', 'astra-child' ); ?>
					</button>
				</noscript>
			</form>
		</aside>

		<!-- ------------------------------------------------------ results -->
		<main class="byrm-results">

			<div class="byrm-results__bar">
				<p class="byrm-results__count" aria-live="polite">
					<strong><?php echo esc_html( number_format_i18n( $total ) ); ?></strong>
					<?php
					// Pluralise the whole phrase, not just the noun — "1 map match" is wrong.
					echo esc_html(
						$filtered
							? _n( 'map matches your filters', 'maps match your filters', $total, 'astra-child' )
							: _n( 'map in the catalogue', 'maps in the catalogue', $total, 'astra-child' )
					);
					?>
				</p>

				<label class="byrm-sort">
					<span class="byrm-sr"><?php esc_html_e( 'Sort by', 'astra-child' ); ?></span>
					<select data-byrm-sort>
						<?php foreach ( byrm_archive_sorts() as $value => $label ) : ?>
							<option value="<?php echo esc_url( byrm_archive_url( array( 'sort' => $value ) ) ); ?>"
								<?php selected( $filters['sort'], $value ); ?>>
								<?php echo esc_html( $label ); ?>
							</option>
						<?php endforeach; ?>
					</select>
				</label>
			</div>

			<?php if ( $chips ) : ?>
				<div class="byrm-chips">
					<?php foreach ( $chips as $chip ) : ?>
						<a class="byrm-chip" href="<?php echo esc_url( $chip['url'] ); ?>">
							<?php echo esc_html( $chip['label'] ); ?>
							<span aria-hidden="true">&times;</span>
							<span class="byrm-sr"><?php esc_html_e( '(remove filter)', 'astra-child' ); ?></span>
						</a>
					<?php endforeach; ?>
				</div>
			<?php endif; ?>

			<?php if ( have_posts() ) : ?>
				<div class="byrm-grid">
					<?php
					while ( have_posts() ) :
						the_post();
						byrm_map_card( get_post() );
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

			<?php elseif ( $catalogue['maps'] > 0 ) : ?>
				<div class="byrm-empty">
					<span class="byrm-empty__mark" aria-hidden="true">
						<svg viewBox="0 0 48 48" focusable="false">
							<circle cx="21" cy="21" r="13"/><path d="m40 40-8.5-8.5"/>
						</svg>
					</span>
					<h2><?php esc_html_e( 'No maps match that', 'astra-child' ); ?></h2>
					<p><?php esc_html_e( 'Nothing in the catalogue fits that combination yet. Try widening the filters, or clear them and start again.', 'astra-child' ); ?></p>
					<a class="byrm-btn byrm-btn--primary" href="<?php echo esc_url( $base_url ); ?>">
						<?php esc_html_e( 'Clear all filters', 'astra-child' ); ?>
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
					<h2><?php esc_html_e( 'The first maps are on the way', 'astra-child' ); ?></h2>
					<p><?php esc_html_e( 'Nothing is published yet. New releases land here the moment they are ready.', 'astra-child' ); ?></p>
					<a class="byrm-btn byrm-btn--ghost" href="<?php echo esc_url( home_url( '/guides/install' ) ); ?>">
						<?php esc_html_e( 'Read the install guide', 'astra-child' ); ?>
					</a>
				</div>
			<?php endif; ?>
		</main>
	</div>
</div>

<?php
byrm_close_document();
