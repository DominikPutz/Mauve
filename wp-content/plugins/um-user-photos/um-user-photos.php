<?php
/*
Plugin Name: Ultimate Member - User Photos
Plugin URI: http://ultimatemember.com/extensions/user-photos
Description: Let users add albums and photos
Version: 2.0.4
Author: Ultimate Member
Author URI: http://ultimatemember.com/
Text Domain: um-user-photos
Domain Path: /languages
UM version: 2.1.0
*/

if ( ! defined( 'ABSPATH' ) ) exit;

require_once( ABSPATH.'wp-admin/includes/plugin.php' );

$plugin_data = get_plugin_data( __FILE__ );

define( 'um_user_photos_url', plugin_dir_url( __FILE__ ) );
define( 'um_user_photos_path', plugin_dir_path( __FILE__ ));
define( 'um_user_photos_plugin', plugin_basename( __FILE__ ) );
define( 'um_user_photos_extension', $plugin_data['Name'] );
define( 'um_user_photos_version', $plugin_data['Version'] );
define( 'um_user_photos_textdomain', 'um-user-photos' );
define( 'um_user_photos_requires', '2.1.0' );

function um_user_photos_plugins_loaded() {
	$locale = ( get_locale() != '' ) ? get_locale() : 'en_US';
	load_textdomain( um_user_photos_textdomain, WP_LANG_DIR . '/plugins/' . um_user_photos_textdomain . '-' . $locale . '.mo' );
	load_plugin_textdomain( um_user_photos_textdomain, false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
}
add_action( 'plugins_loaded', 'um_user_photos_plugins_loaded', 0 );

add_action( 'plugins_loaded', 'um_user_photos_check_dependencies', -20 );

if ( ! function_exists( 'um_user_photos_check_dependencies' ) ) {
	function um_user_photos_check_dependencies() {
		if ( ! defined( 'um_path' ) || ! file_exists( um_path  . 'includes/class-dependencies.php' ) ) {
			//UM is not installed
			function um_user_photos_dependencies() {
				echo '<div class="error"><p>' . sprintf( __( 'The <strong>%s</strong> extension requires the Ultimate Member plugin to be activated to work properly. You can download it <a href="https://wordpress.org/plugins/ultimate-member">here</a>', 'um-user-photos' ), um_user_photos_extension ) . '</p></div>';
			}

			add_action( 'admin_notices', 'um_user_photos_dependencies' );
		} else {

			if ( ! function_exists( 'UM' ) ) {
				require_once um_path . 'includes/class-dependencies.php';
				$is_um_active = um\is_um_active();
			} else {
				$is_um_active = UM()->dependencies()->ultimatemember_active_check();
			}

			if ( ! $is_um_active ) {
				//UM is not active
				function um_user_photos_dependencies() {
					echo '<div class="error"><p>' . sprintf( __( 'The <strong>%s</strong> extension requires the Ultimate Member plugin to be activated to work properly. You can download it <a href="https://wordpress.org/plugins/ultimate-member">here</a>', 'um-user-photos' ), um_user_photos_extension ) . '</p></div>';
				}

				add_action( 'admin_notices', 'um_user_photos_dependencies' );

			} elseif ( true !== UM()->dependencies()->compare_versions( um_user_photos_requires, um_user_photos_version, 'user-photos', um_user_photos_extension ) ) {
				//UM old version is active
				function um_user_photos_dependencies() {
					echo '<div class="error"><p>' . UM()->dependencies()->compare_versions( um_user_photos_requires, um_user_photos_version, 'user-photos', um_user_photos_extension ) . '</p></div>';
				}

				add_action( 'admin_notices', 'um_user_photos_dependencies' );

			} else {
				require_once um_user_photos_path . 'includes/core/um-user-photos-init.php';
			}
		}
	}
}


register_activation_hook( um_user_photos_plugin, 'um_user_photos_activation_hook' );
function um_user_photos_activation_hook() {
	//first install
	$version_old = get_option( 'um_user_photos_latest_version' );
	$version = get_option( 'um_user_photos_version' );
	if ( ! $version && ! $version_old )
		update_option( 'um_user_photos_last_version_upgrade', um_user_photos_version );

	if ( $version != um_user_photos_version )
		update_option( 'um_user_photos_version', um_user_photos_version );


	//run setup
	if ( ! class_exists( 'um_ext\um_user_photos\core\User_Photos_Setup' ) )
		require_once um_user_photos_path . 'includes/core/class-user-photos-setup.php';

	$user_photos_setup = new um_ext\um_user_photos\core\User_Photos_Setup();
	$user_photos_setup->run_setup();
}
