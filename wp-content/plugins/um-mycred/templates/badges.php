<?php
/**
 * Template for the UM myCRED.
 * Used on Profile page, Badges tab, All Badges subtab.
 *
 * Caller: method myCRED->show_badges_all()
 *
 * This template can be overridden by copying it to yourtheme/ultimate-member/um-mycred/badges.php
 */
if ( ! defined( 'ABSPATH' ) ) exit; ?>

<span class="um-badges">
	<?php foreach ( $all_badges as $badge_id ) {
		$image_identification = false;
		$class = '';

		$has_no_main_image = true;
		$badge = mycred_get_badge( $badge_id ); ?>

		<span class="um-badge-leaderboard title">
			<strong><?php _e( $badge->title, 'um-mycred') ?></strong>
		</span>
		<div class="um-clear"></div>

		<?php $badge_level_reached = mycred_badge_level_reached( um_profile_id() , $badge_id );
		$image_identification = get_post_meta( $badge_id, 'main_image', true );
		$image_url = wp_get_attachment_url( $image_identification );
		$badge_title = strtoupper( $badge->title );
		$badge_title = apply_filters( 'um_mycred_badge_loop_title', $badge_title, $badge );

		if( ! empty( $image_url ) ) { ?>
			<span class="the-badge">
				<img src="<?php echo esc_url( $image_url ) ?>"
					 title="<?php echo esc_attr( $badge_title ) ?>"
					 alt="<?php echo esc_attr( $badge_title ) ?>"
					 width="<?php echo esc_attr( $size ) ?>"
					 height="<?php echo esc_attr( $size ) ?>"
					 class="mycred-badge earned um-tip-n <?php echo esc_attr( $class ) ?>" />
			</span>
		<?php }

		foreach ( $badge->levels as $level_key => $level ) {
			$base_requirements = array();
			$req_count = count( $level['requires'] );
			$lvl_count = count( $badge->levels );

			foreach ( $level['requires'] as $requirement_row => $requirement ) {

				if ( $requirement['type'] == '' ) {
					$requirement['type'] = MYCRED_DEFAULT_TYPE_KEY;
				}

				if ( ! array_key_exists( $requirement['type'], $point_types ) ) {
					continue;
				}

				if ( ! array_key_exists( $requirement['reference'], $references ) ) {
					$reference = '-';
				} else {
					$reference = $references[ $requirement['reference'] ];
				}

				$base_requirements[ $requirement_row ] = array(
					'type'   => $requirement['type'],
					'ref'    => $reference,
					'amount' => $requirement['amount'],
					'by'     => $requirement['by']
				);

			}

			$badge_title = '';
			$requirements = '';

			$image_identification = $level['image_url'];
			if ( $level['attachment_id'] > 0 ) {
				$image_identification = $level['attachment_id'];
				$level_title = $level['label'];

				if ( ! empty( $level_title ) ) {
					$badge_title = "{$badge->title} - {$level_title}";
				} else {
					$level_key__count = $level_key + 1;
					$badge_title = sprintf( __( "%s - Level %s", 'um-mycred' ), $badge->title, $level_key__count );
				}
			} else {
				$image_identification = $level['attachment_id'];
			}

			$badge_title = strtoupper( $badge_title );

			if ( ! ( $badge_level_reached !== false && $level_key <= $badge_level_reached ) ) {
				$class = 'um-mycred-locked-badge';
			}

			if ( ! empty( $base_requirements ) ) {
				foreach ( $base_requirements as $requirement ) {
					if ( ( $badge_level_reached !== false && $level_key <= $badge_level_reached ) ) { // Unlocked
						$badge_title .= sprintf( _x( '&#013;&#10;%s %s for "%s"', '"Gained/Lost" "x points" for "reference"', 'um-mycred' ), ( ( $requirement['amount'] < 0 ) ? __( 'Lost', 'um-mycred' ) : __( 'Gained', 'um-mycred' ) ), $mycred->format_creds( $requirement['amount'] ), $requirement['ref'] );
					} else { // Locked
						$badge_title .= sprintf( _x( '&#013;&#10;%s %s for "%s"', '"Gain/Lost" "x points" for "reference"', 'um-mycred' ), ( ( $requirement['amount'] < 0 ) ? __( 'Lost', 'um-mycred' ) : __( 'Gain', 'um-mycred' ) ), $mycred->format_creds( $requirement['amount'] ), $requirement['ref'] );
					}
				}
			}

			$image_url = wp_get_attachment_url( $image_identification );

			if ( ! empty( $image_url  ) ) {

				$title = apply_filters( 'um_mycred_badge_loop_title', $badge_title, $badge ); ?>

				<span class="the-badge um-badge-attachment-id-<?php echo esc_attr( $image_identification ) ?>">
					<img src="<?php echo esc_url( $image_url ) ?>"
						 title="<?php echo esc_attr( $title ) ?>"
						 alt="<?php echo esc_attr( $title ) ?>"
						 width="<?php echo esc_attr( $size ) ?>"
						 height="<?php echo esc_attr( $size ) ?>"
						 class="mycred-badge earned um-tip-n <?php echo esc_attr( $class ) ?>" />
				</span>

			<?php }
		} ?>

		<div class="um-clear"></div>
	<?php } ?>
</span>