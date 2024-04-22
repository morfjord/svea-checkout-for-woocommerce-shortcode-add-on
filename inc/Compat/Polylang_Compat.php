<?php
namespace Svea_Checkout_For_Woocommerce\Compat;

if ( ! defined( 'ABSPATH' ) ) {
	exit; } // Exit if accessed directly

/**
 * Compability with Polylang
 */
class Polylang_Compat {

	/**
	 * Init function, add hooks
	 *
	 * @return void
	 */
	public function init() {
		add_action( 'woocommerce_sco_before_session_load_cart', [ $this, 'set_language_from_session' ] );
		// Since the cart is flushed between the runs, the language has to be set again
		add_action( 'woocommerce_sco_cart_session_after', [ $this, 'set_language_from_session' ] );

		add_action( 'woocommerce_sco_session_data', [ $this, 'save_lang_in_session' ] );

		add_filter( 'woocommerce_sco_create_order', [ $this, 'change_push_uri' ] );
	}

	/**
	 * Change data callback URI to the correct language
	 *
	 * @param array $data
	 *
	 * @return array
	 */
	public function change_push_uri( $data ) {
		if ( ! empty( $data['MerchantSettings'] ) ) {
			$data['MerchantSettings']['PushUri'] = str_replace( pll_home_url(), home_url( '/' ), $data['MerchantSettings']['PushUri'] );
			$data['MerchantSettings']['CheckoutValidationCallBackUri'] = str_replace( pll_home_url(), home_url( '/' ), $data['MerchantSettings']['CheckoutValidationCallBackUri'] );
			$data['MerchantSettings']['WebhookUri'] = str_replace( pll_home_url(), home_url( '/' ), $data['MerchantSettings']['WebhookUri'] );
		}

		return $data;
	}

	/**
	 * Save the language in the session for later use
	 *
	 * @return void
	 */
	public function save_lang_in_session() {
		WC()->session->set( 'sco_lang', pll_current_language() );
	}

	/**
	 * Set the current language
	 *
	 * @return void
	 */
	public function set_language_from_session() {
		$lang_code = WC()->session->get( 'sco_lang' );

		if ( $lang_code ) {
			PLL()->curlang = PLL()->model->get_language( $lang_code );
		}
	}

}
