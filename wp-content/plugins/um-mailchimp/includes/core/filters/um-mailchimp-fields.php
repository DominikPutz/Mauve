<?php
if( !defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Add field type
 *
 * @extend core fields
 *
 * @param array $fields
 * @return array
 */
function um_mc_add_field( $fields ) {

	$fields[ 'mailchimp' ] = array(
			'name'		 => __( 'MailChimp', 'um-mailchimp' ),
			'col1'		 => array( '_title' ),
			'col2'		 => array( '_mailchimp_list' ),
			'col3'		 => array( '_mailchimp_auto_subscribe' ),
			'validate' => array(
					'_title' => array(
							'mode'	 => 'required',
							'error'	 => __( 'You must provide a title', 'um-mailchimp' )
					),
			)
	);

	return $fields;
}
add_filter( "um_core_fields_hook", 'um_mc_add_field', 10 );

/**
 * Visiable fields on Regiatration page
 *
 * @param string $output
 * @param array $data
 * @return string
 */
function um_mc_edit_field_register( $output, $data ) {
	/**
	 * @var $mailchimp_list
	 * @var $metakey
	 */
	extract( $data );

	$wp_list = get_post( $mailchimp_list );

	if( !$wp_list || !$wp_list->_um_status || $wp_list->_um_reg_status ) {
		return;
	}

	$value = wp_create_nonce( "um-mailchimp-nonce:$wp_list->ID:" . date( 'Y-m-d' ) );

	// classes
	if ( !isset( $data[ 'classes' ] ) ) {
		$data[ 'classes' ] = '';
	}

	// conditions
	$data[ 'conditional' ] = '';
	if ( isset( $data[ 'conditions' ] ) && is_array( $data[ 'conditions' ] ) ) {
		$data[ 'classes' ] .= ' um-is-conditional';

		foreach ( $data[ 'conditions' ] as $cond_id => $cond ) {
			$data[ 'conditional' ] .= ' data-cond-' . $cond_id . '-action="' . $cond[ 0 ] . '" data-cond-' . $cond_id . '-field="' . $cond[ 1 ] . '" data-cond-' . $cond_id . '-operator="' . $cond[ 2 ] . '" data-cond-' . $cond_id . '-value="' . $cond[ 3 ] . '"';
		}
	}

	ob_start();
	?>

		<div class="um-field um-field-b um-field-mailchimp <?php echo $data['classes']; ?>" <?php echo $data['conditional']; ?> data-key="<?php echo $metakey; ?>">
		<div class="um-field-area">
			<label class="um-field-checkbox">

				<?php if( empty( $data[ 'mailchimp_auto_subscribe' ] ) ) : ?>
					<input type="checkbox" name="um-mailchimp[<?php echo esc_attr( $wp_list->ID ); ?>]" value="<?php echo esc_attr( $value ); ?>" />
					<span class="um-field-checkbox-state"><i class="um-icon-android-checkbox-outline-blank"></i></span>
				<?php else : ?>
					<input type="checkbox" name="um-mailchimp[<?php echo esc_attr( $wp_list->ID ); ?>]" value="<?php echo esc_attr( $value ); ?>" checked="checked" />
					<span class="um-field-checkbox-state"><i class="um-icon-android-checkbox-outline"></i></span>
				<?php endif; ?>

				<span class="um-field-checkbox-option"><?php echo ( $wp_list->_um_desc_reg ) ? $wp_list->_um_desc_reg : $data['title']; ?></span>
			</label>
			<div class="um-clear"></div>
		</div>
	</div>

	<?php
	$output .= ob_get_clean();

	return $output;
}
add_filter( 'um_edit_field_register_mailchimp', 'um_mc_edit_field_register', 10, 2 );

/**
 * Default last_login value
 *
 * @param string $value
 * @return string
 */
function um_mc_last_login__filter( $value ) {

	if( !$value ) {
		$value = um_user( 'user_registered' );
	}
	return $value;
}
add_filter( 'um_profile_last_login_empty__filter', 'um_mc_last_login__filter', 999, 1 );

/**
 * Register field type
 *
 * @do not require a metakey on mailchimp field
 *
 * @param array $array
 * @return string
 */
function um_mc_requires_no_metakey( $array ) {
	$array[] = 'mailchimp';
	return $array;
}
add_filter( 'um_fields_without_metakey', 'um_mc_requires_no_metakey' );
