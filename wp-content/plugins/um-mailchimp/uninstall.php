<?php
/**
 * Uninstall UM Mailchimp
 *
 */

// Exit if accessed directly.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) exit;


if ( ! defined( 'um_mailchimp_path' ) ) {
	define( 'um_mailchimp_path', plugin_dir_path( __FILE__ ) );
}

if ( ! defined( 'um_mailchimp_url' ) ) {
	define( 'um_mailchimp_url', plugin_dir_url( __FILE__ ) );
}

if ( ! defined( 'um_mailchimp_plugin' ) ) {
	define( 'um_mailchimp_plugin', plugin_basename( __FILE__ ) );
}

$options = get_option( 'um_options', array() );
if ( ! empty( $options['uninstall_on_delete'] ) ) {
	if ( ! class_exists( 'um_ext\um_mailchimp\core\Setup' ) ) {
		require_once um_mailchimp_path . 'includes/core/class-setup.php';
	}

	$mailchimp_setup = new um_ext\um_mailchimp\core\Setup();

	//remove settings
	foreach ( $mailchimp_setup->settings_defaults as $k => $v ) {
		unset( $options[ $k ] );
	}

	unset( $options['um_mailchimp_license_key'] );

	update_option( 'um_options', $options );

	$um_mailchimps = get_posts( array(
		'post_type'     => array(
			'um_mailchimp'
		),
		'numberposts'   => -1
	) );
	foreach ( $um_mailchimps as $um_mailchimp ) {
		wp_delete_post( $um_mailchimp->ID, 1 );
	}

	$mailchimp_log = UM()->files()->upload_basedir . 'mailchimp.log';
	if ( file_exists( $mailchimp_log ) ) {
		unlink( $mailchimp_log );
	}

	delete_option( 'um_mailchimp_last_version_upgrade' );
	delete_option( 'um_mailchimp_version' );
}