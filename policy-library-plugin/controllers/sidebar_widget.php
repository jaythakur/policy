<?php
/**
 * Adds Sidebar_Widget widget.
 */
class Sidebar_Widget extends WP_Widget {

	/**
	 * Register widget with WordPress.
	 */
	function __construct() {
		parent::__construct(
			'foo_widget', // Base ID
			__( 'Widget Title', 'text_domain' ), // Name
			array( 'description' => __( 'A Foo Widget', 'text_domain' ), ) // Args
		);
	}

	/**
	 * Front-end display of widget.
	 *
	 * @see WP_Widget::widget()
	 *
	 * @param array $args     Widget arguments.
	 * @param array $instance Saved values from database.
	 */
	public function widget( $args, $instance ) {
		global $wpdb;
		echo $args['before_widget'];
		if ( ! empty( $instance['title'] ) ) {
			echo $args['before_title'] . apply_filters( 'widget_title', $instance['title'] ). $args['after_title'];
		}
		//echo __( 'Hello, World!', 'text_domain' );
		?>
        <form role="search" method="get" class="" action="<?php echo esc_url(home_url('/')); ?>">
    <div>
    <label for="keywords">Keywords</label><br><br>
        <input type="text" onfocus="if (this.value == 'Keyword') {this.value = '';}" onblur="if (this.value == '') {this.value = 'Keyword';}"  value="Keyword" name="s" id="s" />
      
        
    </div>
    <br><br>
    <div>
    <label for="filter_office">Filter by Office</label><br><br>
    <?php $responsible_offices_data = $wpdb->get_results("select * from ".$wpdb->prefix."responsible_offices"); ?>
       <select name="ofc" id="ofc">
       <option value="all">All Offices</option>
           	<?php foreach($responsible_offices_data as $responsible_office) { ?>
            <option value="<?php echo $responsible_office->id; ?>" <?php if($responsible_office->id == $responsible_office_id) { echo "selected"; } ?>><?php echo $responsible_office->name; ?></option>
            <?php } ?>
            </select>
      
        
    </div>
    <br><br>
    <input type="submit" id="searchsubmit" value="Seacrh Polcies" />
</form>
<div class="clear"></div>
<br/>

<?php
		echo $args['after_widget'];
	}

	/**
	 * Back-end widget form.
	 *
	 * @see WP_Widget::form()
	 *
	 * @param array $instance Previously saved values from database.
	 */
	public function form( $instance ) {
		$title = ! empty( $instance['title'] ) ? $instance['title'] : __( 'New title', 'text_domain' );
		?>
		<p>
		<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title:' ); ?></label> 
		<input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>">
		</p>
		<?php 
	}

	/**
	 * Sanitize widget form values as they are saved.
	 *
	 * @see WP_Widget::update()
	 *
	 * @param array $new_instance Values just sent to be saved.
	 * @param array $old_instance Previously saved values from database.
	 *
	 * @return array Updated safe values to be saved.
	 */
	public function update( $new_instance, $old_instance ) {
		$instance = array();
		$instance['title'] = ( ! empty( $new_instance['title'] ) ) ? strip_tags( $new_instance['title'] ) : '';

		return $instance;
	}

} // class Foo_Widget


// register Foo_Widget widget
function register_foo_widget() {
    register_widget( 'Sidebar_Widget' );
}
add_action( 'widgets_init', 'register_foo_widget' );