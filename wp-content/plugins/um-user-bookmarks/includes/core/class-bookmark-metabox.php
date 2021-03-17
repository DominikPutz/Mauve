<?php
namespace um_ext\um_user_bookmarks\core;


if ( ! defined( 'ABSPATH' ) ) exit;


/**
 * Class Bookmark_Metabox
 *
 * @package um_ext\um_user_bookmarks\core
 */
class Bookmark_Metabox {


	/**
	 * Bookmark_Metabox constructor.
	 */
	function __construct() {
		add_action( 'add_meta_boxes', array( $this, 'add_metabox' ) );
		add_action( 'save_post', array( $this, 'save_um_user_bookmarks_data' ) );
	}


	/**
	 *
	 */
	function add_metabox() {
		$post_types = UM()->options()->get( 'um_user_bookmarks_post_types' );

		add_meta_box(
			"um-admin-custom-bookmarks/metabox{" . um_user_bookmarks_path . "}",
			__( 'UM User Bookmarks', 'um-frontend-posting' ),
			array( UM()->metabox(), 'load_metabox_custom' ),
			$post_types,
			'advanced',
			'default'
		);
	}


	/**
	 * @param $post_id
	 */
	function save_um_user_bookmarks_data( $post_id ) {
		if ( isset( $_POST['_um_user_bookmarks'] ) ) {
			update_post_meta( $post_id, '_um_user_bookmarks', UM()->clean_array( $_POST['_um_user_bookmarks'] ) );
		}
	}
}