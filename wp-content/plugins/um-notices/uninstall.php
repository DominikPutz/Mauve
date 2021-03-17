<?php
/**
 * Uninstall UM Notices
 *
 */

// Exit if accessed directly.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) exit;


if ( ! defined( 'um_notices_path' ) ) {
	define( 'um_notices_path', plugin_dir_path( __FILE__ ) );
}

if ( ! defined( 'um_notices_url' ) ) {
	define( 'um_notices_url', plugin_dir_url( __FILE__ ) );
}

if ( ! defined( 'um_notices_plugin' ) ) {
	define( 'um_notices_plugin', plugin_basename( __FILE__ ) );
}

$options = get_option( 'um_options', array() );
if ( ! empty( $options['uninstall_on_delete'] ) ) {
	if ( ! class_exists( 'um_ext\um_notices\core\Notices_Setup' ) ) {
		require_once um_notices_path . 'includes/core/class-notices-setup.php';
	}

	$notices_setup = new um_ext\um_notices\core\Notices_Setup();

	//remove settings
	foreach ( $notices_setup->settings_defaults as $k => $v ) {
		unset( $options[ $k ] );
	}

	unset( $options['um_notices_license_key'] );

	update_option( 'um_options', $options );

	$um_notices = get_posts( array(
		'post_type'     => 'um_notice',
		'numberposts'   => -1
	) );

	foreach ( $um_notices as $um_notice ) {
		wp_delete_post( $um_notice->ID, 1 );
	}

	delete_option( 'um_notices_last_version_upgrade' );
	delete_option( 'um_notices_version' );
}