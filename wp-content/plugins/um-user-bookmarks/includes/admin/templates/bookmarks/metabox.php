<?php if ( ! defined( 'ABSPATH' ) ) exit;

global $post;
$post_settings = get_post_meta( $post->ID,'_um_user_bookmarks',true ); ?>

<div class="um-admin-metabox">

	<?php $fields = array(
		array(
			'id'    => 'disable_bookmark',
			'type'  => 'checkbox',
			'label' => __( 'Disable Bookmark', 'um-user-bookmarks' ),
			'value' => isset( $post_settings['disable_bookmark'] ) ? $post_settings['disable_bookmark'] : 0,
		)
	);

	UM()->admin_forms()->set_data( array(
		'class'     => 'um-user-bookmarks-content um-long-field',
		'prefix_id' => '_um_user_bookmarks',
		'fields'    => $fields
	) )->render_form(); ?>

</div>