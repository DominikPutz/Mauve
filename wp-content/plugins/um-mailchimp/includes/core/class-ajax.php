<?php
namespace um_ext\um_mailchimp\core;

// Exit if accessed directly.
if ( !defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class for AJAX handlers
 *
 * @example UM()->classes['um_mailchimp_ajax']
 * @example UM()->Mailchimp()->ajax()
 */
class Ajax {

	/**
	 * Class constructor
	 */
	public function __construct() {

		add_action( 'wp_ajax_um_mailchimp_clear_log', array( $this, 'ajax_clear_log' ) );
		add_action( 'wp_ajax_um_mailchimp_get_merge_fields', array( $this, 'ajax_get_merge_fields' ) );

		add_action( 'wp_ajax_um_mailchimp_test_subscribe', array( $this, 'ajax_test_subscribe' ) );
		add_action( 'wp_ajax_um_mailchimp_test_update', array( $this, 'ajax_test_update' ) );
		add_action( 'wp_ajax_um_mailchimp_test_unsubscribe', array( $this, 'ajax_test_unsubscribe' ) );
		add_action( 'wp_ajax_um_mailchimp_test_delete', array( $this, 'ajax_test_delete' ) );
	}

	public function ajax_clear_log() {
		UM()->admin()->check_ajax_nonce();

		UM()->Mailchimp()->log()->clear();
	}

	public function ajax_get_merge_fields() {
		check_ajax_referer( 'um_mailchimp_get_merge_fields', 'nonce' );

		$list_id = filter_input( INPUT_POST, 'list_id' );
		if ( empty( $list_id ) ) {
			wp_send_json_error( 'Empty list ID' );
		}

		ob_start();
		include_once um_mailchimp_path . 'includes/admin/templates/merge.php';
		$content = ob_get_clean();

		wp_send_json_success( array(
			'list'					 => UM()->Mailchimp()->api()->mc_get_list( $list_id ),
			'merge_content'	 => $content
		) );
	}


	/**
	 *
	 */
	public function ajax_test_subscribe() {
		UM()->admin()->check_ajax_nonce();

		$data = ! empty( $_POST['test_data'] ) ? $_POST['test_data'] : array();
		if ( method_exists( UM(), 'clean_array' ) ) {
			$data = UM()->clean_array( $data );
		}

		if ( ! empty( $data['_um_test_email'] ) && is_email( $data['_um_test_email'] ) ) {
			$email = $data['_um_test_email'];
			unset( $data['_um_test_emaild'] );
		} else {
			wp_send_json_error( __( 'Please enter email', 'um-mailchimp' ) );
		}

		if ( ! empty( $data['list_id'] ) ) {
			$list_id = $data['list_id'];
			unset( $data['list_id'] );
		} else {
			wp_send_json_error( __( 'Please select list', 'um-mailchimp' ) );
		}

		$response = UM()->Mailchimp()->api()->call()->post( "lists/{$list_id}/members/", array(
			'email_address' => $email,
			'merge_fields'  => $response = UM()->Mailchimp()->api()->prepare_data( $data ),
			'status'        => apply_filters_ref_array( 'um_mailchimp_default_subscription_status', array(
				'subscribed',
				'subscribe',
				$list_id,
				$email
			) ),
		) );

		wp_send_json_success( array(
			'result'    => ! empty( $response['id'] ),
			'message'   => json_encode( $response )
		) );
	}


	/**
	 *
	 */
	public function ajax_test_update() {
		UM()->admin()->check_ajax_nonce();

		$data = ! empty( $_POST['test_data'] ) ? $_POST['test_data'] : array();
		if ( method_exists( UM(), 'clean_array' ) ) {
			$data = UM()->clean_array( $data );
		}

		if ( ! empty( $data['_um_test_email'] ) && is_email( $data['_um_test_email'] ) ) {
			$email = $data['_um_test_email'];
			unset( $data['_um_test_email'] );
		} else {
			wp_send_json_error( __( 'Please enter email', 'um-mailchimp' ) );
		}

		if ( ! empty( $data['list_id'] ) ) {
			$list_id = $data['list_id'];
			unset( $data['list_id'] );
		} else {
			wp_send_json_error( __( 'Please select list', 'um-mailchimp' ) );
		}

		$response = UM()->Mailchimp()->api()->call()->put( "lists/{$list_id}/members/" . md5( $email ), array(
			'email_address' => $email,
			'merge_fields'  => $response = UM()->Mailchimp()->api()->prepare_data( $data ),
			'status'        => apply_filters_ref_array( 'um_mailchimp_default_subscription_status', array(
				'subscribed',
				'update',
				$list_id,
				$email
			) ),
		) );

		wp_send_json_success( array(
			'result'    => ! empty( $response['id'] ),
			'message'   => json_encode( $response )
		) );
	}


	/**
	 *
	 */
	public function ajax_test_unsubscribe() {
		UM()->admin()->check_ajax_nonce();

		$data = ! empty( $_POST['test_data'] ) ? $_POST['test_data'] : array();
		if ( method_exists( UM(), 'clean_array' ) ) {
			$data = UM()->clean_array( $data );
		}

		if ( ! empty( $data['_um_test_email'] ) && is_email( $data['_um_test_email'] ) ) {
			$email = $data['_um_test_email'];
			unset( $data['_um_test_email'] );
		} else {
			wp_send_json_error( __( 'Please enter email', 'um-mailchimp' ) );
		}

		if ( ! empty( $data['list_id'] ) ) {
			$list_id = $data['list_id'];
			unset( $data['list_id'] );
		} else {
			wp_send_json_error( __( 'Please select list', 'um-mailchimp' ) );
		}

		$response = UM()->Mailchimp()->api()->call()->patch( "lists/{$list_id}/members/" . md5( $email ), array(
			'email_address' => $email,
			'status'        => 'unsubscribed',
		) );

		wp_send_json_success( array(
			'result'    => ! empty( $response['id'] ),
			'message'   => json_encode( $response )
		) );
	}


	/**
	 *
	 */
	public function ajax_test_delete() {
		UM()->admin()->check_ajax_nonce();

		$data = ! empty( $_POST['test_data'] ) ? $_POST['test_data'] : array();
		if ( method_exists( UM(), 'clean_array' ) ) {
			$data = UM()->clean_array( $data );
		}

		if ( ! empty( $data['_um_test_email'] ) && is_email( $data['_um_test_email'] ) ) {
			$email = $data['_um_test_email'];
			unset( $data['_um_test_email'] );
		} else {
			wp_send_json_error( __( 'Please enter email', 'um-mailchimp' ) );
		}

		if ( ! empty( $data['list_id'] ) ) {
			$list_id = $data['list_id'];
			unset( $data['list_id'] );
		} else {
			wp_send_json_error( __( 'Please select list', 'um-mailchimp' ) );
		}

		$response = UM()->Mailchimp()->api()->call()->delete( "lists/{$list_id}/members/" . md5( $email ) );

		wp_send_json_success( array(
			'result'    => empty( $response ),
			'message'   => empty( $response ) ? '' : json_encode( $response )
		) );
	}

}