<?php
/**
 * Template for the UM User Photos, The single "Album" block
 *
 * Page: "Profile", tab "Photos"
 * Parent template: gallery.php
 *
 * This template can be overridden by copying it to yourtheme/ultimate-member/um-user-photos/album-block.php
 */
if ( !defined( 'ABSPATH' ) ) {
	exit;
}

$column = UM()->options()->get( 'um_user_photos_albums_column' );
if( !$column ) {
	$column = 'um-user-photos-col-2';
}

$img = UM()->Photos_API()->common()->um_photos_get_album_cover( $id );
?>

<!-- um-user-photos/templates/album-block.php -->
<div class="um-user-photos-album <?php echo esc_attr( $column ); ?>">
	<a href="javascript:void(0);" class="um-user-photos-album-block" original-title="<?php echo esc_attr( $title ); ?>" data-id="<?php echo esc_attr( $id ); ?>" data-scope="page" data-action="<?php echo esc_url( admin_url( 'admin-ajax.php?action=get_um_user_photos_single_album_view' ) ); ?>">
		<div class="album-overlay"></div>
		<img src="<?php echo esc_url( $img ); ?>" alt="<?php echo esc_attr( $title ); ?>"/>
	</a>

	<div class="um-clear"></div>

	<p class="album-title">
		<strong><?php echo esc_html( $title ); ?></strong>
		<?php if ( $count_msg ) { ?>
			<small> - <?php echo esc_html( $count_msg ); ?></small>
		<?php } ?>
	</p>
</div>