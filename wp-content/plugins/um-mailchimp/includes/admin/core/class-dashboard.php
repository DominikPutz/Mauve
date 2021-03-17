<?php

namespace um_ext\um_mailchimp\admin\core;

if ( !defined( 'ABSPATH' ) ) exit;

/**
 * Class for admin side functionality
 *
 * @example UM()->classes['um_mailchimp_dashboard']
 * @example UM()->Mailchimp()->admin()->dashboard()
 */
class Dashboard {

	const batch_limit = 500;

	private $wp_list = null;

	public function __construct() {

		add_action( 'admin_menu', array( $this, 'prepare_metabox' ), 20 );

		add_action( 'wp_ajax_um_mailchimp_bulk_subscribe', array( $this, 'ajax_bulk_action' ) );
		add_action( 'wp_ajax_um_mailchimp_bulk_unsubscribe', array( $this, 'ajax_bulk_action' ) );
		add_action( 'wp_ajax_um_mailchimp_scan_now', array( $this, 'ajax_scan_now' ) );
		add_action( 'wp_ajax_um_mailchimp_sync_now', array( $this, 'ajax_sync_now' ) );
	}

	/**
	 * Bulk Subscribe & Unubscribe
	 *
	 * @since 2.2.0
	 */
	public function ajax_bulk_action() {
		UM()->admin()->check_ajax_nonce();

		$action = filter_input( 0, 'action', FILTER_SANITIZE_STRING );
		if ( empty( $action ) ) {
			wp_send_json_error( __( 'Empty action', 'um-mailchimp' ) );
		}

		$action_key = filter_input( 0, 'key', FILTER_SANITIZE_STRING );
		if ( empty( $action_key ) ) {
			wp_send_json_error( __( 'Empty key', 'um-mailchimp' ) );
		}

		$list_id = filter_input( 0, 'list_id', FILTER_SANITIZE_STRING );
		if ( is_numeric( $list_id ) ) {
			$this->wp_list = get_post( $list_id );
			$list_id = $this->wp_list->_um_list;
		}
		elseif ( !empty( $list_id ) ) {
			$this->wp_list = UM()->Mailchimp()->api()->get_wp_list( $list_id );
		}
		else {
			wp_send_json_error( __( 'Empty audience ID' ) );
		}
		if ( empty( $this->wp_list ) ) {
			wp_send_json_error( __( 'Wrong list' ) );
		}

		$offset = filter_input( 0, 'offset', FILTER_SANITIZE_STRING );
		if ( empty( $offset ) ) {
			$offset = 0;
		}

		$length = filter_input( 0, 'length', FILTER_SANITIZE_STRING );
		if ( empty( $length ) ) {
			$length = ceil( self::batch_limit * 0.4 );
		}

		$role = filter_input( 0, 'role', FILTER_SANITIZE_STRING );
		$status = filter_input( 0, 'status', FILTER_SANITIZE_STRING );

		$users_all = $this->get_users( $action_key, $role, $status );
		if ( empty( $users_all ) ) {
			wp_send_json_error( __( 'You don\'t have any users', 'um-mailchimp' ) );
		}
		else {
			$total = count( $users_all );
		}

		$users = array_slice( $users_all, $offset, $length );
		$subtotal = count( $users );

		switch ( $action ) {
			case 'um_mailchimp_bulk_subscribe':
				$batch = $this->bulk_subscribe_process( $list_id, $users, 'subscribed', $action_key, $offset );
				break;
			case 'um_mailchimp_bulk_unsubscribe':
				$batch = $this->bulk_unsubscribe_process( $list_id, $users, 'unsubscribed', $action_key, $offset );
				break;
			default :
				wp_send_json_error( __( 'Unknown action', 'um-mailchimp' ) );
		}

		if ( is_wp_error( $batch ) ) {
			wp_send_json_error( $batch->get_error_message() );
		}

		if ( $subtotal < $length ) {
			$message = __( 'Completed', 'um-mailchimp' );
			UM()->Mailchimp()->api()->delete_temp_files( 1, $action_key );
		}
		else {
			$message = sprintf( __( 'Processed... %s', 'um-mailchimp' ), round( ($offset + $subtotal) / $total * 100 ) ) . '%';
		}

		$response = array(
			'batch'		 => $batch,
			'key'			 => $action_key,
			'list_id'	 => $list_id,
			'message'	 => $message,
			'length'	 => (int) $length,
			'offset'	 => (int) $offset,
			'subtotal' => (int) $subtotal,
			'total'		 => (int) $total
		);

		wp_send_json_success( $response );
	}

	/**
	 * Bulk Subscribe
	 *
	 * @deprecated since version 2.2.0
	 */
	public function ajax_bulk_subscribe() {
		$this->ajax_bulk_action();
	}

	/**
	 * Bulk Unubscribe
	 *
	 * @deprecated since version 2.2.0
	 */
	public function ajax_bulk_unsubscribe() {
		$this->ajax_bulk_action();
	}

	/**
	 * Find mached users
	 */
	public function ajax_scan_now() {
		UM()->admin()->check_ajax_nonce();

		$role = filter_input( 0, 'role', FILTER_SANITIZE_STRING );
		$status = filter_input( 0, 'status', FILTER_SANITIZE_STRING );
		$action_key = filter_input( 0, 'key', FILTER_SANITIZE_STRING );
		if ( empty( $action_key ) ) {
			$action_key = uniqid();
		}

		$users = $this->get_users( $action_key, $role, $status );
		$total = count( $users );

		// display the results
		wp_send_json_success( array(
			'key'			 => $action_key,
			'message'	 => sprintf( _n( '%d user was selected', '%d users were selected', $total, 'um-mailchimp' ), $total ),
			'total'		 => $total
		) );
	}

	/**
	 * "Sync Profiles" tool handler
	 */
	public function ajax_sync_now() {
		UM()->admin()->check_ajax_nonce();

		$action_key = filter_input( 0, 'key', FILTER_SANITIZE_STRING );
		if ( empty( $action_key ) ) {
			$action_key = uniqid();
		}

		$list_id = filter_input( 0, 'list_id', FILTER_SANITIZE_STRING );
		if ( is_numeric( $list_id ) ) {
			$this->wp_list = get_post( $list_id );
			$list_id = $this->wp_list->_um_list;
		}
		elseif ( !empty( $list_id ) ) {
			$this->wp_list = UM()->Mailchimp()->api()->get_wp_list( $list_id );
		}
		else {
			wp_send_json_error( __( 'Empty audience ID' ) );
		}
		if ( empty( $this->wp_list ) ) {
			wp_send_json_error( __( 'Wrong list' ) );
		}

		$offset = filter_input( 0, 'offset', FILTER_SANITIZE_STRING );
		if ( empty( $offset ) ) {
			$offset = 0;
		}

		$length = filter_input( 0, 'length', FILTER_SANITIZE_STRING );
		if ( empty( $length ) ) {
			$length = ceil( self::batch_limit * 0.4 );
		}


		if ( function_exists( 'set_time_limit' ) && false === strpos( ini_get( 'disable_functions' ), 'set_time_limit' ) && !ini_get( 'safe_mode' ) ) { // phpcs:ignore PHPCompatibility.PHP.DeprecatedIniDirectives.safe_modeDeprecatedRemoved
			@set_time_limit( 0 ); // @codingStandardsIgnoreLine
		}


		$users_all = $this->get_users( $action_key );
		if ( empty( $users_all ) ) {
			wp_send_json_error( __( 'You don\'t have any users', 'um-mailchimp' ) );
		}
		else {
			$total = count( $users_all );
		}

		$users = array_slice( $users_all, $offset, $length );
		$subtotal = count( $users );

		$mc_emails = $this->get_users_external( $action_key, $list_id, true );

		/* >> prepare $data */
		$data = array( 'wp_list' => $this->wp_list );
		if ( $this->wp_list->_um_reg_status ) {

			$groups = UM()->Mailchimp()->api()->prepare_groups( $list_id, $this->wp_list );

			$tags = UM()->Mailchimp()->api()->prepare_tags( $list_id, $this->wp_list->_um_reg_tags );

			$data = array(
				'status'  => 'subscribed',
				'groups'  => $groups,
				'tags'    => $this->wp_list->_um_reg_tags,
				'wp_list' => $this->wp_list
			);
		}
		/* << prepare $data */

		$Batch = UM()->Mailchimp()->api()->call()->new_batch();

		foreach ( $users as $user ) {
			$email_md5 = md5( $user->user_email );

			$user_lists = get_user_meta( $user->ID, '_mylists', true );
			if ( !is_array( $user_lists ) ) {
				$user_lists = array();
			}

			if ( in_array( $user->user_email, $mc_emails ) && empty( $user_lists[ $list_id ] ) ) {
				//user only in mailchimp list
				UM()->Mailchimp()->api()->update_mylists( $list_id, $user->ID, $this->wp_list->ID );
			}
			elseif ( !in_array( $user->user_email, $mc_emails ) && !empty( $user_lists[ $list_id ] ) ) {
				//user only in internal list
				$request_data = apply_filters( 'um_mailchimp_api_create_member', array(
					'email_address'	 => $user->user_email,
					'status'				 => 'subscribed'
					), $list_id, $user->ID, $data );

				$Batch->post( "op_uid:{$user->ID}_list:{$list_id}_key:{$action_key}_o:{$offset}", "lists/{$list_id}/members", $request_data );

				//update tags
				if ( !empty( $tags ) ) {
					$Batch->post( "op_tags_uid:{$user->ID}_list:{$list_id}_key:{$action_key}_o:{$offset}", "lists/{$list_id}/members/{$email_md5}/tags", array( 'tags' => $tags ) );
				}
			}
			elseif ( in_array( $user->user_email, $mc_emails ) && !empty( $user_lists[ $list_id ] ) ) {
				//user in both lists, need only update data in mailchimp list
				$request_data = apply_filters( 'um_mailchimp_api_update_member', array(
					'email_address'	 => $user->user_email,
					'status'				 => 'subscribed'
					), $list_id, $user->ID, array() );

				$Batch->put( "op_uid:{$user->ID}_list:{$list_id}_key:{$action_key}_o:{$offset}", "lists/{$list_id}/members/{$email_md5}", $request_data );
			}
		}

		$batch = $Batch->execute();
		if ( is_wp_error( $batch ) ) {
			wp_send_json_error( $batch->get_error_message() );
		}

		if ( $subtotal < $length ) {
			$message = __( 'Completed', 'um-mailchimp' );
			UM()->Mailchimp()->api()->delete_temp_files( 1, $action_key );
		}
		else {
			$message = sprintf( __( 'Processed... %s', 'um-mailchimp' ), round( ($offset + $subtotal) / $total * 100 ) ) . '%';
		}

		$response = array(
			'batch'		 => $batch,
			'key'			 => $action_key,
			'list_id'	 => $list_id,
			'message'	 => $message,
			'length'	 => (int) $length,
			'offset'	 => (int) $offset,
			'subtotal' => (int) $subtotal,
			'total'		 => (int) $total
		);

		wp_send_json_success( $response );
	}

	/**
	 * "Bulk Subscribe & Unubscribe" tool handler - subscribe
	 *
	 * @param string $list_id
	 * @param array $users
	 * @param string $status
	 * @param string $action_key
	 * @return array|\WP_Error
	 */
	public function bulk_subscribe_process( $list_id, $users, $status = 'subscribed', $action_key = '', $offset = 0 ) {

		if ( empty( $this->wp_list ) ) {
			$this->wp_list = UM()->Mailchimp()->api()->get_wp_list( $list_id );
		}
		if ( empty( $this->wp_list ) ) {
			return new \WP_Error( 'um_mailchimp_wrong_list', __( 'Wrong list', 'um-mailchimp' ) );
		}

		if ( empty( $users ) ) {
			return new \WP_Error( 'um_mailchimp_wrong_users', __( 'Wrong users', 'um-mailchimp' ) );
		}

		if ( function_exists( 'set_time_limit' ) && false === strpos( ini_get( 'disable_functions' ), 'set_time_limit' ) && !ini_get( 'safe_mode' ) ) { // phpcs:ignore PHPCompatibility.PHP.DeprecatedIniDirectives.safe_modeDeprecatedRemoved
			@set_time_limit( 0 ); // @codingStandardsIgnoreLine
		}

		/* >> prepare $data */
		$data = array( 'wp_list' => $this->wp_list );
		if ( $this->wp_list->_um_reg_status ) {

			$groups = UM()->Mailchimp()->api()->prepare_groups( $list_id, $this->wp_list );

			$tags = UM()->Mailchimp()->api()->prepare_tags( $list_id, $this->wp_list->_um_reg_tags );

			$data = array(
				'status'  => 'subscribed',
				'groups'  => $groups,
				'tags'    => $this->wp_list->_um_reg_tags,
				'wp_list' => $this->wp_list
			);
		}
		/* << prepare $data */

		$Batch = UM()->Mailchimp()->api()->call()->new_batch();

		foreach ( $users as $user ) {
			$email_md5 = md5( $user->user_email );

			$request_data = apply_filters( 'um_mailchimp_api_update_member', array(
				'email_address'	 => $user->user_email,
				'status'				 => $status
				), $list_id, $user->ID, $data );

			$Batch->put( "op_uid:{$user->ID}_list:{$list_id}_key:{$action_key}_o:{$offset}", "lists/{$list_id}/members/{$email_md5}", $request_data );

			//update tags
			if ( !empty( $tags ) ) {
				$Batch->post( "op_tags_uid:{$user->ID}_list:{$list_id}_key:{$action_key}_o:{$offset}", "lists/{$list_id}/members/{$email_md5}/tags", array( 'tags' => $tags ) );
			}

			UM()->Mailchimp()->api()->update_mylists( $list_id, $user->ID, $this->wp_list->ID );
		}

		$batch = $Batch->execute();

		return $batch;
	}

	/**
	 * "Bulk Subscribe & Unubscribe" tool handler - unsubscribe
	 *
	 * @param string $list_id
	 * @param array $users
	 * @param string $status
	 * @param string $action_key
	 * @return array|\WP_Error
	 */
	public function bulk_unsubscribe_process( $list_id, $users, $status = 'unsubscribed', $action_key = '', $offset = 0 ) {

		if ( empty( $this->wp_list ) ) {
			$this->wp_list = UM()->Mailchimp()->api()->get_wp_list( $list_id );
		}
		if ( empty( $this->wp_list ) ) {
			return new \WP_Error( 'um_mailchimp_wrong_list', __( 'Wrong list', 'um-mailchimp' ) );
		}

		if ( empty( $users ) ) {
			return new \WP_Error( 'um_mailchimp_wrong_users', __( 'Wrong users', 'um-mailchimp' ) );
		}

		if ( function_exists( 'set_time_limit' ) && false === strpos( ini_get( 'disable_functions' ), 'set_time_limit' ) && !ini_get( 'safe_mode' ) ) { // phpcs:ignore PHPCompatibility.PHP.DeprecatedIniDirectives.safe_modeDeprecatedRemoved
			@set_time_limit( 0 ); // @codingStandardsIgnoreLine
		}

		$Batch = UM()->Mailchimp()->api()->call()->new_batch();

		foreach ( $users as $user ) {
			$email_md5 = md5( $user->user_email );

			if ( UM()->options()->get( 'mailchimp_unsubscribe_delete' ) ) {
				$Batch->delete( "op_del_uid:{$user->ID}_list:{$list_id}_key:{$action_key}_o:{$offset}", "lists/{$list_id}/members/{$email_md5}" );
			}
			else {
				$Batch->patch( "op_rem_uid:{$user->ID}_list:{$list_id}_key:{$action_key}_o:{$offset}", "lists/{$list_id}/members/{$email_md5}", array(
					'email_address'	 => $user->user_email,
					'status'				 => $status,
				) );
			}

			UM()->Mailchimp()->api()->update_mylists( $list_id, $user->ID, 'remove' );
		}

		$batch = $Batch->execute();

		return $batch;
	}

	/**
	 * Get an array of the subscribed members emails from the audience
	 *
	 * @param string $action_key
	 * @param string $list_id
	 * @param boolean $cache
	 * @return array
	 */
	public function get_users_external( $action_key, $list_id, $cache = true ) {

		//get users list from cache
		$file = UM()->files()->upload_basedir . 'temp/_um_mailchimp_users_external_' . $action_key . '_' . $list_id . '.txt';
		if ( $cache && file_exists( $file ) ) {
			$mc_emails = array_map( 'trim', file( $file ) );
		}

		if ( empty( $mc_emails ) || !is_array( $mc_emails ) ) {
			$mc_emails = array();

			$members = UM()->Mailchimp()->api()->mc_get_members( $list_id );
			if ( is_array( $members ) ) {
				foreach ( $members as $member ) {
					if ( $member[ 'status' ] === 'subscribed' ) {
						$mc_emails[] = $member[ 'email_address' ];
					}
				}
			}

			//set users list cache
			if ( $cache ) {
				$fd = fopen( $file, 'w' );
				if ( $fd ) {
					foreach ( $mc_emails as $mc_email ) {
						fwrite( $fd, trim( $mc_email ) . PHP_EOL );
					}
					fclose( $fd );
				}
			}
		}

		return $mc_emails;
	}

	/**
	 * Get users for action
	 *
	 * @param string $action_key
	 * @param string $role
	 * @param string $status
	 * @return array
	 */
	public function get_users( $action_key, $role = '', $status = '' ) {

		//get users list from cache
		$file = UM()->files()->upload_basedir . 'temp/_um_mailchimp_users_' . $action_key . '_' . $role . '_' . $status . '.txt';
		if ( file_exists( $file ) ) {
			$users_json = file( $file );
			$users = array_map( 'json_decode', $users_json );
		}

		if ( empty( $users ) || !is_array( $users ) ) {
			//get all users with selected role and status
			$args = array(
				'fields' => array( 'user_email', 'ID' )
			);

			if ( !empty( $role ) ) {
				$args[ 'role' ] = $role;
			}

			if ( !empty( $status ) ) {
				$args[ 'meta_query' ][] = array(
					'key'			 => 'account_status',
					'value'		 => $status,
					'compare'	 => '=',
				);
			}

			$query_users = new \WP_User_Query( $args );
			$users = $query_users->get_results();

			//set users list cache
			$fd = fopen( $file, 'w' );
			if ( $fd ) {
				foreach ( $users as $user ) {
					fwrite( $fd, json_encode( $user, JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE ) . PHP_EOL );
				}
				fclose( $fd );
			}
		}

		return $users;
	}

	public function load_metabox() {
		add_meta_box(
			'um-metaboxes-mailchimp', __( 'MailChimp', 'um-mailchimp' ), array( &$this, 'metabox_content' ), UM()->Mailchimp()->admin()->pagehook, 'core', 'core'
		);
	}

	public function metabox_content() {
		$suffix = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG || defined( 'UM_SCRIPT_DEBUG' ) ) ? '' : '.min';

		wp_enqueue_script( 'um-mailchimp-dashboard', um_mailchimp_url . 'assets/js/um-mailchimp-dashboard' . $suffix . '.js', array( 'jquery', 'wp-util', 'um_admin_global' ), um_mailchimp_version, true );

		wp_localize_script( 'um-mailchimp-dashboard', 'um_mailchimp_data', array(
			'current_url'		 => UM()->permalinks()->get_current_url(),
			'internal_lists' => UM()->Mailchimp()->api()->get_wp_lists_array(),
			'role'					 => isset( $_SESSION[ '_um_mailchimp_selected_role' ] ) ? $_SESSION[ '_um_mailchimp_selected_role' ] : '',
			'roles'					 => UM()->roles()->get_roles(),
			'status'				 => isset( $_SESSION[ '_um_mailchimp_selected_status' ] ) ? $_SESSION[ '_um_mailchimp_selected_status' ] : '',
			'status_list'		 => array(
				'approved'										 => __( 'Approved', 'um-mailchimp' ),
				'awaiting_admin_review'				 => __( 'Awaiting Admin Review', 'um-mailchimp' ),
				'awaiting_email_confirmation'	 => __( 'Awaiting Email Confirmation', 'um-mailchimp' ),
				'inactive'										 => __( 'Inactive', 'um-mailchimp' ),
				'rejected'										 => __( 'Rejected', 'um-mailchimp' ),
			),
			'labels'				 => array(
				'sync_message'									 => __( 'Starting synchronization...', 'um-mailchimp' ),
				'scan_message'									 => __( 'Checking subscription status...', 'um-mailchimp' ),
				'start_bulk_subscribe_process'	 => __( 'Subscribe users... 0%', 'um-mailchimp' ),
				'start_bulk_unsubscribe_process' => __( 'Unsubscribe users... 0%', 'um-mailchimp' ),
				'sync_process'									 => __( 'Syncronization...', 'um-mailchimp' ),
				'processing'										 => __( 'Processing...', 'um-mailchimp' )
			)
		) );

		include_once um_mailchimp_path . 'includes/admin/templates/dashboard.php';
	}

	public function prepare_metabox() {

		add_action( 'load-' . UM()->Mailchimp()->admin()->pagehook, array( &$this, 'load_metabox' ) );
	}

}
