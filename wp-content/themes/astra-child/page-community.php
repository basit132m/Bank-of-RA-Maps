<?php
/**
 * Community — /community.
 *
 * WordPress picks this template up automatically for a Page whose slug is
 * "community", so nothing needs assigning in the admin.
 *
 * The board further down is the point of the page: real messages, posted on
 * this site, threaded and moderated. Everything above it exists to get somebody
 * to write the first one — which channels are live, what the rules are, and what
 * people are already talking about.
 *
 * @package Astra_Child
 */

defined( 'ABSPATH' ) || exit;

byrm_open_document();

$config   = byrm_community_config();
$stats    = byrm_community_stats();
$maps_url = post_type_exists( 'map' ) ? get_post_type_archive_link( 'map' ) : home_url( '/maps/' );
$discord  = $config['discord_url'];
$email    = $config['contact_email'];
$board_on = byrm_community_board_enabled();

while ( have_posts() ) :
	the_post();
	?>

	<div id="byrm-content" class="byrm-community">

		<!-- --------------------------------------------------------- banner -->
		<header class="byrm-cbanner">
			<div class="byrm-cbanner__veil" aria-hidden="true"></div>

			<div class="byrm-community__shell byrm-cbanner__inner">
				<nav class="byrm-ccrumbs" aria-label="<?php esc_attr_e( 'Breadcrumb', 'astra-child' ); ?>">
					<a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Home', 'astra-child' ); ?></a>
					<span aria-hidden="true">/</span>
					<span aria-current="page"><?php the_title(); ?></span>
				</nav>

				<p class="byrm-cbanner__eyebrow"><?php esc_html_e( 'Community', 'astra-child' ); ?></p>
				<h1 class="byrm-cbanner__title"><?php the_title(); ?></h1>
				<p class="byrm-cbanner__lead">
					<?php esc_html_e( 'Red Alert 2 has been going for a quarter of a century and people are still building for it. This is where they talk — about maps, matches, balance and what to make next.', 'astra-child' ); ?>
				</p>

				<div class="byrm-cbanner__actions">
					<?php if ( $board_on ) : ?>
						<a class="byrm-btn byrm-btn--primary" href="#board" data-byrm-jump>
							<?php esc_html_e( 'Post on the board', 'astra-child' ); ?>
						</a>
					<?php endif; ?>

					<?php if ( $discord ) : ?>
						<a class="byrm-btn byrm-btn--discord" href="<?php echo esc_url( $discord ); ?>" target="_blank" rel="noopener">
							<?php
							/* translators: %s: chat service name. */
							printf( esc_html__( 'Join us on %s', 'astra-child' ), esc_html( $config['discord_name'] ) );
							?>
						</a>
					<?php endif; ?>

					<a class="byrm-btn byrm-btn--ghost" href="<?php echo esc_url( $maps_url ); ?>">
						<?php esc_html_e( 'Browse maps', 'astra-child' ); ?>
					</a>
				</div>

				<?php
				// Only counted figures appear here. A tile with nothing behind
				// it is left out rather than shown as a zero, which reads as a
				// dead site even when the site is simply new.
				$tiles = array();

				if ( $stats['messages'] > 0 ) {
					$tiles[] = array(
						'num'   => number_format_i18n( $stats['messages'] ),
						'label' => _n( 'message posted', 'messages posted', $stats['messages'], 'astra-child' ),
					);
				}

				if ( $stats['voices'] > 0 ) {
					$tiles[] = array(
						'num'   => number_format_i18n( $stats['voices'] ),
						'label' => _n( 'person talking', 'people talking', $stats['voices'], 'astra-child' ),
					);
				}

				if ( $stats['maps'] > 0 ) {
					$tiles[] = array(
						'num'   => number_format_i18n( $stats['maps'] ),
						'label' => _n( 'map published', 'maps published', $stats['maps'], 'astra-child' ),
					);
				}

				if ( $tiles ) :
					?>
					<ul class="byrm-cstats">
						<?php foreach ( $tiles as $tile ) : ?>
							<li class="byrm-cstat">
								<span class="byrm-cstat__num"><?php echo esc_html( $tile['num'] ); ?></span>
								<span class="byrm-cstat__label"><?php echo esc_html( $tile['label'] ); ?></span>
							</li>
						<?php endforeach; ?>
					</ul>
				<?php endif; ?>
			</div>
		</header>

		<div class="byrm-community__shell byrm-community__body">

			<!-- ------------------------------------------------------ channels -->
			<section class="byrm-csec" aria-labelledby="byrm-channels-title">
				<header class="byrm-csec__head">
					<h2 id="byrm-channels-title" class="byrm-csec__title"><?php esc_html_e( 'Where the talking happens', 'astra-child' ); ?></h2>
					<p class="byrm-csec__note"><?php esc_html_e( 'Three places, each good for something different.', 'astra-child' ); ?></p>
				</header>

				<div class="byrm-chans">

					<?php if ( $board_on ) : ?>
						<article class="byrm-chan byrm-chan--board">
							<header class="byrm-chan__head">
								<span class="byrm-chan__dot byrm-chan__dot--live" aria-hidden="true"></span>
								<h3 class="byrm-chan__name"><?php esc_html_e( 'The board', 'astra-child' ); ?></h3>
							</header>
							<p class="byrm-chan__desc">
								<?php esc_html_e( 'Right here on the site. Best for anything worth keeping — map requests, feedback, finding players, and questions that deserve a proper answer. No account needed.', 'astra-child' ); ?>
							</p>
							<p class="byrm-chan__foot">
								<a class="byrm-chan__go" href="#board" data-byrm-jump>
									<?php esc_html_e( 'Read and post', 'astra-child' ); ?>
									<span aria-hidden="true">&darr;</span>
								</a>
							</p>
						</article>
					<?php endif; ?>

					<?php if ( $discord ) : ?>
						<article class="byrm-chan byrm-chan--chat">
							<header class="byrm-chan__head">
								<span class="byrm-chan__dot byrm-chan__dot--chat" aria-hidden="true"></span>
								<h3 class="byrm-chan__name"><?php echo esc_html( $config['discord_name'] ); ?></h3>
							</header>
							<p class="byrm-chan__desc">
								<?php esc_html_e( 'Live chat. Best for getting a game together this evening, quick questions, and watching maps get picked apart in real time.', 'astra-child' ); ?>
							</p>
							<p class="byrm-chan__foot">
								<a class="byrm-chan__go" href="<?php echo esc_url( $discord ); ?>" target="_blank" rel="noopener">
									<?php esc_html_e( 'Open the invite', 'astra-child' ); ?>
									<span aria-hidden="true">&rarr;</span>
								</a>
							</p>
						</article>
					<?php elseif ( current_user_can( 'manage_options' ) ) : ?>
						<?php // Visible only to you, so a missing invite never shows as a dead link. ?>
						<article class="byrm-chan byrm-chan--todo">
							<header class="byrm-chan__head">
								<span class="byrm-chan__dot" aria-hidden="true"></span>
								<h3 class="byrm-chan__name"><?php esc_html_e( 'Discord — not set up', 'astra-child' ); ?></h3>
							</header>
							<p class="byrm-chan__desc">
								<?php esc_html_e( 'Only you can see this card. Paste your invite link into byrm_header_settings() in functions.php and it becomes a real Discord card for everyone.', 'astra-child' ); ?>
							</p>
						</article>
					<?php endif; ?>

					<article class="byrm-chan byrm-chan--maps">
						<header class="byrm-chan__head">
							<span class="byrm-chan__dot byrm-chan__dot--maps" aria-hidden="true"></span>
							<h3 class="byrm-chan__name"><?php esc_html_e( 'On a map itself', 'astra-child' ); ?></h3>
						</header>
						<p class="byrm-chan__desc">
							<?php esc_html_e( 'Comments on one particular map — where the spawn is unfair, where the ore runs dry, what it plays like at six players. That belongs on the map, not here.', 'astra-child' ); ?>
						</p>
						<p class="byrm-chan__foot">
							<a class="byrm-chan__go" href="<?php echo esc_url( $maps_url ); ?>">
								<?php esc_html_e( 'Pick a map', 'astra-child' ); ?>
								<span aria-hidden="true">&rarr;</span>
							</a>
						</p>
					</article>

				</div>
			</section>

			<!-- --------------------------------------------------- house rules -->
			<section class="byrm-csec" aria-labelledby="byrm-rules-title">
				<header class="byrm-csec__head">
					<h2 id="byrm-rules-title" class="byrm-csec__title"><?php esc_html_e( 'House rules', 'astra-child' ); ?></h2>
					<p class="byrm-csec__note"><?php esc_html_e( 'Short, and enforced.', 'astra-child' ); ?></p>
				</header>

				<ol class="byrm-rules">
					<li class="byrm-rule">
						<h3><?php esc_html_e( 'Argue about maps, not people', 'astra-child' ); ?></h3>
						<p><?php esc_html_e( 'Tear a map apart all you like. Say the same thing about whoever made it and the message goes.', 'astra-child' ); ?></p>
					</li>
					<li class="byrm-rule">
						<h3><?php esc_html_e( 'Credit the author', 'astra-child' ); ?></h3>
						<p><?php esc_html_e( 'If you post or re-upload somebody else\'s map, name them. If you know who made one we have marked "unknown author", tell us and we will fix the credit.', 'astra-child' ); ?></p>
					</li>
					<li class="byrm-rule">
						<h3><?php esc_html_e( 'No links to the game', 'astra-child' ); ?></h3>
						<p><?php esc_html_e( 'Maps, yes. Copies of Red Alert 2 or Yuri\'s Revenge, no — those get removed without discussion.', 'astra-child' ); ?></p>
					</li>
					<li class="byrm-rule">
						<h3><?php esc_html_e( 'Game problems go elsewhere', 'astra-child' ); ?></h3>
						<p>
							<?php esc_html_e( 'Will not install, will not launch, crashes on load — that is a question for the Command & Conquer forums.', 'astra-child' ); ?>
							<?php if ( $config['forum_url'] ) : ?>
								<a href="<?php echo esc_url( $config['forum_url'] ); ?>" target="_blank" rel="noopener"><?php echo esc_html( $config['forum_name'] ); ?></a>.
							<?php endif; ?>
						</p>
					</li>
					<li class="byrm-rule">
						<h3><?php esc_html_e( 'One language is enough', 'astra-child' ); ?></h3>
						<p><?php esc_html_e( 'English is easiest for most people here, but post in your own if that is easier for you — somebody will usually manage.', 'astra-child' ); ?></p>
					</li>
					<li class="byrm-rule">
						<h3><?php esc_html_e( 'Spam is deleted on sight', 'astra-child' ); ?></h3>
						<p><?php esc_html_e( 'Adverts, affiliate links, and anything that has nothing to do with the game. No warning, no appeal.', 'astra-child' ); ?></p>
					</li>
				</ol>
			</section>

			<!-- -------------------------------------------------------- board -->
			<?php
			if ( $board_on ) {
				// The board partial lives in comments.php so a map page can load
				// exactly the same thing later.
				comments_template( '/comments.php', false );
			}
			?>

			<!-- ----------------------------------------------- recent activity -->
			<?php
			$feed   = byrm_community_recent_comments( (int) $config['feed_count'] );
			$voices = byrm_community_voices( (int) $config['voice_count'] );

			// A roll of honour with one or two names on it looks worse than no
			// roll of honour, so it waits until there are a few.
			$show_voices = count( $voices ) >= 3;

			if ( $feed ) :
				?>
				<section class="byrm-csec byrm-csec--split" aria-labelledby="byrm-feed-title">
					<div class="byrm-csplit<?php echo $show_voices ? '' : ' byrm-csplit--solo'; ?>">

						<div class="byrm-csplit__main">
							<header class="byrm-csec__head">
								<h2 id="byrm-feed-title" class="byrm-csec__title"><?php esc_html_e( 'Latest activity', 'astra-child' ); ?></h2>
								<p class="byrm-csec__note"><?php esc_html_e( 'Across the whole site — the board and every map.', 'astra-child' ); ?></p>
							</header>

							<ul class="byrm-feed">
								<?php
								foreach ( $feed as $item ) :
									$where = get_the_title( $item->comment_post_ID );
									$who   = get_comment_author( $item );
									?>
									<li class="byrm-feed__item">
										<div class="byrm-feed__avatar">
											<?php byrm_community_avatar( $item, 38, $who ); ?>
										</div>

										<div class="byrm-feed__body">
											<p class="byrm-feed__who">
												<span class="byrm-feed__name"><?php echo esc_html( $who ); ?></span>
												<span class="byrm-feed__sep" aria-hidden="true">&middot;</span>
												<span class="byrm-feed__when"><?php echo esc_html( byrm_community_ago( $item ) ); ?></span>
											</p>

											<p class="byrm-feed__text">
												<a href="<?php echo esc_url( get_comment_link( $item ) ); ?>">
													<?php echo esc_html( wp_trim_words( wp_strip_all_tags( $item->comment_content ), 20 ) ); ?>
												</a>
											</p>

											<?php if ( $where ) : ?>
												<p class="byrm-feed__where">
													<?php esc_html_e( 'on', 'astra-child' ); ?>
													<a href="<?php echo esc_url( get_permalink( $item->comment_post_ID ) ); ?>"><?php echo esc_html( $where ); ?></a>
												</p>
											<?php endif; ?>
										</div>
									</li>
								<?php endforeach; ?>
							</ul>
						</div>

						<?php if ( $show_voices ) : ?>
							<aside class="byrm-csplit__side" aria-labelledby="byrm-voices-title">
								<h2 id="byrm-voices-title" class="byrm-csec__title byrm-csec__title--small">
									<?php esc_html_e( 'Most active', 'astra-child' ); ?>
								</h2>

								<ul class="byrm-voices">
									<?php foreach ( $voices as $index => $voice ) : ?>
										<li class="byrm-voice">
											<span class="byrm-voice__rank" aria-hidden="true"><?php echo esc_html( (string) ( $index + 1 ) ); ?></span>
											<span class="byrm-voice__avatar">
												<?php byrm_community_avatar( get_comment( $voice['comment_id'] ), 32, $voice['name'] ); ?>
											</span>
											<span class="byrm-voice__name"><?php echo esc_html( $voice['name'] ); ?></span>
											<span class="byrm-voice__count">
												<?php echo esc_html( number_format_i18n( $voice['total'] ) ); ?>
											</span>
										</li>
									<?php endforeach; ?>
								</ul>

								<p class="byrm-voices__note">
									<?php esc_html_e( 'Counted from approved messages.', 'astra-child' ); ?>
								</p>
							</aside>
						<?php endif; ?>

					</div>
				</section>
			<?php endif; ?>

			<!-- ------------------------------------------------- ways to help -->
			<section class="byrm-csec" aria-labelledby="byrm-help-title">
				<header class="byrm-csec__head">
					<h2 id="byrm-help-title" class="byrm-csec__title"><?php esc_html_e( 'Ways to pitch in', 'astra-child' ); ?></h2>
					<p class="byrm-csec__note"><?php esc_html_e( 'None of it takes long, and all of it helps.', 'astra-child' ); ?></p>
				</header>

				<div class="byrm-helps">
					<article class="byrm-help">
						<h3 class="byrm-help__title"><?php esc_html_e( 'Send a map you made', 'astra-child' ); ?></h3>
						<p><?php esc_html_e( 'Built something and want it published here? Post on the board with what it is, how many players, and a screenshot or two. Your name stays on it.', 'astra-child' ); ?></p>
					</article>

					<article class="byrm-help">
						<h3 class="byrm-help__title"><?php esc_html_e( 'Name an unknown author', 'astra-child' ); ?></h3>
						<p><?php esc_html_e( 'Some maps here were found online with no author attached. If you recognise one, say so — we would much rather credit the person who made it.', 'astra-child' ); ?></p>
					</article>

					<article class="byrm-help">
						<h3 class="byrm-help__title"><?php esc_html_e( 'Report something broken', 'astra-child' ); ?></h3>
						<p><?php esc_html_e( 'A download that fails, a map that will not load in CnCNet, a page that looks wrong. Tell us which map and what happened and it gets fixed.', 'astra-child' ); ?></p>
					</article>

					<article class="byrm-help">
						<h3 class="byrm-help__title"><?php esc_html_e( 'Say what to build next', 'astra-child' ); ?></h3>
						<p><?php esc_html_e( 'Requests get read. Naval, urban, eight-player, a remake of something from the original campaign — if a few people want the same thing, it moves up the list.', 'astra-child' ); ?></p>
					</article>
				</div>

				<?php if ( $email ) : ?>
					<p class="byrm-helps__email">
						<?php esc_html_e( 'Would rather not post in public?', 'astra-child' ); ?>
						<a href="<?php echo esc_url( 'mailto:' . antispambot( $email ) ); ?>"><?php echo esc_html( antispambot( $email ) ); ?></a>
					</p>
				<?php endif; ?>
			</section>

			<?php
			// Anything typed into the page editor renders here, so notes can be
			// added without touching this file.
			$extra = trim( get_the_content() );

			if ( '' !== $extra ) :
				?>
				<section class="byrm-csec byrm-cprose"><?php the_content(); ?></section>
			<?php endif; ?>

			<!-- ---------------------------------------------------- closing cta -->
			<div class="byrm-cpanel">
				<h2><?php esc_html_e( 'New here?', 'astra-child' ); ?></h2>
				<p><?php esc_html_e( 'Grab a map, read the four-step install, and come back and tell everyone how it went.', 'astra-child' ); ?></p>
				<div class="byrm-cpanel__actions">
					<a class="byrm-btn byrm-btn--primary" href="<?php echo esc_url( $maps_url ); ?>">
						<?php esc_html_e( 'Browse the maps', 'astra-child' ); ?>
					</a>
					<a class="byrm-btn byrm-btn--ghost" href="<?php echo esc_url( home_url( '/guides/install' ) ); ?>">
						<?php esc_html_e( 'How to install', 'astra-child' ); ?>
					</a>
				</div>
			</div>

		</div>
	</div>

	<?php
endwhile;

byrm_close_document();
