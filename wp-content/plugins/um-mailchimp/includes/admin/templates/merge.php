<div class="um-admin-metabox">
	<?php
	if ( empty( $list_id ) && !empty( $post_id ) ) {
		$list_id = get_post_meta( $post_id, '_um_list', true );
		if ( empty( $list_id ) ) {
			$lists = UM()->Mailchimp()->api()->get_lists();
			if ( count( $lists ) ) {
				list( $list_id ) = array_keys( $lists );
			}
		}
		$merged = get_post_meta( $post_id, '_um_merge', true );
	}

	$merge_vars = UM()->builtin()->all_user_fields();

	$options_array = array();
	$options_date = array();
	$options_string = array();
	foreach ( $merge_vars as $k => $var ) {
		if ( empty( $var['title'] ) ) {
			continue;
		}

		switch ( $k ) {
			case 'gender':
			case 'role_radio':
			case 'role_select':
				$options_array[$k] = $var['title'];
				break;

			case 'user_registered':
				$options_date[$k] = $var['title'];
				break;

			case 'country':
				$options_string[$k] = $var['title'];
				break;
		}

		$type = UM()->fields()->get_field_type( $k );
		switch ( $type ) {
			case 'multiselect':
			case 'radio':
			case 'select':
			case 'user_tags':
				$options_array[$k] = $var['title'];
				break;

			case 'date':
			case 'time':
				$options_date[$k] = $var['title'];
				break;

			case 'checkbox':
			default:
				$options_string[$k] = $var['title'];
				break;
		}
	}

	$mc_merge_fields = UM()->Mailchimp()->api()->mc_get_merge_fields( $list_id );

	$fields = array();
	foreach ( $mc_merge_fields as $arr ) {

		$_tooltip = __( 'Type: ', 'um-mailchimp' ) . $arr['type'];
		$_options = array();
		$_value = isset( $merged[$arr['tag']] ) ? $merged[$arr['tag']] : '';
		$_description = '';

		switch ( $arr['type'] ) {
			case 'dropdown':
			case 'radio':
				$_options = $options_array;
				if ( isset( $arr['options'] ) && is_array( $arr['options']['choices'] ) ) {
					$_tooltip .= '<br>' . __( 'Choices: ', 'um-mailchimp' ) . implode( ', ', $arr['options']['choices'] );
					if ( $_value ){
						$field_data = UM()->builtin()->get_a_field( $_value );
						if ( empty( $field_data['options'] ) || $field_data['options'] !== $arr['options']['choices'] ){
							$_description .= __( "Warning! Choices doesn't match", 'um-mailchimp' );
							$_description .= ' <span class="um_tooltip dashicons dashicons-editor-help" title="' . __( 'MC Choices: ', 'um-mailchimp' ) . implode( ', ', (array) $arr['options']['choices'] ) . '<br><br>' . __( 'UM Choices: ', 'um-mailchimp' ) . implode( ', ', (array) $field_data['options'] ) . '"></span>';
						}
					}
				}
				break;

			case 'birthday':
			case 'date':
				$_options = $options_date;
				break;

			default:
				$_options = $options_string;
				break;
		}
		if ( empty( $arr['required'] ) ) {
			$_options = array_merge( array( '0' => __( '~Ignore this field~', 'um-mailchimp' ) ), $_options );
		}

		$fields[] = array(
				'id'					 => $arr['tag'],
				'type'				 => 'select',
				'size'				 => 'long',
				'required'		 => isset( $arr['required'] ) ? $arr['required'] : false,
				'label'				 => $arr['name'] . ' <sup>(' . $arr['tag'] . ')</sup>',
				'tooltip'			 => $_tooltip,
				'options'			 => $_options,
				'value'				 => $_value,
				'description'	 => $_description,
		);
	}

	UM()->admin_forms( array(
			'class'			 => 'um-form-mailchimp-merge um-half-column',
			'fields'		 => $fields,
			'prefix_id'	 => 'mailchimp[_um_merge]'
	) )->render_form();
	?>

	<div class="um-admin-clear"></div>
</div>