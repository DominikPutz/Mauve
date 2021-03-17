<?php
namespace um_ext\um_user_photos\core;


if ( ! defined( 'ABSPATH' ) ) exit;


/**
 * Class User_Photos_Profile
 * @package um_ext\um_user_photos\core
 */
class User_Photos_Profile {


	/**
	 * User_Photos_Profile constructor.
	 */
	function __construct() {
		add_filter( 'um_profile_tabs', array( $this, 'add_profile_tab' ), 800 );
		add_filter( 'um_user_profile_tabs', array( &$this, 'add_user_tab' ), 5, 1 );

		add_action( 'um_profile_content_photos_default', array( $this, 'get_gallery_content' ) );
		add_action( 'um_profile_content_photos_albums', array( $this, 'get_gallery_content' ) );
		
		add_action( 'um_profile_content_photos_photo', array( $this, 'get_gallery_photos_content' ) );
	}


	/**
	 * Add tab for Photos
	 *
	 * @param array $tabs
	 *
	 * @return array
	 */
	function add_profile_tab( $tabs ) {
		$tabs['photos'] = array(
			'name' => __( 'Photos', 'um-user-photos' ),
			'icon' => 'um-icon-images',
		);

		return $tabs;
	}



	function add_user_tab( $tabs ) {
		if ( empty( $tabs['photos'] ) ) {
			return $tabs;
		}

		if ( ! um_user( 'enable_user_photos' ) ) {
			unset( $tabs['photos'] );
			return $tabs;
		}

		$tabs['photos']['subnav'] = array(
			'albums'    => __( 'Albums', 'um-user-photos' ),
			'photo'     => __( 'Photos', 'um-user-photos' )
		);
		$tabs['photos']['subnav_default'] = 'albums';
		return $tabs;
	}


	/**
	 * Galleries Content
	 */
	function get_gallery_content() {
		$shortcode = '[ultimatemember_gallery user_id="'.um_user('ID').'"]';
		echo do_shortcode( $shortcode );
	}


	/**
	 * Gallery Content
	 */
	function get_gallery_photos_content() {
		$shortcode = '[ultimatemember_gallery_photos user_id="'.um_user('ID').'"]';
		echo do_shortcode( $shortcode );
	}
}