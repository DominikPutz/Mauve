<?php
namespace um_ext\um_user_bookmarks\core;


if ( ! defined( 'ABSPATH' ) ) exit;


/**
 * Class Bookmark_Privacy
 * @package um_ext\um_user_bookmarks\core
 */
class Bookmark_Privacy {


	/**
	 * Bookmark_Privacy constructor.
	 */
	function __construct() {
		add_filter( 'um_account_tab_privacy_fields', array( $this , 'um_user_bookmarks_account_privacy_fields' ), 11, 2 );
		add_filter( 'um_predefined_fields_hook', array( $this , 'um_user_bookmarks_account_privacy_fields_add' ) );
		add_filter( 'um_user_permissions_filter',  array( $this , 'um_user_bookmarks_user_permissions_filter' ), 10, 4 );
	}


	/**
	 * @param $fields
	 *
	 * @return array
	 */
	function um_user_bookmarks_account_privacy_fields_add( $fields ) {
		if ( ! um_user( 'enable_bookmark' ) ) {
			return $fields;
		}

		$array =  array(
			'everyone' => __( 'Everyone', 'um-user-bookmarks' ),
			'only_me'  => __( 'Only me', 'um-user-bookmarks' )
		);

		$bookmark_privacy = apply_filters( 'um_user_bookmarks_privacy_dropdown_values', $array );

		$fields['um_bookmark_privacy'] = array(
			'title'         => __( 'Who can see user bookmarks tab?', 'um-user-bookmarks' ),
			'metakey'       => 'um_bookmark_privacy',
			'type'          => 'select',
			'label'         => __( 'Who can see user bookmarks tab?', 'um-user-bookmarks' ),
			'required'      => 0,
			'public'        => 1,
			'editable'      => 1,
			'default'       => 'everyone',
			'options'       => $bookmark_privacy,
			'options_pair'  => 1,
			'allowclear'    => 0,
			'account_only'  => true,
		);

		UM()->account()->add_displayed_field( 'um_bookmark_privacy', 'privacy' );

		return $fields;
	}


	/**
	 * @param $args
	 * @param $shortcode_args
	 *
	 * @return string
	 */
	function um_user_bookmarks_account_privacy_fields( $args, $shortcode_args ) {
		if ( um_user( 'enable_bookmark' ) ) {
			if ( isset( $shortcode_args['um_bookmark_privacy'] ) && 0 == $shortcode_args['um_bookmark_privacy'] )
				return $args;

			if ( UM()->options()->get( 'bookmark_enable_privacy' ) ) {
				$args = $args . ',um_bookmark_privacy';
			}

			$args = $args . ',um_bookmark_privacy';
		}

		return $args;
	}


	/**
	 * @param $meta
	 * @param $user_id
	 *
	 * @return mixed
	 */
	function um_user_bookmarks_user_permissions_filter( $meta, $user_id ) {
		if ( ! isset( $meta['enable_bookmark'] ) ) {
			$meta['enable_bookmark'] = 1;
		}

		return $meta;
	}
}