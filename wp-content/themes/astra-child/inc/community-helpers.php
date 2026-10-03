<?php
/**
 * Helpers for the community page.
 *
 * The page's centrepiece is a real discussion board, built on WordPress core
 * comments rather than a forum plugin. That is a deliberate choice: core
 * comments already give threaded replies, moderation, spam hooks, email
 * notifications, RSS and an admin screen, and they cost nothing to maintain.
 * A forum plugin would add a second user system, a second set of templates and
 * a second thing to keep updated — for a board that, on day one, has nobody on
 * it yet. If the board outgrows this, the comments can be exported.
 *
 * @package Astra_Child
 */

defined( 'ABSPATH' ) || exit;

/**
 * Configurable bits of the community page.
 *
 * Override from functions.php with the byrm_community_config filter.
 *
 * @return array<string, mixed>
 */
function byrm_community_config() {
	$header = function_exists( 'byrm_header_config' ) ? byrm_header_config() : array();

	$defaults = array(
		// Falls back to the header's Discord invite so there is only one place
		// to paste it. Leave both empty and every Discord link disappears
		// instead of pointing nowhere.
		'discord_url'   => isset( $header['discord_url'] ) ? $header['discord_url'] : '',
		'discord_name'  => 'Discord',
		'forum_url'     => '',   // An external CnC forum you want to point at.
		'forum_name'    => 'CnCNet forums',
		'contact_email' => '',
		'feed_count'    => 6,
		'voice_count'   => 8,
		'board_depth'   => 4,
	);

	return apply_filters( 'byrm_community_config', $defaults );
}

/**
 * The community page's post ID.
 *
 * Resolved by slug and memoised. This has to work outside a query too, because
 * comment submissions land on wp-comments-post.php, where is_page() is useless.
 *
 * @return int
 */
function byrm_community_page_id() {
	static $id = null;

	if ( null !== $id ) {
		return $id;
	}

	$page = get_page_by_path( 'community' );
	$id   = $page ? (int) $page->ID : 0;

	return $id;
}

/**
 * Are we rendering the community page?
 *
 * Guarded on did_action( 'wp' ) so it is safe to call from filters that may run
 * before the main query exists — a conditional tag used that early throws a
 * notice and returns a meaningless answer.
 *
 * @return bool
 */
function byrm_is_community_page() {
	return did_action( 'wp' ) && is_page( array( 'community' ) );
}

/**
 * Is the on-site board switched on?
 *
 * Return false from the byrm_community_board filter to keep the page but drop
 * the board — useful if you would rather run the conversation on Discord only.
 *
 * @return bool
 */
function byrm_community_board_enabled() {
	return (bool) apply_filters( 'byrm_community_board', true );
}

/**
 * Keep the board open for business.
 *
 * WordPress hides the Discussion panel on Pages by default, so the community
 * page would otherwise need a hidden checkbox found and ticked before anyone
 * could post. This forces it open for that one page — and for nothing else.
 *
 * Matched on post ID rather than a conditional tag so it holds on
 * wp-comments-post.php, which is where submissions are actually authorised.
 *
 * @param  bool $open    Whether comments are open.
 * @param  int  $post_id Post being asked about.
 * @return bool
 */
function byrm_community_comments_open( $open, $post_id ) {
	$community = byrm_community_page_id();

	if ( $community && (int) $post_id === $community && byrm_community_board_enabled() ) {
		return true;
	}

	return $open;
}
add_filter( 'comments_open', 'byrm_community_comments_open', 10, 2 );

/**
 * Newest-first on the board.
 *
 * A board is a wall of separate conversations, so the latest one belongs at the
 * top; replies inside a thread stay in the order they were written, which is
 * how WordPress threads them regardless of this setting.
 *
 * @param  string $order Stored comment order.
 * @return string
 */
function byrm_community_comment_order( $order ) {
	return byrm_is_community_page() ? 'desc' : $order;
}
add_filter( 'option_comment_order', 'byrm_community_comment_order' );

/* -------------------------------------------------------------------------
 * Spam guard
 *
 * Two cheap checks that between them stop most drive-by comment spam without a
 * third-party service, a CAPTCHA, or anything that costs a real visitor a
 * moment of their time:
 *
 *   1. A honeypot field, hidden from people and irresistible to bots.
 *   2. A render timestamp — nobody writes and posts a message in five seconds.
 *
 * Both are skipped for signed-in users, and the timing check only applies when
 * the field is actually present, so a comment form from somewhere else is never
 * caught by it.
 * ---------------------------------------------------------------------- */

/**
 * The hidden fields, printed inside the comment form.
 */
function byrm_comment_guard_fields() {
	if ( is_user_logged_in() ) {
		return;
	}

	printf(
		'<p class="byrm-cform__honey" aria-hidden="true">'
			. '<label for="byrm-hp">%s</label>'
			. '<input id="byrm-hp" type="text" name="byrm_hp" value="" tabindex="-1" autocomplete="off">'
			. '</p>',
		esc_html__( 'Leave this field empty', 'astra-child' )
	);

	printf(
		'<input type="hidden" name="byrm_ts" value="%d">',
		(int) time()
	);
}

/**
 * Reject the obvious bots.
 *
 * @param  array<string, mixed> $data Comment data.
 * @return array<string, mixed>
 */
function byrm_comment_spam_guard( $data ) {
	if ( is_user_logged_in() ) {
		return $data;
	}

	// Filled honeypot: only a script does that.
	if ( ! empty( $_POST['byrm_hp'] ) ) {
		wp_die(
			esc_html__( 'That looked automated, so it was not posted. If you are a person, go back and try again.', 'astra-child' ),
			esc_html__( 'Comment not posted', 'astra-child' ),
			array(
				'response'  => 403,
				'back_link' => true,
			)
		);
	}

	// Timing. Only enforced when our own field came back with the form.
	if ( isset( $_POST['byrm_ts'] ) ) {
		$sent = (int) $_POST['byrm_ts'];
		$age  = time() - $sent;

		if ( $sent > 0 && $age < 5 ) {
			wp_die(
				esc_html__( 'That was posted a little too fast to be typed. Go back, wait a moment, and send it again.', 'astra-child' ),
				esc_html__( 'Comment not posted', 'astra-child' ),
				array(
					'response'  => 403,
					'back_link' => true,
				)
			);
		}

		if ( $sent > 0 && $age > DAY_IN_SECONDS ) {
			wp_die(
				esc_html__( 'This page had been open for a while and the form expired. Reload it and post again — your words are still in the box.', 'astra-child' ),
				esc_html__( 'Comment not posted', 'astra-child' ),
				array(
					'response'  => 403,
					'back_link' => true,
				)
			);
		}
	}

	return $data;
}
add_filter( 'preprocess_comment', 'byrm_comment_spam_guard', 5 );

/* -------------------------------------------------------------------------
 * Activity, voices and totals
 * ---------------------------------------------------------------------- */

/**
 * The most recent approved comments across the whole site.
 *
 * Cached for five minutes: the page is read far more often than it is written
 * to, and this runs three queries behind the scenes.
 *
 * @param  int $limit How many to return.
 * @return WP_Comment[]
 */
function byrm_community_recent_comments( $limit = 6 ) {
	$limit = max( 1, (int) $limit );
	$key   = 'byrm_community_feed_' . $limit;

	$cached = get_transient( $key );

	if ( is_array( $cached ) ) {
		return array_filter( array_map( 'get_comment', $cached ) );
	}

	$comments = get_comments(
		array(
			'status'      => 'approve',
			'type'        => 'comment',
			'post_status' => 'publish',
			'number'      => $limit,
			'orderby'     => 'comment_date_gmt',
			'order'       => 'DESC',
		)
	);

	$ids = array_map(
		static function ( $comment ) {
			return (int) $comment->comment_ID;
		},
		$comments
	);

	set_transient( $key, $ids, 5 * MINUTE_IN_SECONDS );

	return $comments;
}

/**
 * Who talks here most.
 *
 * Grouped by display name, which is how people recognise each other. Two
 * different guests who pick the same name will be counted as one — a known
 * trade-off, and the reason the panel is a roll of honour rather than a
 * scoreboard with anything riding on it.
 *
 * @param  int $limit How many names to return.
 * @return array<int, array{name: string, total: int, comment_id: int}>
 */
function byrm_community_voices( $limit = 8 ) {
	$limit = max( 1, (int) $limit );
	$key   = 'byrm_community_voices_' . $limit;

	$cached = get_transient( $key );

	if ( is_array( $cached ) ) {
		return $cached;
	}

	global $wpdb;

	$rows = $wpdb->get_results(
		$wpdb->prepare(
			"SELECT c.comment_author AS name,
			        COUNT(*) AS total,
			        MAX(c.comment_ID) AS comment_id
			 FROM {$wpdb->comments} AS c
			 INNER JOIN {$wpdb->posts} AS p ON p.ID = c.comment_post_ID
			 WHERE c.comment_approved = '1'
			   AND c.comment_type IN ('', 'comment')
			   AND c.comment_author <> ''
			   AND p.post_status = 'publish'
			 GROUP BY c.comment_author
			 ORDER BY total DESC, comment_id DESC
			 LIMIT %d",
			$limit
		),
		ARRAY_A
	);

	$voices = array();

	foreach ( (array) $rows as $row ) {
		$voices[] = array(
			'name'       => (string) $row['name'],
			'total'      => (int) $row['total'],
			'comment_id' => (int) $row['comment_id'],
		);
	}

	set_transient( $key, $voices, 10 * MINUTE_IN_SECONDS );

	return $voices;
}

/**
 * Numbers for the banner tiles.
 *
 * Every one is counted, never estimated: a community page that inflates its own
 * figures is the fastest way to lose the people reading it.
 *
 * @return array{messages: int, voices: int, maps: int}
 */
function byrm_community_stats() {
	$cached = get_transient( 'byrm_community_stats' );

	if ( is_array( $cached ) ) {
		return $cached;
	}

	global $wpdb;

	$counts = wp_count_comments();

	$stats = array(
		'messages' => isset( $counts->approved ) ? (int) $counts->approved : 0,
		'voices'   => (int) $wpdb->get_var(
			"SELECT COUNT(DISTINCT c.comment_author)
			 FROM {$wpdb->comments} AS c
			 INNER JOIN {$wpdb->posts} AS p ON p.ID = c.comment_post_ID
			 WHERE c.comment_approved = '1'
			   AND c.comment_type IN ('', 'comment')
			   AND c.comment_author <> ''
			   AND p.post_status = 'publish'"
		),
		'maps'     => 0,
	);

	if ( function_exists( 'byrm_catalogue_totals' ) ) {
		$totals        = byrm_catalogue_totals();
		$stats['maps'] = isset( $totals['maps'] ) ? (int) $totals['maps'] : 0;
	}

	set_transient( 'byrm_community_stats', $stats, 10 * MINUTE_IN_SECONDS );

	return $stats;
}

/**
 * Drop the cached activity whenever a comment appears, is approved, or goes.
 */
function byrm_flush_community_caches() {
	delete_transient( 'byrm_community_stats' );

	$config = byrm_community_config();

	foreach ( array( (int) $config['feed_count'], 3, 6, 8, 10 ) as $n ) {
		delete_transient( 'byrm_community_feed_' . max( 1, $n ) );
	}

	foreach ( array( (int) $config['voice_count'], 4, 6, 8, 10, 12 ) as $n ) {
		delete_transient( 'byrm_community_voices_' . max( 1, $n ) );
	}
}
add_action( 'wp_insert_comment', 'byrm_flush_community_caches' );
add_action( 'transition_comment_status', 'byrm_flush_community_caches' );
add_action( 'deleted_comment', 'byrm_flush_community_caches' );
add_action( 'edit_comment', 'byrm_flush_community_caches' );

/* -------------------------------------------------------------------------
 * Rendering
 * ---------------------------------------------------------------------- */

/**
 * An avatar, or a monogram when avatars are switched off.
 *
 * get_avatar() returns false when "Show Avatars" is unticked, which many site
 * owners do rather than send visitor data to Gravatar. The initial keeps the
 * layout intact either way, so the board does not fall apart on a privacy
 * setting.
 *
 * @param WP_Comment|int|null $comment Comment or its ID.
 * @param int                 $size    Pixel size.
 * @param string              $name    Name to take the initial from.
 */
function byrm_community_avatar( $comment, $size = 44, $name = '' ) {
	$avatar = $comment ? get_avatar( $comment, $size, '', '', array( 'class' => 'byrm-av__img' ) ) : '';

	if ( $avatar ) {
		printf(
			'<span class="byrm-av" style="--byrm-av-size:%1$dpx">%2$s</span>',
			(int) $size,
			$avatar // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- get_avatar() returns safe markup.
		);

		return;
	}

	$name    = $name ? $name : __( 'Anonymous', 'astra-child' );
	$initial = mb_strtoupper( mb_substr( wp_strip_all_tags( $name ), 0, 1 ) );

	printf(
		'<span class="byrm-av byrm-av--mono" style="--byrm-av-size:%1$dpx" aria-hidden="true">%2$s</span>',
		(int) $size,
		esc_html( $initial )
	);
}

/**
 * How long ago, in words.
 *
 * @param  WP_Comment $comment Comment.
 * @return string
 */
function byrm_community_ago( $comment ) {
	$stamp = get_comment_date( 'U', $comment );

	if ( ! $stamp ) {
		return '';
	}

	/* translators: %s: human readable time difference, e.g. "2 hours". */
	return sprintf( __( '%s ago', 'astra-child' ), human_time_diff( (int) $stamp, time() ) );
}

/**
 * One comment on the board.
 *
 * Used as the wp_list_comments() callback, so it opens the list item and
 * byrm_community_comment_end() closes it — the walker prints any nested replies
 * in between.
 *
 * @param WP_Comment           $comment Comment being rendered.
 * @param array<string, mixed> $args    Walker arguments.
 * @param int                  $depth   Current depth.
 */
function byrm_community_comment( $comment, $args, $depth ) {
	$author  = get_comment_author( $comment );
	$held    = '0' === (string) $comment->comment_approved;
	$classes = array( 'byrm-cm' );

	if ( $held ) {
		$classes[] = 'byrm-cm--held';
	}

	if ( $comment->user_id && user_can( (int) $comment->user_id, 'edit_posts' ) ) {
		$classes[] = 'byrm-cm--staff';
	}
	?>
	<li id="comment-<?php comment_ID(); ?>" <?php comment_class( $classes, $comment ); ?>>
		<article class="byrm-cm__inner">
			<div class="byrm-cm__avatar">
				<?php byrm_community_avatar( $comment, 44, $author ); ?>
			</div>

			<div class="byrm-cm__main">
				<header class="byrm-cm__head">
					<span class="byrm-cm__author"><?php echo esc_html( $author ); ?></span>

					<?php if ( in_array( 'byrm-cm--staff', $classes, true ) ) : ?>
						<span class="byrm-cm__badge"><?php esc_html_e( 'Site team', 'astra-child' ); ?></span>
					<?php endif; ?>

					<a class="byrm-cm__date" href="<?php echo esc_url( get_comment_link( $comment ) ); ?>">
						<time datetime="<?php echo esc_attr( get_comment_date( 'c', $comment ) ); ?>">
							<?php echo esc_html( byrm_community_ago( $comment ) ); ?>
						</time>
					</a>
				</header>

				<?php if ( $held ) : ?>
					<p class="byrm-cm__hold">
						<?php esc_html_e( 'Waiting to be approved — only you can see this.', 'astra-child' ); ?>
					</p>
				<?php endif; ?>

				<div class="byrm-cm__text"><?php comment_text( $comment ); ?></div>

				<footer class="byrm-cm__tools">
					<?php
					comment_reply_link(
						array(
							'depth'      => $depth,
							'max_depth'  => isset( $args['max_depth'] ) ? $args['max_depth'] : 4,
							'reply_text' => __( 'Reply', 'astra-child' ),
							'before'     => '',
							'after'      => '',
							'class'      => 'byrm-cm__reply',
						),
						$comment
					);

					// Three arguments only: the fourth was added in a recent release,
					// and the walker has already set the global comment anyway.
					edit_comment_link( __( 'Edit', 'astra-child' ), '<span class="byrm-cm__edit">', '</span>' );
					?>
				</footer>
			</div>
		</article>
	<?php
	// No closing </li> — the walker adds the replies first, then calls the end
	// callback below.
}

/**
 * Close a board item.
 */
function byrm_community_comment_end() {
	echo '</li>';
}

/**
 * Arguments for the board's comment form.
 *
 * Every field is rewritten so the form matches the rest of the site; the IDs
 * WordPress's threading script depends on — respond, reply-title,
 * cancel-comment-reply-link, comment_parent — are left exactly as they are, or
 * the Reply links stop working.
 *
 * @return array<string, mixed>
 */
function byrm_community_form_args() {
	$commenter = wp_get_current_commenter();
	$required  = (bool) get_option( 'require_name_email' );
	$mark      = $required ? ' <span class="byrm-cform__req" aria-hidden="true">*</span>' : '';
	$req_attr  = $required ? ' required' : '';

	$author = sprintf(
		'<p class="byrm-cform__field"><label for="author">%1$s%2$s</label>'
			. '<input id="author" name="author" type="text" value="%3$s" maxlength="245" autocomplete="nickname"%4$s></p>',
		esc_html__( 'Name', 'astra-child' ),
		$mark,
		esc_attr( $commenter['comment_author'] ),
		$req_attr
	);

	$email = sprintf(
		'<p class="byrm-cform__field"><label for="email">%1$s%2$s</label>'
			. '<input id="email" name="email" type="email" value="%3$s" maxlength="100" autocomplete="email"%4$s>'
			. '<span class="byrm-cform__hint">%5$s</span></p>',
		esc_html__( 'Email', 'astra-child' ),
		$mark,
		esc_attr( $commenter['comment_author_email'] ),
		$req_attr,
		esc_html__( 'Never published. Used for your avatar and reply notifications.', 'astra-child' )
	);

	$comment = sprintf(
		'<p class="byrm-cform__field byrm-cform__field--wide"><label for="comment">%1$s</label>'
			. '<textarea id="comment" name="comment" rows="5" maxlength="4000" required '
			. 'placeholder="%2$s" data-byrm-draft="1"></textarea>'
			. '<span class="byrm-cform__hint">%3$s</span></p>',
		esc_html__( 'Message', 'astra-child' ),
		esc_attr__( 'Say hello, ask about a map, or post what you are working on…', 'astra-child' ),
		esc_html__( 'Be civil, stay on topic, and do not post download links to the game itself.', 'astra-child' )
	);

	return array(
		'fields'               => array(
			'author' => $author,
			'email'  => $email,
			// No website field: it adds nothing here and is a magnet for
			// link spam.
		),
		'comment_field'        => $comment,
		'class_container'      => 'comment-respond byrm-cform__wrap',
		'class_form'           => 'comment-form byrm-cform',
		'class_submit'         => 'byrm-btn byrm-btn--primary byrm-cform__submit',
		'name_submit'          => 'submit',
		'title_reply'          => __( 'Post to the board', 'astra-child' ),
		/* translators: %s: name of the person being replied to. */
		'title_reply_to'       => __( 'Reply to %s', 'astra-child' ),
		'title_reply_before'   => '<h3 id="reply-title" class="byrm-cform__title">',
		'title_reply_after'    => '</h3>',
		'cancel_reply_before'  => ' <span class="byrm-cform__cancel">',
		'cancel_reply_after'   => '</span>',
		'cancel_reply_link'    => __( 'Cancel reply', 'astra-child' ),
		'label_submit'         => __( 'Post message', 'astra-child' ),
		'comment_notes_before' => '',
		'comment_notes_after'  => '',
		'format'               => 'html5',
	);
}

/**
 * Print the guard fields inside every comment form we render.
 */
add_action( 'comment_form', 'byrm_comment_guard_fields' );
