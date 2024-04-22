<?php
namespace Svea_Checkout_For_Woocommerce\Compat;

use Svea_Checkout_For_Woocommerce\Models\Svea_Item;
use YITH_YWGC_Backend;

if ( ! defined( 'ABSPATH' ) ) {
	exit; } // Exit if accessed directly

/**
 * Compability with the YITH gift cards plugin
 */
class Yith_Gift_Cards_Compat {

	/**
	 * Init function, add hooks
	 *
	 * @return void
	 */
	public function init() {
		add_filter( 'woocommerce_sco_cart_items', [ $this, 'maybe_add_coupons' ] );
		add_action( 'woocommerce_sco_checkout_error_order', [ $this, 'restore_gift_cards' ] );
		add_action( 'woocommerce_sco_checkout_send_json_validation_before', [ $this, 'change_meta' ] );
		add_action( 'woocommerce_sco_after_push_order', [ $this, 'restore_fee_compatibility' ] );

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
		if ( $svea_order_item['MerchantData'] === 'yith_gift_card' ) {
			$should_add = false;
		}

		return $should_add;
	}

	/**
	 * Restore the possibility for YITH to use gift cards as fees
	 *
	 * @param \WC_Order $wc_order
	 * @return void
	 */
	public function restore_fee_compatibility( $wc_order ) {
		$wc_order->delete_meta_data( 'ywgc_gift_card_updated_as_fee' );
		$wc_order->save();
	}

	/**
	 * Remove the meta information that might not be true now and add some other
	 *
	 * @param \WC_Order $wc_order
	 * @return void
	 */
	public function change_meta( $wc_order ) {
		// When the validation has been made we assume that this meta should be removed
		$wc_order->delete_meta_data( '_ywgc_is_gift_card_amount_refunded' );

		// Prevent this from happening before the order is finalized
		$wc_order->update_meta_data( 'ywgc_gift_card_updated_as_fee', 'true' );
		$wc_order->save();
	}

	/**
	 * If the order used any gift cards, restore them into the cart
	 *
	 * @param int $wc_order_id The removed order
	 * @return void
	 */
	public function restore_gift_cards( $wc_order_id ) {
		$wc_order = wc_get_order( $wc_order_id );

		if ( ! $wc_order ) {
			return;
		}

		$gift_cards = $wc_order->get_meta( '_ywgc_applied_gift_cards' );

		if ( ! empty( $gift_cards ) ) {
			foreach ( $gift_cards as $gift_card_code => $gift_card_value ) {
				$args = [
					'gift_card_number' => $gift_card_code,
				];
				$gift_card = new \YITH_YWGC_Gift_Card( $args );
				$new_amount = $gift_card->get_balance() + $gift_card_value;
				$gift_card->update_balance( $new_amount );
			}

			$codes = array_keys( $gift_cards );
			WC()->session->set( 'applied_gift_cards', $codes );

			// Make sure it wont be added back again if we're cancelling the order
			$wc_order->update_meta_data( '_ywgc_is_gift_card_amount_refunded', 'yes' );
			$wc_order->save();
		}
	}

	/**
	 * Get the applied gift cards from the cart
	 *
	 * @return array
	 */
	public function get_gift_cards() {
		return isset( WC()->cart->applied_gift_cards_amounts ) ? WC()->cart->applied_gift_cards_amounts : [];
	}

	/**
	 * Add coupons as a negative cost
	 *
	 * @param Svea_Item[] $items
	 * @return Svea_Item[]
	 */
	public function maybe_add_coupons( $items ) {
		$gift_cards = $this->get_gift_cards();

		if ( ! empty( $gift_cards ) ) {
			foreach ( $gift_cards as $name => $amount ) {
				$svea_item = new Svea_Item();

				$gift_card_name = esc_html__( 'Gift card', 'svea-checkout-for-woocommerce' ) . ' (' . $name . ')';
				$svea_item->map_simple_fee( $gift_card_name, -$amount )
					->set_merchant_data( 'yith_gift_card' );

				$items[] = $svea_item;
			}
		}

		return $items;
	}

}
