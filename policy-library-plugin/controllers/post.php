<?php

/**
 * This is file is responsible for custom logic needed by all templates. NO
 * admin code should be placed in this file.
 */
Class Post {

    /**
     * Run the following methods when this class is loaded
     */
    public function __construct(){
        add_action( 'init', array( &$this, 'init' ) );
		add_filter( 'display_post_states', array( &$this, 'plp_display_archive_state' ) );
		/*if(!is_admin()) {*/
		
		add_action('save_post', array( &$this, 'save_post_callback' ));
		/*add_filter( 'gettext', array( &$this, 'change_publish_button' ), 10, 2 );*/
		add_action( 'admin_footer-post.php', array( &$this, 'plp_append_post_status_list' ) );
		add_action('admin_footer-post-new.php',  array( &$this, 'plp_new_post_status_list' ));
		/*}*/
    }


    /**
     * During WordPress' init load various methods.
     */
    public function init(){
		
		 register_post_status( 'approval', array(
          'label'                     => _x( 'Approval', 'post' ),
          'public'                    => true,
          'show_in_admin_all_list'    => false,
          'show_in_admin_status_list' => true,
          'label_count'               => _n_noop( 'Approval <span class="count">(%s)</span>', 'Approval <span class="count">(%s)</span>' )
     ) );
	
		
		}

 function plp_append_post_status_list(){
	global $post;
 		$post_type = $this->get_current_post_type();
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
 
 
 function plp_new_post_status_list(){
	global $post;
 		$post_type = get_current_post_type();
		if($post_type == 'policy') {
         ?>
          <script>
          jQuery(document).ready(function($){
			  
               jQuery("select#post_status").append('<option value="approval">Submit for approval</option>');
               
          });
          </script>
         <?php
		}
	
    
}

	
		function plp_display_archive_state() {
			
     global $post;
     $arg = get_query_var( 'post_status' );
     if($arg != 'pending'){
          if($post->post_status == 'pending'){
               return array('Pending');
          }
     }
    return $states;
	
		}
		
function get_current_post_type() {
  global $post, $typenow, $current_screen;
	
  //we have a post so we can just get the post type from that
  if ( $post && $post->post_type )
    return $post->post_type;
    
  //check the global $typenow - set in admin.php
  elseif( $typenow )
    return $typenow;
    
  //check the global $current_screen object - set in sceen.php
  elseif( $current_screen && $current_screen->post_type )
    return $current_screen->post_type;
  
  //lastly check the post_type querystring
  elseif( isset( $_REQUEST['post_type'] ) )
    return sanitize_key( $_REQUEST['post_type'] );
	
  //we do not know the post type!
  return null;
}


function save_post_callback($post_id){
	global $wpdb;
    global $post;
	global $user_ID;

	
			$post_data = get_page($post_ID);
		
			// If post is saved via autosave, post is a revision or if it is not in the designated list of post status types, stop running.
			if ($post_data->post_type=="policy") {
			
				$current_user = wp_get_current_user();
				$post_author_email = $current_user->user_email;
	$user = new WP_User( $user_ID );
	if ( !empty( $user->roles ) && is_array( $user->roles ) ) {
		foreach ( $user->roles as $role )
			$role = $role;
		}
	if ( $role == 'policy_editor') {
			$division_id = get_user_meta($user_ID, 'division_id', 'true');
			$responsible_office = get_user_meta($user_ID, 'responsible_office', 'true');
		
			$user_query = new WP_User_Query( array( 'role' => 'policy_publisher','meta_query' => array(
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
			) ) );
			foreach ( $user_query->results as $user ) {
			
				$user = get_userdata( $user->ID );
				$to = $user->user_email;
				
				// Get the needed information
			$post_taxonomies = get_the_taxonomies($post_ID);
			$post_author_email = get_the_author_meta('user_email', $post_data->post_author);
		
			$post_meta = get_post_meta($post_ID);

			// Clean up the taxonomies print out for the email message
			if (isset($post_taxonomies['post_tag'])) $post_taxonomies['post_tag'] = str_replace('Tags: ', '', $post_taxonomies['post_tag']);
			else $post_taxonomies['post_tag'] = '';
			
			if (isset($post_taxonomies['category'])) $post_taxonomies['category'] = str_replace('Categories: ', '', $post_taxonomies['category']);
			else $post_taxonomies['category'] = '';
			
			// Remove hidden keys from post meta and then print out
			/*foreach($post_meta as $key => $value) {
				if(strpos($key, '_')=="0")
					unset($post_meta[$key]);
			}*/
			
			$message = '';
			
			// Generate email message			
		
				$message .= '<b>Title:</b> '. $post_data->post_title .'<br />';
				$message .= '<b>Author:</b> '. get_the_author_meta('display_name', $post_data->post_author) .'<br />';
				$message .= '<b>Post Date:</b> '. $post_data->post_date .'<br />';
				$message .= '<b>Categories:</b> '.$post_taxonomies['category'] .'<br />';
				$message .= '<b>Tags:</b> '. $post_taxonomies['post_tag'] .'<br />';
				$message .= '<br /><b>Post Meta: </b><br />';
				if(count($post_meta)>0) {
				foreach($post_meta as $key => $value) {
					$message .= $key .': '. $value[0] .'<br />';	
				}
				}
				$message .= '<br /><b>Post Body:</b><br />'. str_replace('<!--more-->', '&lt;!--more--&gt;', $post_data->post_content) .'<br />';
		
			
			$message .= '<br />----------------------------------------------------<br />';
			$message .= '';
			
			echo $message;
			// Change From to site's name & admin email, author's email as the Reply-To email, set HTML header and send email.
			$headers[] = 'From: "'.get_bloginfo('name').'" <'. get_bloginfo('admin_email') .'>';
			$headers[] = 'Reply-To: '. $post_author_email;
			
			add_filter('wp_mail_content_type', create_function('', 'return "text/html";'));
			wp_mail($to, 'Post Needing Approval: '. $post_data->post_title, $message, $headers);
			
			}	
			
			
	}
	
			
			}
		 
/*    if ($post->post_type == 'policy'){
		 if($_POST['post_status'] == 'publish') {
	  		$wpdb->update( $wpdb->posts, array( 'post_status' => 'approval' ), array( 'ID' => $post->ID ) );
	  }
    }
*/    //if you get here then it's your post type so do your thing....
}


function change_publish_button( $translation, $text ) {
	
if ( 'policy' == get_post_type())
if ( $text == 'Publish' )
    return 'Submit for Approval';

return $translation;
}
}
function plp_plugins_loaded_user_post(){
    new Post;
}
add_action( 'plugins_loaded', 'plp_plugins_loaded_user_post' );