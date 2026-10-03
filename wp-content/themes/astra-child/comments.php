<?php
/**
 * The discussion board.
 *
 * Loaded by comments_template(). The community page calls it, and single-map.php
 * can call the same file later so every map gets its own thread without a second
 * set of markup to maintain.
 *
 * @package Astra_Child
 */

defined( 'ABSPATH' ) || exit;

// A password-protected post must not leak its discussion.
if ( post_password_required() ) {
	return;
}

$byrm_config = function_exists( 'byrm_community_config' )
	? byrm_community_config()
	: array( 'board_depth' => 4 );

$byrm_total = (int) get_comments_number();
$byrm_depth = isset( $byrm_config['board_depth'] ) ? (int) $byrm_config['board_depth'] : 4;
?>

<section id="board" class="byrm-board" aria-labelledby="byrm-board-title">

	<header class="byrm-board__head">
		<h2 id="byrm-board-title" class="byrm-board__title">
			<?php esc_html_e( 'The board', 'astra-child' ); ?>
		</h2>

		<p class="byrm-board__count">
			<?php
			if ( $byrm_total > 0 ) {
				printf(
					/* translators: %s: number of messages. */
					esc_html( _n( '%s message', '%s messages', $byrm_total, 'astra-child' ) ),
					esc_html( number_format_i18n( $byrm_total ) )
				);
			} else {
				esc_html_e( 'Nothing posted yet', 'astra-child' );
			}
			?>
		</p>
	</header>

	<?php if ( have_comments() ) : ?>

		<ol class="byrm-board__list">
			<?php
			wp_list_comments(
				array(
					// Set explicitly so threaded replies work even when
					// Settings → Discussion has threading switched off.
					'max_depth'    => $byrm_depth,
					'style'        => 'ol',
					'short_ping'   => true,
					'avatar_size'  => 44,
					'callback'     => 'byrm_community_comment',
					'end-callback' => 'byrm_community_comment_end',
				)
			);
			?>
		</ol>

		<?php
		// Only prints anything once the discussion runs past one page, which
		// depends on Settings → Discussion. Harmless until then.
		$byrm_pages = paginate_comments_links(
			array(
				'echo'      => false,
				'type'      => 'list',
				'prev_text' => __( 'Older', 'astra-child' ),
				'next_text' => __( 'Newer', 'astra-child' ),
			)
		);

		if ( $byrm_pages ) :
			?>
			<nav class="byrm-board__pages" aria-label="<?php esc_attr_e( 'Board pages', 'astra-child' ); ?>">
				<?php echo $byrm_pages; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- paginate_comments_links() returns safe markup. ?>
			</nav>
		<?php endif; ?>

	<?php elseif ( comments_open() ) : ?>

		<div class="byrm-board__empty">
			<p class="byrm-board__emptytitle"><?php esc_html_e( 'The board is empty.', 'astra-child' ); ?></p>
			<p>
				<?php esc_html_e( 'Somebody has to go first. Introduce yourself, say which maps you play, or ask for something you would like built.', 'astra-child' ); ?>
			</p>
		</div>

	<?php endif; ?>

	<?php if ( comments_open() ) : ?>

		<?php
		// Starter prompts. They drop a line into the box and put the cursor
		// after it — a nudge past the blank-page problem, and nothing more.
		$byrm_prompts = apply_filters(
			'byrm_community_prompts',
			array(
				__( 'Introduce yourself', 'astra-child' )     => __( "Hi all — I'm ", 'astra-child' ),
				__( 'Looking for a game', 'astra-child' )     => __( 'Looking for players for a ', 'astra-child' ),
				__( 'Request a map', 'astra-child' )          => __( 'Map request: I would love a ', 'astra-child' ),
				__( 'Feedback on a map', 'astra-child' )      => __( 'Played ', 'astra-child' ),
				__( 'Report a problem', 'astra-child' )       => __( 'Something is broken: ', 'astra-child' ),
			)
		);
		?>

		<div class="byrm-starters" data-byrm-starters>
			<span class="byrm-starters__label"><?php esc_html_e( 'Not sure what to write?', 'astra-child' ); ?></span>
			<div class="byrm-starters__row">
				<?php foreach ( $byrm_prompts as $byrm_label => $byrm_text ) : ?>
					<button type="button" class="byrm-starter" data-byrm-prompt="<?php echo esc_attr( $byrm_text ); ?>">
						<?php echo esc_html( $byrm_label ); ?>
					</button>
				<?php endforeach; ?>
			</div>
		</div>

		<?php comment_form( byrm_community_form_args() ); ?>

	<?php else : ?>

		<p class="byrm-board__closed">
			<?php esc_html_e( 'The board is closed to new messages at the moment.', 'astra-child' ); ?>
		</p>

	<?php endif; ?>

</section>
