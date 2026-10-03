<?php
/**
 * Helpers for the guides section.
 *
 * Guides are ordinary Pages filed under /guides/. That means the index below
 * never needs editing: publish a child page and it appears, reorder them with
 * the Order field and the index follows. It also means a guide can be written
 * entirely in the block editor — only the install guide needs a template of its
 * own, because its content is hand-built.
 *
 * @package Astra_Child
 */

defined( 'ABSPATH' ) || exit;

/**
 * The /guides/ page's ID, or 0 before it has been created.
 *
 * Memoised, and resolved by slug so nothing has to be configured.
 *
 * @return int
 */
function byrm_guides_page_id() {
	static $id = null;

	if ( null !== $id ) {
		return $id;
	}

	$page = get_page_by_path( 'guides' );
	$id   = $page ? (int) $page->ID : 0;

	return $id;
}

/**
 * Is this the guides index itself?
 *
 * @return bool
 */
function byrm_is_guides_index() {
	return did_action( 'wp' ) && is_page( array( 'guides' ) );
}

/**
 * Is this the index or any guide filed under it?
 *
 * Matching on ancestry rather than a list of slugs is what lets a new guide be
 * published without touching the theme. The slug fallback keeps the install
 * guide styled correctly even before the parent page exists.
 *
 * @return bool
 */
function byrm_is_guide_page() {
	if ( ! did_action( 'wp' ) || ! is_page() ) {
		return false;
	}

	$guides = byrm_guides_page_id();

	if ( ! $guides ) {
		return is_page( array( 'install' ) );
	}

	$current = get_queried_object_id();

	if ( (int) $current === $guides ) {
		return true;
	}

	return in_array( $guides, array_map( 'absint', get_post_ancestors( $current ) ), true );
}

/**
 * Every published guide, in the order they should be read.
 *
 * Ordered by the page Order field first, so the sequence is yours to set, then
 * by title so unordered guides are at least predictable.
 *
 * @return WP_Post[]
 */
function byrm_guide_children() {
	$guides = byrm_guides_page_id();

	if ( ! $guides ) {
		return array();
	}

	return get_posts(
		array(
			'post_type'      => 'page',
			'post_parent'    => $guides,
			'post_status'    => 'publish',
			'posts_per_page' => -1,
			'orderby'        => array(
				'menu_order' => 'ASC',
				'title'      => 'ASC',
			),
			'no_found_rows'  => true,
		)
	);
}

/**
 * The guide to put at the top as the one everybody needs first.
 *
 * @return string Slug, or an empty string for no featured guide.
 */
function byrm_guide_featured_slug() {
	return (string) apply_filters( 'byrm_guide_featured', 'install' );
}

/**
 * A one-line summary for a guide card.
 *
 * The page Excerpt field wins. Guides whose content lives in a template rather
 * than the editor have nothing to auto-generate from, so a fallback is kept
 * here for those — write an excerpt on any new guide and this never applies.
 *
 * @param  WP_Post $page Guide.
 * @return string
 */
function byrm_guide_summary( $page ) {
	if ( has_excerpt( $page ) ) {
		return (string) get_the_excerpt( $page );
	}

	$content = trim( wp_strip_all_tags( (string) $page->post_content ) );

	if ( '' !== $content ) {
		return $content;
	}

	$fallbacks = apply_filters(
		'byrm_guide_summaries',
		array(
			'install' => __( 'Download the right version, rename it to .map, drop it in RA2 → Maps → Custom, then pick it under STANDARD in CnCNet.', 'astra-child' ),
		)
	);

	return isset( $fallbacks[ $page->post_name ] ) ? (string) $fallbacks[ $page->post_name ] : '';
}

/**
 * The guide before and after this one, for the footer of a guide page.
 *
 * @param  int $post_id Current guide.
 * @return array{prev: WP_Post|null, next: WP_Post|null}
 */
function byrm_guide_siblings( $post_id ) {
	$all  = byrm_guide_children();
	$out  = array(
		'prev' => null,
		'next' => null,
	);

	foreach ( $all as $index => $page ) {
		if ( (int) $page->ID !== (int) $post_id ) {
			continue;
		}

		$out['prev'] = isset( $all[ $index - 1 ] ) ? $all[ $index - 1 ] : null;
		$out['next'] = isset( $all[ $index + 1 ] ) ? $all[ $index + 1 ] : null;

		break;
	}

	return $out;
}

/**
 * One guide on the index.
 *
 * @param WP_Post $page    Guide.
 * @param int     $number  Position in the list, for the marker.
 */
function byrm_guide_card( $page, $number ) {
	$summary = byrm_guide_summary( $page );
	$link    = get_permalink( $page );
	?>
	<a class="byrm-gcard" href="<?php echo esc_url( $link ); ?>">
		<span class="byrm-gcard__num" aria-hidden="true"><?php echo esc_html( sprintf( '%02d', (int) $number ) ); ?></span>

		<span class="byrm-gcard__body">
			<span class="byrm-gcard__title"><?php echo esc_html( get_the_title( $page ) ); ?></span>

			<?php if ( '' !== trim( $summary ) ) : ?>
				<span class="byrm-gcard__excerpt"><?php echo esc_html( wp_trim_words( $summary, 26 ) ); ?></span>
			<?php endif; ?>
		</span>

		<span class="byrm-gcard__go" aria-hidden="true">&rarr;</span>
	</a>
	<?php
}
