<?php
/**
 * Template for the UM Followers. The list of user followers
 *
 * Shortcode: [ultimatemember_followers]
 * Caller: method Followers_Shortcode->ultimatemember_followers()
 *
 * This template can be overridden by copying it to yourtheme/ultimate-member/um-followers/followers.php
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( $followers ) {

	foreach ( $followers as $k => $arr ) {
		/**
		 * @var $user_id2;
		 */
		extract( $arr );

		um_fetch_user( $user_id2 ); ?>

		<div class="um-followers-user">

			<a href="<?php echo esc_url( um_user_profile_url() ); ?>" class="um-followers-user-photo" title="<?php echo esc_attr( um_user( 'display_name' ) ); ?>">
				<?php echo get_avatar( um_user('ID'), 50 ); ?>
			</a>

			<div class="um-followers-user-btn">
				<?php if ( $user_id2 == get_current_user_id() ) {
					echo '<a href="' . esc_url( um_edit_profile_url() ) . '" class="um-follow-edit um-button um-alt">' . __( 'Edit profile', 'um-followers' ) . '</a>';
				} else {
					echo UM()->Followers_API()->api()->follow_button( $user_id2, get_current_user_id() );
				} ?>
			</div>

			<div class="um-followers-user-name">
				<a href="<?php echo esc_url( um_user_profile_url() ); ?>" title="<?php echo esc_attr( um_user( 'display_name' ) ); ?>">
					<?php echo esc_html( um_user( 'display_name' ) ); ?>
				</a>

				<?php do_action('um_followers_list_post_user_name', $user_id, $user_id2 );

				if ( um_user( 'ID' ) == get_current_user_id() ) { ?>
					<span class="um-followers-user-span"><?php _e( 'You', 'um-followers' ); ?></span>
				<?php } elseif ( $user_id == get_current_user_id() && UM()->Followers_API()->api()->followed( get_current_user_id(), $user_id2 ) ) { ?>
					<span class="um-followers-user-span"><?php _e( 'Follows you', 'um-followers' ); ?></span>
				<?php }

				do_action('um_followers_list_after_user_name', $user_id, $user_id2 ); ?>
			</div>

			<?php do_action('um_followers_list_pre_user_bio', $user_id, $user_id2 ); ?>

			<div class="um-followers-user-bio">
				<?php echo um_get_snippet( um_filtered_value( 'description' ), 25 ); ?>
			</div>

			<?php do_action('um_followers_list_post_user_bio', $user_id, $user_id2 ); ?>

		</div>

	<?php }

} else { ?>

	<div class="um-profile-note">
		<span><?php echo ( $user_id == get_current_user_id() ) ? __( 'You do not have any followers yet.', 'um-followers' ) : __( 'This user does not have any followers yet.', 'um-followers' ); ?></span>
	</div>

<?php }