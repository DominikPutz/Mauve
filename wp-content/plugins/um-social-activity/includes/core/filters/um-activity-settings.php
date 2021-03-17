<?php if ( ! defined( 'ABSPATH' ) ) exit;


/**
 * Extend core pages
 *
 * @param $pages
 *
 * @return mixed
 */
function um_activity_core_page( $pages ) {
	$pages['activity'] = array( 'title' => __( 'Activity', 'um-activity' ) );
	return $pages;
}
add_filter( 'um_core_pages', 'um_activity_core_page', 10, 1 );


/**
 * Extend settings
 *
 * @param $settings
 *
 * @return array
 */
function um_activity_settings( $settings ) {
	$settings['licenses']['fields'][] = array(
		'id'        => 'um_activity_license_key',
		'label'     => __( 'Social Activity License Key', 'um-activity' ),
		'item_name' => 'Social Activity',
		'author'    => 'Ultimate Member',
		'version'   => um_activity_version,
	);

	$key = ! empty( $settings['extensions']['sections'] ) ? 'activity' : '';
	$settings['extensions']['sections'][$key] = array(
		'title'     => __( 'Social Activity', 'um-activity' ),
		'fields'    => array(
			array(
				'id'    => 'activity_posts_num',
				'type'  => 'text',
				'label' => __( 'Number of wall posts on desktop','um-activity' ),
				'size'  => 'small'
			),
			array(
				'id'       => 'activity_max_faces',
				'type'     => 'text',
				'label'    => __( 'Maximum number of faces','um-activity' ),
				'size' => 'small'
			),
			array(
				'id'       => 'activity_posts_num_mob',
				'type'     => 'text',
				'label'    => __( 'Number of wall posts on mobile','um-activity' ),
				'size' => 'small'
			),
			array(
				'id'       => 'activity_init_comments_count',
				'type'     => 'text',
				'label'    => __( 'Number of initial comments/replies to display per post','um-activity' ),
				'size' => 'small'
			),
			array(
				'id'       => 'activity_load_comments_count',
				'type'     => 'text',
				'label'    => __( 'Number of comments/replies to get when user load more','um-activity' ),
				'size' => 'small'
			),
			array(
				'id'       	=> 'activity_order_comment',
				'type'     	=> 'select',
				'label'    	=> __( 'Comments order','um-activity' ),
				'options' 	=> array(
					'desc' 		=> __('Newest first','um-activity'),
					'asc' 		=> __('Oldest first','um-activity'),
				),
				'placeholder'=> __('Select...','um-activity'),
				'size' => 'small'
			),
			array(
				'id'       => 'activity_post_truncate',
				'type'     => 'text',
				'label'    => __( 'How many words appear before wall post is truncated?','um-activity' ),
				'size' => 'small'

			),
			array(
				'id'       	=> 'activity_enable_privacy',
				'type'     	=> 'checkbox',
				'label'   	=> __( 'Allow users to set their activity wall privacy through account page?','um-activity'),
			),
			array(
				'id'       => 'activity_trending_days',
				'type'     => 'text',
				'label'	   => __('Trending Hashtags Days','um-activity'),
				'desc'     => __( 'Enter number of days here. For example: 0 will calculate trending hashtags over today only, and 7 will calculate trending hashtags over a 7 day period.','um-activity' ),
				'size' => 'small',
			),
			array(
				'id'       	=> 'activity_require_login',
				'type'     	=> 'checkbox',
				'label'   	=> __( 'Require user to login to view activity walls?','um-activity'),
			),
			array(
				'id'       => 'activity_need_to_login',
				'type'     => 'textarea',
				'label'    => __( 'Text to display If user needs to login to interact in a post','um-activity' ),
				'rows'	   => 2,
			),
			array(
				'id'       		=> 'activity_highlight_color',
				'type'     		=> 'color',
				'label'    		=> __( 'Active Color','um-activity' ),
				'validate' 		=> 'color',
				'transparent'	=> false,
			)
		)
	);


	$settings = apply_filters( 'um_activity_settings_structure', $settings, $key );

	foreach ( apply_filters( 'um_activity_global_actions', UM()->Activity_API()->api()->global_actions ) as $k => $v ) {
		if ( $k == 'status' ) {
			continue;
		}

		$settings['extensions']['sections'][ $key ]['fields'] = array_merge( $settings['extensions']['sections'][ $key ]['fields'], array(
			array(
				'id'    => 'activity-' . $k,
				'type'  => 'checkbox',
				'label' => sprintf( __( 'Enable "%s" in activity', 'um-activity' ), $v ),
			)
		) );
	}

	return $settings;
}
add_filter( 'um_settings_structure', 'um_activity_settings', 10, 1 );


/**
 * Enable image upload
 *
 * @param $data
 *
 * @return array
 */
function um_custom_image_handle_wall_img_upload( $data ) {
	$data = array(
		'role' => 'wall-upload'
	);
	return $data;
}
add_filter( 'um_custom_image_handle_wall_img_upload', 'um_custom_image_handle_wall_img_upload', 10, 1 );


/**
 * Exclude from comments tab
 *
 * @param array $array
 *
 * @return array
 */
function um_activity_excluded_comment_types( $array ) {
	$array[] = 'um-social-activity';
	return $array;
}
add_filter( 'um_excluded_comment_types', 'um_activity_excluded_comment_types', 10, 1 );