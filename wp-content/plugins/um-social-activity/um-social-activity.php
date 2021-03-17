<?php
/*
Plugin Name: Ultimate Member - Social Activity
Plugin URI: https://ultimatemember.com/extensions/social-activity
Description: Engage community users with beautiful social activity walls
Version: 2.2.0
Author: Ultimate Member
Author URI: https://ultimatemember.com/
Text Domain: um-activity
Domain Path: /languages
UM version: 2.1.0
*/

if ( ! defined( 'ABSPATH' ) ) exit;

require_once( ABSPATH.'wp-admin/includes/plugin.php' );

$plugin_data = get_plugin_data( __FILE__ );

define( 'um_activity_url', plugin_dir_url( __FILE__ ) );
define( 'um_activity_path', plugin_dir_path( __FILE__ ) );
define( 'um_activity_plugin', plugin_basename( __FILE__ ) );
define( 'um_activity_extension', $plugin_data['Name'] );
define( 'um_activity_version', $plugin_data['Version'] );
define( 'um_activity_textdomain', 'um-activity' );

define( 'um_activity_requires', '2.1.0' );

function um_activity_plugins_loaded() {
	$locale = ( get_locale() != '' ) ? get_locale() : 'en_US';
	load_textdomain( um_activity_textdomain, WP_LANG_DIR . '/plugins/um-social-activity-' . $locale . '.mo' );
	load_plugin_textdomain( um_activity_textdomain, false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
}
add_action( 'init', 'um_activity_plugins_loaded', 0 );

add_action( 'plugins_loaded', 'um_activity_check_dependencies', -20 );

if ( ! function_exists( 'um_activity_check_dependencies' ) ) {
	function um_activity_check_dependencies() {
		if ( ! defined( 'um_path' ) || ! file_exists( um_path  . 'includes/class-dependencies.php' ) ) {
			//UM is not installed
			function um_activity_dependencies() {
				echo '<div class="error"><p>' . sprintf( __( 'The <strong>%s</strong> extension requires the Ultimate Member plugin to be activated to work properly. You can download it <a href="https://wordpress.org/plugins/ultimate-member">here</a>', 'um-activity' ), um_activity_extension ) . '</p></div>';
			}

			add_action( 'admin_notices', 'um_activity_dependencies' );
		} else {

			if ( ! function_exists( 'UM' ) ) {
				require_once um_path . 'includes/class-dependencies.php';
				$is_um_active = um\is_um_active();
			} else {
				$is_um_active = UM()->dependencies()->ultimatemember_active_check();
			}

			if ( ! $is_um_active ) {
				//UM is not active
				function um_activity_dependencies() {
					echo '<div class="error"><p>' . sprintf( __( 'The <strong>%s</strong> extension requires the Ultimate Member plugin to be activated to work properly. You can download it <a href="https://wordpress.org/plugins/ultimate-member">here</a>', 'um-activity' ), um_activity_extension ) . '</p></div>';
				}

				add_action( 'admin_notices', 'um_activity_dependencies' );

			} elseif ( true !== UM()->dependencies()->compare_versions( um_activity_requires, um_activity_version, 'social-activity', um_activity_extension ) ) {
				//UM old version is active
				function um_activity_dependencies() {
					echo '<div class="error"><p>' . UM()->dependencies()->compare_versions( um_activity_requires, um_activity_version, 'social-activity', um_activity_extension ) . '</p></div>';
				}

				add_action( 'admin_notices', 'um_activity_dependencies' );

			} else {
				require_once um_activity_path . 'includes/core/um-activity-init.php';
			}
		}
	}
}


if ( ! function_exists( 'um_activity_activation_hook' ) ) {
	function um_activity_activation_hook() {
		//run setup
		if ( ! class_exists( 'um_ext\um_social_activity\core\Activity_Setup' ) ) {
			require_once um_activity_path . 'includes/core/class-activity-setup.php';
		}

		$activity_setup = new um_ext\um_social_activity\core\Activity_Setup();
		$activity_setup->run_setup();

		//first install
		$version = get_option( 'um_activity_version' );
		if ( ! $version ) {
			update_option( 'um_activity_last_version_upgrade', um_activity_version );
		}

		if ( $version != um_activity_version ) {
			update_option( 'um_activity_version', um_activity_version );
		}
	}
}
register_activation_hook( um_activity_plugin, 'um_activity_activation_hook' );