<?php
/**
 * Template for the list of orders
 * Used on the "Profile" page, "My Orders" tab
 * Called from the WooCommerce_Account->account_orders_tab_content() method
 *
 * This template can be overridden by copying it to yourtheme/ultimate-member/um-woocommerce/orders.php
 */
if( !defined( 'ABSPATH' ) ) {
	exit;
}

$order = '';
?>

<div class="um-woo-form um-woo-orders">

	<?php
	if ( UM()->account()->current_tab === 'orders' && function_exists( 'wc_print_notices' ) ) {
		wc_print_notices();
	}
	?>

	<?php if( $total_pages ) { ?>

		<table class="shop_table shop_table_responsive my_account_orders">
			<thead>
				<tr>
					<?php do_action( 'um_woocommerce_orders_tab_before_table_header_row', $order, $customer_orders ); ?>
					<th class="order-date"><span class="nobr"><?php _e( 'Date', 'woocommerce' ); ?></span></th>
					<th class="order-status"><span class="nobr"><?php _e( 'Status', 'woocommerce' ); ?></span></th>
					<th class="order-total"><span class="nobr"><?php _e( 'Total', 'woocommerce' ); ?></span></th>
					<th class="order-actions">&nbsp;</th>
					<?php do_action( 'um_woocommerce_orders_tab_after_table_header_row', $order, $customer_orders ); ?>
				</tr>
			</thead>

			<tbody>
				<?php
				foreach( $customer_orders as $customer_order ) {
					$order = wc_get_order( $customer_order->ID );
					$order_id = $customer_order->ID;
					$order_data = $order->get_data();
					$order_date = strtotime( $order->get_date_created() );
					?>

					<tr class="order" data-order_id="<?php echo esc_attr( $order_id ); ?>">

						<?php do_action( 'um_woocommerce_orders_tab_before_table_row', $order, $customer_orders ); ?>

						<td class="order-date" data-title="<?php _e( 'Date', 'woocommerce' ); ?>">
							<time datetime="<?php echo date_i18n( 'Y-m-d', strtotime( $order->get_date_created() ) ); ?>" title=""><?php echo date_i18n( $date_time_format, $order_date, true ); ?></time>
						</td>
						<td class="order-status" data-title="<?php _e( 'Status', 'woocommerce' ); ?>" style="text-align:left; white-space:nowrap;">
							<span class="um-woo-status <?php echo $order->get_status(); ?>"><?php echo wc_get_order_status_name( $order->get_status() ); ?></span>
						</td>
						<td class="order-total" data-title="<?php _e( 'Total', 'woocommerce' ); ?>"><?php echo $order->get_formatted_order_total() ?></td>
						<td class="order-detail">
							<?php echo '<a href="' . esc_url( "$url#$order_id" ) . '" class="um-woo-view-order um-tip-n" title="' . __( 'View order', 'um-woocommerce' ) . '"><i class="um-icon-eye"></i></a>'; ?>
							<?php do_action( 'um_woocommerce_orders_tab_actions', $order, $customer_orders ); ?>
						</td>
						<?php do_action( 'um_woocommerce_orders_tab_after_table_row', $order, $customer_orders ); ?>
					</tr>

				<?php } ?>
			</tbody>
		</table>

		<?php if( $total_pages > 1 ) : ?>

			<div class="um-members-pagidrop uimob340-show uimob500-show">

				<?php
				_e( 'Jump to page:', 'um-woocommerce' );

				if( $pages_to_show < $total_pages ) {
					?>
					<select onChange="window.location.href = this.value" class="um-s2" style="width: 100px">
						<?php for( $i = 1; $i <= $pages_to_show; $i++ ) { ?>
							<option value="<?php echo '?orders_page=' . esc_attr( $i ); ?>" <?php selected( $i, $orders_page ); ?>><?php printf( __( '%s of %d', 'um-woocommerce' ), $i, $total_pages ); ?></option>
						<?php } ?>
					</select>
				<?php } ?>

			</div>

			<div class="um-members-pagi uimob340-hide uimob500-hide">

				<?php if( $orders_page != 1 ) { ?>
					<a href="<?php echo '?orders_page=1'; ?>" class="pagi pagi-arrow um-tip-n" title="<?php _e( 'First Page', 'um-woocommerce' ); ?>"><i class="um-faicon-angle-double-left"></i></a>
				<?php } else { ?>
					<span class="pagi pagi-arrow disabled"><i class="um-faicon-angle-double-left"></i></span>
					<?php
				}

				if( $orders_page > 1 ) {
					?>
					<a href="<?php echo '?orders_page=' . ( $orders_page - 1 ); ?>" class="pagi pagi-arrow um-tip-n" title="<?php _e( 'Previous', 'um-woocommerce' ); ?>"><i class="um-faicon-angle-left"></i></a>
				<?php } else { ?>
					<span class="pagi pagi-arrow disabled"><i class="um-faicon-angle-left"></i></span>
					<?php
				}

				if( $pages_to_show ) {
					for( $i = 1; $i <= $pages_to_show; $i++ ) {
						if( $orders_page == $i ) {
							?>
							<span class="pagi current"><?php echo esc_html( $i ); ?></span>
						<?php } else { ?>
							<a href="<?php echo '?orders_page=' . esc_attr( $i ); ?>" class="pagi"><?php echo esc_html( $i ); ?></a>
							<?php
						}
					}
				}

				if( $orders_page != $total_pages ) {
					?>
					<a href="<?php echo '?orders_page=' . ( esc_html( $orders_page ) + 1 ); ?>" class="pagi pagi-arrow um-tip-n" title="<?php _e( 'Next', 'um-woocommerce' ); ?>"><i class="um-faicon-angle-right"></i></a>
				<?php } else { ?>
					<span class="pagi pagi-arrow disabled"><i class="um-faicon-angle-right"></i></span>
					<?php
				}

				if( $orders_page != $total_pages ) {
					?>
					<a href="<?php echo '?orders_page=' . ( esc_attr( $total_pages ) ); ?>" class="pagi pagi-arrow um-tip-n" title="<?php _e( 'Last Page', 'um-woocommerce' ); ?>"><i class="um-faicon-angle-double-right"></i></a>
				<?php } else { ?>
					<span class="pagi pagi-arrow disabled"><i class="um-faicon-angle-double-right"></i></span>
				<?php } ?>

			</div>

		<?php endif; ?>

	<?php } else { ?>
		<div class="um-field"><?php _e( 'You don\'t have orders yet', 'um-woocommerce' ); ?></div>
	<?php } ?>

</div>