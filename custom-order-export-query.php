<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}


class COEEOrderExport {

	private $vpm_order_export;

	public function __construct() {
		add_action( 'admin_menu', array( $this, 'vpm_order_export_add_plugin_page' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'order_export_enquee' ) );
		add_action( 'wp_ajax_viewexportorder', array( $this, 'vpm_view_export_order_data' ) );
	}

	public function order_export_enquee() {
		$wp_scripts = wp_scripts();
		wp_enqueue_script( 'jquery-ui-datepicker' );
		wp_enqueue_style( 'jquery-ui-css',
			'https://ajax.googleapis.com/ajax/libs/jqueryui/' . $wp_scripts->registered['jquery-ui-core']->ver . '/themes/overcast/jquery-ui.css',
			false,
			'1.0.1',
			false );
	}

	public function vpm_order_export_add_plugin_page() {

		add_submenu_page(
			'exclutips-settings',
			'Order Export Settings', // page_title
			'Order Export ', // menu_title
			'manage_options', // capability
			'vpm-order-export', // menu_slug
			array( &$this, 'vpm_order_export_create_admin_page' )
		);
	}

	public function vpm_order_export_create_admin_page() { ?>

        <div class="wrap catbox-area-admin" style="width: 100%; height:auto;background: #fff;padding: 27px 50px;">
            <h2>VPM Order Export</h2>

            <div style="display:flex;margin-top:30px">
                <label for="order_from_date">From</label>
                <input id="order_from_date" class="datepicker" type="text" name="order_from_date" autocomplete="off"/>
                <label for="order_to_date">To</label>
                <input id="order_to_date" class="datepicker" type="text" name="order_to_date" autocomplete="off"/>

                <label for="currency_code">Currency:</label>

                <select id="currency_code" name="currency_code">
					<?php
					$currencies = get_woocommerce_currencies();
					foreach ( $currencies as $code => $details ) {
						echo "<option value=\"$code\">$code - {$details['symbol']}</option>";
					}
					?>
                </select>
                <button id="generate_order" name="generate_order">View Data</button>
                <button id="exportCSV" class="btn btn-primary align-right" style="display:none;">Export Report</button>
            </div>

            <table class="table table-bordered" id="OrderExportResults" style="display:none;" width="85%"></table>


            <script>
                jQuery(document).ready(function ($) {
                    $(".datepicker").datepicker({
                        dateFormat: 'yy-mm-dd',
                        changeMonth: true,
                        changeYear: true
                    });

                    $("#exportCSV").click(function () {
                        var current_date = $("#order_from_date").val();
                        $('#OrderExportResults').csvExport({title: 'Sales-report-' + current_date});
                    });

                    //Ajax Load data
                    $('#generate_order').click(function (ev) {
                        // Prevent the form from submitting
                        ev.preventDefault();
                        // Get the coupon code
                        const order_from_date = $('#order_from_date').val();
                        const order_to_date = $('#order_to_date').val();
                        const currency_code = $('#currency_code').val();
                        const button = $(this);

                        const orderTable = $('#OrderExportResults');
                        const exportBTN = $('#exportCSV');

                        $.ajax({
                            type: "post",
                            dataType: "json",
                            url: ajaxurl,
                            data: {
                                action: 'viewexportorder',
                                order_from_date: order_from_date,
                                order_to_date: order_to_date,
                                currency_code: currency_code,

                            },
                            beforeSend: function () {
                                button.html('Please Wait.');
                            },
                            success: function (response) {
                                console.log(currency_code);
                                //if(response.status==='200'){
                                orderTable.html("");
                                let total_price = 0;
                                //append response into the table
                                let orders = '';
                                orders += '<tr>';
                                orders += '<th class="remData" width="5%">Id</th>';
                                orders += '<th class="remData" width="5%">Ordered on</th>';
                                orders += '<th class="remData" width="5%">Currency</th>';
                                orders += '<th class="remData" width="50%">Products</th>';
                                orders += '<th class="remData" width="5%">Tax</th>';
                                orders += '<th class="remData" width="5%">Paid on</th>';
                                orders += '<th class="remData" width="10%">Total</th>';
                                orders += '</tr>';

                                $.each(response.tabledata, function (key, value) {
                                    // DATA FROM JSON OBJECT
                                    orders += '<tr>';
                                    orders += '<td>' + value.order_id + '</td>';
                                    orders += '<td>' + value.order_created_on + '</td>';
                                    orders += '<td>' + value.currency + '</td>';
                                    orders += '<td>' + value.order_items + '</td>';
                                    orders += '<td>' + value.order_tax + '</td>';
                                    orders += '<td>' + value.paid_on + '</td>';
                                    orders += '<td>' + value.order_total + '</td>';
                                    orders += '</tr>';
                                });
                                //Grand Total
                                $.each(response.tabledata, function (key, value) {
                                    total_price += parseFloat(value.order_total);
                                });

                                orders += '<tr>';
                                orders += '<td colspan="5"></td>';
                                orders += '<td>Grand Total</td>';
                                orders += '<td>' + (total_price).toFixed(2) + '</td>';
                                orders += '</tr>';

                                orderTable.append(orders);

                                setTimeout(function () {
                                    //reload with ajax
                                    button.html('View Data');
                                    orderTable.show();
                                    exportBTN.show();
                                }, 3000);
                                //}

                            },
                            error: function (errorThrown) {
                                alert(errorThrown);
                            }
                        });

                    });
                });
            </script>
            <style>

                table#OrderExportResults {
                    border: 0px solid #000000;
                    border-collapse: collapse;
                    margin: 20px 0;
                }

                table#OrderExportResults td, table#OrderExportResults th {
                    border: 1px solid #AAAAAA;
                    padding: 3px 4px;
                }

                table#OrderExportResults tbody td {
                    font-size: 14px;
                }

                table#OrderExportResults thead {
                    background: #E1F5FF;
                }

                table#OrderExportResults thead th {
                    font-weight: normal;
                    text-align: center;
                }

                table#OrderExportResults tfoot {
                    font-weight: bold;
                }


            </style>
        </div>
		<?php
	}

	public function vpm_view_export_order_data() {

		// Get the value of the coupon code
		$currency_code = $_REQUEST['currency_code'];
		$from_date     = $_REQUEST['order_from_date'];
		$to_date       = $_REQUEST['order_to_date'];


		// Check coupon code to make sure is not empty
		if ( empty( $from_date ) || empty( $to_date ) ) {
			// Build our response
			$response = array(
				'status'  => 'error',
				'message' => 'Please select date range.',
			);
			header( 'Content-Type: application/json' );
			echo json_encode( $response );
			// Always exit when doing ajax
			exit();
		} else {
			// Check coupon to make determine if It's valid or not
			if ( isset( $from_date ) && isset( $to_date ) ) {
				global $wpdb;
				$wp_post                    = $wpdb->prefix . 'posts';
				$wp_postmeta                = $wpdb->prefix . 'postmeta';
				$wp_woocommerce_order_items = $wpdb->prefix . 'woocommerce_order_items';
				$from_date                  = date( $from_date );

				$to_date = date( 'Y-m-d', strtotime( $to_date . " +1 days" ) );
				//$to_date = date( $to_date );

				$prepared_query = "
					select
						p.ID as order_id,
						DATE(p.post_date) as order_created_on,
						cpm.meta_value AS currency,
						 ( select group_concat( order_item_name separator '|' ) from $wp_woocommerce_order_items where order_id = p.ID ) as order_items,
						max( CASE WHEN pm.meta_key = '_order_tax' and p.ID = pm.post_id THEN pm.meta_value END ) as order_tax,
						 DATE(max( CASE WHEN pm.meta_key = '_paid_date' and p.ID = pm.post_id THEN pm.meta_value END )) as paid_on,
						max( CASE WHEN pm.meta_key = '_order_total' and p.ID = pm.post_id THEN pm.meta_value END ) as order_total
					from
						$wp_post p 
						JOIN $wp_postmeta cpm ON p.ID = cpm.post_id
						JOIN $wp_postmeta pm on p.ID = pm.post_id
						JOIN $wp_woocommerce_order_items oi on p.ID = oi.order_id
						
					where
						post_type = 'shop_order' and
						post_date BETWEEN DATE '$from_date' AND '$to_date' and
						cpm.meta_key = '_order_currency' AND
						post_status = 'wc-completed' AND 
						cpm.meta_value = '$currency_code'
						
					group by
						p.ID
				";

				$tabledata = $wpdb->get_results( $prepared_query, OBJECT );

				if ( ! empty( $tabledata ) ) {
					$response = array(
						'status'    => 200,
						'message'   => 'Successfully generated!',
						'tabledata' => $tabledata,
					);

					header( 'Content-Type: application/json' );
					echo json_encode( $response );
					exit();
				} else {
					//if not valid error_message 
					$response = array(
						'status'    => 201,
						'message'   => 'Failed to generate!',
						'tabledata' => 'No data found!',
					);

					header( 'Content-Type: application/json' );
					echo json_encode( $response );
					exit();
				}


			} else {
				//if not valid error_message 
				$response = array(
					'status'  => 'error',
					'message' => 'error_message',
				);

				header( 'Content-Type: application/json' );
				echo json_encode( $response );
				exit();
			}

		}

	}

}

if ( is_admin() ) {
	$vpm_order_export = new COEEOrderExport();
}