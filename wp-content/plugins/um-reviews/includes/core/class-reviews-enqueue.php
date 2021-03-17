<?php
namespace um_ext\um_reviews\core;


if ( ! defined( 'ABSPATH' ) ) exit;


/**
 * Class Reviews_Enqueue
 * @package um_ext\um_reviews\core
 */
class Reviews_Enqueue {


	/**
	 * Reviews_Enqueue constructor.
	 */
	function __construct() {
		add_action( 'wp_enqueue_scripts',  array( &$this, 'wp_enqueue_scripts' ), 0 );
	}


	/**
	 * Enqueue scripts/styles
	 */
	function wp_enqueue_scripts() {
		$suffix = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG || defined( 'UM_SCRIPT_DEBUG' ) ) ? '' : '.min';

		wp_register_script( 'um_reviews', um_reviews_url . 'assets/js/um-reviews' . $suffix . '.js', array( 'jquery', 'wp-util', 'um_raty', 'um_scripts' ), um_reviews_version, true );
		// Localize the script with new data
		$translation_array = array(
			'add_rating'                => __( 'Please add a rating.', 'um-reviews' ),
			'provide_title'             => __( 'You must provide a title.', 'um-reviews' ),
			'provide_review_content'    => __( 'You must provide review content.', 'um-reviews' ),
			'remove'                    => __( 'Remove', 'um-reviews' ),
			'cancel'                    => __( 'Cancel', 'um-reviews' ),
			'star'                      => __( 'Star', 'um-reviews' ),
		);
		wp_localize_script( 'um_reviews', 'um_reviews', $translation_array );

		wp_register_style( 'um_reviews', um_reviews_url . 'assets/css/um-reviews' . $suffix . '.css', array( 'um_raty' ), um_reviews_version );
	}
}