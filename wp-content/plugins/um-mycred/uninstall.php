<?php
/**
 * Uninstall UM myCRED
 *
 */

// Exit if accessed directly.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) exit;


if ( ! defined( 'um_mycred_path' ) ) {
	define( 'um_mycred_path', plugin_dir_path( __FILE__ ) );
}

if ( ! defined( 'um_mycred_url' ) ) {
	define( 'um_mycred_url', plugin_dir_url( __FILE__ ) );
}

if ( ! defined( 'um_mycred_plugin' ) ) {
	define( 'um_mycred_plugin', plugin_basename( __FILE__ ) );
}

$options = get_option( 'um_options', array() );

if ( ! empty( $options['uninstall_on_delete'] ) ) {
	if ( ! class_exists( 'myCRED_Setup' ) ) {
		require_once um_mycred_path . 'includes/core/class-mycred-setup.php';
	}

	$mycred_setup = new um_ext\um_mycred\core\myCRED_Setup();

	//remove settings
	foreach ( $mycred_setup->settings_defaults as $k => $v ) {
		unset( $options[ $k ] );
	}

	unset( $options['um_mycred_license_key'] );

	update_option( 'um_options', $options );

	delete_option( 'um_mycred_last_version_upgrade' );
	delete_option( 'um_mycred_version' );
}