<?php
namespace um_ext\um_verified_users\core;

if ( ! defined( 'ABSPATH' ) ) exit;


/**
 * Class Verified_Users_Enqueue
 * @package um_ext\um_verified_users\core
 */
class Verified_Users_Enqueue {


	/**
	 * Verified_Users_Enqueue constructor.
	 */
	function __construct() {
		$priority = apply_filters( 'um_verified_enqueue_priority', 0 );

		add_action( 'wp_enqueue_scripts',  array( &$this, '_enqueue_scripts' ), $priority );
		add_action( 'admin_enqueue_scripts',  array( &$this, '_enqueue_scripts' ), $priority );
	}


	/**
	 *
	 */
	function _enqueue_scripts() {
		$suffix = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG || defined( 'UM_SCRIPT_DEBUG' ) ) ? '' : '.min';
		wp_register_style( 'um-verified', um_verified_users_url . 'assets/css/um-verified' . $suffix . '.css', array(), um_verified_users_version );
	}

}