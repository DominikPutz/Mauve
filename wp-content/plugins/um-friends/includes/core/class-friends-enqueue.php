<?php
namespace um_ext\um_friends\core;


// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) exit;


/**
 * Class Friends_Enqueue
 * @package um_ext\um_friends\core
 */
class Friends_Enqueue {


	/**
	 * Friends_Enqueue constructor.
	 */
	function __construct() {
		add_action( 'wp_enqueue_scripts',  array( &$this, 'wp_enqueue_scripts' ), 9999 );
	}


	/**
	 * Register custom friends scripts
	 */
	function wp_enqueue_scripts() {
		$suffix = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG || defined( 'UM_SCRIPT_DEBUG' ) ) ? '' : '.min';

		wp_register_script( 'um_friends', um_friends_url . 'assets/js/um-friends' . $suffix . '.js', array( 'jquery', 'wp-util', 'um_scripts' ), um_friends_version, true );
		wp_register_style( 'um_friends', um_friends_url . 'assets/css/um-friends' . $suffix . '.css', array( 'um_styles', 'um_fonticons_ii', 'um_fonticons_fa' ), um_friends_version );
	}
}