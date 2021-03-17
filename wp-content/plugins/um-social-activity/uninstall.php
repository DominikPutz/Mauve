<?php
/**
 * Uninstall UM Social Activity
 *
 */

// Exit if accessed directly.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) exit;


if ( ! defined( 'um_activity_path' ) ) {
	define( 'um_activity_path', plugin_dir_path( __FILE__ ) );
}

if ( ! defined( 'um_activity_url' ) ) {
	define( 'um_activity_url', plugin_dir_url( __FILE__ ) );
}

if ( ! defined( 'um_activity_plugin' ) ) {
	define( 'um_activity_plugin', plugin_basename( __FILE__ ) );
}

$options = get_option( 'um_options', array() );

if ( ! empty( $options['uninstall_on_delete'] ) ) {
	if ( ! class_exists( 'um_ext\um_social_activity\core\Activity_Setup' ) ) {
		require_once um_activity_path . 'includes/core/class-activity-setup.php';
	}

	$activity_setup = new um_ext\um_social_activity\core\Activity_Setup();

	//remove settings
	foreach ( $activity_setup->settings_defaults as $k => $v ) {
		unset( $options[ $k ] );
	}

	unset( $options['um_activity_license_key'] );

	update_option( 'um_options', $options );

	$um_activities = get_posts( array(
		'post_type'     => array(
			'um_activity'
		),
		'numberposts'   => -1
	) );

	foreach ( $um_activities as $um_activity ) {
		$image = get_post_meta( $um_activity->ID, '_photo', true );
		if ( $image ) {
			$user_id = get_post_meta( $um_activity->ID, '_user_id', true );
			$upload_dir = wp_upload_dir();
			$image_path = $upload_dir['basedir'] . '/ultimatemember/' . $user_id . '/' . $image;
			if ( file_exists( $image_path ) ) {
				unlink( $image_path );
			}
		}
		wp_delete_post( $um_activity->ID, 1 );
	}

	delete_option( 'um_activity_last_version_upgrade' );
	delete_option( 'um_activity_version' );
	delete_option( 'widget_um_activity_trending_tags' );
	delete_option( 'um_activity_flagged' );
}