<?php
// Plugin Settings Page
class EXCLUTIPS_Settings_Page {
    /**
     * Holds the values to be used in the fields callbacks
     */
    private $options;
    
    /**
     * Start up
     */
    public function __construct() {
        add_action('admin_menu', array($this, 'exclutips_option_menu'));
	}

 	public	function exclutips_option_menu() {
		//create new top-level menu
		 $plugin_url = plugin_dir_url( __FILE__ );
		 $exclutips_page_title = 'Exclutips Dashboard';
		 $exclutips_menu_title = 'Exclutips Settings';
		 $exclutips_capability =  'administrator';
		 $exclutips_menu_slug = 'exclutips-settings';
		 $exclutips_function = array ($this,'exclutips_create_main_page');
		 $exclutips_menu_icon = $plugin_url.'assets/images/menu-icon.png';
		 $exclutips_position = 7;
		 add_menu_page( $exclutips_page_title, $exclutips_menu_title, $exclutips_capability, $exclutips_menu_slug, $exclutips_function, $exclutips_menu_icon, $exclutips_position );
	}
     /**
     * Options Dashboard callback
  	 */
	 public function exclutips_create_main_page() {
       require_once dirname(__FILE__) . '/inc/dashboard.php';
  	 }

}
if (is_admin())
    $exclutips_settings_page = new EXCLUTIPS_Settings_Page();