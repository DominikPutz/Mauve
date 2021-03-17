<?php if ( ! defined( 'ABSPATH' ) ) exit;


/**
 * Extend UM settings
 *
 * @param $settings
 * @return mixed
 */
function um_private_content_settings( $settings ) {
    $settings['licenses']['fields'][] = array(
        'id'      		=> 'um_private_content_license_key',
        'label'    		=> __( 'Private Content License Key', 'um-private-content' ),
        'item_name'     => 'Private Content',
        'author' 	    => 'Ultimate Member',
        'version' 	    => um_private_content_version,
    );

    $key = ! empty( $settings['extensions']['sections'] ) ? 'private-content' : '';
    $settings['extensions']['sections'][$key] = array(
        'title'     => __( 'Private Content', 'um-private-content' ),
        'fields'    => array(
            array(
                'id'       => 'private_content_generate',
                'type'     => 'ajax_button',
                'label'    => __( 'Generate pages', 'um-private-content' ),
                'value'    => __( 'Generate pages for existing users', 'um-private-content' ),
                'tooltip'  => __( 'Generate pages for already existing users', 'um-private-content' ),
                'size'     => 'small'
            ),
            array(
                'id'        => 'tab_private_content_title',
                'type'      => 'text',
                'label'     => __( 'Private Content Tab Title','um-private-content' ),
                'tooltip'   => __( 'This is the title of the tab for show user\'s private content', 'um-private-content' ),
            ),
            array(
                'id'            => 'tab_private_content_icon',
                'type'          => 'text',
                'title'         => __( 'Private Content Tab Icon','um-private-content' ),
                'tooltip' 	    => __( 'This is the icon of the tab for show user\'s private content','um-private-content' ),
                'class'         => 'private_content_icon',
            )
        )
    );

    return $settings;
}
add_filter( 'um_settings_structure', 'um_private_content_settings', 10, 1 );