<?php
/*
Plugin Name: Ultimate Member - Notices
Plugin URI: http://ultimatemember.com/extensions/notices
Description: Show notices to your users when they visit your WordPress site.
Version: 2.0.5
Author: Ultimate Member
Author URI: http://ultimatemember.com/
Text Domain: um-notices
Domain Path: /languages
UM version: 2.1.0
*/

if ( ! defined( 'ABSPATH' ) ) exit;

require_once( ABSPATH.'wp-admin/includes/plugin.php' );

$plugin_data = get_plugin_data( __FILE__ );

define( 'um_notices_url', plugin_dir_url( __FILE__ ) );
define( 'um_notices_path', plugin_dir_path( __FILE__ ) );
define( 'um_notices_plugin', plugin_basename( __FILE__ ) );
define( 'um_notices_extension', $plugin_data['Name'] );
define( 'um_notices_version', $plugin_data['Version'] );
define( 'um_notices_textdomain', 'um-notices' );

define( 'um_notices_requires', '2.1.0' );

function um_notices_plugins_loaded() {
	$locale = ( get_locale() != '' ) ? get_locale() : 'en_US';
	load_textdomain( um_notices_textdomain, WP_LANG_DIR . '/plugins/' . um_notices_textdomain . '-' . $locale . '.mo' );
	load_plugin_textdomain( um_notices_textdomain, false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
}
add_action( 'plugins_loaded', 'um_notices_plugins_loaded', 0 );

add_action( 'plugins_loaded', 'um_notices_check_dependencies', -20 );

if ( ! function_exists( 'um_notices_check_dependencies' ) ) {
	function um_notices_check_dependencies() {
		if ( ! defined( 'um_path' ) || ! file_exists( um_path  . 'includes/class-dependencies.php' ) ) {
			//UM is not installed
			function um_notices_dependencies() {
				echo '<div class="error"><p>' . sprintf( __( 'The <strong>%s</strong> extension requires the Ultimate Member plugin to be activated to work properly. You can download it <a href="https://wordpress.org/plugins/ultimate-member">here</a>', 'um-notices' ), um_notices_extension ) . '</p></div>';
			}

			add_action( 'admin_notices', 'um_notices_dependencies' );
		} else {

			if ( ! function_exists( 'UM' ) ) {
				require_once um_path . 'includes/class-dependencies.php';
				$is_um_active = um\is_um_active();
			} else {
				$is_um_active = UM()->dependencies()->ultimatemember_active_check();
			}

			if ( ! $is_um_active ) {
				//UM is not active
				function um_notices_dependencies() {
					echo '<div class="error"><p>' . sprintf( __( 'The <strong>%s</strong> extension requires the Ultimate Member plugin to be activated to work properly. You can download it <a href="https://wordpress.org/plugins/ultimate-member">here</a>', 'um-notices' ), um_notices_extension ) . '</p></div>';
				}

				add_action( 'admin_notices', 'um_notices_dependencies' );

			} elseif ( true !== UM()->dependencies()->compare_versions( um_notices_requires, um_notices_version, 'notices', um_notices_extension ) ) {
				//UM old version is active
				function um_notices_dependencies() {
					echo '<div class="error"><p>' . UM()->dependencies()->compare_versions( um_notices_requires, um_notices_version, 'notices', um_notices_extension ) . '</p></div>';
				}

				add_action( 'admin_notices', 'um_notices_dependencies' );

			} else {
				require_once um_notices_path . 'includes/core/um-notices-init.php';
			}
		}
	}
}


if ( ! function_exists( 'um_notices_activation_hook' ) ) {
	function um_notices_activation_hook() {
		//first install
		$version = get_option( 'um_notices_version' );
		if ( ! $version ) {
			update_option( 'um_notices_last_version_upgrade', um_notices_version );
		}

		if ( $version != um_notices_version ) {
			update_option( 'um_notices_version', um_notices_version );
		}

		//run setup
		if ( ! class_exists( 'um_ext\um_notices\core\Notices_Setup' ) ) {
			require_once um_notices_path . 'includes/core/class-notices-setup.php';
		}

		$notices_setup = new um_ext\um_notices\core\Notices_Setup();
		$notices_setup->run_setup();
	}
}
register_activation_hook( um_notices_plugin, 'um_notices_activation_hook' );