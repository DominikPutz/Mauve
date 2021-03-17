<?php
/**
 * Template for the UM myCRED.
 * Used on Account page, My Points tab
 *
 * Caller: function um_mycred_bb_rank_bar()
 *
 * This template can be overridden by copying it to yourtheme/ultimate-member/um-mycred/rank_bar.php
 */
if( ! defined( 'ABSPATH' ) ) exit;


if ( is_object( $rank ) ) { ?>

	<div class="um-mycred-bb-progress">
		<span class="um-mycred-progress um-tip-n" title="<?php echo esc_attr( $rank->title . ' ' . ( int ) UM()->myCRED()->get_rank_progress( $user_id ) . '%' ) ?>">
			<span class="um-mycred-progress-done" style="" data-pct="<?php echo esc_attr( UM()->myCRED()->get_rank_progress( $user_id ) ) ?>"></span>
		</span>
	</div>

<?php }