<?php if ( ! defined( 'ABSPATH' ) ) exit;


/**
 * Extend settings
 *
 * @param $settings
 *
 * @return mixed
 */
function um_notices_settings( $settings ) {

	$settings['licenses']['fields'][] = array(
		'id'        => 'um_notices_license_key',
		'label'     => __( 'Notices License Key', 'um-notices' ),
		'item_name' => 'Notices',
		'author'    => 'Ultimate Member',
		'version'   => um_notices_version,
	);

	$key = ! empty( $settings['extensions']['sections'] ) ? 'notices' : '';

	$settings['extensions']['sections'][ $key ] = array(
		'title'     => __( 'Notices', 'um-notices' ),
		'fields'    => array(
			array(
				'id'            => 'notice_pos',
				'type'          => 'select',
				'label'         => __( 'Notice Position in Footer', 'um-notices' ),
				'options'       => array(
					'right' => __( 'Show to Right', 'um-notices' ),
					'left'  => __( 'Show to Left', 'um-notices' ),
				),
				'placeholder'   => __( 'Select...', 'um-notices' ),
				'size'          => 'middle'
			)
		)
	);

	return $settings;
}
add_filter( 'um_settings_structure', 'um_notices_settings', 10, 1 );