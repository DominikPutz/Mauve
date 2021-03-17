<?php
/*
Plugin Name: Ultimate Member - User Reviews
Plugin URI: http://ultimatemember.com/extensions/user-reviews
Description: Let users give reviews to each other and filter your members based on rating.
Version: 2.1.5
Author: Ultimate Member
Author URI: http://ultimatemember.com/
Text Domain: um-reviews
Domain Path: /languages
UM version: 2.1.0
*/

if ( ! defined( 'ABSPATH' ) ) exit;

require_once( ABSPATH.'wp-admin/includes/plugin.php' );

$plugin_data = get_plugin_data( __FILE__ );

define( 'um_reviews_url', plugin_dir_url( __FILE__ ) );
define( 'um_reviews_path', plugin_dir_path( __FILE__ ) );
define( 'um_reviews_plugin', plugin_basename( __FILE__ ) );
define( 'um_reviews_extension', $plugin_data['Name'] );
define( 'um_reviews_version', $plugin_data['Version'] );
define( 'um_reviews_textdomain', 'um-reviews' );

define( 'um_reviews_requires', '2.1.0' );

function um_reviews_plugins_loaded() {
	$locale = ( get_locale() != '' ) ? get_locale() : 'en_US';
	load_textdomain( um_reviews_textdomain, WP_LANG_DIR . '/plugins/' . um_reviews_textdomain . '-' . $locale . '.mo' );
	load_plugin_textdomain( um_reviews_textdomain, false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
}
add_action( 'plugins_loaded', 'um_reviews_plugins_loaded', 0 );

add_action( 'plugins_loaded', 'um_reviews_check_dependencies', -20 );

if ( ! function_exists( 'um_reviews_check_dependencies' ) ) {
	function um_reviews_check_dependencies() {
		if ( ! defined( 'um_path' ) || ! file_exists( um_path  . 'includes/class-dependencies.php' ) ) {
			//UM is not installed
			function um_reviews_dependencies() {
				echo '<div class="error"><p>' . sprintf( __( 'The <strong>%s</strong> extension requires the Ultimate Member plugin to be activated to work properly. You can download it <a href="https://wordpress.org/plugins/ultimate-member">here</a>', 'um-reviews' ), um_reviews_extension ) . '</p></div>';
			}

			add_action( 'admin_notices', 'um_reviews_dependencies' );
		} else {

			if ( ! function_exists( 'UM' ) ) {
				require_once um_path . 'includes/class-dependencies.php';
				$is_um_active = um\is_um_active();
			} else {
				$is_um_active = UM()->dependencies()->ultimatemember_active_check();
			}

			if ( ! $is_um_active ) {
				//UM is not active
				function um_reviews_dependencies() {
					echo '<div class="error"><p>' . sprintf( __( 'The <strong>%s</strong> extension requires the Ultimate Member plugin to be activated to work properly. You can download it <a href="https://wordpress.org/plugins/ultimate-member">here</a>', 'um-reviews' ), um_reviews_extension ) . '</p></div>';
				}

				add_action( 'admin_notices', 'um_reviews_dependencies' );

			} elseif ( true !== UM()->dependencies()->compare_versions( um_reviews_requires, um_reviews_version, 'reviews', um_reviews_extension ) ) {
				//UM old version is active
				function um_reviews_dependencies() {
					echo '<div class="error"><p>' . UM()->dependencies()->compare_versions( um_reviews_requires, um_reviews_version, 'reviews', um_reviews_extension ) . '</p></div>';
				}

				add_action( 'admin_notices', 'um_reviews_dependencies' );

			} else {
				require_once um_reviews_path . 'includes/core/um-reviews-init.php';
			}
		}
	}
}

if ( ! function_exists( 'um_reviews_activation_hook' ) ) {
	function um_reviews_activation_hook() {
		//first install
		$version = get_option( 'um_reviews_version' );
		if ( ! $version ) {
			update_option( 'um_reviews_last_version_upgrade', um_reviews_version );
		}

		if ( $version != um_reviews_version ) {
			update_option( 'um_reviews_version', um_reviews_version );
		}

		//run setup
		if ( ! class_exists( 'um_ext\um_reviews\core\Reviews_Setup' ) ) {
			require_once um_reviews_path . 'includes/core/class-reviews-setup.php';
		}

		$reviews_setup = new um_ext\um_reviews\core\Reviews_Setup();
		$reviews_setup->run_setup();
	}
}
register_activation_hook( um_reviews_plugin, 'um_reviews_activation_hook' );