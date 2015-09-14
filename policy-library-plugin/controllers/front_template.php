<?php

 
 function policies_func(  ) {
	global $wpdb;
	$posts_per_row = 3;
$posts_per_page = -1;
$pageURL = 'http';
$post_type = 'policy';
 
if ($_SERVER["HTTPS"] == "on") {$pageURL .= "s";}
$pageURL .= "://";
if ($_SERVER["SERVER_PORT"] != "80") {
 $pageURL .= $_SERVER["SERVER_NAME"].":".$_SERVER["SERVER_PORT"].$_SERVER["REQUEST_URI"];
} else {
 $pageURL .= $_SERVER["SERVER_NAME"].$_SERVER["REQUEST_URI"];
}
 
$letters = $wpdb->get_col(
"SELECT DISTINCT LEFT(post_title,1) AS first_letter FROM $wpdb->posts
WHERE post_type = '$post_type' AND post_status = 'publish'
ORDER BY first_letter ASC"
);
 
$first_letter = ($_GET['first_letter']) ? $_GET['first_letter'] : $letters[0];

foreach ($letters as $letter) {
               $url = add_query_arg('first_letter',$letter,$pageURL);
               echo "<a href='$url' title='Starting letter $letter' >[ $letter ]&nbsp;&nbsp;</a>";
}

 
			$options = array(
				'first_letter' => $first_letter
			);
			
			$age_filter = function ($where = '') use ( $options ) {
				$where .= " AND LEFT(wp_posts.post_title,1) = '".$options[ 'first_letter' ]."'";
				return $where;
			};
			
			add_filter('posts_where', $age_filter);



         $paged = (get_query_var('paged')) ? get_query_var('paged') : 1;
         $args = array (
            'posts_per_page' => $posts_per_page,
            'post_type' => $post_type,
			'post_status' => 'publish',
            'orderby' => 'title',
            'order' => 'ASC',
            'paged' => $paged,
            'caller_get_posts' => 1,
			'suppress_filters' => FALSE
         );
         query_posts($args);
		 
         $mam_global_where = '';  // Turn off filter
         if ( have_posts() ) {
            $in_this_row = 0;
            while ( have_posts() ) {
               the_post();
			
               $first_letter = strtoupper(substr(get_the_title(),0,1));
               if ($first_letter != $curr_letter) {
                  if (++$post_count > 1) {
                     end_prev_letter();
                  }
                  start_new_letter($first_letter);
                  $curr_letter = $first_letter;
               }
               if (++$in_this_row > $posts_per_row) {
                  end_prev_row();
                  start_new_row();
                  ++$in_this_row;  // Account for this first post
               } ?>
               <div class="title-cell"><a href="<?php the_permalink() ?>" rel="bookmark" title="Permanent Link to <?php the_title_attribute(); ?>"><?php the_title(); ?></a></div>
            <?php }
            end_prev_letter();
            ?>
            
         <?php } else {
            echo "<h2>Sorry, no posts were found!</h2>";
         }
		 
}
add_shortcode( 'policies', 'policies_func' );


         			
function end_prev_letter() {
   end_prev_row();
   echo "</div><!-- End of letter-group -->\n";
   echo "<div class='clear'></div>\n";
}
function start_new_letter($letter) {
   echo "<div class='letter-group'>\n";
   echo "\t<div class='letter-cell'>$letter</div>\n";
   start_new_row($letter);
}
function end_prev_row() {
   echo "\t</div><!-- End row-cells -->\n";
}
function start_new_row() {
   global $in_this_row;
   $in_this_row = 0;
   echo "\t<div class='row-cells'>\n";
}