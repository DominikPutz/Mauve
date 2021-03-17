<?php if ( ! defined( 'ABSPATH' ) ) exit;


/**
 * Extend settings
 *
 * @param $settings
 *
 * @return mixed
 */
function um_user_tags_settings( $settings ) {
	$settings['licenses']['fields'][] = array(
		'id'        => 'um_user_tags_license_key',
		'label'     => __( 'User Tags License Key', 'um-user-tags' ),
		'item_name' => 'User Tags',
		'author'    => 'Ultimate Member',
		'version'   => um_user_tags_version,
	);


	$forms_query = new \WP_Query;
	$member_directories = $forms_query->query( array(
		'post_type'         => 'um_directory',
		'posts_per_page'    => -1,
		'fields'            => array( 'ID', 'post_title' ),
	) );

	$directories = array(
		''  => __( '(None)', 'um-user-tags' ),
	);
	if ( ! empty( $member_directories ) && ! is_wp_error( $member_directories ) ) {
		foreach ( $member_directories as $directory ) {
			$directories[ $directory->ID ] = $directory->post_title;
		}
	}


	$fields = array(
		array(
			'id'            => 'user_tags_slug',
			'type'          => 'text',
			'label'         => __( 'User tag slug', 'um-user-tags' ),
			'descriptions'  => __( 'Base permalink for user tag', 'um-user-tags' ),
			'size'          => 'small'
		),
		array(
			'id'            => 'user_tags_max_num',
			'type'          => 'text',
			'label'         => __( 'Maximum number of tags to display in user profile', 'um-user-tags' ),
			'validate'      => 'numeric',
			'descriptions'  => __( 'Remaining tags will appear by clicking on a link', 'um-user-tags'),
			'size'          => 'small'
		),
	);

	if ( UM()->options()->get( 'members_page' ) ) {
		$fields[] = array(
			'id'        => 'user_tags_base_directory',
			'type'      => 'select',
			'label'     => __( 'Base member directory', 'um-user-tags' ),
			'tooltip'   => __( 'Select base member directory to use its settings for displaying users with this tag', 'um-user-tags' ),
			'options'   => $directories,
			'size'      => 'small',
		);
	}


	$key = ! empty( $settings['extensions']['sections'] ) ? 'user_tags' : '';
	$settings['extensions']['sections'][ $key ] = array(
		'title'     => __( 'User Tags', 'um-user-tags' ),
		'fields'    => $fields,
	);

	return $settings;
}
add_filter( 'um_settings_structure', 'um_user_tags_settings', 10, 1 );