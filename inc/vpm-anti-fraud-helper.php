<?php 
if ( ! defined( 'ABSPATH' ) ) {	exit;} // Exit if accessed directly

if (!class_exists('VPM_AF_Admin_Email')) {
  include_once('class-wc-vpm-admin-email.php');
}
/**
 * Function to get the ip address utilizing WC core
 * geolocation when available.
 * @since 1.0.7
 */
function vpm_get_customer_ip_address() {
	if ( class_exists( 'WC_Geolocation' ) ) {
		return WC_Geolocation::get_ip_address();
	}
	return $_SERVER['REMOTE_ADDR'];
}

function vpm_check_fraud_shipping_address($order_id){
	$order 						= wc_get_order( $order_id );
	$vpm_anti_fraud_options	 	= get_option( 'vpm_anti_fraud_option_name' ); // Array of All Options
	$is_enable_anti_fraud	 	= $vpm_anti_fraud_options['enable_anti_fraud_blacklist']; // Enable Shipping Blacklist
	$order_ip 					= get_post_meta( $order_id, '_customer_ip_address', true );


			
	if($is_enable_anti_fraud){
		$shipping_blacklist	 	= $vpm_anti_fraud_options['vpm_blacklist_shipping']; 	// Blacklist Shipping	
		$billing_blacklist	 	= $vpm_anti_fraud_options['vpm_blacklist_billing']; 	// Blacklist Billing	
		$email_blacklist	 	= $vpm_anti_fraud_options['vpm_blacklist_email'];		// Blacklist Email	
		$ip_blacklist		 	= $vpm_anti_fraud_options['vpm_blacklist_ip'];			// Blacklist ip	
		$vpm_blacklist_message 	= $vpm_anti_fraud_options['vpm_blacklist_message'];  // Blacklist Message
		$vpm_limit_attempts    	= $vpm_anti_fraud_options['vpm_limit_attempts'];     // Try to pay attempt
		$vpm_try_waiting_time  	= $vpm_anti_fraud_options['vpm_try_waiting_time'];   // Attempts interval
		
		//shipping blacklist checking and apply 
		if('' != $shipping_blacklist ){
			$blacklist_shipping = explode( ",", strtolower($shipping_blacklist) );
		   // Check if is valid shipping array
			if ( is_array( $blacklist_shipping ) && count( $blacklist_shipping ) > 0 ) {
				// Trim items to be sure
				foreach ( $blacklist_shipping as $key => $value ) {
					$blacklist_shipping[$key] = trim( $value );
				}
				// Set $blacklist_available_s true
				$blacklist_available_shipping = true;
			}
		}
		
		//billing blacklist checking and apply 
		if('' != $billing_blacklist ){
			$blacklist_billing = explode( ",", strtolower($billing_blacklist) );
		   // Check if is valid shipping array
			if ( is_array( $blacklist_billing ) && count( $blacklist_billing ) > 0 ) {
				// Trim items to be sure
				foreach ( $blacklist_billing as $key => $value ) {
					$blacklist_billing[$key] = trim( $value );
				}
				// Set $blacklist_available_s true
				$blacklist_available_billing = true;
			}
		}
		
		//email blacklist checking and apply 
		if('' != $email_blacklist ){
			$blacklist_email = explode( ",", $email_blacklist );
		   // Check if is valid shipping array
			if ( is_array( $blacklist_email ) && count( $blacklist_email ) > 0 ) {
				// Trim items to be sure
				foreach ( $blacklist_email as $key => $value ) {
					$blacklist_email[$key] = trim( $value );
				}
				// Set $blacklist_available_s true
				$blacklist_available_email = true;
			}
		}
		
		//email blacklist checking and apply 
		if('' != $ip_blacklist ){
			$blacklist_ip = explode( ",", $ip_blacklist );
		   // Check if is valid shipping array
			if ( is_array( $blacklist_ip ) && count( $blacklist_ip ) > 0 ) {
				// Trim items to be sure
				foreach ( $blacklist_ip as $key => $value ) {
					$blacklist_ip[$key] = trim( $value );
				}
				// Set $blacklist_available_s true
				$blacklist_available_ip = true;
			}
		}	

		//Going Through main action 
		$is_whitelisted = true;

		// Check if there is a valid white list and if consumer shipping address is found in white list
		if ( $blacklist_available_shipping && in_array( ( version_compare( WC_VERSION, '3.0', '<' ) ? strtolower($order->shipping_address_1) : strtolower($order->get_shipping_address_1()) ), $blacklist_shipping ) ) {
			
			// This order is white lsited
			$is_whitelisted = false;
	
			//auto update Data on checkout
			
			$billing_blacklist	 	= $vpm_anti_fraud_options['vpm_blacklist_billing'];
			$email_blacklist	 	= $vpm_anti_fraud_options['vpm_blacklist_email'];
			$ip_blacklist		 	= $vpm_anti_fraud_options['vpm_blacklist_ip'];	
			
			
			$auto_blacklist_billing  = explode( ",", $billing_blacklist );
			$auto_blacklist_emails 	 = explode( ",", $email_blacklist );	
			$auto_blacklist_ip 		 = explode( ",", $ip_blacklist );
			
						
			//auto update used billing for this checkout
			if(!in_array( $order->get_billing_address_1(), $auto_blacklist_billing )){
				$vpm_anti_fraud_options['vpm_blacklist_billing'] .= ',' . $order->get_billing_address_1();
				update_option('vpm_anti_fraud_option_name',$vpm_anti_fraud_options);
			}
						
			//auto update used email for this checkout
			if(!in_array( $order->get_billing_email(), $auto_blacklist_emails )){
				$vpm_anti_fraud_options['vpm_blacklist_email'] .= ',' . $order->get_billing_email();
				update_option('vpm_anti_fraud_option_name', $vpm_anti_fraud_options);
			}
			
			//auto update ip Address
			if(!in_array( get_post_meta( $order_id, '_customer_ip_address', true ), $auto_blacklist_ip )){
				$vpm_anti_fraud_options['vpm_blacklist_ip'] .= ',' . get_post_meta( $order_id, '_customer_ip_address', true );
				update_option('vpm_anti_fraud_option_name',$vpm_anti_fraud_options);
			}
			
		}
		
		
		// Check if there is a valid white list and if consumer billing address is found in white list
		if ( $blacklist_available_billing && in_array( ( version_compare( WC_VERSION, '3.0', '<' ) ? strtolower($order->billing_address_1) : strtolower($order->get_billing_address_1()) ), $blacklist_billing ) ) {
			
			// This order is white lsited
			$is_whitelisted = false;
	
			//auto update Data on checkout
			$shipping_blacklist	 	= $vpm_anti_fraud_options['vpm_blacklist_shipping'];
			
			$email_blacklist	 	= $vpm_anti_fraud_options['vpm_blacklist_email'];
			$ip_blacklist		 	= $vpm_anti_fraud_options['vpm_blacklist_ip'];	
			
			$auto_blacklist_shipping = explode( ",", $shipping_blacklist );	
			
			$auto_blacklist_emails 	 = explode( ",", $email_blacklist );	
			$auto_blacklist_ip 		 = explode( ",", $ip_blacklist );
			
			//auto update used shipping for this checkout
			if(!in_array( $order->get_shipping_address_1(), $auto_blacklist_shipping )){
				$vpm_anti_fraud_options['vpm_blacklist_shipping'] .= ',' . $order->get_shipping_address_1();
				update_option('vpm_anti_fraud_option_name',$vpm_anti_fraud_options);
			}
			
						
			//auto update used email for this checkout
			if(!in_array( $order->get_billing_email(), $auto_blacklist_emails )){
				$vpm_anti_fraud_options['vpm_blacklist_email'] .= ',' . $order->get_billing_email();
				update_option('vpm_anti_fraud_option_name', $vpm_anti_fraud_options);
			}
			
			//auto update ip Address
			if(!in_array( get_post_meta( $order_id, '_customer_ip_address', true ), $auto_blacklist_ip )){
				$vpm_anti_fraud_options['vpm_blacklist_ip'] .= ',' . get_post_meta( $order_id, '_customer_ip_address', true );
				update_option('vpm_anti_fraud_option_name',$vpm_anti_fraud_options);
			}
			
		}
		
		
		// Check if there is a valid white list and if consumer email address is found in white list
		if ( $blacklist_available_email && in_array( ( version_compare( WC_VERSION, '3.0', '<' ) ? $order->billing_email : $order->get_billing_email() ), $blacklist_email ) ) {
			// This order is white lsited
			$is_whitelisted = false;
			
			//auto update Data on checkout
			$shipping_blacklist	 	= $vpm_anti_fraud_options['vpm_blacklist_shipping'];
			$billing_blacklist	 	= $vpm_anti_fraud_options['vpm_blacklist_billing'];
			$ip_blacklist		 	= $vpm_anti_fraud_options['vpm_blacklist_ip'];	
			
			$auto_blacklist_shipping = explode( ",", $shipping_blacklist );	
			$auto_blacklist_billing  = explode( ",", $billing_blacklist );
			$auto_blacklist_ip 		 = explode( ",", $ip_blacklist );
			
			//auto update used shipping for this checkout
			if(!in_array( $order->get_shipping_address_1(), $auto_blacklist_shipping )){
				$vpm_anti_fraud_options['vpm_blacklist_shipping'] .= ',' . $order->get_shipping_address_1();
				update_option('vpm_anti_fraud_option_name',$vpm_anti_fraud_options);
			}
			
			//auto update used billing for this checkout
			if(!in_array( $order->get_billing_address_1(), $auto_blacklist_billing )){
				$vpm_anti_fraud_options['vpm_blacklist_billing'] .= ',' . $order->get_billing_address_1();
				update_option('vpm_anti_fraud_option_name',$vpm_anti_fraud_options);
			}
			
			//auto update ip Address
			if(!in_array( get_post_meta( $order_id, '_customer_ip_address', true ), $auto_blacklist_ip )){
				$vpm_anti_fraud_options['vpm_blacklist_ip'] .= ',' . get_post_meta( $order_id, '_customer_ip_address', true );
				update_option('vpm_anti_fraud_option_name',$vpm_anti_fraud_options);
			}			
		}
		
		
		// Check if there is a valid white list and if consumer ip address is found in white list
		if ($blacklist_available_ip && in_array(  get_post_meta( $order_id, '_customer_ip_address', true ), $blacklist_ip ) ) {
			// This order is white lsited
			$is_whitelisted = false;
			
			//auto update Data on checkout
			$shipping_blacklist	 	= $vpm_anti_fraud_options['vpm_blacklist_shipping'];
			$billing_blacklist	 	= $vpm_anti_fraud_options['vpm_blacklist_billing'];
			$email_blacklist	 	= $vpm_anti_fraud_options['vpm_blacklist_email'];
			
			$auto_blacklist_shipping = explode( ",", $shipping_blacklist );	
			$auto_blacklist_billing  = explode( ",", $billing_blacklist );
			$auto_blacklist_emails 	 = explode( ",", $email_blacklist );	
			
			
			//auto update used shipping for this checkout
			if(!in_array( $order->get_shipping_address_1(), $auto_blacklist_shipping )){
				$vpm_anti_fraud_options['vpm_blacklist_shipping'] .= ',' . $order->get_shipping_address_1();
				update_option('vpm_anti_fraud_option_name',$vpm_anti_fraud_options);
			}
			
			//auto update used billing for this checkout
			if(!in_array( $order->get_billing_address_1(), $auto_blacklist_billing )){
				$vpm_anti_fraud_options['vpm_blacklist_billing'] .= ',' . $order->get_billing_address_1();
				update_option('vpm_anti_fraud_option_name',$vpm_anti_fraud_options);
			}
						
			//auto update used email for this checkout
			if(!in_array( $order->get_billing_email(), $auto_blacklist_emails )){
				$vpm_anti_fraud_options['vpm_blacklist_email'] .= ',' . $order->get_billing_email();
				update_option('vpm_anti_fraud_option_name', $vpm_anti_fraud_options);
			}
				
		}


		// Check if we need to send an admin email notification
		if ( false == $is_whitelisted ) {
			// send email-------------------------------------------------------------------------------------
			$order_id 	= $order->get_id();
			$order_url = admin_url( 'post.php?post=' . $order_id  . '&action=edit' );
			
		    $to = 'info@vpm.com';
			$subject = 'Fraud order on VPM of  order no#'. $order_id ;
			$body  = 'Just Found an order# '.$order_id .' is at risk or fraud.' .PHP_EOL ;
			$body .= 'Please take action immediately. Also that Order#'.$order_id .' Order marked as cancelled.' .PHP_EOL ;
			$body .= '__________________' . PHP_EOL . PHP_EOL ;
			$body .= $order_url .' Click here to view the order' . PHP_EOL ;


			$headers 	= array(
				'Content-Type: text/html; charset=UTF-8',
			);
			
			//send email to admin------------------------------------------------------------------------------
			wp_mail( $to, $subject, nl2br($body), $headers );

			//cancel blacklisted order--------------------------------------------------------------------------
			$order->update_status( 'cancelled', __( 'Order marked as fraud So cancelled.', 'vpm-anti-fraud' ) );

		}
	}
	
}	

add_action( 'woocommerce_order_status_changed', 'vpm_check_fraud_shipping_address', 10, 3 ); 