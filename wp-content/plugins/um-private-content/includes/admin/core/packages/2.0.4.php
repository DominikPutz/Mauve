<?php if ( ! defined( 'ABSPATH' ) ) exit;


$tab_privacy = UM()->options()->get( 'show_private_content_on_profile' );
UM()->options()->update( 'profile_tab_private_content', $tab_privacy );
UM()->options()->remove( 'show_private_content_on_profile' );