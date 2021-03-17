<?php
/**
* Uninstall UM Bookmark
*
*/

// Exit if accessed directly.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) exit;


if ( ! defined( 'um_bookmark_path' ) ) {
	define( 'um_bookmark_path', plugin_dir_path( __FILE__ ) );
}

if ( ! defined( 'um_bookmark_url' ) ) {
	define( 'um_bookmark_url', plugin_dir_url( __FILE__ ) );
}

if ( ! defined( 'um_bookmark_plugin' ) ) {
	define( 'um_bookmark_plugin', plugin_basename( __FILE__ ) );
}

$options = get_option( 'um_options', array() );
if ( ! empty( $options['uninstall_on_delete'] ) ) {
	if ( ! class_exists( 'um_ext\um_bookmark\core\Bookmark_Setup' ) ) {
		require_once um_bookmark_path . 'includes/core/class-bookmark-setup.php';
	}

	$bookmark_setup = new um_ext\um_user_bookmarks\core\Bookmark_Setup();

	//remove settings
	foreach ( $bookmark_setup->settings_defaults as $k => $v ) {
		unset( $options[ $k ] );
	}

	unset( $options['um_bookmark_license_key'] );

	update_option( 'um_options', $options );
	
	global $wpdb;

	$wpdb->query(
		"DELETE 
		FROM {$wpdb->usermeta} 
		WHERE meta_key = '_um_user_bookmarks'"
	);

	$wpdb->query(
		"DELETE 
        FROM {$wpdb->postmeta} 
        WHERE meta_key = '_um_in_users_bookmarks'"
	);

	delete_option( 'um_user_bookmarks_last_version_upgrade' );
	delete_option( 'um_user_bookmarks_version' );
}