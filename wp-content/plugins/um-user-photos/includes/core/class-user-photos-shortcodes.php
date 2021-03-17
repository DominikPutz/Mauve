<?php
namespace um_ext\um_user_photos\core;


if ( ! defined( 'ABSPATH' ) ) exit;


/**
 * Class User_Photos_Shortcodes
 * @package um_ext\um_user_photos\core
 */
class User_Photos_Shortcodes {


	/**
	 * User_Photos_Shortcodes constructor.
	 */
	function __construct() {

		add_action( 'wp_enqueue_scripts', array( &$this, 'wp_enqueue_scripts' ), 9999 );

		add_shortcode( 'ultimatemember_gallery', array( $this, 'get_gallery_content' ) );
		add_shortcode( 'ultimatemember_gallery_photos', array( $this, 'gallery_photos_content' ) );

	}


	function wp_enqueue_scripts() {
		$suffix = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG || defined( 'UM_SCRIPT_DEBUG' ) ) ? '' : '.min';

		wp_register_style( 'um-images-grid', um_user_photos_url . 'assets/css/images-grid' . $suffix . '.css', array(),um_user_photos_version );
		wp_register_style( 'um-user-photos', um_user_photos_url . 'assets/css/um-user-photos' . $suffix . '.css', array( 'um-images-grid' ), um_user_photos_version );

		wp_register_script( 'um-images-grid', um_user_photos_url . 'assets/js/images-grid' . $suffix . '.js', array( 'jquery' ), um_user_photos_version, true );

		wp_register_script( 'um-user-photos', um_user_photos_url . 'assets/js/um-user-photos' . $suffix . '.js', array( 'wp-util', 'um-images-grid' ), um_user_photos_version, true );


		wp_enqueue_script( 'um-images-grid' );
		wp_enqueue_style( 'um-images-grid' );
		wp_enqueue_script( 'um-user-photos' );
		wp_enqueue_style( 'um-user-photos' );
	}


	/**
	 * Display the "Albums" block
	 *
	 * @param array $atts
	 * @return string
	 */
	function get_gallery_content( $atts = array() ) {

		if ( ! empty( $atts ) ) {
			extract( $atts );
		}

		if ( ! isset( $user_id ) ) {
			$user_id = um_user( 'ID' );
		}
		if ( isset( $_POST['user_id'] ) ) {
			$user_id = absint( $_POST['user_id'] );
		}

		$is_my_profile = is_user_logged_in() && get_current_user_id() == $user_id;

		$args_t = compact( 'is_my_profile', 'user_id' );
		$output = UM()->get_template( 'gallery-head.php', um_user_photos_plugin, $args_t );

		wp_enqueue_script( 'um-user-photos' );
		wp_enqueue_style( 'um-user-photos' );

		$albums = new \WP_Query( array(
			'post_type'         => 'um_user_photos',
			'author__in'        => array( $user_id ),
			'posts_per_page'    => -1,
			'post_status'       => 'publish'
		) );

		if ( empty( $albums ) || ! $albums->have_posts() ) {
			return $output;
		}

		$args_t = compact( 'albums', 'user_id' );
		$output .= UM()->get_template( 'gallery.php', um_user_photos_plugin, $args_t );

		return $output;
	}


	/**
	 * Display the "Photos" block
	 *
	 * @param array $atts
	 * @return string
	 */
	function gallery_photos_content( $atts = array() ) {

		if ( ! empty( $atts ) ) {
			extract( $atts );
		}

		if ( empty( $user_id ) ) {
			$user_id = um_user( 'ID' );
		}
		if ( isset( $_POST['user_id'] ) ) {
			$user_id = absint( $_POST['user_id'] );
		}

		$is_my_profile = is_user_logged_in() && get_current_user_id() == $user_id;

		$column = UM()->options()->get( 'um_user_photos_images_column' );
		if ( ! $column ) {
			$column = 'um-user-photos-col-3';
		}
		$per_page = intval( substr( $column, -1 ) );

		$latest_photos = new \WP_Query( array(
			'post_type'         => 'attachment',
			'author__in'        => array( $user_id ),
			'post_status'       => 'inherit',
			'post_mime_type'    => 'image',
			'posts_per_page'    => $per_page,
			'meta_query'        => array(
				array(
					'key'       => '_part_of_gallery',
					'value'     => 'yes',
					'compare'   => '=',
				)
			)
		) );

		if ( empty( $latest_photos ) || ! $latest_photos->have_posts() ) {
			return '';
		}

		$count = $latest_photos->found_posts;

		$photos = array();
		foreach ( $latest_photos->posts as $photo ) {
			$photos[] = $photo->ID;
		}

		$args_t = compact( 'count', 'is_my_profile', 'per_page', 'photos', 'user_id' );
		$output = UM()->get_template( 'photos.php', um_user_photos_plugin, $args_t );

		wp_enqueue_script( 'um-user-photos' );
		wp_enqueue_style( 'um-user-photos' );

		return $output;
	}

}