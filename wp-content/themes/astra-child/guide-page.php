<?php
/**
 * Any guide written in the editor.
 *
 * Routed here by byrm_guide_template() for pages filed under /guides/ that have
 * no template of their own, so a new guide can be written entirely in the block
 * editor and still come out in the site's design rather than Astra's default.
 *
 * The install guide keeps page-install.php, because its content is hand-built
 * rather than typed into the editor.
 *
 * @package Astra_Child
 */

defined( 'ABSPATH' ) || exit;

byrm_open_document();

while ( have_posts() ) :
	the_post();

	$parent_id  = wp_get_post_parent_id( get_the_ID() );
	$parent_url = $parent_id ? get_permalink( $parent_id ) : '';
	$siblings   = byrm_guide_siblings( get_the_ID() );
	$summary    = byrm_guide_summary( get_post() );
	?>

	<div id="byrm-content" class="byrm-guide">

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

				<?php if ( has_excerpt() && '' !== trim( $summary ) ) : ?>
					<p class="byrm-gbanner__lead"><?php echo esc_html( $summary ); ?></p>
				<?php endif; ?>
			</div>
		</header>

		<div class="byrm-guide__shell byrm-guide__body">

			<section class="byrm-mblock byrm-prose"><?php the_content(); ?></section>

			<?php if ( $siblings['prev'] || $siblings['next'] ) : ?>
				<nav class="byrm-gnav" aria-label="<?php esc_attr_e( 'Other guides', 'astra-child' ); ?>">
					<?php if ( $siblings['prev'] ) : ?>
						<a class="byrm-gnav__link byrm-gnav__link--prev" href="<?php echo esc_url( get_permalink( $siblings['prev'] ) ); ?>">
							<span class="byrm-gnav__dir"><?php esc_html_e( 'Previous', 'astra-child' ); ?></span>
							<span class="byrm-gnav__title"><?php echo esc_html( get_the_title( $siblings['prev'] ) ); ?></span>
						</a>
					<?php endif; ?>

					<?php if ( $siblings['next'] ) : ?>
						<a class="byrm-gnav__link byrm-gnav__link--next" href="<?php echo esc_url( get_permalink( $siblings['next'] ) ); ?>">
							<span class="byrm-gnav__dir"><?php esc_html_e( 'Next', 'astra-child' ); ?></span>
							<span class="byrm-gnav__title"><?php echo esc_html( get_the_title( $siblings['next'] ) ); ?></span>
						</a>
					<?php endif; ?>
				</nav>
			<?php endif; ?>

			<?php if ( $parent_url ) : ?>
				<p class="byrm-gback">
					<a class="byrm-arrow" href="<?php echo esc_url( $parent_url ); ?>">
						<?php esc_html_e( 'All guides', 'astra-child' ); ?>
					</a>
				</p>
			<?php endif; ?>
		</div>
	</div>

	<?php
endwhile;

byrm_close_document();
