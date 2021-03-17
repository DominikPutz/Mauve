<?php if ( ! defined( 'ABSPATH' ) ) exit; ?>

<div class="um-admin-metabox">

	<?php $role = $object['data'];

	UM()->admin_forms( array(
		'class'		=> 'um-role-photos um-half-column',
		'prefix_id'	=> 'role',
		'fields' => array(
			array(
				'id'        => '_um_enable_user_photos',
				'type'      => 'checkbox',
				'default'   => 0,
				'label'     => __( 'Enable photos feature?', 'um-user-photos' ),
				'tooltip'   => __( 'Can this role have user photos feature?', 'um-user-photos' ),
				'value'     => isset( $role['_um_enable_user_photos'] ) ? $role['_um_enable_user_photos'] : 0,
			)
		)
	) )->render_form(); ?>

	<div class="um-admin-clear"></div>
</div>
