<?php
/**
 * Template for the "View order" popup
 * Used on the "Profile" page, "My Orders" tab
 * Called from the WooCommerce_Main_API->ajax_get_order() method
 *
 * This template can be overridden by copying it to yourtheme/ultimate-member/um-woocommerce/order-popup.php
 */
if( !defined( 'ABSPATH' ) ) {
	exit;
}
?>

<div class="um-woo-order-head um-popup-header">

	<div class="um-woo-customer">
		<?php echo get_avatar( get_current_user_id(), 34 ); ?>
		<span><?php echo esc_html( um_user( 'display_name' ) ); ?></span>
	</div>

	<div class="um-woo-orderid">
		<?php printf( __( 'Order# %s', 'um-woocommerce' ), $order_id ); ?>
		<a href="#" class="um-woo-order-hide"><i class="um-icon-close"></i></a>
	</div>

	<div class="um-clear"></div>
</div>

<div class="um-woo-order-body um-popup-autogrow2">

	<p class="order-info"><?php printf( __( 'Order #<mark class="order-number">%s</mark> was placed on <mark class="order-date">%s</mark> and is currently <mark class="order-status">%s</mark>.', 'um-woocommerce' ), $order->get_order_number(), date_i18n( get_option( 'date_format' ), strtotime( $order->get_date_created() ) ), wc_get_order_status_name( $order->get_status() ) ); ?></p>

	<?php if( $notes ) : ?>

		<h2><?php _e( 'Order Updates', 'woocommerce' ); ?></h2>
		<ol class="commentlist notes">
			<?php foreach( $notes as $note ) : ?>
				<li class="comment note">
					<div class="comment_container">
						<div class="comment-text">
							<p class="meta"><?php echo date_i18n( __( 'l jS \o\f F Y, h:ia', 'woocommerce' ), strtotime( $note->comment_date ) ); ?></p>
							<div class="description">
								<?php echo wpautop( wptexturize( $note->comment_content ) ); ?>
							</div>
							<div class="clear"></div>
						</div>
						<div class="clear"></div>
					</div>
				</li>
			<?php endforeach; ?>
		</ol>

		<?php
	endif;

	do_action( 'woocommerce_view_order', $order_id );
	?>

</div>

<div class="um-popup-footer" style="height:30px"></div>