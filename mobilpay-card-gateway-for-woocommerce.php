<?php
/*
Plugin Name: mobilPay Card Gateway for WooCommerce
Plugin URI:  https://github.com/santiagointeractive/mobilpay-card-gateway-for-woocommerce/
Description: Extends WooCommerce payment options by adding the mobilPay Card Gateway.
Version:     1.0
Author:      Santiago Interactive
Author URI:  http://santiagointeractive.com/
License:     GPL2
License URI: https://www.gnu.org/licenses/gpl-2.0.html
Domain Path: /languages
Text Domain: mobilpay

mobilPay Card Gateway for WooCommerce is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 2 of the License, or
any later version.
 
mobilPay Card Gateway for WooCommerce is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.
 
You should have received a copy of the GNU General Public License
along with mobilPay Card Gateway for WooCommerce.
If not, see https://www.gnu.org/licenses/gpl-2.0.html
*/

if ( ! defined( 'ABSPATH' ) ) { die; } // Cannot access this directly.

add_action('plugins_loaded', 'woocommerce_mobilpay_card_init', 0);

function woocommerce_mobilpay_card_init() {
    if ( !class_exists( 'WC_Payment_Gateway' ) ) return;

    // mobilPay Card Gateway Class
    class WC_Mobilpay_Card extends WC_Payment_Gateway {

        public function __construct() {
            $this->id                 = 'mobilpaycard';
            $this->method_title       = __( 'mobilPay Card', 'mobilpay' );
            $this->icon               = plugins_url( 'images/mobilpay.png' , __FILE__ );
            $this->has_fields         = false;
            $this->order_button_text  = __( 'Continue to mobilPay', 'mobilpay' );
            
            $this->init_form_fields();
            $this->init_settings();

            $this->title       = $this->settings['title'];
            $this->description = $this->settings['description'];
            $this->merchant_id = $this->settings['trans_key'];
            $this->environment = $this->settings['environment'];

            $this->notify_url  = WC()->api_request_url( 'WC_Mobilpay_Card' );

            add_action( 'woocommerce_api_wc_mobilpay_card', array( $this, 'check_mobilpaycard_response' ) );

            if ( version_compare( WOOCOMMERCE_VERSION, '2.0.0', '>=' ) ) {
            	add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'process_admin_options' ) );
            } else {
            	add_action( 'woocommerce_update_options_payment_gateways', array( &$this, 'process_admin_options' ) );
            }
            add_action( 'woocommerce_receipt_mobilpaycard', array( $this, 'mobilpay_receipt_page' ) );
            add_action( 'woocommerce_thankyou_mobilpaycard', array( $this, 'mobilpay_thankyou_page' ) );
        }

        function init_form_fields() {
			$this->form_fields = array(
				'enabled' => array(
					'title'	  => __( 'Enable/Disable', 'mobilpay' ),
					'label'	  => __( 'Enable mobilPay Card', 'mobilpay' ),
					'type'	  => 'checkbox',
					'default' => 'no',
				),
				'title' => array(
					'title'	   => __( 'Title', 'mobilpay' ),
					'type'	   => 'text',
					'desc_tip' => __( 'This controls the title which the user sees during checkout.', 'mobilpay' ),
					'default'  => __( 'Credit/Debit Card', 'mobilpay' ),
				),
				'description' => array(
					'title'	   => __( 'Description', 'mobilpay' ),
					'type'	   => 'textarea',
					'desc_tip' => __( 'This controls the description which the user sees during checkout.', 'mobilpay' ),
					'default'  => __( 'Pay with your credit/debit card via mobilPay 3D Secure gateway.', 'mobilpay' ),
				),
				'trans_key' => array(
					'title'	   => __( 'Merchant ID', 'mobilpay' ),
					'type'	   => 'text',
					'desc_tip' => __( 'Unique key assigned to your mobilPay merchant account for the payment process.', 'mobilpay' ),
				),
				'environment' => array(
					'title'		  => __( 'Sandbox', 'mobilpay' ),
					'label'		  => __( 'Enable mobilPay sandbox', 'mobilpay' ),
					'type'		  => 'checkbox',
					'description' => __( 'The mobilPay sandbox environment can be used to test payments.', 'mobilpay' ),
					'default'	  => 'no',
				)
			);
		}

        // Display admin panel options
        public function admin_options() {
            echo '<h3>'.__('mobilPay Card Gateway', 'mobilpay').'</h3>';
            echo '<table class="form-table">';
            $this->generate_settings_html();
            echo '</table>';
        }

        // Show description for this payment option
        function payment_fields() {
            if ($this->description) echo wpautop(wptexturize($this->description));
        }

        // Receipt page
        function mobilpay_receipt_page( $order ) {
        	echo '<div id="mobilpay_payment_form">';
            echo '<p>'.__('To complete your order please make the payment.', 'mobilpay').'</p>';
            echo $this->generate_mobilpay_card_form($order);
            echo '</div>';
        }

        // Thank you page
        function mobilpay_thankyou_page( $order_id ) {
        	$order = new WC_Order($order_id);
            if ( isset($_GET['orderId']) && $_GET['orderId']==$order_id && $order->status != 'processing' && $order->status != 'completed' ) {
            	echo '<div class="woocommerce-error">';
            	echo '<strong>'.__( 'Payment failed.', 'mobilpay' ).'</strong> '.__( 'To complete your order please make the payment.', 'mobilpay' );
				echo '<a class="button wc-forward" href="'.esc_url( $order->get_checkout_payment_url() ).'">'.__( 'Pay for order', 'mobilpay' ).'</a>';
				echo '</div>';
        	}
        }

        // Process the payment and return the result
        function process_payment( $order_id ) {
            $order = new WC_Order($order_id);
            return array(
            	'result'   => 'success',
            	'redirect' => $order->get_checkout_payment_url(true)
            );
        }

        // Check for valid mobilPay server callback
        function check_mobilpaycard_response() {
            global $woocommerce;

			require_once plugin_dir_path( __FILE__ ).'includes/request/class-mobilpay-abstract.php';
			require_once plugin_dir_path( __FILE__ ).'includes/request/class-mobilpay-card.php';
			require_once plugin_dir_path( __FILE__ ).'includes/request/class-mobilpay-notify.php';
			require_once plugin_dir_path( __FILE__ ).'includes/class-mobilpay-invoice.php';
			require_once plugin_dir_path( __FILE__ ).'includes/class-mobilpay-address.php';

			$errorCode 	  = 0;
			$errorType	  = Mobilpay_Payment_Request_Abstract::CONFIRM_ERROR_TYPE_NONE;
			$errorMessage = '';

			if ( strcasecmp($_SERVER['REQUEST_METHOD'], 'post') == 0 ) {
				if ( isset($_POST['env_key']) && isset($_POST['data']) ) {

					// Path to your merchant private key
					if ( $this->environment == "yes" ) {
						$privateKeyFilePath = plugin_dir_path( __FILE__ ).'certificates/sandbox.'.$this->merchant_id.'private.key';
					} else {
						$privateKeyFilePath = plugin_dir_path( __FILE__ ).'certificates/live.'.$this->merchant_id.'private.key';
					}

					try {
						$objPmReq = Mobilpay_Payment_Request_Abstract::factoryFromEncrypted($_POST['env_key'], $_POST['data'], $privateKeyFilePath);
						// Get order information
						$order = new WC_Order( $objPmReq->orderId );
						// action = status only if the associated error code is zero
						if ( $objPmReq->objPmNotify->errorCode == 0 ) {
					    	switch ($objPmReq->objPmNotify->action) {
								case 'confirmed':
									$errorMessage = $objPmReq->objPmNotify->errorMessage;
									if ( $order->status != 'processing' ) {
										// Mark order as paid
										$order->payment_complete();
										// Payment has been successful
										$order->add_order_note( $errorMessage );
		                				// Empty shopping cart
		                				$woocommerce->cart->empty_cart();
		                			}
							    	break;
								case 'confirmed_pending':
									$errorMessage = $objPmReq->objPmNotify->errorMessage;
							    	break;
								case 'paid_pending':
									$errorMessage = $objPmReq->objPmNotify->errorMessage;
							    	break;
								case 'paid':
									$errorMessage = $objPmReq->objPmNotify->errorMessage;
							    	break;
								case 'canceled':
									$errorMessage = $objPmReq->objPmNotify->errorMessage;
									$order->update_status( 'cancelled' );
							    	break;
								case 'credit':
									$errorMessage = $objPmReq->objPmNotify->errorMessage;
									$order->update_status( 'refunded' );
							   		break;
								default:
									$errorType	  = Mobilpay_Payment_Request_Abstract::CONFIRM_ERROR_TYPE_PERMANENT;
							    	$errorCode 	  = Mobilpay_Payment_Request_Abstract::ERROR_CONFIRM_INVALID_ACTION;
							    	$errorMessage = 'mobilPay refference action paramaters is invalid.';
							    break;
					    	}
						} else {
							// Rejected transaction error message
							$errorMessage = $objPmReq->objPmNotify->errorMessage;
							$order->add_order_note( $errorMessage );
						}
					}
					catch (Exception $e) {
						$errorType 	  = Mobilpay_Payment_Request_Abstract::CONFIRM_ERROR_TYPE_TEMPORARY;
						$errorCode	  = $e->getCode();
						$errorMessage = $e->getMessage();
					}

				} else {
					$errorType 	  = Mobilpay_Payment_Request_Abstract::CONFIRM_ERROR_TYPE_PERMANENT;
					$errorCode	  = Mobilpay_Payment_Request_Abstract::ERROR_CONFIRM_INVALID_POST_PARAMETERS;
					$errorMessage = 'mobilPay posted invalid parameters.';
				}
			} else {
				$errorType 	  = Mobilpay_Payment_Request_Abstract::CONFIRM_ERROR_TYPE_PERMANENT;
				$errorCode	  = Mobilpay_Payment_Request_Abstract::ERROR_CONFIRM_INVALID_POST_METHOD;
				$errorMessage = 'Invalid request method for payment confirmation.';
			}

			// Generate XML response
			ob_start();
			echo "<?xml version=\"1.0\" encoding=\"utf-8\" ?>\n";
			if ( $errorCode == 0 ) {
				echo "<crc>{$errorMessage}</crc>";
			} else {
				echo "<crc error_type=\"{$errorType}\" error_code=\"{$errorCode}\">{$errorMessage}</crc>";
			}
			ob_flush();
            exit;
    	}

		// Generate mobilPay payment request
		public function generate_mobilpay_card_form( $order_id ) {
			global $woocommerce;
			// Get order information
			$order = new WC_Order( $order_id );

			// Decide which URL to post to
			$environment_url = ( $this->environment == "yes" )
							   ? 'http://sandboxsecure.mobilpay.ro'
							   : 'https://secure.mobilpay.ro';

			require_once plugin_dir_path( __FILE__ ).'includes/request/class-mobilpay-abstract.php';
			require_once plugin_dir_path( __FILE__ ).'includes/request/class-mobilpay-card.php';
			require_once plugin_dir_path( __FILE__ ).'includes/class-mobilpay-invoice.php';
			require_once plugin_dir_path( __FILE__ ).'includes/class-mobilpay-address.php';

			// Path to your merchant public certificate
			if ( $this->environment == "yes" ) {
				$x509FilePath = plugin_dir_path( __FILE__ ).'certificates/sandbox.'.$this->merchant_id.'.public.cer';
			} else {
				$x509FilePath = plugin_dir_path( __FILE__ ).'certificates/live.'.$this->merchant_id.'.public.cer';
			}

			try {
				srand((double) microtime() * 1000000);
				$objPmReqCard = new Mobilpay_Payment_Request_Card();
				// Merchant account signature - generated by mobilPay for every merchant account
				$objPmReqCard->signature  = $this->merchant_id;
				// Order ID
				$objPmReqCard->orderId 	  = str_replace( "#", "", $order->get_order_number() );
				// Where mobilPay will send the payment result - this URL will always be called first
				$objPmReqCard->confirmUrl = $this->notify_url;
				// Where mobilPay redirects the client once the payment process is finished
				$objPmReqCard->returnUrl  = $this->get_return_url($order);

				// Payment information: currency, amount, details
				$objPmReqCard->invoice           = new Mobilpay_Payment_Invoice();
				$objPmReqCard->invoice->currency = 'RON'; // currency accepted by mobilPay
				$objPmReqCard->invoice->amount	 = $order->order_total;
				$objPmReqCard->invoice->details	 = __('Credit/debit card payment via mobilPay', 'mobilpay');
				
				// Billing details
				$billingAddress              = new Mobilpay_Payment_Address();
				if ( !empty($order->billing_company) ) {
					$billingAddress->type    = 'company';	
				} else {
					$billingAddress->type    = 'person';
				}
				$billingAddress->firstName	 = $order->billing_first_name;
				$billingAddress->lastName	 = $order->billing_last_name;
				$billingAddress->address	 = $order->billing_address_1.', '.$order->billing_address_2.', '.$order->billing_city.', '.$order->billing_state.', '.$order->billing_postcode.', '.$order->billing_country;
				$billingAddress->email		 = $order->billing_email;
				$billingAddress->mobilePhone = $order->billing_phone;

				$objPmReqCard->invoice->setBillingAddress($billingAddress);

				// Shipping details
				if ( !empty($order->shipping_address_1) ) {
					$shippingAddress 			  = new Mobilpay_Payment_Address();
					if ( !empty($order->shipping_company) ) {
						$shippingAddress->type    = 'company';	
					} else {
						$shippingAddress->type    = 'person';
					}
					$shippingAddress->firstName	  = $order->shipping_first_name;
					$shippingAddress->lastName	  = $order->shipping_last_name;
					$shippingAddress->address     = $order->shipping_address_1.', '.$order->shipping_address_2.', '.$order->shipping_city.', '.$order->shipping_state.', '.$order->shipping_postcode.', '.$order->shipping_country;
					$shippingAddress->email		  = $order->shipping_email;
					$shippingAddress->mobilePhone = $order->shipping_phone;

					$objPmReqCard->invoice->setShippingAddress($shippingAddress);
				}

				// Encrypt data
				$objPmReqCard->encrypt($x509FilePath);
			}
			catch (Exception $e) {
			}

			wc_enqueue_js( '
			    $.blockUI({
			        message: "' . esc_js( __( 'Redirecting to mobilPay 3D Secure payment gateway to make the payment.', 'mobilpay' ) ) . '",
			        baseZ: 99999,
			        overlayCSS:
			        {
			            background: "#fff",
			            opacity: 1,
			            cursor: "wait"
			        },
			        css: {
			            padding:         "30px",
			            zindex:          "9999999",
			            textAlign:       "center",
			            color:           "#333",
			            border:          "3px solid #aaa",
			            backgroundColor: "#fff",
			            cursor:          "wait"
			        }
			    });
			jQuery("#submit_mobilpay_card_payment_form").click();
			' );

			?>
			<form name="frmPaymentRedirect" method="post" action="<?php echo $environment_url; ?>">
				<input type="hidden" name="env_key" value="<?php echo $objPmReqCard->getEnvKey();?>"/>
				<input type="hidden" name="data" value="<?php echo $objPmReqCard->getEncData();?>"/>
				<input type="submit" class="button" id="submit_mobilpay_card_payment_form" value="<?php echo __( 'Pay for order', 'mobilpay' ); ?>" /> <a class="button" href="<?php echo esc_url( $order->get_cancel_order_url() ); ?>"><?php echo __( 'Cancel order', 'mobilpay' ); ?></a>
				<script type="text/javascript">
					jQuery("#mobilpay_payment_form").hide();
				</script>
			</form>

		<?php
		}
	}

	// Add the gateway to WooCommerce
	function woocommerce_add_mobilpay_card_gateway( $methods ) {
	    $methods[] = 'WC_Mobilpay_Card';
	    return $methods;
	}
	add_filter('woocommerce_payment_gateways', 'woocommerce_add_mobilpay_card_gateway' );

	// Add custom action links
	add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), 'mobilpay_card_action_links' );
	function mobilpay_card_action_links( $links ) {
		$plugin_links = array(
			'<a href="' . admin_url( 'admin.php?page=wc-settings&tab=checkout' ) . '">' . __( 'Settings', 'mobilpay' ) . '</a>',
		);
		return array_merge( $plugin_links, $links );	
	}

	// Load plugin text domain
	function mobilpaycard_load_textdomain() {
	    load_plugin_textdomain( 'mobilpay', FALSE, basename( dirname( __FILE__ ) ) . '/languages/' );
	}
	add_action( 'plugins_loaded', 'mobilpaycard_load_textdomain' );

}
