<?php
/**
 * Template for the UM Notices
 * Shown in the footer
 * Called from the Notices_Query->show_notice() method
 *
 * This template can be overridden by copying it to yourtheme/ultimate-member/um-notices/notice.php
 */
if ( ! defined( 'ABSPATH' ) ) exit; ?>

<!-- um-notices/templates/notice.php -->
<div class="um-notices-wrap <?php echo ( $force_id ) ? 'yes-shortcode' : 'no-shortcode'; ?> um-notices-<?php echo esc_attr( UM()->options()->get( 'notice_pos' ) ); ?>"
     style="<?php echo esc_attr( $style ); ?>" data-notice_id="<?php echo esc_attr( $notice_id ); ?>" data-user_id="<?php echo esc_attr( get_current_user_id() ); ?>">
	<div class="um-notices-box <?php echo empty( $meta['_um_icon'][0] ) ? '' : 'has-icon'; ?>">

		<a href="javascript:void(0);" class="um-notices-close" style="<?php echo esc_attr( $close_color ); ?>">
			<i class="um-icon-android-close"></i>
		</a>

		<?php if ( ! empty( $meta['_um_icon'][0] ) ) { ?>
			<i class="<?php echo esc_attr( $meta['_um_icon'][0] ); ?>" style="<?php echo esc_attr( $icon_color ); ?>"></i>
		<?php }

		echo wpautop( $post->post_content );

		if ( $meta['_um_cta'][0] ) {

			$cta_bg = ! empty( $meta['_um_cta_bg'][0] ) ? $meta['_um_cta_bg'][0] : '#666';
			$cta_color = ! empty( $meta['_um_cta_clr'][0] ) ? $meta['_um_cta_clr'][0] : '#fff';
			?>

			<div class="um-notices-cta">
				<a href="<?php echo esc_url( $meta['_um_cta_url'][0] ); ?>" style="background:<?php echo esc_attr( $cta_bg ); ?>;color:<?php echo esc_attr( $cta_color ); ?>;">
					<?php echo $meta['_um_cta_text'][0]; ?>
				</a>
			</div>

		<?php } ?>

	</div>
</div>

<style type="text/css">
	<?php if ( ! empty( $meta['_um_textcolor'][0] ) ) { ?>
		.um-notices-wrap p {
			color: <?php echo $meta['_um_textcolor'][0]; ?> !important;
		}
	<?php }

	if ( ! empty( $meta['_um_textcolor'][0] ) ) { ?>
		.um-notices-wrap p a {
			color: <?php echo $meta['_um_textcolor'][0]; ?> !important;
			text-decoration: underline !important;
		}
	<?php }

	if ( ! empty( $meta['_um_min_width'][0] ) ) { ?>
		.um-notices-wrap.no-shortcode {
			min-width: <?php echo $meta['_um_min_width'][0]; ?>;
		}
	<?php } ?>
</style>