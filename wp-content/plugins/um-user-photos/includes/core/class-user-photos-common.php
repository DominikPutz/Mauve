<?php
namespace um_ext\um_user_photos\core;


if ( ! defined( 'ABSPATH' ) ) exit;


/**
 * Class User_Photos_Common
 * @package um_ext\um_user_photos\core
 */
class User_Photos_Common {


	/**
	 * User_Photos_Common constructor.
	 */
	function __construct() {
		add_action( 'init', array( $this, 'photos_post_type' ), 0 );
		add_action( 'template_redirect', array( $this, 'photos_redirect' ) );
		add_filter( 'ajax_query_attachments_args', array( $this, 'hide_cv_media_overlay_view' ), 10, 1 );

		add_action( 'after_setup_theme', array( $this, 'add_image_sizes' ) );
	}


	/**
	 * Register Custom Post Type
	 */
	function photos_post_type() {
		$labels = array(
			'name'                  => _x( 'User Photos', 'Post Type General Name', 'um-user-photos' ),
			'singular_name'         => _x( 'User Album', 'Post Type Singular Name', 'um-user-photos' ),
			'menu_name'             => __( 'User Photos', 'um-user-photos' ),
			'name_admin_bar'        => __( 'User Photos', 'um-user-photos' ),
			'archives'              => __( 'Item Archives', 'um-user-photos' ),
			'attributes'            => __( 'Item Attributes', 'um-user-photos' ),
			'parent_item_colon'     => __( 'Parent Item:', 'um-user-photos' ),
			'all_items'             => __( 'All Items', 'um-user-photos' ),
			'add_new_item'          => __( 'Add New Item', 'um-user-photos' ),
			'add_new'               => __( 'Add New', 'um-user-photos' ),
			'new_item'              => __( 'New Item', 'um-user-photos' ),
			'edit_item'             => __( 'Edit Item', 'um-user-photos' ),
			'update_item'           => __( 'Update Item', 'um-user-photos' ),
			'view_item'             => __( 'View Item', 'um-user-photos' ),
			'view_items'            => __( 'View Items', 'um-user-photos' ),
			'search_items'          => __( 'Search Item', 'um-user-photos' ),
			'not_found'             => __( 'Not found', 'um-user-photos' ),
			'not_found_in_trash'    => __( 'Not found in Trash', 'um-user-photos' ),
			'featured_image'        => __( 'Album Cover', 'um-user-photos' ),
			'set_featured_image'    => __( 'Set album cover', 'um-user-photos' ),
			'remove_featured_image' => __( 'Remove album cover', 'um-user-photos' ),
			'use_featured_image'    => __( 'Use as album cover', 'um-user-photos' ),
			'insert_into_item'      => __( 'Insert into item', 'um-user-photos' ),
			'uploaded_to_this_item' => __( 'Uploaded to this item', 'um-user-photos' ),
			'items_list'            => __( 'Items list', 'um-user-photos' ),
			'items_list_navigation' => __( 'Items list navigation', 'um-user-photos' ),
			'filter_items_list'     => __( 'Filter items list', 'um-user-photos' ),
		);
		$args = array(
			'label'                 => __( 'User Photos', 'um-user-photos' ),
			'description'           => __( 'Image gallery for Ultimate member Users', 'um-user-photos' ),
			'labels'                => $labels,
			'supports'              => array( 'title','thumbnail','author'),
			'hierarchical'          => false,
			'public'                => false,
			'show_ui'               => false,
			'show_in_menu'          => false,
			'menu_position'         => 5,
			'show_in_admin_bar'     => true,
			'show_in_nav_menus'     => false,
			'can_export'            => true,
			'has_archive'           => false,
			'exclude_from_search'   => true,
			'publicly_queryable'    => true,
			'capability_type'       => 'page',
		);
		register_post_type( 'um_user_photos', $args );
	}


	/**
	 * No access to single um_user_photos post type
	 */
	function photos_redirect() {
		if ( is_singular( 'um_user_photos' ) ) {
			wp_redirect( home_url() );
		}
	}


	/**
	 * Additional meta query for photos
	 *
	 * @param array $args
	 *
	 * @return array
	 */
	function hide_cv_media_overlay_view( $args ) {
		// Bail if this is not the admin area.
		if ( ! is_admin() ) {
			return $args;
		}

		$args['meta_query'] = array(
			array(
				'key'     => '_part_of_gallery',
				'compare' => 'NOT EXISTS',
			)
		);

		return $args;
	}


	/**
	 * Create custom Image sizes for Gallery
	 */
	function add_image_sizes() {
		$cover_width = 350;
		$cover_height = 250;
		$cover_size = UM()->options()->get( 'um_user_photos_cover_size' );
		if ( $cover_size && trim( $cover_size ) != '' ) {
			$cover_size = strtolower( $cover_size );
			$size = explode( 'x', $cover_size );
			if ( is_array( $size ) && count( $size ) == 2 ) {
				$cover_width = intval( $size[0] );
				$cover_height = intval( $size[1] );
			}
		}
		add_image_size( 'album_cover', $cover_width, $cover_height, true );

		$photo_width = 250;
		$photo_height = 250;
		$photo_size = UM()->options()->get( 'um_user_photos_image_size' );
		if ( $photo_size && trim( $photo_size ) != '' ) {
			$photo_size = strtolower( $photo_size );
			$size = explode( 'x', $photo_size );
			if ( is_array( $size ) && count( $size ) == 2 ) {
				$photo_width = intval( $size[0] );
				$photo_height = intval( $size[1] );
			}
		}

		add_image_size( 'gallery_image', $photo_width, $photo_height, true );
	}


	/**
	 * @param $album_id
	 *
	 * @return string
	 */
	function um_photos_get_album_cover( $album_id ) {
		
		$photos = get_post_meta( $album_id,'_photos',true );
		if ( has_post_thumbnail( $album_id ) ) {
			$image = wp_get_attachment_image_src( get_post_thumbnail_id( $album_id ), 'album_cover' );
			$img = $image[0];
		} elseif ( $photos && is_array( $photos ) && ! empty( $photos ) ) {
			$photo_id = $photos[0];
			$image = wp_get_attachment_image_src( $photo_id, 'album_cover' );
			$img = $image[0];
		} else {
			$img = um_user_photos_url . '/assets/images/dummy_album_cover.png';
			$img = $this->generate_album_cover_placeholder();
		}
		
		return $img;
	}


	/**
	 * @param int $width
	 * @param int $height
	 * @param string $background
	 *
	 * @return string
	 */
	function generate_album_cover_placeholder( $width = 340, $height = 240, $background = '0, 0, 0, 0.8' ) {
		ob_start();
		$width = absint( $width );
		$height = absint( $height );

		$cover_size = UM()->options()->get( 'um_user_photos_cover_size' );
		if ( $cover_size && trim( $cover_size ) != '' ) {
			$cover_size = strtolower( $cover_size );
			$size = explode( 'x', $cover_size );
			if ( is_array( $size ) && count( $size ) == 2 ) {
				$width = intval( $size[0] );
				$height = intval( $size[1] );
			}
		}
		
		$image = imagecreatetruecolor( $width, $height );
		imagesavealpha( $image, true );
		$color = imagecolorallocatealpha( $image,243,243,243,1 );
		imagefill( $image, 0, 0, $color );

		imagepng( $image );
		$imagedata = ob_get_clean();
		return 'data:image/png;base64,'.base64_encode($imagedata);
	}
}