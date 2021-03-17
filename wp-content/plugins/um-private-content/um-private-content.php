<?php
/*
Plugin Name: Ultimate Member - Private Content
Plugin URI: http://ultimatemember.com/
Description: Display private content to logged in users that only they can access.
Version: 2.0.4
Author: Ultimate Member
Author URI: http://ultimatemember.com/
Text Domain: um-private-content
Domain Path: /languages
*/

require_once( ABSPATH.'wp-admin/includes/plugin.php' );

$plugin_data = get_plugin_data( __FILE__ );

define( 'um_private_content_url', plugin_dir_url( __FILE__ ) );
define( 'um_private_content_path', plugin_dir_path( __FILE__ ) );
define( 'um_private_content_plugin', plugin_basename( __FILE__ ) );
define( 'um_private_content_extension', $plugin_data['Name'] );
define( 'um_private_content_version', $plugin_data['Version'] );
define( 'um_private_content_textdomain', 'um-private-content' );

define( 'um_private_content_requires', '2.0');

function um_private_content_plugins_loaded() {
	$locale = ( get_locale() != '' ) ? get_locale() : 'en_US';
	load_textdomain( um_private_content_textdomain, WP_LANG_DIR . '/plugins/' .um_private_content_textdomain . '-' . $locale . '.mo');
	load_plugin_textdomain( um_private_content_textdomain, false, dirname( plugin_basename(  __FILE__ ) ) . '/languages/' );
}
add_action( 'init', 'um_private_content_plugins_loaded', 0 );


add_action( 'plugins_loaded', 'um_private_content_check_dependencies', -20 );

if ( ! function_exists( 'um_private_content_check_dependencies' ) ) {
	function um_private_content_check_dependencies() {
		if ( ! defined( 'um_path' ) || ! file_exists( um_path  . 'includes/class-dependencies.php' ) ) {
			//UM is not installed
			function um_private_content_dependencies() {
				echo '<div class="error"><p>' . sprintf( __( 'The <strong>%s</strong> extension requires the Ultimate Member plugin to be activated to work properly. You can download it <a href="https://wordpress.org/plugins/ultimate-member">here</a>', 'um-private-content' ), um_private_content_extension ) . '</p></div>';
			}

			add_action( 'admin_notices', 'um_private_content_dependencies' );
		} else {

			if ( ! function_exists( 'UM' ) ) {
				require_once um_path . 'includes/class-dependencies.php';
				$is_um_active = um\is_um_active();
			} else {
				$is_um_active = UM()->dependencies()->ultimatemember_active_check();
			}

			if ( ! $is_um_active ) {
				//UM is not active
				function um_private_content_dependencies() {
					echo '<div class="error"><p>' . sprintf( __( 'The <strong>%s</strong> extension requires the Ultimate Member plugin to be activated to work properly. You can download it <a href="https://wordpress.org/plugins/ultimate-member">here</a>', 'um-private-content' ), um_private_content_extension ) . '</p></div>';
				}

				add_action( 'admin_notices', 'um_private_content_dependencies' );

			} elseif ( true !== UM()->dependencies()->compare_versions( um_private_content_requires, um_private_content_version, 'private-content', um_private_content_extension ) ) {
				//UM old version is active
				function um_private_content_dependencies() {
					echo '<div class="error"><p>' . UM()->dependencies()->compare_versions( um_private_content_requires, um_private_content_version, 'private-content', um_private_content_extension ) . '</p></div>';
				}

				add_action( 'admin_notices', 'um_private_content_dependencies' );

			} else {

				require_once um_private_content_path . 'includes/core/um-private-content-init.php';
			}
		}
	}
}




register_activation_hook( um_private_content_plugin, 'um_private_content_activation_hook' );
function um_private_content_activation_hook() {
	//first install
	//run setup
	if ( ! class_exists( 'um_ext\um_private_content\core\Private_Content_Setup' ) ) {
		require_once um_private_content_path . 'includes/core/class-private-content-setup.php';
	}

	$private_content_setup = new um_ext\um_private_content\core\Private_Content_Setup();
	$private_content_setup->run_setup();

	$version = get_option( 'um_private_content_version' );
	if ( ! $version ) {
		update_option( 'um_private_content_last_version_upgrade', um_private_content_version );
	}

	if ( $version != um_private_content_version ) {
		update_option( 'um_private_content_version', um_private_content_version );
	}
}