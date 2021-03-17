<?php
/**
 * Uninstall UM Woocommerce
 *
 */

// Exit if accessed directly.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) exit;


if ( ! defined( 'um_woocommerce_path' ) ) {
	define( 'um_woocommerce_path', plugin_dir_path( __FILE__ ) );
}

if ( ! defined( 'um_woocommerce_url' ) ) {
	define( 'um_woocommerce_url', plugin_dir_url( __FILE__ ) );
}

if ( ! defined( 'um_woocommerce_plugin' ) ) {
	define( 'um_woocommerce_plugin', plugin_basename( __FILE__ ) );
}

$options = get_option( 'um_options', array() );

if ( ! empty( $options['uninstall_on_delete'] ) ) {
	if ( ! class_exists( 'um_ext\um_woocommerce\core\WooCommerce_Setup' ) ) {
		require_once um_woocommerce_path . 'includes/core/class-woocommerce-setup.php';
	}

	$woocommerce_setup = new um_ext\um_woocommerce\core\WooCommerce_Setup();

	//remove settings
	foreach ( $woocommerce_setup->settings_defaults as $k => $v ) {
		unset( $options[ $k ] );
	}

	unset( $options['um_woocommerce_license_key'] );

	update_option( 'um_options', $options );


	global $wpdb;
	$wpdb->query(
		"DELETE 
		FROM {$wpdb->postmeta} 
		WHERE meta_key LIKE '_um_woo%'"
	);
	$wpdb->query(
		"DELETE 
		FROM {$wpdb->usermeta} 
		WHERE meta_key LIKE 'um_woo%'"
	);

	delete_option( 'um_woocommerce_last_version_upgrade' );
	delete_option( 'um_woocommerce_version' );
}