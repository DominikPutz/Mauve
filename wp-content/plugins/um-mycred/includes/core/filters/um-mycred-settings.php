<?php if ( ! defined( 'ABSPATH' ) ) exit;


/**
 * Extend settings
 *
 * @param array $settings
 *
 * @return array
 */
function um_mycred_settings( $settings ) {

	$settings['licenses']['fields'][] = array(
		'id'        => 'um_mycred_license_key',
		'label'     => __( 'myCRED License Key', 'um-mycred' ),
		'item_name' => 'myCRED',
		'author'    => 'Ultimate Member',
		'version'   => um_mycred_version,
	);

	$fields = array(
		array(
			'id'        => 'account_tab_points',
			'type'      => 'checkbox',
			'label'     => __( 'Account Tab','um-mycred' ),
			'tooltip'   => __('Show or hide an account tab that shows the user balance','um-mycred'),
		),
		array(
			'id'        => 'mycred_decimals',
			'type'      => 'text',
			'label'     => __( 'Number of decimals to allow in balance', 'um-mycred' ),
			'size'      => 'small',
		),
		array(
			'id'        => 'mycred_refer',
			'type'      => 'checkbox',
			'label'     => __( 'Show user affiliate link in account page', 'um-mycred' ),
		),
	);

	if ( function_exists( 'mycred_get_users_badges' ) ) {
		$fields = array_merge( $fields, array(
			array(
				'id'        => 'mycred_badge_size',
				'type'      => 'text',
				'validate'  => 'numeric',
				'label'     => __( 'Width / height of badge in pixels','um-mycred' ),
				'tooltip'   => __( 'Badges appearing in profile tab','um-mycred' ),
				'size'      => 'small',
			),
			array(
				'id'        => 'mycred_show_badges_in_header',
				'type'      => 'checkbox',
				'label'     => __( 'Show user badges in profile header?', 'um-mycred' ),
			),
			array(
				'id'        => 'mycred_show_badges_in_members',
				'type'      => 'checkbox',
				'label'     => __( 'Show user badges in Member Directories?', 'um-mycred' ),
			),
		) );
	}


	$key = ! empty( $settings['extensions']['sections'] ) ? 'mycred' : '';
	$settings['extensions']['sections'][ $key ] = array(
		'title'     => __( 'myCRED', 'um-mycred' ),
		'fields'    => $fields
	);

	$settings = apply_filters( 'um_mycred_settings_extend', $settings, $key );
	return $settings;
}
add_filter( 'um_settings_structure', 'um_mycred_settings', 10, 1 );


/**
 * @param $notifications_log
 *
 * @return mixed
 */
function um_mycred_notifications_log( $notifications_log ) {

	$notifications_log['mycred_custom_notification'] = array(
		'title' => 'myCRED notification',
		'template' => '',
		'account_desc' => '',
	);

	$notifications_log['mycred_award'] = array(
		'title' => __('User awarded points for action','um-mycred'),
		'template' => __('You have received <strong>{mycred_points}</strong> for <strong>{mycred_task}</strong>','um-mycred'),
		'account_desc' => __('When I receive points by completing an action','um-mycred'),
	);

	$notifications_log['mycred_deduct'] = array(
		'title' => __('User deducted points for action','um-mycred'),
		'template' => __('<strong>{mycred_points}</strong> deduction for <strong>{mycred_task}</strong>','um-mycred'),
		'account_desc' => __('Points deducted when incompleted an action','um-mycred'),
	);

	$notifications_log['mycred_points_sent'] = array(
		'title' => __('User receives points from another person','um-mycred'),
		'template' => __('You have just got <strong>{mycred_points}</strong> from <strong>{mycred_sender}</strong>','um-mycred'),
		'account_desc' => __('When I receive points balance from another member','um-mycred'),
	);

	return $notifications_log;
}
add_filter( 'um_notifications_core_log_types', 'um_mycred_notifications_log', 10, 1 );