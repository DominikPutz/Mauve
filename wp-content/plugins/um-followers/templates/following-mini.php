<?php
/**
 * Template for the UM Followers. The list of user following
 *
 * Shortcode: [ultimatemember_following]
 * Caller: method Followers_Shortcode->ultimatemember_following()
 *
 * This template can be overridden by copying it to yourtheme/ultimate-member/um-followers/following-mini.php
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
} ?>

<div class="um-followers-m" data-max="<?php echo $max; ?>">

	<?php if ( $following ) {

		foreach ( $following as $k => $arr ) {
			extract( $arr );
			um_fetch_user( $user_id1 ); ?>

			<div class="um-followers-m-user">
				<div class="um-followers-m-pic">
					<a href="<?php echo esc_url( um_user_profile_url() ); ?>" class="um-tip-n" title="<?php echo esc_attr( um_user( 'display_name' ) ); ?>">
						<?php echo get_avatar( um_user( 'ID' ), 40 ); ?>
					</a>
				</div>
			</div>

		<?php }

	} else { ?>

		<p>
			<?php echo ( $user_id == get_current_user_id() ) ? __( 'You did not follow anybody yet.', 'um-followers' ) : __( 'This user did not follow anybody yet.', 'um-followers' ); ?>
		</p>

	<?php } ?>

</div>
<div class="um-clear"></div>