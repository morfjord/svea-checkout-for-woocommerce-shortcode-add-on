<?php

namespace Svea_Checkout_For_Woocommerce;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Class Helper
 */
class Helper {

	/**
	 * Convert codes into messages
	 *
	 * @param \Exception $e
	 * @return string
	 */
	public static function get_svea_error_message( \Exception $e ) {
		$code = $e->getCode();

		switch ( $code ) {
			case 400:
				return esc_html__( 'The current currency is not supported in the selected country. Please switch country or currency and reload the page.', 'svea-checkout-for-woocommerce' );
			case 401:
				return esc_html__( 'The checkout cannot be displayed due to an error in the connection to Svea. Please contact the shop owner regarding this issue.', 'svea-checkout-for-woocommerce' );
			case 403:
				return esc_html__( 'Order could not be fetched. Please contact the shop owner regarding this issue', 'svea-checkout-for-woocommerce' );
			case 1000:
				return esc_html__( 'Could not connect to Svea - 404', 'svea-checkout-for-woocommerce' );
			default:
				return esc_html( $e->getMessage() );
		}
	}

	/**
	 * Get the locale needed for Svea
	 *
	 * @param string $locale
	 * @return string
	 */
	public static function get_svea_locale( $locale ) {
		switch ( $locale ) {
			case 'sv_SE':
				return 'sv-SE';
			case 'nn_NO':
			case 'nb_NO':
				return 'nn-NO';
			case 'fi_FI':
				return 'fi-FI';
			case 'da_DK':
				return 'da-DK';
			default:
				return 'sv-SE';
		}
	}

	/**
	 * Splits full names into first name and last name
	 *
	 * @param $full_name
	 *
	 * @return string[]
	 */
	public static function split_customer_name( $full_name ) {
		$customer_name = [
			'first_name' => '',
			'last_name'  => '',
		];

		// Split name and trim whitespace
		$full_name_split = array_map( 'trim', explode( ' ', trim( $full_name ) ) );

		$full_name_split_count = count( $full_name_split );

		if ( $full_name_split_count > 0 ) {
			$customer_name['first_name'] = $full_name_split[0];

			if ( $full_name_split_count > 1 ) {
				$customer_name['last_name'] = implode( ' ', array_slice( $full_name_split, 1, $full_name_split_count - 1 ) );
			}
		}

		return $customer_name;
	}

	/**
	 * Convert strings into camelCase
	 *
	 * @param   string $str
	 *
	 * @return string
	 */
	public static function to_pascal_case( $str ) {
		$str = preg_replace_callback(
			'/[A-Z]{3,}/',
			function ( $m ) {
				return ucfirst( strtolower( $m[0] ) );
			},
			$str
		);

		$str = preg_replace_callback(
			'/([^A-Za-z0-9]+([A-Za-z]))/',
			function ( $m ) {
				return ucfirst( $m[2] );
			},
			$str
		);

		return ucfirst( $str );
	}

	/**
	 * Convert strings into snake-case
	 *
	 * @param   string $str
	 *
	 * @return string
	 */
	public static function to_snake_case( $str ) {
		$str = preg_replace_callback(
			'/([^A-Za-z0-9]+([A-Za-z0-9]))|([a-z]([A-Z]))/',
			function ( $m ) {
				if ( ! empty( $m[2] ) ) {
					return '_' . $m[2];
				}

				if ( ! empty( $m[4] ) ) {
					return $m[3][0] . '_' . $m[4];
				}
			},
			$str
		);

		return preg_replace( '/^[^A-Za-z0-9]+/', '', strtolower( $str ) );
	}

	/**
	 * Get the array value of a string position.
	 * Example: BillingAddress:FirstName would return $array['BillingAddress']['FirstName']
	 *
	 * @param array  $array
	 * @param string $address
	 * @param string $delimiter
	 *
	 * @return mixed
	 */
	public static function delimit_array( $array, $address, $delimiter = ':' ) {
		$parts = explode( ',', $address );

		foreach ( $parts as $part ) {
			$address = explode( $delimiter, $part );
			$steps   = count( $address );

			$val = $array;
			for ( $i = 0; $i < $steps; $i++ ) {
				// Every iteration brings us closer to the truth
				$val = $val[ $address[ $i ] ];
			}

			if ( ! empty( $val ) ) {
				break;
			}
		}

		return $val;
	}


	/**
	 * Get the first key of the array
	 *
	 * @param array $arr
	 *
	 * @return string
	 */
	public static function array_key_first( $arr ) {
		reset( $arr );
		$first_key = key( $arr );

		return ! empty( $arr ) ? $first_key : '';
	}

}
