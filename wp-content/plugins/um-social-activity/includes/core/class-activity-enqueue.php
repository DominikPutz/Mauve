<?php
namespace um_ext\um_social_activity\core;


if ( ! defined( 'ABSPATH' ) ) exit;


/**
 * Class Activity_Enqueue
 * @package um_ext\um_social_activity\core
 */
class Activity_Enqueue {


	/**
	 * Activity_Enqueue constructor.
	 */
	function __construct() {
		$priority = apply_filters( 'um_activity_enqueue_priority', 0 );
		add_action('wp_enqueue_scripts',  array(&$this, 'wp_enqueue_scripts'), $priority );
	}


	/**
	 * Enqueue scripts
	 */
	function wp_enqueue_scripts() {
		$suffix = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG || defined( 'UM_SCRIPT_DEBUG' ) ) ? '' : '.min';

		wp_register_script( 'um_autosize', um_activity_url . 'assets/js/autoresize-mod.jquery' . $suffix . '.js', array( 'jquery' ), um_activity_version, true );
		wp_register_script( 'um_autosize-old', um_activity_url . 'assets/js/autosize.min.js', array( 'jquery' ), um_activity_version, true );
		wp_register_script( 'um_scrollto', um_activity_url . 'assets/js/um-scrollto' . $suffix . '.js', array( 'jquery' ), um_activity_version, true );

		wp_register_script( 'um_activity', um_activity_url . 'assets/js/um-activity' . $suffix . '.js', array( 'jquery', 'jquery-ui-autocomplete','um_autosize', 'um_autosize-old', 'wp-util', 'um_scrollto', 'um_scripts' ), um_activity_version, true );

		wp_register_style('um_activity', um_activity_url . 'assets/css/um-activity' . $suffix . '.css', array(), um_activity_version );
		wp_register_style('um_activity_responsive', um_activity_url . 'assets/css/um-activity-responsive' . $suffix . '.css', array( 'um_activity' ), um_activity_version );
	}


	/**
	 *
	 */
	function enqueue_scripts() {
		wp_enqueue_script( 'um_activity' );
		wp_enqueue_style( 'um_activity_responsive' );

		do_action( 'um_social_activity_enqueue_scripts' );
	}
}