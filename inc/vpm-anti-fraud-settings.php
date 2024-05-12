<?php
if ( ! defined( 'ABSPATH' ) ) {	exit;} // Exit if accessed directly

class VPMAntiFraud {
	private $vpm_anti_fraud_options;

	public function __construct() {
		add_action( 'admin_menu', array( $this, 'vpm_anti_fraud_add_plugin_page' ) );
		add_action( 'admin_init', array( $this, 'vpm_anti_fraud_page_init' ) );
	}

	public function vpm_anti_fraud_add_plugin_page() {
		add_submenu_page(
			'exclutips-settings',
			'VPM Anti Fraud', // page_title
			'VPM Anti Fraud', // menu_title
			'manage_options', // capability
			'vpm-anti-fraud', // menu_slug
			array( $this, 'vpm_anti_fraud_create_admin_page' ) // function
		);
	}
	

	public function vpm_anti_fraud_create_admin_page() {
		$this->vpm_anti_fraud_options = get_option( 'vpm_anti_fraud_option_name' ); ?>

		<div class="wrap">
		<div class="catbox-area-admin" style="width: 650px;background: #fff;padding: 27px 50px;">
			<h2>VPM Anti Fraud System</h2>

			<?php settings_errors(); ?>

			<form method="post" action="options.php">
				<?php
					settings_fields( 'vpm_anti_fraud_option_group' );
					do_settings_sections( 'vpm-anti-fraud-admin' );
					submit_button();
				?>
			</form>
		</div>
		</div>
	<?php }

	public function vpm_anti_fraud_page_init() {
		
		register_setting(
			'vpm_anti_fraud_option_group', // option_group
			'vpm_anti_fraud_option_name', // option_name
			array( $this, 'vpm_anti_fraud_sanitize' ) // sanitize_callback
		);

		add_settings_section(
			'vpm_anti_fraud_setting_section', // id
			'Settings', // title
			array( $this, 'vpm_anti_fraud_section_info' ), // callback
			'vpm-anti-fraud-admin' // page
		);

		add_settings_field(
			'enable_anti_fraud_blacklist', // id
			'Enable Blacklist', // title
			array( $this, 'enable_anti_fraud_callback' ), // callback
			'vpm-anti-fraud-admin', // page
			'vpm_anti_fraud_setting_section' // section
		);

		add_settings_field(
			'vpm_limit_attempts', // id
			'Card Try Limit per Order', // title
			array( $this, 'vpm_limit_attempts_callback' ), // callback
			'vpm-anti-fraud-admin', // page
			'vpm_anti_fraud_setting_section' // section
		);


		add_settings_field(
			'vpm_reset_cart', // id
			'Reset cart', // title
			array( $this, 'vpm_reset_cart_callback' ), // callback
			'vpm-anti-fraud-admin', // page
			'vpm_anti_fraud_setting_section' // section
		);


		add_settings_field(
			'vpm_cooldown_duration', // id
			'Cooldown Duration (Seconds)', // title
			array( $this, 'vpm_cooldown_duration_callback' ), // callback
			'vpm-anti-fraud-admin', // page
			'vpm_anti_fraud_setting_section' // section
		);


		add_settings_field(
			'vpm_cooldown_message', // id
			'Cooldown Message', // title
			array( $this, 'vpm_cooldown_message_callback' ), // callback
			'vpm-anti-fraud-admin', // page
			'vpm_anti_fraud_setting_section' // section
		);

		add_settings_field(
			'vpm_blacklist_message', // id
			'Blacklist Message', // title
			array( $this, 'vpm_blacklist_message_callback' ), // callback
			'vpm-anti-fraud-admin', // page
			'vpm_anti_fraud_setting_section' // section
		);

		add_settings_field(
			'vpm_limit_message', // id
			'Card Try Limit Message', // title
			array( $this, 'vpm_limit_message_callback' ), // callback
			'vpm-anti-fraud-admin', // page
			'vpm_anti_fraud_setting_section' // section
		);


		add_settings_field(
			'vpm_blacklist_shipping', // id
			'Blacklist Shipping', // title
			array( $this, 'vpm_blacklist_shipping_callback' ), // callback
			'vpm-anti-fraud-admin', // page
			'vpm_anti_fraud_setting_section' // section
		);
		
		add_settings_field(
			'vpm_blacklist_billing', // id
			'Blacklist Billing', // title
			array( $this, 'vpm_blacklist_billing_callback' ), // callback
			'vpm-anti-fraud-admin', // page
			'vpm_anti_fraud_setting_section' // section
		);
		
		add_settings_field(
			'vpm_blacklist_email', // id
			'Blacklist Email', // title
			array( $this, 'vpm_blacklist_email_callback' ), // callback
			'vpm-anti-fraud-admin', // page
			'vpm_anti_fraud_setting_section' // section
		);
				
		add_settings_field(
			'vpm_blacklist_ip', // id
			'Blacklist IPs', // title
			array( $this, 'vpm_blacklist_ip_callback' ), // callback
			'vpm-anti-fraud-admin', // page
			'vpm_anti_fraud_setting_section' // section
		);	

		/*
		add_settings_field(
			'vpm_blacklist_phone', // id
			'Blacklist Phone', // title
			array( $this, 'vpm_blacklist_phone_callback' ), // callback
			'vpm-anti-fraud-admin', // page
			'vpm_anti_fraud_setting_section' // section
		);*/
	}

	/**
	 * @param $input
	 */
	public function vpm_anti_fraud_sanitize($input) {
		$sanitary_values = array();
		if ( isset( $input['enable_anti_fraud_blacklist'] ) ) {
			$sanitary_values['enable_anti_fraud_blacklist'] = $input['enable_anti_fraud_blacklist'];
		}

		if ( isset( $input['vpm_reset_cart'] ) ) {
			$sanitary_values['vpm_reset_cart'] = $input['vpm_reset_cart'];
		}

		if ( isset( $input['vpm_blacklist_shipping'] ) ) {
			$sanitary_values['vpm_blacklist_shipping'] = esc_textarea( $input['vpm_blacklist_shipping'] );
		}
		
		if ( isset( $input['vpm_blacklist_billing'] ) ) {
			$sanitary_values['vpm_blacklist_billing'] = esc_textarea( $input['vpm_blacklist_billing'] );
		}
		
		if ( isset( $input['vpm_blacklist_email'] ) ) {
			$sanitary_values['vpm_blacklist_email'] = esc_textarea( $input['vpm_blacklist_email'] );
		}
        
		if ( isset( $input['vpm_blacklist_ip'] ) ) {
			$sanitary_values['vpm_blacklist_ip'] = esc_textarea( $input['vpm_blacklist_ip'] );
		}
        
		if ( isset( $input['vpm_blacklist_message'] ) ) {
			$sanitary_values['vpm_blacklist_message'] = esc_textarea( $input['vpm_blacklist_message'] );
		}

		if ( isset( $input['vpm_limit_message'] ) ) {
			$sanitary_values['vpm_limit_message'] = esc_textarea( $input['vpm_limit_message'] );
		}

		if ( isset( $input['vpm_cooldown_message'] ) ) {
			$sanitary_values['vpm_cooldown_message'] = esc_textarea( $input['vpm_cooldown_message'] );
		}
		
		if ( isset( $input['vpm_limit_attempts'] ) ) {
			$sanitary_values['vpm_limit_attempts'] = esc_attr($input['vpm_limit_attempts']);
		}
		
		if ( isset( $input['vpm_cooldown_duration'] ) ) {
			$sanitary_values['vpm_cooldown_duration'] = esc_attr($input['vpm_cooldown_duration']);
		}
		
		/*if ( isset( $input['vpm_blacklist_phone'] ) ) {
			$sanitary_values['vpm_blacklist_phone'] = esc_textarea( $input['vpm_blacklist_phone'] );
		}*/
		
		return $sanitary_values;
	}

	public function vpm_anti_fraud_section_info() {
		
	}

	public function enable_anti_fraud_callback() {
		printf(
			'<input type="checkbox" name="vpm_anti_fraud_option_name[enable_anti_fraud_blacklist]" id="enable_anti_fraud_blacklist" value="enable_anti_fraud_blacklist" %s> <label for="enable_anti_fraud_blacklist">Enable Anti fraud blacklist</label>',
			( isset( $this->vpm_anti_fraud_options['enable_anti_fraud_blacklist'] ) && $this->vpm_anti_fraud_options['enable_anti_fraud_blacklist'] === 'enable_anti_fraud_blacklist' ) ? 'checked' : ''
		);
	}

	public function vpm_reset_cart_callback() {
		printf(
			'<input type="checkbox" name="vpm_anti_fraud_option_name[vpm_reset_cart]" id="vpm_reset_cart" value="vpm_reset_cart" %s> <label for="vpm_reset_cart">Reset the cart(After Limit over)</label>',
			( isset( $this->vpm_anti_fraud_options['vpm_reset_cart'] ) && $this->vpm_anti_fraud_options['vpm_reset_cart'] === 'vpm_reset_cart' ) ? 'checked' : ''
		);
	}

	public function vpm_blacklist_shipping_callback() {
		printf(
			'<textarea class="large-text vpm_tags_input" rows="5" name="vpm_anti_fraud_option_name[vpm_blacklist_shipping]" id="vpm_blacklist_shipping">%s</textarea>',
			isset( $this->vpm_anti_fraud_options['vpm_blacklist_shipping'] ) ? esc_attr( $this->vpm_anti_fraud_options['vpm_blacklist_shipping']) : ''
		);
	}
	
	public function vpm_blacklist_billing_callback() {
		printf(
			'<textarea class="large-text vpm_tags_input" rows="5" name="vpm_anti_fraud_option_name[vpm_blacklist_billing]" id="vpm_blacklist_billing">%s</textarea>',
			isset( $this->vpm_anti_fraud_options['vpm_blacklist_billing'] ) ? esc_attr( $this->vpm_anti_fraud_options['vpm_blacklist_billing']) : ''
		);
	}
	
	
	public function vpm_blacklist_email_callback() {
		printf(
			'<textarea class="large-text vpm_tags_input" rows="5" name="vpm_anti_fraud_option_name[vpm_blacklist_email]" id="vpm_blacklist_email">%s</textarea>',
			isset( $this->vpm_anti_fraud_options['vpm_blacklist_email'] ) ? esc_attr( $this->vpm_anti_fraud_options['vpm_blacklist_email']) : ''
		);
	}
	public function vpm_blacklist_ip_callback() {
		printf(
			'<textarea class="large-text vpm_tags_input" rows="5" name="vpm_anti_fraud_option_name[vpm_blacklist_ip]" id="vpm_blacklist_ip">%s</textarea>',
			isset( $this->vpm_anti_fraud_options['vpm_blacklist_ip'] ) ? esc_attr( $this->vpm_anti_fraud_options['vpm_blacklist_ip']) : ''
		);
	}
	public function vpm_blacklist_message_callback() {
		printf(
			'<textarea class="large-text" rows="3" name="vpm_anti_fraud_option_name[vpm_blacklist_message]" id="vpm_blacklist_message">%s</textarea>',
			isset( $this->vpm_anti_fraud_options['vpm_blacklist_message'] ) ? esc_attr( $this->vpm_anti_fraud_options['vpm_blacklist_message']) : ''
		);
	}

	public function vpm_limit_message_callback() {
		printf(
			'<textarea class="large-text" rows="3" name="vpm_anti_fraud_option_name[vpm_limit_message]" id="vpm_limit_message">%s</textarea>',
			isset( $this->vpm_anti_fraud_options['vpm_limit_message'] ) ? esc_attr( $this->vpm_anti_fraud_options['vpm_limit_message']) : ''
		);
	}
	public function vpm_cooldown_message_callback() {
		printf(
			'<textarea class="large-text" rows="3" name="vpm_anti_fraud_option_name[vpm_cooldown_message]" id="vpm_cooldown_message">%s</textarea>',
			isset( $this->vpm_anti_fraud_options['vpm_cooldown_message'] ) ? esc_attr( $this->vpm_anti_fraud_options['vpm_cooldown_message']) : ''
		);
	}

	public function vpm_limit_attempts_callback() {
			printf(
			'<input class="regular-text" type="number" name="vpm_anti_fraud_option_name[vpm_limit_attempts]" id="vpm_limit_attempts" value="%s">',
			isset( $this->vpm_anti_fraud_options['vpm_limit_attempts'] ) ? esc_attr( $this->vpm_anti_fraud_options['vpm_limit_attempts']) : '3'
		);
	}
	
	public function vpm_cooldown_duration_callback() {
			printf(
			'<input class="regular-text" type="number" name="vpm_anti_fraud_option_name[vpm_cooldown_duration]" id="vpm_cooldown_duration" value="%s">',
			isset( $this->vpm_anti_fraud_options['vpm_cooldown_duration'] ) ? esc_attr( $this->vpm_anti_fraud_options['vpm_cooldown_duration']) : '0'
		);
	}
	
	/*public function vpm_blacklist_phone_callback() {
		printf(
			'<textarea class="large-text vpm_tags_input" rows="5" name="vpm_anti_fraud_option_name[vpm_blacklist_phone]" id="vpm_blacklist_phone">%s</textarea>',
			isset( $this->vpm_anti_fraud_options['vpm_blacklist_phone'] ) ? esc_attr( $this->vpm_anti_fraud_options['vpm_blacklist_phone']) : ''
		);
	}*/
}
if ( is_admin() )
	$vpm_anti_fraud = new VPMAntiFraud();

/* 
 * Retrieve this value with:
 * $vpm_anti_fraud_options = get_option( 'vpm_anti_fraud_option_name' ); // Array of All Options
 * $enable_shipping_blacklist = $vpm_anti_fraud_options['enable_shipping_blacklist']; 
 * $vpm_blacklist_shipping = $vpm_anti_fraud_options['vpm_blacklist_shipping']; 
 * $vpm_blacklist_email = $vpm_anti_fraud_options['vpm_blacklist_email']; 
 * $vpm_blacklist_phone = $vpm_anti_fraud_options['vpm_blacklist_phone']; 
 * $vpm_blacklist_message = $vpm_anti_fraud_options['vpm_blacklist_message']; 
 * $vpm_limit_message = $vpm_anti_fraud_options['vpm_limit_message'];
 * $vpm_cooldown_message = $vpm_anti_fraud_options['vpm_cooldown_message'];
 *
 * $vpm_limit_attempts = $vpm_anti_fraud_options['vpm_limit_attempts']; 
 * $vpm_cooldown_duration = $vpm_anti_fraud_options['vpm_cooldown_duration'];
 */


