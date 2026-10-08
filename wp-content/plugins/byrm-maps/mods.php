<?php
/**
 * The Mod post type.
 *
 * Mods get their own menu in the admin rather than sharing the Maps one: a mod
 * is a different thing with different fields, and mixing them in one list makes
 * both harder to find.
 *
 * Almost everything else is deliberately shared with maps. The off-site file
 * link, its type and size, the screenshot gallery and the counted download all
 * live in post meta under the same keys, so the accessors, the admin panels and
 * the /…-download/ endpoint work for either type without a second copy of any
 * of it.
 *
 * @package BYRM_Maps
 */

defined( 'ABSPATH' ) || exit;

/**
 * Every mod field, in one place.
 *
 * Same shape as byrm_map_fields(), so the admin panel and the save routine in
 * admin.php render and store these without knowing anything about mods.
 *
 * @return array<string, array<string, mixed>>
 */
function byrm_mod_fields() {
	return apply_filters(
		'byrm_mod_fields',
		array(
			'mod_version'  => array(
				'label'   => __( 'Version', 'byrm-maps' ),
				'type'    => 'text',
				'default' => '1.0',
			),
			'mod_author'   => array(
				'label' => __( 'Made by', 'byrm-maps' ),
				'type'  => 'text',
				'hint'  => __( 'Credit the person or team who built the mod.', 'byrm-maps' ),
			),
			'mod_requires' => array(
				'label'   => __( 'Requires', 'byrm-maps' ),
				'type'    => 'select',
				'default' => 'yr',
				'options' => array(
					'yr'   => __( "Yuri's Revenge", 'byrm-maps' ),
					'ra2'  => __( 'Red Alert 2', 'byrm-maps' ),
					'both' => __( 'Either', 'byrm-maps' ),
				),
			),
			'mod_multiplayer' => array(
				'label' => __( 'Multiplayer safe', 'byrm-maps' ),
				'type'  => 'checkbox',
				'hint'  => __( 'Tick if everyone in the game can play with this installed. Most mods change the rules, so most are single-player or need every player to install it.', 'byrm-maps' ),
			),
			'mod_homepage' => array(
				'label' => __( 'Official page', 'byrm-maps' ),
				'type'  => 'text',
				'hint'  => __( "The mod's own site or forum thread, if it has one. Linked from the mod page.", 'byrm-maps' ),
			),
			'install_notes' => array(
				'label' => __( 'Install notes', 'byrm-maps' ),
				'type'  => 'textarea',
				'hint'  => __( 'Anything specific to this mod — which folder it goes in, what it overwrites, how to undo it.', 'byrm-maps' ),
			),
		)
	);
}

/**
 * Read one mod field.
 *
 * @param  string   $key     Field key from byrm_mod_fields().
 * @param  int|null $post_id Post to read, or the current post.
 * @return mixed
 */
function byrm_mod_meta( $key, $post_id = null ) {
	$post_id = $post_id ? (int) $post_id : get_the_ID();

	if ( ! $post_id ) {
		return '';
	}

	$value  = get_post_meta( $post_id, '_byrm_' . $key, true );
	$fields = byrm_mod_fields();

	if ( '' === $value && isset( $fields[ $key ]['default'] ) ) {
		return $fields[ $key ]['default'];
	}

	return $value;
}

/**
 * What "Requires" means in words, for the front end.
 *
 * @param  int|null $post_id Post to read, or the current post.
 * @return string
 */
function byrm_mod_requires_label( $post_id = null ) {
	$key     = (string) byrm_mod_meta( 'mod_requires', $post_id );
	$fields  = byrm_mod_fields();
	$options = isset( $fields['mod_requires']['options'] ) ? $fields['mod_requires']['options'] : array();

	return isset( $options[ $key ] ) ? (string) $options[ $key ] : '';
}

/* ==========================================================================
   Post type and taxonomies
   ========================================================================== */

/**
 * Register the Mod post type, with its own admin menu.
 */
function byrm_register_mod_post_type() {
	register_post_type(
		'mod',
		array(
			'labels'        => array(
				'name'               => __( 'Mods', 'byrm-maps' ),
				'singular_name'      => __( 'Mod', 'byrm-maps' ),
				'add_new'            => __( 'Add New Mod', 'byrm-maps' ),
				'add_new_item'       => __( 'Add New Mod', 'byrm-maps' ),
				'edit_item'          => __( 'Edit Mod', 'byrm-maps' ),
				'new_item'           => __( 'New Mod', 'byrm-maps' ),
				'view_item'          => __( 'View Mod', 'byrm-maps' ),
				'search_items'       => __( 'Search Mods', 'byrm-maps' ),
				'not_found'          => __( 'No mods yet', 'byrm-maps' ),
				'not_found_in_trash' => __( 'No mods in the bin', 'byrm-maps' ),
				'all_items'          => __( 'All Mods', 'byrm-maps' ),
				'menu_name'          => __( 'Mods', 'byrm-maps' ),
			),
			'public'        => true,
			'has_archive'   => 'mods',
			'menu_icon'     => 'dashicons-admin-plugins',

			// Directly under Maps, which sits at 5.
			'menu_position' => 6,
			'rewrite'       => array( 'slug' => 'mods', 'with_front' => false ),
			'supports'      => array( 'title', 'editor', 'thumbnail', 'excerpt', 'revisions', 'comments' ),
			'show_in_rest'  => true,
		)
	);
}
add_action( 'init', 'byrm_register_mod_post_type' );

/**
 * What kind of mod it is, plus free-form tags.
 *
 * Separate taxonomies from the map ones: "Snow" means nothing to a mod, and
 * sharing them would put map terms in the mod picker and vice versa.
 */
function byrm_register_mod_taxonomies() {
	register_taxonomy(
		'mod_type',
		'mod',
		array(
			'labels'            => array(
				'name'          => __( 'Mod types', 'byrm-maps' ),
				'singular_name' => __( 'Mod type', 'byrm-maps' ),
				'menu_name'     => __( 'Types', 'byrm-maps' ),
			),
			'public'            => true,
			'hierarchical'      => true,
			'show_admin_column' => true,
			'show_in_rest'      => true,
			'rewrite'           => array( 'slug' => 'mod-type' ),
		)
	);

	register_taxonomy(
		'mod_tag',
		'mod',
		array(
			'labels'            => array(
				'name'          => __( 'Mod tags', 'byrm-maps' ),
				'singular_name' => __( 'Mod tag', 'byrm-maps' ),
				'menu_name'     => __( 'Tags', 'byrm-maps' ),
			),
			'public'            => true,
			'hierarchical'      => false,
			'show_admin_column' => true,
			'show_in_rest'      => true,
			'rewrite'           => array( 'slug' => 'mod-tag' ),
		)
	);
}
add_action( 'init', 'byrm_register_mod_taxonomies' );

/**
 * Seed the type list the first time the mods section is used.
 *
 * Runs once, guarded by an option, because the plugin was already active when
 * mods were added and the activation hook will not fire again.
 */
function byrm_seed_mod_terms() {
	if ( get_option( 'byrm_mod_terms_seeded' ) ) {
		return;
	}

	byrm_register_mod_taxonomies();

	$types = array(
		__( 'Total conversion', 'byrm-maps' ),
		__( 'Balance patch', 'byrm-maps' ),
		__( 'New units', 'byrm-maps' ),
		__( 'Graphics', 'byrm-maps' ),
		__( 'Audio', 'byrm-maps' ),
		__( 'Mission pack', 'byrm-maps' ),
		__( 'Utility', 'byrm-maps' ),
	);

	foreach ( $types as $term ) {
		if ( ! term_exists( $term, 'mod_type' ) ) {
			wp_insert_term( $term, 'mod_type' );
		}
	}

	update_option( 'byrm_mod_terms_seeded', '1', true );
}
add_action( 'init', 'byrm_seed_mod_terms', 20 );

/* ==========================================================================
   Admin
   ========================================================================== */

/**
 * The same three panels the Map screen has, pointed at mods.
 *
 * The gallery and download renderers in admin.php take a post and read nothing
 * type-specific, so they are reused as they are. The specifications panel picks
 * its field list from the post type.
 */
function byrm_add_mod_meta_boxes() {
	add_meta_box(
		'byrm-mod-gallery',
		__( 'Screenshots', 'byrm-maps' ),
		'byrm_render_gallery_box',
		'mod',
		'normal',
		'high'
	);

	add_meta_box(
		'byrm-mod-file',
		__( 'Mod download', 'byrm-maps' ),
		'byrm_render_file_box',
		'mod',
		'normal',
		'high'
	);

	add_meta_box(
		'byrm-mod-specs',
		__( 'Mod details', 'byrm-maps' ),
		'byrm_render_specs_box',
		'mod',
		'normal',
		'high'
	);
}
add_action( 'add_meta_boxes_mod', 'byrm_add_mod_meta_boxes' );

// Same save routine: it reads the field list from the post type.
add_action( 'save_post_mod', 'byrm_save_map' );

/**
 * Useful columns on the Mods list screen.
 *
 * @param  array<string, string> $columns Existing columns.
 * @return array<string, string>
 */
function byrm_mod_columns( $columns ) {
	$new = array();

	foreach ( $columns as $key => $label ) {
		$new[ $key ] = $label;

		if ( 'title' === $key ) {
			$new['byrm_preview']   = __( 'Preview', 'byrm-maps' );
			$new['byrm_requires']  = __( 'Requires', 'byrm-maps' );
			$new['byrm_downloads'] = __( 'Downloads', 'byrm-maps' );
		}
	}

	return $new;
}
add_filter( 'manage_mod_posts_columns', 'byrm_mod_columns' );

/**
 * @param string $column  Column key.
 * @param int    $post_id Row's post.
 */
function byrm_mod_column_content( $column, $post_id ) {
	if ( 'byrm_preview' === $column ) {
		if ( has_post_thumbnail( $post_id ) ) {
			echo get_the_post_thumbnail( $post_id, array( 60, 60 ) );
		} else {
			echo '<span class="description">' . esc_html__( '—', 'byrm-maps' ) . '</span>';
		}
	}

	if ( 'byrm_requires' === $column ) {
		echo esc_html( byrm_mod_requires_label( $post_id ) );
	}

	if ( 'byrm_downloads' === $column ) {
		echo esc_html( number_format_i18n( byrm_map_downloads( $post_id ) ) );
	}
}
add_action( 'manage_mod_posts_custom_column', 'byrm_mod_column_content', 10, 2 );

/**
 * Give every mod a download counter, so "most downloaded" can sort on it.
 *
 * @param int $post_id Post being saved.
 */
function byrm_seed_mod_download_counter( $post_id ) {
	if ( '' === get_post_meta( $post_id, '_byrm_downloads', true ) ) {
		update_post_meta( $post_id, '_byrm_downloads', 0 );
	}
}
add_action( 'save_post_mod', 'byrm_seed_mod_download_counter' );
