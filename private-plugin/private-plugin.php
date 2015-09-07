<?php
/*
Plugin Name: Private Plugin
Description: Private Plugin Description
Author: Jay Thakur
Version: 1.0
Plugin URI: http://www.webcrazy.in
Author URI: http://www.webcrazy.in
Donate link: http://www.webcrazy.in
License: GPLv2 or later
License URI: http://www.webcrazy.in
*/
global $wpdb, $wp_version;

add_action( 'init', 'process_custome_post' );

function process_custome_post() {
     
	global $wpdb;
	include('pages/create-cst-pst-type.php');
	
	add_action( 'admin_init','pv_add_files');
	 
}

function pv_add_files() {

	wp_enqueue_style( 'style-name',plugins_url('pages/assets/css/style.css',__FILE__ ) );
	wp_enqueue_script( 'script-name',plugins_url('pages/assets/js/script.js',__FILE__ ) );
	wp_register_script( 'pv-jquery', 'http://ajax.googleapis.com/ajax/libs/jquery/1.7.2/jquery.min.js');
	wp_enqueue_script('jquery-ui-datepicker');
	wp_enqueue_style('jquery-style', 'http://ajax.googleapis.com/ajax/libs/jqueryui/1.8.2/themes/smoothness/jquery-ui.css');
    wp_enqueue_script( 'pv-jquery' );


}
/*
function main_page(){
	global $wpdb;
	include('pages/private-plugin.php');

}


function pV_add_to_menu() 
{

	global $pV_menu_slug;
	add_menu_page( __( 'Private Plugin', 'private-plugin' ), __( 'Private Plugin', 'private-plugin' ), 'administrator', 'private-plugin', 'main_page' );
	
}
add_action('admin_menu', 'pV_add_to_menu');*/



?>