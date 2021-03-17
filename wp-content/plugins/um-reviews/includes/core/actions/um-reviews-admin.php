<?php if ( ! defined( 'ABSPATH' ) ) exit;


/**
 * Fallback for empty roles
 *
 * @param $post_id
 */
function um_reviews_reset_roles( $post_id ) {
	delete_post_meta( $post_id, '_um_can_review_roles' );
}
add_action( 'um_admin_before_saving_role_meta', 'um_reviews_reset_roles' );


/**
 * Creates options in Role page
 *
 * @param $roles_metaboxes
 *
 * @return array
 */
function um_reviews_add_role_metabox( $roles_metaboxes ) {
	$roles_metaboxes[] = array(
		'id'        => "um-admin-form-reviews{" . um_reviews_path . "}",
		'title'     => __( 'User Reviews', 'um-reviews' ),
		'callback'  => array( UM()->metabox(), 'load_metabox_role' ),
		'screen'    => 'um_role_meta',
		'context'   => 'normal',
		'priority'  => 'default'
	);

	return $roles_metaboxes;
}
add_filter( 'um_admin_role_metaboxes', 'um_reviews_add_role_metabox', 10, 1 );