<?php
namespace um_ext\um_private_content\core;

if ( ! defined( 'ABSPATH' ) ) exit;


class Private_Content_Shortcode {

	function __construct() {
	
		add_shortcode( 'um_private_content', array(&$this, 'private_content_shortcode') );

	}


    /**
     * @param array $args
     * @return string
     */
    function private_content_shortcode( $args = array() ) {

        if ( ! is_user_logged_in() )
            return '';

        um_fetch_user( get_current_user_id() );

        $private_post_id = get_user_meta( um_user( 'ID' ), '_um_private_content_post_id', true );

        $post = get_post( $private_post_id );

        if ( ! empty( $post ) ) {
        	ob_start();
            setup_postdata( $post );
            the_content();
            wp_reset_postdata();
            return ob_get_clean();
        }
    }

}