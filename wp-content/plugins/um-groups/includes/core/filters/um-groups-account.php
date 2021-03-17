<?php
if ( ! defined( 'ABSPATH' ) ) exit;


	/***
	***	@add tab to account page
	***/
  add_filter( 'um_account_page_default_tabs_hook','add_groups_account_tab', 10, 1 );
	function add_groups_account_tab( $tabs ) {

        $tabs[800]['groups']['icon'] = 'um-faicon-users';
    		$tabs[800]['groups']['title'] = __('Groups','um-groups');
    		$tabs[800]['groups']['custom'] = false;
    		$tabs[800]['groups']['show_button'] = true;
        $tabs[800]['groups']['submit_title'] = __('Update','um-groups');

		    return $tabs;
	}




  add_filter('um_account_content_hook_groups','um_account_content_hook_groups');
  function um_account_content_hook_groups( $output ){
    $user_id = get_current_user_id();
    ob_start();
    $post_notification = get_user_meta($user_id,'um_group_post_notification',true );
    $comment_notification = get_user_meta($user_id,'um_group_comment_notification',true );
    ?>
    <div class="um-field" data-key="">
			<div class="um-field-label"><strong><?php _e( 'Receiving Notifications', 'um-groups' ); ?></strong></div>
			<div class="um-field-area">

    <?php if($post_notification): ?>
    <label class="um-field-checkbox active">
				<input type="checkbox" name="um_group_post_notification" value="1" checked/>
				<span class="um-field-checkbox-state"><i class="um-icon-android-checkbox-outline"></i></span>
				<span class="um-field-checkbox-option"><?php _e('Notify me when someone posts on group','um-groups'); ?></span>
		</label>
  <?php else: ?>
    <label class="um-field-checkbox">
				<input type="checkbox" name="um_group_post_notification" value="1"/>
				<span class="um-field-checkbox-state"><i class="um-icon-android-checkbox-outline-blank"></i></span>
				<span class="um-field-checkbox-option"><?php _e('Notify me when someone posts on group','um-groups'); ?></span>
		</label>
  <?php endif; ?>


  <?php if($comment_notification): ?>
    <label class="um-field-checkbox active">
        <input type="checkbox" name="um_group_comment_notification" value="1" checked/>
        <span class="um-field-checkbox-state"><i class="um-icon-android-checkbox-outline"></i></span>
        <span class="um-field-checkbox-option"><?php _e('Notify me when someone comments on group','um-groups'); ?></span>
    </label>
<?php else: ?>
  <label class="um-field-checkbox">
      <input type="checkbox" name="um_group_comment_notification" value="1" />
      <span class="um-field-checkbox-state"><i class="um-icon-android-checkbox-outline-blank"></i></span>
      <span class="um-field-checkbox-option"><?php _e('Notify me when someone comments on group','um-groups'); ?></span>
  </label>
<?php endif; ?>

    </div>
  </div>
    <?php
    $output .= ob_get_contents();
    ob_end_clean();
    return $output;

  }
