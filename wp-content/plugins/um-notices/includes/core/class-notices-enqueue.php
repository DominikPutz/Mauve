<?php
namespace um_ext\um_notices\core;


if ( ! defined( 'ABSPATH' ) ) exit;


/**
 * Class Notices_Enqueue
 * @package um_ext\um_notices\core
 */
class Notices_Enqueue {


	/**
	 * Notices_Enqueue constructor.
	 */
	function __construct() {
		add_action( 'wp_enqueue_scripts',  array( &$this, 'wp_enqueue_scripts' ), 0 );
	}


	/**
	 * Enqueue
	 */
	function wp_enqueue_scripts() {
		$suffix = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG || defined( 'UM_SCRIPT_DEBUG' ) ) ? '' : '.min';

		wp_register_script( 'um_notices', um_notices_url . 'assets/js/um-notices' . $suffix . '.js', array( 'jquery', 'wp-util', 'um_scripts' ), um_notices_version, true );
		wp_register_style( 'um_notices', um_notices_url . 'assets/css/um-notices' . $suffix . '.css', array(), um_notices_version );
	}
}