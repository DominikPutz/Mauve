<?php
/**
* Uninstall UM bbPress
*
*/

// Exit if accessed directly.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) exit;


if ( ! defined( 'um_bbpress_path' ) ) {
	define( 'um_private_content_path', plugin_dir_path( __FILE__ ) );
}

if ( ! defined( 'um_bbpress_url' ) ) {
	define( 'um_rivate_content_url', plugin_dir_url( __FILE__ ) );
}

if ( ! defined( 'um_bbpress_plugin' ) ) {
	define( 'um_rivate_content_plugin', plugin_basename( __FILE__ ) );
}

$options = get_option( 'um_options', array() );

if ( ! empty( $options['uninstall_on_delete'] ) ) {
	if ( ! class_exists( 'um_ext\um_private_content\core\Private_Content_Setup' ) ) {
		require_once um_private_content_path . 'includes/core/class-private-content-setup.php';

		$private_setup = new um_ext\um_private_content\core\Private_Content_Setup();

		//remove settings
		foreach ( $private_setup->settings_defaults as $k => $v ) {
			unset( $options[ $k ] );
		}

		unset( $options['um_private_content_license_key'] );
	}
	update_option( 'um_options', $options );

	global $wpdb;
	$wpdb->query(
		"DELETE 
		FROM {$wpdb->usermeta} 
		WHERE meta_key = '_um_private_content_post_id'"
	);

	$um_private_posts = get_posts( array(
		'post_type'     => 'um_private_content',
		'numberposts'   => -1
	) );

	foreach ( $um_private_posts as $um_private_post ) {
		wp_delete_post( $um_private_post->ID, 1 );
	}

	delete_option( 'um_private_content_last_version_upgrade' );
	delete_option( 'um_private_content_version' );
}