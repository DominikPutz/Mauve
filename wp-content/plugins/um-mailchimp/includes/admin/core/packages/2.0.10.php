<?php if ( ! defined( 'ABSPATH' ) ) exit;

$lists = get_posts( array( 'post_type' => 'um_mailchimp' ) );

foreach( $lists as $list ) {
	$merge_fields = get_post_meta( $list->ID, '_um_merge', true );
	update_post_meta( $list->ID, '_um_merge', array_flip( $merge_fields ) );
}