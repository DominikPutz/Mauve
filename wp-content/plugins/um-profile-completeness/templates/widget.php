<?php
/**
 * Template for the UM Profile Completeness, "Complete your Profile " widget
 *
 * Called from the Profile_Completeness_Shortcode->ultimatemember_profile_completeness() method
 *
 * This template can be overridden by copying it to yourtheme/ultimate-member/um-profile-completeness/widget.php
 */
if ( ! defined( 'ABSPATH' ) ) exit;
?>

<!-- um-profile-completeness/templates/widget.php -->
<div class="um-completeness-widget" data-user_id="<?php echo get_current_user_id(); ?>">
	<div style="font-weight: bold;line-height: 22px;">
		<span>
			<?php _e('Profile:','um-profile-completeness'); ?>
			<span class="um-completeness-progress" style="color: #3BA1DA;">
				<span class="um-completeness-jx">
					<?php echo $result['progress']; ?>
				</span>%
			</span>
		</span>
	</div>

	<div class="um-completeness-bar-holder">
		<?php echo $result['bar']; ?>
	</div>

	<?php if ( isset( $result['steps'] ) && is_array( $result['steps'] ) ) { ?>
		<div class="um-completeness-steps">

			<?php $i = 0;
			foreach ( $result['steps'] as $key => $pct ) {
				if ( $key == 'synced_profile_photo' || $key == 'synced_gravatar_hashed_id' ) {
					continue;
				}

				if ( in_array( $key, $result['completed'] ) ) {
					continue;
				}

				$skip_field = apply_filters( 'um_profile_completeness_skip_field', false, $key, $result );
				if ( $skip_field ) {
					continue;
				}

				$label = UM()->Profile_Completeness_API()->get_field_title( $key );

				if ( $key == 'profile_photo' || $key == 'cover_photo' ) {
					if ( $key == 'profile_photo' && um_user( 'synced_gravatar_hashed_id' ) && UM()->options()->get( 'use_gravatars' ) ) {
						continue;
					}

					if ( um_is_core_page( 'user' ) ) {
						$edit_link = '<a href="'. um_edit_profile_url() .'" data-key="'.$key.'" class="um-completeness-edit">' . $label . '</a>';
					} else {
						$edit_link = '<a href="javascript:void(0);" data-key="'.$key.'" class="um-completeness-edit">' . $label . '</a>';
					}

				} else {
					$edit_link = '<a href="javascript:void(0);" data-key="'.$key.'" class="um-completeness-edit">' . $label . '</a>';
				}

				$i++; ?>

				<div data-key="<?php echo $key; ?>" class="um-completeness-step <?php //if ( in_array( $key, array('profile_photo','cover_photo') ) && um_is_core_page('user') ) echo 'is-core'; ?> <?php if ( in_array( $key, $result['completed'] ) ) echo 'completed'; ?>">
					<span class="um-completeness-bullet"><?php echo $i; ?>.</span>
					<span class="um-completeness-desc"><?php printf( __( '<strong>%s</strong>', 'um-profile-completeness' ), $edit_link ); ?></span>
					<span class="um-completeness-pct"><?php echo $pct; ?>%</span>
				</div>

			<?php } ?>
		</div>
	<?php } ?>

	<div style="padding-top: 15px;text-align: center;">
		<a href="<?php echo esc_url( um_edit_profile_url() ); ?>"><?php _e( 'Complete your profile','um-profile-completeness' ); ?></a>
	</div>
</div>