<?php 
/*
Plugin Name:  Custom Order Export
Plugin URI:   https://exclutips.com
Description:  Order Export to Excel
Version:      1.0.1
Author:       Rupom	Khondaker
Author URI:   #
License:      GPL2
License URI:  https://www.gnu.org/licenses/gpl-2.0.html
Text Domain:  exclutips-order-export
Domain Path:  /languages
*/
if ( !function_exists( 'add_action' ) ) {
	echo 'Hi there!  I\'m just a plugin, not much I can do when called directly.';
	exit;
}


//adding scripts
function vpm_anti_fraud_enqueue_scripts(){
	//enqueue Styles 
	wp_enqueue_style( 'anti-fraud-css', plugins_url('assets/css/anti-fraud.css', __FILE__ ), array(), '1.1', 'all');
	wp_enqueue_style( 'tag-input', plugins_url('assets/css/tags-input.css', __FILE__ ), array(), '1.1', 'all');

	//enqueue Scripts 
	wp_enqueue_script( 'tag-input', plugins_url('assets/js/tags-input.js', __FILE__ ), array(), false, true );
}
add_action('admin_enqueue_scripts', 'vpm_anti_fraud_enqueue_scripts');


// Check that the class exists before trying to use it
if (!class_exists('EXCLUTIPS_Settings_Page')) {
    include_once('exclutips-option.php');
}

include_once('inc/vpm-anti-fraud-settings.php');
include_once('inc/vpm-anti-fraud-helper.php');
include_once('inc/vpm-fraud-limit-block.php');
include_once('inc/vpm-anti-fraud-meta.php');
//include 'inc/vpm-anti-fraud-limit-attempts.php';
