<?php
/**
 * Template for the list of purchased products
 * Used on the "Profile" page, "Purchases" tab
 * Called from the um_profile_content_purchases_default() function
 *
 * This template can be overridden by copying it to yourtheme/ultimate-member/um-woocommerce/my-purchases.php
 */
if( !defined( 'ABSPATH' ) ) {
	exit;
}

global $post, $product;

if( !$loop->have_posts() ) {
	?>

	<div class="um-profile-note"><span><?php echo ( um_profile_id() == get_current_user_id() ) ? __( 'You did not purchase any product yet.', 'um-woocommerce' ) : __( 'User did not purchase any product yet.', 'um-woocommerce' ); ?></span></div>

	<?php
	return;
}

$i = 0;
while( $loop->have_posts() ) :
	$loop->the_post();

	if( !wc_customer_bought_product( um_user( 'user_email' ), um_profile_id(), get_the_ID() ) ) {
		continue;
	}

	$product = wc_get_product( get_the_ID() );
	?>

	<div class="um-woo-grid um-split-2">
		<div class="um-woo-grid-item">

			<div class="um-woo-grid-img">

				<?php
				if( has_post_thumbnail( $product->get_id() ) ) {
					echo sprintf( __( '<a href="%s" class="um-woo-grid-imgc">%s</a>', 'um-woocommerce' ), $product->get_permalink(), $product->get_image( 'medium' ) );
				} else {
					echo sprintf( __( '<img src="%s" alt="%s" class="um-woo-grid-imgc" />', 'um-woocommerce' ), wc_placeholder_img_src(), __( 'Placeholder', 'um-woocommerce' ) );
				}
				?>

			</div>

			<span class="um-woo-grid-title"><a href="<?php echo $product->get_permalink(); ?>"><span><?php $product->get_title(); ?></span></a></span>
			<span class="um-woo-grid-price"><?php echo $product->get_price_html(); ?></span>
			<span class="um-woo-grid-meta">
				<span class="um-woo-salescount" title="<?php _e( 'Total Sales', 'um-woocommerce' ); ?>"><i class="um-faicon-shopping-cart"></i><?php echo $product->get_total_sales(); ?></span>
				<span class="um-woo-stock_state"><?php echo ucfirst( $product->get_stock_status() ); ?></span>
			</span>

		</div>
	</div>

	<?php
	$i++;
	if( $i % 2 == 0 ) {
		echo '<div class="um-clear"></div>';
	}
endwhile;
wp_reset_postdata();
