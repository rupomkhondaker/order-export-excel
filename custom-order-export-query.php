<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class COEEOrderExport {

	public function __construct() {
		add_action( 'admin_menu', array( $this, 'coee_order_export_add_plugin_page' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'coee_order_export_enquee' ) );
		add_action( 'wp_ajax_coee_view_export_order', array( $this, 'coee_view_export_order_data' ) );
	}

	public function coee_order_export_enquee() {
		$wp_scripts = wp_scripts();
		wp_enqueue_script( 'jquery-ui-datepicker' );
		wp_enqueue_style( 'jquery-ui-css',
			'https://ajax.googleapis.com/ajax/libs/jqueryui/' . $wp_scripts->registered['jquery-ui-core']->ver . '/themes/overcast/jquery-ui.css',
			false,
			'1.0.1',
			false );
	}

	public function coee_order_export_add_plugin_page() {

		add_submenu_page(
			'exclutips-settings',
			'Order Export Settings', // page_title
			'Order Export ', // menu_title
			'manage_options', // capability
			'coee-order-export', // menu_slug
			array( &$this, 'coee_order_export_create_admin_page' )
		);
	}

	public function coee_order_export_create_admin_page() { ?>

        <div class="wrap catbox-area-admin" style="width: 100%; height:auto;background: #fff;padding: 27px 50px;">
            <h2>Order Export</h2>

            <div style="display:flex;margin-top:30px">
                <label for="order_from_date">From</label>
                <input id="order_from_date" class="datepicker" type="text" name="order_from_date" autocomplete="off"/>
                <label for="order_to_date">To</label>
                <input id="order_to_date" class="datepicker" type="text" name="order_to_date" autocomplete="off"/>

                <label for="currency_code">Currency:</label>

                <select id="currency_code" name="currency_code">
					<?php $currency_code = get_woocommerce_currency(); ?>
                    <option value="<?php echo $currency_code; ?>"><?php echo $currency_code; ?></option>
                </select>

                <label for="order_status">Order Status:</label>
                <select id="order_status" name="order_status">
                    <option value="all">All</option>
					<?php
					$statuses = wc_get_order_statuses();
					foreach ( $statuses as $status_slug => $status_name ) {
						echo '<option value="' . esc_attr( $status_slug ) . '">' . esc_html( $status_name ) . '</option>';
					}
					?>
                </select>

                <button id="generate_order" name="generate_order" class="button button-secondary margin-right-10">View Data</button>
                <button id="exportCSV" class="button button-primary align-right" style="display:none;">Export Report</button>
            </div>

            <table class="table table-bordered" id="OrderExportResults" style="display:none;" width="85%"></table>

            <script>
                jQuery(document).ready(function ($) {
                    // Get current date
                    const currentDate = new Date();
                    const formattedDate = currentDate.getFullYear() + '-' + ('0' + (currentDate.getMonth() + 1)).slice(-2) + '-' + ('0' + currentDate.getDate()).slice(-2);

                    $(".datepicker").datepicker({
                        dateFormat: 'yy-mm-dd',
                        changeMonth: true,
                        changeYear: true,
                        defaultDate: formattedDate
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
                        const order_status = $('#order_status').val();
                        const button = $(this);

                        const orderTable = $('#OrderExportResults');
                        const exportBTN = $('#exportCSV');
                        orderTable.html("");
                        exportBTN.hide();
                        $.ajax({
                            type: "post",
                            dataType: "json",
                            url: ajaxurl,
                            data: {
                                action: 'coee_view_export_order',
                                order_from_date: order_from_date,
                                order_to_date: order_to_date,
                                currency_code: currency_code,
                                order_status: order_status,
                            },
                            beforeSend: function () {
                                button.html('Please Wait.');
                            },
                            success: function (response) {

                                if (parseInt(response.status) === 200) {

                                    let total_price = 0;
                                    //append response into the table
                                    let orders = '';
                                    orders += '<tr>';
                                    orders += '<th class="remData" width="5%">Invoice</th>';
                                    orders += '<th class="remData" width="5%">Name</th>';
                                    orders += '<th class="remData" width="10%">Address</th>';
                                    orders += '<th class="remData" width="5%">Phone</th>';
                                    orders += '<th class="remData" width="25%">Products</th>';
                                    orders += '<th class="remData" width="15%">Customer Note</th>';
                                    orders += '<th class="remData" width="15%">Admin Note</th>';
                                    orders += '<th class="remData" width="10%">Status</th>';
                                    orders += '<th class="remData" width="10%">Date</th>';
                                    orders += '<th class="remData" width="10%">Amount</th>';
                                    orders += '</tr>';

                                    $.each(response.tabledata, function (key, value) {
                                        orders += '<tr>';
                                        orders += '<td>' + value.order_id + '</td>';
                                        orders += '<td>' + value.first_name + ' </td>';
                                        orders += '<td>' + value.billing_address + '</td>';
                                        orders += '<td>' + value.phone + '</td>';
                                        orders += '<td>' + value.order_items + '</td>';
                                        orders += '<td>' + value.customer_note + '</td>';
                                        orders += '<td>' + value.admin_note + '</td>';
                                        orders += '<td>' + value.order_status + '</td>';
                                        orders += '<td>' + value.order_created_on + '</td>';
                                        orders += '<td>' + value.order_total + '</td>';
                                        orders += '</tr>';
                                    });

                                    //Grand Total
                                    $.each(response.tabledata, function (key, value) {
                                        total_price += parseFloat(value.order_total);
                                    });

                                    orders += '<tr>';
                                    orders += '<td colspan="8"><b class="alignright">Grand Total</b></td>';
                                    orders += '<td></td>';
                                    orders += '<td><strong>' + (total_price).toFixed(2) + '</strong></td>';
                                    orders += '</tr>';

                                    orderTable.append(orders);

                                    setTimeout(function () {
                                        button.html('View Data');
                                        orderTable.show();
                                        exportBTN.show();
                                    }, 500);

                                } else {
                                    orderTable.append('<tr colspan="10"><td>' + response.message + '</td></tr>');
                                    button.html('View Data');
                                    orderTable.show();
                                }

                            },
                            error: function (errorThrown) {
                                alert(errorThrown);
                            }
                        });

                    });
                });
            </script>
            <style>
                #generate_order {
                    margin-right: 5px;
                    margin-left: 5px;
                }

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

	/** ----------------------------------------------------------------
	 * High performance Order Export Query
	 * ----------------------------------------------------------------*/

	public function coee_view_export_order_data() {

		// Get the value of the coupon code
		$currency_code = $_REQUEST['currency_code'];
		$order_status  = $_REQUEST['order_status'];
		$from_date     = $_REQUEST['order_from_date'];
		$to_date       = $_REQUEST['order_to_date'];

		// Check Date to make sure is not empty
		if ( empty( $from_date ) || empty( $to_date ) ) {
			$response = array(
				'status'    => 201,
				'message'   => 'Please select date range.',
				'tabledata' => array( '0' => 'Please select Date Range!' ),
			);
			header( 'Content-Type: application/json' );
			echo json_encode( $response );
			exit();
		}

		if ( isset( $currency_code ) && isset( $order_status ) ) {

			global $wpdb;

			$from_date = date( $from_date );
			$to_date   = date( 'Y-m-d', strtotime( $to_date . " +1 days" ) );

			// Determine if HPOS is enabled
			$wc_orders       = $wpdb->prefix . 'wc_orders';
            $wc_order_meta    = $wpdb->prefix . 'wc_order_meta';
			$wp_woocommerce_order_items = $wpdb->prefix . 'woocommerce_order_items';

			$prepared_query = "
			
			                    SELECT 
                                    o.id AS order_id,
                                    DATE(o.date_created_gmt) AS order_created_on,
                                    o.status AS order_status,
                                    o.currency AS currency,
                                    (
                                        SELECT GROUP_CONCAT(order_item_name SEPARATOR '|')
                                        FROM $wp_woocommerce_order_items
                                        WHERE order_id = o.id
                                    ) AS order_items,
                                
                                    o.total_amount AS order_total,
                                    o.customer_note AS _customer_note,
                                    MAX(CASE WHEN onum.meta_key = '_order_number' THEN onum.meta_value END) AS order_number,
                                    MAX(CASE WHEN address.meta_key = '_billing_address_index' THEN address.meta_value END) AS billing_address
                                    
                                FROM $wc_orders AS o 
                                
                                LEFT JOIN $wc_order_meta AS om ON o.id = om.order_id
                                LEFT JOIN $wc_order_meta AS onum ON o.id = onum.order_id
                                LEFT JOIN $wc_order_meta AS address ON o.id = address.order_id 
                                
                                WHERE 
                                    o.date_created_gmt BETWEEN DATE('$from_date') AND DATE('$to_date')
                                    AND o.currency = '$currency_code'
                                    AND o.status = '$order_status'
                                   " . ( $order_status !== 'all' ? "AND o.status = '$order_status'" : "" ) . "
                                GROUP BY 
                                    o.id
                                    
                                ORDER BY 
                                    o.date_created_gmt ASC;
                            ";




			// You may need to further sanitize and prepare the variables for security.
			$prepared_query = $wpdb->prepare( $prepared_query, $from_date, $to_date, $currency_code, $order_status );
			$results_data   = $wpdb->get_results( $prepared_query, OBJECT );

			if ( ! empty( $results_data ) ) {
				$response = array(
					'status'    => 200,
					'message'   => 'Successfully generated!',
					'tabledata' => $results_data,
				);

				header( 'Content-Type: application/json' );
				echo json_encode( $response );
				exit();
			} else {
				//if not valid error_message
				$response = array(
					'status'    => 201,
					'message'   => 'Order Not Found!',
					'tabledata' => array( '0' => 'No data found!.' ),
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

if ( is_admin() ) {
	$coee_order_export = new COEEOrderExport();
}
?>
