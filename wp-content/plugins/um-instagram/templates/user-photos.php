<?php
/**
 * Template for the UM Instagram field, user photos section
 * Used on the "Profile" page or other page with "Instagram Gallery" field type
 * Called from the Instagram_Public->get_user_photos() method
 *
 * This template can be overridden by copying it to yourtheme/ultimate-member/um-instagram/user-photos.php
 */
if ( !defined( 'ABSPATH' ) ) exit;
?>

<ul id="um-ig-show_photos" data-offset="<?php echo esc_attr( $offset ) ?>" data-photos-count="<?php echo esc_attr( $photos_count ) ?>" data-viewing="<?php echo esc_attr( $viewing ) ?>">

	<?php
	foreach ( $photos as $a => $photo ) {
		$standard_resolution = $photo->images->standard_resolution->url;
		$thumb = $photo->images->thumbnail->url;
		?>

		<li>
			<a class="um-photo-modal" href="<?php echo esc_url( $standard_resolution ) ?>" data-src="<?php echo esc_url( $standard_resolution ) ?>">
				<img class="um-lazy" src="<?php echo esc_url( $thumb ) ?>" data-original="<?php echo esc_attr( $standard_resolution ) ?>" />
			</a>
		</li>
	<?php } ?>

	<?php while ( ( ++$a) < 6 || $a % 3 !== 0 ) { ?>
		<li class="um-ig-photo-placeholder"></li>
		<?php } ?>
</ul>