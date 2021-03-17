<?php
/*
Plugin Name: Ultimate Member - WooCommerce
Plugin URI: http://ultimatemember.com/extensions/woocommerce
Description: Integrates your WooCommerce store with Ultimate Member.
Version: 2.1.9
Author: Ultimate Member
Author URI: http://ultimatemember.com/
Text Domain: um-woocommerce
Domain Path: /languages
UM version: 2.1.0

WC requires at least: 2.6.14
WC tested up to: 3.8
*/

if ( ! defined( 'ABSPATH' ) ) exit;

require_once( ABSPATH.'wp-admin/includes/plugin.php' );

$plugin_data = get_plugin_data( __FILE__ );

define( 'um_woocommerce_url', plugin_dir_url( __FILE__ ) );
define( 'um_woocommerce_path', plugin_dir_path( __FILE__ ) );
define( 'um_woocommerce_plugin', plugin_basename( __FILE__ ) );
define( 'um_woocommerce_extension', $plugin_data['Name'] );
define( 'um_woocommerce_version', $plugin_data['Version'] );
define( 'um_woocommerce_textdomain', 'um-woocommerce' );

define( 'um_woocommerce_requires', '2.1.0' );

function um_woocommerce_plugins_loaded() {
	$locale = ( get_locale() != '' ) ? get_locale() : 'en_US';
	load_textdomain( um_woocommerce_textdomain, WP_LANG_DIR . '/plugins/' . um_woocommerce_textdomain . '-' . $locale . '.mo' );
	load_plugin_textdomain( um_woocommerce_textdomain, false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
}
add_action( 'plugins_loaded', 'um_woocommerce_plugins_loaded', 0 );

add_action( 'plugins_loaded', 'um_woocommerce_check_dependencies', -20 );

if ( ! function_exists( 'um_woocommerce_check_dependencies' ) ) {
	function um_woocommerce_check_dependencies() {
		if ( ! defined( 'um_path' ) || ! file_exists( um_path  . 'includes/class-dependencies.php' ) ) {
			//UM is not installed
			function um_woocommerce_dependencies() {
				echo '<div class="error"><p>' . sprintf( __( 'The <strong>%s</strong> extension requires the Ultimate Member plugin to be activated to work properly. You can download it <a href="https://wordpress.org/plugins/ultimate-member">here</a>', 'um-woocommerce' ), um_woocommerce_extension ) . '</p></div>';
			}

			add_action( 'admin_notices', 'um_woocommerce_dependencies' );
		} else {

			if ( ! function_exists( 'UM' ) ) {
				require_once um_path . 'includes/class-dependencies.php';
				$is_um_active = um\is_um_active();
			} else {
				$is_um_active = UM()->dependencies()->ultimatemember_active_check();
			}

			if ( ! $is_um_active ) {
				//UM is not active
				function um_woocommerce_dependencies() {
					echo '<div class="error"><p>' . sprintf( __( 'The <strong>%s</strong> extension requires the Ultimate Member plugin to be activated to work properly. You can download it <a href="https://wordpress.org/plugins/ultimate-member">here</a>', 'um-woocommerce' ), um_woocommerce_extension ) . '</p></div>';
				}

				add_action( 'admin_notices', 'um_woocommerce_dependencies' );

			} elseif ( ! UM()->dependencies()->woocommerce_active_check() ) {
				//UM old version is active
				function um_woocommerce_dependencies() {
					echo '<div class="error"><p>' . sprintf(__('WooCommerce must be activated before you can use %s.','um-woocommerce'), um_woocommerce_extension ) . '</p></div>';
				}

				add_action( 'admin_notices', 'um_woocommerce_dependencies' );

			} elseif ( true !== UM()->dependencies()->compare_versions( um_woocommerce_requires, um_woocommerce_version, 'woocommerce', um_woocommerce_extension ) ) {
				//UM old version is active
				function um_woocommerce_dependencies() {
					echo '<div class="error"><p>' . UM()->dependencies()->compare_versions( um_woocommerce_requires, um_woocommerce_version, 'woocommerce', um_woocommerce_extension ) . '</p></div>';
				}

				add_action( 'admin_notices', 'um_woocommerce_dependencies' );

			} else {
				require_once um_woocommerce_path . 'includes/core/um-woocommerce-init.php';
			}
		}
	}
}


register_activation_hook( um_woocommerce_plugin, 'um_woocommerce_activation_hook' );
if ( ! function_exists( 'um_woocommerce_activation_hook' ) ) {
	function um_woocommerce_activation_hook() {
		//first install
		$version = get_option( 'um_woocommerce_version' );
		if ( ! $version ) {
			update_option( 'um_woocommerce_last_version_upgrade', um_woocommerce_version );
		}

		if ( $version != um_woocommerce_version ) {
			update_option( 'um_woocommerce_version', um_woocommerce_version );
		}

		//run setup
		if ( ! class_exists( 'um_ext\um_woocommerce\core\WooCommerce_Setup' ) ) {
			require_once um_woocommerce_path . 'includes/core/class-woocommerce-setup.php';
		}

		$woocommerce_setup = new um_ext\um_woocommerce\core\WooCommerce_Setup();
		$woocommerce_setup->run_setup();
	}
}