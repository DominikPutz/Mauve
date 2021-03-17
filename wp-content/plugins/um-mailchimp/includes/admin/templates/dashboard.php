<?php
$result = UM()->Mailchimp()->api()->get_account_data();
$count_temp_files = UM()->Mailchimp()->api()->count_temp_files();
$count_log_files = UM()->Mailchimp()->api()->count_log_files()
?>

<p class="sub"><?php _e( 'Connection status', 'um-mailchimp' ); ?></p>

<?php
if( is_wp_error( $result ) ) {
	?>

	<p><span class="red"><?php echo $result->get_error_message(); ?></span></p>

	<?php
}
else {
	if( isset( $result[ 'account_name' ] ) ) {
		?>
		<p>
			<?php printf( __( 'Your site is successfully <strong><span class="ok">linked</span></strong> to <strong>%s</strong> MailChimp account.', 'um-mailchimp' ), $result[ 'account_name' ] ); ?>
		</p>
	<?php } ?>

	<p class="sub">
		<?php _e( 'Account status', 'um-mailchimp' ); ?>
	</p>

	<?php $external_lists = UM()->Mailchimp()->api()->get_lists( true ); ?>
	<p><?php printf( _n( '%d subscriber', ' %d subscribers', $result[ 'total_subscribers' ], 'um-mailchimp' ), $result[ 'total_subscribers' ] ); ?>
	<p><?php printf( _n( '%d Mailchimp audience', '%d Mailchimp audiences', count( $external_lists ), 'um-mailchimp' ), count( $external_lists ) ); ?></p>

	<?php $internal_lists = UM()->Mailchimp()->api()->get_wp_lists_array( false ); ?>
	<p><?php printf( _n( '%d UM Mailchimp audience', '%d UM Mailchimp audiences', count( $internal_lists ), 'um-mailchimp' ), count( $internal_lists ) ); ?></p>

	<script type="text/html" id="tmpl-um-mailchimp-sync-metabox">
		<div class="um_mailchimp_metabox">
			<select name="um_mailchimp_sync_list" class="um_mailchimp_sync_list" style="max-width:300px;min-width:100px;">
				<option value=""><?php _e( '~Select audience~', 'um-mailchimp' ); ?></option>
				<# for( index in data.internal_lists ) { #>
				<option value="<# print( index ) #>" <# if( data.internal_list == index ) { #>selected="selected"<# } #>><# print( data.internal_lists[ index ] ) #></option>
				<# } #>
			</select>
			<a href="javascript:void(0);" id="btn_um_mailchimp_sync_now" class="um-btn-mailchimp-progress-start button <# if( data.button_disabled ) { #>disabled<# } #>"><?php _e( 'Sync Now', 'um-mailchimp' ); ?></a>
			<# if( data.message ) { #>
			<p class="um-progress-message-area">
				<# if( data.loading ) { #>
			<span class="spinner"></span>
			<# } #>
			<span class="um-progress-message"><# print( data.message ) #></span>
			</p>
			<# } #>
		</div>
	</script>

	<script type="text/html" id="tmpl-um-mailchimp-subscribe-metabox">
		<div class="um_mailchimp_metabox">
			<# if( ! data.step ) { #>

			<select name="um_mailchimp_user_role" class="um_mailchimp_user_role"  style="width:100px;">
				<option value=""><?php _e( 'All Roles', 'um-mailchimp' ) ?></option>
				<# for( index in data.roles ) { #>
				<option value="<# print( index ) #>" <# if( data.role == index ) { #>selected="selected"<# } #>><# print( data.roles[ index ] ) #></option>
				<# } #>
			</select>
			<select name="um_mailchimp_user_status" class="um_mailchimp_user_status" style="width:100px;">
				<option value=""><?php _e( 'All Status', 'um-mailchimp' ) ?></option>
				<# for( index in data.status_list ) { #>
				<option value="<# print( index ) #>" <# if( data.status == index ) { #>selected="selected"<# } #>><# print( data.status_list[ index ] ) #></option>
				<# } #>
			</select>
			<a href="javascript:void(0);" id="btn_um_mailchimp_scan_now" class="um-btn-mailchimp-progress-start button <# if( data.button_disabled ) { #>disabled<# } #>"><?php _e( 'Scan Now', 'um-mailchimp' ); ?></a>

			<# } else if( data.step == 2 ) { #>

			<select name="um_mailchimp_list" class="um_mailchimp_list" style="max-width:300px;min-width:100px;">
				<option value=""><?php _e( '~Select audience~', 'um-mailchimp' ); ?></option>
				<# for( index in data.internal_lists ) { #>
				<option value="<# print( index ) #>" <# if( data.internal_list == index ) { #>selected="selected"<# } #>><# print( data.internal_lists[ index ] ) #></option>
				<# } #>
			</select>
			<a href="javascript:void(0);" id="btn_um_mailchimp_bulk_subscribe" class="um-btn-mailchimp-progress-start button <# if( data.button_disabled ) { #>disabled<# } #>"><?php _e( 'Subscribe', 'um-mailchimp' ); ?></a>
			<a href="javascript:void(0);" id="btn_um_mailchimp_bulk_unsubscribe" class="um-btn-mailchimp-progress-start button <# if( data.button_disabled ) { #>disabled<# } #>"><?php _e( 'Unsubscribe', 'um-mailchimp' ); ?></a>
			<# } #>
			<# if( data.message ) { #>
			<p class="um-progress-message-area">
				<# if( data.loading ) { #>
			<span class="spinner"></span>
			<# } #>
			<span class="um-progress-message"><# print( data.message ) #></span>
			</p>

			<# } #>
		</div>
	</script>

	<p class="sub"><?php _e( 'Cache', 'um-mailchimp' ); ?> <small>(<?php echo UM()->options()->get( 'mailchimp_enable_cache' ) ? __( 'Enabled', 'um-mailchimp' ) : __( 'Disabled', 'um-mailchimp' ); ?>)</small></p>
	<div id="um-mailchimp-cache-metabox-wrapper">
		<p><?php printf( _n( '%d temporary file', '%d temporary files', $count_temp_files, 'um-mailchimp' ), $count_temp_files ); ?>, <?php printf( _n( '%d log file', '%d log files', $count_log_files, 'um-mailchimp' ), $count_log_files ); ?></p>
		<a href="<?php echo esc_url( add_query_arg( array( 'um_action' => 'um_mailchimp_clear_cache' ) ) ); ?>" id="btn_um_mailchimp_clear_cache" class="um-btn-mailchimp-progress-start button"><?php _e( 'Clear Cache', 'um-mailchimp' ); ?></a>
		<button class="button" id="um_mailchimp_clear_log"><?php _e( 'Clear Log', 'um-mailchimp' ) ?></button>
	</div>
	<br />

	<p class="sub"><?php _e( 'Sync Profiles', 'um-mailchimp' ); ?></p>
	<div id="um-mailchimp-sync-metabox-wrapper"></div>
	<br />

	<p class="sub"><?php _e( 'Bulk Subscribe & Unsubscribe', 'um-mailchimp' ); ?></p>
	<div id="um-mailchimp-subscribe-metabox-wrapper"></div>
<?php } ?>