<?php
if ( ! defined( 'ABSPATH' ) ) exit;

add_action('um_post_account_update', 'um_groups_account_update');
function um_groups_account_update(){

  if(isset($_POST['um_group_post_notification'])){
    update_user_meta(get_current_user_id(),'um_group_post_notification',1);
  }else{
    delete_user_meta(get_current_user_id(),'um_group_post_notification');
  }

  if(isset($_POST['um_group_comment_notification'])){
    update_user_meta(get_current_user_id(),'um_group_comment_notification',1);
  }else{
    delete_user_meta(get_current_user_id(),'um_group_comment_notification');
  }

}
