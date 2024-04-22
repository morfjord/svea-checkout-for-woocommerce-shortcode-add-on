<?php
/**
 * Checkout review order block
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/svea-checkout.php.
 *
 * We will in some rare cases update this file. For this reason, it is important that you keep a look at the version-number and implement the new changes in your theme.
 * If you do not keep this file updated, there is no guarantee that the plugin will work as intended.
 *
 * @author  The Generation
 * @package Svea_Checkout_For_WooCommerce/Templates
 * @version 1.5.0
 */

use Svea_Checkout_For_Woocommerce\Template_Handler;

if( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

wc_print_notices();

// Get checkout object
$checkout = WC()->checkout();

// If checkout registration is disabled and not logged in, the user cannot checkout
if ( ! $checkout->is_registration_enabled() && $checkout->is_registration_required() && ! is_user_logged_in() ) {
	echo apply_filters( 'woocommerce_checkout_must_be_logged_in_message', __( 'You must be logged in to checkout.', 'woocommerce' ) );
	return;
}

do_action( 'woocommerce_sco_before_checkout_page' ); ?>

<section class="wc-svea-checkout-page">
    <div class="wc-svea-checkout-page-inner">
        
        <?php do_action( 'woocommerce_sco_before_order_details' ); ?>
        
        <div class="wc-svea-checkout-order-details">

	        <?php do_action( 'woocommerce_before_checkout_form', $checkout ); ?>

            <?php do_action( 'woocommerce_sco_before_co_form' ); ?>
            
            <form class="wc-svea-checkout-form woocommerce-checkout">
                <?php

                // Billing country selector
                woocommerce_form_field(
                    'billing_country',
                    array(
                        'label'       => esc_html__( 'Country', 'svea-checkout-for-woocommerce' ),
                        'description' => '',
                        'required'    => true,
                        'type'        => 'country',
                    ),
                    WC()->customer->get_billing_country()
                );

                // Billing postcode field

                ?>
                <input id="billing_postcode" type="hidden" name="billing_postcode" value="<?php echo WC()->customer->get_billing_postcode(); ?>" >
                
                <div class="woocommerce-checkout-review-order-wrapper">
                <?php

                $review_order_template = locate_template( 'svea-checkout-for-woocommerce/checkout/review-order.php' );

                if ( $review_order_template == '' ) {
                    $review_order_template = SVEA_CHECKOUT_FOR_WOOCOMMERCE_DIR . '/templates/checkout/review-order.php';
                }

                include $review_order_template;
                
                ?>
                </div>

                <?php if ( ! is_user_logged_in() && WC()->checkout->is_registration_enabled() ) : ?>
                    <div class="wc-svea-checkout-login-field">
                        <?php if ( ! WC()->checkout->is_registration_required() ) : ?>

                            <p class="form-row form-row-wide create-account">
                                <label class="woocommerce-form__label woocommerce-form__label-for-checkbox checkbox">
                                    <input class="woocommerce-form__input woocommerce-form__input-checkbox input-checkbox" id="createaccount" <?php checked( ( true === WC()->checkout->get_value( 'createaccount' ) || ( true === apply_filters( 'woocommerce_create_account_default_checked', false ) ) ), true ); ?> type="checkbox" name="createaccount" value="1" /> <span><?php esc_html_e( 'Create an account?', 'woocommerce' ); ?></span>
                                </label>
                            </p>

                        <?php endif; ?>

                        <?php do_action( 'woocommerce_before_checkout_registration_form', WC()->checkout ); ?>

                        <div class="create-account">
                            <?php if ( WC()->checkout->get_checkout_fields( 'account' ) ) : ?>
                        
                                <?php foreach ( WC()->checkout->get_checkout_fields( 'account' ) as $key => $field ) : ?>
                                    <?php woocommerce_form_field( $key, $field, WC()->checkout->get_value( $key ) ); ?>
                                <?php endforeach; ?>
                                <div class="clear"></div>
  
                            <?php endif; ?>
                            <p>
                                <?php echo esc_html_e( 'A password will be generated and sent to your email', 'svea-checkout-for-woocommerce' ); ?>
                            </p>
                        </div>

                        <?php do_action( 'woocommerce_after_checkout_registration_form', WC()->checkout ); ?>
                    </div>
                <?php endif; ?>

                <?php do_action( 'woocommerce_sco_before_notes_field' ); ?>

				<?php do_action( 'woocommerce_before_order_notes', $checkout ); ?>

                <div class="wc-svea-checkout-other-fields">
                    <?php foreach ( $checkout->get_checkout_fields( 'order' ) as $key => $field ) : ?>
                        <?php woocommerce_form_field( $key, $field, $checkout->get_value( $key ) ); ?>
                    <?php endforeach; ?>
                </div>

				<?php do_action( 'woocommerce_after_order_notes', $checkout ); ?>
				
                <?php do_action( 'woocommerce_sco_after_notes_field' ); ?>

                <?php do_action( 'woocommerce_checkout_after_customer_details' ); ?>

	            <?php do_action( 'woocommerce_after_checkout_billing_form', $checkout ); ?>
                
            </form>

            <?php do_action( 'woocommerce_sco_after_co_form' ); ?>

	        <?php do_action( 'woocommerce_after_checkout_form', $checkout ); ?>
            
            <?php if ( count( WC()->payment_gateways->get_available_payment_gateways() ) > 1 ) : ?>
                <a id="sco-change-payment" class="button" href="#"><?php esc_html_e( 'Other payment options', 'svea-checkout-for-woocommerce' ) ?></a>
            <?php endif; ?>

        </div>
        <?php do_action( 'woocommerce_sco_before_sco_module' ); ?>
        
        <div class="wc-svea-checkout-checkout-module">
            <?php Template_Handler::get_svea_snippet(); ?>
        </div>
        
        <?php do_action( 'woocommerce_sco_after_sco_module' ); ?>

    </div>
    
</section>

<?php do_action( 'woocommerce_sco_after_checkout_page' );
