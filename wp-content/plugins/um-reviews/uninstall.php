<?php
/**
 * Uninstall UM Reviews
 *
 */

// Exit if accessed directly.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) exit;


if ( ! defined( 'um_reviews_path' ) ) {
	define( 'um_reviews_path', plugin_dir_path( __FILE__ ) );
}

if ( ! defined( 'um_reviews_url' ) ) {
	define( 'um_reviews_url', plugin_dir_url( __FILE__ ) );
}

if ( ! defined( 'um_reviews_plugin' ) ) {
	define( 'um_reviews_plugin', plugin_basename( __FILE__ ) );
}

$options = get_option( 'um_options', array() );

if ( ! empty( $options['uninstall_on_delete'] ) ) {
	if ( ! class_exists( 'um_ext\um_reviews\core\Reviews_Setup' ) ) {
		require_once um_reviews_path . 'includes/core/class-reviews-setup.php';
	}

	$reviews_setup = new um_ext\um_reviews\core\Reviews_Setup();

	//remove settings
	foreach ( $reviews_setup->settings_defaults as $k => $v ) {
		unset( $options[ $k ] );
	}

	unset( $options['um_reviews_license_key'] );
	update_option( 'um_options', $options );


	$um_reviews = get_posts( array(
		'post_type'     => 'um_review',
		'numberposts'   => -1
	) );

	foreach ( $um_reviews as $um_review ){
		wp_delete_post( $um_review->ID, 1 );
	}
	
	global $wpdb;
	$wpdb->query(
		"DELETE 
        FROM {$wpdb->usermeta} 
        WHERE meta_key = '_reviews' OR 
              meta_key = '_reviews_compound' OR 
              meta_key = '_reviews_total' OR 
              meta_key = '_reviews_avg'"
	);

	delete_option( 'um_reviews_last_version_upgrade' );
	delete_option( 'um_reviews_version' );
	delete_option( 'widget_um_reviews_top_rated' );
	delete_option( 'widget_um_reviews_most_rated' );
	delete_option( 'widget_um_reviews_lowest_rated' );
}