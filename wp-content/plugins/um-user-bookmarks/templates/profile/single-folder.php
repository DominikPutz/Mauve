<?php if ( ! defined( 'ABSPATH' ) ) exit; ?>

<header style="width:100%;display:block;position:relative;">
	<a href="javascript:void(0);" class="um-user-bookmarks-back-btn" data-profile="<?php echo esc_attr( $user ); ?>"
	   data-nonce="<?php echo wp_create_nonce( 'um_user_bookmarks_back' ); ?>" style="width:5%;float:left;display:inline-block;text-align:center;">
		<i class="um-faicon-arrow-left"></i>
	</a>

	<h3 style="width:89%;float:none;text-align:center;margin:0;display:inline-block;"><?php echo $title; ?></h3>

	<?php if ( is_user_logged_in() && $user == get_current_user_id() ) {

		UM()->get_template( 'profile/single-folder/dropdown.php', um_user_bookmarks_plugin, array(
			'key'       => $key,
			'user_id'   => $user,
		), true );

	} ?>
</header>
<br/>
<hr/>
<br/>

<?php $bookmarks = array();

$user_bookmarks = get_user_meta( $user, '_um_user_bookmarks', true );
if ( $user_bookmarks && isset( $user_bookmarks[ $key ] ) ) {
	if ( ! empty( $user_bookmarks[ $key ]['bookmarks'] ) ) {
		$bookmarks = array_keys( $user_bookmarks[ $key ]['bookmarks'] );
	}
} ?>

<section class="um-user-bookmarks">
	<?php if ( empty( $bookmarks ) ) {
		_e( 'Folder is empty', 'um-user-bookmarks' );
	} else {
		UM()->get_template( 'profile/bookmarks.php', um_user_bookmarks_plugin, array(
			'bookmarks' => $bookmarks
		), true );
	} ?>
</section>
<div class="um-clear"></div>