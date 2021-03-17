<?php if ( ! defined( 'ABSPATH' ) ) exit; ?>

<div class="um-admin-metabox">

	<?php $role = $object['data'];

	UM()->admin_forms( array(
		'class'		=> 'um-role-bookmark um-half-column',
		'prefix_id'	=> 'role',
		'fields' => array(
			array(
				'id'        => '_um_enable_bookmark',
				'type'      => 'checkbox',
				'default'   => 1,
				'label'     => __( 'Enable bookmark feature?', 'um-user-bookmarks' ),
				'tooltip'   => __( 'Can this role have bookmark feature?', 'um-user-bookmarks' ),
				'value'     => isset( $role['_um_enable_bookmark'] ) ? $role['_um_enable_bookmark'] : 1,
			)
		)
	) )->render_form(); ?>

	<div class="um-admin-clear"></div>
</div>