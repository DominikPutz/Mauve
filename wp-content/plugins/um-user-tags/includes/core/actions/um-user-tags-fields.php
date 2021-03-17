<?php if ( ! defined( 'ABSPATH' ) ) exit;


/**
 * Modal field settings
 *
 * @param $val
 */
function um_admin_field_edit_hook_tag_source( $val ) {

	$parent_tags = UM()->User_Tags()->get_localized_terms( array(
		'parent'    => 0,
	) ); ?>

	<p>
		<label for="_tag_source">
			<?php _e( 'Select a user tags source', 'um-user-tags' ); ?>
			<?php UM()->tooltip( __( 'Choose the user tags type that user can select from', 'um-user-tags' ) ); ?>
		</label>
		<select name="_tag_source" id="_tag_source" style="width: 100%">
			<?php foreach ( $parent_tags as $tag ) { ?>
				<option value="<?php echo esc_attr( $tag->term_id ); ?>" <?php selected( $tag->term_id, $val ); ?>>
					<?php echo $tag->name; ?>
				</option>
			<?php } ?>
		</select>
	</p>

	<?php
}
add_action( 'um_admin_field_edit_hook_tag_source', 'um_admin_field_edit_hook_tag_source' );