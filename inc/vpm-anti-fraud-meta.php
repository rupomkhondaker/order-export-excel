<?php 
if ( ! defined( 'ABSPATH' ) ) {	exit;} // Exit if accessed directly

// Add a custom metabox only for shop_order post type (order edit pages)
add_action( 'add_meta_boxes', 'add_meta_boxesws' );
function add_meta_boxesws()
{
    add_meta_box( 'custom_order_meta_box', __( 'Black List Fraud Order' ),'custom_metabox_content', 'shop_order', 'side', 'high');
}

function custom_metabox_content(){
    $post_id = isset($_GET['post']) ? $_GET['post'] : false;
    if(! $post_id ) return; // Exit
	$order = new WC_Order($post_id);
	
	$anti_fraud_options	 = get_option( 'vpm_anti_fraud_option_name' ); // Array of All Options
	
	$is_enable_anti_fraud= $anti_fraud_options['enable_anti_fraud_blacklist']; // Enable Shipping Blacklist
	
	$shipping_blacklist	 = $anti_fraud_options['vpm_blacklist_shipping']; 	// Blacklist Shipping	
	$billing_blacklist	 = $anti_fraud_options['vpm_blacklist_billing']; 	// Blacklist Billing	
	$email_blacklist	 = $anti_fraud_options['vpm_blacklist_email'];		// Blacklist Email	
	

	
	//shipping blacklist checking and apply 
	if('' != $shipping_blacklist ){
		$blacklist_shipping = explode( ",", $shipping_blacklist );

		$blacklist_shipping = array_map('strtolower', $blacklist_shipping);

		
	   // Check if is valid shipping array
		if ( is_array( $blacklist_shipping ) && count( $blacklist_shipping ) > 0 ) {
			// Trim items to be sure
			foreach ( $blacklist_shipping as $key => $value ) {
				$blacklist_shipping[$key] = trim( $value );
			}
			
		}
	}else{
		$blacklist_shipping = array();
	}
	
	
	//billing blacklist checking and apply 
	if('' != $billing_blacklist ){
		$blacklist_billing = explode( ",", $billing_blacklist );

		$blacklist_billing = array_map('strtolower', $blacklist_billing);
		
		
	   // Check if is valid billing array
		if ( is_array( $blacklist_billing ) && count( $blacklist_billing ) > 0 ) {
			// Trim items to be sure
			foreach ( $blacklist_billing as $key => $value ) {
				$blacklist_billing[$key] = trim( $value );
			}
			
		}
	}else{
		$blacklist_billing = array();
	}
	
	
	
	
	
	//email blacklist checking and apply 
	if('' != $email_blacklist ){
		$blacklist_email = explode( ",", $email_blacklist );
		
		$blacklist_email = array_map('strtolower', $blacklist_email);
		
	   // Check if is valid shipping array
		if ( is_array( $blacklist_email ) && count( $blacklist_email ) > 0 ) {
			// Trim items to be sure
			foreach ( $blacklist_email as $key => $value ) {
				$blacklist_email[$key] = trim( $value );
			}
			
		}
	}else{
		$blacklist_email = array();
	}
	
	$status="block";
    ?>
	<?php
		//update used email for this checkout
		if(in_array( strtolower(trim($order->get_billing_email())), $blacklist_email )){
			echo '<p class="warning">email found in block list probably scammer</p>';
		}else{
			
			
			if(!isset( $_GET['block_email'] ) && empty( $_GET['block_email'] )){
				echo'<div class="block-item">';
				echo $order->get_billing_email();
				?>
				<a href="?post=<?php echo $post_id; ?>&action=edit&block_email=<?php echo $status; ?>" class="button block" onclick="return confirm('Are you sure? Block : <?php echo $order->get_billing_email();?>')"><?php _e('Block Email'); ?></a>
				</div>
				<br>
				<hr>
				<?php 
			}
			
			if ( !in_array( $order->get_billing_email(), $blacklist_email ) && isset( $_GET['block_email'] ) && ! empty( $_GET['block_email'] ) ) {
				$anti_fraud_options['vpm_blacklist_email'] .= ',' . $order->get_billing_email();
				update_option('vpm_anti_fraud_option_name',$anti_fraud_options);
			}
		}
		?>
		
		<?php
		//update used Shipping for this checkout
		if(in_array( strtolower(trim( $order->get_shipping_address_1())), $blacklist_shipping )){

			echo '<p class="warning">Shipping address found in block list probably scammer</p>';
		}else{
			
			if(!isset( $_GET['block_address'] ) &&  empty( $_GET['block_address'] )){
				echo'<div class="block-item">';
				echo $order->get_shipping_address_1();
				?>
				<a href="?post=<?php echo $post_id; ?>&action=edit&block_address=<?php echo $status; ?>" class="button block" onclick="return confirm('Are you sure? block : <?php echo $order->get_billing_address_1();?>')"><?php _e('Block Address'); ?></a>
				</div>
				<?php 
			}
			if ( !in_array( $order->get_shipping_address_1(), $blacklist_shipping ) && isset( $_GET['block_address'] ) && ! empty( $_GET['block_address'] ) ) {
				$anti_fraud_options['vpm_blacklist_shipping'] .= ',' . $order->get_shipping_address_1();
				update_option('vpm_anti_fraud_option_name',$anti_fraud_options);
			}
		}
		
		
		//update used Shipping for this checkout
		if(in_array( strtolower(trim( $order->get_billing_address_1())), $blacklist_billing )){

			echo '<p class="warning">Billing address found in block list probably scammer</p>';
		}else{
			
			if(!isset( $_GET['block_address_billing'] ) &&  empty( $_GET['block_address_billing'] )){
				echo'<div class="block-item">';
				echo $order->get_billing_address_1();
				?>
				<a href="?post=<?php echo $post_id; ?>&action=edit&block_address_billing=<?php echo $status; ?>" class="button block" onclick="return confirm('Are you sure? block : <?php echo $order->get_billing_address_1();?>')"><?php _e('Block Address'); ?></a>
				</div>
				<?php 
			}
			if ( !in_array( $order->get_billing_address_1(), $blacklist_billing ) && isset( $_GET['block_address_billing'] ) && ! empty( $_GET['block_address_billing'] ) ) {
				$anti_fraud_options['vpm_blacklist_billing'] .= ',' . $order->get_billing_address_1();
				update_option('vpm_anti_fraud_option_name',$anti_fraud_options);
			}
		}
		
		
	
		
		
		?>
			<style>
				p.warning {
					color: white;
					background: #ff7878;
					padding: 2px 15px;
				}
				
				a.button.block {
					padding: 2px!important;
					line-height: inherit!important;
					float: right;
					min-height: inherit!important;
				}
			</style>
	<?php	

	}
