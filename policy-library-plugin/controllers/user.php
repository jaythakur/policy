<?php

/**
 * This is file is responsible for custom logic needed by all templates. NO
 * admin code should be placed in this file.
 */
Class User {

    /**
     * Run the following methods when this class is loaded
     */
    public function __construct(){
        add_action( 'init', array( &$this, 'init' ) );
		add_action('user_register', array( &$this, 'save_custom_plp_user_profile_fields' ));
		add_action( 'wp_ajax_responsible_office_ajax_request',  array( &$this, 'responsible_office_ajax_request' ) );
		wp_enqueue_script( 'script-user',plugins_url('../assets/js/user_plp.js',__FILE__ ) );
		if ( !current_user_can( 'manage_options' ) ) {
			add_action( 'admin_menu',  array( &$this, 'remove_admin_menus' ) );
			add_action( 'admin_menu',  array( &$this, 'remove_admin_submenus' ) );
		}
		
    }


    /**
     * During WordPress' init load various methods.
     */
    public function init(){
		$user_ID = get_current_user_id();
		$user = new WP_User( $user_ID );
		
		if ( !empty( $user->roles ) && is_array( $user->roles ) ) {
		foreach ( $user->roles as $role )
			$role = $role;
		}
		if($role == 'policy_editor') {
			
		if ( current_user_can( 'read_own_post' ) ) {
			
			add_action( 'pre_get_posts', array( &$this, 'read_own_post_function' ) );
		}
		$role  = get_role('policy_editor');
		$role->add_cap( 'read_own_post' );
		$role->add_cap( 'edit_posts' );
		$role->add_cap( 'edit_private_posts' );
		$role->add_cap( 'edit_published_posts' );
		$role->add_cap( 'delete_posts' );
		$role->add_cap( 'delete_private_posts' );
		$role->add_cap( 'manage_categories' );
		add_action( 'current_screen',array( &$this, 'thisScreen' ));
		}
		else if($role == 'policy_publisher') {
					add_action( 'current_screen',array( &$this, 'thisScreen' ));
			if ( current_user_can( 'read_group_posts' ) ) {
			
						add_action( 'pre_get_posts', array( &$this, 'read_group_posts_function' ) );
			}
			$role1  = get_role('policy_publisher');
			$role1->add_cap( 'read_group_posts' );
			$role1->add_cap( 'read' );
			$role1->add_cap( 'delete_others_posts' );
			$role1->add_cap( 'delete_posts' );
			$role1->add_cap( 'delete_private_posts' );
			$role1->add_cap( 'delete_published_posts' );
			$role1->add_cap( 'edit_others_posts' );
			$role1->add_cap( 'edit_posts' );
			$role1->add_cap( 'edit_private_posts' );
			$role1->add_cap( 'edit_published_posts' );
			$role1->add_cap( 'publish_posts' );
			$role1->add_cap( 'manage_categories' );
			
		
		}
		
		add_action( 'user_new_form',array( &$this, 'plp_add_custom_user_profile_fields' ));
		add_action( 'show_user_profile',array( &$this, 'plp_edit_custom_user_profile_fields' ));
		add_action( 'edit_user_profile',array( &$this, 'plp_edit_custom_user_profile_fields' ));
		add_action( 'personal_options_update', array( &$this, 'plp_save_custom_user_profile_fields' ));
		add_action( 'edit_user_profile_update', array( &$this, 'plp_save_custom_user_profile_fields' ));
		add_role('policy_editor', __( 'Policy Editor' ), array('read' => true, 'edit_posts'   => true, 'delete_posts' => true));
		add_role('policy_publisher', __( 'Policy Publisher' ), array('read' => true, 'edit_posts'   => true, 'delete_posts' => true));
		
		foreach( array( 'edit-post','edit-policy') as $hook )
    		add_filter( "views_".$hook , array( &$this, 'wpse_30331_custom_view_count' ), 10, 1);
			add_filter( "views_".$hook , array( &$this, 'modified_views_so_15799171' ), 10, 1);
		
		}
		
	function plp_add_custom_user_profile_fields( $user ) {
	global $wpdb;
	$divisions_data = $wpdb->get_results("select * from ".$wpdb->prefix."divisions");
	$responsible_offices_data = $wpdb->get_results("select * from ".$wpdb->prefix."responsible_offices");
	
?>
	<h3><?php _e('Group Information', 'policy_library_plugin'); ?></h3>
	
	<table class="form-table">
		<tr>
			<th>
				<label for="division"><?php _e('Divisions', 'policy_library_plugin'); ?>
			</label></th>
			<td>
				<select name="division_id" id="division_id_change" onchange="get_responsible_office(this.value);">
                <option value="">None</option>
           	<?php foreach($divisions_data as $division) { ?>
            <option value="<?php echo $division->id; ?>"><?php echo $division->name; ?></option>
            <?php } ?>
            </select><br />
				<span class="description"><?php _e('Please select divisions.', 'policy_library_plugin'); ?></span>
			</td>
		</tr>
        <tr>
			<th>
				<label for="responsible_office"><?php _e('Responsible Office', 'policy_library_plugin'); ?>
			</label></th>
			<td>
				<select name="responsible_office" id="responsible_office_user">
           	<?php foreach($responsible_offices_data as $responsible_office) { ?>
            <option value="<?php echo $responsible_office->id; ?>"><?php echo $responsible_office->name; ?></option>
            <?php } ?>
            </select><br />
				<span class="description"><?php _e('Please select responsible office.', 'policy_library_plugin'); ?></span>
			</td>
		</tr>
	</table>
<?php }


function save_custom_plp_user_profile_fields($user_id){
    # again do this only if you can
    if(!current_user_can('manage_options'))
        return false;

    # save my custom field
    update_usermeta($user_id, 'division_id', $_POST['division_id']);
	update_usermeta($user_id, 'responsible_office', $_POST['responsible_office']);
}

function responsible_office_ajax_request() {
 	global $wpdb;
    // The $_REQUEST contains all the data sent via ajax
    if ( isset($_REQUEST) ) {
     
        $division_id = $_REQUEST['division_id'];
      	$responsible_offices_data = $wpdb->get_results("select id,name from ".$wpdb->prefix."responsible_offices where division_id = '".$division_id."'");
       echo json_encode($responsible_offices_data);
     
    }
     
    // Always die in functions echoing ajax content
   die();
}


function plp_edit_custom_user_profile_fields( $user ) {
	global $wpdb;
	$divisions_data = $wpdb->get_results("select * from ".$wpdb->prefix."divisions");
	
	$division_id  = esc_attr( get_the_author_meta( 'division_id', $user->ID ) );
	$responsible_office_id  = esc_attr( get_the_author_meta( 'responsible_office', $user->ID ) );
	$responsible_offices_data = $wpdb->get_results("select * from ".$wpdb->prefix."responsible_offices where division_id = $division_id");
	
?>
	<h3><?php _e('Group Information', 'policy_library_plugin'); ?></h3>
	
	<table class="form-table">
		<tr>
			<th>
				<label for="division"><?php _e('Divisions', 'policy_library_plugin'); ?>
			</label></th>
			<td>
				<select name="division_id" id="division_id_change" onchange="get_responsible_office(this.value);">
                <option value="">None</option>
           	<?php foreach($divisions_data as $division) { ?>
            <option value="<?php echo $division->id; ?>" <?php if($division->id == $division_id) { echo "selected"; } ?> ><?php echo $division->name; ?></option>
            <?php } ?>
            </select><br />
				<span class="description"><?php _e('Please select divisions.', 'policy_library_plugin'); ?></span>
			</td>
		</tr>
        <tr>
			<th>
				<label for="responsible_office"><?php _e('Responsible Office', 'policy_library_plugin'); ?>
			</label></th>
			<td>
				<select name="responsible_office" id="responsible_office_user">
           	<?php foreach($responsible_offices_data as $responsible_office) { ?>
            <option value="<?php echo $responsible_office->id; ?>" <?php if($responsible_office->id == $responsible_office_id) { echo "selected"; } ?>><?php echo $responsible_office->name; ?></option>
            <?php } ?>
            </select><br />
				<span class="description"><?php _e('Please select responsible office.', 'policy_library_plugin'); ?></span>
			</td>
		</tr>
	</table>
<?php }

function plp_save_custom_user_profile_fields( $user_id ) {
	
	if ( !current_user_can( 'edit_user', $user_id ) )
		return FALSE;
	
	update_usermeta($user_id, 'division_id', $_POST['division_id']);
	update_usermeta($user_id, 'responsible_office', $_POST['responsible_office']);
}


 function read_own_post_function( $query ) {
	 
	 if($query->is_admin && !current_user_can('administrator') ){
		global $user_ID;
		$query->set('author',  $user_ID);
	}
	return $query;
	
}

function read_group_posts_function($query) {
	
	if($query->is_admin && !current_user_can('administrator') ){
		global $user_ID;
		
		$division_id = get_user_meta($user_ID, 'division_id', 'true');
		$responsible_office = get_user_meta($user_ID, 'responsible_office', 'true');
		$users = new WP_User_Query(array(
	  'exclude' => array(3), 
    'meta_query' => array(
        'relation' => 'OR',
        array(
            'key' => 'division_id',
            'value' => $division_id,
            'compare' => 'LIKE'
        ),
        array(
            'key' => 'responsible_office',
            'value' => $responsible_office,
            'compare' => 'LIKE'
        )
    )
)); 

$user_query = new WP_User_Query(array('meta_query' => array(
        'relation' => 'AND',
        array(
            'key' => 'division_id',
            'value' => $division_id,
            'compare' => 'LIKE'
        ),
        array(
            'key' => 'responsible_office',
            'value' => $responsible_office,
            'compare' => 'LIKE'
        )
    )) );
foreach ( $user_query->results as $user ) {
		//echo '<p>' . $user->ID . '</p>';
		 $ids[] = $user->ID;
	}
		
		$query->set('author__in', $ids );
	}
	return $query;
}


function get_user_by_group_id() {
	global $wpdb;

		global $user_ID;
		
		$division_id = get_user_meta($user_ID, 'division_id', 'true');
		$responsible_office = get_user_meta($user_ID, 'responsible_office', 'true');
		
$user_query = new WP_User_Query(array('meta_query' => array(
        'relation' => 'AND',
        array(
            'key' => 'division_id',
            'value' => $division_id,
            'compare' => 'LIKE'
        ),
        array(
            'key' => 'responsible_office',
            'value' => $responsible_office,
            'compare' => 'LIKE'
        )
    )) );
foreach ( $user_query->results as $user ) {
		//echo '<p>' . $user->ID . '</p>';
		 $ids[] = $user->ID;
	}
		
		
	
	
	return $ids;
	
}

function wpse_30331_custom_view_count( $views ) 
{
	
    global $current_screen;
	
    switch( $current_screen->id ) 
    {
        case 'edit-post':
            $views = $this->wpse_30331_manipulate_views( 'post', $views );
            break;
        case 'edit-policy':
            $views = $this->wpse_30331_manipulate_views( 'policy', $views );
            break;
       
    }
    return $views;
}

function wpse_30331_manipulate_views( $what, $views )
{
    global $user_ID, $wpdb;

    /*
     * This is not working for me, 'artist' and 'administrator' are passing this condition (?)
     */
		
		$user = new WP_User( $user_ID );
		
		if ( !empty( $user->roles ) && is_array( $user->roles ) ) {
		foreach ( $user->roles as $role )
			$role = $role;
		}
		
    if ( $role == 'policy_editor') {
        /*
     * This needs refining, and maybe a better method
     * e.g. Attachments have completely different counts 
     */
	 
    $total = $wpdb->get_var("SELECT COUNT(*) FROM $wpdb->posts WHERE (post_status = 'publish' OR post_status = 'draft' OR post_status = 'pending') AND (post_author = '$user_ID'  AND post_type = '$what' ) ");
    $publish = $wpdb->get_var("SELECT COUNT(*) FROM $wpdb->posts WHERE post_status = 'publish' AND post_author = '$user_ID' AND post_type = '$what' ");
    $draft = $wpdb->get_var("SELECT COUNT(*) FROM $wpdb->posts WHERE post_status = 'draft' AND post_author = '$user_ID' AND post_type = '$what' ");
    $pending = $wpdb->get_var("SELECT COUNT(*) FROM $wpdb->posts WHERE post_status = 'pending' AND post_author = '$user_ID' AND post_type = '$what' ");
	$trash = $wpdb->get_var("SELECT COUNT(*) FROM $wpdb->posts WHERE post_status = 'trash' AND post_author = '$user_ID' AND post_type = '$what' ");

	}
	else if($role == 'policy_publisher') {
		/*
     * This needs refining, and maybe a better method
     * e.g. Attachments have completely different counts 
     */
	$ids = implode(',',$this->get_user_by_group_id());
	 
    $total = $wpdb->get_var("SELECT COUNT(*) FROM $wpdb->posts WHERE (post_status = 'publish' OR post_status = 'draft' OR post_status = 'pending') AND (post_author IN($ids)  AND post_type = '$what' ) ");
    $publish = $wpdb->get_var("SELECT COUNT(*) FROM $wpdb->posts WHERE post_status = 'publish' AND post_author IN($ids) AND post_type = '$what' ");
    $draft = $wpdb->get_var("SELECT COUNT(*) FROM $wpdb->posts WHERE post_status = 'draft' AND post_author IN($ids) AND post_type = '$what' ");
    $pending = $wpdb->get_var("SELECT COUNT(*) FROM $wpdb->posts WHERE post_status = 'pending' AND post_author IN($ids) AND post_type = '$what' ");
	$trash = $wpdb->get_var("SELECT COUNT(*) FROM $wpdb->posts WHERE post_status = 'trash' AND post_author IN($ids) AND post_type = '$what' ");
	}
	else {
		return $views;
	}
    

    /*
     * Only tested with Posts/Pages
     * - there are moments where Draft and Pending shouldn't return any value
     */
	 
    $views['all'] = preg_replace( '/\(.+\)/U', '('.$total.')', $views['all'] );
	$views['publish'] = preg_replace( '/\(.+\)/U', '('.$publish.')', $views['publish'] );
    $views['draft'] = preg_replace( '/\(.+\)/U', '('.$draft.')', $views['draft'] );
    $views['pending'] = preg_replace( '/\(.+\)/U', '('.$pending.')', $views['pending'] );
	$views['trash'] = preg_replace( '/\(.+\)/U', '('.$trash.')', $views['trash'] );
    // Debug info
    //echo 'Default counts: <pre>'.print_r($views,true).'</pre>';
    //echo '<hr><hr>';
    //echo 'Query for this screen of this post_type: <b>'.$what.'</b><pre>'.print_r($wp_query,true).'</pre>';

    return $views;
}

function modified_views_so_15799171( $views ) 
{
	
  

 /*   if( isset( $views['publish'] ) )
        $views['publish'] = str_replace( 'Published ', 'Online ', $views['publish'] );*/

    
        $views['pending'] = str_replace( 'Pending ', 'Pending Approval', $views['pending'] );

   /* if( isset( $views['draft'] ) )
        $views['draft'] = str_replace( 'Drafts ', 'In progress ', $views['draft'] );

    if( isset( $views['trash'] ) )
        $views['trash'] = str_replace( 'Trash ', 'Dustbin ', $views['trash'] );
*/
    return $views;
}

//Remove top level admin menus
function remove_admin_menus() {
    remove_menu_page( 'edit-comments.php' );
    remove_menu_page( 'link-manager.php' );
    remove_menu_page( 'tools.php' );
    remove_menu_page( 'plugins.php' );
    remove_menu_page( 'users.php' );
    remove_menu_page( 'options-general.php' );
    remove_menu_page( 'upload.php' );
    remove_menu_page( 'edit.php' );
	remove_menu_page( 'edit.php?post_type=page' );
	remove_menu_page( 'edit.php?post_type=calender' );
	remove_menu_page( 'edit.php?post_type=spotlight' );
	remove_menu_page( 'edit.php?post_type=inthenews' );
	remove_menu_page( 'edit.php?post_type=pridepoints' );
	remove_menu_page( 'edit.php?post_type=adbanner' );
	remove_menu_page( 'edit.php?post_type=snippets' );
    remove_menu_page( 'edit.php?post_type=page' );
    remove_menu_page( 'themes.php' );
}


//Remove sub level admin menus
function remove_admin_submenus() {
    remove_submenu_page( 'themes.php', 'theme-editor.php' );
    remove_submenu_page( 'themes.php', 'themes.php' );
    remove_submenu_page( 'edit.php', 'edit-tags.php?taxonomy=post_tag' );
    remove_submenu_page( 'edit.php', 'edit-tags.php?taxonomy=category' );
    remove_submenu_page( 'edit.php', 'post-new.php' );
    remove_submenu_page( 'themes.php', 'nav-menus.php' );
    remove_submenu_page( 'themes.php', 'widgets.php' );
    remove_submenu_page( 'themes.php', 'theme-editor.php' );
    remove_submenu_page( 'plugins.php', 'plugin-editor.php' );
    remove_submenu_page( 'plugins.php', 'plugin-install.php' );
    remove_submenu_page( 'users.php', 'users.php' );
    remove_submenu_page( 'users.php', 'user-new.php' );
    remove_submenu_page( 'upload.php', 'media-new.php' );
    remove_submenu_page( 'options-general.php', 'options-writing.php' );
    remove_submenu_page( 'options-general.php', 'options-discussion.php' );
    remove_submenu_page( 'options-general.php', 'options-reading.php' );
    remove_submenu_page( 'options-general.php', 'options-discussion.php' );
    remove_submenu_page( 'options-general.php', 'options-media.php' );
    remove_submenu_page( 'options-general.php', 'options-privacy.php' );
    remove_submenu_page( 'options-general.php', 'options-permalinks.php' );
    remove_submenu_page( 'index.php', 'update-core.php' );
}



function thisScreen() {

    $currentScreen = get_current_screen();
	
	if($currentScreen->id == 'edit-policy-types' || $currentScreen->id == 'edit-policy_tag')
   	add_action( 'admin_head',array( &$this, 'my_custom_admin_head' ));

}

function my_custom_admin_head() {
	echo '<style>.col-wrap .form-wrap { display:none; }.row-actions { display:none; }.tablenav .actions { display:none; }#edittag input[type="submit"] { display:none; }</style>';
}

}
function plp_plugins_loaded_user_register(){
    new User;
}
add_action( 'plugins_loaded', 'plp_plugins_loaded_user_register' );