<?php 
/*
Plugin Name:  Custom Order Export Excel
Plugin URI:   https://exclutips.com
Description:  Order Export to Excel
Version:      1.0.2
Author:       Rupom	Khondaker
Author URI:   #
License:      GPL2
License URI:  https://www.gnu.org/licenses/gpl-2.0.html
Text Domain:  coee-order-export
Domain Path:  /languages
*/


if ( !function_exists( 'add_action' ) ) {
	echo 'Hi there!  I\'m just a plugin, not much I can do when called directly.';
	exit;
}


//adding scripts
function exclutips_coee_enqueue_scripts(){
	//enqueue Styles 
	wp_enqueue_style( 'coee-css', plugins_url('assets/css/order-export.css', __FILE__ ), array(), '1.1', 'all');


	//enqueue Scripts 
	wp_enqueue_script( 'coee-csvExcel', plugins_url('assets/js/tableexport.min.js', __FILE__ ), array(), false, true );
	wp_enqueue_script( 'coee-script', plugins_url('assets/js/order-export.js', __FILE__ ), array(), false, true );
}
add_action('admin_enqueue_scripts', 'exclutips_coee_enqueue_scripts');


// Check that the class exists before trying to use it
if (!class_exists('EXCLUTIPS_Settings_Page')) {
    include_once('exclutips-option.php');
}

// Check that the class exists before trying to use it
if (!class_exists('COEEOrderExport')){
	include_once('custom-order-export-query.php');
}
