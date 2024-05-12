<?php

if ( ! defined( 'ABSPATH' ) ) {	exit;} // Exit if accessed directly

class VPM_fraud_limit_block {


	public function __construct() {
		add_action( 'woocommerce_after_checkout_validation', array( $this, 'validate_blacklisted_customer' ), 10, 1 );
		add_action( 'woocommerce_before_pay_action', array( $this, 'manage_blacklisted_customers_order_pay' ), 99, 1 );
		add_action( 'woocommerce_after_checkout_validation', array( $this, 'check_customers_order_payment_try_limit' ), 99, 1 );
		add_action( 'woocommerce_after_pay_action', array( $this, 'manage_multiple_failed_attempts_order_pay' ), 99, 1 );
		add_action( 'woocommerce_checkout_order_processed', array( $this, 'manage_multiple_failed_attempts_checkout' ), 100, 3 );
		add_action( 'woocommerce_order_status_failed', array( $this, 'manage_multiple_failed_attempts_default' ), 100, 2 );
		add_action( 'template_redirect', array( $this, 'handle_session_clear_redirection' ) );
	}


	public function handle_session_clear_redirection() {
		if ( isset( $_SESSION['session_cleared'] ) && $_SESSION['session_cleared'] ) {
			// Reload the current page
			wp_redirect( $_SERVER['REQUEST_URI'] );
			exit;
		}
	}



	public function manage_multiple_failed_attempts_checkout( $_order_id, $_posted_data, $order ) {
		if ( is_admin() ) {
			return;
		}
		$this->manage_multiple_failed_attempts( $order );
	}


	public function manage_multiple_failed_attempts_order_pay( $order ) {
		if ( is_admin() ) {
			return;
		}
		$this->manage_multiple_failed_attempts( $order );
	}


	public function manage_multiple_failed_attempts_default( $order_id, $order ) {
		if ( is_admin() ) {
			return;
		}
		$this->manage_multiple_failed_attempts( $order );
	}


	public function manage_multiple_failed_attempts( $order ) {

		if ( $order->get_status() === 'failed' || $order->get_status() === 'pending' ) {

			$pre_fraud_attempt = (int) $order->get_meta( '_wmfo_fraud_attempts', true );
			$order->update_meta_data( '_wmfo_fraud_attempts', $pre_fraud_attempt + 1 );
			$order->save();
		}
	}


	public function validate_blacklisted_customer( $posted ) {
		if ( ! empty( $errors->errors ) ) {
			return;
		}

		//If there are such errors, skip.
		if ( ! isset( WC()->session->reload_checkout ) ) {
			$error_notices = wc_get_notices( 'error' );
		}

		if ( ! empty( $error_notices ) ) {
			return;
		}

		$vpm_anti_fraud_options = get_option( 'vpm_anti_fraud_option_name' );
		$is_enable_anti_fraud = $vpm_anti_fraud_options['enable_anti_fraud_blacklist'];

		if ( $is_enable_anti_fraud ) {
			$vpm_blacklist_message = $vpm_anti_fraud_options['vpm_blacklist_message'];
			$email                 = isset( $posted['billing_email'] ) ? sanitize_email( $posted['billing_email'] ) : '';
			$billing_address       = isset( $posted['billing_address_1'] ) ? sanitize_text_field( $posted['billing_address_1'] ) : '';
			$shipping_address      = isset( $posted['shipping_address_1'] ) ? sanitize_text_field( $posted['shipping_address_1'] ) : '';
			$ip_address            = $this->get_customer_ip_address();
			$email_blacklist       = explode( ",", $vpm_anti_fraud_options['vpm_blacklist_email'] );
			$billing_blacklist     = explode( ",", $vpm_anti_fraud_options['vpm_blacklist_billing'] );
			$shipping_blacklist    = explode( ",", $vpm_anti_fraud_options['vpm_blacklist_shipping'] );
			$ip_blacklist          = explode( ",", $vpm_anti_fraud_options['vpm_blacklist_ip'] );

			if ( in_array( $email, $email_blacklist ) || in_array( $billing_address, $billing_blacklist ) ||
			     in_array( $shipping_address, $shipping_blacklist ) || in_array( $ip_address, $ip_blacklist ) ) {
				$error_message = ( $vpm_blacklist_message ) ?: 'Sorry, You are not allowed to place an order at VPM.';
				wc_add_notice( $error_message, 'error' );
			}
		}
	}


	public function check_customers_order_payment_try_limit( $order ) {
		
		if ( ! empty( $errors->errors ) ) {
			return;
		}
		
		// If there are such errors, skip.
		if ( ! isset( WC()->session->reload_checkout ) ) {
			$error_notices = wc_get_notices( 'error' );
		}

		if ( ! empty( $error_notices ) ) {
			return;
		}

		$order_id               = WC()->session->get( 'order_awaiting_payment' );
		$vpm_anti_fraud_options = get_option( 'vpm_anti_fraud_option_name' );
		$vpm_cooldown_message   = $vpm_anti_fraud_options['vpm_cooldown_message'];
		$vpm_limit_message      = $vpm_anti_fraud_options['vpm_limit_message'];
		$vpm_limit_attempts     = $vpm_anti_fraud_options['vpm_limit_attempts'];
		$vpm_reset_cart         = $vpm_anti_fraud_options['vpm_reset_cart'];
		$vpm_cooldown_duration  = $vpm_anti_fraud_options['vpm_cooldown_duration'];

		$vpm_limit_message      = str_replace( '{{limit}}', $vpm_limit_attempts, $vpm_limit_message );

		$fraud_limit            = ( $vpm_limit_attempts ) ?: 5;
		$cooldown_duration      = ( $vpm_cooldown_duration ) ?: 60; // Default 60 seconds

		$attempts_meta_key  = '_wmfo_fraud_attempts';
		$cooldown_meta_key  = '_order_last_checkout_time';

		$attempts_meta_value             = get_post_meta( $order_id, $attempts_meta_key, true );

		// Check if cooldown period has passed
		$last_checkout_time = get_post_meta( $order_id, $cooldown_meta_key, true );

		$current_time = time();

		if($last_checkout_time ){
			$time_since_last_checkout = $current_time - $last_checkout_time;
		}else{
			$time_since_last_checkout = $current_time;
		}

		if ( $last_checkout_time && $time_since_last_checkout < (int)$cooldown_duration ) {
			$remaining_cooldown = $cooldown_duration - $time_since_last_checkout;
			$cooldown_message = sprintf( $vpm_cooldown_message?? 'Please wait %d seconds.', $remaining_cooldown );
			wc_add_notice( $cooldown_message, 'error' );
			return;
		}

		if ( $attempts_meta_value && $attempts_meta_value > (int) $fraud_limit ) {
			$error_message = ( $vpm_limit_message ) ?: 'Sorry, your order payment limit is over.';
			wc_add_notice( $error_message, 'error' );

			//clear the current cart session
			if($vpm_reset_cart == 'vpm_reset_cart'){
				WC()->cart->empty_cart(); // Clear the cart
			}
			update_post_meta( $order_id, $cooldown_meta_key, $current_time );
			return;

		}

		update_post_meta( $order_id, $cooldown_meta_key, $current_time );

	}

	private function get_customer_ip_address() {
		if ( ! empty( $_SERVER['HTTP_CLIENT_IP'] ) ) {
			//ip from shared internet
			$ip = $_SERVER['HTTP_CLIENT_IP'];
		} elseif ( ! empty( $_SERVER['HTTP_X_FORWARDED_FOR'] ) ) {
			//ip pass from proxy
			$ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
		} else {
			$ip = $_SERVER['REMOTE_ADDR'];
		}
		return $ip;
	}

}

$vpm_fraud_limit_block = new VPM_fraud_limit_block();