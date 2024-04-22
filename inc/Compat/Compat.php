<?php
namespace Svea_Checkout_For_Woocommerce\Compat;

if ( ! defined( 'ABSPATH' ) ) {
	exit; } // Exit if accessed directly

class Compat {

	/**
	 * Yith WooCommerce Gift Cards compatibility class
	 *
	 * @var Yith_Gift_Cards_Compat
	 */
	public $gift_cards;

	/**
	 * Woocs compatibility class
	 *
	 * @var Woocs_Compat
	 */
	public $woocs;

	/**
	 * Aelia currency switcher compatibility class
	 *
	 * @var AeliaCS_Compat
	 */
	public $aelia_cs;

	/**
	 * Polylang compatibility class
	 *
	 * @var Polylang_Compat
	 */
	public $polylang;

	/**
	 * WPML compatibility class
	 *
	 * @var WPML_Compat
	 */
	public $wpml;

	/**
	 * Woocommerce multicurrency compatibility class
	 *
	 * @var WooMc_Compat
	 */
	public $woomc;

	/**
	 * WPC Product Bundles compatibility class
	 *
	 * @var WPC_Product_Bundles_Compat
	 */
	public $wpc_product_bundles;

	/**
	 * Ingrid compat file
	 *
	 * @var Ingrid_Compat
	 */
	public $ingrid;

	/**
	 * WC Smart Coupons compatibility class
	 *
	 * @var WC_Smart_Coupons_Compat
	 */
	public $wc_smart_coupons;

	/**
	 * UAP compat file
	 *
	 * @var UAP_Compat
	 */
	public $uap;

	/**
	 * Init function, add hooks
	 *
	 * @return void
	 */
	public function init() {
		add_action( 'init', [ $this, 'check_for_plugins' ], 1 );
		add_action( 'plugins_loaded', [ $this, 'check_for_plugins_early' ], 1 );
	}

	/**
	 * Check for plugins that might need compatibility
	 *
	 * @return void
	 */
	public function check_for_plugins() {
		if ( function_exists( 'YITH_YWGC' ) ) {
			$this->gift_cards = new Yith_Gift_Cards_Compat();
			$this->gift_cards->init();
		}

		if ( defined( 'WOOCS_VERSION' ) ) {
			$this->woocs = new Woocs_Compat();
			$this->woocs->init();
		}

		if ( class_exists( 'WC_Aelia_CurrencySwitcher' ) ) {
			$this->aelia_cs = new AeliaCS_Compat();
			$this->aelia_cs->init();
		}

		if ( defined( 'POLYLANG_ROOT_FILE' ) ) {
			$this->polylang = new Polylang_Compat();
			$this->polylang->init();
		}

		if ( defined( 'ICL_SITEPRESS_VERSION' ) ) {
			$this->wpml = new WPML_Compat();
			$this->wpml->init();
		}

		if ( defined( 'WOOCOMMERCE_MULTICURRENCY_VERSION' ) ) {
			$this->woomc = new WooMc_Compat();
			$this->woomc->init();
		}

		if ( defined( 'WOOSB_VERSION' ) ) {
			$this->wpc_product_bundles = new WPC_Product_Bundles_Compat();
			$this->wpc_product_bundles->init();
		}

		if ( defined( 'UAP_PLUGIN_VER' ) ) {
			$this->uap = new UAP_Compat();
			$this->uap->init();
		}

		if ( defined( 'WC_SC_PLUGIN_FILE' ) ) {
			$this->wc_smart_coupons = new WC_Smart_Coupons_Compat();
			$this->wc_smart_coupons->init();
		}
	}

	/**
	 * Check for plugins that might need compatibility on an early hook
	 *
	 * @return void
	 */
	public function check_for_plugins_early() {
		if ( defined( 'INGRID_PLUGIN_VERSION' ) ) {
			$this->ingrid = new Ingrid_Compat();
			$this->ingrid->init();
		}
	}
}
