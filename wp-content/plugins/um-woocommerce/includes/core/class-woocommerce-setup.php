<?php
namespace um_ext\um_woocommerce\core;

if ( ! defined( 'ABSPATH' ) ) exit;


/**
 * Class WooCommerce_Setup
 * @package um_ext\um_woocommerce\core
 */
class WooCommerce_Setup {


	/**
	 * @var array
	 */
	var $settings_defaults;


	/**
	 * WooCommerce_Setup constructor.
	 */
	function __construct() {
		//settings defaults
		$this->settings_defaults = array(
			'woo_oncomplete_role'                   => '',
			'woo_oncomplete_except_roles'           => '',
			'woo_onhold_change_roles'               => 0,
			'woo_onrefund_role'                     => '',
			'woo_remove_roles'                      => 0,
			'woo_hide_billing_tab_from_account'     => 0,
			'woo_hide_shipping_tab_from_account'    => 0,
			'profile_tab_purchases'                 => 1,
			'profile_tab_purchases_privacy'         => 0,
			'profile_tab_product-reviews'           => 1,
			'profile_tab_product-reviews_privacy'   => 0,
		);
	}


	/**
	 *
	 */
	function set_default_settings() {
		$options = get_option( 'um_options' );
		$options = empty( $options ) ? array() : $options;

		foreach ( $this->settings_defaults as $key => $value ) {
			//set new options to default
			if ( ! isset( $options[$key] ) )
				$options[$key] = $value;

		}

		update_option( 'um_options', $options );
	}


	/**
	 *
	 */
	function run_setup() {
		$this->set_default_settings();
	}
}