<?php
/**
 * Template for the "View subscription" block
 * Used on the "Account" page, "Subscriptions" tab
 * Called from the WooCommerce_Main_API->ajax_get_subscription() method
 *
 * This template can be overridden by copying it to yourtheme/ultimate-member/um-woocommerce/subscription.php
 */
if( !defined( 'ABSPATH' ) ) {
	exit;
}
?>

<!-- um-woocommerce/templates/subscription.php -->
<div class="um_account_subscription" style="">
	<a href="#" class="button back_to_subscriptions"><?php _e( 'All subscriptions', 'ultimate-member' ); ?></a>

	<table class="shop_table subscription_details shop_table_responsive my_account_subscriptions my_account_orders">
		<tr>
			<td><?php esc_html_e( 'Subscription', 'ultimate-member' ); ?></td>
			<td>#<?php echo esc_html( sanitize_key( $_POST[ 'subscription_id' ] ) ); ?></td>
		</tr>
		<tr>
			<td><?php esc_html_e( 'Status', 'ultimate-member' ); ?></td>
			<td><?php echo esc_html( wcs_get_subscription_status_name( $subscription->get_status() ) ); ?></td>
		</tr>
		<tr>
			<td><?php echo esc_html_x( 'Start Date', 'table heading', 'ultimate-member' ); ?></td>
			<td><?php echo esc_html( $subscription->get_date_to_display( 'date_created' ) ); ?></td>
		</tr>

		<?php
		foreach( $columns as $date_type => $date_title ) {
			$date = $subscription->get_date( $date_type );

			if( !empty( $date ) ) {
				?>
				<tr>
					<td><?php echo esc_html( $date_title ); ?></td>
					<td><?php echo esc_html( $subscription->get_date_to_display( $date_type ) ); ?></td>
				</tr>
				<?php
			}
		}

		do_action( 'woocommerce_subscription_before_actions', $subscription );

		if( !empty( $actions ) ) {
			?>
			<tr>
				<td><?php esc_html_e( 'Actions', 'ultimate-member' ); ?></td>
				<td>
					<?php foreach( $actions as $key => $action ) { ?>
						<a href="<?php echo esc_url( $action[ 'url' ] ); ?>" class="button <?php echo sanitize_html_class( $key ) ?>">
							<?php echo esc_html( $action[ 'name' ] ); ?>
						</a>
					<?php } ?>
				</td>
			</tr>
			<?php
		}

		do_action( 'woocommerce_subscription_after_actions', $subscription );
		?>
	</table>

	<?php if( $notes ) { ?>
		<h2><?php esc_html_e( 'Subscription Updates', 'ultimate-member' ); ?></h2>
		<ol class="commentlist notes">
			<?php foreach( $notes as $note ) { ?>
				<li class="comment note">
					<div class="comment_container">
						<div class="comment-text">
							<p class="meta">
								<?php echo esc_html( date_i18n( _x( 'l jS \o\f F Y, h:ia', 'date on subscription updates list. Will be localized', 'ultimate-member' ), wcs_date_to_time( $note->comment_date ) ) ); ?>
							</p>
							<div class="description">
								<?php echo wp_kses_post( wpautop( wptexturize( $note->comment_content ) ) ); ?>
							</div>
							<div class="clear"></div>
						</div>
						<div class="clear"></div>
					</div>
				</li>
			<?php } ?>
		</ol>
		<?php
	}

	/** Gets subscription totals table template */
	do_action( 'woocommerce_subscription_totals_table', $subscription );

	/** Related Orders */
	do_action( 'woocommerce_subscription_details_after_subscription_table', $subscription );
	?>

</div>