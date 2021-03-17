<?php if ( ! defined( 'ABSPATH' ) ) exit;

global $wpdb;

$query = new WP_Query(array(
	'post_type'   => 'um_form',
	'post_status' => 'publish',
	'meta_query'  => array(
		array(
			'key'   => '_um_mode',
			'value' => 'register'
		)
	),
	'fields' => 'ids'
));

$registration_form_ids = $query->get_posts();

$internal_lists = array_keys( UM()->Mailchimp()->api()->get_lists( false ) );

foreach ( $registration_form_ids as $form_id ) {
	$fields = get_post_meta( $form_id, '_um_custom_fields', true );
	foreach ( $fields as $field_key => $field ) {
		if ( $field['type'] != 'mailchimp' ) {
			continue;
		}

		foreach ( $internal_lists as $list_id ) {
			if ( $field['mailchimp_list'] == $list_id ) {
				$list_data = UM()->Mailchimp()->api()->fetch_list( $list_id );
				if ( $list_data['auto_register'] == '1' ) {
					$fields[ $field_key ]['mailchimp_auto_subscribe'] = '1';
				}
			}
			delete_post_meta( $list_id, '_um_reg_status' );
		}
	}

	update_post_meta( $form_id, '_um_custom_fields', $fields );
}