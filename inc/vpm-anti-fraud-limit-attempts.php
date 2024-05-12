<?php
if ( ! defined( 'ABSPATH' ) ) {	exit;} // Exit if accessed directly
/**
 *
 */
add_action( 'woocommerce_after_checkout_validation', 'vpm_validate_blacklisted_customer', 10, 1 );
add_action( 'woocommerce_before_pay_action', 'manage_blacklisted_customers_order_pay', 99, 1 );
add_action( 'woocommerce_after_checkout_validation', 'check_customers_order_payment_try_limit', 99, 1 );
add_action( 'woocommerce_after_pay_action', 'manage_multiple_failed_attempts_order_pay', 99, 1 );
add_action( 'woocommerce_checkout_order_processed', 'manage_multiple_failed_attempts_checkout', 100, 3 );
add_action( 'woocommerce_order_status_failed', 'manage_multiple_failed_attempts_default', 100, 2 );

function manage_multiple_failed_attempts_checkout( $_order_id, $_posted_data, $order ) {
	if ( is_admin() ) {
		return;
	}
	vpm_manage_multiple_failed_attempts( $order );
}

function manage_multiple_failed_attempts_order_pay( $order ) {
	if ( is_admin() ) {
		return;
	}
	vpm_manage_multiple_failed_attempts( $order );
}


function manage_multiple_failed_attempts_default( $order_id, $order ) {
	if ( is_admin() ) {
		return;
	}
	vpm_manage_multiple_failed_attempts( $order, 'failed' );
}


function vpm_manage_multiple_failed_attempts( $order ) {

	if ( $order->get_status() === 'failed' || $order->get_status() === 'pending' ) {

		// Save to the order meta
		$pre_fraud_attempt = (int) $order->get_meta( '_wmfo_fraud_attempts', true );
		$order->update_meta_data( '_wmfo_fraud_attempts', $pre_fraud_attempt + 1 );
		$order->save();
	}
}


function vpm_validate_blacklisted_customer( $posted ) {

	$vpm_anti_fraud_options = get_option( 'vpm_anti_fraud_option_name' ); // Array of All Options
	$is_enable_anti_fraud   = $vpm_anti_fraud_options['enable_anti_fraud_blacklist']; // Enable Blacklist

	if ( $is_enable_anti_fraud ) {

		$vpm_blacklist_message = $vpm_anti_fraud_options['vpm_blacklist_message'];   // Blacklist Message

		// Get the customer's information from the checkout data
		$email            = isset( $posted['billing_email'] ) ? sanitize_email( $posted['billing_email'] ) : '';
		$billing_address  = isset( $posted['billing_address_1'] ) ? sanitize_text_field( $posted['billing_address_1'] ) : '';
		$shipping_address = isset( $posted['shipping_address_1'] ) ? sanitize_text_field( $posted['shipping_address_1'] ) : '';
		$ip_address       = vpm_get_customer_ip_address();

		// Get the blacklist entries
		$email_blacklist    = explode( ",", $vpm_anti_fraud_options['vpm_blacklist_email'] );
		$billing_blacklist  = explode( ",", $vpm_anti_fraud_options['vpm_blacklist_billing'] );
		$shipping_blacklist = explode( ",", $vpm_anti_fraud_options['vpm_blacklist_shipping'] );
		$ip_blacklist       = explode( ",", $vpm_anti_fraud_options['vpm_blacklist_ip'] );

		// Check if any of the customer's information matches the blacklist
		if ( in_array( $email, $email_blacklist ) ||
		     in_array( $billing_address, $billing_blacklist ) ||
		     in_array( $shipping_address, $shipping_blacklist ) ||
		     in_array( $ip_address, $ip_blacklist ) ) {
			// Add an error notice to prevent checkout

			$error_message = ( $vpm_blacklist_message ) ?: 'Sorry, You are now allowed to place an order at VPM.';
			wc_add_notice( $error_message, 'error' );

		}
	}
}

// Display fraud_attempts meta data on checkout page
function check_customers_order_payment_try_limit( $order ) {

	$order_id               = WC()->session->get( 'order_awaiting_payment' );
	$vpm_anti_fraud_options = get_option( 'vpm_anti_fraud_option_name' );
	$vpm_limit_message      = $vpm_anti_fraud_options['vpm_limit_message'];   // Blacklist Message
	$vpm_limit_attempts     = $vpm_anti_fraud_options['vpm_limit_attempts'];      // Try to pay attempt

	$vpm_limit_message = str_replace( '{{limit}}', $vpm_limit_attempts, $vpm_limit_message );

	$fraud_limit = ( $vpm_limit_attempts ) ?: 5;

	$meta_key = '_wmfo_fraud_attempts';

	$meta_value = get_post_meta( $order_id, $meta_key, true );

	if ( $meta_value && $meta_value > (int) $fraud_limit ) {

		$error_message = ( $vpm_limit_message ) ?: 'Sorry, your order payment limit is over.';
		wc_add_notice( $error_message, 'error' );
	}
}