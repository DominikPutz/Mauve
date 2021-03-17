<?php
/**
 * Template for the list of reviewed products
 * Used on the "Profile" page, "Product Reviews" tab
 * Called from the um_profile_content_product_reviews_default() function
 *
 * This template can be overridden by copying it to yourtheme/ultimate-member/um-woocommerce/product-reviews.php
 */
if( !defined( 'ABSPATH' ) ) {
	exit;
}

if( empty( $comments ) ) {
	?>

	<div class="um-profile-note"><span><?php echo ( um_profile_id() == get_current_user_id() ) ? __( 'You did not review any products yet.', 'um-woocommerce' ) : __( 'User did not review any product yet.', 'um-woocommerce' ); ?></span></div>

	<?php
	return;
}

$i = 0;
foreach( $comments as $comment ) {
	$product = wc_get_product( $comment->comment_post_ID );
	$rating = intval( get_comment_meta( $comment->comment_ID, 'rating', true ) );
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

			<span class="um-woo-grid-title"><a href="<?php echo $product->get_permalink(); ?>"><span><?php echo $product->get_title(); ?></span></a></span>
			<span class="um-woo-grid-price"><?php echo $product->get_price_html(); ?></span>
			<span class="um-woo-grid-avg um-rating-readonly" data-number="5" data-score="<?php echo esc_attr( $rating ); ?>"></span>
			<span class="um-woo-grid-content"><?php echo '&ldquo;' . esc_html( $comment->comment_content ) . '&rdquo;'; ?></span>

		</div>
	</div>

	<?php
	$i++;
	if( $i % 2 == 0 ) {
		echo '<div class="um-clear"></div>';
	}
}
wp_reset_postdata();
