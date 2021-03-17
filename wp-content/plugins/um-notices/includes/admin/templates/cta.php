<?php if ( ! defined( 'ABSPATH' ) ) exit; ?>

<div class="um-admin-metabox">

	<?php UM()->admin_forms( array(
		'class'		=> 'um-form-notice-cta um-half-column',
		'prefix_id'	=> 'notice',
		'fields' => array(
			array(
				'id'		    => '_um_cta',
				'type'		    => 'checkbox',
				'label'		    => __( 'Enable Call to Action button', 'um-notices' ),
				'value'		    => UM()->query()->get_meta_value( '_um_cta', null, 0 ),
			),
			array(
				'id'		    => '_um_cta_text',
				'type'		    => 'text',
				'label'		    => __( 'Button Text', 'um-notices' ),
				'value'		    => UM()->query()->get_meta_value( '_um_cta_text', null, 'na' ),
				'conditional'   => array( '_um_cta', '=', 1 )
			),
			array(
				'id'		    => '_um_cta_url',
				'type'		    => 'text',
				'label'		    => __( 'Button URL', 'um-notices' ),
				'value'		    => UM()->query()->get_meta_value( '_um_cta_url', null, 'na' ),
				'placeholder'	=> 'http://',
				'conditional'   => array( '_um_cta', '=', 1 )
			),
			array(
				'id'		    => '_um_cta_bg',
				'type'		    => 'color',
				'label'		    => __( 'Button Background Color', 'um-notices' ),
				'value'		    => UM()->query()->get_meta_value( '_um_cta_bg', null, 'na' ),
				'conditional'   => array( '_um_cta', '=', 1 )
			),
			array(
				'id'		    => '_um_cta_clr',
				'type'		    => 'color',
				'label'		    => __( 'Button Text Color', 'um-notices' ),
				'value'		    => UM()->query()->get_meta_value( '_um_cta_clr', null, 'na' ),
				'conditional'   => array( '_um_cta', '=', 1 )
			),
		)
	) )->render_form(); ?>

	<div class="um-admin-clear"></div>
</div>