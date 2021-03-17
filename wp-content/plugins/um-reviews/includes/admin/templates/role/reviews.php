<div class="um-admin-metabox">

	<?php $role = $object['data'];

	UM()->admin_forms( array(
		'class'		=> 'um-role-reviews um-half-column',
		'prefix_id'	=> 'role',
		'fields' => array(
			array(
				'id'		    => '_um_can_have_reviews_tab',
				'type'		    => 'checkbox',
				'label'		    => __( 'Can have user reviews tab?', 'um-reviews' ),
				'tooltip'		=> __( 'If this is turned off user reviews will be disabled for this role.', 'um-reviews' ),
				'value'		    => isset( $role['_um_can_have_reviews_tab'] ) ? $role['_um_can_have_reviews_tab'] : 1,
			),
			array(
				'id'		    => '_um_can_review',
				'type'		    => 'checkbox',
				'label'		    => __( 'Can review other members?', 'um-reviews' ),
				'tooltip'		=> __( 'Decide If this role can review other members', 'um-reviews' ),
				'value'		    => isset( $role['_um_can_review'] ) ? $role['_um_can_review'] : 1,
			),
			array(
				'id'		    => '_um_can_review_roles',
				'type'		    => 'select',
				'multi'		    => true,
				'label'		    => __( 'Can review these roles only','um-reviews' ),
				'tooltip'		=> __( 'Which roles that role can review, choose none to allow role to review all member roles', 'um-reviews' ),
				'value'		    => ! empty( $role['_um_can_review_roles'] ) ? $role['_um_can_review_roles'] : array(),
				'options'		=>  UM()->roles()->get_roles(),
				'conditional'	=> array( '_um_can_review', '=', '1' )
			),
			array(
				'id'		    => '_um_can_publish_review',
				'type'		    => 'checkbox',
				'label'		    => __( 'Automatically publish reviews from this role?', 'um-reviews' ),
				'tooltip'		=> __( 'If turned off, reviews from this role will be pending admin review.', 'um-reviews' ),
				'value'		    => isset( $role['_um_can_publish_review'] ) ? $role['_um_can_publish_review'] : 1,
			),
			array(
				'id'		    => '_um_can_remove_own_review',
				'type'		    => 'checkbox',
				'label'		    => __( 'Can edit/remove their own reviews?', 'um-reviews' ),
				'tooltip'		=> __( 'If this is turned on user can edit and remove own reviews for other members.', 'um-reviews' ),
				'value'		    => isset( $role['_um_can_remove_own_review'] ) ? $role['_um_can_remove_own_review'] : 1,
			),
			array(
				'id'		    => '_um_can_remove_review',
				'type'		    => 'checkbox',
				'label'		    => __( 'Can edit/remove other reviews?', 'um-reviews' ),
				'tooltip'		=> __( 'If this is turned on user can edit and remove any reviews.', 'um-reviews' ),
				'value'		    => isset( $role['_um_can_remove_review'] ) ? $role['_um_can_remove_review'] : 0,
			),
		)
	) )->render_form(); ?>

	<div class="um-admin-clear"></div>
</div>