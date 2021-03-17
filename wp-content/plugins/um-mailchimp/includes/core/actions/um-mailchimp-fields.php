<?php if ( ! defined( 'ABSPATH' ) ) exit;


/**
 * Field option "Select a audience"
 *
 * @param string $val
 */
function um_admin_field_edit_hook_mailchimp_list( $val ) {

	$wp_lists = UM()->Mailchimp()->api()->get_wp_lists();
	if ( ! $wp_lists ) {
		return;
	}
	?>

	<p>
		<label for="_mailchimp_list"><?php _e( 'Select a audience', 'um-mailchimp' ); ?> <?php UM()->tooltip( __( 'You can set up audiences or integrations in Ultimate Member > MailChimp', 'um-mailchimp' ) ); ?></label>
		<select name="_mailchimp_list" id="_mailchimp_list" style="width: 100%">

			<?php foreach ( $wp_lists as $wp_list ) { ?>
				<option value="<?php echo esc_attr( $wp_list->ID ); ?>" <?php selected( $wp_list->ID, $val ); ?>><?php echo $wp_list->post_title; ?></option>
			<?php } ?>

		</select>
	</p>

	<?php
}
add_action( 'um_admin_field_edit_hook_mailchimp_list', 'um_admin_field_edit_hook_mailchimp_list' );


/**
 * Field option "Automatically add users to this audience"
 *
 * @param string $val
 */
function um_admin_field_edit_hook_mailchimp_auto_subscribe( $val ) {
	?>
	<p>
		<label for="_mailchimp_auto_subscribe">
			<?php
			_e( 'Automatically add users to this audience', 'um-mailchimp' );
			UM()->tooltip( __( 'If turned on users will be subscribed to audience on form submit. When turned on this audience will appear selected.', 'um-mailchimp' ) );
			?>
		</label>
		<input type="checkbox" name="_mailchimp_auto_subscribe" id="_mailchimp_auto_subscribe" value="1" <?php checked( $val, '1' ) ?> />
	</p>
	<?php
}
add_action( 'um_admin_field_edit_hook_mailchimp_auto_subscribe', 'um_admin_field_edit_hook_mailchimp_auto_subscribe' );


/**
 * Call subscription process on registration when status set to 'approved'
 *
 * @hook 'um_after_user_status_is_changed'
 * @since 2.2.0
 *
 * @param string $status
 * @return boolean
 */
function um_mc_after_user_status_is_changed( $status ) {

	if( $status != 'approved' ) {
		return;
	}

	$lists_subscribed = array();

	$user_id = um_user( 'ID' );
	$date = date( 'Y-m-d', strtotime( um_user( 'user_registered' ) ) );

	/**
	 * Subscribe to the audience checked in the registration form
	 */
	$user_lists = get_user_meta( $user_id, 'um-mailchimp', true );
	if( is_array( $user_lists ) ) {
		foreach( $user_lists as $list_id => $nonce ) {
			if( empty( $nonce ) || !wp_verify_nonce( $nonce, "um-mailchimp-nonce:$list_id:$date" ) ) {
				continue;
			}

			$wp_list = null;
			if( is_numeric( $list_id ) ) {
				$wp_list = get_post( $list_id );
				$list_id = $wp_list->_um_list;
			}

			if( UM()->Mailchimp()->api()->mc_subscribe_member( $list_id, $user_id, $wp_list ) ) {
				$lists_subscribed[] = $list_id;
			}
		}
		if( $lists_subscribed ) {
			delete_user_meta( $user_id, 'um-mailchimp' );
		}
	}

	/**
	 * Subscribe to the audience that has option "Automatically add new users to this audience"
	 */
	$wp_lists = UM()->Mailchimp()->api()->get_wp_lists();
	foreach( $wp_lists as $wp_list ) {
		if( $wp_list->_um_status && $wp_list->_um_reg_status && !in_array( $wp_list->list_id, $lists_subscribed ) ) {
			if( UM()->Mailchimp()->api()->mc_subscribe_member( $wp_list->list_id, $user_id, $wp_list ) ) {
				$lists_subscribed[] = $wp_list->list_id;
			}
		}
	}

	return $lists_subscribed;
}
add_action( 'um_after_user_status_is_changed', 'um_mc_after_user_status_is_changed', 20 );



/**
 * Hidden fields on the Registration page
 *
 * @hook 'um_after_register_fields'
 * @deprecated since version 2.2.0
 *
 * @param type $val
 * @return boolean
 */
function um_mailchimp_after_register_fields( $val ) {

	$wp_lists = UM()->Mailchimp()->api()->get_wp_lists();
	if ( ! $wp_lists ) {
		return;
	}

	foreach ( $wp_lists as $wp_list ) {
		if ( $wp_list->_um_status && $wp_list->_um_reg_status ) {
			echo '<input type="hidden" name="um-mailchimp[' . esc_attr( $wp_list->ID ) . ']" value="1" />';
		}
	}
}