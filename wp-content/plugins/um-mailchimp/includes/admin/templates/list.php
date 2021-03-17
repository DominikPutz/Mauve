<div class="um-admin-metabox">

	<?php
	$fields = array();

	$lists = UM()->Mailchimp()->api()->get_lists();

	$current_roles = array();
	foreach ( UM()->roles()->get_roles() as $key => $value ) {
		if ( UM()->query()->get_meta_value( '_um_roles', $key ) ) {
			$current_roles[] = $key;
		}
	}

	if ( isset( $_REQUEST['action'] ) && sanitize_key( $_REQUEST['action'] ) == 'edit' ) {

		$list_id = UM()->query()->get_meta_value( '_um_list' );

		$fields[] = array(
			'id'		 => 'mailing_list_id',
			'type'	 => 'info_text',
			'label'	 => __( 'Connected to Mailing audience ID', 'um-mailchimp' ),
			'value'	 => $list_id,
		);
	}
	else {
		$fields[] = array(
			'id'			 => '_um_list',
			'type'		 => 'select',
			'size'		 => 'medium',
			'label'		 => __( 'Choose a audience', 'um-mailchimp' ),
			'tooltip'	 => __( 'Choose a audience from your MailChimp account', 'um-mailchimp' ),
			'value'		 => '',
			'options'	 => $lists,
		);
	}

	$fields = array_merge( $fields, array(
		array(
			'id'			 => '_um_status',
			'type'		 => 'checkbox',
			'label'		 => __( 'Enable this MailChimp audience', 'um-mailchimp' ),
			'tooltip'	 => __( 'Turn on or off this audience globally. If enabled the audience will be available in user account page.', 'um-mailchimp' ),
			'value'		 => UM()->query()->get_meta_value( '_um_status', null, 1 ),
		),
		array(
			'id'			 => '_um_double_optin',
			'type'		 => 'select',
			'size'		 => 'medium',
			'label'		 => __( 'Enable double opt-in', 'um-mailchimp' ),
			'tooltip'	 => __( 'Send contacts an opt-in confirmation email when they subscribe to your audience.', 'um-mailchimp' ),
			'value'		 => UM()->query()->get_meta_value( '_um_double_optin' ),
			'options'	 => array(
				''	 => __( 'Default', 'um-mailchimp' ),
				'1'	 => __( 'Yes', 'um-mailchimp' ),
				'0'	 => __( 'No', 'um-mailchimp' ),
			),
		),
		array(
			'id'			 => '_um_desc',
			'type'		 => 'text',
			'label'		 => __( 'Audience Description in Account Page', 'um-mailchimp' ),
			'tooltip'	 => __( 'This text will be displayed in Account > Notifications to encourage user to sign or know what this audience is about', 'um-mailchimp' ),
			'value'		 => UM()->query()->get_meta_value( '_um_desc', null, 'na' ),
		),
		array(
			'id'			 => '_um_desc_reg',
			'type'		 => 'text',
			'label'		 => __( 'Audience Description in Registration', 'um-mailchimp' ),
			'tooltip'	 => __( 'This text will be displayed in register form if you enable this mailing audience to be available during signup', 'um-mailchimp' ),
			'value'		 => UM()->query()->get_meta_value( '_um_desc_reg', null, 'na' ),
		),
		array(
			'id'			 => '_um_roles',
			'multi'		 => true,
			'type'		 => 'select',
			'label'		 => __( 'Which roles can subscribe to this audience', 'um-mailchimp' ),
			'tooltip'	 => __( 'Select which roles can subscribe to this audience. Users who cannot subscribe to this audience will not see this audience on their account page.', 'um-mailchimp' ),
			'value'		 => !empty( $current_roles ) ? $current_roles : array(),
			'options'	 => UM()->roles()->get_roles(),
		),
		array(
			'id'			 => '_um_reg_status',
			'type'		 => 'checkbox',
			'label'		 => __( 'Automatically add new users to this audience', 'um-mailchimp' ),
			'tooltip'	 => __( 'If turned on users will automatically be subscribed to this when they register. When turned on this audience will not show on register form even if you add MailChimp field to register form.', 'um-mailchimp' ),
			'value'		 => UM()->query()->get_meta_value( '_um_reg_status', null, 0 ),
		),
		) );


	// groups
	$mc_groups = array();
	if ( !empty( $list_id ) ) {
		$mc_groups = UM()->Mailchimp()->api()->mc_get_interest_categories_array( $list_id );
	}
	foreach ( $mc_groups as $id => $name ) {

		$mc_interests = UM()->Mailchimp()->api()->mc_get_interests_array( $list_id, $id );
		$wp_interests = (array) get_post_meta( get_the_ID(), "_um_reg_groups_$id", true );
		$interests_value = array_intersect( $wp_interests, array_keys( $mc_interests ) );

		$fields[] = array(
			'id'					 => "_um_reg_groups_$id",
			'type'				 => 'select',
			'multi'				 => true,
			'label'				 => sprintf( __( 'Default group "%s" interests', 'um-mailchimp' ), $name ),
			'tooltip'			 => __( 'Add this group interests to the member on registration', 'um-mailchimp' ),
			'options'			 => $mc_interests,
			'value'				 => $interests_value,
			//'conditional'	 => array( '_um_reg_status', '=', '1' )
		);
	}


	// tags
	$mc_tags = array();
	if ( ! empty( $list_id ) ) {
		$mc_tags = UM()->Mailchimp()->api()->mc_get_tags_array( $list_id );
	}
	$wp_tags = (array) get_post_meta( get_the_ID(), '_um_reg_tags', true );
	$tags_value = array_intersect( $wp_tags, array_keys( $mc_tags ) );

	$fields[] = array(
		'id'					 => '_um_reg_tags',
		'type'				 => 'select',
		'multi'				 => true,
		'label'				 => __( 'Default tags', 'um-mailchimp' ),
		'tooltip'			 => __( 'Add this tags to the member on registration.', 'ultimate-member' ),
		'options'			 => $mc_tags,
		'value'				 => $tags_value,
		//'conditional'	 => array( '_um_reg_status', '=', '1' )
	);


	UM()->admin_forms( array(
		'class'			 => 'um-form-mailchimp um-half-column',
		'prefix_id'	 => 'mailchimp',
		'fields'		 => $fields
	) )->render_form();
	?>

	<div class="um-admin-clear"></div>
</div>

<script type="text/javascript">
	jQuery(document).on('change', '#mailchimp__um_list', function (e) {
		jQuery('#um-admin-mailchimp-merge .inside').html('');
		wp.ajax.post('um_mailchimp_get_merge_fields', {
			list_id: jQuery(e.target).val(),
			nonce: '<?php echo wp_create_nonce( 'um_mailchimp_get_merge_fields' ) ?>'
		}).done(function (response) {
			var responseType = typeof (response);
			switch (responseType) {
				case 'object':
					jQuery('#mailchimp__um_desc, #mailchimp__um_desc_reg').val(response.list.name);
					jQuery('#um-admin-mailchimp-merge .inside').html(response.merge_content);
					break;
				case 'string':
					jQuery('#um-admin-mailchimp-merge .inside').html(response);
					break;
				default:
					console.error('UM: Wrong "um_mailchimp_get_merge_fields" response', response);
			}
		}).fail(function (response) {
			alert(response);
		});
	});
</script>