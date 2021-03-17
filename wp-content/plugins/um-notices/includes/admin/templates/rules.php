<?php if ( ! defined( 'ABSPATH' ) ) exit; ?>

<div class="um-admin-metabox">
	<?php UM()->admin_forms( array(
		'class'		=> 'um-form-notice-rules um-half-column',
		'prefix_id'	=> 'notice',
		'fields' => array(
			array(
				'id'		    => '_um_show_in_urls',
				'type'		    => 'checkbox',
				'label'		    => __( 'Show on specific URLs only', 'um-notices' ),
				'value'		    => UM()->query()->get_meta_value( '_um_show_in_urls', null, 0 ),
			),
			array(
				'id'		    => '_um_allowed_urls',
				'type'		    => 'textarea',
				'label'		    => __( 'Enter allowed URLs one per line', 'um-notices' ),
				'value'		    => UM()->query()->get_meta_value('_um_allowed_urls', null, 'na' ),
				'conditional'   => array( '_um_show_in_urls', '=', 1 )
			),
			array(
				'id'		    => '_um_show_in_home',
				'type'		    => 'checkbox',
				'label'		    => __( 'Show on Homepage', 'um-notices' ),
				'value'		    => UM()->query()->get_meta_value( '_um_show_in_home', null, 1 ),
				'conditional'   => array( '_um_show_in_urls', '=', 0 )
			),
			array(
				'id'		    => '_um_show_in_posts',
				'type'		    => 'checkbox',
				'label'		    => __( 'Show on Posts', 'um-notices' ),
				'value'		    => UM()->query()->get_meta_value( '_um_show_in_posts', null, 1 ),
				'conditional'   => array( '_um_show_in_urls', '=', 0 )
			),
			array(
				'id'		    => '_um_show_in_pages',
				'type'		    => 'checkbox',
				'label'		    => __( 'Show on Pages', 'um-notices' ),
				'value'		    => UM()->query()->get_meta_value( '_um_show_in_pages', null, 1 ),
				'conditional'   => array( '_um_show_in_urls', '=', 0 )
			),
			array(
				'id'		    => '_um_show_in_types',
				'type'		    => 'checkbox',
				'label'		    => __( 'Show on Custom Post Types', 'um-notices' ),
				'value'		    => UM()->query()->get_meta_value( '_um_show_in_types', null, 1 ),
				'conditional'   => array( '_um_show_in_urls', '=', 0 )
			)
		)
	) )->render_form(); ?>

	<div class="um-admin-clear"></div>
</div>