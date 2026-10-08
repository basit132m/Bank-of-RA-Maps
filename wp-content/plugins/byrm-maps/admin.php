<?php
/**
 * Admin screens for the Map post type.
 *
 * @package BYRM_Maps
 */

defined( 'ABSPATH' ) || exit;

/**
 * Register the three panels on the Map edit screen.
 */
function byrm_add_map_meta_boxes() {
	add_meta_box(
		'byrm-map-gallery',
		__( 'Screenshots', 'byrm-maps' ),
		'byrm_render_gallery_box',
		'map',
		'normal',
		'high'
	);

	// Full width rather than the side column: a hosted URL is long, and reading
	// one back in a 250px box to check it is miserable.
	add_meta_box(
		'byrm-map-file',
		__( 'Map download', 'byrm-maps' ),
		'byrm_render_file_box',
		'map',
		'normal',
		'high'
	);

	add_meta_box(
		'byrm-map-specs',
		__( 'Map specifications', 'byrm-maps' ),
		'byrm_render_specs_box',
		'map',
		'normal',
		'high'
	);
}
add_action( 'add_meta_boxes_map', 'byrm_add_map_meta_boxes' );

/**
 * Screenshot gallery picker.
 *
 * @param WP_Post $post Current post.
 */
function byrm_render_gallery_box( $post ) {
	wp_nonce_field( 'byrm_save_map', 'byrm_map_nonce' );

	$ids = byrm_map_gallery( $post->ID );
	?>
	<div class="byrm-gallery-box">
		<p class="description">
			<?php esc_html_e( 'These are the images shown on the map page. Upload them at the highest resolution you have — the page serves full size in the lightbox. Drag to reorder.', 'byrm-maps' ); ?>
		</p>

		<ul class="byrm-gallery-list" id="byrm-gallery-list">
			<?php foreach ( $ids as $id ) : ?>
				<li class="byrm-gallery-item" data-id="<?php echo esc_attr( $id ); ?>">
					<?php echo wp_get_attachment_image( $id, 'medium' ); ?>
					<button type="button" class="byrm-gallery-remove" aria-label="<?php esc_attr_e( 'Remove image', 'byrm-maps' ); ?>">&times;</button>
				</li>
			<?php endforeach; ?>
		</ul>

		<input type="hidden" name="byrm_gallery" id="byrm-gallery-input"
		       value="<?php echo esc_attr( implode( ',', $ids ) ); ?>">

		<p>
			<button type="button" class="button button-primary" id="byrm-gallery-add">
				<?php esc_html_e( 'Add screenshots', 'byrm-maps' ); ?>
			</button>
			<button type="button" class="button" id="byrm-gallery-clear">
				<?php esc_html_e( 'Remove all', 'byrm-maps' ); ?>
			</button>
		</p>

		<p class="description">
			<?php esc_html_e( 'The Featured Image is used separately, as the minimap preview in listings and at the top of the gallery.', 'byrm-maps' ); ?>
		</p>
	</div>
	<?php
}

/**
 * Where the download lives.
 *
 * Map files are hosted off-site, so this panel stores a link rather than a file.
 * Type and size are typed in by hand because the file is not on this server to
 * measure — copy both from whatever the file host shows you.
 *
 * @param WP_Post $post Current post.
 */
function byrm_render_file_box( $post ) {
	$url  = byrm_map_file_url( $post->ID );
	$type = byrm_map_file_type( $post->ID );
	$size = byrm_map_file_size( $post->ID );
	$raw  = (string) get_post_meta( $post->ID, '_byrm_file_url', true );
	?>
	<div class="byrm-file-box">
		<p class="description">
			<?php esc_html_e( 'Upload the map to your file host, then paste the shareable link here. Nothing is stored on this server.', 'byrm-maps' ); ?>
		</p>

		<table class="form-table byrm-specs-table" role="presentation">
			<tbody>
			<tr>
				<th scope="row">
					<label for="byrm-file-url"><?php esc_html_e( 'Download link', 'byrm-maps' ); ?></label>
				</th>
				<td>
					<input type="url" id="byrm-file-url" name="byrm_file_url" class="large-text code"
					       placeholder="https://datadock-host.site/..."
					       value="<?php echo esc_attr( $raw ); ?>">

					<?php if ( $url ) : ?>
						<p class="description">
							<a href="<?php echo esc_url( $url ); ?>" target="_blank" rel="noopener noreferrer">
								<?php esc_html_e( 'Open the link in a new tab to check it', 'byrm-maps' ); ?>
							</a>
							— <?php echo esc_html( byrm_map_file_host( $post->ID ) ); ?>
						</p>
					<?php elseif ( '' !== $raw ) : ?>
						<p class="description byrm-warn">
							<?php esc_html_e( 'That is not a usable link. It has to start with http:// or https:// — anything else is ignored and no download button appears.', 'byrm-maps' ); ?>
						</p>
					<?php else : ?>
						<p class="description">
							<?php esc_html_e( 'Must be a direct, public link. Visitors reach it through /map-download/ so the download still gets counted.', 'byrm-maps' ); ?>
						</p>
					<?php endif; ?>
				</td>
			</tr>

			<tr>
				<th scope="row">
					<label for="byrm-file-type"><?php esc_html_e( 'File type', 'byrm-maps' ); ?></label>
				</th>
				<td>
					<input type="text" id="byrm-file-type" name="byrm_file_type" class="regular-text"
					       list="byrm-file-types" placeholder=".yrm"
					       value="<?php echo esc_attr( $type ); ?>">

					<datalist id="byrm-file-types">
						<option value=".yrm"></option>
						<option value=".map"></option>
						<option value=".mpr"></option>
						<option value=".zip"></option>
						<option value=".rar"></option>
					</datalist>

					<p class="description">
						<?php esc_html_e( 'Shown next to the download button. Pick one from the list or type your own.', 'byrm-maps' ); ?>
					</p>
				</td>
			</tr>

			<tr>
				<th scope="row">
					<label for="byrm-file-size"><?php esc_html_e( 'File size', 'byrm-maps' ); ?></label>
				</th>
				<td>
					<input type="text" id="byrm-file-size" name="byrm_file_size" class="regular-text"
					       placeholder="248 KB" value="<?php echo esc_attr( $size ); ?>">

					<p class="description">
						<?php esc_html_e( 'As your file host reports it. Copy it exactly — "248 KB", "1.2 MB".', 'byrm-maps' ); ?>
					</p>
				</td>
			</tr>

			<tr>
				<th scope="row"><?php esc_html_e( 'Downloads', 'byrm-maps' ); ?></th>
				<td>
					<strong><?php echo esc_html( number_format_i18n( byrm_map_downloads( $post->ID ) ) ); ?></strong>
					<p class="description">
						<?php esc_html_e( 'Counted here, not by the file host. Repeat visits within 24 hours count once.', 'byrm-maps' ); ?>
					</p>
				</td>
			</tr>
			</tbody>
		</table>
	</div>
	<?php
}

/**
 * Specification fields, generated from byrm_map_fields().
 *
 * @param WP_Post $post Current post.
 */
function byrm_render_specs_box( $post ) {
	// This panel is registered for both Maps and Mods. The two have different
	// field lists, so it asks the post type rather than assuming.
	$is_mod = ( 'mod' === $post->post_type && function_exists( 'byrm_mod_fields' ) );
	$fields = $is_mod ? byrm_mod_fields() : byrm_map_fields();
	?>
	<table class="form-table byrm-specs-table" role="presentation">
		<tbody>
		<?php foreach ( $fields as $key => $field ) : ?>
			<?php
			$value = $is_mod ? byrm_mod_meta( $key, $post->ID ) : byrm_map_meta( $key, $post->ID );
			$id    = 'byrm-field-' . $key;
			$name  = 'byrm_field[' . $key . ']';
			?>
			<tr>
				<th scope="row">
					<label for="<?php echo esc_attr( $id ); ?>"><?php echo esc_html( $field['label'] ); ?></label>
				</th>
				<td>
					<?php if ( 'textarea' === $field['type'] ) : ?>
						<textarea id="<?php echo esc_attr( $id ); ?>" name="<?php echo esc_attr( $name ); ?>"
						          rows="4" class="large-text"><?php echo esc_textarea( (string) $value ); ?></textarea>

					<?php elseif ( 'select' === $field['type'] ) : ?>
						<select id="<?php echo esc_attr( $id ); ?>" name="<?php echo esc_attr( $name ); ?>">
							<?php foreach ( $field['options'] as $option => $label ) : ?>
								<option value="<?php echo esc_attr( $option ); ?>"
									<?php selected( (string) $value, (string) $option ); ?>>
									<?php echo esc_html( $label ); ?>
								</option>
							<?php endforeach; ?>
						</select>

					<?php elseif ( 'checkbox' === $field['type'] ) : ?>
						<label>
							<input type="checkbox" id="<?php echo esc_attr( $id ); ?>"
							       name="<?php echo esc_attr( $name ); ?>" value="1"
								<?php checked( '1', (string) $value ); ?>>
							<?php echo esc_html( $field['label'] ); ?>
						</label>

					<?php elseif ( 'number' === $field['type'] ) : ?>
						<input type="number" id="<?php echo esc_attr( $id ); ?>" name="<?php echo esc_attr( $name ); ?>"
						       value="<?php echo esc_attr( (string) $value ); ?>" class="small-text"
						       <?php echo isset( $field['min'] ) ? 'min="' . esc_attr( (string) $field['min'] ) . '"' : ''; ?>
						       <?php echo isset( $field['max'] ) ? 'max="' . esc_attr( (string) $field['max'] ) . '"' : ''; ?>>

					<?php else : ?>
						<input type="text" id="<?php echo esc_attr( $id ); ?>" name="<?php echo esc_attr( $name ); ?>"
						       value="<?php echo esc_attr( (string) $value ); ?>" class="regular-text">
					<?php endif; ?>

					<?php if ( ! empty( $field['hint'] ) ) : ?>
						<p class="description"><?php echo esc_html( $field['hint'] ); ?></p>
					<?php endif; ?>
				</td>
			</tr>
		<?php endforeach; ?>
		</tbody>
	</table>

	<p class="description">
		<?php esc_html_e( "The main editor above is the designer's notes — how the map actually plays. That is the part visitors value most, so it is worth writing properly.", 'byrm-maps' ); ?>
	</p>
	<?php
}

/**
 * Save everything on the Map edit screen.
 *
 * @param int $post_id Post being saved.
 */
function byrm_save_map( $post_id ) {
	// Nonce, autosave and capability checks before touching anything.
	if ( ! isset( $_POST['byrm_map_nonce'] )
		|| ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['byrm_map_nonce'] ) ), 'byrm_save_map' ) ) {
		return;
	}

	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}

	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}

	// Specification fields. Hooked to both save_post_map and save_post_mod, so
	// the list comes from the post type being saved.
	$fields = ( 'mod' === get_post_type( $post_id ) && function_exists( 'byrm_mod_fields' ) )
		? byrm_mod_fields()
		: byrm_map_fields();
	$submitted = isset( $_POST['byrm_field'] ) && is_array( $_POST['byrm_field'] )
		? wp_unslash( $_POST['byrm_field'] )
		: array();

	foreach ( $fields as $key => $field ) {
		$raw = isset( $submitted[ $key ] ) ? $submitted[ $key ] : '';

		switch ( $field['type'] ) {
			case 'number':
				$value = '' === $raw ? '' : (string) absint( $raw );
				break;

			case 'checkbox':
				// An unticked box submits nothing at all.
				$value = isset( $submitted[ $key ] ) ? '1' : '';
				break;

			case 'textarea':
				$value = sanitize_textarea_field( (string) $raw );
				break;

			case 'select':
				$value = array_key_exists( (string) $raw, $field['options'] ) ? (string) $raw : '';
				break;

			default:
				$value = sanitize_text_field( (string) $raw );
		}

		update_post_meta( $post_id, '_byrm_' . $key, $value );
	}

	// Gallery: a comma-separated list of attachment IDs.
	$gallery = isset( $_POST['byrm_gallery'] ) ? sanitize_text_field( wp_unslash( $_POST['byrm_gallery'] ) ) : '';
	$ids     = array_filter( array_map( 'absint', explode( ',', $gallery ) ) );
	update_post_meta( $post_id, '_byrm_gallery', implode( ',', $ids ) );

	// Download: an off-site link, plus the type and size typed alongside it.
	$url = isset( $_POST['byrm_file_url'] ) ? trim( (string) wp_unslash( $_POST['byrm_file_url'] ) ) : '';

	// esc_url_raw() strips anything that is not a URL; the protocol allowlist
	// then rules out javascript:, data: and the rest, so /map-download/ can only
	// ever forward to a real web address.
	$url = '' === $url ? '' : esc_url_raw( $url, array( 'http', 'https' ) );
	update_post_meta( $post_id, '_byrm_file_url', $url );

	$type = isset( $_POST['byrm_file_type'] ) ? sanitize_text_field( wp_unslash( $_POST['byrm_file_type'] ) ) : '';
	update_post_meta( $post_id, '_byrm_file_type', $type );

	$size = isset( $_POST['byrm_file_size'] ) ? sanitize_text_field( wp_unslash( $_POST['byrm_file_size'] ) ) : '';
	update_post_meta( $post_id, '_byrm_file_size', $size );
}
add_action( 'save_post_map', 'byrm_save_map' );

/**
 * Media library scripts and the small amount of styling the panels need.
 *
 * @param string $hook Current admin page.
 */
function byrm_admin_assets( $hook ) {
	if ( ! in_array( $hook, array( 'post.php', 'post-new.php' ), true ) ) {
		return;
	}

	if ( ! in_array( get_post_type(), array( 'map', 'mod' ), true ) ) {
		return;
	}

	wp_enqueue_media();

	$dir = plugin_dir_path( BYRM_MAPS_FILE );
	$uri = plugin_dir_url( BYRM_MAPS_FILE );

	wp_enqueue_script(
		'byrm-admin',
		$uri . 'admin.js',
		array( 'jquery', 'jquery-ui-sortable' ),
		file_exists( $dir . 'admin.js' ) ? (string) filemtime( $dir . 'admin.js' ) : BYRM_MAPS_VERSION,
		true
	);

	wp_localize_script(
		'byrm-admin',
		'byrmAdmin',
		array(
			'galleryTitle' => __( 'Select screenshots', 'byrm-maps' ),
			'galleryButton' => __( 'Use these images', 'byrm-maps' ),
			'confirmClear' => __( 'Remove all screenshots?', 'byrm-maps' ),
		)
	);

	wp_enqueue_style(
		'byrm-admin',
		$uri . 'admin.css',
		array(),
		file_exists( $dir . 'admin.css' ) ? (string) filemtime( $dir . 'admin.css' ) : BYRM_MAPS_VERSION
	);
}
add_action( 'admin_enqueue_scripts', 'byrm_admin_assets' );

/**
 * Useful columns on the Maps list screen.
 *
 * @param  array<string, string> $columns Existing columns.
 * @return array<string, string>
 */
function byrm_map_columns( $columns ) {
	$new = array();

	foreach ( $columns as $key => $label ) {
		$new[ $key ] = $label;

		if ( 'title' === $key ) {
			$new['byrm_preview']   = __( 'Preview', 'byrm-maps' );
			$new['byrm_players']   = __( 'Players', 'byrm-maps' );
			$new['byrm_downloads'] = __( 'Downloads', 'byrm-maps' );
		}
	}

	return $new;
}
add_filter( 'manage_map_posts_columns', 'byrm_map_columns' );

/**
 * @param string $column  Column key.
 * @param int    $post_id Row's post.
 */
function byrm_map_column_content( $column, $post_id ) {
	if ( 'byrm_preview' === $column ) {
		if ( has_post_thumbnail( $post_id ) ) {
			echo get_the_post_thumbnail( $post_id, array( 60, 60 ) );
		} else {
			echo '<span class="description">' . esc_html__( '—', 'byrm-maps' ) . '</span>';
		}
	}

	if ( 'byrm_players' === $column ) {
		echo esc_html( (string) byrm_map_meta( 'players', $post_id ) );
	}

	if ( 'byrm_downloads' === $column ) {
		echo esc_html( number_format_i18n( byrm_map_downloads( $post_id ) ) );
	}
}
add_action( 'manage_map_posts_custom_column', 'byrm_map_column_content', 10, 2 );
