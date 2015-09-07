<?php

	  
	  $labels = array(
		'name'               => _x( 'Policys', 'post type general name', 'your-plugin-textdomain' ),
		'singular_name'      => _x( 'Policy', 'post type singular name', 'your-plugin-textdomain' ),
		'menu_name'          => _x( 'Policys', 'admin menu', 'your-plugin-textdomain' ),
		'name_admin_bar'     => _x( 'Policy', 'add new on admin bar', 'your-plugin-textdomain' ),
		'add_new'            => _x( 'Add Policy', 'Policy', 'your-plugin-textdomain' ),
		'add_new_item'       => __( 'Add New Policy', 'your-plugin-textdomain' ),
		'new_item'           => __( 'New Policy', 'your-plugin-textdomain' ),
		'edit_item'          => __( 'Edit Policy', 'your-plugin-textdomain' ),
		'view_item'          => __( 'View Policy', 'your-plugin-textdomain' ),
		'all_items'          => __( 'All Policys', 'your-plugin-textdomain' ),
		'search_items'       => __( 'Search Policys', 'your-plugin-textdomain' ),
		'parent_item_colon'  => __( 'Parent Policys:', 'your-plugin-textdomain' ),
		'not_found'          => __( 'No Policys found.', 'your-plugin-textdomain' ),
		'not_found_in_trash' => __( 'No Policys found in Trash.', 'your-plugin-textdomain' )
	);

	$args = array(
		'labels'             => $labels,
         'description'        => __( 'Description.', 'your-plugin-textdomain' ),
		'public'             => true,
		'publicly_queryable' => true,
		'show_ui'            => true,
		'show_in_menu'       => true,
		'query_var'          => true,
		'rewrite'            => array( 'slug' => 'Policy' ),
		'capability_type'    => 'post',
		'has_archive'        => true,
		'hierarchical'       => false,
		'menu_position'      => null,
		'supports'           => array( 'title', 'editor', 'author', 'thumbnail', 'excerpt','revision' )
	);

	register_post_type( 'Policy', $args );
	
	register_taxonomy(
		'policy-type',
		'policy',
		array(
			'label' => __( 'Policy Type' ),
			'rewrite' => array( 'slug' => 'policy-type' ),
			'hierarchical' => true,
		)
	);
	
	register_taxonomy(
		'policy_tag',
		'policy',
		array(
			'label' => __( 'Policy Tags' ),
			'rewrite' => array( 'slug' => 'policy_tag' )
		)
	);


	
	
	/**
	* Adds a box to the main column on the Post and Page edit screens.
	*/
	function pvplugin_add_meta_box() {
	
	$screens = array( 'policy' );
	
	foreach ( $screens as $screen ) {
	
		add_meta_box(
			'myplugin_sectionid',
			__( 'Custom Meta Box', 'myplugin_textdomain' ),
			'pvplugin_meta_box_callback',
			$screen
		);
	}
	}
	add_action( 'add_meta_boxes', 'pvplugin_add_meta_box' );
	
	/**
 * Prints the box content.
 * 
 * @param WP_Post $post The object for the current post/page.
 */
function pvplugin_meta_box_callback( $post ) {

	// Add a nonce field so we can check for it later.
	wp_nonce_field( 'pvplugin_save_meta_box_data', 'pvplugin_meta_box_nonce' );

	/*
	 * Use get_post_meta() to retrieve an existing value
	 * from the database and use the value for the form.
	 */
	$approvers = get_post_meta( $post->ID, 'approvers', true );
	$next_update = get_post_meta( $post->ID, 'next_update', true );
	$authorize_release = get_post_meta( $post->ID, 'authorize_release', true );
	$date_adopted = get_post_meta( $post->ID, 'date_adopted', true );
	$additional_refrence = get_post_meta( $post->ID, 'additional_refrence', true );
	
	

	echo '<div class="meta_container"><label for="pvplugin_new_field">';
	_e( 'Approvers', 'pvplugin_textdomain' );
	echo '</label>';
	echo '<div class="field"><input type="text" id="approvers" name="approvers" value="' . esc_attr( $approvers ) . '" size="25" /></div></div>';
	
	
	echo '<div class="meta_container"><label for="pvplugin_new_field">';
	_e( 'Next Update', 'pvplugin_textdomain' );
	echo '</label> ';
	echo '<div class="field"><input type="text" id="next_update" name="next_update" value="' . esc_attr( $next_update ) . '" size="25" /></div></div>';
	
	
	echo '<div class="meta_container"><label for="pvplugin_new_field">';
	_e( 'Authorizes Release', 'pvplugin_textdomain' );
	echo '</label> ';
	echo '<div class="field"><input type="text" id="authorize_release" name="authorize_release" value="' . esc_attr( $authorize_release ) . '" size="25" /></div></div>';
	
	echo '<br>';
	
	echo '<div class="meta_container"><label for="pvplugin_new_field">';
	_e( 'Date Adopted', 'pvplugin_textdomain' );
	echo '</label> ';
	echo '<div class="field"><input type="text" id="date_adopted" name="date_adopted" value="' . esc_attr( $date_adopted ) . '" size="25" /></div></div>';
	
	echo '<br>';
	
	echo '<div class="meta_container"><label for="pvplugin_new_field">';
	_e( 'Additional References & Related Policies', 'pvplugin_textdomain' );
	echo '</label> ';
	echo '<div class="field"><input type="text" id="additional_refrence" name="additional_refrence" value="' . esc_attr( $additional_refrence ) . '" size="25" /></div></div>';
}

/**
 * When the post is saved, saves our custom data.
 *
 * @param int $post_id The ID of the post being saved.
 */
function pvplugin_save_meta_box_data( $post_id ) {

	/*
	 * We need to verify this came from our screen and with proper authorization,
	 * because the save_post action can be triggered at other times.
	 */

	// Check if our nonce is set.
	if ( ! isset( $_POST['pvplugin_meta_box_nonce'] ) ) {
		return;
	}

	// Verify that the nonce is valid.
	if ( ! wp_verify_nonce( $_POST['pvplugin_meta_box_nonce'], 'pvplugin_save_meta_box_data' ) ) {
		return;
	}

	// If this is an autosave, our form has not been submitted, so we don't want to do anything.
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}

	// Check the user's permissions.
	if ( isset( $_POST['post_type'] ) && 'policy' == $_POST['post_type'] ) {

		if ( ! current_user_can( 'edit_page', $post_id ) ) {
			return;
		}

	} else {

		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}
	}

	/* OK, it's safe for us to save the data now. */
	


	// Sanitize user input.
	$approvers = sanitize_text_field( $_POST['approvers'] );
	$next_update = sanitize_text_field( $_POST['next_update'] );
	$authorize_release = sanitize_text_field( $_POST['authorize_release'] );
	$date_adopted = sanitize_text_field( $_POST['date_adopted'] );
	$additional_refrence = sanitize_text_field( $_POST['additional_refrence'] );

	// Update the meta field in the database.
	update_post_meta( $post_id, 'approvers', $approvers );
	update_post_meta( $post_id, 'next_update', $next_update );
	update_post_meta( $post_id, 'authorize_release', $authorize_release );
	update_post_meta( $post_id, 'date_adopted', $date_adopted );
	update_post_meta( $post_id, 'additional_refrence', $additional_refrence );
}
add_action( 'save_post', 'pvplugin_save_meta_box_data' );
	
?>