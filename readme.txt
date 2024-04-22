=== Svea Checkout for WooCommerce ===
Contributors: sveaekonomi, thegeneration
Tags: woocommerce, svea ekonomi, checkout, payment gateway, svea checkout, credit card, invoice, part payment, direct bank
Donate link: https://www.svea.com/
Requires at least: 4.9
Tested up to: 6.4.3
Requires PHP: 8.0
WC requires at least: 5.0.0
WC tested up to: 8.5.2
License: Apache 2.0
License URI: https://www.apache.org/licenses/LICENSE-2.0
Stable tag: 2.7.2

Supercharge your WooCommerce Store with powerful features to pay via Svea Checkout

== Upgrade notice ==

If you've recently upgraded to Svea Checkout 2.0 you might've noticed that new orders now can appear with the status "Pending payment".
This is normal and expected as orders now are being created once a payment has been started (the customer selected a payment method and clicked "Pay").

However you might also have noticed that older orders appeared with the status "Pending payments". These orders are not complete orders and was needed in the background for Svea Checkout 1.0.
If the order is a couple of days old, you can safely remove them since it never will change status.

== Description ==

Accept Credit cards, invoice, direct bank and part payments in your WooCommerce store. Svea Checkout for WooCommerce is a fully featured checkout solution which simplifies the checkout for your customers and increases conversion.

Advantages for you as a customer:

* Supports sales to both B2B and B2C clients
* Machine learning - learns how your customer likes to pay
* One payment gateway where all payment methods and merchant agreements are included

= Part payment widget =

The plugin provides a widget that can be displayed on the product pages to inform your customers that they can pay with part payments in the checkout. It will display the lowest monthly price which they can pay through part payments.

To activate the feature, follow these steps:

1. Go to **WooCommerce > Settings > Payments > Svea Checkout**
2. Check the box **Display product part payment widget**
3. Select where on the page you want to display the widget
4. View the part payment widget on the product page for eligable products. If the widget is not displayed it might be due to the price since part payment plans often require a minimum amount.

There's also a shortcode available to display the part payment widget on a product page. Add the shortcode `[svea_checkout_part_payment_widget]` to the product page you want to use it on. Or call `do_shortcode()` through a template file.

== Installation ==

1. Install the plugin either through your web browser in the WordPress admin panel or manually through FTP.
2. Activate the plugin
3. Configure the credentials by browsing to WooCommerce > Settings > Payments > Svea Checkout
4. Check the box "Activate Svea Checkout"
5. Enter "Merchant ID" and "Secret", these credentials are required for the plugin to work
6. Follow Sveas instructions to get your production credentials

== Upgrade Notice ==

= 2.7.2 =
2.7.2 is a patch release

= 2.7.1 =
2.7.1 is a patch release

= 2.7.0 =
2.7.0 is a minor release

= 2.6.5 =
2.6.5 is a patch release

= 2.6.4 =
2.6.4 is a patch release

= 2.6.3 =
2.6.3 is a patch release

= 2.6.2 =
2.6.2 is a patch release

= 2.6.1 =
2.6.1 is a patch release

= 2.6.0 =
2.6.0 is a minor release

= 2.5.2 =
2.5.2 is a patch release

= 2.5.1 =
2.5.1 is a patch release

= 2.5.0 =
2.5.0 is a minor release

= 2.4.4 =
2.4.4 is a patch release

= 2.4.3 =
2.4.3 is a patch release

= 2.4.2 =
2.4.2 is a patch release

= 2.4.1 =
2.4.1 is a patch release

= 2.4.0 =
2.4.0 is a minor release

= 2.3.3 =
2.3.3 is a patch release

= 2.3.2 =
2.3.2 is a patch release

= 2.3.1 =
2.3.1 is a patch release

= 2.3.0 =
2.3.0 is a minor release

= 2.2.2 =
2.2.2 is a patch release

= 2.2.1 =
2.2.1 is a patch release

= 2.2.0 =
2.2.0 is a minor release

= 2.1.6 =
2.1.6 is a patch release

= 2.1.5 =
2.1.5 is a patch release

= 2.1.4 =
2.1.4 is a patch release

= 2.1.3 =
2.1.3 is a patch release

= 2.1.2 =
2.1.2 is a patch release

= 2.1.1 =
2.1.1 is a patch release

= 2.1.0 =
2.1.0 is a minor release

= 2.0.3 =
2.0.3 is a patch release

= 2.0.2 =
2.0.2 is a patch release

= 2.0.1 =
2.0.1 is a patch release

= 2.0.0 =
2.0.0 is a rewrite

= 1.18.3 =
1.18.3 is a patch release.

= 1.18.2 =
1.18.2 is a patch release.

= 1.18.1 =
1.18.1 is a patch release.

= 1.18.0 =
1.18.0 is a minor release.

= 1.17.2 =
1.17.2 is a patch release.

= 1.17.1 =
1.17.1 is a patch release.

= 1.17.0 =
1.17.0 is a minor release.

= 1.16.0 =
1.16.0 is a minor release.

= 1.15.1 =
1.15.1 is a patch release.

= 1.15.0 =
1.15.0 is a minor release.

= 1.14.3 =
1.14.3 is a patch release.

= 1.14.2 =
1.14.2 is a patch release.

= 1.14.1 =
1.14.1 is a patch release.

= 1.14.0 =
1.14.0 is a minor release.

= 1.13.2 =
1.13.2 is a patch release.

= 1.13.1 =
1.13.1 is a patch release.

= 1.13.0 =
1.13.0 is a minor release.

= 1.12.0 =
1.12.0 is a minor release.

= 1.11.0 =
1.11.0 is a minor release.

= 1.10.0 =
1.10.0 is a minor release.

= 1.9.0 =
1.9.0 is a minor release.

= 1.8.0 =
1.8.0 is a minor release.

= 1.7.2 =
1.7.2 is a patch release.

= 1.7.1 =
1.7.1 is a patch release.

= 1.7.0 =
1.7.0 is a minor release.

= 1.6.0 =
1.6.0 is a minor release.

= 1.5.0 =
1.5.0 is a minor release.

== Screenshots ==

1. Initial checkout page before contact details are entered.
2. Checkout page after contact details are entered and possible payment methods are displayed.
3. Thank-you page displayed after successful purchase through Svea Checkout.
4. Settings page used to configure the plugin.

== Changelog ==

= 2.7.2 2024-04-15 =
* Correctly save the new orderID to ensure that an order gets delivered in PaymentAdmin

= 2.7.1 2024-04-08 =
* Prevent payment validations to make the order not syncing order status

= 2.7.0 2024-04-03 =
* Added support for WooCommerce Smart Coupons store credit
* Removed limit to recurring payments not being able to process orders with initial cost of 0
* Display a warning if validation callback have recorded too long response times.
* Added "SWISH_PF" as a payment method allowing admin to see that an order used that method
* Corrected double tax applied from YITH gift cards
* Better handling of double checkout sessions to combat multiple orders being created
* Restored attribution data functionality
* Set correct variation while syncing order rows from Svea in finalize callback
* Fix for required fields in Ultimate Affiliate Pro
* Prevent crash in part pay widget if merchant credentials are missing/faulty for the current currency
* Correctly set "tms_ref" on webhook callback
* Prevent checkout from updating more than needed
* Better handling for recurring orders that fail
* Added filters to allow skipping of syncing order row items
* Added process ID in logs in order to easier follow a chain of events

= 2.6.5 2024-02-14 =
* Corrected Ingrid load order
* Added support for attribution data in Woo. You can now see the source of the order
* Added compatibility for the plugin Ultimate affiliate pro
* Create a new checkout when switching between recurring and non-recurring carts
* General improvements

= 2.6.4 2024-01-10 =
* Corrected currency for WPML

= 2.6.3 2024-01-02 =
* Corrected WPML geolocation based currency switcher
* Removed PHP notice on order lookup
* Added multilingual support for nShift webhooks

= 2.6.2 2023-12-21 =
* Corrected error message in webhook handler when PA isn't ready yet

= 2.6.1 2023-12-18 =
* Restored setup of company name at checkout

= 2.6.0 2023-12-11 =
* New status "Awaiting status" for orders which are set at "Pending" in PaymentAdmin. 
* Fields registered under checkout fields "order" will now appear in the checkout.
* Fixed fatal error for push callbacks when trying to access removed sessions.
* Add IP-restriction to recurring order callbacks.
* Automatically populate $_POST with checkout data for better compatibility with other plugins

= 2.5.2 2023-10-26 =
* Restored compatibility with WPML geolocation based currency switcher

= 2.5.1 2023-10-26 =
* Corrected wrong table column name
* Added support for WPC Product Bundles in the part pay widget

= 2.5.0 2023-10-25 =
* Added support for recurring orders via WooCommerce Subscriptions
* Better support for account creation during checkout
* Added new table to properly map sessions between Svea and WooCommerce

= 2.4.4 2023-10-02 =
* Added support for HPOS

= 2.4.3 2023-09-28 =
* Ensure the checkout iframe reloads if changing country on the checkout page.
* Add filter `woocommerce_sco_should_do_cart_items_mapping_validation` to turn off cart item mapping validation.

= 2.4.2 2023-08-04 =
* Improve logic for multiple shipping locations

= 2.4.1 2023-07-28 =
* Populate WooComerce customer object with informaiton from Svea when updating the cart
* Better support for Fraktjakt
* Trigger an update in the checkout when customer switches between company and private
* Fixed typo in description
* Added description to payment gateway visible when multiple payment gateways are being used

= 2.4.0 2023-07-24 =
* Allow orders with 0 sum to be processed (requires Svea to enable on account)
* Remove loading of missing css file
* Remove notice from WPML
* Remove state from the checkout if Svea is used since Svea don't handle states
* Corrected path name in comment for overriding the template
* Remove "order awaiting payment" on new order creation to prevent faulty checkout values
* Fix for Polylang and WPML callback/push URLs
* Allow classes to be accessed via svea_checkout(). Example: svea_checkout()->compat->polylang;

= 2.3.3 2023-07-05 =
* Better logging for item missmatch
* Improved mapping for shipping for methods that use more references than method ID and instance ID such as table rate shipping

= 2.3.2 2023-07-04 =
* Added error message if the cart does not match items in Svea
* Only sync order items once when order is finalized
* Removed false "depricated template" notification on the status page
* Using better function for getting default country

= 2.3.1 2023-05-31 =
* Fix for non loading cart if store uses "hide shipping until address is provided"

= 2.3.0 2023-05-26 =
* Improved logic for nShift when chosing shipping
* Fix for shipping when multiple zones are being used by ensuring customer information is present when validation callback is made
* Prevents cash rounding from replacing removed items. Reworked logic for updating orders in WooCommerce and syncing to PaymentAdmin. 

= 2.2.2 2023-05-09 =
* Stop the change of order status if the order is failed
* Make sure that order id is present when checking for order reference
* Prevent orders from going from Cancelled to Processing by creating new orders for cancelled orders
* Change order status in a later stage so that org-number is present in order processing email
* Corrected ID used for "woocommerce_resume_order" so that it uses a correct ID if any

= 2.2.1 2023-04-14 =
* Fixed cookie-data which allows users to login during checkout and not getting the error "Your session timed out"
* Corrected logic regarding cash rounding
* Corrected textdomain for "Cash rounding" making it translatable again
* Performance improvements

= 2.2.0 2023-04-13 =
* Added filter for order information keys in order for developers to extend functionality easier in the checkout
* Fixed typo where sprintf used textdomain as input instead of message
* Changed edge case where session would overwrite shipping method if request took to long to finish
* Corrected if statement that would refresh the iframe more than needed
* Changed behaviour so that multiple orders don't have to be made.
* Changed behaviour so that a order does not have to be set to "failed" between validate callbacks

= 2.1.6 2023-03-09 =
* Corrected compatibility code for Polylang preventing double products in checkout

= 2.1.5 2023-03-08 =
* Prevent emails being sent when order is temporarily set to failed
* Send confirmation email later so that the payment method is included to the customer

= 2.1.4 2023-03-03 =
* Temporary set order to failed if validation is made multiple times. This makes it easier for things such as coupons to be correctly validated
* Include namespace in class check
* Fixed bug where YITH WooCommerce gift card would case an issue if checkout failed
* Removed unnecessary validation check for pushes
* Renamed "Payment intent" to "Payment validations" in order to reduce confusion in metabox

= 2.1.3 2023-02-22 =
* Fix for when using WPML but not WCML

= 2.1.2 2023-02-21 =
* Better compatibility with WCML currency switcher based on geolocation
* Js: No longer reload checkout if postcode is empty

= 2.1.1 2023-02-13 =
* Remove dev code which casued fatal error

= 2.1.0 2023-02-10 =
* nShift integration directly in the checkout
* Restore styling for part payment
* Only enqueue checkout script and style on the checkout page
* Better support for shipping options with special IDs such as Table Rate Shipping
* Correct country for WPML/WCML in order to use correct currency
* Support for conditional countries by WCML
* Create new order if session customer id changed (user logged/in out during checkout)
* Prevent cancelling in Svea from older WooCommerce orders
* Fallback to shipping address is no billing address is available
* Corrected labels for metabox while logging is enabled
* Removed unnecessary heartbeat function from javascript
* Updated dependency for sveawebpay/php-checkout to ^1.4
* New structure for MerchantData

= 2.0.3 2023-01-24 =
* Ensure the WooCommerce session is available
* Create a new order if current ID has been cancelled
* Correct the array key for refunds
* Correct the PSR-4 autoload name for Yith Gift Cards
* Added more logging

= 2.0.2 2023-01-16 =
* Corrected comparison for order sync
* Corrected changelog version for 2.0.1 
* Removed unused setting
* Added missing translations

= 2.0.1 2023-01-13 =
* Corrected call to esc_html__()
* Catch the output from other sources before sending response to Svea in validation_callback

= 2.0.0 2023-01-09 =
* Rewrite of order logic. Now follows WooCommerce logic more closely and orders are not created in the background

= 1.18.3 2022-08-24 =
* Correctly map the payment methods Swish, Vipps and Mobilepay
* Better delivery for orders in Svea Payment Admin when orders are without shipping

= 1.18.2 2021-05-23 =
* Allow filters to change the cleanup interval
* Updated supported versions

= 1.18.1 2021-04-12 =
* Updated supported versions

= 1.18.0 2021-01-11 =
* Partially support for YITH Coupons free version only
* Changed order of sync and hook for better compability

= 1.17.2 2020-10-07 =
* Observe postcode changes even if checkout is loaded earlier

= 1.17.1 2020-09-21 =
* Update Svea logo
* Fix line calculations

= 1.17.0 2020-09-11 =
* Redirect to standard WooCommerce checkout page if cart total is 0
* Properly calculate credit amounts when products have many decimals

= 1.16.0 2020-08-05 =
* Shortcode for part payment widget
* Create new order when there's a change in currency

= 1.15.1 2020-05-14 =
* Handle problems with crediting orders that contain order rows with many decimals
* Properly fetch the order row ID when adding order rows to an order

= 1.15.0 2020-04-14 =
* Storing and displaying company reg numbers on view order page

= 1.14.3 2020-04-10 =
* Optimizations

= 1.14.2 2020-04-06 =
* Multisite support

= 1.14.1 2020-03-27 =
* Checkout performance improvements

= 1.14.0 2020-03-24 =
* Add support for WooCommerce 4.0
* Add support for multisite network activation

= 1.13.2 2020-02-03 =
* Split customer reference to populate first- and lastname fields on company orders

= 1.13.1 2019-09-26 =
* Change do_action-format for woocommerce checkout order processed

= 1.13.0 2019-06-27 =
* Add support for Denmark
* Start logging pushes to add warnings if the connection to Svea is not working in the future

= 1.12.0 2019-06-18 =
* Filters to enable reload of checkout
* Only target Svea-checkout form for changes to prevent unneeded ajax-requests

= 1.11.0 2019-04-24 =
* Compatibility with WooCommerce 3.6
* Add filter to toggle order rows-sync after completed order

= 1.10.0 2019-03-13 =
* Add ability to hide elements in the Svea iframe through settings

= 1.9.0 2019-03-13 =
* Add ability to hide elements in the Svea iframe through settings

= 1.9.0 2019-02-14 =
* Add support for invoice fee

= 1.8.0 2019-02-01 =
* Add setting to keep cancelled orders
* Prevent empty cancelled orders from showing up in the order list

= 1.7.2 2019-01-03 =
* Upgrade Svea integration package
* Test with WordPress 5.0.2
* Changes to syncing of unfinished orders

= 1.7.1 2018-11-18 =
* Fix calculations for whether or not to display part payment widget on products

= 1.7.0 2018-10-18 =
* Added support for WooCommerce Subscribe to Newsletter and other third party plugins adding fields to the checkout
* Add new part payment widget with options to show and select position on product page
* Add option to disable ZIP code syncing between Svea and WooCommerce, usable for shops only selling digital wares

= 1.6.0 2018-09-28 =
* Add support for WooCommerce Shipping Calculator
* Add versioning to template files
* Add new hooks and filters

= 1.5.0 2018-06-21 =
* Add support for zipcode based shipping
* Optimize and improve load times for the checkout

== Frequently Asked Questions ==

= What type of clients can I sell to through the gateway? =

You can sell to both B2B and B2C clients through the gateway.