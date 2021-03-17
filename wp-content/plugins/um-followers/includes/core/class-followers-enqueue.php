<?php
namespace um_ext\um_followers\core;


// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) exit;


/**
 * Class Followers_Enqueue
 * @package um_ext\um_followers\core
 */
class Followers_Enqueue {


	/**
	 * Followers_Enqueue constructor.
	 */
	function __construct() {
		add_action( 'wp_enqueue_scripts',  array( &$this, 'wp_enqueue_scripts' ), 9999 );
	}


	/**
	 *
	 */
	function wp_enqueue_scripts() {
		$suffix = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG || defined( 'UM_SCRIPT_DEBUG' ) ) ? '' : '.min';
		wp_register_script( 'um_followers', um_followers_url . 'assets/js/um-followers' . $suffix . '.js', array( 'jquery', 'wp-util', 'um_scripts' ), um_followers_version, true );
		wp_register_style( 'um_followers', um_followers_url . 'assets/css/um-followers' . $suffix . '.css', array( 'um_fonticons_ii', 'um_fonticons_fa' ), um_followers_version );
	}
}