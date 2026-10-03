<?php
/**
 * Search results — /?s=term
 *
 * Maps are what people come here for, so they lead as cards; pages and guides
 * follow underneath as a plain list. Two things core search cannot find are
 * offered alongside: maps by a designer's name, and whole theaters or game
 * modes whose name matches.
 *
 * WordPress marks search results noindex by itself, so there is nothing to add
 * for search engines.
 *
 * @package Astra_Child
 */

defined( 'ABSPATH' ) || exit;

byrm_open_document();

$query_text = get_search_query();
$total      = (int) $GLOBALS['wp_query']->found_posts;
$maps_url   = post_type_exists( 'map' ) ? get_post_type_archive_link( 'map' ) : home_url( '/maps/' );
$on_page_1  = ( (int) get_query_var( 'paged' ) < 2 );

// Split this page of results: maps as cards, everything else as a list.
$result_maps  = array();
$result_other = array();

if ( have_posts() ) {
	while ( have_posts() ) {
		the_post();

		if ( 'map' === get_post_type() ) {
			$result_maps[] = get_post();
		} else {
			$result_other[] = get_post();
		}
	}

	rewind_posts();
}

// The extras only make sense on the first page — they are not paginated.
$shown       = wp_list_pluck( array_merge( $result_maps, $result_other ), 'ID' );
$by_designer = $on_page_1 ? byrm_search_by_designer( $query_text, $shown ) : array();
$term_hits   = $on_page_1 ? byrm_search_matching_terms( $query_text ) : array();

$found_anything = $total > 0 || $by_designer || $term_hits;
?>

<div id="byrm-content" class="byrm-srch">

	<!-- ------------------------------------------------------------ banner -->
	<header class="byrm-sbanner">
		<div class="byrm-sbanner__veil" aria-hidden="true"></div>

		<div class="byrm-srch__shell byrm-sbanner__inner">
			<nav class="byrm-scrumbs" aria-label="<?php esc_attr_e( 'Breadcrumb', 'astra-child' ); ?>">
				<a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Home', 'astra-child' ); ?></a>
				<span aria-hidden="true">/</span>
				<span aria-current="page"><?php esc_html_e( 'Search', 'astra-child' ); ?></span>
			</nav>

			<p class="byrm-sbanner__eyebrow"><?php esc_html_e( 'Search', 'astra-child' ); ?></p>

			<h1 class="byrm-sbanner__title">
				<?php if ( '' !== $query_text ) : ?>
					<?php esc_html_e( 'Results for', 'astra-child' ); ?>
					<span class="byrm-sbanner__query">&ldquo;<?php echo esc_html( $query_text ); ?>&rdquo;</span>
				<?php else : ?>
					<?php esc_html_e( 'Search the site', 'astra-child' ); ?>
				<?php endif; ?>
			</h1>

			<?php if ( '' !== $query_text ) : ?>
				<p class="byrm-sbanner__count" aria-live="polite">
					<?php
					// The count has to account for the designer and category
					// matches too. Saying "nothing matched" above a screen full
					// of maps by that designer is worse than saying nothing.
					if ( $total > 0 ) {
						printf(
							/* translators: %s: number of results. */
							esc_html( _n( '%s result', '%s results', $total, 'astra-child' ) ),
							'<strong>' . esc_html( number_format_i18n( $total ) ) . '</strong>'
						);
					} elseif ( $by_designer ) {
						$designer_total = count( $by_designer );

						printf(
							/* translators: %s: number of maps. */
							esc_html( _n( '%s map by that designer', '%s maps by that designer', $designer_total, 'astra-child' ) ),
							'<strong>' . esc_html( number_format_i18n( $designer_total ) ) . '</strong>'
						);
					} elseif ( $term_hits ) {
						esc_html_e( 'No map by that name — but it matches a category', 'astra-child' );
					} else {
						esc_html_e( 'Nothing matched that', 'astra-child' );
					}
					?>
				</p>
			<?php endif; ?>

			<form class="byrm-sform" role="search" method="get" action="<?php echo esc_url( home_url( '/' ) ); ?>">
				<label class="byrm-sr" for="byrm-sform-field"><?php esc_html_e( 'Search for', 'astra-child' ); ?></label>
				<input class="byrm-sform__input" type="search" id="byrm-sform-field" name="s"
				       value="<?php echo esc_attr( $query_text ); ?>"
				       placeholder="<?php esc_attr_e( 'Map name, designer, theater…', 'astra-child' ); ?>"
				       autocomplete="off">
				<button class="byrm-btn byrm-btn--primary byrm-sform__submit" type="submit">
					<?php esc_html_e( 'Search', 'astra-child' ); ?>
				</button>
			</form>

			<p class="byrm-sform__hint">
				<?php esc_html_e( 'Looking for something specific?', 'astra-child' ); ?>
				<a href="<?php echo esc_url( add_query_arg( 'q', rawurlencode( $query_text ), $maps_url ) ); ?>">
					<?php esc_html_e( 'Search the catalogue with filters', 'astra-child' ); ?>
				</a>
			</p>
		</div>
	</header>

	<div class="byrm-srch__shell byrm-srch__body">

		<!-- ------------------------------------------- matching categories -->
		<?php if ( $term_hits ) : ?>
			<section class="byrm-ssec" aria-labelledby="byrm-jump-title">
				<header class="byrm-ssec__head">
					<h2 id="byrm-jump-title" class="byrm-ssec__title"><?php esc_html_e( 'Jump straight to', 'astra-child' ); ?></h2>
					<p class="byrm-ssec__note"><?php esc_html_e( 'That matches a whole category, not just a map.', 'astra-child' ); ?></p>
				</header>

				<div class="byrm-sjump">
					<?php foreach ( $term_hits as $hit ) : ?>
						<a class="byrm-sjumplink" href="<?php echo esc_url( $hit['url'] ); ?>">
							<span class="byrm-sjumplink__name"><?php echo esc_html( $hit['name'] ); ?></span>
							<span class="byrm-sjumplink__count">
								<?php
								printf(
									/* translators: %s: number of maps. */
									esc_html( _n( '%s map', '%s maps', $hit['count'], 'astra-child' ) ),
									esc_html( number_format_i18n( $hit['count'] ) )
								);
								?>
							</span>
						</a>
					<?php endforeach; ?>
				</div>
			</section>
		<?php endif; ?>

		<!-- -------------------------------------------------------- maps -->
		<?php if ( $result_maps ) : ?>
			<section class="byrm-ssec" aria-labelledby="byrm-maps-title">
				<header class="byrm-ssec__head">
					<h2 id="byrm-maps-title" class="byrm-ssec__title"><?php esc_html_e( 'Maps', 'astra-child' ); ?></h2>
				</header>

				<div class="byrm-grid">
					<?php foreach ( $result_maps as $map ) : ?>
						<?php byrm_map_card( $map ); ?>
					<?php endforeach; ?>
				</div>
			</section>
		<?php endif; ?>

		<!-- --------------------------------------------- maps by designer -->
		<?php if ( $by_designer ) : ?>
			<section class="byrm-ssec" aria-labelledby="byrm-designer-title">
				<header class="byrm-ssec__head">
					<h2 id="byrm-designer-title" class="byrm-ssec__title"><?php esc_html_e( 'Maps by this designer', 'astra-child' ); ?></h2>
					<p class="byrm-ssec__note">
						<?php
						printf(
							/* translators: %s: the search term. */
							esc_html__( 'Credited to someone matching "%s".', 'astra-child' ),
							esc_html( $query_text )
						);
						?>
					</p>
				</header>

				<div class="byrm-grid">
					<?php foreach ( $by_designer as $map ) : ?>
						<?php byrm_map_card( $map ); ?>
					<?php endforeach; ?>
				</div>
			</section>
		<?php endif; ?>

		<!-- ----------------------------------------------- pages and guides -->
		<?php if ( $result_other ) : ?>
			<section class="byrm-ssec" aria-labelledby="byrm-other-title">
				<header class="byrm-ssec__head">
					<h2 id="byrm-other-title" class="byrm-ssec__title"><?php esc_html_e( 'Pages and guides', 'astra-child' ); ?></h2>
				</header>

				<div class="byrm-slist">
					<?php foreach ( $result_other as $item ) : ?>
						<?php byrm_search_result( $item ); ?>
					<?php endforeach; ?>
				</div>
			</section>
		<?php endif; ?>

		<!-- ---------------------------------------------------- pagination -->
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
			<nav class="byrm-spager" aria-label="<?php esc_attr_e( 'Pagination', 'astra-child' ); ?>">
				<?php foreach ( $pagination as $link ) : ?>
					<?php echo wp_kses_post( $link ); ?>
				<?php endforeach; ?>
			</nav>
		<?php endif; ?>

		<!-- --------------------------------------------------- nothing found -->
		<?php if ( ! $found_anything ) : ?>
			<div class="byrm-sempty">
				<span class="byrm-empty__mark" aria-hidden="true">
					<svg viewBox="0 0 48 48" focusable="false">
						<circle cx="21" cy="21" r="13"/><path d="m40 40-8.5-8.5"/>
					</svg>
				</span>

				<h2 class="byrm-sempty__title">
					<?php if ( '' !== $query_text ) : ?>
						<?php esc_html_e( 'No match for that', 'astra-child' ); ?>
					<?php else : ?>
						<?php esc_html_e( 'Type something to search', 'astra-child' ); ?>
					<?php endif; ?>
				</h2>

				<p>
					<?php if ( '' !== $query_text ) : ?>
						<?php esc_html_e( 'Try fewer words, or a different spelling. Searching for a theater — snow, urban, desert — or a player count often works better than a full map name.', 'astra-child' ); ?>
					<?php else : ?>
						<?php esc_html_e( 'Search for a map name, the person who made it, or a theater.', 'astra-child' ); ?>
					<?php endif; ?>
				</p>

				<div class="byrm-sempty__routes">
					<?php foreach ( byrm_search_routes() as $route ) : ?>
						<a class="byrm-sroute" href="<?php echo esc_url( $route['url'] ); ?>">
							<span class="byrm-sroute__label"><?php echo esc_html( $route['label'] ); ?></span>
							<span class="byrm-sroute__note"><?php echo esc_html( $route['note'] ); ?></span>
						</a>
					<?php endforeach; ?>
				</div>
			</div>
		<?php endif; ?>
	</div>
</div>

<?php
byrm_close_document();
