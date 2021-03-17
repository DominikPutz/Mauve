<div class="um-admin-metabox">
	<?php $role = $object['data'];

	UM()->admin_forms( array(
		'class'     => 'um-role-social-activity um-half-column',
		'prefix_id' => 'role',
		'fields'    => array(
			array(
				'id'    => '_um_activity_wall_off',
				'type'  => 'checkbox',
				'label' => __( 'Turn off social wall for this user?', 'um-activity' ),
				'value' => ! empty( $role['_um_activity_wall_off'] ) ? $role['_um_activity_wall_off'] : 0,
			),
			array(
				'id'            => '_um_activity_posts_off',
				'type'          => 'checkbox',
				'label'         => __( 'Do not allow this role to write posts?', 'um-activity' ),
				'value'         => ! empty( $role['_um_activity_posts_off'] ) ? $role['_um_activity_posts_off'] : 0,
				'conditional'   => array( '_um_activity_wall_off', '=', '0' )
			),
			array(
				'id'            => '_um_activity_photo_off',
				'type'          => 'checkbox',
				'label'         => __( 'Turn off uploading photos?', 'um-activity' ),
				'value'         => ! empty( $role['_um_activity_photo_off'] ) ? $role['_um_activity_photo_off'] : 0,
				'conditional'   => array( '_um_activity_posts_off', '=', '0' )
			),
			array(
				'id'            => '_um_activity_comments_off',
				'type'          => 'checkbox',
				'label'         => __( 'Do not allow this role to write comments?','um-activity'),
				'value'         => ! empty( $role['_um_activity_comments_off'] ) ? $role['_um_activity_comments_off'] : 0,
				'conditional'   => array( '_um_activity_wall_off', '=', '0' )
			),
		)
	) )->render_form(); ?>

	<div class="um-admin-clear"></div>
</div>