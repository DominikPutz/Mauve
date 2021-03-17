<?php
namespace um_ext\um_notices\core;


if ( ! defined( 'ABSPATH' ) ) exit;


/**
 * Class Notices_Shortcode
 * @package um_ext\um_notices\core
 */
class Notices_Shortcode {


	/**
	 * Notices_Shortcode constructor.
	 */
	function __construct() {
		add_shortcode( 'ultimatemember_notice', array( &$this, 'ultimatemember_notice' ) );
	}


	/**
	 * Shortcode
	 *
	 * @param array $args
	 *
	 * @return string
	 */
	function ultimatemember_notice( $args = array() ) {
		wp_enqueue_script( 'um_notices' );
		wp_enqueue_style( 'um_notices' );

		$t_args = compact( 'args' );
		$output = UM()->get_template( 'shortcode.php', um_notices_plugin, $t_args );

		UM()->Notices()->shortcodes[ $args['id'] ] = 1;

		return $output;
	}

}