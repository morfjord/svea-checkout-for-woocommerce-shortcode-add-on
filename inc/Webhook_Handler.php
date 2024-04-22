<?php

namespace Svea_Checkout_For_Woocommerce;

use Svea_Checkout_For_Woocommerce\Models\Svea_Order;
use Svea_Checkout_For_Woocommerce\Session_Table;

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * Class Webhook_Handler
 */
class Webhook_Handler {

	/**
	 * Svea order id
	 *
	 * @var int
	 */
	private $svea_order_id = 0;

	/**
	 * Token to get customer session from
	 *
	 * @var string
	 */
	private $token;

	/**
	 * Gateway instance
	 *
	 * @var WC_Gateway_Svea_Checkout
	 */
	private $gateway;

	/**
	 * Svea order response
	 *
	 * @var array
	 */
	private static $svea_order;

	/**
	 * Cart session
	 *
	 * @var array
	 */
	public static $cart_session;

	/**
	 * User id used in validation
	 *
	 * @var mixed
	 */
	public $user_id;

	/**
	 * Cache key
	 */
	public const SCO_CACHE_KEY = '_sco_session_cache_';

	/**
	 * Start time of request
	 *
	 * @var int
	 */
	public $start_time = 0;

	/**
	 * Init function
	 *
	 * @return void
	 */
	public function init() {
		add_action( 'woocommerce_api_svea_checkout_push', [ $this, 'process_push' ] );
		add_action( 'woocommerce_api_svea_checkout_push_recurring', [ $this, 'process_push_recurring' ] );
		add_action( 'woocommerce_api_svea_validation_callback', [ $this, 'validate_order' ] );
		add_action( 'woocommerce_api_svea_webhook', [ $this, 'handle_webhook' ] );
	}

	/**
	 * Get the svea order response
	 *
	 * @return array
	 */
	public static function get_svea_order() {
		return self::$svea_order;
	}

	/**
	 * Get the cart session
	 *
	 * @param string $token
	 * @param bool $load_cart
	 * @return array
	 */
	public static function get_cart_session( $token, $load_cart = true ) {
		$session_key = Session_Table::get_session_key_by_sco_token( $token );

		if ( empty( $session_key ) ) {
			return;
		}

		// Treat the request as if it was the logged in user if applicable
		if ( is_numeric( $session_key ) ) {
			$usr = get_user_by( 'ID', $session_key );

			if ( $usr ) {
				wp_set_current_user( $session_key );
			}
		}

		do_action( 'woocommerce_sco_cart_session_before' );

		$cookie_value = self::spoof_cookie( $session_key );

		$_COOKIE[ apply_filters( 'woocommerce_cookie', 'wp_woocommerce_session_' . COOKIEHASH ) ] = $cookie_value;

		WC()->session = new \WC_Session_Handler();
		WC()->session->init();

		if ( ! $load_cart ) {
			return;
		}

		/**
		 * So here we remove the hook so "did_action" wont return true no more.
		 * This is in order to trick WC into loading the cart from the session again.
		 * But this time, since we set a new cookie we're loading the users cart
		 */
		global $wp_actions;
		unset( $wp_actions['woocommerce_load_cart_from_session'] );

		do_action( 'woocommerce_sco_before_session_load_cart' );

		WC()->customer = null;
		WC()->initialize_cart();

		// Simply trigger the hook in order for the cart session to load
		WC()->cart->get_cart();

		do_action( 'woocommerce_sco_cart_session_after' );
	}

	/**
	 * Spoof cookie value
	 *
	 * @param string $session_key
	 * @return string
	 */
	public static function spoof_cookie( $session_key ) {
		$session_expiring   = time() + intval( apply_filters( 'wc_session_expiring', 60 * 60 * 47 ) ); // 47 Hours.
		$session_expiration = time() + intval( apply_filters( 'wc_session_expiration', 60 * 60 * 48 ) ); // 48 Hours.
		$time_diff = $session_expiration - $session_expiring;

		// Get current session_expiry
		global $wpdb;
		$session_expiry = $wpdb->get_var( $wpdb->prepare( "SELECT session_expiry FROM {$wpdb->prefix}woocommerce_sessions WHERE session_key = %s", $session_key ) );

		$to_hash           = $session_key . '|' . $session_expiry;
		$cookie_hash       = hash_hmac( 'md5', $to_hash, wp_hash( $to_hash ) );
		return $session_key . '||' . ( $session_expiry ?? 0 ) . '||' . ( $session_expiry - $time_diff ) . '||' . $cookie_hash;
	}

	/**
	 * Check if the current session already is in use
	 *
	 * @return void
	 */
	public function check_session() {
		$last_validation = WC()->session->get( 'sco_validation_time', 0 );

		// Reserve the session for 15 seconds
		if ( time() - $last_validation < 15 ) {
			$this->send_response( esc_html__( 'Something went wrong, please try again in a moment', 'svea-checkout-for-woocommerce' ) );
		}

		WC()->session->set( 'sco_validation_time', time() );
	}

	/**
	 * Get caller IP
	 *
	 * @return string
	 */
	private function get_referer_ip() {
		if ( ! empty( $_SERVER['HTTP_CLIENT_IP'] ) ) {
			$ip = $_SERVER['HTTP_CLIENT_IP'];
		} else if ( ! empty( $_SERVER['HTTP_X_FORWARDED_FOR'] ) ) {
			$ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
		} else {
			$ip = $_SERVER['REMOTE_ADDR'];
		}

		return $ip;
	}

	/**
	 * Validate that the request comes from Svea servers
	 *
	 * @return void
	 */
	public function validate_referer() {
		if ( $this->gateway->get_option( 'use_ip_restriction' ) !== 'yes' ) {
			return;
		}
		$ip_address = $this->get_referer_ip();
		$ref_ip = ip2long( $ip_address );

		$high_ip_range_1 = ip2long( '193.13.207.255' );
		$low_ip_range_1 = ip2long( '193.13.207.0' );
		$high_ip_range_2 = ip2long( '193.105.138.255' );
		$low_ip_range_2 = ip2long( '193.105.138.0' );

		$in_range_1 = ( $ref_ip <= $high_ip_range_1 && $ref_ip >= $low_ip_range_1 );
		$in_range_2 = ( $ref_ip <= $high_ip_range_2 && $ref_ip >= $low_ip_range_2 );

		if ( ! $in_range_1 && ! $in_range_2 ) {
			status_header( 403 );

			WC_Gateway_Svea_Checkout::log( 'A non allowed IP tried to make a webhook request: ' . $ip_address );

			wc_add_notice( esc_html__( 'IP not allowed', 'svea-checkout-for-woocommerce' ), 'error' );
			$this->send_response( wc_print_notices( true ) );
		}
	}

	/**
	 * Handle the webhook sent from Svea
	 *
	 * @return void
	 */
	public function handle_webhook() {
		$this->gateway = WC_Gateway_Svea_Checkout::get_instance();

		$raw_webhook = file_get_contents( 'php://input' );

		if ( empty( $raw_webhook ) ) {
			$this->gateway::log( 'Empty webhook, no content' );
			exit;
		}

		$webhook_data = json_decode( $raw_webhook );
		$webhook_data->description = json_decode( $webhook_data->description );
		// phpcs:disable WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
		$this->gateway::log( sprintf( 'Webhook callback received for Svea cart (%s) received', $webhook_data->orderId ) );

		$this->validate_referer();

		if ( empty( $webhook_data->orderId ) ) {
			$this->gateway::log( 'No orderID was found. Aborting' );
			exit;
		} else {
			$wc_order = self::get_order_by_svea_id( $webhook_data->orderId );

			if ( empty( $wc_order ) ) {
				$this->gateway::log( sprintf( 'Received webhook but the order does not exists (%s)', $webhook_data->orderId ) );
				exit;
			}

			// Save the data
			if ( ! empty( $webhook_data->type ) ) {
				$wc_order->update_meta_data( '_sco_nshift_type', $webhook_data->type );
			}

			if ( ! empty( $webhook_data->description->tmsReference ) ) {
				$wc_order->update_meta_data( '_sco_nshift_tms_ref', $webhook_data->description->tmsReference );
			}

			if ( ! empty( $webhook_data->description->selectedShippingOption ) ) {
				$wc_order->update_meta_data( '_sco_nshift_carrier_id', $webhook_data->description->selectedShippingOption->id );
				$wc_order->update_meta_data( '_sco_nshift_carrier_name', $webhook_data->description->selectedShippingOption->carrier );
			}

			$wc_order->save();
		}

		$svea_order = new Svea_Order( $wc_order );

		try {
			self::$svea_order = $svea_order->get( $webhook_data->orderId );
		} catch ( \Exception $e ) {
			WC_Gateway_Svea_Checkout::log( sprintf( 'Error in push webhook when getting order id %s. Message from Svea: %s' ), $this->svea_order_id, $e->getMessage() );
			status_header( 404 );
			exit;
		}

		if ( isset( self::$svea_order['ShippingInformation']['ShippingProvider']['ShippingOption']['ShippingFee'] ) ) {
			// We've got the fee, now verify that it's the same
			$shipping_fee = self::$svea_order['ShippingInformation']['ShippingProvider']['ShippingOption']['ShippingFee'];
			$shipping_fee = $shipping_fee / 100;

			$current_shipping = (float) $wc_order->get_shipping_total() + (float) $wc_order->get_shipping_tax();

			// If the shipping has changed (or the visitor tampered with the data) a re-calculation has to be made
			if ( (float) $current_shipping !== (float) $shipping_fee ) {
				$this->sync_shipping_fee( $wc_order, $shipping_fee );

				$wc_order->calculate_totals();

				do_action( 'woocommerce_sco_before_webhook_update_shipping', $wc_order );

				$wc_order->save();

				do_action( 'woocommerce_sco_after_webhook_update_shipping', $wc_order );
			}
		}

		exit;
	}

	/**
	 * Validate order
	 *
	 * @return void
	 */
	public function validate_order() {
		// Set start time in order to see how long the request takes
		$this->start_time = time();

		// Ensure nothing gets printed
		ob_start();
		$this->gateway = WC_Gateway_Svea_Checkout::get_instance();

		if ( ! isset( $_GET['svea_order_id'], $_GET['svea_token'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			return;
		}

		$this->svea_order_id = sanitize_text_field( $_GET['svea_order_id'] ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$this->token = sanitize_text_field( $_GET['svea_token'] ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended

		$this->gateway::log( sprintf( 'Validation callback for Svea order ID (%s) received', $this->svea_order_id ) );

		$this->validate_referer();

		add_filter( 'woocommerce_checkout_posted_data', [ $this, 'setup_post_data' ] );

		// Force generation of passwords if needed
		add_filter(
			'pre_option_woocommerce_registration_generate_password',
			[
				Template_Handler::class,
				'_return_yes',
			]
		);

		self::get_cart_session( $this->token );

		$this->user_id = get_current_user_id();
		add_action( 'woocommerce_checkout_update_user_meta', [ $this, 'check_login' ] );

		$args = [
			'currency' => WC()->session->get( 'sco_currency' ),
			'country'  => WC()->session->get( 'sco_order_country_code' ),
		];

		if ( $args['currency'] === null ) {
			$this->gateway::log( sprintf( 'Could not get currency from session. Svea order ID: %s', $this->svea_order_id ) );
		}

		if ( $args['country'] === null ) {
			$this->gateway::log( sprintf( 'Could not get country from session. Svea order ID: %s', $this->svea_order_id ) );
		}

		$s_order = new Svea_Order( $args );

		try {
			self::$svea_order = $s_order->get( $this->svea_order_id );
		} catch ( \Exception $e ) {
			$this->gateway::log( sprintf( 'Could not get order from Svea: %s. Svea Order ID: %s', $e->getMessage(), $this->svea_order_id ) );
			WC()->session->__unset( 'sco_validation_time' );
			$this->send_response( esc_html__( 'Could not contact Svea. Please try again', 'svea-checkout-for-woocommerce' ) );
		}

		if ( ! isset( self::$svea_order['OrderId'] ) ) {
			$this->gateway::log( sprintf( 'Could not get order from Svea, Order ID: %s', $this->svea_order_id ) );
			WC()->session->__unset( 'sco_validation_time' );
			$this->send_response( esc_html__( 'Could not contact Svea. Please try again', 'svea-checkout-for-woocommerce' ) );
		}

		if ( strtoupper( self::$svea_order['Status'] ) !== 'CREATED' ) {
			$this->send_response( esc_html__( 'This order is not open no longer. Please reload the checkout.', 'svea-checkout-for-woocommerce' ) );
		}

		// Pass the nonce validation
		$nonce = wp_create_nonce( 'woocommerce-process_checkout' );
		$_REQUEST['woocommerce-process-checkout-nonce'] = $nonce;

		do_action( 'woocommerce_sco_process_checkout_before', self::$svea_order );
		add_action( 'woocommerce_created_customer', [ $this, 'user_created_by_checkout_flag' ] );

		$this->spoof_global_post();

		try {
			$this->gateway::log( sprintf( 'Processing checkout for Svea Order ID: %s', $this->svea_order_id ) );

			/**
			 * The function will exit with a json response
			 *
			 * @see WC_Gateway_Svea_Checkout::process_payment()
			 */
			WC()->checkout()->process_checkout();

		} catch ( \Exception $e ) {
			// Log
			$this->gateway::log( $this->gateway::GATEWAY_ID, 'Error during checkout process in push: ' . $e->getMessage() );
			WC()->session->__unset( 'sco_validation_time' );
		}

		$this->send_response( wc_print_notices( true ) );
	}

	/**
	 * Setup all variables in the superglobal "post" variabel
	 *
	 * @return void
	 */
	public function spoof_global_post() {
		// Get the values that Svea populates
		$data = apply_filters( 'woocommerce_checkout_posted_data', [] );

		foreach ( $data as $key => $value ) {
			$_POST[ $key ] = $value;
		}
	}

	/**
	 * Save user meta saying this order was created during checkout
	 *
	 * @param int $customer_id
	 * @return void
	 */
	public function user_created_by_checkout_flag( $customer_id ) {
		update_user_meta( $customer_id, 'created_during_svea_checkout', true );
	}

	/**
	 * Check if the user logged in (or out) during checkout
	 *
	 * @return void
	 */
	public function check_login() {
		if ( get_current_user_id() !== $this->user_id ) {
			add_action( 'shutdown', [ $this, 'catch_key_before_shutdown' ], 25 );
		}
	}

	/**
	 * Save data from the session
	 *
	 * @return void
	 */
	public function catch_key_before_shutdown( $old_id ) {
		if ( $old_id !== Session_Table::get_session_key_by_sco_token( $this->token ) ) {
			Session_Table::update_wc_session_key( $this->token, WC()->session->get_customer_id() );
		}
	}

	/**
	 * Get an order based on Svea order ID
	 *
	 * @param int $svea_order_id
	 * @return \WC_Order|false
	 */
	public static function get_order_by_svea_id( int $svea_order_id ) {
		$args = [
			'status'     => 'any',
			'limit'      => 1,
			'meta_key'   => '_svea_co_order_id',
			'meta_value' => $svea_order_id,
		];

		$orders = wc_get_orders( $args );

		if ( ! empty( $orders ) ) {
			return $orders[0];
		}

		return false;
	}

	/**
	 * Check for an existing order
	 *
	 * @return bool
	 */
	public function check_existing_order() {
		$order = self::get_order_by_svea_id( $this->svea_order_id );

		if ( ! empty( $order ) ) {
			$this->gateway::log( sprintf( 'Validation callback found existing order. Svea order ID: %s', $this->svea_order_id ) );

			$wc_order = wc_get_order( $order );

			// Since the order already exists we'll just say we passed the validation
			$this->send_response( 'Order found', true, $wc_order->get_order_number() );
		}

		return false;
	}

	/**
	 * Send validation response
	 *
	 * @param string $msg Message
	 * @param string $valid Valid or not
	 * @param string $order_number Client order number
	 * @return void
	 */
	public function send_response( $msg, $valid = false, $order_number = '' ) {
		$response = [
			'Valid'             => $valid,
			'Message'           => $msg,
			'ClientOrderNumber' => $order_number,
		];

		$this->gateway::log( sprintf( 'Sending validation response: %s', var_export( $response, true ) ) ); // phpcs:ignore

		$ob = ob_get_clean();
		if ( ! empty( $ob ) ) {
			$this->gateway::log( sprintf( 'Output buffer catched the following: %s', var_export( $ob, true ) ) ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_var_export
		}

		wp_send_json( $response );
		die;
	}

	/**
	 * Setup variables in the $_POST array an cart session since WooCommerce's expects it
	 *
	 * @return array
	 */
	public function setup_post_data( $data ) {
		$nonce = wp_create_nonce( 'woocommerce-process_checkout' );
		$_REQUEST['woocommerce-process-checkout-nonce'] = $nonce;

		// Map fields from Svea order
		$fields = $this->gateway::get_checkout_fields_mapping();
		foreach ( $fields as $wc_name => $svea_field_name ) {
			$value = Helper::delimit_array( self::$svea_order, $svea_field_name );

			if ( $value ) {
				$data[ $wc_name ] = $value;
			}
		}

		// Set dummy names as this will change when finalized
		if ( self::$svea_order['Customer']['IsCompany'] ) {
			$data['billing_company'] = self::$svea_order['BillingAddress']['FullName'];
			$data['billing_first_name'] = self::$svea_order['BillingAddress']['FullName'];
			$data['billing_last_name'] = ' ';

			$data['shipping_first_name'] = self::$svea_order['ShippingAddress']['FullName'];
			$data['shipping_last_name'] = ' ';
			$data['shipping_company'] = self::$svea_order['ShippingAddress']['FullName'];
		}

		$data = apply_filters( 'woocommerce_sco_checkout_fields_data', $data );

		// Map from local session
		$comments = WC()->session->get( 'sco_order_comments' );
		if ( ! empty( $comments ) ) {
			$data['order_comments'] = $comments;
		}

		$data['payment_method'] = $this->gateway::GATEWAY_ID;
		$data['shipping_method'] = WC()->session->get( 'chosen_shipping_methods' );

		if ( ! is_user_logged_in() && ( WC()->checkout()->is_registration_required() || WC()->session->get( '_sco_createaccount' ) ) ) {
			// Because of security risks we don't allow this value to be stored in the session
			$data['account_password'] = '';
			$data['createaccount'] = WC()->session->get( '_sco_createaccount' );
		}

		if ( WC()->session->get( '_sco_account_username' ) ) {
			$data['account_username'] = WC()->session->get( '_sco_account_username' );
		}

		// We can force this to be true since you've accepted the terms when clicking "Finish purchase"
		$data['terms'] = 'on';

		$data['ship_to_different_address'] = ( $data['billing_address_1'] !== $data['shipping_address_1'] );

		// Also add additional fields
		$additional_fields = WC()->checkout->get_checkout_fields( 'order' );
		$keys = ! empty( $additional_fields ) ? array_keys( $additional_fields ) : [];
		$keys = apply_filters( 'woocommerce_sco_update_order_info_keys', $keys );

		foreach ( $keys as $key ) {
			$val = WC()->session->get( '_sco_' . $key );

			if ( ! is_null( $val ) ) {
				$data[ $key ] = WC()->session->get( '_sco_' . $key );
			}
		}

		// Fill theese in as empty strings if they're empty
		if ( empty( $data['billing_company'] ) ) {
			$data['billing_company'] = '';
		}

		if ( empty( $data['shipping_company'] ) ) {
			$data['shipping_company'] = '';
		}

		return apply_filters( 'woocommerce_sco_order_post_params', $data );
	}

	/**
	 * Process push notifications for the order
	 *
	 * @return void
	 */
	public function process_push() {
		$this->svea_order_id = isset( $_GET['svea_order_id'] ) ? sanitize_text_field( $_GET['svea_order_id'] ) : 0; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$this->token = isset( $_GET['svea_token'] ) ? sanitize_text_field( $_GET['svea_token'] ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$this->gateway = WC_Gateway_Svea_Checkout::get_instance();

		if ( ! $this->svea_order_id ) {
			status_header( 400 );
			esc_html_e( 'Missing params', 'svea-checkout-for-woocommerce' );
			die;
		}

		if ( $this->gateway->get_option( 'use_ip_restriction' ) === 'yes' ) {
			$this->validate_referer();
		}

		$wc_order = $this->get_order_by_svea_id( $this->svea_order_id );

		self::get_cart_session( $this->token );

		if ( empty( $wc_order ) ) {
			if ( (string) WC()->session->get( 'sco_order_id' ) === (string) $this->svea_order_id ) {
				$msg = sprintf( 'Received push for ongoing order. Standing by (%s)', $this->svea_order_id );
				$this->gateway::log( $msg );
				status_header( 200 );
				echo esc_html( $msg );
				exit;

			} else {
				// This order hasn't been created yet, Svea will try to push again
				$msg = sprintf( 'Received push for Svea order id: %s which we could not find. Returning 404', $this->svea_order_id );
				$this->gateway::log( $msg );
				status_header( 404 );
				echo esc_html( $msg );
				exit;
			}
		}

		if ( ! $this->validate_customer_id( $wc_order, $this->token ) ) {
			$this->gateway::log( sprintf( 'Could not verify push customer ID. Svea ID: %s. Customer ID: %s. Continuing anyway', $this->svea_order_id, $this->token ) );
		}

		// Recognize that a push was made
		update_option( 'sco_last_push', time() );

		do_action( 'woocommerce_sco_process_push_before', $wc_order );

		$svea_order = new Svea_Order( $wc_order );

		try {
			self::$svea_order = $svea_order->get( $this->svea_order_id );
		} catch ( \Exception $e ) {
			WC_Gateway_Svea_Checkout::log( sprintf( 'Error in push webhook when getting order id %s. Message from Svea: %s' ), $this->svea_order_id, $e->getMessage() );
			status_header( 404 );
			exit;
		}

		switch ( strtoupper( self::$svea_order['Status'] ) ) {
			case 'FINAL':
				$this->finalize_order( $wc_order );
				break;

			case 'CREATED':
				$this->created_order( $wc_order );
				break;

			case 'CANCELLED':
				$this->cancel_order( $wc_order );
				break;
		}
	}

	/**
	 * Process the push for a recurring order
	 *
	 * @return void
	 */
	public function process_push_recurring() {
		$order_id = isset( $_GET['wc_order_id'] ) ? sanitize_text_field( $_GET['wc_order_id'] ) : 0; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$key = isset( $_GET['key'] ) ? sanitize_text_field( $_GET['key'] ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$this->gateway = WC_Gateway_Svea_Checkout::get_instance();

		if ( $order_id === 0 ) {
			$this->gateway::log( 'Missing order ID in recurring push webhook' );
			status_header( 400 );
			exit;
		}

		$wc_order = wc_get_order( $order_id );

		if ( empty( $wc_order ) ) {
			$this->gateway::log( sprintf( 'Could not find order %s in recurring push webhook', $order_id ) );
			status_header( 404 );
			exit;
		}

		if ( $this->gateway->get_option( 'use_ip_restriction' ) === 'yes' ) {
			$this->validate_referer();
		}

		$token = $wc_order->get_meta( '_svea_co_token' );

		// Check for token
		if ( empty( $token ) ) {
			$this->gateway::log( sprintf( 'Could not find token for order %s', $order_id ) );
			status_header( 404 );
			exit;
		}

		// Check if key matches key from order
		if ( $key !== $wc_order->get_order_key() ) {
			$this->gateway::log( sprintf( 'Could not verify key for order %s', $order_id ) );
			status_header( 404 );
			exit;
		}

		// Fetch order from Svea
		$svea_order = new Svea_Order( $wc_order );
		$svea_order_id = $wc_order->get_meta( '_svea_co_order_id' );

		try {
			self::$svea_order = $svea_order->get( $svea_order_id, $token );
		} catch ( \Exception $e ) {
			WC_Gateway_Svea_Checkout::log( sprintf( 'Error in token push webhook when getting order id %s. Message from Svea: %s' ), $this->svea_order_id, $e->getMessage() );
			status_header( 404 );
			exit;
		}

		$wc_order->set_transaction_id( self::$svea_order['orderId'] );
		$wc_order->save();
	}

	/**
	 * Validate that the order has the correct customer ID
	 *
	 * @param \WC_Order $wc_order
	 * @param string|int $customer_id
	 * @return void
	 */
	public function validate_customer_id( $wc_order, $customer_id ) {
		$order_cid = $wc_order->get_meta( '_svea_co_cid' );

		return $order_cid === $customer_id;
	}

	/**
	 * Maybe sync the order
	 *
	 * @param \WC_Order $wc_order
	 * @return void
	 */
	public function maybe_sync_order( $wc_order ) {
		if ( apply_filters( 'use_svea_order_sync', true ) ) {
			$this->sync_order_rows( $wc_order );
		}
	}

	/**
	 * The order is canceled
	 *
	 * @param \WC_Order $wc_order
	 * @return void
	 */
	public function cancel_order( $wc_order ) {
		$this->gateway::log( sprintf( 'Push callback. Cancelling order. %s', $wc_order->get_id() ) );

		$wc_order->set_status( 'cancelled' );
		$wc_order->update_meta_data( '_svea_co_order_cancelled', true );
		$wc_order->save();

		do_action( 'woocommerce_sco_after_push_order', $wc_order, self::$svea_order );
		do_action( 'woocommerce_sco_after_push_order_cancel', $wc_order, self::$svea_order );

		echo 'Order cancelled';
		die;
	}

	/**
	 * The order has been created
	 *
	 * @param \WC_Order $wc_order WooCommerce order
	 * @return void
	 */
	public function created_order( $wc_order ) {
		$this->gateway::log( sprintf( 'Push callback. Created order. %s', $wc_order->get_id() ) );

		do_action( 'woocommerce_sco_after_push_order', $wc_order, self::$svea_order );
		do_action( 'woocommerce_sco_after_push_order_create', $wc_order, self::$svea_order );

		echo 'Order created';
		die;
	}

	/**
	 * Finalize the order
	 *
	 * @param \WC_Order $wc_order
	 * @return void
	 */
	public function finalize_order( $wc_order ) {
		$current_status = $wc_order->get_status();

		// Complete the payment in WooCommerce
		if ( ! $wc_order->is_paid() ) {
			$this->maybe_sync_order( $wc_order );

			$svea_payment_type = strtoupper( sanitize_text_field( self::$svea_order['PaymentType'] ) );

			// Check if Payment method is set and exists in array
			$method_name = $this->gateway->get_payment_method_name( $svea_payment_type );

			if ( ! empty( $method_name ) ) {
				$wc_order->set_payment_method_title(
					sprintf( '%s (%s)', $this->gateway->get_title(), $method_name )
				);

				$wc_order->update_meta_data( '_svea_co_payment_type', $method_name );
			}

			// Make sure the name gets saved
			if ( self::$svea_order['Customer']['IsCompany'] ) {
				Admin::save_refs( $wc_order, self::$svea_order );
			}

			$wc_order->update_meta_data( '_svea_co_is_company', (bool) self::$svea_order['Customer']['IsCompany'] );
			$wc_order->update_meta_data( '_svea_co_order_final', current_time( 'timestamp', true ) );

			$this->gateway::log( sprintf( 'Push callback finalized order. Svea ID:%s OrderID: %s', $this->svea_order_id, $wc_order->get_id() ) );

			// Get order from PaymentAdmin
			$svea_order = new Svea_Order( $wc_order, true );

			// Max 10 tries
			for ( $i = 0; $i < 10; $i++ ) {
				try {
					$pa_order = $svea_order->get_order( $this->svea_order_id );
					break;
				} catch ( \Exception $e ) {
					$this->gateway::log( sprintf( 'Tried fetching %s. Trying again', $this->svea_order_id ) );
				}

				sleep( 1 );
			}

			if ( ! isset( $pa_order ) ) {
				$this->gateway::log( 'Tried fetching order from PaymentAdmin but failed. Aborting' );
				status_header( 404 );
				exit;
			}

			// Check system status
			if ( strtoupper( $pa_order['SystemStatus'] ) === 'PENDING' ) {
				$wc_order->set_status( Admin::AWAITING_ORDER_STATUS );
				// Check in an hour
				wp_schedule_single_event( time() + HOUR_IN_SECONDS, 'sco_check_pa_order_status', [ $this->svea_order_id ] );
			} else {
				$wc_order->payment_complete( $this->svea_order_id );
			}

			$wc_order->save();
			status_header( 200 );

			// If this is a recurring order, save the token
			if ( self::$svea_order['Recurring'] && isset( self::$svea_order['RecurringToken'] ) && function_exists( 'wcs_get_subscriptions_for_order' ) ) {
				$subscriptions = wcs_get_subscriptions_for_order( $wc_order );

				// Save token on all subscriptions
				foreach ( $subscriptions as $subscription ) {
					$subscription->update_meta_data( '_svea_co_token', self::$svea_order['RecurringToken'] );
					$subscription->save();
				}
			}
		}

		// If the order was set to completed in this go, make sure the order get delivered
		if (
			$current_status !== 'completed' && // Old status
			$wc_order->get_status() === 'completed' && // New status
			$wc_order->get_meta( '_svea_co_deliver_date' ) === ''
		) {
			$this->gateway->deliver_order( $wc_order->get_id() );
		}

		do_action( 'woocommerce_sco_after_push_order', $wc_order, self::$svea_order );
		do_action( 'woocommerce_sco_after_push_order_final', $wc_order, self::$svea_order );

		echo 'Order finalized';
		die;
	}

	/**
	 * Sync order rows
	 *
	 * @param \WC_Order $wc_order
	 * @return void
	 */
	public function sync_order_rows( $wc_order ) {
		$this->gateway::log( sprintf( 'Syncing order rows. Order ID: %s', $wc_order->get_id() ) );

		$svea_cart_items = self::$svea_order['Cart']['Items'];
		$svea_wc_order_item_row_ids = [];

		$rounding_order_id = $wc_order->get_meta( '_svea_co_rounding_order_row_id' );

		foreach ( $wc_order->get_items( [ 'line_item', 'fee', 'shipping' ] ) as $item_key => $order_item ) {
			$svea_wc_order_item_row_ids[] = intval( $order_item->get_meta( '_svea_co_order_row_id' ) );
		}

		// We might not need to calculate if the order is already in sync
		$calc_totals = false;

		// Only add new items
		foreach ( $svea_cart_items as $svea_cart_item ) {
			if (
				! in_array( $svea_cart_item['RowNumber'], $svea_wc_order_item_row_ids, true ) &&
				$svea_cart_item['RowType'] !== 'ShippingFee' &&
				(int) $rounding_order_id !== $svea_cart_item['RowNumber']
			) {
				if ( ! apply_filters( 'woocommerce_sco_should_add_new_item', true, $svea_cart_item ) ) {
					continue;
				}

				$product_id = wc_get_product_id_by_sku( $svea_cart_item['ArticleNumber'] );

				if ( $product_id ) {
					$order_item = new \WC_Order_Item_Product();

					$product = wc_get_product( $product_id );

					if ( $product->is_type( 'product_variation' ) ) {
						$order_item->set_variation_id( $product_id );
						$product_id = $product->get_parent_id();
					}

					$order_item->set_product_id( $product_id );
				} else {
					$order_item = new \WC_Order_Item_Fee();
				}

				$quantity = $svea_cart_item['Quantity'] / 100;
				$total = ( $svea_cart_item['UnitPrice'] / 100 ) * $quantity;

				$order_item->set_props(
					[
						'quantity'  => $quantity,
						'name'      => $svea_cart_item['Name'],
						'total'     => $total / ( $svea_cart_item['VatPercent'] / 10000 + 1 ),
						'total_tax' => $total - ( $total / ( $svea_cart_item['VatPercent'] / 10000 + 1 ) ),
					]
				);

				$calc_totals = true;
				$wc_order->add_item( $order_item );
				$order_item->update_meta_data( '_svea_co_order_row_id', $svea_cart_item['RowNumber'] );
				$order_item->save();
			}
		}

		if ( $calc_totals ) {
			$wc_order->calculate_totals();

			do_action( 'woocommerce_sco_before_push_update_order_items', $wc_order );

			$wc_order->save();

			do_action( 'woocommerce_sco_after_push_update_order_items', $wc_order );
		}
	}

	/**
	 * Sync the shipping fee
	 *
	 * @param \WC_Order $wc_order
	 * @param float $shipping_fee
	 * @return void
	 */
	public function sync_shipping_fee( $wc_order, $shipping_fee ) {
		/** @var \WC_Order_Item_Product[] $line_items */
		$line_items = $wc_order->get_items();

		// Mimic the same structure as cart items so that the nShift calculations can be with same functions
		$items = [];
		$total = 0;

		if ( ! empty( $line_items ) ) {
			foreach ( $line_items as $key => $item ) {
				$data = $item->get_data();

				$items[] = [
					'line_total' => $data['total'],
					'line_tax'   => $data['total_tax'],
					'data'       => $item->get_product(),
				];

				$total += $wc_order->get_line_total( $item );
			}
		}

		$taxes = WC_Shipping_Svea_Nshift::get_taxes_amounts_and_percent( $items, $total );
		$data = WC_Shipping_Svea_Nshift::get_tax_fractions( $taxes, $shipping_fee );
		$actual_taxes = WC_Shipping_Svea_Nshift::get_real_taxes( $data );
		$shipping_fee -= array_sum( $actual_taxes );

		$shipping_items = $wc_order->get_items( 'shipping' );
		/** @var \WC_Order_Item_Shipping $shipping_item */
		$shipping_item = reset( $shipping_items );

		// Update the cost and taxes
		$shipping_item->set_total( $shipping_fee );
		$shipping_item->set_taxes( $actual_taxes );
		$shipping_item->delete_meta_data( 'nshift_taxes' );
		$shipping_item->add_meta_data( 'nshift_taxes', $actual_taxes );
		$shipping_item->save_meta_data();
		$shipping_item->save();

		// Send these changes back to Svea
		$admin_checkout_client = new Svea_Order( $wc_order, true );
		$shipping_id = $shipping_item->get_method_id() . ':' . $shipping_item->get_instance_id();
		$svea_order_id = self::$svea_order['OrderId'];

		try {
			$pa_order = $admin_checkout_client->get_order( $svea_order_id );
		} catch ( \Exception $e ) {
			WC_Gateway_Svea_Checkout::log( sprintf( 'Could not get order to update shipping fee. Message from Svea: %s', $e->getMessage() ) );
			return;
		}

		if ( ! in_array( 'CanUpdateOrderRow', $pa_order['Actions'], true ) ) {
			// Order can't be updated
			WC_Gateway_Svea_Checkout::log( 'Order can\'t be updated when updating shippingFee' );
			return;
		}

		$order_rows = $pa_order['OrderRows'];
		$update_order_rows = [];

		if ( ! empty( $order_rows ) ) {
			foreach ( $order_rows as $order_row ) {
				if ( $order_row['ArticleNumber'] === $shipping_id ) {
					foreach ( $data as $percent => $ammounts ) {
						if ( $order_row['VatPercent'] / 100 === $percent ) {
							$order_row['UnitPrice'] = $ammounts['cost'] * 100;
							$update_order_rows[] = $order_row;
						}
					}
				}
			}
		}

		if ( ! empty( $update_order_rows ) ) {
			foreach ( $update_order_rows as $update_row ) {
				try {
					$admin_checkout_client->update_order_row( $svea_order_id, $update_row['OrderRowId'], $update_row );
				} catch ( \Exception $e ) {
					WC_Gateway_Svea_Checkout::log( sprintf( 'Could not update shipping fee. Message from Svea: %s', $e->getMessage() ) );
				}
			}
		}
	}

}
