<?php if ( ! defined( 'ABSPATH' ) ) exit;


/**
 * Replace user tag ID to name in {submitted_registration} placeholder
 * @param array $data
 * @return array
 */
function um_user_tags_email_registration_data( $data ) {

	foreach ( $data as $k => $v ) {
		$field_type = UM()->fields()->get_field_type( $k );
		if ( $field_type !== 'user_tags' ) {
			continue;
		}

		$user_tags_names = array();
		foreach ( ( array ) $v as $term_id ) {
			if ( is_numeric( $term_id ) ) {
				$term = get_term( $term_id );
				$user_tags_names[] = $term->name;
			} else {
				$user_tags_names[] = $term_id;
			}
		}
		$data[ $k ] = $user_tags_names;
	}

	return $data;
}
add_filter( 'um_email_registration_data', 'um_user_tags_email_registration_data' );


/**
 * Change how multiselect keys are treated
 *
 * @param $value
 * @param $field_type
 *
 * @return int
 */
function um_user_tags_multiselect_options( $value, $field_type ) {
	if ( $field_type == 'user_tags' ) {
		return 1;
	}

	return 0;
}
add_filter( 'um_multiselect_option_value', 'um_user_tags_multiselect_options', 10, 2 );


/**
 * Save our user tags filters
 *
 * @param $args
 * @return mixed
 */
function um_user_tags_assign_new_tags_field( $args ) {

	if ( $args['type'] == 'user_tags' ) {
		$store = get_option( 'um_user_tags_filters', array() );

		$store[ $args['metakey'] ] = $args['tag_source'];
		update_option( 'um_user_tags_filters', $store );
	}

	return $args;
}
add_filter( 'um_admin_pre_save_field_to_form', 'um_user_tags_assign_new_tags_field' );


/**
 * Outputs user tags
 *
 * @param $value
 * @param $data
 *
 * @return string
 */
function um_profile_field_filter_hook__user_tags( $value, $data ) {
	$metakey = $data['metakey'];
	$value = UM()->User_Tags()->get_tags( um_user( 'ID' ), $metakey );
	return $value;
}
add_filter( 'um_profile_field_filter_hook__user_tags', 'um_profile_field_filter_hook__user_tags', 99, 2 );


/**
 * Dynamically change field type
 *
 * @param $type
 *
 * @return string
 */
function um_hook_for_field_user_tags( $type ) {
	return 'multiselect';
}
add_filter( 'um_hook_for_field_user_tags', 'um_hook_for_field_user_tags' );


/**
 * Get custom user tags
 *
 * @param $options
 * @param $data
 *
 * @return array
 */
function um_multiselect_options_user_tags( $options, $data ) {

	$tags = UM()->User_Tags()->get_localized_terms( array(
		'child_of'  => $data['tag_source'],
	) );

	if ( ! $tags ) {
		return array( '' );
	}

	$options = array();
	foreach ( $tags as $term ) {
		$id = $term->term_id;
		$options[ $id ] = $term->name;
	}
	return $options;
}
add_filter( 'um_multiselect_options_user_tags', 'um_multiselect_options_user_tags', 100, 2 );


/**
 * @param $use_keyword
 * @param $type
 *
 * @return bool
 */
function um_multiselect_option_value_user_tags( $use_keyword, $type ) {
	if ( $type == 'user_tags' ) {
		return true;
	}

	return $use_keyword;
}
add_filter( 'um_multiselect_option_value', 'um_multiselect_option_value_user_tags', 10, 2 );


/**
 * Extend core fields
 *
 * @param $fields
 *
 * @return mixed
 */
function um_user_tags_add_field( $fields ) {

	$fields['user_tags'] = array(
		'name'     => __( 'User Tags', 'um-user-tags' ),
		'col1'     => array( '_title', '_metakey', '_help', '_visibility', '_public', '_roles' ),
		'col2'     => array( '_label', '_max_selections', '_tag_source' ),
		'col3'     => array( '_required', '_editable', '_icon' ),
		'validate' => array(
			'_title'   => array(
				'mode'  => 'required',
				'error' => __( 'You must provide a title', 'um-user-tags' )
			),
			'_metakey' => array(
				'mode' => 'unique',
			),
		)
	);

	return $fields;

}
add_filter( 'um_core_fields_hook', 'um_user_tags_add_field', 10 );


/**
 * Do not require a metakey
 *
 * @param $array
 *
 * @return array
 */
function um_user_tags_requires_no_metakey( $array ) {
	$array[] = 'user_tags';
	return $array;
}
add_filter( 'um_fields_without_metakey', 'um_user_tags_requires_no_metakey' );


/**
 * Do not require a metakey
 *
 * @param $array
 *
 * @return array
 */
function um_user_tags_requires_all_user_fields_no_metakey( $array ) {
	unset( $array[ array_search( 'user_tags', $array ) ] );
	return $array;
}
add_filter( 'um_all_user_fields_without_metakey', 'um_user_tags_requires_all_user_fields_no_metakey' );
add_filter( 'um_profile_completeness_fields_without_metakey', 'um_user_tags_requires_all_user_fields_no_metakey' );



/*
save custom tags submitted by user
*/
function um_user_tags_save_custom_tags( $to_update ) {
	$form_id = absint( $_REQUEST['form_id'] );

	$filters = get_option( 'um_user_tags_filters' );
	$fields = UM()->query()->get_attr( 'custom_fields', $form_id );

	foreach ( $fields as $metakey => $value ) {
		if ( $value['type'] == 'user_tags' && in_array( $metakey, array_keys( $filters ) ) ) {
			
			/*newly added*/
			if ( um_user( 'user_tags_can_add' ) && isset($value['tag_source'] ) ) {
				for ( $i=0; $i < count( $to_update[ $metakey ] ); $i++ ) {
					if ( ! is_numeric( $to_update[ $metakey ][ $i ] ) ) {

						$new_tag = wp_insert_term( $to_update[ $metakey ][ $i ], 'um_user_tag', array(
							'parent' => $value['tag_source']
						) );

						if ( ! is_wp_error( $new_tag ) ) {
							$to_update[ $metakey ][ $i ] = $new_tag['term_id'];
						}
					}
				}
			}
		}
	}

	return $to_update;
}
add_filter( 'um_user_pre_updating_profile_array','um_user_tags_save_custom_tags');
