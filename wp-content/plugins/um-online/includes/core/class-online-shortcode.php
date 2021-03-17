<?php
namespace um_ext\um_online\core;


if ( ! defined( 'ABSPATH' ) ) exit;


/**
 * Class Online_Shortcode
 * @package um_ext\um_online\core
 */
class Online_Shortcode {


	/**
	 * Online_Shortcode constructor.
	 */
	function __construct() {
		add_shortcode( 'ultimatemember_online', array( &$this, 'ultimatemember_online' ) );
	}


	/**
	 * Online users list shortcode
	 *
	 * @param array $args
	 *
	 * @return string
	 */
	function ultimatemember_online( $args = array() ) {
		UM()->Online()->enqueue_scripts();

		$defaults = array(
			'max'   => 11,
			'roles' => 'all'
		);
		$args = wp_parse_args( $args, $defaults );

		$args['online'] = UM()->Online()->get_users();
		$template = ( $args['online'] && count( $args['online'] ) > 0 ) ? 'online' : 'nobody';

		ob_start();

		UM()->get_template( "{$template}.php", um_online_plugin, $args, true );

		$output = ob_get_clean();
		return $output;
	}
}