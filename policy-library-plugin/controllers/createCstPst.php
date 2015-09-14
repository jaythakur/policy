<?php

/**
 * This is file is responsible for custom logic needed by all templates. NO
 * admin code should be placed in this file.
 */
Class CreateCustomPostType {

    /**
     * Run the following methods when this class is loaded
     */
    public function __construct(){
        add_action( 'init', array( &$this, 'init' ) );
		add_action( 'add_meta_boxes', array( &$this, 'pvplugin_add_meta_box' ) );
		add_action( 'save_post', array( &$this, 'pvplugin_save_meta_box_data' ) );
		add_action( 'restrict_manage_posts', array( &$this, 'wpse45436_admin_posts_filter_restrict_manage_posts' ) );
		add_filter( 'parse_query', array( &$this, 'wpse45436_posts_filter' ) );
		
		
    }


    /**
     * During WordPress' init load various methods.
     */
    public function init(){
		
		  $labels = array(
		'name'               => _x( 'Policies', 'post type general name', 'your-plugin-textdomain' ),
		'singular_name'      => _x( 'Policy', 'post type singular name', 'your-plugin-textdomain' ),
		'menu_name'          => _x( 'Policies', 'admin menu', 'your-plugin-textdomain' ),
		'name_admin_bar'     => _x( 'Policy', 'add new on admin bar', 'your-plugin-textdomain' ),
		'add_new'            => _x( 'Add Policy', 'Policy', 'your-plugin-textdomain' ),
		'add_new_item'       => __( 'Add New Policy', 'your-plugin-textdomain' ),
		'new_item'           => __( 'New Policy', 'your-plugin-textdomain' ),
		'edit_item'          => __( 'Edit Policy', 'your-plugin-textdomain' ),
		'view_item'          => __( 'View Policies', 'your-plugin-textdomain' ),
		'all_items'          => __( 'All Policies', 'your-plugin-textdomain' ),
		'search_items'       => __( 'Search Policy', 'your-plugin-textdomain' ),
		'parent_item_colon'  => __( 'Parent Policy:', 'your-plugin-textdomain' ),
		'not_found'          => __( 'No Policy found.', 'your-plugin-textdomain' ),
		'not_found_in_trash' => __( 'No Policy found in Trash.', 'your-plugin-textdomain' )
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
		'policy-types',
		'policy',
		array(
			'label' => __( 'Policy Types' ),
			'rewrite' => array( 'slug' => 'policy-types' ),
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


       
    }
	
	
	/**
	* Adds a box to the main column on the Post and Page edit screens.
	*/
	function pvplugin_add_meta_box() {
	
	$screens = array( 'policy' );
	
	foreach ( $screens as $screen ) {
	
		add_meta_box(
			'myplugin_sectionid',
			__( 'Custom Meta Box', 'policy_library_plugin' ),
			 array( &$this, 'pvplugin_meta_box_callback' ),
			$screen
		);
		
		add_meta_box(
			'myplugin_side_sectionid',
			__( 'Policy Expire', 'policy_library_plugin' ),
			 array( &$this, 'pvplugin_side_meta_box_callback' ),
			$screen,'side','high'
		);
		
	}
	}

	
	function pvplugin_side_meta_box_callback( $post ) {
			global $wpdb;
			global $user_ID;
			$expiration_date = get_post_meta( $post->ID, '_expiration-date', true );
			if($expiration_date) {
$diff = abs($expiration_date - time());

$years = floor($diff / (365*60*60*24));
$months = floor(($diff - $years * 365*60*60*24) / (30*60*60*24));
$days = floor(($diff - $years * 365*60*60*24 - $months*30*60*60*24)/ (60*60*24));

printf("Policy %d years, %d months, %d days about to expire\n", $years, $months, $days);
			}
	}
	/**
 * Prints the box content.
 * 
 * @param WP_Post $post The object for the current post/page.
 */
function pvplugin_meta_box_callback( $post ) {
	global $wpdb;
	global $user_ID;
	
	// Add a nonce field so we can check for it later.
	wp_nonce_field( 'pvplugin_save_meta_box_data', 'pvplugin_meta_box_nonce' );

	/*
	 * Use get_post_meta() to retrieve an existing value
	 * from the database and use the value for the form.
	 */
	$approvers = get_post_meta( $post->ID, 'approvers', true );
	
	$next_update = get_post_meta( $post->ID, 'next_update', true );
	
	$next_update_month = get_post_meta( $post->ID, 'next_update_month', true );
	
	$next_update_year = get_post_meta( $post->ID, 'next_update_year', true );
	
	$division_id = get_post_meta( $post->ID, 'division', true );
	
	$date_adopted = get_post_meta( $post->ID, 'date_adopted', true );
	
	$date_adopted_month = get_post_meta( $post->ID, 'date_adopted_month', true );
	
	$date_adopted_year = get_post_meta( $post->ID, 'date_adopted_year', true );
	
	$additional_refrence = get_post_meta( $post->ID, 'additional_refrence', true );
	
	
		
	
	
	
	if ( current_user_can( 'manage_options' ) ) {
		
		
	$responsible_office_id  = get_post_meta( $post->ID, 'responsible_office', true );
	$responsible_offices_data = $wpdb->get_results("select * from ".$wpdb->prefix."responsible_offices");
	$divisions_data = $wpdb->get_results("select * from ".$wpdb->prefix."divisions");
	
	
	}
	else {
	$division_id  = esc_attr( get_the_author_meta( 'division_id', $user_ID) );
	$responsible_offices_data = $wpdb->get_results("select * from ".$wpdb->prefix."responsible_offices where division_id = '".$division_id."'");
	$divisions_data = $wpdb->get_row("select * from ".$wpdb->prefix."divisions where id = '".$division_id."'");
	$responsible_office_id = get_post_meta( $post->ID, 'responsible_office', true );
	}
	
	
?>
	<div class="meta_container"><label for="pvplugin_new_field"><?php echo _e( 'Approvers', 'pvplugin_textdomain' ); ?></label>
    <div class="field"><input type="text" id="approvers" name="approvers" value="<?php echo esc_attr( $approvers ); ?>" size="25" /></div></div>
    <div class="meta_container"><label for="pvplugin_new_field"><?php echo _e( 'Authorize Release', 'pvplugin_textdomain' ); ?></label>
    <div class="field">
    <select name="division" id="division_id_change" onchange="get_responsible_office(this.value);">
    <?php
	
	if ( current_user_can( 'manage_options' ) ) {
			foreach($divisions_data as $division) { ?>
	
     <option value="<?php echo $division->id; ?>" <?php if($division->id == $division_id) { echo 'selected'; } ?>><?php echo $division->name; ?></option>
            <?php } ?>
		
        
        <?php
	}
	else {
		?>
		<option value="<?php echo $division_id; ?>"><?php echo $divisions_data->name; ?></option>

            
      <?php } ?>
    </select>
    </div>
    </div>
    <div class="meta_container"><label for="pvplugin_new_field"><?php echo _e( 'Responsible Office', 'pvplugin_textdomain' ); ?></label>
    <div class="field">
    <select name="responsible_office" id="responsible_office_user">
           	<?php 
			
			foreach($responsible_offices_data as $responsible_office) { ?>
            <option value="<?php echo $responsible_office->id; ?>" <?php if($responsible_office->id == $responsible_office_id) { echo "selected"; } ?>><?php echo $responsible_office->name; ?></option>
            <?php } ?>
            </select>
    </div>
    </div>
    <div class="meta_container"><label for="pvplugin_new_field"><?php echo _e( 'Next Update', 'pvplugin_textdomain' ); ?></label>
    <div class="field">
    <select name="next_update_month" id="next_update_month">
    <?php
	for ($m=1; $m<=12; $m++) {
     $month = date('F', mktime(0,0,0,$m, 1, date('Y')));
	 ?>
    <option value="<?php echo $month; ?>" <?php if($month == $next_update_month) { echo 'selected'; }?>><?php echo $month; ?></option>
    <?php } ?>
    </select>
    <select name="next_update_year" id="next_update_year">
    <?php $currentYear = date('Y');
        foreach (range($currentYear, 2025) as $value) {
			?>
    <option value="<?php echo $value; ?>" <?php if($value == $next_update_year) { echo 'selected'; }?>><?php echo $value; ?></option>
    <?php } ?>
    </select></div>
    </div>
    <div class="meta_container"><label for="pvplugin_new_field"><?php echo _e( 'Date Adopted', 'pvplugin_textdomain' ); ?></label>
    <div class="field">
    <select name="date_adopted_month" id="date_adopted_month">
    <?php
	for ($m=1; $m<=12; $m++) {
     $month = date('F', mktime(0,0,0,$m, 1, date('Y')));
	 ?>
    <option value="<?php echo $month; ?>" <?php if($month == $date_adopted_month) { echo 'selected'; }?>><?php echo $month; ?></option>
    <?php } ?>
    </select>
    <select name="date_adopted_year" id="date_adopted_year">
    <?php $currentYear = date('Y');
        foreach (range($currentYear, 2025) as $value) {
			?>
    <option value="<?php echo $value; ?>" <?php if($value == $date_adopted_year) { echo 'selected'; }?>><?php echo $value; ?></option>
    <?php } ?>
    </select></div>
    </div>
    <div class="meta_container"><label for="pvplugin_new_field"><?php echo _e( 'Additional References & Related Policies', 'pvplugin_textdomain' ); ?></label>
    <div class="field"><textarea name="additional_refrence" style="width:100%;" rows="5"><?php echo  esc_attr( $additional_refrence ); ?></textarea></div></div>
    <?php

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
	

	$next_update = $_POST['next_update_month'].'/'.$_POST['next_update_year'];
	$date_adopted = $_POST['date_adopted_month'].'/'.$_POST['date_adopted_year'];

	// Sanitize user input.
	$approvers = sanitize_text_field( $_POST['approvers'] );
	$next_update = sanitize_text_field( $next_update );
	$division = sanitize_text_field( $_POST['division'] );
	$responsible_office = sanitize_text_field( $_POST['responsible_office'] );
	$date_adopted = sanitize_text_field( $_POST['date_adopted'] );
	$additional_refrence = sanitize_text_field( $_POST['additional_refrence'] );

	// Update the meta field in the database.
	update_post_meta( $post_id, 'approvers', $approvers );
	update_post_meta( $post_id, 'next_update', $next_update );
	update_post_meta( $post_id, 'next_update_month', $_POST['next_update_month'] );
	update_post_meta( $post_id, 'next_update_year', $_POST['next_update_year'] );
	update_post_meta( $post_id, 'division', $division );
	update_post_meta( $post_id, 'responsible_office', $responsible_office );
	update_post_meta( $post_id, 'date_adopted', $date_adopted );
	update_post_meta( $post_id, 'date_adopted_month', $_POST['date_adopted_month'] );
	update_post_meta( $post_id, 'date_adopted_year', $_POST['date_adopted_year'] );
	update_post_meta( $post_id, 'additional_refrence', $additional_refrence );
	update_post_meta( $post_id, '_expiration-date', strtotime('+ 1 year') );
	
}

/**
 * First create the dropdown
 * make sure to change POST_TYPE to the name of your custom post type
 * 
 * @author Ohad Raz
 * 
 * @return void
 */
function wpse45436_admin_posts_filter_restrict_manage_posts(){
	global $wpdb;
    $type = 'policy';
    if (isset($_GET['post_type'])) {
        $type = $_GET['post_type'];
    }

    //only add filter to post type you want
    if ('policy' == $type){
        //change this to the list of values you want to show
        //in 'label' => 'value' format
		$values = array();
		$responsible_offices_data = $wpdb->get_results("select * from ".$wpdb->prefix."responsible_offices");
		foreach($responsible_offices_data as $responsible_office) { 
		
			$values[$responsible_office->name] = $responsible_office->id;
		}
      /*  $values = array(
            'Finance Office' => '4', 
            'Police Department' => '5'
        );*/
        ?>
        <select name="ADMIN_FILTER_FIELD_VALUE">
        <option value=""><?php _e('Filter By ', 'wose45436'); ?></option>
        <?php
            $current_v = isset($_GET['ADMIN_FILTER_FIELD_VALUE'])? $_GET['ADMIN_FILTER_FIELD_VALUE']:'';
            foreach ($values as $label => $value) {
                printf
                    (
                        '<option value="%s"%s>%s</option>',
                        $value,
                        $value == $current_v? ' selected="selected"':'',
                        $label
                    );
                }
        ?>
        </select>
        <?php
    }
}

/**
 * if submitted filter by post meta
 * 
 * make sure to change META_KEY to the actual meta key
 * and POST_TYPE to the name of your custom post type
 * @author Ohad Raz
 * @param  (wp_query object) $query
 * 
 * @return Void
 */
function wpse45436_posts_filter( $query ){
    global $pagenow;
    $type = 'policy';
    if (isset($_GET['post_type'])) {
        $type = $_GET['post_type'];
    }
    if ( 'policy' == $type && is_admin() && $pagenow=='edit.php' && isset($_GET['ADMIN_FILTER_FIELD_VALUE']) && $_GET['ADMIN_FILTER_FIELD_VALUE'] != '') {
        $query->query_vars['meta_key'] = 'responsible_office';
        $query->query_vars['meta_value'] = $_GET['ADMIN_FILTER_FIELD_VALUE'];
    }
}

}
function plp_plugins_loaded_register(){
    new CreateCustomPostType;
}
add_action( 'plugins_loaded', 'plp_plugins_loaded_register' );


