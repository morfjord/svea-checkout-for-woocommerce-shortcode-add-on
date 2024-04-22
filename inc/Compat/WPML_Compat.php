<?php
namespace Svea_Checkout_For_Woocommerce\Compat;

use Svea_Checkout_For_Woocommerce\Webhook_Handler;
use WCML\MultiCurrency\Geolocation;

if ( ! defined( 'ABSPATH' ) ) {
	exit; } // Exit if accessed directly

/**
 * Compability with WPML
 */
class WPML_Compat {

	/**
	 * Init function, add hooks
	 *
	 * @return void
	 */
	public function init() {
		add_action( 'woocommerce_sco_cart_session_before', [ $this, 'set_language_from_session' ] );
		add_action( 'woocommerce_sco_cart_session_after', [ $this, 'set_language_from_session' ] );

		add_action( 'woocommerce_sco_session_data', [ $this, 'save_data_in_session' ] );

		add_action( 'woocommerce_sco_process_checkout_before', [ $this, 'before_process_checkout' ] );

		add_action( 'woocommerce_sco_handle_checkout_error_before', [ $this, 'restore_correct_currency' ] );

		add_action( 'woocommerce_sco_before_session_load_cart', [ $this, 'restore_correct_currency' ] );

		add_filter( 'woocommerce_sco_create_order', [ $this, 'change_push_uri' ] );

		// Apply this filter very early in case WPML uses geolocation
		if ( isset( $_GET['svea_token'], $_GET['svea_order_id'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			$this->geolocate();
		}
	}

	/**
	 * Geolocate
	 *
	 * @return void
	 */
	public function geolocate() {
		Webhook_Handler::get_cart_session( $_GET['svea_token'], false ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended

		$country = WC()->session->get( 'sco_wpml_country_geolocation' );

		if ( ! empty( $country ) ) {
			add_filter(
				'wcml_geolocation_get_user_country',
				function( $default ) use ( $country ) {
					return $country;
				}
			);
		}
	}

	/**
	 * Modify information before WooCommerce checkout
	 *
	 * @param \Svea_Checkout_For_Woocommerce\Models\Svea_Order $svea_order
	 * @return void
	 */
	public function before_process_checkout( $svea_order ) {
		// Change the currency in WooCommerce to the one on the order
		add_filter(
			'wcml_client_currency',
			function() use ( $svea_order ) {
				return $svea_order['Currency'];
			}
		);

		// Change the country in WooCommerce to the one on the order
		add_filter(
			'wcml_geolocation_get_user_country',
			function() use ( $svea_order ) {
				return WC()->session->get( 'sco_wpml_country_geolocation', $svea_order['CountryCode'] );
			}
		);
	}

	/**
	 * Set the currency based on the session
	 *
	 * @return void
	 */
	public function restore_correct_currency() {
		add_filter(
			'wcml_client_currency',
			function() {
				return WC()->session->get( 'sco_currency' );
			}
		);
	}

	/**
	 * Change data callback URI to the correct language
	 *
	 * @param array $data
	 * @return array
	 */
	public function change_push_uri( $data ) {
		if ( ! empty( $data['MerchantSettings'] ) ) {
			$current_home_url = home_url( '/' );

			global $sitepress;
			$default_language_code = apply_filters( 'wpml_default_language', '' );
			$default_home_url = trailingslashit( $sitepress->language_url( $default_language_code ) );

			$data['MerchantSettings']['PushUri'] = str_replace( $current_home_url, $default_home_url, $data['MerchantSettings']['PushUri'] );
			$data['MerchantSettings']['CheckoutValidationCallBackUri'] = str_replace( $current_home_url, $default_home_url, $data['MerchantSettings']['CheckoutValidationCallBackUri'] );
			$data['MerchantSettings']['WebhookUri'] = str_replace( $current_home_url, $default_home_url, $data['MerchantSettings']['WebhookUri'] );
		}

		return $data;
	}

	/**
	 * Save data in the session for later use
	 *
	 * @return void
	 */
	public function save_data_in_session() {
		WC()->session->set( 'sco_lang', ICL_LANGUAGE_CODE );

		if ( class_exists( '\WCML\MultiCurrency\Geolocation' ) && Geolocation::isUsed() ) {
			WC()->session->set( 'sco_wpml_country_geolocation', Geolocation::getUserCountry() );
		} else {
			WC()->session->__unset( 'sco_wpml_country_geolocation' );
		}
	}

	/**
	 * Set the current language
	 *
	 * @return void
	 */
	public function set_language_from_session() {
		$lang_code = WC()->session->get( 'sco_lang' );

		if ( $lang_code ) {
			global $sitepress;
			$sitepress->switch_lang( $lang_code );
		}
	}
}
