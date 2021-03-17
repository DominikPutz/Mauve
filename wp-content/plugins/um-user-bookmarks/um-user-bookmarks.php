<?php
/*
Plugin Name: Ultimate Member - User Bookmarks
Plugin URI: http://ultimatemember.com/
Description: Let users bookmark posts, pages and custom post types
Version: 2.0.2
Author: Ultimate Member
Author URI: http://ultimatemember.com/
Text Domain: um-user-bookmarks
Domain Path: /languages
UM version: 2.1.0
*/

if ( ! defined( 'ABSPATH' ) ) exit;

require_once( ABSPATH . 'wp-admin/includes/plugin.php' );

$plugin_data = get_plugin_data( __FILE__ );

define( 'um_user_bookmarks_url' , plugin_dir_url( __FILE__ ) );
define( 'um_user_bookmarks_path' , plugin_dir_path( __FILE__ ));
define( 'um_user_bookmarks_plugin' , plugin_basename( __FILE__ ) );
define( 'um_user_bookmarks_extension' , $plugin_data['Name'] );
define( 'um_user_bookmarks_version' , $plugin_data['Version'] );
define( 'um_user_bookmarks_textdomain' , 'um-user-bookmarks' );
define( 'um_user_bookmarks_requires' , '2.1.0' );


/**
 * Load text domain
 */
function um_user_bookmarks_plugins_loaded() {
	$locale = ( get_locale() != '' ) ? get_locale() : 'en_US';
	load_textdomain( um_user_bookmarks_textdomain, WP_LANG_DIR . '/plugins/' . um_user_bookmarks_textdomain . '-' . $locale . '.mo' );
	load_plugin_textdomain( um_user_bookmarks_textdomain, false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
}
add_action( 'plugins_loaded', 'um_user_bookmarks_plugins_loaded', 0 );

add_action( 'plugins_loaded', 'um_user_bookmarks_check_dependencies', -20 );

if ( ! function_exists( 'um_user_bookmarks_check_dependencies' ) ) {

	function um_user_bookmarks_check_dependencies() {
		if ( ! defined( 'um_path' ) || ! file_exists( um_path  . 'includes/class-dependencies.php' ) ) {

			//UM is not installed
			function um_user_bookmarks_dependencies() {
				echo '<div class="error"><p>' . sprintf( __( 'The <strong>%s</strong> extension requires the Ultimate Member plugin to be activated to work properly. You can download it <a href="https://wordpress.org/plugins/ultimate-member">here</a>', 'um-user-bookmarks' ), um_user_bookmarks_extension ) . '</p></div>';
			}

			add_action( 'admin_notices', 'um_user_bookmarks_dependencies' );

		} else {

			if ( ! function_exists( 'UM' ) ) {
				require_once um_path . 'includes/class-dependencies.php';
				$is_um_active = um\is_um_active();
			} else {
				$is_um_active = UM()->dependencies()->ultimatemember_active_check();
			}

			if ( ! $is_um_active ) {
				
				//UM is not active
				function um_user_bookmarks_dependencies() {
					
					echo '<div class="error"><p>' . sprintf( __( 'The <strong>%s</strong> extension requires the Ultimate Member plugin to be activated to work properly. You can download it <a href="https://wordpress.org/plugins/ultimate-member">here</a>', 'um-user-bookmarks' ), um_user_bookmarks_extension ) . '</p></div>';
					
				}

				add_action( 'admin_notices', 'um_user_bookmarks_dependencies' );
				

			} elseif ( true !== UM()->dependencies()->compare_versions( um_user_bookmarks_requires, um_user_bookmarks_version, 'user-bookmarks', um_user_bookmarks_extension ) ) {
				//UM old version is active
				function um_user_bookmarks_dependencies() {
					echo '<div class="error"><p>' . UM()->dependencies()->compare_versions( um_user_bookmarks_requires, um_user_bookmarks_version, 'user-bookmarks', um_user_bookmarks_extension ) . '</p></div>';
				}

				add_action( 'admin_notices', 'um_user_bookmarks_dependencies' );

			} else {
				require_once um_user_bookmarks_path . 'includes/core/um-user-bookmarks-functions.php';
				require_once um_user_bookmarks_path . 'includes/core/um-user-bookmarks-init.php';
			}
		}
	}
}


if ( ! function_exists( 'um_user_bookmarks_activation_hook' ) ) {
	function um_user_bookmarks_activation_hook() {
		//first install
		$version_old = get_option( 'um_user_bookmarks_latest_version' );
		$version = get_option( 'um_user_bookmarks_version' );

		if ( ! $version && ! $version_old ) {
			update_option( 'um_user_bookmarks_last_version_upgrade', um_user_bookmarks_version );
		}

		if ( $version != um_user_bookmarks_version ) {
			update_option( 'um_user_bookmarks_version', um_user_bookmarks_version );
		}

		//run setup
		if ( ! class_exists( 'um_ext\um_user_bookmarks\core\Bookmark_Setup' ) ) {
			require_once um_user_bookmarks_path . 'includes/core/class-bookmark-setup.php';
		}

		$user_bookmark_setup = new um_ext\um_user_bookmarks\core\Bookmark_Setup();
		$user_bookmark_setup->run_setup();
	}
}
register_activation_hook( um_user_bookmarks_plugin, 'um_user_bookmarks_activation_hook' );