<?php if ( ! defined( 'ABSPATH' ) ) exit; ?>

<div class="um-clear">
	<a href="javascript:void(0);" class="um-user-bookmarks-button um-user-bookmarks-add-button" data-post="<?php echo esc_attr( $post_id ); ?>"
	   data-um_user_bookmarks_id="<?php echo esc_attr( $post_id ); ?>" data-user="<?php echo esc_attr( $user_id ); ?>">
		<i class="<?php echo esc_attr( $icon ); ?>"></i>
		<span class="text"><?php echo $text; ?></span>
	</a>
</div>