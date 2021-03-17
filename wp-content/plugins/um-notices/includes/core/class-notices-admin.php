<?php
namespace um_ext\um_notices\core;


if ( ! defined( 'ABSPATH' ) ) exit;


/**
 * Class Notices_Admin
 * @package um_ext\um_notices\core
 */
class Notices_Admin {


	/**
	 * Notices_Admin constructor.
	 */
	function __construct() {
		$this->slug = 'ultimatemember';
		$this->pagehook = 'toplevel_page_ultimatemember';

		add_action( 'um_extend_admin_menu',  array( &$this, 'um_extend_admin_menu' ), 800 );
		add_filter( 'enter_title_here', array( &$this, 'enter_title_here') );

		add_filter( 'manage_edit-um_notice_columns', array( &$this, 'manage_edit_um_notice_columns' ) );
		add_action( 'manage_um_notice_posts_custom_column', array( &$this, 'manage_um_notice_posts_custom_column' ), 10, 3 );

		add_action( 'um_admin_do_action__flush_notice', array( &$this,'um_admin_do_action__flush_notice' ), 10, 1 );
	}


	/**
	 * Flush a notice
	 *
	 * @param string $action
	 */
	function um_admin_do_action__flush_notice( $action ) {
		if ( ! is_admin() || ! current_user_can( 'manage_options' ) ) {
			die();
		}

		delete_post_meta( absint( $_REQUEST['notice_id'] ), '_users' );

		$url = remove_query_arg('um_adm_action', UM()->permalinks()->get_current_url() );
		exit( wp_redirect( $url ) );
	}


	/**
	 * Custom title
	 *
	 * @param string $title
	 *
	 * @return string
	 */
	function enter_title_here( $title ) {
		$screen = get_current_screen();
		if ( 'um_notice' == $screen->post_type ) {
			$title = __( 'Enter notice title here', 'um-notices' );
		}

		return $title;
	}


	/**
	 * Extends the admin menu
	 */
	function um_extend_admin_menu() {
		add_submenu_page( $this->slug, __( 'Notices', 'um-notices' ), __( 'Notices', 'um-notices' ), 'manage_options', 'edit.php?post_type=um_notice', '' );
	}


	/**
	 * Add columns
	 *
	 * @param array $columns
	 *
	 * @return array
	 */
	function manage_edit_um_notice_columns( $columns ) {
		$columns['shortcode'] = __('Shortcode','um-notices');
		$columns['reach'] = __('Reach','um-notices') . UM()->tooltip( __('How many people reached this notice? Count users who seen and closed the notice only','um-notices') );
		return $columns;
	}


	/**
	 * Show columns
	 *
	 * @param string $column_name
	 * @param int $id
	 */
	function manage_um_notice_posts_custom_column( $column_name, $id ) {
		switch ( $column_name ) {
			case 'shortcode':
				echo '[ultimatemember_notice id="' . $id . '" /]';
				break;
			case 'reach':
				$count = 0;
				$users = get_post_meta( $id, '_users', true );
				if ( is_array( $users ) ) {
					$count = count( $users );
				}

				echo '<span class="um-admin-icontext"><i class="um-icon-stats-bars"></i> ' . $count . '</span>';
		}
	}

}