<?php if ( ! defined( 'ABSPATH' ) ) exit;


/**
 * Adds a main tab to display forum activity in profile
 *
 * @param $tabs
 *
 * @return mixed
 */
function um_private_content_add_tab( $tabs ) {
	$tab_title = UM()->options()->get( 'tab_private_content_title' );
	$tab_title = ! empty( $tab_title ) ? $tab_title : __( 'Private Content', 'um-private-content' );

	$tab_icon = UM()->options()->get( 'tab_private_content_icon' );
	$tab_icon = ! empty( $tab_icon ) ? $tab_icon : 'um-faicon-eye-slash';

	$tabs['private_content'] = array(
		'name'              => $tab_title,
		'icon'              => $tab_icon,
		'default_privacy'   => 3,
	);

	return $tabs;
}
add_filter( 'um_profile_tabs', 'um_private_content_add_tab', 1000, 1 );


/**
 * Add tabs based on user
 *
 * @param $tabs
 *
 * @return mixed
 */
function um_private_content_user_add_tab( $tabs ) {
	if ( empty( $tabs['private_content'] ) ) {
		return $tabs;
	}

	$private_post_id = get_user_meta( um_user( 'ID' ), '_um_private_content_post_id', true );

	$post = get_post( $private_post_id );

	if ( empty( $post ) || empty( $post->post_content ) ) {
		unset( $tabs['private_content'] );
	}

	return $tabs;
}
add_filter( 'um_user_profile_tabs', 'um_private_content_user_add_tab', 1000, 1 );


/**
 * Default private content tab
 *
 * @param $args
 */
function um_profile_content_private_content( $args ) {
	$private_post_id = get_user_meta( um_user( 'ID' ), '_um_private_content_post_id', true );

	$post = get_post( $private_post_id );
	if ( ! empty( $post ) ) {
		setup_postdata( $post );
		the_content();
		wp_reset_postdata();
	}
}
add_action( 'um_profile_content_private_content', 'um_profile_content_private_content' );


/**
 * Fix formatting issue on private content - helpscout#26171
 * @param string $content
 * @return string
 */
if ( ! function_exists( 'um_profile_content_nl2br' ) ) {
	function um_profile_content_nl2br( $pages, $post ) {

		if ( $post->post_type === 'um_private_content' ) {
			foreach ( $pages as &$page ) {
				$page = preg_replace( '/\n\s*\n/im', '<br>', $page );
			}
		}

		return $pages;
	}
}
add_filter( 'content_pagination', 'um_profile_content_nl2br', 20, 2 );