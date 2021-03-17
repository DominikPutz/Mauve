<?php
namespace um_ext\um_user_photos\core;


if ( ! defined( 'ABSPATH' ) ) exit;


/**
 * Class User_Photos_Account
 * @package um_ext\um_user_photos\core
 */
class User_Photos_Account {


	/**
	 * User_Photos_Account constructor.
	 */
	function __construct() {
		add_filter( 'um_account_page_default_tabs_hook', array( $this, 'add_user_photos_tab' ), 100 );
		add_action( 'um_account_tab__um_user_photos', array( $this, 'um_account_tab__um_user_photos' ) );
		add_filter( 'um_account_content_hook_um_user_photos', array( $this, 'um_account_content_hook_um_user_photos' ) );
	}


	/**
	 * @param $tabs
	 *
	 * @return mixed
	 */
	function add_user_photos_tab( $tabs ) {
		$tabs[800]['um_user_photos']['icon'] = 'um-faicon-image';
		$tabs[800]['um_user_photos']['title'] = __( 'My Photos', 'um-user-photos' );
		$tabs[800]['um_user_photos']['custom'] = true;
		$tabs[800]['um_user_photos']['show_button']  = false;

		return $tabs;
	}


	/**
	 * @param $info
	 */
	function um_account_tab__um_user_photos( $info ) {
		if ( is_array( $info ) ) {
			extract( $info );
		}

		$output = UM()->account->get_tab_output( 'um_user_photos' );
		if ( $output ) {
			echo $output;
		}
	}


	/**
	 * Get template for the "Account" page "My Photos" tab
	 * @hook    'um_account_content_hook_um_user_photos'
	 * @param   string  $output
	 * @return  string
	 */
	function um_account_content_hook_um_user_photos( $output = '' ) {
		wp_enqueue_script( 'um-user-photos' );
		wp_enqueue_style( 'um-user-photos' );

		$user_id = um_user( 'ID' );

		$download_my_photos_notice = get_user_meta( $user_id, 'um_download_my_photos_notice', true );
		if ( $download_my_photos_notice ) {
			update_user_meta( $user_id, 'um_download_my_photos_notice', '' );
		}

		$t_args = compact( 'download_my_photos_notice', 'user_id' );
		$output .= UM()->get_template( 'account.php', um_user_photos_plugin, $t_args );

		return $output;
	}

}