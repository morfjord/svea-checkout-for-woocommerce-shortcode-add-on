<?php
namespace Svea_Checkout_For_Woocommerce\Compat;

use Svea_Checkout_For_Woocommerce\Models\Svea_Item;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

/**
 * Compability with the WooCommerce Smart Coupons plugin
 */
class WC_Smart_Coupons_Compat {

	/**
	 * Init function, add hooks
	 *
	 * @return void
	 */
	public function init() {
		add_filter( 'woocommerce_sco_cart_items', [ $this, 'maybe_add_coupons' ] );
		add_action( 'woocommerce_sco_process_push_before', [ $this, 'add_spoof_hook' ] );

		add_filter( 'woocommerce_sco_should_add_new_item', [ $this, 'should_add_coupon_to_order' ], 10, 2 );
	}

	/**
	 * Check if the order item should be added to the order
	 *
	 * @param bool $should_add
	 * @param array $svea_order_item
	 * @return bool
	 */
	public function should_add_coupon_to_order( $should_add, $svea_order_item ) {
		if ( $svea_order_item['MerchantData'] === 'wc_smart_coupon' ) {
			$should_add = false;
		}

		return $should_add;
	}

	/**
	 * Add spoof hook
	 *
	 * @return void
	 */
	public function add_spoof_hook() {
		add_action( 'woocommerce_order_after_calculate_totals', [ $this, 'spoof_action_to_calculate_totals' ], 5 );
	}

	/**
	 * Spoof the action to calculate totals
	 *
	 * @return void
	 */
	public function spoof_action_to_calculate_totals() {
		$_POST['action'] = 'woocommerce_save_order_items';
	}

	/**
	 * Add coupons as a negative cost
	 *
	 * @param Svea_Item[] $items
	 * @return Svea_Item[]
	 */
	public function maybe_add_coupons( $items ) {
		foreach ( WC()->cart->get_coupons() as $wc_coupon ) {
			/** @var \WC_Coupon $wc_coupon */
			if ( $wc_coupon->get_discount_type() === 'smart_coupon' ) {
				$svea_item = new Svea_Item();

				$gift_card_name = esc_html__( 'Gift card', 'svea-checkout-for-woocommerce' ) . ' (' . $wc_coupon->get_code() . ')';

				$amount = WC()->cart->get_coupon_discount_amount( $wc_coupon->get_code() ) +
					WC()->cart->get_coupon_discount_tax_amount( $wc_coupon->get_code() );

				$svea_item->map_simple_fee( $gift_card_name, -$amount )
					->set_merchant_data( 'wc_smart_coupon' );

				$items[] = $svea_item;
			}
		}

		return $items;
	}

}
