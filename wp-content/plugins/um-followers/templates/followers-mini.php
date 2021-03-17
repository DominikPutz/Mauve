<?php
/**
 * Template for the UM Followers. The list of user followers
 *
 * Shortcode: [ultimatemember_followers]
 * Caller: method Followers_Shortcode->ultimatemember_followers()
 *
 * This template can be overridden by copying it to yourtheme/ultimate-member/um-followers/followers-mini.php
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
} ?>

<div class="um-followers-m" data-max="<?php echo esc_attr( $max ); ?>">

	<?php if ( $followers ) {
		foreach ( $followers as $k => $arr ) {
			/**
			 * @var int $user_id2
			 */
			extract( $arr );
			um_fetch_user( $user_id2 );  ?>

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
			<?php echo ( $user_id == get_current_user_id() ) ? __( 'You do not have any followers yet.', 'um-followers' ) : __( 'This user do not have any followers yet.', 'um-followers' ); ?>
		</p>

	<?php } ?>

</div>
<div class="um-clear"></div>