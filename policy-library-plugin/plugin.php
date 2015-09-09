<?php

/**
 * Plugin Name: Policy Library Plugin
 * Plugin URI: http://webcrazy.in
 * Description: Creates a Policy Library Plugin.
 * Version: 1.1.1
 * Author: Jay Thakur
 * Author URI: http://webcrazy.in
 * License: GPL V2 or Later
 */
global $wpdb;
define( 'Policy_Library_Plugin', '1.1.1' );
global $policy_library_plugin_db_version;
$policy_library_plugin_db_version = '1.1'; // version changed from 1.0 to 1.1
include dirname(__FILE__) . '/post-expirator.php';

function plp_enqueue_scripts(){

	wp_enqueue_style( 'jquery-ui-custom', plugin_dir_url( __FILE__ ) . "assets/jquery-ui.css" );
	
    wp_enqueue_style( 'style-name',plugins_url('assets/css/style.css',__FILE__ ) );
	wp_enqueue_script( 'script-name',plugins_url('assets/js/script.js',__FILE__ ) );
	wp_register_script( 'pv-jquery', 'http://ajax.googleapis.com/ajax/libs/jquery/1.7.2/jquery.min.js');
	/*wp_enqueue_script('jquery-ui-datepicker');
	wp_enqueue_style('jquery-style', 'http://ajax.googleapis.com/ajax/libs/jqueryui/1.8.2/themes/smoothness/jquery-ui.css');*/
    wp_enqueue_script( 'pv-jquery' );
}
add_action( 'admin_init', 'plp_enqueue_scripts');

/**
 * create custom post type
 */
require_once 'controllers/createCstPst.php';
require_once 'controllers/user.php';
require_once 'controllers/post.php';


function manage_division() {

    global $wpdb;
	require_once 'controllers/manage-division.php';
    $table = new Division();
    $table->prepare_items();

    $message = '';
    if ('delete' === $table->current_action()) {
        $message = '<div class="updated below-h2" id="message"><p>' . sprintf(__('Items deleted: %d', 'policy_library_plugin'), count($_REQUEST['id'])) . '</p></div>';
    }
    ?>
<div class="wrap">

    <div class="icon32 icon32-posts-post" id="icon-edit"><br></div>
    <h2><?php _e('Divisions', 'policy_library_plugin')?> <a class="add-new-h2"
                                 href="<?php echo get_admin_url(get_current_blog_id(), 'admin.php?page=divisions_form');?>"><?php _e('Add new', 'policy_library_plugin')?></a>
    </h2>
    <?php echo $message; ?>

    <form id="divisions-table" method="GET">
        <input type="hidden" name="page" value="<?php echo $_REQUEST['page'] ?>"/>
        <?php $table->display() ?>
    </form>

</div>
<?php
	
}

function manage_responsible_offices() {
	
	

    global $wpdb;
	require_once 'controllers/manage-responsible-offices.php';
    $table = new Responsible_offices();
    $table->prepare_items();

    $message = '';
    if ('delete' === $table->current_action()) {
        $message = '<div class="updated below-h2" id="message"><p>' . sprintf(__('Items deleted: %d', 'policy_library_plugin'), count($_REQUEST['id'])) . '</p></div>';
    }
    ?>
<div class="wrap">

    <div class="icon32 icon32-posts-post" id="icon-edit"><br></div>
    <h2><?php _e('Responsible offices', 'policy_library_plugin')?> <a class="add-new-h2"
                                 href="<?php echo get_admin_url(get_current_blog_id(), 'admin.php?page=responsible_offices_form');?>"><?php _e('Add new', 'policy_library_plugin')?></a>
    </h2>
    <?php echo $message; ?>

    <form id="divisions-table" method="GET">
        <input type="hidden" name="page" value="<?php echo $_REQUEST['page'] ?>"/>
        <?php $table->display() ?>
    </form>

</div>
<?php
	

}

function manage_division_form() {
	 global $wpdb;
	 require_once 'controllers/manage-division-form.php';
	}
	
function manage_responsible_offices_form() {
	 global $wpdb;
	 require_once 'controllers/manage-responsible-offices-form.php';
	}

function plp_add_to_menu() {
	
	add_menu_page(__('Policy Library', 'policy-library'), __('Policy Library', 'policy-library'), 'activate_plugins', 'library', 'manage_division');
    add_submenu_page('library', __('Divisions', 'policy-library'), __('Divisions', 'policy-library'), 'activate_plugins', 'library', 'manage_division');
    add_submenu_page('library', __('Add new', 'policy-library'), __('Add new', 'policy-library'), 'activate_plugins', 'divisions_form', 'manage_division_form');
	add_submenu_page('library', __('Responsible Offices', 'policy-library'), __('Responsible Offices', 'policy-library'), 'activate_plugins', 'responsible_offices', 'manage_responsible_offices');
    add_submenu_page('library', __('Add new', 'policy-library'), __('Add new', 'policy-library'), 'activate_plugins', 'responsible_offices_form', 'manage_responsible_offices_form');
	add_submenu_page('library',__('Post Expirator Options','post-expirator'),__('Post Expirator','post-expirator'),'activate_plugins','policy-expiration','postExpiratorMenu');
	
}


add_action('admin_menu', 'plp_add_to_menu');




/**
 * register_activation_hook implementation
 *
 * will be called when user activates plugin first time
 * must create needed database tables
 */
function table_example_install()
{
    global $wpdb;
    global $policy_library_plugin_db_version;

    $table_name = $wpdb->prefix . 'divisions'; // do not forget about tables prefix
	$table_offices = $wpdb->prefix . 'responsible_offices'; // do not forget about tables prefix

    // sql to create your table
    // NOTICE that:
    // 1. each field MUST be in separate line
    // 2. There must be two spaces between PRIMARY KEY and its name
    //    Like this: PRIMARY KEY[space][space](id)
    // otherwise dbDelta will not work
    $sql = "CREATE TABLE " . $table_name . " (
      id int(11) NOT NULL AUTO_INCREMENT,
      name tinytext NOT NULL,
      PRIMARY KEY  (id)
    );";
	
	$sql_offices = "CREATE TABLE " . $table_offices . " (
      id int(11) NOT NULL AUTO_INCREMENT,
	  division_id int(11) NOT NULL,
      name tinytext NOT NULL,
      PRIMARY KEY  (id)
    );";

    // we do not execute sql directly
    // we are calling dbDelta which cant migrate database
    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
	dbDelta($sql_offices);

    // save current database version for later use (on upgrade)
    add_option('policy_library_plugin_dv_version', $policy_library_plugin_db_version);

    /**
     * [OPTIONAL] Example of updating to 1.1 version
     *
     * If you develop new version of plugin
     * just increment $policy_library_plugin_db_version variable
     * and add following block of code
     *
     * must be repeated for each new version
     * in version 1.1 we change email field
     * to contain 200 chars rather 100 in version 1.0
     * and again we are not executing sql
     * we are using dbDelta to migrate table changes
     */
    $installed_ver = get_option('policy_library_plugin_db_version');
    if ($installed_ver != $policy_library_plugin_db_version) {
        $sql = "CREATE TABLE " . $table_name . " (
          id int(11) NOT NULL AUTO_INCREMENT,
          name tinytext NOT NULL,
          PRIMARY KEY  (id)
        );";
		
		$sql_offices = "CREATE TABLE " . $table_offices . " (
      id int(11) NOT NULL AUTO_INCREMENT,
	  division_id int(11) NOT NULL,
      name tinytext NOT NULL,
      PRIMARY KEY  (id)
    );";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
		dbDelta($sql_offices);

        // notice that we are updating option, rather than adding it
        update_option('policy_library_plugin_db_version', $policy_library_plugin_db_version);
    }
}

register_activation_hook(__FILE__, 'table_example_install');

/**
 * register_activation_hook implementation
 *
 * [OPTIONAL]
 * additional implementation of register_activation_hook
 * to insert some dummy data
 */
function table_install_data()
{
    global $wpdb;

    $table_name = $wpdb->prefix . 'divisions'; // do not forget about tables prefix

    $wpdb->insert($table_name, array(
        'name' => 'President’s Office'
    ));
    $wpdb->insert($table_name, array(
        'name' => 'Administration and Finance'
    ));
	$wpdb->insert($table_name, array(
        'name' => 'Provost’s Office'
    ));
	$wpdb->insert($table_name, array(
        'name' => 'Student Development'
    ));
	$wpdb->insert($table_name, array(
        'name' => 'Information Technology and Library Services'
    ));
	$wpdb->insert($table_name, array(
        'name' => 'University Advancement'
    ));
	$wpdb->insert($table_name, array(
        'name' => 'Mission'
    ));
	$wpdb->insert($table_name, array(
        'name' => 'Enrollment Management'
    ));
}

register_activation_hook(__FILE__, 'table_install_data');

/**
 * Trick to update plugin database, see docs
 */
function policy_library_plugin_update_db_check()
{
    global $policy_library_plugin_db_version;
    if (get_site_option('policy_library_plugin_db_version') != $policy_library_plugin_db_version) {
        table_install_data();
    }
}

add_action('plugins_loaded', 'policy_library_plugin_update_db_check');



add_action('admin_footer-post.php', 'jc_append_post_status_list');
function jc_append_post_status_list(){
	global $post;
 		$post_type = get_current_post_type();
		$complete = '';
     	$label = '';
		if($post_type == 'policy') {
			if($post->post_status == 'approval'){
               $complete = ' selected="selected"';
               $label = '<span id="post-status-display"> Approval</span>';
          }
         ?>
          <script>
          jQuery(document).ready(function($){
               $("select#post_status").append('<option value="approval" <?php echo $complete; ?>>Submit for approval</option>');
               $(".misc-pub-section label").append('<?php echo $label; ?>');
			   
          });
          </script>
         <?php
		}
    
}









