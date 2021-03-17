<?php
/**
 * Template for the UM Private Messages.
 * Used on the "Profile" page, "Messages" tab.
 *
 * Shortcode: [ultimatemember_messages]
 * Caller: method Messaging_Shortcode->ultimatemember_messages()
 *
 * This template can be overridden by copying it to yourtheme/ultimate-member/um-messaging/conversations.php
 */
if ( ! defined( 'ABSPATH' ) ) exit;

$current_conversation = '""';
if ( isset( $_GET['conversation_id'] ) ) {
	$current_conversation = esc_attr( absint( sanitize_key( $_GET['conversation_id'] ) ) );
} ?>

<script type="text/template" id="tmpl-um_messages_convesations">
	<# _.each( data.conversations, function( conversation, key ) { #>
		<a href="{{{conversation.url}}}" class="um-message-conv-item <# if ( conversation.conversation_id == <?php echo $current_conversation; ?> ) { #> active <# } #>" data-message_to="{{{conversation.user}}}" data-trigger_modal="conversation" data-conversation_id="{{{conversation.conversation_id}}}">

			<span class="um-message-conv-name">{{{conversation.user_name}}}</span>

			<span class="um-message-conv-pic">{{{conversation.avatar}}}</span>

			<# if ( conversation.new_conv ) { #>
				<span class="um-message-conv-new"><i class="um-faicon-circle"></i></span>
			<# } #>

			<?php do_action( 'um_messaging_conversation_list_name_js' ); ?>
		</a>
	<# }); #>
</script>

<?php if ( ! empty( $conversations ) ) { ?>

	<div class="um um-viewing">
		<div class="um-message-conv" data-user="<?php echo esc_attr( um_profile_id() ); ?>">

			<?php $i = 0;
			$profile_can_read = um_user( 'can_read_pm' );
			foreach ( $conversations as $conversation ) {

				if ( $conversation->user_a == um_profile_id() ) {
					$user = $conversation->user_b;
				} else {
					$user = $conversation->user_a;
				}

				$i++;

				if ( $i == 1 && ! isset( $current_conversation ) ) {
					$current_conversation = $conversation->conversation_id;
				}

				um_fetch_user( $user );

				$user_name = ( um_user( 'display_name' ) ) ? um_user( 'display_name' ) : __( 'Deleted User', 'um-messaging' );

				$is_unread = UM()->Messaging_API()->api()->unread_conversation( $conversation->conversation_id, um_profile_id() ); ?>

				<a href="<?php echo esc_url( add_query_arg( 'conversation_id', $conversation->conversation_id ) ); ?>" class="um-message-conv-item <?php if ( $conversation->conversation_id == $current_conversation ) echo 'active '; ?>" data-message_to="<?php echo esc_attr( $user ); ?>" data-trigger_modal="conversation" data-conversation_id="<?php echo esc_attr( $conversation->conversation_id ); ?>">

					<span class="um-message-conv-name"><?php echo esc_html( $user_name ); ?></span>

					<span class="um-message-conv-pic"><?php echo get_avatar( $user, 40 ); ?></span>

					<?php if ( $is_unread && $profile_can_read ) { ?>
						<span class="um-message-conv-new"><i class="um-faicon-circle"></i></span>
					<?php }

					do_action( 'um_messaging_conversation_list_name' ); ?>

				</a>

			<?php } ?>
			<div data-user="<?php echo um_profile_id(); ?>" class="um-message-conv-load-more"></div>
		</div>

		<div class="um-message-conv-view">

			<?php $i = 0;
			foreach ( $conversations as $conversation ) {

				if ( isset( $current_conversation ) && $current_conversation != $conversation->conversation_id ) {
					continue;
				}

				if ( $conversation->user_a == um_profile_id() ) {
					$user = $conversation->user_b;
				} else {
					$user = $conversation->user_a;
				}

				if ( UM()->Messaging_API()->api()->blocked_user( $user ) ) {
					continue;
				}
				if ( UM()->Messaging_API()->api()->hidden_conversation( $conversation->conversation_id ) ) {
					continue;
				}

				$i++;
				if ( $i > 1 ) {
					continue;
				}

				um_fetch_user( $user );

				$user_name = ( um_user( 'display_name' ) ) ? um_user( 'display_name' ) : __( 'Deleted User', 'um-messaging' );

				UM()->get_template( 'conversation.php', um_messaging_plugin, array(
					'message_to' => $user,
					'user_id' => $user_id,
				), true );
			} ?>

		</div>
		<div class="um-clear"></div>
	</div>

	<?php do_action( 'um_messaging_after_conversations_list' );

} else { ?>

	<div class="um-message-noconv">
		<i class="um-icon-android-chat"></i>
		<?php _e( 'No chats found here', 'um-messaging' ); ?>
	</div>

<?php } ?>
