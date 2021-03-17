<?php 
foreach($likes as $like){ 
um_fetch_user($like);
?>
<p class="um-user-photos-like-list-item">
	<a  target="_blank" href="<?php echo esc_url( um_user_profile_url( $like ) ); ?>">
	<?php echo get_avatar( esc_attr( $like ), 40 ); ?>
	<strong style="margin-left:10px;"><?php echo esc_html( um_user('display_name') ); ?></strong>
	</a>
</p>
<?php } ?>

<div class="um-user-photos-modal-footer text-right">
		<a href="javascript:void(0);" class="um-modal-btn alt um-user-photos-modal-close-link">
			<?php _e( 'Close', 'um-user-photos' ); ?>
		</a>
</div>