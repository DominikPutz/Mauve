<?php
add_action( 'um_render_field_type_mailchimp_action_buttons', 'um_render_field_type_mailchimp_action_buttons', 10, 2 );

function um_render_field_type_mailchimp_action_buttons( $data, $form_data ) {
	return '<input type="button" id="um_mailchimp_test_subscribe" value="' . __( 'Test subscribe', 'um-mailchimp' ) . '" />
        <input type="button" id="um_mailchimp_test_update" value="' . __( 'Test update', 'um-mailchimp' ) . '" />
        <input type="button" id="um_mailchimp_test_unsubscribe" value="' . __( 'Test unsubscribe', 'um-mailchimp' ) . '" />
        <input type="button" id="um_mailchimp_test_delete" value="' . __( 'Test delete', 'um-mailchimp' ) . '" />
        <span id="um_test_message" style="word-break: break-all;"></span>';
}
?>

<div class="um-admin-metabox">
	<?php
	$fields = array(
			array(
					'id'		 => '_um_test_email',
					'type'	 => 'text',
					'label'	 => __( 'Email', 'um-mailchimp' ),
					'value'	 => '',
			)
	);

	if( empty( $list_id ) && !empty( $post_id ) ) {
		$list_id = get_post_meta( $post_id, '_um_list', true );
		if( empty( $list_id ) ) {
			$lists = UM()->Mailchimp()->api()->get_lists();
			if( count( $lists ) ) {
				list( $list_id ) = array_keys( $lists );
			}
		}
		$merged = get_post_meta( $post_id, '_um_merge', true );
	}

	if( !empty( $list_id ) ) {
		$fields[] = array(
				'id'		 => 'list_id',
				'name'	 => 'list_id',
				'type'	 => 'hidden',
				'value'	 => $list_id,
		);
	}

	foreach( UM()->Mailchimp()->api()->get_vars( $list_id ) as $arr ) {
		$fields[] = array(
				'id'			 => $arr[ 'tag' ],
				'type'		 => 'text',
				'required' => isset( $arr[ 'required' ] ) ? $arr[ 'required' ] : false,
				'label'		 => $arr[ 'name' ],
				'value'		 => ''
		);
	}

	$fields[] = array(
			'id'		 => 'private_content_generate',
			'type'	 => 'mailchimp_action_buttons',
			'value'	 => __( 'S', 'um-mailchimp' ),
			'size'	 => 'small'
	);

	UM()->admin_forms()->set_data( array(
			'class'			 => 'um-form-mailchimp-test um-long-field',
			'prefix_id'	 => 'test_data',
			'fields'		 => $fields
	) )->render_form();
	?>

	<div class="um-admin-clear"></div>
</div>

<script type="text/javascript">
	jQuery(document).on('click', '#um_mailchimp_test_subscribe', function () {
		var data = {},
						serialize = jQuery('#um-admin-mailchimp-test-connection').find(':input').serializeArray();

		jQuery('#um_test_message').html('');

		for (k in serialize) {
			data[ serialize[ k ].name ] = serialize[ k ].value;
		}

		if (jQuery('#mailchimp__um_list').length) {
			data['test_data[list_id]'] = jQuery('#mailchimp__um_list').val();
		}

		data.nonce = um_admin_scripts.nonce;

		wp.ajax.post('um_mailchimp_test_subscribe', data).done(function (response) {
			jQuery('#um_test_message').html('<span style="color: ' + (response.result ? 'green' : 'red') + ';">' + response.message + '</span>');
		}).fail(function (response) {
			alert(response);
		});
	});

	jQuery(document).on('click', '#um_mailchimp_test_update', function () {
		var data = {},
						serialize = jQuery('#um-admin-mailchimp-test-connection').find(':input').serializeArray();

		jQuery('#um_test_message').html('');

		for (k in serialize) {
			data[ serialize[ k ].name ] = serialize[ k ].value;
		}

		if (jQuery('#mailchimp__um_list').length) {
			data['test_data[list_id]'] = jQuery('#mailchimp__um_list').val();
		}

		data.nonce = um_admin_scripts.nonce;

		wp.ajax.post('um_mailchimp_test_update', data).done(function (response) {
			jQuery('#um_test_message').html('<span style="color: ' + (response.result ? 'green' : 'red') + ';">' + response.message + '</span>');
		}).fail(function (response) {
			alert(response);
		});
	});

	jQuery(document).on('click', '#um_mailchimp_test_unsubscribe', function () {
		var data = {},
						serialize = jQuery('#um-admin-mailchimp-test-connection').find(':input').serializeArray();

		jQuery('#um_test_message').html('');

		for (k in serialize) {
			data[ serialize[ k ].name ] = serialize[ k ].value;
		}

		if (jQuery('#mailchimp__um_list').length) {
			data['test_data[list_id]'] = jQuery('#mailchimp__um_list').val();
		}

		data.nonce = um_admin_scripts.nonce;

		wp.ajax.post('um_mailchimp_test_unsubscribe', data).done(function (response) {
			jQuery('#um_test_message').html('<span style="color: ' + (response.result ? 'green' : 'red') + ';">' + response.message + '</span>');
		}).fail(function (response) {
			alert(response);
		});
	});

	jQuery(document).on('click', '#um_mailchimp_test_delete', function () {
		var data = {},
						serialize = jQuery('#um-admin-mailchimp-test-connection').find(':input').serializeArray();

		jQuery('#um_test_message').html('');

		for (k in serialize) {
			data[ serialize[ k ].name ] = serialize[ k ].value;
		}

		if (jQuery('#mailchimp__um_list').length) {
			data['test_data[list_id]'] = jQuery('#mailchimp__um_list').val();
		}

		data.nonce = um_admin_scripts.nonce;

		wp.ajax.post('um_mailchimp_test_delete', data).done(function (response) {
			jQuery('#um_test_message').html('<span style="color: ' + (response.result ? 'green' : 'red') + ';">' + (response.result ? 'Success' : response.message) + '</span>');
		}).fail(function (response) {
			alert(response);
		});
	});
</script>