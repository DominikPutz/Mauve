<?php if ( ! defined( 'ABSPATH' ) ) exit; ?>

<div class="um-admin-metabox">
	<?php $role = $object['data'];

	UM()->admin_forms( array(
		'class'     => 'um-role-mycred um-half-column',
		'prefix_id' => 'role',
		'fields'    => array(
			array(
				'id'    => '_um_can_transfer_mycred',
				'type'  => 'checkbox',
				'label' => __( 'Can transfer points to other members?', 'um-mycred' ),
				'value' => !empty( $role['_um_can_transfer_mycred'] ) ? $role['_um_can_transfer_mycred'] : 0,
			),
			array(
				'id'    => '_um_cannot_receive_mycred',
				'type'  => 'checkbox',
				'label' => __( 'Can not receive points from other members?', 'um-mycred' ),
				'value' => !empty( $role['_um_cannot_receive_mycred'] ) ? $role['_um_cannot_receive_mycred'] : 0,
			),

		)
	) )->render_form(); ?>
</div>