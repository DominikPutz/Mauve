<?php

if ( !defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Add a notice to the Account form
 *
 * @param type $args
 */
function um_mc_account_notice( $args ) {
	if ( isset( $args[ 'mode' ] ) && $args[ 'mode' ] === 'account' ) {

		$user_id = UM()->Mailchimp()->api()->get_user_id();
		$errors = get_transient( "um_mc_api:errors_$user_id" );

		if ( !$errors ) {
			return;
		}

		foreach ( $errors as $key => $error ) {
			$error_text = "<!-- Error: $key -->";
			if ( !empty( $error[ 'title' ] ) ) {
				$error_text .= '<strong>' . $error[ 'title' ] . '</strong>';
			}
			if ( !empty( $error[ 'detail' ] ) ) {
				$error_text .= '<br><small>' . $error[ 'detail' ] . '</small>';
			}
			echo '<p class="um-notice err"><i class="um-icon-ios-close-empty" onclick="jQuery(this).parent().fadeOut();"></i>' . $error_text . '</p>';
		}

		delete_transient( "um_mc_api:errors_$user_id" );
		remove_action( 'um_before_form', 'um_add_update_notice', 500 );
	}
}

add_action( 'um_before_form', 'um_mc_account_notice', 200 );


/**
 * Subscribe or unsubscribe to audience on account update
 *
 * @param $user_id
 * @param array $changes
 *
 * @return array|bool
 */
function um_mc_account_update( $user_id, $changes = array() ) {

	if ( empty( $_POST['um-mailchimp'] ) || ! is_array( $_POST['um-mailchimp'] ) ) {
		return false;
	}
	if ( ! UM()->user()->is_approved( $user_id ) ) {
		return false;
	}

	$my_lists = UM()->Mailchimp()->api()->get_lists_my( $user_id );

	$lists_subscribed = array();
	$lists_unsubscribed = array();

	$mailchimp_array = $_POST['um-mailchimp'];
	if ( method_exists( UM(), 'clean_array' ) ) {
		$mailchimp_array = UM()->clean_array( $mailchimp_array );
	}
	foreach ( $mailchimp_array as $list_id => $data ) {
		if ( empty( $data['wp_list_id'] ) ) {
			continue;
		}

		if ( ! empty( $data[ 'enabled' ] ) ) {
			$response = UM()->Mailchimp()->api()->mc_update_member( $list_id, $user_id, $data );
			$lists_subscribed[] = $list_id;
		} elseif ( ! empty( $my_lists[ $list_id ] ) ) {

			if ( UM()->options()->get( 'mailchimp_unsubscribe_delete' ) ) {
				$response = UM()->Mailchimp()->api()->mc_delete_member( $list_id, $user_id );
			} else {
				$response = UM()->Mailchimp()->api()->mc_unsubscribe_member( $list_id, $user_id );
			}

			$lists_unsubscribed[] = $list_id;
		}
	}

	$updated_users = array(
		'subscribed'    => $lists_subscribed,
		'unsubscribed'  => $lists_unsubscribed
	);

	return $updated_users;
}

add_action( 'um_after_user_account_updated', 'um_mc_account_update', 20, 2 );

/**
 * Call update subscriber information on profile update
 *
 * @global WP_User $um_old_user_data
 * @param int $user_id
 * @param WP_User $old_user_data
 * @return array
 */
function um_mc_after_profile_update( $user_id, $old_user_data = null ) {
	global $um_mc_old_user_data, $um_mc_old_email;

	if ( is_wp_error( $user_id ) ) {
		return;
	}
	if ( !UM()->user()->is_approved( $user_id ) ) {
		return false;
	}

	if ( $old_user_data && is_a( $old_user_data, 'WP_User' ) ) {
		$um_mc_old_user_data = $old_user_data;
		$um_mc_old_email = $old_user_data->user_email;
	}

	$my_lists = UM()->Mailchimp()->api()->get_lists_my( $user_id );
	$lists_updated = array();

	foreach ( $my_lists as $list_id => $value ) {
		if ( !$value ) {
			continue;
		}

		$result = UM()->Mailchimp()->api()->mc_update_member( $list_id, $user_id );
		if ( $result ) {
			$lists_updated[] = $list_id;
		}
	}

	return $lists_updated;
}

add_action( 'profile_update', 'um_mc_after_profile_update', 10, 2 );

/**
 * Subscribe or unsubscribe to audience if user role changed
 *
 * @staticvar array $updated_users
 * @param int $user_id
 * @param null|string $role
 * @return array
 */
function um_mc_after_role_is_updated( $user_id, $role = null ) {
	static $updated_users = array();

	if ( is_wp_error( $user_id ) ) {
		return false;
	}
	if ( !UM()->user()->is_approved( $user_id ) ) {
		return false;
	}
	if ( !empty( $updated_users[ $user_id ] ) ) {
		return true;
	}

	if ( empty( $role ) ) {
		$role = $profilerole = UM()->user()->profile[ 'role' ];
	}

	$my_lists = UM()->Mailchimp()->api()->get_lists_my( $user_id );
	$wp_lists = UM()->Mailchimp()->api()->get_wp_lists( $role );
	$wp_list_ids = array_map( function( $wp_list ) {
		return $wp_list->list_id;
	}, $wp_lists );

	$lists_unsubscribed = array();
	foreach ( $my_lists as $list_id => $subscribed ) {
		if ( $subscribed && !in_array( $list_id, $wp_list_ids ) ) {
			if ( UM()->Mailchimp()->api()->mc_unsubscribe_member( $list_id, $user_id ) ) {
				$lists_unsubscribed[] = $list_id;
			}
		}
	}

	$lists_subscribed = array();
	foreach ( $wp_lists as $wp_list ) {
		if ( empty( $my_lists[ $list_id ] ) ) {
			if ( !$wp_list->_um_reg_status ) {
				continue;
			}
			if ( UM()->Mailchimp()->api()->mc_subscribe_member( $wp_list->list_id, $user_id, $wp_list ) ) {
				$lists_subscribed[] = $wp_list->list_id;
			}
		}
	}

	$updated_users[ $user_id ] = array(
		'subscribed'	 => $lists_subscribed,
		'unsubscribed' => $lists_unsubscribed
	);

	return $updated_users[ $user_id ];
}

add_action( 'um_after_user_role_is_updated', 'um_mc_after_role_is_updated', 20, 2 );

/**
 * Subscribe or unsubscribe to audience if user role changed by admin
 *
 * @param int $user_id
 * @param null|string $role
 * @return array
 */
function um_mc_after_set_user_role( $user_id, $role = null ) {

	if ( !is_admin() || defined( 'DOING_AJAX' ) ) {
		return;
	}

	return um_mc_after_role_is_updated( $user_id, $role );
}

add_action( 'set_user_role', 'um_mc_after_set_user_role', 20, 2 );

/**
 * Store old email to determine email changed
 *
 * @global string $old_email
 * @param array $to_update
 */
function um_mc_pre_updating_profile( $to_update = null ) {
	global $um_mc_old_email;
	$um_mc_old_email = um_user( 'user_email' );
}

add_action( 'um_user_pre_updating_profile', 'um_mc_pre_updating_profile', 20 );

/**
 * This action will be executed when someone registers user from wp-admin area.
 * It subscribes created user to all audiences with option 'auto_register'
 *
 * @param $user_id
 */
function um_mc_user_register( $user_id ) {

	if ( !is_admin() || defined( 'DOING_AJAX' ) ) {
		return;
	}
	if ( is_wp_error( $user_id ) ) {
		return false;
	}
	if ( !UM()->user()->is_approved( $user_id ) ) {
		return false;
	}

	$wp_lists = UM()->Mailchimp()->api()->get_wp_lists( true );

	$lists_updated = array();
	foreach ( $wp_lists as $wp_list ) {
		if ( UM()->Mailchimp()->api()->mc_subscribe_member( $wp_list->list_id, $user_id, $wp_list ) ) {
			$lists_updated[] = $wp_list->list_id;
		}
	}

	return $lists_updated;
}

add_action( 'user_register', 'um_mc_user_register' );

/**
 * Delete user from Mailchimp audiences on delete process
 *
 * @staticvar array $deleted_users
 * @param int|string|WP_User $user
 * @return boolean
 */
function um_mc_unsubscribe_user( $user ) {
	static $deleted_users = array();

	$user_id = UM()->Mailchimp()->api()->get_user_id( $user );
	if ( !empty( $deleted_users[ $user_id ] ) ) {
		return true;
	}

	$my_lists = UM()->Mailchimp()->api()->get_lists_my( $user_id );

	foreach ( $my_lists as $list_id => $subscribed ) {
		UM()->Mailchimp()->api()->mc_delete_member( $list_id, $user );
	}

	$deleted_users[ $user_id ] = true;
	return true;
}

add_action( 'delete_user', 'um_mc_unsubscribe_user' );
add_action( 'um_delete_user', 'um_mc_unsubscribe_user' );
