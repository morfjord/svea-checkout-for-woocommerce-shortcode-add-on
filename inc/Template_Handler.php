<?php

namespace Svea_Checkout_For_Woocommerce;

use Svea_Checkout_For_Woocommerce\Models\Svea_Order;
use Svea_Checkout_For_Woocommerce\Session_Table;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}
/**
 * Checkout shortcode
 *
 * Used on the checkout page to display the checkout
 *
 */
class Template_Handler {

	/**
	 * Init function
	 */
	public function init() {
		add_shortcode( 'svea_checkout', [ $this, 'display_svea_checkout_page' ] );

		add_action( 'wc_ajax_refresh_sco_snippet', [ $this, 'refresh_sco_snippet' ] );
		add_action( 'wc_ajax_update_sco_order_information', [ $this, 'update_order_information' ] );

		add_action( 'wc_ajax_update_sco_order_nshift_information', [ $this, 'update_order_nshift_information' ] );
		add_action( 'wc_ajax_sco_change_payment_method', [ $this, 'change_payment_method' ], 100 );

		add_action( 'woocommerce_thankyou', [ $this, 'display_thank_you_box' ] );

		add_filter( 'wc_get_template', [ $this, 'maybe_override_checkout_template' ], 10, 2 );

		if ( isset( $_GET['sco_redirect'], $_GET['sco_token'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			add_action( 'wp', [ $this, 'maybe_redirect_to_thankyou' ] );
		}

		if ( isset( $_GET['callback'] ) && $_GET['callback'] === 'svea' ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			add_action( 'init', [ $this, 'handle_checkout_error' ] );
		}

		add_action( 'woocommerce_before_checkout_registration_form', [ $this, 'remove_account_password' ] );

		// Store the WC cookie when it's being set to it can be reached within in the same request
		add_filter( 'woocommerce_set_cookie_options', [ $this, 'store_cookie' ], 10, 3 );

		add_filter( 'woocommerce_default_address_fields', [ $this, 'remove_state_field' ] );

		add_filter( 'woocommerce_sco_update_order_info_keys', [ $this, 'add_attribution_form_keys' ] );
	}

	/**
	 * Add attribution keys to be saved in the session
	 *
	 * @param string[] $keys
	 * @return string[]
	 */
	public function add_attribution_form_keys( $keys ) {
		$prefix = (string) apply_filters(
			'wc_order_attribution_tracking_field_prefix',
			'wc_order_attribution_'
		);

		$fields = [
			// main fields.
			'source_type',
			'referrer',

			// utm fields.
			'utm_campaign',
			'utm_source',
			'utm_medium',
			'utm_content',
			'utm_id',
			'utm_term',

			// additional fields.
			'session_entry',
			'session_start_time',
			'session_pages',
			'session_count',
			'user_agent',
		];

		foreach ( $fields as $field ) {
			$keys[] = $prefix . $field;
		}

		return $keys;
	}

	/**
	 * Remove the state field in Svea to enable pruchases in countrues that uses states
	 *
	 * @param array $fields
	 * @return array
	 */
	public function remove_state_field( $fields ) {
		if ( isset( $fields['state'] ) && $this->is_svea() ) {
			unset( $fields['state'] );
		}

		return $fields;
	}

	/**
	 * Store WooCommerce cookie so that it can be used when creating a order
	 *
	 * @param array $args
	 * @param string $name
	 * @param mixed $val
	 * @return array
	 */
	public function store_cookie( $args, $name, $val ) {
		$cookie_name = apply_filters( 'woocommerce_cookie', 'wp_woocommerce_session_' . COOKIEHASH );

		if (
			$name === $cookie_name &&
			(
				! isset( $_COOKIE[ $cookie_name ] ) ||
				$_COOKIE[ $cookie_name ] !== $val
			)
		) {
			$_COOKIE[ $cookie_name ] = $val;
		}

		return $args;
	}

	/**
	 * Remove account password
	 *
	 * @param \WC_Checkout $checkout
	 * @return void
	 */
	public function remove_account_password( $checkout ) {
		if ( $this->is_svea() ) {
			add_filter( 'pre_option_woocommerce_registration_generate_password', [ __CLASS__, '_return_yes' ] );
		}
	}

	/**
	 * Returns 'yes'
	 *
	 * @return string
	 */
	public static function _return_yes() {
		return 'yes';
	}

	/**
	 * The checkout redirected the user back. Allow plugins to handle errors
	 *
	 * @return void
	 */
	public function handle_checkout_error() {
		$svea_id = WC()->session->get( 'sco_order_id' );
		$wc_id = WC()->session->get( 'order_awaiting_payment' );

		do_action( 'woocommerce_sco_checkout_error_order', $wc_id, $svea_id );
	}

	/**
	 * Change payment method
	 *
	 * @return void
	 */
	public function change_payment_method() {
		$use_svea = isset( $_POST['svea'] ) && sanitize_text_field( $_POST['svea'] ) === 'true'; // phpcs:ignore WordPress.Security.NonceVerification.Missing

		// Allow plugins and themes to understand that we're in the checkout
		wc_maybe_define_constant( 'WOOCOMMERCE_CHECKOUT', true );

		/** @var \WC_Payment_Gateway[] $available_gateways */
		$available_gateways = WC()->payment_gateways->get_available_payment_gateways();

		if ( ! empty( $available_gateways ) ) {
			foreach ( $available_gateways as $key => $gateway ) {
				if ( $use_svea && WC_Gateway_Svea_Checkout::GATEWAY_ID === $key ) {
					WC()->session->set( 'chosen_payment_method', $key );
					wp_send_json_success( 'Ok' );
				} else if ( ! $use_svea && WC_Gateway_Svea_Checkout::GATEWAY_ID !== $key ) {
					WC()->session->set( 'chosen_payment_method', $key );
					wp_send_json_success( 'Ok' );
				}
			}
		}

		wp_send_json_error();
	}

	/**
	 * Maybe redirect to the thank you page
	 *
	 * @return void
	 */
	public function maybe_redirect_to_thankyou() {
		if ( is_checkout() && $_GET['sco_redirect'] === 'true' ) { // phpcs:ignore
			// If the row still exists, the redirection has not been made previously
			if ( ! Session_Table::get_session_key_by_sco_token( $_GET['sco_token'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
				return;
			}

			// If the order has been created the should be able to find it with the _svea_co_cid from the query-param
			$args = [
				'meta_key'   => '_svea_co_cid',
				'meta_value' => $_GET['sco_token'], // phpcs:ignore
				'limit'      => 1,
			];

			$orders = wc_get_orders( $args );

			if ( ! empty( $orders ) ) {
				/** @var \Automattic\WooCommerce\Admin\Overrides\Order $order */
				$order = $orders[0];

				$url = $order->get_checkout_order_received_url();

				if ( ! is_user_logged_in() && is_numeric( $order->get_customer_id() ) && get_user_meta( $order->get_customer_id(), 'created_during_svea_checkout', true ) ) {
					wp_set_current_user( $order->get_customer_id() );
					wp_set_auth_cookie( $order->get_customer_id() );

					delete_user_meta( $order->get_customer_id(), 'created_during_svea_checkout' );
				}

				Session_Table::delete_session_by_sco_token( $_GET['sco_token'] ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
				// Clear session from sco-specific information
				$additional_fields = WC()->checkout->get_checkout_fields( 'order' );
				$keys = ! empty( $additional_fields ) ? array_keys( $additional_fields ) : [];
				$keys = apply_filters( 'woocommerce_sco_update_order_info_keys', $keys );

				foreach ( $keys as $key ) {
					$val = WC()->session->get( '_sco_' . $key );

					if ( ! is_null( $val ) ) {
						WC()->session->__unset( '_sco_' . $key );
					}
				}

				wp_safe_redirect( $url );
				die;
			}
		}
	}

	/**
	 * Get the iframe snippet from Svea
	 *
	 * @return string
	 */
	public static function get_svea_snippet() {
		$svea_checkout_module = self::get_svea_checkout_module( WC()->cart );
		echo isset( $svea_checkout_module['Gui']['Snippet'] ) ? $svea_checkout_module['Gui']['Snippet'] : esc_html__( 'Could not load Svea Checkout', 'svea-checkout-for-woocommerce' ); // phpcs:ignore
	}

	/**
	 * Is the current checkout using svea?
	 *
	 * @return bool
	 */
	public function is_svea() {
		if ( ! WC()->payment_gateways() ) {
			return false;
		}

		$is_svea = false;
		$available_gateways = WC()->payment_gateways()->get_available_payment_gateways();

		// Is Svea checkout available?
		if (
			isset( $available_gateways[ WC_Gateway_Svea_Checkout::GATEWAY_ID ] ) &&
			$available_gateways[ WC_Gateway_Svea_Checkout::GATEWAY_ID ]->is_available()
		) {
			$chosen_payment_method = WC()->session ? WC()->session->get( 'chosen_payment_method' ) : '';

			if ( $chosen_payment_method ) {
				if ( $chosen_payment_method === WC_Gateway_Svea_Checkout::GATEWAY_ID ) {
					// User has actually chosen Svea Checkout
					$is_svea = true;
				}
			} else if ( Helper::array_key_first( $available_gateways ) === WC_Gateway_Svea_Checkout::GATEWAY_ID ) {
				// User hasn't chosen but the first option is Svea checkout
				$is_svea = true;
			}
		}

		return $is_svea;
	}

	/**
	 * If the customer chose Svea Checkout as payment method, we'll change the whole template
	 *
	 * @param string $template      Template path
	 * @param string $template_name Name of template
	 * @return string
	 */
	public function maybe_override_checkout_template( $template, $template_name ) {
		if ( is_admin() && ! wp_doing_ajax() ) {
			return $template;
		}

		if ( $template_name === 'checkout/form-checkout.php' && $this->is_svea() ) {
			// Look for template in theme
			$template = locate_template( 'woocommerce/svea-checkout.php' );

			if ( ! $template ) {
				$template = SVEA_CHECKOUT_FOR_WOOCOMMERCE_DIR . '/templates/svea-checkout.php';
			}
		}

		return $template;
	}

	/**
	 * Display iframe on order received page
	 *
	 * @param int $order_id ID of the order being displayed
	 * @return void
	 */
	public function display_thank_you_box( $order_id ) {
		$wc_order = wc_get_order( $order_id );

		if ( ! $wc_order ) {
			return;
		}

		if ( $wc_order->get_payment_method() !== WC_Gateway_Svea_Checkout::GATEWAY_ID ) {
			return;
		}

		$svea_order_id = $wc_order->get_meta( '_svea_co_order_id' );

		if ( ! $svea_order_id ) {
			return;
		}

		$svea_order_id = absint( $svea_order_id );

		// Load the Svea_Order to make sure the checkout client goes to the correct country
		$svea_order = new Svea_Order( $wc_order );
		$response = $svea_order->get( $svea_order_id );
		?>
		<div class="wc-svea-checkout-thank-you-box">
			<?php echo $response['Gui']['Snippet']; // phpcs:ignore ?>
		</div>
		<?php
	}

	/**
	 * Update the session data for later use
	 *
	 * @return void
	 */
	public function update_order_nshift_information() {
		if ( ! isset( $_REQUEST['security'] ) || ! wp_verify_nonce( $_REQUEST['security'], 'update-sco-order-nshift-information' ) ) {
			echo 'Nonce fail';
			exit;
		}

		if ( ! isset( $_POST['price'], $_POST['name'] ) ) {
			echo 'Missing params';
			exit;
		}

		// Well verify from Svea later
		WC()->session->set( 'sco_nshift_name', sanitize_text_field( $_POST['name'] ) );
		WC()->session->set( 'sco_nshift_price', sanitize_text_field( $_POST['price'] ) );

		// Clear the shipping cache
		$packages = WC()->cart->get_shipping_packages();

		foreach ( $packages as $key => $_value ) {
			unset( WC()->session->{ 'shipping_for_package_' . $key } );
		}

		WC()->cart->calculate_shipping();
		WC()->cart->calculate_totals();

		$this->update_current_svea_order();

		$fragments = $this->get_cart_fragments();

		wp_send_json(
			[
				'fragments' => $fragments,
			]
		);
	}

	/**
	 * Update the order in Svea
	 *
	 * @return void
	 */
	public function update_current_svea_order() {
		// Update order in Svea
		try {
			$sco_id = WC()->session->get( 'sco_order_id' );
			$svea = new Svea_Order( WC()->cart );
			$svea->update( $sco_id );

		} catch ( \Exception $e ) {
			WC_Gateway_Svea_Checkout::log( sprintf( 'Error when updating order. %s', $e->getMessage() ) );

			return $e->getMessage();
		}
	}

	/**
	 * Update the session data for later use
	 *
	 * @return void
	 */
	public function update_order_information() {
		if ( ! isset( $_REQUEST['security'] ) || ! wp_verify_nonce( $_REQUEST['security'], 'update-sco-order-information' ) ) {
			echo 'Nonce fail';
			exit;
		}

		if ( ! isset( $_POST['form_data'] ) ) {
			exit;
		}

		/** @var \WC_Session_Handler $session */
		$session = WC()->session;
		parse_str( $_POST['form_data'], $form_data );

		$fields = WC()->checkout->get_checkout_fields( 'order' );

		$keys = ! empty( $fields ) ? array_keys( $fields ) : [];
		$keys = apply_filters( 'woocommerce_sco_update_order_info_keys', $keys );

		foreach ( $keys as $key ) {
			if ( isset( $form_data[ $key ] ) ) {
				$session->set( '_sco_' . $key, $form_data[ $key ] );
			} else {
				unset( $session->{'_sco_' . $key } );
			}
		}

		echo 'OK';
		exit;
	}

	/**
	 * Update order and refresh the Svea Checkout snippet
	 *
	 * @return void
	 */
	public function refresh_sco_snippet() {
		check_ajax_referer( 'refresh-sco-snippet', 'security' );

		wc_maybe_define_constant( 'WOOCOMMERCE_CHECKOUT', true );

		// Error message if the cart is empty
		if ( WC()->cart->is_empty() ) {
			$data = [
				'fragments' => apply_filters(
					'woocommerce_update_order_review_fragments',
					[
						'.wc-svea-checkout-checkout-module' => '<div class="wc-svea-checkout-checkout-module"><div class="woocommerce-error">' . esc_html__( 'Sorry, your session has expired.', 'woocommerce' ) . ' <a href="' . esc_url( wc_get_page_permalink( 'shop' ) ) . '" class="wc-backward">' . esc_html__( 'Return to shop', 'woocommerce' ) . '</a></div></div>',
					]
				),
			];

			wp_send_json( $data );
			exit;
		}

		parse_str( $_POST['post_data'], $post_data );

		$chosen_shipping_methods = isset( $post_data['shipping_method'] ) ? $post_data['shipping_method'] : [];

		$nshift_updated = false;

		if ( ! empty( $chosen_shipping_methods ) && WC()->session->get( 'chosen_shipping_methods' ) !== $chosen_shipping_methods ) {
			// If switching to nShift we'll force an update
			if ( strpos( current( $chosen_shipping_methods ), WC_Shipping_Svea_Nshift::METHOD_ID ) !== false ) {
				$nshift_updated = true;

			} else if ( is_array( WC()->session->get( 'chosen_shipping_methods' ) ) && strpos( current( WC()->session->get( 'chosen_shipping_methods' ) ), WC_Shipping_Svea_Nshift::METHOD_ID ) !== false ) {
				// Check if we're going away from nShift
				$nshift_updated = true;
			}

			WC()->session->set( 'chosen_shipping_methods', array_map( 'wc_clean', $chosen_shipping_methods ) );
		}

		WC()->session->set( 'sco_currency', get_woocommerce_currency() );

		do_action( 'woocommerce_sco_session_data' );

		$country_changed = ( WC()->customer->get_billing_country() !== $post_data['billing_country'] ?: '' ) ? true : false;

		WC()->customer->set_props(
			[
				'billing_country'   => isset( $post_data['billing_country'] ) ? sanitize_text_field( $post_data['billing_country'] ) : '',
				'billing_postcode'  => isset( $post_data['billing_postcode'] ) ? sanitize_text_field( $post_data['billing_postcode'] ) : '',
				'shipping_country'  => isset( $post_data['billing_country'] ) ? sanitize_text_field( $post_data['billing_country'] ) : '',
				'shipping_postcode' => isset( $post_data['billing_postcode'] ) ? sanitize_text_field( $post_data['billing_postcode'] ) : '',
			]
		);

		if ( ! empty( $post_data['billing_country'] ) ) {
			WC()->customer->set_calculated_shipping( true );
		}

		// Save info and calc totals
		WC()->customer->save();
		WC()->cart->calculate_totals();

		// Fetch the current checkout
		$svea_checkout_module = self::get_svea_checkout_module( WC()->cart );

		// Update customer information based on given information
		if ( isset( $svea_checkout_module['BillingAddress'] ) ) {
			$billing_info = $svea_checkout_module['BillingAddress'];
			$shipping_info = $svea_checkout_module['ShippingAddress'];

			$billing_postcode = $billing_info['PostalCode'] ?? null;
			$shipping_postcode = $shipping_info['PostalCode'] ?? $billing_postcode;

			$billing_city = $billing_info['City'] ?? null;
			$shipping_city = $shipping_info['City'] ?? $billing_city;

			$billing_address_1 = $billing_info['StreetAddress'] ?? null;
			$shipping_address_1 = $shipping_info['StreetAddress'] ?? $billing_address_1;

			$billing_address_2 = $billing_info['CoAddress'] ?? null;
			$shipping_address_2 = $shipping_info['CoAddress'] ?? $billing_address_2;

			$is_company = isset( $svea_checkout_module['Customer']['IsCompany'] ) && $svea_checkout_module['Customer']['IsCompany'];

			// Update the information that is being provided inside the checkout
			WC()->customer->set_props(
				[
					'billing_postcode'   => $billing_postcode,
					'billing_city'       => $billing_city,
					'billing_address_1'  => $billing_address_1,
					'billing_address_2'  => $billing_address_2,
					'billing_company'    => $is_company ? $billing_info['FullName'] : '',
					'shipping_postcode'  => $shipping_postcode,
					'shipping_city'      => $shipping_city,
					'shipping_address_1' => $shipping_address_1,
					'shipping_address_2' => $shipping_address_2,
					'shipping_company'   => $is_company ? $shipping_info['FullName'] : '',
				]
			);
		}

		// Save again since changes have been made
		WC()->customer->save();
		WC()->cart->calculate_shipping();
		WC()->cart->calculate_totals();

		// Get messages if reload checkout is not true
		$messages = '';

		if ( ! isset( WC()->session->reload_checkout ) ) {
			ob_start();
			wc_print_notices();
			$messages = ob_get_clean();
		}

		// If cart total is 0, reload the page, this will instead show the regular WooCommerce checkout
		$reload = isset( WC()->session->reload_checkout );

		if ( ! $reload && ! WC_Gateway_Svea_Checkout::is_zero_sum_enabled() ) {
			$reload = WC()->cart->total <= 0 ? true : false;
		}

		if ( ! $reload && ! in_array( WC_Gateway_Svea_Checkout::GATEWAY_ID, array_keys( WC()->payment_gateways()->get_available_payment_gateways() ), true ) ) {
			$reload = true;
		}

		$reload = apply_filters( 'woocommerce_sco_refresh_snippet_reload', $reload );

		$fragments = [];

		if ( $reload !== true ) {
			$fragments = $this->get_cart_fragments();
		}

		$current_id = WC()->session->get( 'sco_order_id' );

		// If force is present check the string, otherwise it's false
		$force = isset( $_POST['force'] ) ? ( $_POST['force'] === 'false' ? false : true ) : false;

		if (
			$force ||
			$country_changed ||
			$nshift_updated ||
			(
				isset( $svea_checkout_module['OrderId'] ) &&
				$current_id !== $svea_checkout_module['OrderId'] &&
				isset( $svea_checkout_module['Gui']['Snippet'] )
			)
		) {
			$fragments['#svea-checkout-container'] = $svea_checkout_module['Gui']['Snippet'];
			WC()->session->set( 'sco_order_id', $svea_checkout_module['OrderId'] );
		}

		wp_send_json(
			[
				'result'    => empty( $messages ) ? 'success' : 'failure',
				'messages'  => $messages,
				'reload'    => $reload,
				'fragments' => $fragments,
			]
		);
	}

	/**
	 * Get the cart fragments
	 *
	 * @return array
	 */
	public function get_cart_fragments() {
		// Get order review fragment
		$fragments = [];

		$review_order_template = locate_template( 'svea-checkout-for-woocommerce/checkout/review-order.php' );

		if ( $review_order_template === '' ) {
			$review_order_template = SVEA_CHECKOUT_FOR_WOOCOMMERCE_DIR . '/templates/checkout/review-order.php';
		}

		ob_start();

		include $review_order_template;

		$fragments['.woocommerce-checkout-review-order-wrapper'] = ob_get_clean();

		return $fragments;
	}

	/**
	 * This function includes the template for the Svea Checkout
	 *
	 * @deprecated 2.0.0 Use the regular [woocommerce_checkout] instead
	 * @return string Template to display the checkout
	 */
	public function display_svea_checkout_page() {
		_deprecated_function( __FUNCTION__, '2.0.0', esc_html__( 'Use [woocommerce_checkout] shortcode', 'svea-checkout-for-woocommerce' ) );

		ob_start();
		echo apply_filters( 'the_content', '[woocommerce_checkout]' ); // phpcs:ignore
		return ob_get_clean();
	}

	/**
	 * This function returns the Svea Checkout module.
	 *
	 * @param \WC_Cart $cart WooCommerce cart
	 * @return array|string The Svea Checkout snippet
	 */
	public static function get_svea_checkout_module( $cart ) {
		$s_order = new Svea_Order( $cart );

		return $s_order->get_module();
	}
}
