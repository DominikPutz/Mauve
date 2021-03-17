<?php
/*
Plugin Name: Ultimate Member - myCRED
Plugin URI: http://ultimatemember.com/extensions/mycred
Description: Integrates myCRED points management system with Ultimate Member.
Version: 2.1.6
Author: Ultimate Member
Author URI: http://ultimatemember.com/
Text Domain: um-mycred
Domain Path: /languages
UM version: 2.1.0
myCRED requires at least: 2.1.0
myCRED tested up to: 2.1.0
*/

if ( ! defined( 'ABSPATH' ) ) exit;

require_once( ABSPATH . 'wp-admin/includes/plugin.php' );

$plugin_data = get_plugin_data( __FILE__ );

define( 'um_mycred_url', plugin_dir_url( __FILE__ ) );
define( 'um_mycred_path', plugin_dir_path( __FILE__ ) );
define( 'um_mycred_plugin', plugin_basename( __FILE__ ) );
define( 'um_mycred_extension', $plugin_data['Name'] );
define( 'um_mycred_version', $plugin_data['Version'] );
define( 'um_mycred_textdomain', 'um-mycred' );

define( 'um_mycred_requires', '2.1.0' );

function um_mycred_plugins_loaded() {
	$locale = ( get_locale() != '' ) ? get_locale() : 'en_US';
	load_textdomain( um_mycred_textdomain, WP_LANG_DIR . '/plugins/' . um_mycred_textdomain . '-' . $locale . '.mo' );
	load_plugin_textdomain( um_mycred_textdomain, false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
}
add_action( 'plugins_loaded', 'um_mycred_plugins_loaded', 0 );

add_action( 'plugins_loaded', 'um_mycred_check_dependencies', -20 );

if ( ! function_exists( 'um_mycred_check_dependencies' ) ) {
	function um_mycred_check_dependencies() {
		if ( ! defined( 'um_path' ) || ! file_exists( um_path  . 'includes/class-dependencies.php' ) ) {
			//UM is not installed
			function um_mycred_dependencies() {
				echo '<div class="error"><p>' . sprintf( __( 'The <strong>%s</strong> extension requires the Ultimate Member plugin to be activated to work properly. You can download it <a href="https://wordpress.org/plugins/ultimate-member">here</a>', 'um-mycred' ), um_mycred_extension ) . '</p></div>';
			}

			add_action( 'admin_notices', 'um_mycred_dependencies' );
		} else {

			if ( ! function_exists( 'UM' ) ) {
				require_once um_path . 'includes/class-dependencies.php';
				$is_um_active = um\is_um_active();
			} else {
				$is_um_active = UM()->dependencies()->ultimatemember_active_check();
			}

			if ( ! $is_um_active ) {
				//UM is not active
				function um_mycred_dependencies() {
					echo '<div class="error"><p>' . sprintf( __( 'The <strong>%s</strong> extension requires the Ultimate Member plugin to be activated to work properly. You can download it <a href="https://wordpress.org/plugins/ultimate-member">here</a>', 'um-mycred' ), um_mycred_extension ) . '</p></div>';
				}

				add_action( 'admin_notices', 'um_mycred_dependencies' );

			} elseif ( ! UM()->dependencies()->mycred_active_check() ) {
				//UM old version is active
				function um_mycred_dependencies() {
					echo '<div class="error"><p>' . sprintf(__('Sorry. You must activate the <strong>myCRED</strong> plugin to use the %s.','um-mycred'), um_mycred_extension ) . '</p></div>';
				}

				add_action( 'admin_notices', 'um_mycred_dependencies' );

			} elseif ( true !== UM()->dependencies()->compare_versions( um_mycred_requires, um_mycred_version, 'mycred', um_mycred_extension ) ) {
				//UM old version is active
				function um_mycred_dependencies() {
					echo '<div class="error"><p>' . UM()->dependencies()->compare_versions( um_mycred_requires, um_mycred_version, 'mycred', um_mycred_extension ) . '</p></div>';
				}

				add_action( 'admin_notices', 'um_mycred_dependencies' );

			} else {
				require_once um_mycred_path . 'includes/core/um-mycred-init.php';
			}
		}
	}
}

if ( ! function_exists( 'um_mycred_activation_hook' ) ) {
	function um_mycred_activation_hook() {
		//first install
		$version = get_option( 'um_mycred_version' );
		if ( ! $version ) {
			update_option( 'um_mycred_last_version_upgrade', um_mycred_version );
		}

		if ( $version != um_mycred_version ) {
			update_option( 'um_mycred_version', um_mycred_version );
		}

		//run setup
		if ( ! class_exists( 'um_ext\um_mycred\core\myCRED_Setup' ) ) {
			require_once um_mycred_path . 'includes/core/class-mycred-setup.php';
		}

		$mycred_setup = new um_ext\um_mycred\core\myCRED_Setup();
		$mycred_setup->run_setup();
	}
}
register_activation_hook( um_mycred_plugin, 'um_mycred_activation_hook' );