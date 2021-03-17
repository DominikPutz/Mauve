<?php if ( ! defined( 'ABSPATH' ) ) exit;


if ( empty( $hook_callback->um_hooks ) ) {
	echo __( 'No options available', 'um-mycred' );
} else {
	$prefs = $hook_callback->prefs;

	$i = 0;
	foreach ( $hook_callback->um_hooks as $hook => $k ) {

		if ( $i != 0 ) { ?>
			<hr/>
		<?php } ?>

		<h2><?php if ( ! empty( $k['icon'] ) ) { ?><i class="<?php echo esc_attr( $k['icon'] ); ?>"></i>&nbsp;<?php } ?><?php echo $k['title']; ?></h2>

		<!-- First we set the amount -->
		<?php if( isset( $hook_callback->um_hooks[ $hook ]['deduct'] ) ) { ?>
			<label class="subheader" for="<?php echo esc_attr( $hook_callback->field_id( array( $hook, 'creds' ) ) ); ?>"><?php printf( __( 'Deduct %s', 'um-mycred' ), $hook_callback->core->plural() ); ?></label>
		<?php } else { ?>
			<label class="subheader" for="<?php echo esc_attr( $hook_callback->field_id( array( $hook, 'creds' ) ) ); ?>"><?php printf( __( 'Award %s', 'um-mycred' ), $hook_callback->core->plural() ); ?></label>
		<?php } ?>

		<ol>
			<li>
				<div class="h2">
					<input type="text" name="<?php echo esc_attr( $hook_callback->field_name( array( $hook, 'creds' ) ) ); ?>" id="<?php echo esc_attr( $hook_callback->field_id( array( $hook, 'creds' ) ) ); ?>" value="<?php echo esc_attr( $hook_callback->core->format_number( $prefs[ $hook ]['creds'] ) ); ?>" size="8" />
				</div>
			</li>
		</ol>
		<!-- Then the log template -->
		<label class="subheader" for="<?php echo esc_attr( $hook_callback->field_id(  array( $hook, 'log' ) ) ); ?>"><?php _e( 'Log template', 'um-mycred' ); ?></label>
		<ol>

			<li>
				<div class="h2"><input type="text" name="<?php echo esc_attr( $hook_callback->field_name(  array( $hook, 'log' ) ) ); ?>" id="<?php echo esc_attr( $hook_callback->field_id(  array( $hook, 'log' ) ) ); ?>" value="<?php echo esc_attr( $prefs[ $hook ]['log'] ); ?>" class="long" /></div>
			</li>
			<li>
				<label for="<?php echo esc_attr( $hook_callback->field_id( array( $hook, 'limit' ) ) ); ?>"><?php _e( 'Limit', 'um-mycred' ); ?></label>
				<?php echo $hook_callback->hook_limit_setting( $hook_callback->field_name(  array( $hook, 'limit' ) ), $hook_callback->field_id(   array( $hook, 'limit' ) ), $prefs[ $hook ]['limit'] ); ?>
			</li>
			<input type="hidden" name="<?php echo esc_attr( $hook_callback->field_name( array( $hook, 'um_hook' ) ) ); ?>" id="<?php echo esc_attr( $hook_callback->field_id( array( $hook, 'limit' ) ) ); ?>" value="<?php echo esc_attr( $hook );?>" />
		</ol>

		<?php do_action( 'um_mycred_hooks_option_extended', $hook, $k, $prefs, $hook_callback ) ?>

		<ol>
			<li class="empty">&nbsp;</li>
		</ol>

		<?php $i++;
	}
}